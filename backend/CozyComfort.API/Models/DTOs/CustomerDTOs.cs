using System.ComponentModel.DataAnnotations;

namespace CozyComfort.API.Models.DTOs
{
    /// <summary>
    /// Customer profile response
    /// </summary>
    public class CustomerResponse
    {
        public int CustomerId { get; set; }
        public int UserId { get; set; }
        public string Email { get; set; } = string.Empty;
        public string FirstName { get; set; } = string.Empty;
        public string LastName { get; set; } = string.Empty;
        public string? PhoneNumber { get; set; }
        public string? ShippingAddress { get; set; }
        public string? BillingAddress { get; set; }
        public int? PreferredDistributorId { get; set; }
        public string? PreferredDistributorName { get; set; }
        public int TotalOrders { get; set; }
        public decimal TotalSpent { get; set; }
        public DateTime CreatedAt { get; set; }
    }

    /// <summary>
    /// Request model for updating customer profile
    /// </summary>
    public class UpdateCustomerRequest
    {
        [MaxLength(50)]
        public string? FirstName { get; set; }

        [MaxLength(50)]
        public string? LastName { get; set; }

        [Phone]
        public string? PhoneNumber { get; set; }

        [MaxLength(500)]
        public string? ShippingAddress { get; set; }

        [MaxLength(500)]
        public string? BillingAddress { get; set; }

        public int? PreferredDistributorId { get; set; }
    }
}
