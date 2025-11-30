package handler

import (
	"net/http"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/response"
	"pointmarket/backend/internal/services"
	"strconv"

	"github.com/gin-gonic/gin"
)

// MissionHandler handles mission-related HTTP requests
type MissionHandler struct {
	missionService services.MissionService
}

// NewMissionHandler creates a new MissionHandler
func NewMissionHandler(missionService services.MissionService) *MissionHandler {
	return &MissionHandler{missionService: missionService}
}

// CreateMission handles creating a new mission (Admin-only)
func (h *MissionHandler) CreateMission(c *gin.Context) {
	var req dtos.CreateMissionRequestDTO
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid request body: "+err.Error())
		return
	}

	mission, err := h.missionService.CreateMission(c.Request.Context(), req)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to create mission: "+err.Error())
		return
	}

	response.Success(c, http.StatusCreated, "Mission created successfully", mission)
}

// GetMissionByID handles fetching a single mission by ID
func (h *MissionHandler) GetMissionByID(c *gin.Context) {
	idParam := c.Param("id")
	id, err := strconv.ParseInt(idParam, 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid mission ID")
		return
	}

	mission, err := h.missionService.GetMissionByID(c.Request.Context(), id)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to retrieve mission: "+err.Error())
		return
	}

	if mission.ID == 0 { // Assuming ID will be 0 if not found
		response.Error(c, http.StatusNotFound, "Mission not found")
		return
	}

	response.Success(c, http.StatusOK, "Mission retrieved successfully", mission)
}

// GetMissions handles fetching a list of all missions (Auth required)
func (h *MissionHandler) GetMissions(c *gin.Context) {
	// Check for user_id query parameter for user-specific listing
	userIDStr := c.Query("user_id")
	if userIDStr != "" {
		userID, err := strconv.ParseInt(userIDStr, 10, 64)
		if err != nil {
			response.Error(c, http.StatusBadRequest, "Invalid user ID parameter")
			return
		}

		// Authorization: allow admin for any user, or allow the authenticated user to fetch their own missions
		roleVal, exists := c.Get("role")
		if !exists {
			response.Error(c, http.StatusUnauthorized, "Unauthorized")
			return
		}
		role := roleVal.(string)
		if role != "admin" {
			authUserIDVal, ok := c.Get("userID")
			if !ok {
				response.Error(c, http.StatusUnauthorized, "Unauthorized")
				return
			}
			authUserID := authUserIDVal.(int64)
			if authUserID != userID {
				response.Error(c, http.StatusForbidden, "Forbidden: Cannot query missions for other users")
				return
			}
		}

		userMissions, err := h.missionService.GetUserMissionsByUserID(c.Request.Context(), userID)
		if err != nil {
			response.Error(c, http.StatusInternalServerError, "Failed to retrieve user missions: "+err.Error())
			return
		}
		response.Success(c, http.StatusOK, "User missions retrieved successfully", dtos.ListUserMissionsResponseDTO{
			UserMissions: userMissions,
			Total:        len(userMissions),
		})
		return
	}

	// If no user_id param, return all missions
	missions, err := h.missionService.GetMissions(c.Request.Context())
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to retrieve missions: "+err.Error())
		return
	}

	response.Success(c, http.StatusOK, "Missions retrieved successfully", dtos.ListMissionsResponseDTO{
		Missions: missions,
		Total:    len(missions),
	})
}

// UpdateMission handles updating an existing mission (Admin-only)
func (h *MissionHandler) UpdateMission(c *gin.Context) {
	idParam := c.Param("id")
	id, err := strconv.ParseInt(idParam, 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid mission ID")
		return
	}

	var req dtos.UpdateMissionRequestDTO
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid request body: "+err.Error())
		return
	}

	updatedMission, err := h.missionService.UpdateMission(c.Request.Context(), id, req)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to update mission: "+err.Error())
		return
	}

	if updatedMission.ID == 0 { // Assuming ID will be 0 if not found
		response.Error(c, http.StatusNotFound, "Mission not found")
		return
	}

	response.Success(c, http.StatusOK, "Mission updated successfully", updatedMission)
}

// DeleteMission handles deleting a mission by its ID (Admin-only)
func (h *MissionHandler) DeleteMission(c *gin.Context) {
	idParam := c.Param("id")
	id, err := strconv.ParseInt(idParam, 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid mission ID")
		return
	}

	err = h.missionService.DeleteMission(c.Request.Context(), id)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to delete mission: "+err.Error())
		return
	}

	response.Success(c, http.StatusOK, "Mission deleted successfully", nil)
}

