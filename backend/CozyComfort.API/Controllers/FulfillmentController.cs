using Microsoft.AspNetCore.Mvc;
using CozyComfort.API.Models.DTOs;
using CozyComfort.API.Services.Interfaces;

namespace CozyComfort.API.Controllers
{
    [ApiController]
    [Route("api/[controller]")]
    public class FulfillmentController : ControllerBase
    {
        private readonly ISupplyChainSellerService _sellerService;
        private readonly ISupplyChainDistributorService _distributorService;
        private readonly ISupplyChainManufacturerService _manufacturerService;

        public FulfillmentController(
            ISupplyChainSellerService sellerService,
            ISupplyChainDistributorService distributorService,
            ISupplyChainManufacturerService manufacturerService)
        {
            _sellerService = sellerService;
            _distributorService = distributorService;
            _manufacturerService = manufacturerService;
        }

        [HttpPost("check-stock")]
        public async Task<IActionResult> CheckStock([FromBody] List<FulfillmentRequestItem> items)
        {
            if (items == null || !items.Any())
            {
                return BadRequest("Request items cannot be empty.");
            }

            var results = new List<SupplyChainQueryResponse>();
            foreach (var item in items)
            {
                var result = await _sellerService.QueryBlanketFulfillmentAsync(item.ProductId, item.Quantity);
                results.Add(result);
            }

            return Ok(results);
        }

        [HttpPost("place-order")]
        public async Task<IActionResult> PlaceOrder([FromBody] List<FulfillmentRequestItem> items)
        {
            if (items == null || !items.Any())
            {
                return BadRequest("Request items cannot be empty.");
            }

            string customerId = "CUST-COZY-001";
            var order = await _sellerService.PlaceBlanketOrderAsync(items, customerId);
            return Ok(order);
        }

        [HttpPost("reset")]
        public async Task<IActionResult> ResetInventory()
        {
            await _sellerService.ResetInventoryAsync();
            return Ok(new { message = "Inventory reset successful." });
        }

        [HttpGet("inventory")]
        public async Task<IActionResult> GetInventoryState()
        {
            var sellerInv = await _sellerService.GetInventoryAsync();
            var distInv = await _distributorService.GetInventoryAsync();
            var mfgInv = await _manufacturerService.GetInventoryAsync();

            return Ok(new
            {
                seller = sellerInv,
                distributor = distInv,
                manufacturer = mfgInv
            });
        }
    }
}
