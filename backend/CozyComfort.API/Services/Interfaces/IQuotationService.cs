using CozyComfort.API.Models.Domain;
using CozyComfort.API.Models.DTOs;

namespace CozyComfort.API.Services.Interfaces
{
    public interface IQuotationService
    {
        Task<List<QuotationResponse>> RequestQuotationsAsync(Order order);
        Task<bool> ProcessQuotationResponseAsync(CreateQuotationRequest request);
        Task<QuotationComparisonResponse> CompareQuotationsAsync(int orderId);
        Task<DistributorResponse> SelectBestDistributorAsync(int orderId);
        Task<List<QuotationResponse>> GetQuotationHistoryAsync(int orderId);
    }
}
