using Microsoft.AspNetCore.Mvc;
using CozyComfort.API.Models.DTOs;
using CozyComfort.API.Services.Interfaces;

namespace CozyComfort.API.Controllers
{
    [ApiController]
    [Route("api/[controller]")]
    public class DistributorsController : ControllerBase
    {
        private readonly IDistributorService _distributorService;
        private readonly ILogger<DistributorsController> _logger;

        public DistributorsController(IDistributorService distributorService, ILogger<DistributorsController> logger)
        {
            _distributorService = distributorService;
            _logger = logger;
        }

        [HttpGet]
        public async Task<ActionResult<ApiResponse<List<DistributorResponse>>>> GetAllDistributors()
        {
            try
            {
                var result = await _distributorService.GetAllDistributorsAsync();
                return Ok(ApiResponse<List<DistributorResponse>>.SuccessResult(result));
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error getting distributors");
                return BadRequest(ApiResponse<List<DistributorResponse>>.ErrorResult(ex.Message));
            }
        }

        [HttpGet("{id}")]
        public async Task<ActionResult<ApiResponse<DistributorResponse>>> GetDistributor(int id)
        {
            try
            {
                var result = await _distributorService.GetDistributorByIdAsync(id);
                return Ok(ApiResponse<DistributorResponse>.SuccessResult(result));
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Error getting distributor {id}");
                return NotFound(ApiResponse<DistributorResponse>.ErrorResult(ex.Message));
            }
        }

        [HttpGet("{id}/availability")]
        public async Task<ActionResult<ApiResponse<bool>>> CheckDistributorAvailability(int id)
        {
            try
            {
                var result = await _distributorService.CheckDistributorAvailabilityAsync(id);
                return Ok(ApiResponse<bool>.SuccessResult(result, $"Distributor availability checked"));
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Error checking availability for distributor {id}");
                return BadRequest(ApiResponse<bool>.ErrorResult(ex.Message));
            }
        }

        [HttpGet("{id}/performance")]
        public async Task<ActionResult<ApiResponse<DistributorPerformanceResponse>>> GetDistributorPerformance(
            int id, [FromQuery] DateTime? startDate = null, [FromQuery] DateTime? endDate = null)
        {
            try
            {
                var result = await _distributorService.GetDistributorPerformanceAsync(id, startDate, endDate);
                return Ok(ApiResponse<DistributorPerformanceResponse>.SuccessResult(result));
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Error getting performance for distributor {id}");
                return BadRequest(ApiResponse<DistributorPerformanceResponse>.ErrorResult(ex.Message));
            }
        }

        [HttpPost]
        public async Task<ActionResult<ApiResponse<DistributorResponse>>> CreateDistributor([FromBody] CreateDistributorRequest request)
        {
            try
            {
                var result = await _distributorService.CreateDistributorAsync(request);
                return CreatedAtAction(nameof(GetDistributor), new { id = result.DistributorId }, 
                    ApiResponse<DistributorResponse>.SuccessResult(result, "Distributor created successfully"));
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error creating distributor");
                return BadRequest(ApiResponse<DistributorResponse>.ErrorResult(ex.Message));
            }
        }

        [HttpPut("{id}")]
        public async Task<ActionResult<ApiResponse<DistributorResponse>>> UpdateDistributor(int id, [FromBody] UpdateDistributorRequest request)
        {
            try
            {
                var result = await _distributorService.UpdateDistributorAsync(id, request);
                return Ok(ApiResponse<DistributorResponse>.SuccessResult(result, "Distributor updated successfully"));
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Error updating distributor {id}");
                return BadRequest(ApiResponse<DistributorResponse>.ErrorResult(ex.Message));
            }
        }

        [HttpDelete("{id}")]
        public async Task<ActionResult<ApiResponse<bool>>> DeleteDistributor(int id)
        {
            try
            {
                var result = await _distributorService.DeleteDistributorAsync(id);
                return Ok(ApiResponse<bool>.SuccessResult(result, "Distributor deleted successfully"));
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Error deleting distributor {id}");
                return BadRequest(ApiResponse<bool>.ErrorResult(ex.Message));
            }
        }
    }
}
