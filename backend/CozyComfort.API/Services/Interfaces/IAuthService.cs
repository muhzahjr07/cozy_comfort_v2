using CozyComfort.API.Models.DTOs;

namespace CozyComfort.API.Services.Interfaces
{
    public interface IAuthService
    {
        Task<AuthResponse> LoginAsync(LoginRequest request);
        Task<AuthResponse> RegisterAsync(RegisterRequest request);
        Task<UserProfileResponse?> GetUserProfileAsync(int userId);
        Task<bool> ChangePasswordAsync(int userId, ChangePasswordRequest request);
        Task<bool> ValidateTokenAsync(string token);
        Task<int?> GetUserIdFromTokenAsync(string token);
        string GenerateJwtToken(int userId, string email, string role);
    }
}
