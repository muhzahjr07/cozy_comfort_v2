using CozyComfort.API.Models.DTOs;

namespace CozyComfort.API.Services.Interfaces
{
    public record ManufacturerResult(
        int AvailableFromStock,
        int Deficit,
        int LeadTimeDays,
        string Details
    );

    public interface ISupplyChainManufacturerService
    {
        Task<ManufacturerResult> QueryStockAndLeadTimeAsync(string productId, int quantity);
        Task<bool> PlaceProductionOrderAsync(string productId, int quantity);
        Task ResetInventoryAsync();
        Task<Dictionary<string, int>> GetInventoryAsync();
    }
}
