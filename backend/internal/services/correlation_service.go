package services

import (
	"context"
	"fmt"
	"log"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/store/gen"
)

type CorrelationService struct {
	q gen.Querier
}

func NewCorrelationService(q gen.Querier) *CorrelationService {
	return &CorrelationService{q: q}
}

func (s *CorrelationService) GetCorrelationAnalysisForStudent(ctx context.Context, studentID int64) (*dtos.CorrelationAnalysisResponse, error) {
	pref, err := s.q.GetLatestUserLearningStyle(ctx, studentID)
	if err != nil {
		return nil, fmt.Errorf("failed to get VARK result: %w", err)
	}

	varkScores := map[string]float64{
		"Visual":      *pref.ScoreVisual,
		"Auditory":    *pref.ScoreAuditory,
		"Reading":     *pref.ScoreReading,
		"Kinesthetic": *pref.ScoreKinesthetic,
	}

	mslqResult, err := s.q.GetLatestLikertResultByType(ctx, gen.GetLatestLikertResultByTypeParams{
		Type:   gen.QuestionnairesTypeMSLQ,
		UserID: studentID,
	})
	if err != nil {
		return nil, fmt.Errorf("failed to get MSLQ result: %w", err)
	}

	amsResult, err := s.q.GetLatestLikertResultByType(ctx, gen.GetLatestLikertResultByTypeParams{
		Type:   gen.QuestionnairesTypeAMS,
		UserID: studentID,
	})
	if err != nil {
		return nil, fmt.Errorf("failed to get AMS result: %w", err)
	}

	analysis, err := s.AnalyzeAndRecommend(varkScores, mslqResult.TotalScore, amsResult.TotalScore)
	if err != nil {
		return nil, err
	}

	return analysis, nil
}

