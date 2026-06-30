using Microsoft.AspNetCore.Mvc;
using CozyComfort.API.Models.DTOs;
using CozyComfort.API.Services.Interfaces;

namespace CozyComfort.API.Controllers
{
    [ApiController]
    [Route("api/[controller]")]
    public class QuotationsController : ControllerBase
    {
        private readonly IQuotationService _quotationService;
        private readonly ILogger<QuotationsController> _logger;

        public QuotationsController(IQuotationService quotationService, ILogger<QuotationsController> logger)
        {
            _quotationService = quotationService;
            _logger = logger;
        }

        [HttpGet("{orderId}/compare")]
        public async Task<ActionResult<ApiResponse<QuotationComparisonResponse>>> CompareQuotations(int orderId)
        {
            try
            {
                var result = await _quotationService.CompareQuotationsAsync(orderId);
                return Ok(ApiResponse<QuotationComparisonResponse>.SuccessResult(result, "Quotations compared successfully"));
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Error comparing quotations for order {orderId}");
                return BadRequest(ApiResponse<QuotationComparisonResponse>.ErrorResult(ex.Message));
            }
        }

        [HttpPost("{orderId}/select-distributor")]
        public async Task<ActionResult<ApiResponse<DistributorResponse>>> SelectBestDistributor(int orderId)
        {
            try
            {
                var result = await _quotationService.SelectBestDistributorAsync(orderId);
                return Ok(ApiResponse<DistributorResponse>.SuccessResult(result, "Best distributor selected successfully"));
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Error selecting distributor for order {orderId}");
                return BadRequest(ApiResponse<DistributorResponse>.ErrorResult(ex.Message));
            }
        }

        [HttpGet("{orderId}/history")]
        public async Task<ActionResult<ApiResponse<List<QuotationResponse>>>> GetQuotationHistory(int orderId)
        {
            try
            {
                var result = await _quotationService.GetQuotationHistoryAsync(orderId);
                return Ok(ApiResponse<List<QuotationResponse>>.SuccessResult(result));
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Error getting quotation history for order {orderId}");
                return BadRequest(ApiResponse<List<QuotationResponse>>.ErrorResult(ex.Message));
            }
        }

        [HttpPost]
        public async Task<ActionResult<ApiResponse<bool>>> ProcessQuotation([FromBody] CreateQuotationRequest request)
        {
            try
            {
                var result = await _quotationService.ProcessQuotationResponseAsync(request);
                return Ok(ApiResponse<bool>.SuccessResult(result, "Quotation processed successfully"));
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error processing quotation");
                return BadRequest(ApiResponse<bool>.ErrorResult(ex.Message));
            }
        }
    }
}
