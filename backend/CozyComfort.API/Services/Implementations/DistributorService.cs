using AutoMapper;
using CozyComfort.API.Models.Domain;
using CozyComfort.API.Models.DTOs;
using CozyComfort.API.Repositories;
using CozyComfort.API.Services.Interfaces;

namespace CozyComfort.API.Services.Implementations
{
    public class DistributorService : IDistributorService
    {
        private readonly IRepository<Distributor> _distributorRepository;
        private readonly IMapper _mapper;
        private readonly ILogger<DistributorService> _logger;
        private readonly IHttpClientFactory _httpClientFactory;

        public DistributorService(
            IRepository<Distributor> distributorRepository,
            IMapper mapper,
            ILogger<DistributorService> logger,
            IHttpClientFactory httpClientFactory)
        {
            _distributorRepository = distributorRepository;
            _mapper = mapper;
            _logger = logger;
            _httpClientFactory = httpClientFactory;
        }

        public async Task<List<DistributorResponse>> GetAllDistributorsAsync()
        {
            var distributors = await _distributorRepository.GetAllAsync();
            return distributors.Select(_mapper.Map<DistributorResponse>).ToList();
        }

        public async Task<DistributorResponse> GetDistributorByIdAsync(int distributorId)
        {
            var distributor = await _distributorRepository.GetByIdAsync(distributorId);
            if (distributor == null)
                throw new KeyNotFoundException($"Distributor with ID {distributorId} not found");

            return _mapper.Map<DistributorResponse>(distributor);
        }

        public async Task<bool> CheckDistributorAvailabilityAsync(int distributorId)
        {
            var distributor = await _distributorRepository.GetByIdAsync(distributorId);
            if (distributor == null || !distributor.IsActive)
                return false;

            // Simulate availability check - in real implementation, this would ping the distributor's API
            try
            {
                using var client = _httpClientFactory.CreateClient();
                client.Timeout = TimeSpan.FromSeconds(5);
                
                // Simulate API health check
                await Task.Delay(100); // Simulate network call
                
                // Randomly simulate some distributors being unavailable
                var random = new Random();
                var isAvailable = random.Next(1, 11) > 1; // 90% availability rate
                
                _logger.LogInformation($"Distributor {distributor.Name} availability: {isAvailable}");
                return isAvailable;
            }
            catch (Exception ex)
            {
                _logger.LogWarning(ex, $"Failed to check availability for distributor {distributor.Name}");
                return false;
            }
        }

        public async Task<DistributorResponse> CreateDistributorAsync(CreateDistributorRequest request)
        {
            var distributor = _mapper.Map<Distributor>(request);
            distributor.CreatedAt = DateTime.UtcNow;
            distributor.ModifiedAt = DateTime.UtcNow;
            distributor.IsActive = true;

            await _distributorRepository.AddAsync(distributor);
            await _distributorRepository.SaveChangesAsync();

            _logger.LogInformation($"Created distributor: {distributor.Name}");
            return _mapper.Map<DistributorResponse>(distributor);
        }

        public async Task<DistributorResponse> UpdateDistributorAsync(int distributorId, UpdateDistributorRequest request)
        {
            var distributor = await _distributorRepository.GetByIdAsync(distributorId);
            if (distributor == null)
                throw new KeyNotFoundException($"Distributor with ID {distributorId} not found");

            _mapper.Map(request, distributor);
            distributor.ModifiedAt = DateTime.UtcNow;

            _distributorRepository.Update(distributor);
            await _distributorRepository.SaveChangesAsync();

            _logger.LogInformation($"Updated distributor: {distributor.Name}");
            return _mapper.Map<DistributorResponse>(distributor);
        }

        public async Task<bool> DeleteDistributorAsync(int distributorId)
        {
            var distributor = await _distributorRepository.GetByIdAsync(distributorId);
            if (distributor == null)
                return false;

            distributor.IsActive = false;
            distributor.ModifiedAt = DateTime.UtcNow;

            _distributorRepository.Update(distributor);
            await _distributorRepository.SaveChangesAsync();

            _logger.LogInformation($"Deactivated distributor: {distributor.Name}");
            return true;
        }

        public async Task<DistributorPerformanceResponse> GetDistributorPerformanceAsync(int distributorId, DateTime? startDate = null, DateTime? endDate = null)
        {
            var distributor = await _distributorRepository.GetByIdAsync(distributorId);
            if (distributor == null)
                throw new KeyNotFoundException($"Distributor with ID {distributorId} not found");

            var start = startDate ?? DateTime.UtcNow.AddMonths(-1);
            var end = endDate ?? DateTime.UtcNow;

            // In a real implementation, this would query actual performance data
            // For now, return simulated performance metrics
            return new DistributorPerformanceResponse
            {
                DistributorId = distributorId,
                DistributorName = distributor.Name,
                TotalQuotations = distributor.TotalQuotations,
                SuccessfulQuotations = (int)(distributor.TotalQuotations * 0.85), // 85% success rate simulation
                SuccessRate = distributor.SuccessRate,
                AverageResponseTimeMs = distributor.AverageResponseTimeMs,
                TotalOrders = distributor.TotalOrders,
                TotalRevenue = distributor.TotalOrders * 1500m, // Simulated average order value
                PeriodStart = start,
                PeriodEnd = end,
                DailyMetrics = GenerateDailyMetrics(start, end)
            };
        }

        private List<DailyPerformanceMetric> GenerateDailyMetrics(DateTime startDate, DateTime endDate)
        {
            var metrics = new List<DailyPerformanceMetric>();
            var random = new Random();

            for (var date = startDate.Date; date <= endDate.Date; date = date.AddDays(1))
            {
                metrics.Add(new DailyPerformanceMetric
                {
                    Date = date,
                    Quotations = random.Next(0, 10),
                    Orders = random.Next(0, 5),
                    AverageResponseTimeMs = random.Next(100, 2000)
                });
            }

            return metrics;
        }
    }
}