func (s *CorrelationService) AnalyzeAndRecommend(varkScores map[string]float64, mslqScore float64, amsScore float64) (*dtos.CorrelationAnalysisResponse, error) {
	dominantVARKStyle := ""
	maxScore := -1.0

	for style, score := range varkScores {
		if score > maxScore {
			maxScore = score
			dominantVARKStyle = style
		}
	}

	response := &dtos.CorrelationAnalysisResponse{
		VARKScores:        varkScores,
		MSLQScore:         mslqScore,
		AMSScore:          amsScore,
		DominantVARKStyle: dominantVARKStyle,
		MSLQCorrelation:   []dtos.MSLQCorrelationDetail{},
		AMSCorrelation:    []dtos.AMSCorrelationDetail{},
		Recommendations:   []string{},
	}

	log.Printf("Dominant VARK style: %s", dominantVARKStyle)

	switch dominantVARKStyle {
	case "Visual":
		response.MSLQCorrelation = []dtos.MSLQCorrelationDetail{
			{Component: "Elaboration", Correlation: 0.65, Explanation: "Strong - excel at creating visual mental models"},
			{Component: "Organization", Correlation: 0.70, Explanation: "Very Strong - visual organization tools preference"},
			{Component: "Critical Thinking", Correlation: 0.55, Explanation: "Moderate - visual pattern recognition enhances analysis"},
			{Component: "Metacognitive Self-Reg", Correlation: 0.52, Explanation: "Moderate - visual monitoring of learning progress"},
			{Component: "Time Management", Correlation: 0.48, Explanation: "Moderate - visual planning tools like calendars"},
			{Component: "Effort Regulation", Correlation: 0.42, Explanation: "Moderate - visual progress tracking motivates effort"},
		}
		response.AMSCorrelation = []dtos.AMSCorrelationDetail{
			{Component: "Intrinsic - To Know", Correlation: 0.60, Explanation: "Strong - visual discovery drives curiosity"},
			{Component: "Intrinsic - Accomplish", Correlation: 0.58, Explanation: "Strong - visual achievement representation"},
			{Component: "Intrinsic - Stimulation", Correlation: 0.45, Explanation: "Moderate - visual content provides stimulation"},
			{Component: "External - Identified", Correlation: 0.40, Explanation: "Moderate - visual rewards recognition"},
			{Component: "External - Introjected", Correlation: 0.35, Explanation: "Low-Moderate - less influenced by guilt/shame"},
			{Component: "Amotivation", Correlation: -0.45, Explanation: "Negative - visual stimulation reduces amotivation"},
		}
		response.Recommendations = []string{
			"Fokus pada pengembangan visual organization tools (mind maps, flowcharts)",
			"Leverage elaboration strategies berbasis visual",
			"Gunakan infografis dan diagram untuk meningkatkan critical thinking",
			"Provide visual progress tracking untuk effort regulation",
		}
	case "Auditory":
		response.MSLQCorrelation = []dtos.MSLQCorrelationDetail{
			{Component: "Help Seeking", Correlation: 0.75, Explanation: "Very Strong - strong preference for verbal explanation"},
			{Component: "Peer Learning", Correlation: 0.68, Explanation: "Strong - excel in group discussions and collaboration"},
			{Component: "Rehearsal", Correlation: 0.60, Explanation: "Strong - verbal repetition as primary memory strategy"},
			{Component: "Elaboration", Correlation: 0.45, Explanation: "Moderate - verbal elaboration and explanation"},
			{Component: "Critical Thinking", Correlation: 0.48, Explanation: "Moderate - verbal reasoning and argumentation"},
			{Component: "Effort Regulation", Correlation: 0.52, Explanation: "Moderate - verbal encouragement maintains effort"},
		}
		response.AMSCorrelation = []dtos.AMSCorrelationDetail{
			{Component: "Intrinsic - Stimulation", Correlation: 0.65, Explanation: "Strong - excitement from verbal interaction and discussion"},
			{Component: "External - Identified", Correlation: 0.55, Explanation: "Strong - social recognition and verbal praise motivates"},
			{Component: "Intrinsic - To Know", Correlation: 0.50, Explanation: "Moderate - learning through listening and discussion"},
			{Component: "External - Introjected", Correlation: 0.45, Explanation: "Moderate - social pressure and approval seeking"},
			{Component: "Intrinsic - Accomplish", Correlation: 0.42, Explanation: "Moderate - verbal recognition of achievements"},
			{Component: "Amotivation", Correlation: -0.38, Explanation: "Negative - social interaction reduces amotivation"},
		}
		response.Recommendations = []string{
			"Prioritize collaborative learning dan discussion groups",
			"Implement verbal rehearsal strategies",
			"Provide audio content dan podcast-style materials",
			"Encourage help-seeking behaviors dalam format verbal",
		}
	case "Reading":
		response.MSLQCorrelation = []dtos.MSLQCorrelationDetail{
			{Component: "Elaboration", Correlation: 0.80, Explanation: "Very Strong - highest correlation, excel at written elaboration"},
			{Component: "Metacognitive Self-Reg", Correlation: 0.75, Explanation: "Very Strong - strong self-monitoring through writing"},
			{Component: "Organization", Correlation: 0.72, Explanation: "Strong - written organization and note-taking skills"},
			{Component: "Critical Thinking", Correlation: 0.68, Explanation: "Strong - written analysis and reflection"},
			{Component: "Time Management", Correlation: 0.65, Explanation: "Strong - written planning and scheduling"},
			{Component: "Help Seeking", Correlation: 0.35, Explanation: "Low - prefer independent written resources"},
		}
		response.AMSCorrelation = []dtos.AMSCorrelationDetail{
			{Component: "Intrinsic - To Know", Correlation: 0.78, Explanation: "Very Strong - highest motivation for knowledge acquisition"},
			{Component: "Intrinsic - Accomplish", Correlation: 0.70, Explanation: "Strong - achievement through written work and documentation"},
			{Component: "External - Identified", Correlation: 0.48, Explanation: "Moderate - recognition through written achievements"},
			{Component: "Intrinsic - Stimulation", Correlation: 0.42, Explanation: "Moderate - stimulation from reading and writing"},
			{Component: "External - Introjected", Correlation: 0.25, Explanation: "Low - less influenced by external pressure"},
			{Component: "Amotivation", Correlation: -0.55, Explanation: "Strong Negative - reading/writing maintains motivation"},
		}
		response.Recommendations = []string{
			"Maximize written elaboration dan reflection activities",
			"Develop strong metacognitive strategies through journaling",
			"Provide extensive reading materials dan text-based resources",
			"Encourage independent learning dan self-regulation",
		}
	case "Kinesthetic":
		response.MSLQCorrelation = []dtos.MSLQCorrelationDetail{
			{Component: "Effort Regulation", Correlation: 0.72, Explanation: "Strong - high persistence in hands-on tasks"},
			{Component: "Critical Thinking", Correlation: 0.62, Explanation: "Strong - learning through experimentation and doing"},
			{Component: "Help Seeking", Correlation: 0.58, Explanation: "Strong - seek practical demonstration and guidance"},
			{Component: "Elaboration", Correlation: 0.48, Explanation: "Moderate - hands-on elaboration and exploration"},
			{Component: "Organization", Correlation: 0.35, Explanation: "Low - less preference for traditional organization"},
			{Component: "Metacognitive Self-Reg", Correlation: 0.40, Explanation: "Moderate - physical feedback for self-monitoring"},
		}
		response.AMSCorrelation = []dtos.AMSCorrelationDetail{
			{Component: "Intrinsic - Stimulation", Correlation: 0.75, Explanation: "Very Strong - highest stimulation from physical activity"},
			{Component: "Intrinsic - Accomplish", Correlation: 0.68, Explanation: "Strong - achievement through tangible, practical results"},
			{Component: "External - Introjected", Correlation: 0.52, Explanation: "Moderate - need for active validation and feedback"},
			{Component: "Intrinsic - To Know", Correlation: 0.45, Explanation: "Moderate - learning through hands-on experience"},
			{Component: "External - Identified", Correlation: 0.48, Explanation: "Moderate - recognition of practical skills"},
			{Component: "Amotivation", Correlation: -0.42, Explanation: "Negative - hands-on activity reduces amotivation"},
		}
		response.Recommendations = []string{
			"Implement hands-on learning activities dan experiments",
			"Provide practical application opportunities",
			"Use physical manipulation dan simulation tools",
			"Encourage learning through trial and error",
		}
	default:
		response.MSLQCorrelation = []dtos.MSLQCorrelationDetail{}
		response.AMSCorrelation = []dtos.AMSCorrelationDetail{}
		response.Recommendations = []string{"No specific recommendations available for this VARK style."}
	}

	return response, nil
}
