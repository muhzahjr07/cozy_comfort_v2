using CozyComfort.API.Models.DTOs;

namespace CozyComfort.API.Services.Interfaces
{
    public interface IOrderService
    {
        Task<OrderResponse> CreateOrderAsync(CreateOrderRequest request);
        Task<OrderResponse> GetOrderByIdAsync(int orderId);
        Task<PaginatedResponse<OrderResponse>> GetCustomerOrdersAsync(
            int customerId, int pageNumber, int pageSize);
        Task<bool> UpdateOrderStatusAsync(int orderId, string status);
        Task<bool> CancelOrderAsync(int orderId);
        Task<OrderResponse> GetOrderByReferenceAsync(string reference);
        Task<PaginatedResponse<OrderSummaryResponse>> GetAllOrdersAsync(int page, int pageSize, string? status = null);
        Task<DashboardStatsResponse> GetDashboardStatsAsync();
    }
}
