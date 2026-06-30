using CozyComfort.API.Models.DTOs;

namespace CozyComfort.API.Services.Interfaces
{
    public interface ISupplyChainSellerService
    {
        Task<SupplyChainQueryResponse> QueryBlanketFulfillmentAsync(string productId, int quantity);
        Task<SupplyChainOrderResponse> PlaceBlanketOrderAsync(List<FulfillmentRequestItem> items, string customerId);
        Task ResetInventoryAsync();
        Task<Dictionary<string, int>> GetInventoryAsync();
    }
}
