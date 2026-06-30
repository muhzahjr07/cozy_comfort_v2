using CozyComfort.API.Services.Interfaces;
using CozyComfort.API.Models.DTOs;

namespace CozyComfort.API.Services.Implementations
{
    public class SupplyChainSellerService : ISupplyChainSellerService
    {
        private readonly ISupplyChainDistributorService _distributorService;

        private static readonly Dictionary<string, int> InitialInventory = new()
        {
            { "blanket-001", 5 },  // Cozy Flannel Classic (Standard)
            { "blanket-002", 2 },  // Royal Sherpa Luxury (Premium)
            { "blanket-003", 2 },  // Thermal Woolen Heavy (Premium)
            { "blanket-004", 8 },  // Soft Fleece Lite (Standard)
            { "blanket-005", 6 },  // Woven Cotton Breeze (Standard)
            { "blanket-006", 1 },  // Premium Cashmere Throw (Premium)
            { "blanket-007", 10 }, // All-Weather Microfiber (Standard)
            { "blanket-008", 2 },  // Quilted Down Comfort (Premium)
            { "blanket-009", 3 },  // Weighted Calm Pressure (Premium)
            { "blanket-010", 4 }   // Chenille Luxury Softness (Standard)
        };

        private static readonly Dictionary<string, decimal> RetailPrices = new()
        {
            { "blanket-001", 3500m },
            { "blanket-002", 6500m },
            { "blanket-003", 8500m },
            { "blanket-004", 2000m },
            { "blanket-005", 2800m },
            { "blanket-006", 12500m },
            { "blanket-007", 1800m },
            { "blanket-008", 9500m },
            { "blanket-009", 7500m },
            { "blanket-010", 4200m }
        };

        private static readonly Dictionary<string, int> _inventory = new(InitialInventory);
        private static readonly object _lock = new();

        public SupplyChainSellerService(ISupplyChainDistributorService distributorService)
        {
            _distributorService = distributorService;
        }

        public async Task<SupplyChainQueryResponse> QueryBlanketFulfillmentAsync(string productId, int quantity)
        {
            await Task.Delay(100);

            int currentStock = 0;
            lock (_lock)
            {
                currentStock = _inventory.GetValueOrDefault(productId, 0);
            }

            int available = Math.Min(currentStock, quantity);
            int deficit = quantity - available;
            int leadTimeDays = 0;
            string details = "";
            var traces = new List<FulfillmentTraceNode>();

            if (deficit > 0)
            {
                details = $"Insufficient seller stock ({currentStock} available). Requesting {deficit} units from Distributor.";

                // Query distributor for deficit
                var distResult = await _distributorService.QueryStockAndLeadTimeAsync(productId, deficit);

                // Collect upstream traces
                traces.AddRange(distResult.Traces);

                // Seller lead time is shipping from Distributor (1 day) + Distributor lead time
                leadTimeDays = 1 + distResult.LeadTimeDays;
                details += $" Logistics delay (Distributor ➡️ Seller): 1 day. Subtotal lead time: {leadTimeDays} days.";
            }
            else
            {
                details = $"Sufficient seller stock ({currentStock} available). Lead time: 0 days.";
            }

            // Add Seller trace node at the front
            traces.Insert(0, new FulfillmentTraceNode(
                "Seller",
                quantity,
                available,
                deficit,
                leadTimeDays,
                details
            ));

            decimal pricePerUnit = RetailPrices.GetValueOrDefault(productId, 5000m);
            decimal totalPrice = pricePerUnit * quantity;
            int finalAvailableQuantity = quantity;

            return new SupplyChainQueryResponse(
                productId,
                quantity,
                finalAvailableQuantity,
                leadTimeDays,
                pricePerUnit,
                totalPrice,
                traces
            );
        }

        public async Task<SupplyChainOrderResponse> PlaceBlanketOrderAsync(List<FulfillmentRequestItem> items, string customerId)
        {
            var orderItems = new List<FulfillmentOrderItem>();
            decimal totalAmount = 0;

            foreach (var item in items)
            {
                var queryResult = await QueryBlanketFulfillmentAsync(item.ProductId, item.Quantity);

                int currentStock = 0;
                lock (_lock)
                {
                    currentStock = _inventory.GetValueOrDefault(item.ProductId, 0);
                }

                int available = Math.Min(currentStock, item.Quantity);
                int deficit = item.Quantity - available;

                lock (_lock)
                {
                    _inventory[item.ProductId] = currentStock - available;
                }

                if (deficit > 0)
                {
                    await _distributorService.PlaceDistributorOrderAsync(item.ProductId, deficit);
                }

                orderItems.Add(new FulfillmentOrderItem(
                    item.ProductId,
                    item.Quantity,
                    queryResult.PricePerUnit,
                    queryResult.TotalLeadTimeDays,
                    "Fulfillment Processed"
                ));

                totalAmount += queryResult.TotalPrice;
            }

            return new SupplyChainOrderResponse(
                $"ORD-{Guid.NewGuid().ToString().Substring(0, 8).ToUpper()}",
                customerId,
                orderItems,
                "Confirmed",
                totalAmount,
                DateTime.UtcNow,
                $"TRK-{Guid.NewGuid().ToString().Substring(0, 10).ToUpper()}"
            );
        }

        public async Task ResetInventoryAsync()
        {
            await Task.Delay(50);
            lock (_lock)
            {
                foreach (var kvp in InitialInventory)
                {
                    _inventory[kvp.Key] = kvp.Value;
                }
            }
            await _distributorService.ResetInventoryAsync();
        }

        public async Task<Dictionary<string, int>> GetInventoryAsync()
        {
            await Task.Delay(10);
            lock (_lock)
            {
                return new Dictionary<string, int>(_inventory);
            }
        }
    }
}
