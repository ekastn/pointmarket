package gateway

import (
	"bytes"
	"context"
	"encoding/json"
	"fmt"
	"net/http"
	"net/url"
	"strconv"
	"time"
)

// RecommendationGateway handles communication with the Recommendation microservice.
type RecommendationGateway struct {
	BaseURL string
	Client  *http.Client
	Token   string 
}

func NewRecommendationGateway(baseURL, token string) *RecommendationGateway {
	return &RecommendationGateway{
		BaseURL: baseURL,
		Token:   token,
		Client:  &http.Client{Timeout: 5 * time.Second},
	}
}

// TrainRequest represents payload to start training.
type TrainRequest struct {
	Episodes int `json:"episodes"`
}

// Train triggers model training upstream (global training, not per student).
func (g *RecommendationGateway) Train(ctx context.Context, episodes int) error {
	if episodes <= 0 {
		episodes = 50 // sensible default
	}
	payload := TrainRequest{Episodes: episodes}
	b, err := json.Marshal(payload)
	if err != nil {
		return err
	}
	endpoint := fmt.Sprintf("%s/train", g.BaseURL)
	req, err := http.NewRequestWithContext(ctx, http.MethodPost, endpoint, bytes.NewReader(b))
	if err != nil {
		return err
	}
	req.Header.Set("Content-Type", "application/json")
	if g.Token != "" {
		req.Header.Set("X-Internal-Token", g.Token)
	}
	resp, err := g.Client.Do(req)
	if err != nil {
		return err
	}
	defer resp.Body.Close()
	if resp.StatusCode >= 400 {
		return fmt.Errorf("upstream train status %d", resp.StatusCode)
	}
	return nil
}

// raw structures mirroring upstream JSON (only what we need)
// FlexibleString handles fields that may be number or string (or empty)
type FlexibleString struct {
	Value string
}

func (fs *FlexibleString) UnmarshalJSON(b []byte) error {
	// Empty or null
	if string(b) == "null" || string(b) == "" || string(b) == "\"\"" {
		fs.Value = ""
		return nil
	}
	// Try as string first
	var s string
	if err := json.Unmarshal(b, &s); err == nil {
		fs.Value = s
		return nil
	}
	// Try as number
	var f float64
	if err := json.Unmarshal(b, &f); err == nil {
		// Normalize: drop decimal if integer
		if f == float64(int64(f)) {
			fs.Value = strconv.FormatInt(int64(f), 10)
		} else {
			fs.Value = strconv.FormatFloat(f, 'f', -1, 64)
		}
		return nil
	}
	fs.Value = ""
	return nil
}

type upstreamAction struct {
	ActionCode int     `json:"action_code"`
	QValue     float64 `json:"q_value"`
	Items      []struct {
		Title             string         `json:"judul"`
		Description       string         `json:"deskripsi"`
		Category          string         `json:"kategori"`
		TargetAudience    string         `json:"target_audience"`
		DifficultyLevel   FlexibleString `json:"difficulty_level"`
		EstimatedDuration string         `json:"estimated_duration"`
	} `json:"items"`
	ItemsRefs []struct {
		RefType string `json:"ref_type"`
		RefID   int64  `json:"ref_id"`
	} `json:"items_refs"`
}

type upstreamResponse struct {
    StudentID             string                    `json:"siswa_id"`
    CurrentState          string                    `json:"current_state"`
    ActionRecommendations map[string]upstreamAction `json:"action_recommendations"`
    Summary               struct {
        HasTrainedQValues bool `json:"has_trained_q_values"`
    } `json:"summary"`
    Message string `json:"message"`
    Trace   map[string]interface{} `json:"trace"`
}

// GetStudentRecommendations fetches upstream recommendations.
func (g *RecommendationGateway) GetStudentRecommendations(studentID string) (*upstreamResponse, error) {
	// Use query parameter endpoint: /recommendations?student_id={id}
	endpoint := fmt.Sprintf("%s/recommendations", g.BaseURL)
	q := url.Values{}
	q.Set("student_id", studentID)
	fullURL := endpoint + "?" + q.Encode()
	req, err := http.NewRequest(http.MethodGet, fullURL, nil)
	if err != nil {
		return nil, err
	}
	if g.Token != "" {
		req.Header.Set("X-Internal-Token", g.Token)
	}
	resp, err := g.Client.Do(req)
	if err != nil {
		return nil, err
	}
	defer resp.Body.Close()
	if resp.StatusCode >= 400 {
		return nil, fmt.Errorf("upstream status %d", resp.StatusCode)
	}
	var up upstreamResponse
	if err := json.NewDecoder(resp.Body).Decode(&up); err != nil {
		return nil, err
	}
	return &up, nil
}

// GetStudentRecommendationsTrace fetches upstream recommendations with trace=1 for admin analysis.
func (g *RecommendationGateway) GetStudentRecommendationsTrace(studentID string) (*upstreamResponse, error) {
    endpoint := fmt.Sprintf("%s/recommendations", g.BaseURL)
    q := url.Values{}
    q.Set("student_id", studentID)
    q.Set("trace", "1")
    fullURL := endpoint + "?" + q.Encode()
    req, err := http.NewRequest(http.MethodGet, fullURL, nil)
    if err != nil {
        return nil, err
    }
    if g.Token != "" {
        req.Header.Set("X-Internal-Token", g.Token)
    }
    resp, err := g.Client.Do(req)
    if err != nil {
        return nil, err
    }
    defer resp.Body.Close()
    if resp.StatusCode >= 400 {
        return nil, fmt.Errorf("upstream status %d", resp.StatusCode)
    }
    var up upstreamResponse
    if err := json.NewDecoder(resp.Body).Decode(&up); err != nil {
        return nil, err
    }
    return &up, nil
}

// UpsertRecommendationStudent ensures a student exists in the recommendation service.
// If create is true it will POST, else PUT (update). We keep it private to avoid exposure via handlers.
func (g *RecommendationGateway) upsertStudent(studentID string, payload map[string]interface{}, create bool) error {
	method := http.MethodPost
	endpoint := fmt.Sprintf("%s/students", g.BaseURL)
	if !create {
		method = http.MethodPut
		endpoint = fmt.Sprintf("%s/students/%s", g.BaseURL, studentID)
	}
	body, err := json.Marshal(payload)
	if err != nil {
		return err
	}
	req, err := http.NewRequest(method, endpoint, bytes.NewReader(body))
	if err != nil {
		return err
	}
	req.Header.Set("Content-Type", "application/json")
	if g.Token != "" {
		req.Header.Set("X-Internal-Token", g.Token)
	}
	resp, err := g.Client.Do(req)
	if err != nil {
		return err
	}
	defer resp.Body.Close()
	if resp.StatusCode >= 400 {
		return fmt.Errorf("upstream student upsert status %d", resp.StatusCode)
	}
	return nil
}

// EnsureStudent mirrors main student data into recommendation service (create if missing, else update).
func (g *RecommendationGateway) EnsureStudent(studentID string, vark, mslq, ams float64, engagement string) error {
	payload := map[string]interface{}{
		"siswa_id":   studentID,
		"vark":       vark,
		"mslq":       mslq,
		"ams":        ams,
		"engagement": engagement,
	}
	// Try create first
	if err := g.upsertStudent(studentID, payload, true); err != nil {
		// Attempt update if already exists
		return g.upsertStudent(studentID, payload, false)
	}
	return nil
}

// Export shims so service layer can map without exporting raw internals broadly.
type UpstreamResponseExportShim = upstreamResponse
type UpstreamActionExportShim = upstreamAction
