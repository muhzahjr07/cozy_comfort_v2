using CozyComfort.API.Services.Interfaces;

namespace CozyComfort.API.Services.Implementations
{
    public class SupplyChainManufacturerService : ISupplyChainManufacturerService
    {
        private static readonly Dictionary<string, int> InitialInventory = new()
        {
            { "blanket-001", 10 }, // Cozy Flannel Classic (Standard)
            { "blanket-002", 5 },  // Royal Sherpa Luxury (Premium)
            { "blanket-003", 5 },  // Thermal Woolen Heavy (Premium)
            { "blanket-004", 15 }, // Soft Fleece Lite (Standard)
            { "blanket-005", 12 }, // Woven Cotton Breeze (Standard)
            { "blanket-006", 4 },  // Premium Cashmere Throw (Premium)
            { "blanket-007", 20 }, // All-Weather Microfiber (Standard)
            { "blanket-008", 6 },  // Quilted Down Comfort (Premium)
            { "blanket-009", 8 },  // Weighted Calm Pressure (Premium)
            { "blanket-010", 10 }  // Chenille Luxury Softness (Standard)
        };

        private static readonly Dictionary<string, int> _inventory = new(InitialInventory);
        private static readonly object _lock = new();

        public async Task<ManufacturerResult> QueryStockAndLeadTimeAsync(string productId, int quantity)
        {
            // Simulate minor network delay
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

            if (deficit > 0)
            {
                // Determine production rate: Standard = 5/day, Premium = 3/day
                bool isPremium = productId == "blanket-002" || productId == "blanket-003" || productId == "blanket-006" || productId == "blanket-008" || productId == "blanket-009";
                int rate = isPremium ? 3 : 5;
                leadTimeDays = (int)Math.Ceiling((double)deficit / rate);
                details = $"Insufficient manufacturer stock ({currentStock} available). Producing {deficit} units at {rate} units/day. Production lead time: {leadTimeDays} days.";
            }
            else
            {
                details = $"Sufficient manufacturer stock ({currentStock} available). Production lead time: 0 days.";
            }

            return new ManufacturerResult(available, deficit, leadTimeDays, details);
        }

        public async Task<bool> PlaceProductionOrderAsync(string productId, int quantity)
        {
            await Task.Delay(150);
            lock (_lock)
            {
                int currentStock = _inventory.GetValueOrDefault(productId, 0);
                if (currentStock >= quantity)
                {
                    _inventory[productId] = currentStock - quantity;
                }
                else
                {
                    _inventory[productId] = 0;
                }
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
