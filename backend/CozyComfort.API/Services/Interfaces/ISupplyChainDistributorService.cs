using CozyComfort.API.Models.DTOs;

namespace CozyComfort.API.Services.Interfaces
{
    public record DistributorResult(
        int AvailableFromStock,
        int Deficit,
        int LeadTimeDays,
        string Details,
        List<FulfillmentTraceNode> Traces
    );

    public interface ISupplyChainDistributorService
    {
        Task<DistributorResult> QueryStockAndLeadTimeAsync(string productId, int quantity);
        Task<bool> PlaceDistributorOrderAsync(string productId, int quantity);
        Task ResetInventoryAsync();
        Task<Dictionary<string, int>> GetInventoryAsync();
    }
}
