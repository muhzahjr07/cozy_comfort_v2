namespace CozyComfort.API.Models.Domain
{
    public class Quotation
    {
        public int QuotationId { get; set; }
        public int OrderId { get; set; }
        public int DistributorId { get; set; }
        public decimal TotalCost { get; set; }
        public int AvailableUnits { get; set; }
        public int RequestedUnits { get; set; }
        public DateTime EstimatedDeliveryTime { get; set; }
        public string Status { get; set; } = string.Empty; // PENDING, RECEIVED, EXPIRED, ERROR
        public DateTime RequestedAt { get; set; }
        public DateTime? ReceivedAt { get; set; }
        public DateTime? ExpiresAt { get; set; }
        public DateTime CreatedAt { get; set; }

        // Navigation
        public virtual Order Order { get; set; } = null!;
        public virtual Distributor Distributor { get; set; } = null!;
        public virtual ICollection<QuotationItem> QuotationItems { get; set; } = new List<QuotationItem>();
    }
}
