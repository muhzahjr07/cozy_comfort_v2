namespace CozyComfort.API.Models.Domain
{
    public class Distributor
    {
        public int DistributorId { get; set; }
        public string Name { get; set; } = string.Empty;
        public string ContactEmail { get; set; } = string.Empty;
        public string? ContactPhone { get; set; }
        public string ApiEndpoint { get; set; } = string.Empty;
        public string ApiKey { get; set; } = string.Empty;
        public bool IsActive { get; set; }
        public int AverageResponseTimeMs { get; set; }
        public decimal SuccessRate { get; set; }
        public int TotalQuotations { get; set; }
        public int TotalOrders { get; set; }
        public DateTime CreatedAt { get; set; }
        public DateTime ModifiedAt { get; set; }

        // Navigation
        public virtual ICollection<Quotation> Quotations { get; set; } = new List<Quotation>();
        public virtual ICollection<Order> Orders { get; set; } = new List<Order>();
        public virtual ICollection<Customer> PreferredByCustomers { get; set; } = new List<Customer>();
    }
}
