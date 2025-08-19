package dtos

// DashboardDTO holds all data required for the dashboard.
type DashboardDTO struct {
	User         UserDTO                   `json:"user"`
	AdminStats   *AdminDashboardStatsDTO   `json:"admin_stats"`
	StudentStats *StudentDashboardStatsDTO `json:"student_stats"`
	Teacherstats *TeacherDashboardStatsDTO `json:"teacher_stats"`
}

// AdminDashboardStatsDTO holds counts for the admin dashboard.
type AdminDashboardStatsDTO struct {
	TotalUsers              int64 `json:"total_users"`
	TotalTeachers           int64 `json:"total_teachers"`
	TotalStudents           int64 `json:"total_students"`
	TotalCourses            int64 `json:"total_courses"`
	TotalPointsTransactions int64 `json:"total_points_transaction"`
	TotalProducts           int64 `json:"total_products"`
	TotalMissions           int64 `json:"total_missions"`
	TotalBadges             int64 `json:"total_badges"`
}

// StudentDashboardStatsDTO holds aggregated statistics for a student's dashboard.
type StudentDashboardStatsDTO struct {
	TotalPoints          int64                       `json:"total_points"`
	CompletedAssignments int64                       `json:"completed_assignments"`
	MSLQScore            float64                     `json:"mslq_score"`
	AMSScore             float64                     `json:"ams_score"`
	LearningStyle        StudentLearningStyle        `json:"learning_style"`
	WeeklyEvaluations    []WeeklyEvaluationDetailDTO `json:"weekly_evaluations"`
}

// TeacherDashboardStatsDTO holds counts for the teacher dashboard.
type TeacherDashboardStatsDTO struct {
	TotalStudents    int64 `json:"total_students"`
	TotalCourses     int64 `json:"total_courses"`
	TotalAssignments int64 `json:"total_assignments"`
	TotalQuizzes     int64 `json:"total_quizzes"`
}
