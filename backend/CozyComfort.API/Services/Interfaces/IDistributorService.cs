using CozyComfort.API.Models.Domain;
using CozyComfort.API.Models.DTOs;

namespace CozyComfort.API.Services.Interfaces
{
    public interface IDistributorService
    {
        Task<List<DistributorResponse>> GetAllDistributorsAsync();
        Task<DistributorResponse> GetDistributorByIdAsync(int distributorId);
        Task<DistributorResponse> CreateDistributorAsync(CreateDistributorRequest request);
        Task<DistributorResponse> UpdateDistributorAsync(int distributorId, UpdateDistributorRequest request);
        Task<bool> DeleteDistributorAsync(int distributorId);
        Task<DistributorPerformanceResponse> GetDistributorPerformanceAsync(int distributorId, DateTime? startDate = null, DateTime? endDate = null);
        Task<bool> CheckDistributorAvailabilityAsync(int distributorId);
    }
}
