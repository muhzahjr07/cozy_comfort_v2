using AutoMapper;
using CozyComfort.API.Models.Domain;
using CozyComfort.API.Models.DTOs;
using CozyComfort.API.Services.Interfaces;
using CozyComfort.API.Repositories;
using CozyComfort.API.Exceptions;
using CozyComfort.API.Constants;
using Newtonsoft.Json;
using System.Text;

namespace CozyComfort.API.Services.Implementations
{
    public class QuotationService : IQuotationService
    {
        private readonly IRepository<Quotation> _quotationRepository;
        private readonly IRepository<Distributor> _distributorRepository;
        private readonly IOrderRepository _orderRepository;
        private readonly IMapper _mapper;
        private readonly ILogger<QuotationService> _logger;
        private readonly IHttpClientFactory _httpClientFactory;

        private readonly Dictionary<string, string> _distributorEndpoints = new()
        {
            { "Cozy Logistics", "https://api.cozylogistics.lk/bids" },
            { "Comfort Shipping Co", "https://api.comfortshipping.com/quotes" },
            { "Woolen Distributors LLC", "https://api.woolendistributors.lk/quotes" }
        };

        public QuotationService(
            IRepository<Quotation> quotationRepository,
            IRepository<Distributor> distributorRepository,
            IOrderRepository orderRepository,
            IMapper mapper,
            ILogger<QuotationService> logger,
            IHttpClientFactory httpClientFactory)
        {
            _quotationRepository = quotationRepository;
            _distributorRepository = distributorRepository;
            _orderRepository = orderRepository;
            _mapper = mapper;
            _logger = logger;
            _httpClientFactory = httpClientFactory;
        }

        public async Task<List<QuotationResponse>> RequestQuotationsAsync(Order order)
        {
            _logger.LogInformation($"Requesting quotations for order {order.OrderId}");
            
            var distributors = await _distributorRepository.GetAllAsync();
            var quotations = new List<QuotationResponse>();
            
            var quotationTasks = distributors.Select(async distributor =>
            {
                try
                {
                    return await RequestQuotationFromDistributorAsync(order, distributor);
                }
                catch (Exception ex)
                {
                    _logger.LogError(ex, $"Failed to get quotation from {distributor.Name}");
                    return null;
                }
            });

            var results = await Task.WhenAll(quotationTasks);
            quotations.AddRange(results.Where(q => q != null).Cast<QuotationResponse>());

            _logger.LogInformation($"Received {quotations.Count} quotations for order {order.OrderId}");
            return quotations;
        }

        private async Task<QuotationResponse?> RequestQuotationFromDistributorAsync(Order order, Distributor distributor)
        {
            // Simulate distributor quotation response - in real implementation, this would call actual APIs
            await Task.Delay(new Random().Next(500, 2000)); // Simulate network delay

            var quotation = new Quotation
            {
                OrderId = order.OrderId,
                DistributorId = distributor.DistributorId,
                Status = QuotationStatus.RECEIVED,
                RequestedAt = DateTime.UtcNow,
                ReceivedAt = DateTime.UtcNow,
                ExpiresAt = DateTime.UtcNow.AddDays(7),
                EstimatedDeliveryTime = DateTime.UtcNow.AddDays(new Random().Next(1, 14)),
                CreatedAt = DateTime.UtcNow
            };

            // Generate simulated quotation items with varying prices
            var quotationItems = new List<QuotationItem>();
            decimal totalAmount = 0;
            
            foreach (var orderItem in order.OrderItems)
            {
                var priceVariation = 1m + ((decimal)new Random().NextDouble() * 0.4m - 0.2m); // ±20% price variation
                var unitPrice = Math.Round(orderItem.UnitPrice * priceVariation, 2);
                var availability = Math.Min(orderItem.Quantity, new Random().Next(0, orderItem.Quantity + 10));
                
                var quotationItem = new QuotationItem
                {
                    ProductId = orderItem.ProductId,
                    RequestedUnits = orderItem.Quantity,
                    AvailableUnits = availability,
                    PricePerUnit = unitPrice,
                    TotalPrice = unitPrice * availability,
                    CreatedAt = DateTime.UtcNow
                };
                
                quotationItems.Add(quotationItem);
                totalAmount += quotationItem.TotalPrice;
            }
            
            quotation.TotalCost = totalAmount;
            quotation.RequestedUnits = order.OrderItems.Sum(oi => oi.Quantity);
            quotation.AvailableUnits = quotationItems.Sum(qi => qi.AvailableUnits);
            quotation.QuotationItems = quotationItems;
            
            // Save quotation to database
            await _quotationRepository.AddAsync(quotation);
            await _quotationRepository.SaveChangesAsync();
            
            return _mapper.Map<QuotationResponse>(quotation);
        }

        public async Task<bool> ProcessQuotationResponseAsync(CreateQuotationRequest request)
        {
            _logger.LogInformation($"Processing quotation for order {request.OrderId}");
            
            var quotation = _mapper.Map<Quotation>(request);
            quotation.Status = QuotationStatus.RECEIVED;
            quotation.CreatedAt = DateTime.UtcNow;
            
            await _quotationRepository.AddAsync(quotation);
            await _quotationRepository.SaveChangesAsync();
            
            return true;
        }

