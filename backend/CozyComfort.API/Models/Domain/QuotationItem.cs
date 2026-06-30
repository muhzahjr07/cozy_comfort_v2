namespace CozyComfort.API.Models.Domain
{
    public class QuotationItem
    {
        public int QuotationItemId { get; set; }
        public int QuotationId { get; set; }
        public int ProductId { get; set; }
        public decimal PricePerUnit { get; set; }
        public int AvailableUnits { get; set; }
        public int RequestedUnits { get; set; }
        public decimal TotalPrice { get; set; }
        public DateTime CreatedAt { get; set; }

        // Navigation
        public virtual Quotation Quotation { get; set; } = null!;
        public virtual Product Product { get; set; } = null!;
    }
}