// StartMission handles starting a specific mission for a user (Auth required)
func (h *MissionHandler) StartMission(c *gin.Context) {
	missionIDParam := c.Param("id")
	missionID, err := strconv.ParseInt(missionIDParam, 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid mission ID")
		return
	}

	var req dtos.CreateUserMissionRequestDTO
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid request body: "+err.Error())
		return
	}

	// If UserID is not provided in the request body, use the authenticated user's ID
	if req.UserID == 0 {
		authUserID, exists := c.Get("userID")
		if !exists {
			response.Error(c, http.StatusUnauthorized, "User ID not found in context")
			return
		}
		req.UserID = authUserID.(int64)
	} else {
		// If UserID is provided, ensure only admin can specify it
		userRole, exists := c.Get("userRole")
		if !exists || userRole.(string) != "admin" {
			response.Error(c, http.StatusForbidden, "Forbidden: Only admins can start missions for other users")
			return
		}
	}

	// Ensure missionID from path matches the one in the request body if provided
	if req.MissionID != 0 && req.MissionID != missionID {
		response.Error(c, http.StatusBadRequest, "Mission ID in path and body do not match")
		return
	}
	req.MissionID = missionID // Use ID from path

	userMission, err := h.missionService.CreateUserMission(c.Request.Context(), req)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to start mission: "+err.Error())
		return
	}

	response.Success(c, http.StatusCreated, "Mission started successfully", userMission)
}

// UpdateUserMissionStatus handles updating the status of a user's mission (Auth required)
func (h *MissionHandler) UpdateUserMissionStatus(c *gin.Context) {
	userMissionIDParam := c.Param("id")
	userMissionID, err := strconv.ParseInt(userMissionIDParam, 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid user mission ID")
		return
	}

	var req dtos.UpdateUserMissionStatusRequestDTO
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid request body: "+err.Error())
		return
	}

	// Get the user mission to verify ownership if not admin
	// This requires a GetUserMissionByID query in service, which we don't have yet.
	// For now, assuming service handles authorization or it's admin.

	updatedUserMission, err := h.missionService.UpdateUserMissionStatus(c.Request.Context(), userMissionID, req)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to update user mission status: "+err.Error())
		return
	}

	response.Success(c, http.StatusOK, "User mission status updated successfully", updatedUserMission)
}

// EndMission handles ending/deleting a user's mission instance (Admin-only)
func (h *MissionHandler) EndMission(c *gin.Context) {
	// missionIDParam := c.Param("id") // This is the mission ID, not user_mission ID
	// missionID, err := strconv.ParseInt(missionIDParam, 10, 64)
	// if err != nil {
	// 	response.Error(c, http.StatusBadRequest, "Invalid mission ID")
	// 	return
	// }

	var req dtos.CreateUserMissionRequestDTO // Reusing DTO for UserID
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid request body: "+err.Error())
		return
	}

	// This requires a way to get user_mission ID from missionID and userID
	// For now, assuming service handles finding the correct user_mission to delete.
	// This will require a GetUserMissionByMissionIDAndUserID query.

	// For now, calling DeleteUserMission with a placeholder userMissionID
	// This needs to be fixed to find the actual user_mission ID
	response.Error(c, http.StatusNotImplemented, "Ending user mission by mission ID and user ID is not yet implemented")
	return

	// err = h.missionService.DeleteUserMission(c.Request.Context(), userMissionID) // Needs actual userMissionID
	// if err != nil {
	// 	response.Error(c, http.StatusInternalServerError, "Failed to end user mission: "+err.Error())
	// 	return
	// }

	// response.Success(c, http.StatusOK, "User mission ended successfully", nil)
}

// GetAllUserMissions handles fetching all user mission progress for admin view
func (h *MissionHandler) GetAllUserMissions(c *gin.Context) {
	page, _ := strconv.Atoi(c.DefaultQuery("page", "1"))
	limit, _ := strconv.Atoi(c.DefaultQuery("limit", "10"))
	search := c.Query("search")

	progress, total, err := h.missionService.GetAllUserMissions(c.Request.Context(), page, limit, search)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to retrieve user mission progress: "+err.Error())
		return
	}

	response.Paginated(c, http.StatusOK, "User mission progress retrieved successfully", progress, total, page, limit)
}
