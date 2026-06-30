namespace CozyComfort.API.Constants
{
    public static class OrderStatus
    {
        public const string PENDING = "PENDING";
        public const string PROCESSING = "PROCESSING";
        public const string CONFIRMED = "CONFIRMED";
        public const string SHIPPED = "SHIPPED";
        public const string DELIVERED = "DELIVERED";
        public const string CANCELLED = "CANCELLED";
        
        public static readonly string[] AllStatuses = 
        {
            PENDING, PROCESSING, CONFIRMED, SHIPPED, DELIVERED, CANCELLED
        };
    }
}
