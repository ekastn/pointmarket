package services

import (
	"context"
	"errors"
	"sync"
	"sync/atomic"
	"time"
)

type SchedulerStatusDTO struct {
	Running    bool      `json:"running"`
	JobRunning bool      `json:"job_running"`
	NextRun    time.Time `json:"next_run"`
}

// SchedulerManager provides an in-process weekly scheduler that can be started/stopped via API.
type SchedulerManager struct {
	mu         sync.Mutex
	running    bool
	loopCancel context.CancelFunc
	nextRun    time.Time

	jobRunning int32 // 0 false, 1 true (atomic)

	svc *WeeklyEvaluationService
}

func NewSchedulerManager(svc *WeeklyEvaluationService) *SchedulerManager {
	return &SchedulerManager{svc: svc}
}

func (m *SchedulerManager) Start() error {
	m.mu.Lock()
	defer m.mu.Unlock()
	if m.running {
		return nil
	}
	ctx, cancel := context.WithCancel(context.Background())
	m.loopCancel = cancel
	m.running = true

	// Compute first next run immediately
	m.nextRun = nextMondayMidnight(time.Now())

	// Kick an immediate one-off run on start
	go func() { _ = m.RunNow(context.Background()) }()

	// Start loop for subsequent scheduled runs
	go m.loop(ctx)
	return nil
}

func (m *SchedulerManager) Stop() error {
	m.mu.Lock()
	defer m.mu.Unlock()
	if !m.running {
		return nil
	}
	m.loopCancel()
	m.loopCancel = nil
	m.running = false
	return nil
}

func (m *SchedulerManager) RunNow(ctx context.Context) error {
	if !atomic.CompareAndSwapInt32(&m.jobRunning, 0, 1) {
		return errors.New("scheduler job already running")
	}
	defer atomic.StoreInt32(&m.jobRunning, 0)
	return m.svc.GenerateAndOverdueWeeklyEvaluations(ctx)
}

func (m *SchedulerManager) Status() SchedulerStatusDTO {
	m.mu.Lock()
	defer m.mu.Unlock()
	return SchedulerStatusDTO{
		Running:    m.running,
		JobRunning: atomic.LoadInt32(&m.jobRunning) == 1,
		NextRun:    m.nextRun,
	}
}

func (m *SchedulerManager) loop(ctx context.Context) {
	for {
		m.mu.Lock()
		next := nextMondayMidnight(time.Now())
		m.nextRun = next
		m.mu.Unlock()

		select {
		case <-ctx.Done():
			return
		case <-time.After(time.Until(next)):
			// fallthrough
		}

		// Execute job; ignore error here (handler surfaces errors for RunNow endpoint)
		_ = m.RunNow(context.Background())
	}
}

// nextMondayMidnight calculates the next Monday at 00:00:00 in the same location as t.
func nextMondayMidnight(t time.Time) time.Time {
	todayMidnight := time.Date(t.Year(), t.Month(), t.Day(), 0, 0, 0, 0, t.Location())
	daysToAdd := (int(time.Monday) - int(todayMidnight.Weekday()) + 7) % 7
	if daysToAdd == 0 && t.After(todayMidnight) {
		daysToAdd = 7
	}
	return todayMidnight.AddDate(0, 0, daysToAdd)
}
