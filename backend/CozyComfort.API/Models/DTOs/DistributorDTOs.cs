using System.ComponentModel.DataAnnotations;

namespace CozyComfort.API.Models.DTOs
{
    /// <summary>
    /// Distributor response model
    /// </summary>
    public class DistributorResponse
    {
        public int DistributorId { get; set; }
        public string Name { get; set; } = string.Empty;
        public string ContactEmail { get; set; } = string.Empty;
        public string? ContactPhone { get; set; }
        public string ApiEndpoint { get; set; } = string.Empty;
        public bool IsActive { get; set; }
        public int AverageResponseTimeMs { get; set; }
        public decimal SuccessRate { get; set; }
        public int TotalQuotations { get; set; }
        public int TotalOrders { get; set; }
        public decimal TotalValue { get; set; }
        public decimal Rating { get; set; }
        public DateTime CreatedAt { get; set; }
    }

    /// <summary>
    /// Request model for creating/updating distributor (Admin)
    /// </summary>
    public class CreateDistributorRequest
    {
        [Required]
        [MaxLength(255)]
        public string Name { get; set; } = string.Empty;

        [Required]
        [EmailAddress]
        public string ContactEmail { get; set; } = string.Empty;

        [Phone]
        public string? ContactPhone { get; set; }

        [Required]
        [Url]
        public string ApiEndpoint { get; set; } = string.Empty;

        [Required]
        public string ApiKey { get; set; } = string.Empty;
    }

    /// <summary>
    /// Request model for updating distributor
    /// </summary>
    public class UpdateDistributorRequest
    {
        [MaxLength(255)]
        public string? Name { get; set; }

        [EmailAddress]
        public string? ContactEmail { get; set; }

        [Phone]
        public string? ContactPhone { get; set; }

        [Url]
        public string? ApiEndpoint { get; set; }

        public string? ApiKey { get; set; }

        public bool? IsActive { get; set; }
    }

    /// <summary>
    /// Distributor performance metrics
    /// </summary>
    public class DistributorPerformanceResponse
    {
        public int DistributorId { get; set; }
        public string DistributorName { get; set; } = string.Empty;
        public int TotalQuotations { get; set; }
        public int SuccessfulQuotations { get; set; }
        public decimal SuccessRate { get; set; }
        public int AverageResponseTimeMs { get; set; }
        public int TotalOrders { get; set; }
        public decimal TotalRevenue { get; set; }
        public DateTime PeriodStart { get; set; }
        public DateTime PeriodEnd { get; set; }
        public List<DailyPerformanceMetric> DailyMetrics { get; set; } = new();
    }

    /// <summary>
    /// Daily performance metric
    /// </summary>
    public class DailyPerformanceMetric
    {
        public DateTime Date { get; set; }
        public int Quotations { get; set; }
        public int Orders { get; set; }
        public int AverageResponseTimeMs { get; set; }
    }
}
