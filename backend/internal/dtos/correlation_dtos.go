package dtos

type MSLQCorrelationDetail struct {
	Component   string  `json:"component"`
	Correlation float64 `json:"correlation"`
	Explanation string  `json:"explanation"`
}

type AMSCorrelationDetail struct {
	Component   string  `json:"component"`
	Correlation float64 `json:"correlation"`
	Explanation string  `json:"explanation"`
}

type CorrelationAnalysisResponse struct {
	DominantVARKStyle string                  `json:"dominant_vark_style"`
	MSLQCorrelation   []MSLQCorrelationDetail `json:"mslq_correlation"`
	AMSCorrelation    []AMSCorrelationDetail  `json:"ams_correlation"`
	Recommendations   []string                `json:"recommendations"`
}

type CorrelationAnalysisRequest struct {
	VARKScores map[string]float64 `json:"vark_scores"`
	MSLQScore  float64            `json:"mslq_score"`
	AMSScore   float64            `json:"ams_score"`
}
