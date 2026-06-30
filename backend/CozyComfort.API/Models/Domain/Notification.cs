namespace CozyComfort.API.Models.Domain
{
    public class Notification
    {
        public int NotificationId { get; set; }
        public int UserId { get; set; }
        public string Type { get; set; } = string.Empty; // Email, SMS, Push
        public string Subject { get; set; } = string.Empty;
        public string Message { get; set; } = string.Empty;
        public string Status { get; set; } = string.Empty; // Pending, Sent, Failed
        public int? RetryCount { get; set; }
        public DateTime CreatedAt { get; set; }
        public DateTime? SentAt { get; set; }

        // Navigation
        public virtual User User { get; set; } = null!;
    }
}
