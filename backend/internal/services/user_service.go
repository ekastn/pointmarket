package services

import (
	"bytes"
	"context"
	"database/sql"
	"fmt"
	"io"
	imgcompress "pointmarket/backend/internal/compress"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/store"
	"pointmarket/backend/internal/store/gen"
	"pointmarket/backend/internal/utils"
)

type UserService struct {
	q gen.Querier
	// Optional avatar storage
	imgStore       store.ImageStore
	storageBaseURL string
}

func NewUserService(q gen.Querier) *UserService {
	return &UserService{q: q}
}

// ConfigureAvatarStore injects an image store and base URL for avatar handling.
// This avoids import cycles by accepting a minimal, structurally-compatible interface.
func (s *UserService) ConfigureAvatarStore(imgStore store.ImageStore, publicBaseURL string) {
	s.imgStore = imgStore
	s.storageBaseURL = publicBaseURL
}

func (s *UserService) GetUserByID(ctx context.Context, id int64) (gen.User, error) {
	return s.q.GetUserByID(ctx, id)
}

var ErrUserAlreadyExists = fmt.Errorf("user with this username or email already exists")

// CreateUser creates a new user
func (s *UserService) CreateUser(ctx context.Context, req dtos.CreateUserRequest) error {
	// Check if user with this email already exists
	_, err := s.q.GetUserByEmail(ctx, req.Email)
	if err == nil {
		return ErrUserAlreadyExists
	}
	if err != sql.ErrNoRows {
		return fmt.Errorf("failed to check existing email: %w", err)
	}

	// Check if user with this username already exists
	_, err = s.q.GetUserByUsername(ctx, req.Username)
	if err == nil {
		return ErrUserAlreadyExists
	}
	if err != sql.ErrNoRows {
		return fmt.Errorf("failed to check existing username: %w", err)
	}

	hashedPassword, err := utils.HashPassword(req.Password)
	if err != nil {
		return fmt.Errorf("failed to hash password: %w", err)
	}

	data := gen.CreateUserParams{
		Email:       req.Email,
		Username:    req.Username,
		Password:    hashedPassword,
		DisplayName: req.Name,
		Role:        gen.UsersRole(req.Role),
	}

	_, err = s.q.CreateUser(ctx, data)
	if err != nil {
		return err
	}

	return nil
}

// UpdateUserProfile updates a user's profile information
func (s *UserService) UpdateUserProfile(ctx context.Context, userID int64, req dtos.UpdateProfileRequest) error {
	// Ensure user exists and get current values
	user, err := s.q.GetUserByID(ctx, userID)
	if err != nil {
		return err
	}
	if user.ID == 0 {
		return sql.ErrNoRows
	}

	// If Name/Email provided, update users row (preserve username and role)
	if req.Name != nil || req.Email != nil {
		upd := gen.UpdateUserParams{
			ID:          userID,
			Username:    user.Username,
			DisplayName: user.DisplayName,
			Email:       user.Email,
			Role:        user.Role,
		}
		if req.Name != nil {
			upd.DisplayName = *req.Name
		}
		if req.Email != nil {
			upd.Email = *req.Email
		}
		if err := s.q.UpdateUser(ctx, upd); err != nil {
			return err
		}
	}

	// If Avatar/Bio provided, upsert into user_profiles
	if req.AvatarURL != nil || req.Bio != nil {
		// Get existing profile to preserve values when field is omitted
		prof, err := s.q.GetUserProfileByID(ctx, userID)
		if err != nil && err != sql.ErrNoRows {
			return err
		}
		avatar := ""
		bio := ""
		if prof.AvatarUrl.Valid {
			avatar = prof.AvatarUrl.String
		}
		if prof.Bio.Valid {
			bio = prof.Bio.String
		}
		if req.AvatarURL != nil {
			avatar = *req.AvatarURL
		}
		if req.Bio != nil {
			bio = *req.Bio
		}
		if err := s.q.UpsertUserProfile(ctx, gen.UpsertUserProfileParams{
			UserID:    userID,
			AvatarUrl: sql.NullString{String: avatar, Valid: avatar != ""},
			Bio:       sql.NullString{String: bio, Valid: bio != ""},
		}); err != nil {
			return err
		}
	}

	return nil
}

