using AutoMapper;
using CozyComfort.API.Models.Domain;
using CozyComfort.API.Models.DTOs;
using CozyComfort.API.Repositories;
using CozyComfort.API.Services.Interfaces;
using CozyComfort.API.Exceptions;

namespace CozyComfort.API.Services.Implementations
{
    public class OrderService : IOrderService
    {
        private readonly IOrderRepository _orderRepository;
        private readonly IQuotationService _quotationService;
        private readonly IMapper _mapper;
        private readonly ILogger<OrderService> _logger;

        public OrderService(
            IOrderRepository orderRepository,
            IQuotationService quotationService,
            IMapper mapper,
            ILogger<OrderService> logger)
        {
            _orderRepository = orderRepository;
            _quotationService = quotationService;
            _mapper = mapper;
            _logger = logger;
        }

        public async Task<OrderResponse> CreateOrderAsync(CreateOrderRequest request)
        {
            // Validate request
            if (!request.Items.Any())
                throw new BadRequestException("Order must contain at least one item");

            // Calculate total
            var totalAmount = request.Items.Sum(x => x.UnitPrice * x.Quantity);

            // Create order entity
            var order = _mapper.Map<Order>(request);
            order.OrderReference = GenerateOrderReference();
            order.TotalAmount = totalAmount;

            // Add order items
            foreach (var item in request.Items)
            {
                var orderItem = _mapper.Map<OrderItem>(item);
                order.OrderItems.Add(orderItem);
            }

            // Save order
            await _orderRepository.AddAsync(order);
            await _orderRepository.SaveChangesAsync();

            _logger.LogInformation($"Order created: {order.OrderId} - {order.OrderReference}");

            // Request quotations from distributors
            _ = Task.Run(async () => await _quotationService.RequestQuotationsAsync(order));

            return _mapper.Map<OrderResponse>(order);
        }

        public async Task<OrderResponse> GetOrderByIdAsync(int orderId)
        {
            var order = await _orderRepository.GetOrderWithDetailsAsync(orderId);
            if (order == null)
                throw new NotFoundException($"Order with ID {orderId} not found");

            return _mapper.Map<OrderResponse>(order);
        }

        public async Task<OrderResponse> GetOrderByReferenceAsync(string reference)
        {
            var order = await _orderRepository.GetOrderByReferenceAsync(reference);
            if (order == null)
                throw new NotFoundException($"Order with reference {reference} not found");

            return _mapper.Map<OrderResponse>(order);
        }

        public async Task<PaginatedResponse<OrderResponse>> GetCustomerOrdersAsync(int customerId, int pageNumber, int pageSize)
        {
            var orders = await _orderRepository.GetOrdersByCustomerAsync(customerId, pageNumber, pageSize);
            var orderResponses = _mapper.Map<List<OrderResponse>>(orders);
            
            var totalCount = await _orderRepository.CountAsync(o => o.CustomerId == customerId);
            
            return PaginatedResponse<OrderResponse>.Create(orderResponses, pageNumber, pageSize, totalCount);
        }

        public async Task<PaginatedResponse<OrderSummaryResponse>> GetAllOrdersAsync(int page, int pageSize, string? status = null)
        {
            var result = await _orderRepository.GetOrdersPagedAsync(page, pageSize, status);
            var orderSummaries = _mapper.Map<List<OrderSummaryResponse>>(result.Data);
            
            return PaginatedResponse<OrderSummaryResponse>.Create(orderSummaries, page, pageSize, result.TotalCount);
        }

        public async Task<bool> UpdateOrderStatusAsync(int orderId, string status)
        {
            var order = await _orderRepository.GetByIdAsync(orderId);
            if (order == null)
                throw new NotFoundException($"Order with ID {orderId} not found");

            order.Status = status;
            order.ModifiedAt = DateTime.UtcNow;
            
            _orderRepository.Update(order);
            await _orderRepository.SaveChangesAsync();

            _logger.LogInformation($"Order {orderId} status updated to {status}");
            return true;
        }

        public async Task<bool> CancelOrderAsync(int orderId)
        {
            return await UpdateOrderStatusAsync(orderId, Constants.OrderStatus.CANCELLED);
        }

        public async Task<DashboardStatsResponse> GetDashboardStatsAsync()
        {
            return await _orderRepository.GetDashboardStatsAsync();
        }

        private string GenerateOrderReference()
        {
            return $"ORD-{DateTime.Now:yyyy}-{Guid.NewGuid().ToString()[..5].ToUpper()}";
        }
    }
}
