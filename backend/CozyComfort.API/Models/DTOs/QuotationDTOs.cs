using System.ComponentModel.DataAnnotations;

namespace CozyComfort.API.Models.DTOs
{
    /// <summary>
    /// Quotation response model
    /// </summary>
    public class QuotationResponse
    {
        public int QuotationId { get; set; }
        public int OrderId { get; set; }
        public int DistributorId { get; set; }
        public string DistributorName { get; set; } = string.Empty;
        public decimal TotalCost { get; set; }
        public int AvailableUnits { get; set; }
        public int RequestedUnits { get; set; }
        public DateTime EstimatedDeliveryTime { get; set; }
        public string Status { get; set; } = string.Empty;
        public DateTime RequestedAt { get; set; }
        public DateTime? ReceivedAt { get; set; }
        public List<QuotationItemResponse> Items { get; set; } = new();
    }

    /// <summary>
    /// Quotation item response
    /// </summary>
    public class QuotationItemResponse
    {
        public int QuotationItemId { get; set; }
        public int ProductId { get; set; }
        public string ProductName { get; set; } = string.Empty;
        public decimal PricePerUnit { get; set; }
        public int AvailableUnits { get; set; }
        public int RequestedUnits { get; set; }
        public decimal TotalPrice { get; set; }
    }

    /// <summary>
    /// Request model for creating quotation (Internal/Distributor)
    /// </summary>
    public class CreateQuotationRequest
    {
        [Required]
        public int OrderId { get; set; }

        [Required]
        public int DistributorId { get; set; }

        [Required]
        [MinLength(1)]
        public List<QuotationItemRequest> Items { get; set; } = new();

        [Required]
        public DateTime EstimatedDeliveryTime { get; set; }
    }

    /// <summary>
    /// Quotation item request
    /// </summary>
    public class QuotationItemRequest
    {
        [Required]
        public int ProductId { get; set; }

        [Required]
        [Range(0.01, double.MaxValue)]
        public decimal PricePerUnit { get; set; }

        [Required]
        [Range(0, int.MaxValue)]
        public int AvailableUnits { get; set; }

        [Required]
        [Range(1, int.MaxValue)]
        public int RequestedUnits { get; set; }
    }

    /// <summary>
    /// Quotation comparison model
    /// </summary>
    public class QuotationComparisonResponse
    {
        public int OrderId { get; set; }
        public List<QuotationResponse> Quotations { get; set; } = new();
        public QuotationResponse? RecommendedQuotation { get; set; }
        public string ComparisonSummary { get; set; } = string.Empty;
        public DateTime ComparedAt { get; set; }
    }
}
