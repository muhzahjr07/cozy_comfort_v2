using Microsoft.AspNetCore.Mvc;
using Microsoft.AspNetCore.Authorization;
using System.Security.Claims;
using CozyComfort.API.Models.DTOs;
using CozyComfort.API.Services.Interfaces;

namespace CozyComfort.API.Controllers
{
    [ApiController]
    [Route("api/[controller]")]
    public class AuthController : ControllerBase
    {
        private readonly IAuthService _authService;
        private readonly ILogger<AuthController> _logger;

        public AuthController(IAuthService authService, ILogger<AuthController> logger)
        {
            _authService = authService;
            _logger = logger;
        }

        /// <summary>
        /// User login
        /// </summary>
        [HttpPost("login")]
        public async Task<ActionResult<ApiResponse<AuthResponse>>> Login([FromBody] LoginRequest request)
        {
            try
            {
                var result = await _authService.LoginAsync(request);
                
                if (result.IsSuccess)
                {
                    return Ok(ApiResponse<AuthResponse>.SuccessResult(result, "Login successful"));
                }
                
                return BadRequest(ApiResponse<AuthResponse>.ErrorResult(result.Message));
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error during login");
                return BadRequest(ApiResponse<AuthResponse>.ErrorResult("Login failed"));
            }
        }

        /// <summary>
        /// User registration
        /// </summary>
        [HttpPost("register")]
        public async Task<ActionResult<ApiResponse<AuthResponse>>> Register([FromBody] RegisterRequest request)
        {
            try
            {
                var result = await _authService.RegisterAsync(request);
                
                if (result.IsSuccess)
                {
                    return Ok(ApiResponse<AuthResponse>.SuccessResult(result, "Registration successful"));
                }
                
                return BadRequest(ApiResponse<AuthResponse>.ErrorResult(result.Message));
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error during registration");
                return BadRequest(ApiResponse<AuthResponse>.ErrorResult("Registration failed"));
            }
        }

        /// <summary>
        /// Get current user profile (requires authentication)
        /// </summary>
        [HttpGet("profile")]
        [Authorize]
        public async Task<ActionResult<ApiResponse<UserProfileResponse>>> GetProfile()
        {
            try
            {
                var userIdClaim = User.FindFirst(ClaimTypes.NameIdentifier)?.Value;
                if (userIdClaim == null || !int.TryParse(userIdClaim, out int userId))
                {
                    return Unauthorized(ApiResponse<UserProfileResponse>.ErrorResult("Invalid token"));
                }

                var profile = await _authService.GetUserProfileAsync(userId);
                if (profile == null)
                {
                    return NotFound(ApiResponse<UserProfileResponse>.ErrorResult("User not found"));
                }

                return Ok(ApiResponse<UserProfileResponse>.SuccessResult(profile));
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error getting user profile");
                return BadRequest(ApiResponse<UserProfileResponse>.ErrorResult("Failed to get profile"));
            }
        }

        /// <summary>
        /// Change user password (requires authentication)
        /// </summary>
        [HttpPost("change-password")]
        [Authorize]
        public async Task<ActionResult<ApiResponse<bool>>> ChangePassword([FromBody] ChangePasswordRequest request)
        {
            try
            {
                var userIdClaim = User.FindFirst(ClaimTypes.NameIdentifier)?.Value;
                if (userIdClaim == null || !int.TryParse(userIdClaim, out int userId))
                {
                    return Unauthorized(ApiResponse<bool>.ErrorResult("Invalid token"));
                }

                var success = await _authService.ChangePasswordAsync(userId, request);
                if (success)
                {
                    return Ok(ApiResponse<bool>.SuccessResult(true, "Password changed successfully"));
                }
                
                return BadRequest(ApiResponse<bool>.ErrorResult("Failed to change password. Please check your current password."));
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error changing password");
                return BadRequest(ApiResponse<bool>.ErrorResult("Failed to change password"));
            }
        }

        /// <summary>
        /// Validate if token is still valid
        /// </summary>
        [HttpPost("validate-token")]
        public async Task<ActionResult<ApiResponse<bool>>> ValidateToken([FromBody] string token)
        {
            try
            {
                var isValid = await _authService.ValidateTokenAsync(token);
                return Ok(ApiResponse<bool>.SuccessResult(isValid, isValid ? "Token is valid" : "Token is invalid"));
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error validating token");
                return BadRequest(ApiResponse<bool>.ErrorResult("Token validation failed"));
            }
        }

        /// <summary>
        /// Logout (client-side token removal, no server action needed for JWT)
        /// </summary>
        [HttpPost("logout")]
        [Authorize]
        public ActionResult<ApiResponse<bool>> Logout()
        {
            // For JWT tokens, logout is handled client-side by removing the token
            // Server-side logout would require token blacklisting which is complex
            return Ok(ApiResponse<bool>.SuccessResult(true, "Logout successful. Please remove the token from client storage."));
        }
    }
}
