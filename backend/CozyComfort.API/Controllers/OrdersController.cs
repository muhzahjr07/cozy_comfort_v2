using Microsoft.AspNetCore.Mvc;
using CozyComfort.API.Models.DTOs;
using CozyComfort.API.Services.Interfaces;

namespace CozyComfort.API.Controllers
{
    [ApiController]
    [Route("api/[controller]")]
    public class OrdersController : ControllerBase
    {
        private readonly IOrderService _orderService;
        private readonly ILogger<OrdersController> _logger;

        public OrdersController(IOrderService orderService, ILogger<OrdersController> logger)
        {
            _orderService = orderService;
            _logger = logger;
        }

        [HttpPost]
        public async Task<ActionResult<ApiResponse<OrderResponse>>> CreateOrder([FromBody] CreateOrderRequest request)
        {
            try
            {
                var result = await _orderService.CreateOrderAsync(request);
                return Ok(ApiResponse<OrderResponse>.SuccessResult(result, "Order created successfully"));
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error creating order");
                return BadRequest(ApiResponse<OrderResponse>.ErrorResult(ex.Message));
            }
        }

        [HttpGet("{id}")]
        public async Task<ActionResult<ApiResponse<OrderResponse>>> GetOrder(int id)
        {
            try
            {
                var result = await _orderService.GetOrderByIdAsync(id);
                return Ok(ApiResponse<OrderResponse>.SuccessResult(result));
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Error getting order {id}");
                return NotFound(ApiResponse<OrderResponse>.ErrorResult(ex.Message));
            }
        }

        [HttpGet("reference/{reference}")]
        public async Task<ActionResult<ApiResponse<OrderResponse>>> GetOrderByReference(string reference)
        {
            try
            {
                var result = await _orderService.GetOrderByReferenceAsync(reference);
                return Ok(ApiResponse<OrderResponse>.SuccessResult(result));
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Error getting order by reference {reference}");
                return NotFound(ApiResponse<OrderResponse>.ErrorResult(ex.Message));
            }
        }

        [HttpGet("customer/{customerId}")]
        public async Task<ActionResult<ApiResponse<PaginatedResponse<OrderResponse>>>> GetCustomerOrders(
            int customerId, [FromQuery] int page = 1, [FromQuery] int pageSize = 10)
        {
            try
            {
                var result = await _orderService.GetCustomerOrdersAsync(customerId, page, pageSize);
                return Ok(ApiResponse<PaginatedResponse<OrderResponse>>.SuccessResult(result));
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Error getting orders for customer {customerId}");
                return BadRequest(ApiResponse<PaginatedResponse<OrderResponse>>.ErrorResult(ex.Message));
            }
        }

        [HttpPatch("{id}/status")]
        public async Task<ActionResult<ApiResponse<bool>>> UpdateOrderStatus(int id, [FromBody] UpdateOrderStatusRequest request)
        {
            try
            {
                var result = await _orderService.UpdateOrderStatusAsync(id, request.Status);
                return Ok(ApiResponse<bool>.SuccessResult(result, "Order status updated successfully"));
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Error updating order status for order {id}");
                return BadRequest(ApiResponse<bool>.ErrorResult(ex.Message));
            }
        }

        [HttpDelete("{id}")]
        public async Task<ActionResult<ApiResponse<bool>>> CancelOrder(int id)
        {
            try
            {
                var result = await _orderService.CancelOrderAsync(id);
                return Ok(ApiResponse<bool>.SuccessResult(result, "Order cancelled successfully"));
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Error cancelling order {id}");
                return BadRequest(ApiResponse<bool>.ErrorResult(ex.Message));
            }
        }

        [HttpGet]
        public async Task<ActionResult<ApiResponse<PaginatedResponse<OrderSummaryResponse>>>> GetAllOrders([FromQuery] int page = 1, [FromQuery] int pageSize = 10, [FromQuery] string? status = null)
        {
            try
            {
                var result = await _orderService.GetAllOrdersAsync(page, pageSize, status);
                return Ok(ApiResponse<PaginatedResponse<OrderSummaryResponse>>.SuccessResult(result));
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error getting all orders");
                return BadRequest(ApiResponse<PaginatedResponse<OrderSummaryResponse>>.ErrorResult(ex.Message));
            }
        }

        [HttpGet("dashboard-stats")]
        public async Task<ActionResult<ApiResponse<DashboardStatsResponse>>> GetDashboardStats()
        {
            try
            {
                var result = await _orderService.GetDashboardStatsAsync();
                return Ok(ApiResponse<DashboardStatsResponse>.SuccessResult(result));
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error getting dashboard stats");
                return BadRequest(ApiResponse<DashboardStatsResponse>.ErrorResult(ex.Message));
            }
        }
    }
}
