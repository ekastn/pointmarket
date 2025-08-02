package utils

import "time"

// GetCurrentWeekAndYear returns the current ISO year and week number.
func GetCurrentWeekAndYear() (int, int) {
	year, week := time.Now().ISOWeek()
	return year, week
}
