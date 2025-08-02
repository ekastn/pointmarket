package utils

import "time"

// GetCurrentWeekAndYear returns the current ISO year and week number.
func GetCurrentWeekAndYear() (int, int) {
	year, week := time.Now().ISOWeek()
	return year, week
}

// GetEndOfCurrentWeek returns the time at the end of the current ISO week (Sunday 23:59:59).
func GetEndOfCurrentWeek() time.Time {
	now := time.Now()
	// Calculate the start of the current week (Monday)
	// time.Monday is 1, time.Sunday is 0. We want to go back to Monday.
	daysToMonday := int(now.Weekday() - time.Monday)
	if daysToMonday < 0 {
		daysToMonday += 7 // Adjust for Sunday (0) which would make daysToMonday negative
	}
	startOfWeek := now.AddDate(0, 0, -daysToMonday)

	// Calculate the end of the week (Sunday 23:59:59)
	endOfWeek := startOfWeek.AddDate(0, 0, 6).Add(time.Hour*23 + time.Minute*59 + time.Second*59)
	return endOfWeek
}