// GetUserProfile returns merged user and user_profiles data for current user
func (s *UserService) GetUserProfile(ctx context.Context, userID int64) (dtos.ProfileResponse, error) {
	row, err := s.q.GetUserProfileByID(ctx, userID)
	if err != nil {
		return dtos.ProfileResponse{}, err
	}
	var avatarPtr *string
	var bioPtr *string
	if row.AvatarUrl.Valid {
		v := row.AvatarUrl.String
		avatarPtr = &v
	}
	if row.Bio.Valid {
		v := row.Bio.String
		bioPtr = &v
	}
	return dtos.ProfileResponse{
		ID:        int(row.ID),
		Username:  row.Username,
		Name:      row.DisplayName,
		Email:     row.Email,
		Role:      string(row.Role),
		Avatar:    avatarPtr,
		Bio:       bioPtr,
		CreatedAt: row.CreatedAt,
		UpdatedAt: row.UpdatedAt,
	}, nil
}

// ChangePassword validates and updates the user's password
func (s *UserService) ChangePassword(ctx context.Context, userID int64, req dtos.ChangePasswordRequest) error {
	if req.NewPassword != req.ConfirmPassword {
		return fmt.Errorf("passwords do not match")
	}

	// Basic password policy: length >= 8, at least one letter and one digit
	if len(req.NewPassword) < 8 {
		return fmt.Errorf("password must be at least 8 characters")
	}
	hasLetter := false
	hasDigit := false
	for _, ch := range req.NewPassword {
		if (ch >= 'a' && ch <= 'z') || (ch >= 'A' && ch <= 'Z') {
			hasLetter = true
		}
		if ch >= '0' && ch <= '9' {
			hasDigit = true
		}
	}
	if !hasLetter || !hasDigit {
		return fmt.Errorf("password must contain letters and numbers")
	}

	// Fetch user to verify current password
	user, err := s.q.GetUserByID(ctx, userID)
	if err != nil {
		return err
	}
	if user.ID == 0 {
		return sql.ErrNoRows
	}

	// Verify current password
	if err := utils.CheckPassword(req.CurrentPassword, user.Password); err != nil {
		return fmt.Errorf("invalid current password")
	}

	// Reject if new password equals current
	if err := utils.CheckPassword(req.NewPassword, user.Password); err == nil {
		return fmt.Errorf("new password must be different from current password")
	}

	// Hash and update
	hashed, err := utils.HashPassword(req.NewPassword)
	if err != nil {
		return err
	}
	if err := s.q.UpdateUserPassword(ctx, gen.UpdateUserPasswordParams{Password: hashed, ID: userID}); err != nil {
		return err
	}
	return nil
}

// SearchUsers retrieves users based on search term and role with pagination
func (s *UserService) SearchUsers(ctx context.Context, search, role string, page, limit int) ([]gen.User, int64, error) {
	if page <= 0 {
		page = 1
	}
	if limit <= 0 {
		limit = 10
	}
	offset := (page - 1) * limit

	// Parameters for both searching and counting
	searchParams := gen.SearchUsersParams{
		Search: search,
		Role:   gen.UsersRole(role),
		Limit:  int32(limit),
		Offset: int32(offset),
	}

	countParams := gen.CountSearchedUsersParams{
		Search: search,
		Role:   gen.UsersRole(role),
	}

	// Get total count of users matching the filter
	totalUsers, err := s.q.CountSearchedUsers(ctx, countParams)
	if err != nil {
		return nil, 0, fmt.Errorf("failed to count users: %w", err)
	}

	// Get the paginated list of users
	users, err := s.q.SearchUsers(ctx, searchParams)
	if err != nil {
		return nil, 0, fmt.Errorf("failed to search users: %w", err)
	}

	return users, totalUsers, nil
}

// GetRoles retrieves a list of available user roles
func (s *UserService) GetRoles() []string {
	return []string{"siswa", "guru", "admin"}
}

// GetAllUsers retrieves all users
func (s *UserService) GetAllUsers(ctx context.Context) ([]gen.User, error) {
	return s.q.GetUsers(ctx)
}

// UpdateUserRole updates a user's role
func (s *UserService) UpdateUserRole(ctx context.Context, userID int64, role string) error {
	data := gen.UpdateUserRoleParams{
		ID:   userID,
		Role: gen.UsersRole(role),
	}
	return s.q.UpdateUserRole(ctx, data)
}

// UpdateUserAvatarURL updates only the avatar URL in user_profiles, preserving other fields.
func (s *UserService) UpdateUserAvatarURL(ctx context.Context, userID int64, avatarURL string) error {
	// Get existing profile to preserve bio
	prof, err := s.q.GetUserProfileByID(ctx, userID)
	if err != nil && err != sql.ErrNoRows {
		return err
	}
	// Preserve existing bio if present
	bio := sql.NullString{}
	if prof.Bio.Valid {
		bio = prof.Bio
	}
	// Set avatar URL
	av := sql.NullString{String: avatarURL, Valid: avatarURL != ""}
	return s.q.UpsertUserProfile(ctx, gen.UpsertUserProfileParams{
		UserID:    userID,
		AvatarUrl: av,
		Bio:       bio,
	})
}

