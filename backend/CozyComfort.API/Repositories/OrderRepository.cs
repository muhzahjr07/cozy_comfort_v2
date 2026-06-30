using Microsoft.EntityFrameworkCore;
using CozyComfort.API.Data;
using CozyComfort.API.Models.Domain;
using CozyComfort.API.Models.DTOs;

namespace CozyComfort.API.Repositories
{
    public class OrderRepository : Repository<Order>, IOrderRepository
    {
        public OrderRepository(ApplicationDbContext context) : base(context)
        {
        }

        public async Task<Order?> GetOrderWithDetailsAsync(int orderId)
        {
            return await _dbSet
                .Include(o => o.Customer)
                .Include(o => o.OrderItems)
                    .ThenInclude(oi => oi.Product)
                .Include(o => o.Quotations)
                    .ThenInclude(q => q.Distributor)
                .Include(o => o.SelectedDistributor)
                .FirstOrDefaultAsync(o => o.OrderId == orderId);
        }

        public async Task<Order?> GetOrderWithQuotationsAsync(int orderId)
        {
            return await _dbSet
                .Include(o => o.Quotations)
                    .ThenInclude(q => q.QuotationItems)
                .Include(o => o.Quotations)
                    .ThenInclude(q => q.Distributor)
                .Include(o => o.OrderItems)
                .FirstOrDefaultAsync(o => o.OrderId == orderId);
        }

        public async Task<Order?> GetOrderByReferenceAsync(string orderReference)
        {
            return await _dbSet
                .Include(o => o.Customer)
                .Include(o => o.OrderItems)
                    .ThenInclude(oi => oi.Product)
                .Include(o => o.Quotations)
                    .ThenInclude(q => q.Distributor)
                .Include(o => o.SelectedDistributor)
                .FirstOrDefaultAsync(o => o.OrderReference == orderReference);
        }

        public async Task<IEnumerable<Order>> GetOrdersByCustomerAsync(int customerId, int page, int pageSize)
        {
            return await _dbSet
                .Where(o => o.CustomerId == customerId)
                .Include(o => o.OrderItems)
                    .ThenInclude(oi => oi.Product)
                .Include(o => o.SelectedDistributor)
                .OrderByDescending(o => o.OrderDate)
                .Skip((page - 1) * pageSize)
                .Take(pageSize)
                .ToListAsync();
        }

        public async Task<IEnumerable<Order>> GetOrdersByStatusAsync(string status)
        {
            return await _dbSet
                .Where(o => o.Status == status)
                .Include(o => o.Customer)
                .Include(o => o.OrderItems)
                .ToListAsync();
        }

        public async Task<PaginatedResponse<Order>> GetOrdersPagedAsync(int page, int pageSize, string? status = null)
        {
            var query = _dbSet.AsQueryable();
            
            if (!string.IsNullOrEmpty(status))
            {
                query = query.Where(o => o.Status == status);
            }

            var totalCount = await query.CountAsync();
            
            var orders = await query
                .Include(o => o.Customer)
                .Include(o => o.OrderItems)
                .Include(o => o.SelectedDistributor)
                .OrderByDescending(o => o.OrderDate)
                .Skip((page - 1) * pageSize)
                .Take(pageSize)
                .ToListAsync();

            return PaginatedResponse<Order>.Create(orders, page, pageSize, totalCount);
        }

        public async Task<decimal> GetTotalRevenueAsync()
        {
            return await _dbSet
                .Where(o => o.Status == "DELIVERED")
                .SumAsync(o => o.TotalAmount);
        }

        public async Task<decimal> GetMonthlyRevenueAsync(DateTime month)
        {
            var startDate = new DateTime(month.Year, month.Month, 1);
            var endDate = startDate.AddMonths(1);

            return await _dbSet
                .Where(o => o.Status == "DELIVERED" && o.OrderDate >= startDate && o.OrderDate < endDate)
                .SumAsync(o => o.TotalAmount);
        }

        public async Task<IEnumerable<OrderStatusCount>> GetOrderCountsByStatusAsync()
        {
            return await _dbSet
                .GroupBy(o => o.Status)
                .Select(g => new OrderStatusCount
                {
                    Status = g.Key,
                    Count = g.Count()
                })
                .ToListAsync();
        }

        public async Task<DashboardStatsResponse> GetDashboardStatsAsync()
        {
            var totalOrders = await _dbSet.CountAsync();
            var pendingOrders = await _dbSet.CountAsync(o => o.Status == "PENDING");
            var completedOrders = await _dbSet.CountAsync(o => o.Status == "DELIVERED");
            var totalCustomers = await _context.Customers.CountAsync();
            var totalRevenue = await GetTotalRevenueAsync();
            var monthlyRevenue = await GetMonthlyRevenueAsync(DateTime.UtcNow);
            
            var ordersByStatus = await GetOrderCountsByStatusAsync();

            // Daily orders for the last 7 days
            var sevenDaysAgo = DateTime.UtcNow.Date.AddDays(-7);
            var dailyOrders = await _dbSet
                .Where(o => o.OrderDate >= sevenDaysAgo)
                .GroupBy(o => o.OrderDate.Date)
                .Select(g => new DailyOrderCount
                {
                    Date = g.Key,
                    Count = g.Count()
                })
                .OrderBy(d => d.Date)
                .ToListAsync();

            // Distributor performance
            var distributors = await _context.Distributors
                .Select(d => new DistributorPerformanceSummary
                {
                    DistributorName = d.Name,
                    OrderCount = d.TotalOrders,
                    SuccessRate = d.SuccessRate,
                    AverageResponseTime = d.AverageResponseTimeMs
                })
                .ToListAsync();

            return new DashboardStatsResponse
            {
                TotalOrders = totalOrders,
                PendingOrders = pendingOrders,
                CompletedOrders = completedOrders,
                TotalCustomers = totalCustomers,
                TotalRevenue = totalRevenue,
                MonthlyRevenue = monthlyRevenue,
                OrdersByStatus = ordersByStatus.ToList(),
                DailyOrders = dailyOrders,
                DistributorPerformance = distributors
            };
        }
    }
}
