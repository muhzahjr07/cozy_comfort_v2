using System.ComponentModel.DataAnnotations;
using System.Text.Json.Serialization;

namespace CozyComfort.API.Models.DTOs
{
    public record BlanketModel(
        [property: JsonPropertyName("id")] string Id,
        [property: JsonPropertyName("name")] string Name,
        [property: JsonPropertyName("material")] string Material,
        [property: JsonPropertyName("basePrice")] decimal BasePrice,
        [property: JsonPropertyName("image")] string Image,
        [property: JsonPropertyName("description")] string Description,
        [property: JsonPropertyName("category")] string Category
    );

    public record FulfillmentRequestItem(
        [Required]
        [property: JsonPropertyName("productId")] string ProductId,

        [Required]
        [Range(1, int.MaxValue, ErrorMessage = "Quantity must be at least 1")]
        [property: JsonPropertyName("quantity")] int Quantity
    );

    public record FulfillmentTraceNode(
        [property: JsonPropertyName("tierName")] string TierName, // "Seller", "Distributor", "Manufacturer"
        [property: JsonPropertyName("checkedQuantity")] int CheckedQuantity,
        [property: JsonPropertyName("availableFromStock")] int AvailableFromStock,
        [property: JsonPropertyName("deficit")] int Deficit,
        [property: JsonPropertyName("leadTimeDays")] int LeadTimeDays,
        [property: JsonPropertyName("details")] string Details
    );

    public record SupplyChainQueryResponse(
        [property: JsonPropertyName("productId")] string ProductId,
        [property: JsonPropertyName("requestedQuantity")] int RequestedQuantity,
        [property: JsonPropertyName("availableQuantity")] int AvailableQuantity,
        [property: JsonPropertyName("totalLeadTimeDays")] int TotalLeadTimeDays,
        [property: JsonPropertyName("pricePerUnit")] decimal PricePerUnit,
        [property: JsonPropertyName("totalPrice")] decimal TotalPrice,
        [property: JsonPropertyName("traces")] List<FulfillmentTraceNode> Traces
    );

    public record FulfillmentOrderItem(
        [property: JsonPropertyName("productId")] string ProductId,
        [property: JsonPropertyName("quantity")] int Quantity,
        [property: JsonPropertyName("unitPrice")] decimal UnitPrice,
        [property: JsonPropertyName("leadTimeDays")] int LeadTimeDays,
        [property: JsonPropertyName("status")] string Status
    );

    public record SupplyChainOrderResponse(
        [property: JsonPropertyName("id")] string Id,
        [property: JsonPropertyName("customerId")] string CustomerId,
        [property: JsonPropertyName("items")] List<FulfillmentOrderItem> Items,
        [property: JsonPropertyName("status")] string Status,
        [property: JsonPropertyName("totalAmount")] decimal TotalAmount,
        [property: JsonPropertyName("createdAt")] DateTime CreatedAt,
        [property: JsonPropertyName("trackingId")] string TrackingId
    );
}