        public async Task<QuotationComparisonResponse> CompareQuotationsAsync(int orderId)
        {
            var order = await _orderRepository.GetOrderWithQuotationsAsync(orderId);
            if (order == null)
                throw new NotFoundException($"Order {orderId} not found");

            var quotations = order.Quotations.Where(q => q.Status == QuotationStatus.RECEIVED).ToList();
            
            if (!quotations.Any())
            {
                return new QuotationComparisonResponse
                {
                    OrderId = orderId,
                    ComparedAt = DateTime.UtcNow,
                    ComparisonSummary = "No quotations available for comparison",
                    Quotations = new List<QuotationResponse>()
                };
            }

            // Find best quotations by different criteria
            var bestByPrice = quotations.OrderBy(q => q.TotalCost).First();
            var bestByDelivery = quotations.OrderBy(q => q.EstimatedDeliveryTime).First();
            var bestByAvailability = quotations.OrderByDescending(q => q.AvailableUnits).First();

            var bestQuotations = new List<Quotation> { bestByPrice };
            if (bestByDelivery.QuotationId != bestByPrice.QuotationId)
                bestQuotations.Add(bestByDelivery);
            if (bestByAvailability.QuotationId != bestByPrice.QuotationId && 
                bestByAvailability.QuotationId != bestByDelivery.QuotationId)
                bestQuotations.Add(bestByAvailability);

            var summary = $"Analyzed {quotations.Count} quotations. " +
                         $"Best price: ${bestByPrice.TotalCost:F2}, " +
                         $"Fastest delivery: {(bestByDelivery.EstimatedDeliveryTime - DateTime.UtcNow).Days} days";

            return new QuotationComparisonResponse
            {
                OrderId = orderId,
                ComparedAt = DateTime.UtcNow,
                ComparisonSummary = summary,
                Quotations = quotations.Select(_mapper.Map<QuotationResponse>).ToList(),
                RecommendedQuotation = _mapper.Map<QuotationResponse>(bestQuotations.First())
            };
        }

        public async Task<DistributorResponse> SelectBestDistributorAsync(int orderId)
        {
            var order = await _orderRepository.GetOrderWithQuotationsAsync(orderId);
            if (order == null)
                throw new NotFoundException($"Order {orderId} not found");

            var quotations = order.Quotations.Where(q => q.Status == QuotationStatus.RECEIVED).ToList();
            
            if (!quotations.Any())
                throw new BadRequestException("No quotations available for distributor selection");

            // Scoring algorithm: 40% price, 30% delivery time, 30% availability
            var scoredQuotations = quotations.Select(q => new
            {
                Quotation = q,
                Score = CalculateQuotationScore(q, quotations)
            }).OrderByDescending(x => x.Score);

            var bestQuotation = scoredQuotations.First().Quotation;
            var distributor = await _distributorRepository.GetByIdAsync(bestQuotation.DistributorId);

            // Update order with selected distributor
            order.SelectedDistributorId = distributor?.DistributorId ?? bestQuotation.DistributorId;
            order.Status = OrderStatus.PROCESSING;
            order.ModifiedAt = DateTime.UtcNow;
            
            _orderRepository.Update(order);
            await _orderRepository.SaveChangesAsync();

            var distributorName = distributor?.Name ?? "Unknown";
            _logger.LogInformation($"Selected distributor {distributorName} for order {orderId}");

            return distributor != null ? _mapper.Map<DistributorResponse>(distributor) : new DistributorResponse();
        }

        private decimal CalculateQuotationScore(Quotation quotation, List<Quotation> allQuotations)
        {
            if (!allQuotations.Any()) return 0m;
            
            // Normalize scores to 0-1 range
            var minPrice = allQuotations.Min(q => q.TotalCost);
            var maxPrice = allQuotations.Max(q => q.TotalCost);
            var priceRange = maxPrice - minPrice;
            var priceScore = priceRange == 0 ? 1m : 1m - (quotation.TotalCost - minPrice) / priceRange;
            
            var minDelivery = allQuotations.Min(q => q.EstimatedDeliveryTime);
            var maxDelivery = allQuotations.Max(q => q.EstimatedDeliveryTime);
            var deliveryRange = (maxDelivery - minDelivery).TotalDays;
            var deliveryScore = deliveryRange == 0 ? 1m : 1m - (decimal)((quotation.EstimatedDeliveryTime - minDelivery).TotalDays / deliveryRange);
            
            var maxAvailability = allQuotations.Max(q => q.AvailableUnits);
            var availabilityScore = maxAvailability == 0 ? 1m : (decimal)quotation.AvailableUnits / maxAvailability;

            return (priceScore * 0.4m) + (deliveryScore * 0.3m) + (availabilityScore * 0.3m);
        }

        public async Task<List<QuotationResponse>> GetQuotationHistoryAsync(int orderId)
        {
            var quotations = await _quotationRepository.FindAsync(q => q.OrderId == orderId);
            return quotations.Select(_mapper.Map<QuotationResponse>).ToList();
        }
    }
}
