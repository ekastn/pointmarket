package dtos

// VARKAveragesDTO represents the VARK averages breakdown.
type VARKAveragesDTO struct {
	Visual      float64 `json:"visual"`
	Auditory    float64 `json:"auditory"`
	Reading     float64 `json:"reading"`
	Kinesthetic float64 `json:"kinesthetic"`
}

// CourseInsightsDTO represents per-course psychometric aggregates for teachers.
type CourseInsightsDTO struct {
	CourseID    int64           `json:"course_id"`
	CourseTitle string          `json:"course_title"`
	AvgMSLQ     float64         `json:"avg_mslq"`
	AvgAMS      float64         `json:"avg_ams"`
	VarkAvg     VARKAveragesDTO `json:"vark_avg"`
}
