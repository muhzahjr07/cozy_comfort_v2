namespace CozyComfort.API.Models.Domain
{
    public class Order
    {
        public int OrderId { get; set; }
        public string OrderReference { get; set; } = string.Empty; // ORD-2024-00001
        public int CustomerId { get; set; }
        public DateTime OrderDate { get; set; }
        public string Status { get; set; } = string.Empty; // PENDING, PROCESSING, CONFIRMED, SHIPPED, DELIVERED
        public decimal TotalAmount { get; set; }
        public string DeliveryAddress { get; set; } = string.Empty;
        public DateTime? EstimatedDeliveryDate { get; set; }
        public DateTime? ActualDeliveryDate { get; set; }
        public int? SelectedDistributorId { get; set; }
        public string? DistributorOrderId { get; set; }
        public string? Notes { get; set; }
        public DateTime CreatedAt { get; set; }
        public DateTime ModifiedAt { get; set; }

        // Navigation
        public virtual Customer Customer { get; set; } = null!;
        public virtual ICollection<OrderItem> OrderItems { get; set; } = new List<OrderItem>();
        public virtual ICollection<Quotation> Quotations { get; set; } = new List<Quotation>();
        public virtual Distributor? SelectedDistributor { get; set; }
    }
}
