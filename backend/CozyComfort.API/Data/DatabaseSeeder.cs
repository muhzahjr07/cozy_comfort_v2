using Microsoft.EntityFrameworkCore;
using CozyComfort.API.Models.Domain;

namespace CozyComfort.API.Data
{
    public static class DatabaseSeeder
    {
        public static async Task SeedAsync(ApplicationDbContext context)
        {
            try
            {
                // Check if data already exists
                if (await context.Users.AnyAsync() || await context.Products.AnyAsync())
                {
                    return; // Database has been seeded
                }

                // Hashed password for default accounts ("Password123!")
                string defaultPasswordHash = BCrypt.Net.BCrypt.HashPassword("Password123!");

                // Seed Users first (required for Customers)
                var users = new List<User>
                {
                    new User
                    {
                        Email = "john.doe@example.com",
                        FirstName = "John",
                        LastName = "Doe",
                        PasswordHash = defaultPasswordHash,
                        Role = "Customer",
                        IsActive = true,
                        CreatedAt = DateTime.UtcNow
                    },
                    new User
                    {
                        Email = "jane.smith@example.com",
                        FirstName = "Jane",
                        LastName = "Smith",
                        PasswordHash = defaultPasswordHash,
                        Role = "Customer",
                        IsActive = true,
                        CreatedAt = DateTime.UtcNow
                    },
                    new User
                    {
                        Email = "admin@cozycomfort.com",
                        FirstName = "Admin",
                        LastName = "User",
                        PasswordHash = defaultPasswordHash,
                        Role = "Admin",
                        IsActive = true,
                        CreatedAt = DateTime.UtcNow
                    }
                };

                context.Users.AddRange(users);
                await context.SaveChangesAsync();

                // Seed Customers
                var customers = new List<Customer>
                {
                    new Customer
                    {
                        UserId = users[0].UserId,
                        FirstName = "John",
                        LastName = "Doe",
                        Email = "john.doe@example.com",
                        PhoneNumber = "555-123-4567",
                        ShippingAddress = "123 Main St, Colombo 03, Sri Lanka",
                        BillingAddress = "123 Main St, Colombo 03, Sri Lanka",
                        TotalOrders = 0,
                        TotalSpent = 0m,
                        CreatedAt = DateTime.UtcNow,
                        ModifiedAt = DateTime.UtcNow
                    },
                    new Customer
                    {
                        UserId = users[1].UserId,
                        FirstName = "Jane",
                        LastName = "Smith",
                        Email = "jane.smith@example.com",
                        PhoneNumber = "555-987-6543",
                        ShippingAddress = "456 Galle Rd, Kandy, Sri Lanka",
                        BillingAddress = "456 Galle Rd, Kandy, Sri Lanka",
                        TotalOrders = 0,
                        TotalSpent = 0m,
                        CreatedAt = DateTime.UtcNow,
                        ModifiedAt = DateTime.UtcNow
                    },
                    new Customer
                    {
                        UserId = users[2].UserId,
                        FirstName = "Admin",
                        LastName = "User",
                        Email = "admin@cozycomfort.com",
                        PhoneNumber = "555-555-0100",
                        ShippingAddress = "789 Factory Rd, Negombo, Sri Lanka",
                        BillingAddress = "789 Factory Rd, Negombo, Sri Lanka",
                        TotalOrders = 0,
                        TotalSpent = 0m,
                        CreatedAt = DateTime.UtcNow,
                        ModifiedAt = DateTime.UtcNow
                    }
                };

                context.Customers.AddRange(customers);
                await context.SaveChangesAsync();

                // Seed 26 Cozy Comfort Blanket Products
                var products = new List<Product>
                {
                    // Category: Weighted Blankets
                    new Product
                    {
                        Name = "CalmPressure 15lbs Weighted Blanket",
                        Description = "Deep touch pressure therapy weighted blanket featuring premium glass beads for stress relief and deep sleep.",
                        Category = "Weighted Blankets",
                        Specifications = "{\"material\": \"Organic Cotton & Glass Beads\", \"weight\": \"15 lbs\", \"dimensions\": \"60 x 80 inches\", \"washable\": \"Cover only\"}",
                        ImageUrl = "https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?q=80&w=800",
                        BasePrice = 12500m,
                        IsActive = true,
                        CreatedAt = DateTime.UtcNow,
                        ModifiedAt = DateTime.UtcNow
                    },
                    new Product
                    {
                        Name = "Cooling Bamboo Weighted Blanket",
                        Description = "Silky bamboo viscose cover filled with micro glass beads. Naturally temperature-regulating for hot sleepers.",
                        Category = "Weighted Blankets",
                        Specifications = "{\"material\": \"Bamboo Viscose & Glass Beads\", \"weight\": \"12 lbs\", \"dimensions\": \"48 x 72 inches\", \"washable\": \"Machine washable\"}",
                        ImageUrl = "https://images.unsplash.com/photo-1584091605335-c3cc26871c84?q=80&w=800",
                        BasePrice = 14200m,
                        IsActive = true,
                        CreatedAt = DateTime.UtcNow,
                        ModifiedAt = DateTime.UtcNow
                    },
                    new Product
                    {
                        Name = "Heavy Duty 20lbs Therapy Blanket",
                        Description = "Extra heavy sensory blanket designed for adults. Distributes weight evenly to promote restful sleep cycles.",
                        Category = "Weighted Blankets",
                        Specifications = "{\"material\": \"Cotton & Glass Microbeads\", \"weight\": \"20 lbs\", \"dimensions\": \"60 x 80 inches\", \"washable\": \"Dry clean recommended\"}",
                        ImageUrl = "https://images.unsplash.com/photo-1565793298595-6a879b1d9492?q=80&w=800",
                        BasePrice = 16800m,
                        IsActive = true,
                        CreatedAt = DateTime.UtcNow,
                        ModifiedAt = DateTime.UtcNow
                    },
                    new Product
                    {
                        Name = "Calming Kids Weighted Blanket",
                        Description = "Soft minky weighted blanket for children. Helps calm active minds before bed.",
                        Category = "Weighted Blankets",
                        Specifications = "{\"material\": \"Minky Plush & Glass Beads\", \"weight\": \"5 lbs\", \"dimensions\": \"36 x 48 inches\", \"washable\": \"Machine washable\"}",
                        ImageUrl = "https://images.unsplash.com/photo-1528938102132-4a9276b8e320?q=80&w=800",
                        BasePrice = 7500m,
                        IsActive = true,
                        CreatedAt = DateTime.UtcNow,
                        ModifiedAt = DateTime.UtcNow
                    },

                    // Category: Heated & Electric Blankets
                    new Product
                    {
                        Name = "Plush Velvet Heated Throw",
                        Description = "Electric heated throw blanket featuring 5 heating levels and an automatic 3-hour shutoff for safety.",
                        Category = "Heated & Electric Blankets",
                        Specifications = "{\"material\": \"Plush Velvet\", \"settings\": 5, \"dimensions\": \"50 x 60 inches\", \"washable\": \"Machine washable (detach controller)\"}",
                        ImageUrl = "https://images.unsplash.com/photo-1589834390005-5d4fb9bf3d32?q=80&w=800",
                        BasePrice = 9800m,
                        IsActive = true,
                        CreatedAt = DateTime.UtcNow,
                        ModifiedAt = DateTime.UtcNow
                    },
                    new Product
                    {
                        Name = "Dual-Zone Electric Bed Blanket",
                        Description = "Full bed electric blanket with dual zones and independent controllers for customized comfort.",
                        Category = "Heated & Electric Blankets",
                        Specifications = "{\"material\": \"Micro-plush Fleece\", \"settings\": 10, \"dimensions\": \"84 x 90 inches (Queen)\", \"washable\": \"Machine washable\"}",
                        ImageUrl = "https://images.unsplash.com/photo-1618220179428-22790b461013?q=80&w=800",
                        BasePrice = 18500m,
                        IsActive = true,
                        CreatedAt = DateTime.UtcNow,
                        ModifiedAt = DateTime.UtcNow
                    },
                    new Product
                    {
                        Name = "ThermaLuxe Heated Wearable Wrap",
                        Description = "A heated shawl and wrap blanket with hand pockets. Perfect for working from home.",
                        Category = "Heated & Electric Blankets",
                        Specifications = "{\"material\": \"Flannel Fleece\", \"settings\": 3, \"dimensions\": \"64 x 57 inches\", \"washable\": \"Machine washable\"}",
                        ImageUrl = "https://images.unsplash.com/photo-1544816155-12df9643f363?q=80&w=800",
                        BasePrice = 11000m,
                        IsActive = true,
                        CreatedAt = DateTime.UtcNow,
                        ModifiedAt = DateTime.UtcNow
                    },
                    new Product
                    {
                        Name = "Fast-Heating Fleece Throw",
                        Description = "Quick-heat blanket with rapid warming technology and plush fleece surface.",
                        Category = "Heated & Electric Blankets",
                        Specifications = "{\"material\": \"Polyester Fleece\", \"settings\": 4, \"dimensions\": \"50 x 60 inches\", \"washable\": \"Machine washable\"}",
                        ImageUrl = "https://images.unsplash.com/photo-1629949009765-40ea3d8bebe5?q=80&w=800",
                        BasePrice = 8900m,
                        IsActive = true,
                        CreatedAt = DateTime.UtcNow,
                        ModifiedAt = DateTime.UtcNow
                    },

                    // Category: Sherpa & Fleece Throws
                    new Product
                    {
                        Name = "Royal Sherpa Luxury Throw",
                        Description = "Reversible throw blanket with soft flannel fleece on one side and cozy warm sherpa lining on the other.",
                        Category = "Sherpa & Fleece Throws",
                        Specifications = "{\"material\": \"Flannel & Sherpa Fleece\", \"weight\": \"3.5 lbs\", \"dimensions\": \"50 x 60 inches\", \"washable\": \"Machine washable\"}",
                        ImageUrl = "https://images.unsplash.com/photo-1600121848594-d8644e57abab?q=80&w=800",
                        BasePrice = 6200m,
                        IsActive = true,
                        CreatedAt = DateTime.UtcNow,
                        ModifiedAt = DateTime.UtcNow
                    },
                    new Product
                    {
                        Name = "Microfleece Lightweight Bed Blanket",
                        Description = "Extra soft, lightweight microfleece blanket suitable for all-season layering on beds.",
                        Category = "Sherpa & Fleece Throws",
                        Specifications = "{\"material\": \"100% Microfleece\", \"weight\": \"2.2 lbs\", \"dimensions\": \"90 x 90 inches (Queen)\", \"washable\": \"Machine washable\"}",
                        ImageUrl = "https://images.unsplash.com/photo-1584100936595-c0654b55a2e2?q=80&w=800",
                        BasePrice = 4800m,
                        IsActive = true,
                        CreatedAt = DateTime.UtcNow,
                        ModifiedAt = DateTime.UtcNow
                    },
                    new Product
                    {
                        Name = "Ribbed Fleece Premium Throw",
                        Description = "Featuring an elegant ribbed corduroy pattern on fleece. Adds a contemporary aesthetic to any couch.",
                        Category = "Sherpa & Fleece Throws",
                        Specifications = "{\"material\": \"Polyester Fleece\", \"weight\": \"2.8 lbs\", \"dimensions\": \"50 x 65 inches\", \"washable\": \"Machine washable\"}",
                        ImageUrl = "https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?q=80&w=800",
                        BasePrice = 5500m,
                        IsActive = true,
                        CreatedAt = DateTime.UtcNow,
                        ModifiedAt = DateTime.UtcNow
                    },
                    new Product
                    {
                        Name = "Cozy Cabin Buffalo Plaid Throw",
                        Description = "Traditional red and black buffalo plaid throw lined with thick winter sherpa.",
                        Category = "Sherpa & Fleece Throws",
                        Specifications = "{\"material\": \"Flannel Fleece & Sherpa\", \"weight\": \"3.2 lbs\", \"dimensions\": \"50 x 60 inches\", \"washable\": \"Machine washable\"}",
                        ImageUrl = "https://images.unsplash.com/photo-1551524559-8af4e6624178?q=80&w=800",
                        BasePrice = 6900m,
                        IsActive = true,
                        CreatedAt = DateTime.UtcNow,
                        ModifiedAt = DateTime.UtcNow
                    },
                    new Product
                    {
                        Name = "Faux Fur Elegant Throw",
                        Description = "Luxuriously thick faux fur throw blanket. Adds texture, warmth, and opulence to bedrooms.",
                        Category = "Sherpa & Fleece Throws",
                        Specifications = "{\"material\": \"Acrylic & Polyester Faux Fur\", \"weight\": \"4.5 lbs\", \"dimensions\": \"50 x 60 inches\", \"washable\": \"Dry clean recommended\"}",
                        ImageUrl = "https://images.unsplash.com/photo-1616594039964-ae9021a400a0?q=80&w=800",
                        BasePrice = 11500m,
                        IsActive = true,
                        CreatedAt = DateTime.UtcNow,
                        ModifiedAt = DateTime.UtcNow
                    },
                    new Product
                    {
                        Name = "Wearable Oversized Hoodie Blanket",
                        Description = "Huge wearable hoodie blanket with front pocket and sleeves. Cozy comfort on the go.",
                        Category = "Sherpa & Fleece Throws",
                        Specifications = "{\"material\": \"Flannel Exterior & Sherpa Interior\", \"weight\": \"2.5 lbs\", \"dimensions\": \"One Size Fits All\", \"washable\": \"Machine washable\"}",
                        ImageUrl = "https://images.unsplash.com/photo-1507330319697-f55475b5f885?q=80&w=800",
                        BasePrice = 7900m,
                        IsActive = true,
                        CreatedAt = DateTime.UtcNow,
                        ModifiedAt = DateTime.UtcNow
                    },

                    // Category: Wool & Knit Blankets
                    new Product
                    {
                        Name = "Australian Merino Wool Heavy Blanket",
                        Description = "Premium quality woven wool blanket. Offers breathable insulation and timeless durability.",
                        Category = "Wool & Knit Blankets",
                        Specifications = "{\"material\": \"100% Merino Wool\", \"weight\": \"4.8 lbs\", \"dimensions\": \"66 x 90 inches (Twin)\", \"washable\": \"Dry clean only\"}",
                        ImageUrl = "https://images.unsplash.com/photo-1563245372-f21724e3856d?q=80&w=800",
                        BasePrice = 24500m,
                        IsActive = true,
                        CreatedAt = DateTime.UtcNow,
                        ModifiedAt = DateTime.UtcNow
                    },
                    new Product
                    {
                        Name = "Hygge Chunky Knit Throw",
                        Description = "Hand-knit chunky tube cotton blanket. Thick, tactile, and highly decorative.",
                        Category = "Wool & Knit Blankets",
                        Specifications = "{\"material\": \"Chunky Cotton Tubing\", \"weight\": \"8.0 lbs\", \"dimensions\": \"40 x 60 inches\", \"washable\": \"Spot clean only\"}",
                        ImageUrl = "https://images.unsplash.com/photo-1606744824163-985d376605aa?q=80&w=800",
                        BasePrice = 16000m,
                        IsActive = true,
                        CreatedAt = DateTime.UtcNow,
                        ModifiedAt = DateTime.UtcNow
                    },
                    new Product
                    {
                        Name = "Traditional Irish Tweed Throw",
                        Description = "Woven in Ireland using heritage herringbone designs. Durable, rustic, and warm.",
                        Category = "Wool & Knit Blankets",
                        Specifications = "{\"material\": \"100% Pure New Wool\", \"weight\": \"3.0 lbs\", \"dimensions\": \"54 x 72 inches\", \"washable\": \"Dry clean only\"}",
                        ImageUrl = "https://images.unsplash.com/photo-1574634534894-89d7576c8259?q=80&w=800",
                        BasePrice = 18900m,
                        IsActive = true,
                        CreatedAt = DateTime.UtcNow,
                        ModifiedAt = DateTime.UtcNow
                    },
                    new Product
                    {
                        Name = "Icelandic Patterned Wool Blanket",
                        Description = "Features native Nordic geometric patterns. Provides extreme insulation for cold nights.",
                        Category = "Wool & Knit Blankets",
                        Specifications = "{\"material\": \"100% Icelandic Wool\", \"weight\": \"3.5 lbs\", \"dimensions\": \"60 x 80 inches\", \"washable\": \"Hand wash only\"}",
                        ImageUrl = "https://images.unsplash.com/photo-1542435503-956c469947f6?q=80&w=800",
                        BasePrice = 21000m,
                        IsActive = true,
                        CreatedAt = DateTime.UtcNow,
                        ModifiedAt = DateTime.UtcNow
                    },
                    new Product
                    {
                        Name = "Handcrafted Alpaca Herringbone Throw",
                        Description = "Incredibly soft, lightweight throw woven from premium alpaca fibers. Silk-like feel.",
                        Category = "Wool & Knit Blankets",
                        Specifications = "{\"material\": \"100% Baby Alpaca Wool\", \"weight\": \"1.8 lbs\", \"dimensions\": \"50 x 70 inches\", \"washable\": \"Dry clean recommended\"}",
                        ImageUrl = "https://images.unsplash.com/photo-1615876234886-fd9a39fda97f?q=80&w=800",
                        BasePrice = 27500m,
                        IsActive = true,
                        CreatedAt = DateTime.UtcNow,
                        ModifiedAt = DateTime.UtcNow
                    },
                    new Product
                    {
                        Name = "Cable Knit Chenille Throw",
                        Description = "Cable knit throw blanket made of chenille polyester yarn. Soft, plush, and non-shedding.",
                        Category = "Wool & Knit Blankets",
                        Specifications = "{\"material\": \"100% Chenille Polyester\", \"weight\": \"3.6 lbs\", \"dimensions\": \"50 x 60 inches\", \"washable\": \"Machine washable\"}",
                        ImageUrl = "https://images.unsplash.com/photo-1511556532299-8f662fc26c06?q=80&w=800",
                        BasePrice = 9200m,
                        IsActive = true,
                        CreatedAt = DateTime.UtcNow,
                        ModifiedAt = DateTime.UtcNow
                    },

                    // Category: Luxury Cashmere & Cotton
                    new Product
                    {
                        Name = "Himalayan Cashmere Luxury Throw",
                        Description = "Hand-loomed cashmere throw blanket from Nepal. Extremely light, soft, and warm.",
                        Category = "Luxury Cashmere & Cotton",
                        Specifications = "{\"material\": \"100% Pure Cashmere\", \"weight\": \"1.2 lbs\", \"dimensions\": \"50 x 80 inches\", \"washable\": \"Dry clean only\"}",
                        ImageUrl = "https://images.unsplash.com/photo-1598928506311-c55ded91a20c?q=80&w=800",
                        BasePrice = 45000m,
                        IsActive = true,
                        CreatedAt = DateTime.UtcNow,
                        ModifiedAt = DateTime.UtcNow
                    },
                    new Product
                    {
                        Name = "Organic Woven Cotton Breeze Blanket",
                        Description = "Woven organic cotton blanket with diamond weave pattern. Perfect lightweight layering for summer.",
                        Category = "Luxury Cashmere & Cotton",
                        Specifications = "{\"material\": \"100% Organic Cotton\", \"weight\": \"2.5 lbs\", \"dimensions\": \"90 x 90 inches (Queen)\", \"washable\": \"Machine washable\"}",
                        ImageUrl = "https://images.unsplash.com/photo-1580301762395-21ce84d00bc6?q=80&w=800",
                        BasePrice = 11900m,
                        IsActive = true,
                        CreatedAt = DateTime.UtcNow,
                        ModifiedAt = DateTime.UtcNow
                    },
                    new Product
                    {
                        Name = "Mulberry Silk Comfort Bed Throw",
                        Description = "Luxury bed throw filled with natural mulberry silk, covered in pure cotton sateen.",
                        Category = "Luxury Cashmere & Cotton",
                        Specifications = "{\"material\": \"Silk Filling & Cotton Sateen Cover\", \"weight\": \"3.0 lbs\", \"dimensions\": \"90 x 96 inches\", \"washable\": \"Dry clean only\"}",
                        ImageUrl = "https://images.unsplash.com/photo-1512310604669-443f26c35f52?q=80&w=800",
                        BasePrice = 38000m,
                        IsActive = true,
                        CreatedAt = DateTime.UtcNow,
                        ModifiedAt = DateTime.UtcNow
                    },
                    new Product
                    {
                        Name = "Quilted Goose Down Winter Comforter",
                        Description = "Heavyweight quilted comforter filled with premium white goose down. Ideal for sub-zero climates.",
                        Category = "Luxury Cashmere & Cotton",
                        Specifications = "{\"material\": \"Goose Down Filling & Egyptian Cotton Shell\", \"weight\": \"5.5 lbs\", \"dimensions\": \"90 x 98 inches (King)\", \"washable\": \"Dry clean recommended\"}",
                        ImageUrl = "https://images.unsplash.com/photo-1583847268964-b28dc8f51f92?q=80&w=800",
                        BasePrice = 32000m,
                        IsActive = true,
                        CreatedAt = DateTime.UtcNow,
                        ModifiedAt = DateTime.UtcNow
                    },
                    new Product
                    {
                        Name = "Waffle Weave Thermal Blanket",
                        Description = "Classic cotton blanket in waffle weave texture. Highly breathable yet retains body heat.",
                        Category = "Luxury Cashmere & Cotton",
                        Specifications = "{\"material\": \"100% Cotton\", \"weight\": \"3.1 lbs\", \"dimensions\": \"66 x 90 inches (Twin)\", \"washable\": \"Machine washable\"}",
                        ImageUrl = "https://images.unsplash.com/photo-1540518614846-7eded433c457?q=80&w=800",
                        BasePrice = 7800m,
                        IsActive = true,
                        CreatedAt = DateTime.UtcNow,
                        ModifiedAt = DateTime.UtcNow
                    },
                    new Product
                    {
                        Name = "Pima Cotton Premium Herringbone Throw",
                        Description = "Woven using long-staple Peruvian Pima cotton. Elegant herringbone pattern with fringe finish.",
                        Category = "Luxury Cashmere & Cotton",
                        Specifications = "{\"material\": \"100% Pima Cotton\", \"weight\": \"2.0 lbs\", \"dimensions\": \"50 x 70 inches\", \"washable\": \"Machine washable\"}",
                        ImageUrl = "https://images.unsplash.com/photo-1545048702-79362596cdc9?q=80&w=800",
                        BasePrice = 13500m,
                        IsActive = true,
                        CreatedAt = DateTime.UtcNow,
                        ModifiedAt = DateTime.UtcNow
                    }
                };

                context.Products.AddRange(products);
                await context.SaveChangesAsync();

                // Seed Distributors
                var distributors = new List<Distributor>
                {
                    new Distributor
                    {
                        Name = "Cozy Logistics",
                        ContactEmail = "quotes@cozylogistics.lk",
                        ContactPhone = "+94-11-2345678",
                        ApiEndpoint = "https://api.cozylogistics.lk/bids",
                        ApiKey = "cl_secure_token_9988",
                        IsActive = true,
                        AverageResponseTimeMs = 950,
                        SuccessRate = 0.94m,
                        TotalQuotations = 420,
                        TotalOrders = 380,
                        CreatedAt = DateTime.UtcNow,
                        ModifiedAt = DateTime.UtcNow
                    },
                    new Distributor
                    {
                        Name = "Comfort Shipping Co",
                        ContactEmail = "wholesale@comfortshipping.com",
                        ContactPhone = "+94-11-8765432",
                        ApiEndpoint = "https://api.comfortshipping.com/quotes",
                        ApiKey = "csc_auth_token_4411",
                        IsActive = true,
                        AverageResponseTimeMs = 720,
                        SuccessRate = 0.96m,
                        TotalQuotations = 850,
                        TotalOrders = 810,
                        CreatedAt = DateTime.UtcNow,
                        ModifiedAt = DateTime.UtcNow
                    },
                    new Distributor
                    {
                        Name = "Woolen Distributors LLC",
                        ContactEmail = "info@woolendistributors.lk",
                        ContactPhone = "+94-31-5558888",
                        ApiEndpoint = "https://api.woolendistributors.lk/quotes",
                        ApiKey = "wd_token_8877",
                        IsActive = true,
                        AverageResponseTimeMs = 1200,
                        SuccessRate = 0.90m,
                        TotalQuotations = 320,
                        TotalOrders = 280,
                        CreatedAt = DateTime.UtcNow,
                        ModifiedAt = DateTime.UtcNow
                    }
                };

                context.Distributors.AddRange(distributors);
                await context.SaveChangesAsync();

                Console.WriteLine("Cozy Comfort database seeded successfully with 26 products!");
            }
            catch (Exception ex)
            {
                Console.WriteLine($"Error seeding database: {ex.Message}");
                throw;
            }
        }
    }
}
