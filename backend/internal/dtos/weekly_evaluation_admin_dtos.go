package dtos

import "time"

// SchedulerNextRunDTO is returned by scheduler start endpoint.
type SchedulerNextRunDTO struct {
	NextRun time.Time `json:"next_run"`
}
