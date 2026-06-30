using CozyComfort.API.Services.Interfaces;
using CozyComfort.API.Models.DTOs;

namespace CozyComfort.API.Services.Implementations
{
    public class SupplyChainDistributorService : ISupplyChainDistributorService
    {
        private readonly ISupplyChainManufacturerService _manufacturerService;

        private static readonly Dictionary<string, int> InitialInventory = new()
        {
            { "blanket-001", 8 },  // Cozy Flannel Classic (Standard)
            { "blanket-002", 4 },  // Royal Sherpa Luxury (Premium)
            { "blanket-003", 4 },  // Thermal Woolen Heavy (Premium)
            { "blanket-004", 12 }, // Soft Fleece Lite (Standard)
            { "blanket-005", 10 }, // Woven Cotton Breeze (Standard)
            { "blanket-006", 3 },  // Premium Cashmere Throw (Premium)
            { "blanket-007", 15 }, // All-Weather Microfiber (Standard)
            { "blanket-008", 5 },  // Quilted Down Comfort (Premium)
            { "blanket-009", 6 },  // Weighted Calm Pressure (Premium)
            { "blanket-010", 8 }   // Chenille Luxury Softness (Standard)
        };

        private static readonly Dictionary<string, int> _inventory = new(InitialInventory);
        private static readonly object _lock = new();

        public SupplyChainDistributorService(ISupplyChainManufacturerService manufacturerService)
        {
            _manufacturerService = manufacturerService;
        }

        public async Task<DistributorResult> QueryStockAndLeadTimeAsync(string productId, int quantity)
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
                details = $"Insufficient distributor stock ({currentStock} available). Requesting {deficit} units from Manufacturer.";

                // Query manufacturer for deficit
                var mfgResult = await _manufacturerService.QueryStockAndLeadTimeAsync(productId, deficit);

                // Add Manufacturer trace node
                traces.Add(new FulfillmentTraceNode(
                    "Manufacturer",
                    deficit,
                    mfgResult.AvailableFromStock,
                    mfgResult.Deficit,
                    mfgResult.LeadTimeDays,
                    mfgResult.Details
                ));

                // Distributor lead time is shipping from Manufacturer (1 day) + Manufacturer lead time
                leadTimeDays = 1 + mfgResult.LeadTimeDays;
                details += $" Logistics delay (Manufacturer ➡️ Distributor): 1 day. Subtotal lead time: {leadTimeDays} days.";
            }
            else
            {
                details = $"Sufficient distributor stock ({currentStock} available). Lead time: 0 days.";
            }

            // Add Distributor trace node
            traces.Insert(0, new FulfillmentTraceNode(
                "Distributor",
                quantity,
                available,
                deficit,
                leadTimeDays,
                details
            ));

            return new DistributorResult(available, deficit, leadTimeDays, details, traces);
        }

        public async Task<bool> PlaceDistributorOrderAsync(string productId, int quantity)
        {
            await Task.Delay(150);
            int currentStock = 0;
            lock (_lock)
            {
                currentStock = _inventory.GetValueOrDefault(productId, 0);
            }

            int available = Math.Min(currentStock, quantity);
            int deficit = quantity - available;

            lock (_lock)
            {
                _inventory[productId] = currentStock - available;
            }

            if (deficit > 0)
            {
                await _manufacturerService.PlaceProductionOrderAsync(productId, deficit);
            }

            return true;
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
            await _manufacturerService.ResetInventoryAsync();
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
