using AutoMapper;
using CozyComfort.API.Models.Domain;
using CozyComfort.API.Models.DTOs;
using Newtonsoft.Json;

namespace CozyComfort.API.Configurations
{
    public class MappingProfile : Profile
    {
        public MappingProfile()
        {
            // User mappings
            CreateMap<RegisterRequest, User>()
                .ForMember(dest => dest.UserId, opt => opt.Ignore())
                .ForMember(dest => dest.PasswordHash, opt => opt.Ignore())
                .ForMember(dest => dest.Role, opt => opt.MapFrom(src => "Customer"))
                .ForMember(dest => dest.IsActive, opt => opt.MapFrom(src => true))
                .ForMember(dest => dest.CreatedAt, opt => opt.MapFrom(src => DateTime.UtcNow));
            
            CreateMap<User, UserProfileResponse>();

            // Customer mappings
            CreateMap<Customer, CustomerResponse>()
                .ForMember(dest => dest.Email, opt => opt.MapFrom(src => src.User.Email))
                .ForMember(dest => dest.PreferredDistributorName, opt => opt.MapFrom(src => 
                    src.PreferredDistributor != null ? src.PreferredDistributor.Name : null));

            CreateMap<UpdateCustomerRequest, Customer>()
                .ForAllMembers(opts => opts.Condition((src, dest, srcMember) => srcMember != null));

            // Order mappings
            CreateMap<CreateOrderRequest, Order>()
                .ForMember(dest => dest.OrderId, opt => opt.Ignore())
                .ForMember(dest => dest.OrderReference, opt => opt.Ignore())
                .ForMember(dest => dest.OrderDate, opt => opt.MapFrom(src => DateTime.UtcNow))
                .ForMember(dest => dest.Status, opt => opt.MapFrom(src => Constants.OrderStatus.PENDING))
                .ForMember(dest => dest.CreatedAt, opt => opt.MapFrom(src => DateTime.UtcNow))
                .ForMember(dest => dest.ModifiedAt, opt => opt.MapFrom(src => DateTime.UtcNow));

            CreateMap<OrderItemRequest, OrderItem>()
                .ForMember(dest => dest.OrderItemId, opt => opt.Ignore())
                .ForMember(dest => dest.TotalPrice, opt => opt.MapFrom(src => src.Quantity * src.UnitPrice))
                .ForMember(dest => dest.CreatedAt, opt => opt.MapFrom(src => DateTime.UtcNow));

            CreateMap<Order, OrderResponse>()
                .ForMember(dest => dest.SelectedDistributorName, opt => opt.MapFrom(src => 
                    src.SelectedDistributor != null ? src.SelectedDistributor.Name : null));

            CreateMap<Order, OrderSummaryResponse>()
                .ForMember(dest => dest.ItemCount, opt => opt.MapFrom(src => src.OrderItems.Count))
                .ForMember(dest => dest.DistributorName, opt => opt.MapFrom(src =>
                    src.SelectedDistributor != null ? src.SelectedDistributor.Name : null));

            CreateMap<OrderItem, OrderItemResponse>()
                .ForMember(dest => dest.ProductName, opt => opt.MapFrom(src => src.Product.Name));

            // Product mappings
            CreateMap<Product, ProductResponse>()
                .ForMember(dest => dest.Specifications, opt => opt.MapFrom(src => 
                    string.IsNullOrEmpty(src.Specifications) 
                        ? new Dictionary<string, string>() 
                        : JsonConvert.DeserializeObject<Dictionary<string, string>>(src.Specifications) ?? new Dictionary<string, string>()));

            CreateMap<Product, ProductSummaryResponse>();

            CreateMap<CreateProductRequest, Product>()
                .ForMember(dest => dest.ProductId, opt => opt.Ignore())
                .ForMember(dest => dest.Specifications, opt => opt.MapFrom(src => 
                    JsonConvert.SerializeObject(src.Specifications)))
                .ForMember(dest => dest.IsActive, opt => opt.MapFrom(src => true))
                .ForMember(dest => dest.CreatedAt, opt => opt.MapFrom(src => DateTime.UtcNow))
                .ForMember(dest => dest.ModifiedAt, opt => opt.MapFrom(src => DateTime.UtcNow));

            // Quotation mappings
            CreateMap<CreateQuotationRequest, Quotation>()
                .ForMember(dest => dest.QuotationId, opt => opt.Ignore())
                .ForMember(dest => dest.Status, opt => opt.MapFrom(src => Constants.QuotationStatus.RECEIVED))
                .ForMember(dest => dest.RequestedAt, opt => opt.MapFrom(src => DateTime.UtcNow))
                .ForMember(dest => dest.ReceivedAt, opt => opt.MapFrom(src => DateTime.UtcNow))
                .ForMember(dest => dest.CreatedAt, opt => opt.MapFrom(src => DateTime.UtcNow));

            CreateMap<QuotationItemRequest, QuotationItem>()
                .ForMember(dest => dest.QuotationItemId, opt => opt.Ignore())
                .ForMember(dest => dest.TotalPrice, opt => opt.MapFrom(src => src.PricePerUnit * src.RequestedUnits))
                .ForMember(dest => dest.CreatedAt, opt => opt.MapFrom(src => DateTime.UtcNow));

            CreateMap<Quotation, QuotationResponse>()
                .ForMember(dest => dest.DistributorName, opt => opt.MapFrom(src => src.Distributor.Name));

            CreateMap<QuotationItem, QuotationItemResponse>()
                .ForMember(dest => dest.ProductName, opt => opt.MapFrom(src => src.Product.Name));

            // Distributor mappings
            CreateMap<CreateDistributorRequest, Distributor>()
                .ForMember(dest => dest.DistributorId, opt => opt.Ignore())
                .ForMember(dest => dest.IsActive, opt => opt.MapFrom(src => true))
                .ForMember(dest => dest.AverageResponseTimeMs, opt => opt.MapFrom(src => 0))
                .ForMember(dest => dest.SuccessRate, opt => opt.MapFrom(src => 0))
                .ForMember(dest => dest.TotalQuotations, opt => opt.MapFrom(src => 0))
                .ForMember(dest => dest.TotalOrders, opt => opt.MapFrom(src => 0))
                .ForMember(dest => dest.CreatedAt, opt => opt.MapFrom(src => DateTime.UtcNow))
                .ForMember(dest => dest.ModifiedAt, opt => opt.MapFrom(src => DateTime.UtcNow));

            CreateMap<UpdateDistributorRequest, Distributor>()
                .ForMember(dest => dest.ModifiedAt, opt => opt.MapFrom(src => DateTime.UtcNow))
                .ForAllMembers(opts => opts.Condition((src, dest, srcMember) => srcMember != null));

            CreateMap<Distributor, DistributorResponse>()
                .ForMember(dest => dest.TotalValue, opt => opt.MapFrom(src => src.TotalOrders * 1500m))
                .ForMember(dest => dest.Rating, opt => opt.MapFrom(src => src.SuccessRate > 0 ? (decimal)src.SuccessRate / 20m : 4.5m));
        }
    }
}
