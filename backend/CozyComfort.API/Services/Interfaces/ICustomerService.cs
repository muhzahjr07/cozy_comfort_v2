using CozyComfort.API.Models.DTOs;

namespace CozyComfort.API.Services.Interfaces
{
    public interface ICustomerService
    {
        Task<CustomerResponse> GetCustomerByIdAsync(int customerId);
        Task<CustomerResponse> GetCustomerByUserIdAsync(int userId);
        Task<CustomerResponse> UpdateCustomerAsync(int customerId, UpdateCustomerRequest request);
        Task<PaginatedResponse<CustomerResponse>> GetAllCustomersAsync(int page, int pageSize);
    }
}
