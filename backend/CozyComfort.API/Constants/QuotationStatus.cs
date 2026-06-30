namespace CozyComfort.API.Constants
{
    public static class QuotationStatus
    {
        public const string PENDING = "PENDING";
        public const string RECEIVED = "RECEIVED";
        public const string EXPIRED = "EXPIRED";
        public const string ERROR = "ERROR";
        
        public static readonly string[] AllStatuses = 
        {
            PENDING, RECEIVED, EXPIRED, ERROR
        };
    }
}
