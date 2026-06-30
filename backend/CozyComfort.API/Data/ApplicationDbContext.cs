using Microsoft.EntityFrameworkCore;
using CozyComfort.API.Models.Domain;

namespace CozyComfort.API.Data
{
    public class ApplicationDbContext : DbContext
    {
        public ApplicationDbContext(DbContextOptions<ApplicationDbContext> options) 
            : base(options) { }

        public DbSet<User> Users { get; set; }
        public DbSet<Customer> Customers { get; set; }
        public DbSet<Product> Products { get; set; }
        public DbSet<Order> Orders { get; set; }
        public DbSet<OrderItem> OrderItems { get; set; }
        public DbSet<Quotation> Quotations { get; set; }
        public DbSet<QuotationItem> QuotationItems { get; set; }
        public DbSet<Distributor> Distributors { get; set; }
        public DbSet<Notification> Notifications { get; set; }

        protected override void OnModelCreating(ModelBuilder modelBuilder)
        {
            base.OnModelCreating(modelBuilder);

            // User configuration
            modelBuilder.Entity<User>(entity =>
            {
                entity.HasKey(u => u.UserId);
                entity.HasIndex(u => u.Email).IsUnique();
                entity.Property(u => u.Email).HasMaxLength(255);
                entity.Property(u => u.FirstName).HasMaxLength(100);
                entity.Property(u => u.LastName).HasMaxLength(100);
                entity.Property(u => u.Role).HasMaxLength(50);
            });

            // Customer configuration
            modelBuilder.Entity<Customer>(entity =>
            {
                entity.HasKey(c => c.CustomerId);
                entity.HasIndex(c => c.Email).IsUnique();
                entity.Property(c => c.Email).HasMaxLength(255);
                entity.Property(c => c.FirstName).HasMaxLength(100);
                entity.Property(c => c.LastName).HasMaxLength(100);
                entity.Property(c => c.PhoneNumber).HasMaxLength(20);
                entity.Property(c => c.TotalSpent).HasColumnType("decimal(18,2)");

                entity.HasOne(c => c.User)
                    .WithOne(u => u.Customer)
                    .HasForeignKey<Customer>(c => c.UserId);

                entity.HasOne(c => c.PreferredDistributor)
                    .WithMany(d => d.PreferredByCustomers)
                    .HasForeignKey(c => c.PreferredDistributorId)
                    .OnDelete(DeleteBehavior.SetNull);
            });

            // Product configuration
            modelBuilder.Entity<Product>(entity =>
            {
                entity.HasKey(p => p.ProductId);
                entity.Property(p => p.Name).HasMaxLength(255).IsRequired();
                entity.Property(p => p.Category).HasMaxLength(100);
                entity.Property(p => p.ImageUrl).HasMaxLength(500);
                entity.Property(p => p.BasePrice).HasColumnType("decimal(18,2)").IsRequired();
            });

            // Order configuration
            modelBuilder.Entity<Order>(entity =>
            {
                entity.HasKey(o => o.OrderId);
                entity.Property(o => o.OrderReference).HasMaxLength(50).IsRequired();
                entity.Property(o => o.Status).HasMaxLength(50);
                entity.Property(o => o.TotalAmount).HasColumnType("decimal(18,2)");
                entity.Property(o => o.DeliveryAddress).HasMaxLength(1000);

                entity.HasOne(o => o.Customer)
                    .WithMany(c => c.Orders)
                    .HasForeignKey(o => o.CustomerId);

                entity.HasOne(o => o.SelectedDistributor)
                    .WithMany(d => d.Orders)
                    .HasForeignKey(o => o.SelectedDistributorId)
                    .OnDelete(DeleteBehavior.SetNull);

                entity.HasMany(o => o.OrderItems)
                    .WithOne(oi => oi.Order)
                    .HasForeignKey(oi => oi.OrderId)
                    .OnDelete(DeleteBehavior.Cascade);

                entity.HasMany(o => o.Quotations)
                    .WithOne(q => q.Order)
                    .HasForeignKey(q => q.OrderId)
                    .OnDelete(DeleteBehavior.Cascade);
            });

            // OrderItem configuration
            modelBuilder.Entity<OrderItem>(entity =>
            {
                entity.HasKey(oi => oi.OrderItemId);
                entity.Property(oi => oi.UnitPrice).HasColumnType("decimal(18,2)");
                entity.Property(oi => oi.TotalPrice).HasColumnType("decimal(18,2)");

                entity.HasOne(oi => oi.Product)
                    .WithMany(p => p.OrderItems)
                    .HasForeignKey(oi => oi.ProductId);
            });

            // Distributor configuration
            modelBuilder.Entity<Distributor>(entity =>
            {
                entity.HasKey(d => d.DistributorId);
                entity.Property(d => d.Name).HasMaxLength(255).IsRequired();
                entity.Property(d => d.ContactEmail).HasMaxLength(255);
                entity.Property(d => d.ContactPhone).HasMaxLength(20);
                entity.Property(d => d.ApiEndpoint).HasMaxLength(500);
                entity.Property(d => d.SuccessRate).HasColumnType("decimal(5,2)");
            });

            // Quotation configuration
            modelBuilder.Entity<Quotation>(entity =>
            {
                entity.HasKey(q => q.QuotationId);
                entity.Property(q => q.TotalCost).HasColumnType("decimal(18,2)");
                entity.Property(q => q.Status).HasMaxLength(50);

                entity.HasOne(q => q.Distributor)
                    .WithMany(d => d.Quotations)
                    .HasForeignKey(q => q.DistributorId);

                entity.HasMany(q => q.QuotationItems)
                    .WithOne(qi => qi.Quotation)
                    .HasForeignKey(qi => qi.QuotationId)
                    .OnDelete(DeleteBehavior.Cascade);
            });

            // QuotationItem configuration
            modelBuilder.Entity<QuotationItem>(entity =>
            {
                entity.HasKey(qi => qi.QuotationItemId);
                entity.Property(qi => qi.PricePerUnit).HasColumnType("decimal(18,2)");
                entity.Property(qi => qi.TotalPrice).HasColumnType("decimal(18,2)");

                entity.HasOne(qi => qi.Product)
                    .WithMany(p => p.QuotationItems)
                    .HasForeignKey(qi => qi.ProductId);
            });

            // Notification configuration
            modelBuilder.Entity<Notification>(entity =>
            {
                entity.HasKey(n => n.NotificationId);
                entity.Property(n => n.Type).HasMaxLength(50);
                entity.Property(n => n.Subject).HasMaxLength(255);
                entity.Property(n => n.Status).HasMaxLength(50);

                entity.HasOne(n => n.User)
                    .WithMany()
                    .HasForeignKey(n => n.UserId);
            });
        }
    }
}
