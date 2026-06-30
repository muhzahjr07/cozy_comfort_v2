using System.ComponentModel.DataAnnotations;

namespace CozyComfort.API.Models.DTOs
{
    /// <summary>
    /// Product response model
    /// </summary>
    public class ProductResponse
    {
        public int ProductId { get; set; }
        public string Name { get; set; } = string.Empty;
        public string Description { get; set; } = string.Empty;
        public string Category { get; set; } = string.Empty;
        public Dictionary<string, string> Specifications { get; set; } = new();
        public string ImageUrl { get; set; } = string.Empty;
        public decimal? BasePrice { get; set; }
        public bool IsActive { get; set; }
        public DateTime CreatedAt { get; set; }
    }

    /// <summary>
    /// Product summary for list views
    /// </summary>
    public class ProductSummaryResponse
    {
        public int ProductId { get; set; }
        public string Name { get; set; } = string.Empty;
        public string Description { get; set; } = string.Empty;
        public string Category { get; set; } = string.Empty;
        public string ImageUrl { get; set; } = string.Empty;
        public decimal? BasePrice { get; set; }
    }

    /// <summary>
    /// Request model for product search and filtering
    /// </summary>
    public class ProductSearchRequest
    {
        public string? Search { get; set; }
        public string? Category { get; set; }
        public decimal? MinPrice { get; set; }
        public decimal? MaxPrice { get; set; }
        public string? SortBy { get; set; } = "newest";
        public int Page { get; set; } = 1;
        public int PageSize { get; set; } = 12;
    }

    /// <summary>
    /// Request model for creating/updating product (Admin)
    /// </summary>
    public class CreateProductRequest
    {
        [Required]
        [MaxLength(255)]
        public string Name { get; set; } = string.Empty;

        [Required]
        [MaxLength(1000)]
        public string Description { get; set; } = string.Empty;

        [Required]
        [MaxLength(100)]
        public string Category { get; set; } = string.Empty;

        public Dictionary<string, string> Specifications { get; set; } = new();

        [Url]
        public string? ImageUrl { get; set; }

        [Range(0.01, double.MaxValue)]
        public decimal? BasePrice { get; set; }
    }

    /// <summary>
    /// Category product count statistics
    /// </summary>
    public class CategoryStatsResponse
    {
        public string Name { get; set; } = string.Empty;
        public int ProductCount { get; set; }
    }
}
