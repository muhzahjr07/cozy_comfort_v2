using CozyComfort.API.Models.Domain;
using CozyComfort.API.Models.DTOs;

namespace CozyComfort.API.Repositories
{
    public interface IOrderRepository : IRepository<Order>
    {
        Task<Order?> GetOrderWithDetailsAsync(int orderId);
        Task<Order?> GetOrderWithQuotationsAsync(int orderId);
        Task<Order?> GetOrderByReferenceAsync(string orderReference);
        Task<IEnumerable<Order>> GetOrdersByCustomerAsync(int customerId, int page, int pageSize);
        Task<IEnumerable<Order>> GetOrdersByStatusAsync(string status);
        Task<PaginatedResponse<Order>> GetOrdersPagedAsync(int page, int pageSize, string? status = null);
        Task<decimal> GetTotalRevenueAsync();
        Task<decimal> GetMonthlyRevenueAsync(DateTime month);
        Task<IEnumerable<OrderStatusCount>> GetOrderCountsByStatusAsync();
        Task<DashboardStatsResponse> GetDashboardStatsAsync();
    }
}
