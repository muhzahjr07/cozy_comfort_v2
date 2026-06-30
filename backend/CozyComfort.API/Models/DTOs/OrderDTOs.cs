using System.ComponentModel.DataAnnotations;

namespace CozyComfort.API.Models.DTOs
{
    /// <summary>
    /// Request model for creating a new order
    /// </summary>
    public class CreateOrderRequest
    {
        [Required]
        public int CustomerId { get; set; }

        [Required]
        [MinLength(1, ErrorMessage = "Order must contain at least one item")]
        public List<OrderItemRequest> Items { get; set; } = new();

        [Required]
        [MaxLength(500)]
        public string DeliveryAddress { get; set; } = string.Empty;

        [Phone]
        public string? PhoneNumber { get; set; }

        public string? Notes { get; set; }
    }

    /// <summary>
    /// Order item within a create order request
    /// </summary>
    public class OrderItemRequest
    {
        [Required]
        [Range(1, int.MaxValue)]
        public int ProductId { get; set; }

        [Required]
        [Range(1, 1000)]
        public int Quantity { get; set; }

        [Required]
        [Range(0.01, double.MaxValue)]
        public decimal UnitPrice { get; set; }
    }

    /// <summary>
    /// Response model for order operations
    /// </summary>
    public class OrderResponse
    {
        public int OrderId { get; set; }
        public string OrderReference { get; set; } = string.Empty;
        public int CustomerId { get; set; }
        public DateTime OrderDate { get; set; }
        public string Status { get; set; } = string.Empty;
        public decimal TotalAmount { get; set; }
        public string DeliveryAddress { get; set; } = string.Empty;
        public DateTime? EstimatedDeliveryDate { get; set; }
        public DateTime? ActualDeliveryDate { get; set; }
        public int? SelectedDistributorId { get; set; }
        public string? SelectedDistributorName { get; set; }
        public string? DistributorOrderId { get; set; }
        public string? Notes { get; set; }
        public List<OrderItemResponse> Items { get; set; } = new();
        public List<QuotationResponse>? Quotations { get; set; }
    }

    /// <summary>
    /// Order item within an order response
    /// </summary>
    public class OrderItemResponse
    {
        public int OrderItemId { get; set; }
        public int ProductId { get; set; }
        public string ProductName { get; set; } = string.Empty;
        public int Quantity { get; set; }
        public decimal UnitPrice { get; set; }
        public decimal TotalPrice { get; set; }
    }

    /// <summary>
    /// Request model for updating order status
    /// </summary>
    public class UpdateOrderStatusRequest
    {
        [Required]
        public string Status { get; set; } = string.Empty;
        public string? Notes { get; set; }
    }

    /// <summary>
    /// Order summary for list views
    /// </summary>
    public class OrderSummaryResponse
    {
        public int OrderId { get; set; }
        public string OrderReference { get; set; } = string.Empty;
        public DateTime OrderDate { get; set; }
        public string Status { get; set; } = string.Empty;
        public decimal TotalAmount { get; set; }
        public int ItemCount { get; set; }
        public string? DistributorName { get; set; }
        public DateTime? EstimatedDeliveryDate { get; set; }
    }
}