// UploadUserAvatar streams an avatar image to the configured image store, updates the
// user's avatar URL, and best-effort deletes the previous object if it belongs to our bucket.
func (s *UserService) UploadUserAvatar(ctx context.Context, userID int64, r io.Reader) (string, error) {
	if s.imgStore == nil {
		return "", fmt.Errorf("image store not configured")
	}

	prev, _ := s.q.GetUserProfileByID(ctx, userID)
	// Compress to a normalized square JPEG before uploading to the image store.
	var buf bytes.Buffer
	opts := imgcompress.DefaultOptions() // MaxEdge=360, MaxBytes=300KB, quality 10..95
	if err := imgcompress.CompressSquareJPEG(r, &buf, opts); err != nil {
		return "", err
	}

	publicURL, objectPath, err := s.imgStore.PutUserAvatar(ctx, userID, bytes.NewReader(buf.Bytes()))
	if err != nil {
		return "", err
	}

	if err := s.UpdateUserAvatarURL(ctx, userID, publicURL); err != nil {
		return "", err
	}

	if s.storageBaseURL != "" && prev.AvatarUrl.Valid {
		prefix := s.storageBaseURL
		if prefix[len(prefix)-1] != '/' {
			prefix += "/"
		}
		if len(prev.AvatarUrl.String) > len(prefix) && prev.AvatarUrl.String[:len(prefix)] == prefix {
			oldPath := prev.AvatarUrl.String[len(prefix):]
			_ = s.imgStore.Delete(ctx, oldPath)
		}
	}
	_ = objectPath // reserved for future logging
	return publicURL, nil
}

// DeleteUser deletes a user (sets role to 'inactive')
func (s *UserService) DeleteUser(ctx context.Context, userID int64) error {
	return s.q.DeleteUser(ctx, userID)
}

// UpdateUser updates a user's information
func (s *UserService) UpdateUser(ctx context.Context, userID int64, req dtos.UpdateUserRequest) error {
	data := gen.UpdateUserParams{
		ID:          userID,
		Username:    req.Username,
		DisplayName: req.Name,
		Email:       req.Email,
		Role:        gen.UsersRole(req.Role),
	}
	return s.q.UpdateUser(ctx, data)
}

// GetAllActiveStudents retrieves all active students (role 'siswa')
func (s *UserService) GetActiveStudents(ctx context.Context) ([]gen.GetActiveStudentsRow, error) {
	return s.q.GetActiveStudents(ctx)
}

// UpdateUserLearningStyle insert new record to user_learning_style table
func (s *UserService) UpdateUserLearningStyle(
	ctx context.Context,
	userID int64,
	prefType string,
	label string,
	score dtos.VARKScores,
) error {
	return s.q.CreateUserLearningStyle(ctx, gen.CreateUserLearningStyleParams{
		UserID:           userID,
		Type:             gen.StudentLearningStylesType(prefType),
		Label:            label,
		ScoreVisual:      &score.Visual,
		ScoreAuditory:    &score.Auditory,
		ScoreReading:     &score.Reading,
		ScoreKinesthetic: &score.Kinesthetic,
	})
}

// GetLatestUserLearningStyle returns the latest learning style data for a user
func (s *UserService) GetLatestUserLearningStyle(ctx context.Context, userID int64) (gen.StudentLearningStyle, error) {
	return s.q.GetLatestUserLearningStyle(ctx, userID)
}

// GetUserDetails retrieves detailed information about a user for the admin panel.
func (s *UserService) GetUserDetails(ctx context.Context, userID int64) (*dtos.UserDetailsDTO, error) {
	user, err := s.q.GetUserByID(ctx, userID)
	if err != nil {
		return nil, err
	}

	profile, err := s.q.GetUserProfileByID(ctx, userID)
	if err != nil && err != sql.ErrNoRows {
		return nil, err
	}

	stats, err := s.q.GetUserStats(ctx, userID)
	if err != nil && err != sql.ErrNoRows {
		return nil, err
	}

	var avatar *string
	if profile.AvatarUrl.Valid {
		avatar = &profile.AvatarUrl.String
	}

	var bio *string
	if profile.Bio.Valid {
		bio = &profile.Bio.String
	}

	details := &dtos.UserDetailsDTO{
		ID:        int(user.ID),
		Username:  user.Username,
		Name:      user.DisplayName,
		Email:     user.Email,
		Role:      string(user.Role),
		Avatar:    avatar,
		Bio:       bio,
		CreatedAt: user.CreatedAt,
		UpdatedAt: user.UpdatedAt,
		Points: dtos.PointsStats{
			TotalPoints: int(stats.TotalPoints),
		},
	}

	return details, nil
}
