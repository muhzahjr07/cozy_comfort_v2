using Microsoft.AspNetCore.Mvc;
using CozyComfort.API.Models.DTOs;
using CozyComfort.API.Services.Interfaces;

namespace CozyComfort.API.Controllers
{
    [ApiController]
    [Route("api/[controller]")]
    public class ProductsController : ControllerBase
    {
        private readonly IProductService _productService;
        private readonly ILogger<ProductsController> _logger;

        public ProductsController(IProductService productService, ILogger<ProductsController> logger)
        {
            _productService = productService;
            _logger = logger;
        }

        [HttpGet]
        public async Task<ActionResult<ApiResponse<PaginatedResponse<ProductSummaryResponse>>>> GetProducts([FromQuery] ProductSearchRequest request)
        {
            try
            {
                var result = await _productService.GetProductsAsync(request);
                return Ok(ApiResponse<PaginatedResponse<ProductSummaryResponse>>.SuccessResult(result));
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error getting products");
                return BadRequest(ApiResponse<PaginatedResponse<ProductSummaryResponse>>.ErrorResult(ex.Message));
            }
        }

        [HttpGet("{id}")]
        public async Task<ActionResult<ApiResponse<ProductResponse>>> GetProduct(int id)
        {
            try
            {
                var result = await _productService.GetProductByIdAsync(id);
                return Ok(ApiResponse<ProductResponse>.SuccessResult(result));
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Error getting product {id}");
                return NotFound(ApiResponse<ProductResponse>.ErrorResult(ex.Message));
            }
        }

        [HttpGet("categories")]
        public async Task<ActionResult<ApiResponse<IEnumerable<string>>>> GetCategories()
        {
            try
            {
                var result = await _productService.GetCategoriesAsync();
                return Ok(ApiResponse<IEnumerable<string>>.SuccessResult(result));
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error getting categories");
                return BadRequest(ApiResponse<IEnumerable<string>>.ErrorResult(ex.Message));
            }
        }

        [HttpGet("categories/stats")]
        public async Task<ActionResult<ApiResponse<IEnumerable<CategoryStatsResponse>>>> GetCategoryStats()
        {
            try
            {
                var result = await _productService.GetCategoryStatsAsync();
                return Ok(ApiResponse<IEnumerable<CategoryStatsResponse>>.SuccessResult(result));
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error getting category stats");
                return BadRequest(ApiResponse<IEnumerable<CategoryStatsResponse>>.ErrorResult(ex.Message));
            }
        }

        [HttpPost]
        public async Task<ActionResult<ApiResponse<ProductResponse>>> CreateProduct([FromBody] CreateProductRequest request)
        {
            try
            {
                var result = await _productService.CreateProductAsync(request);
                return CreatedAtAction(nameof(GetProduct), new { id = result.ProductId }, 
                    ApiResponse<ProductResponse>.SuccessResult(result, "Product created successfully"));
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error creating product");
                return BadRequest(ApiResponse<ProductResponse>.ErrorResult(ex.Message));
            }
        }

        [HttpPut("{id}")]
        public async Task<ActionResult<ApiResponse<ProductResponse>>> UpdateProduct(int id, [FromBody] CreateProductRequest request)
        {
            try
            {
                var result = await _productService.UpdateProductAsync(id, request);
                return Ok(ApiResponse<ProductResponse>.SuccessResult(result, "Product updated successfully"));
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Error updating product {id}");
                return BadRequest(ApiResponse<ProductResponse>.ErrorResult(ex.Message));
            }
        }

        [HttpDelete("{id}")]
        public async Task<ActionResult<ApiResponse<bool>>> DeleteProduct(int id)
        {
            try
            {
                var result = await _productService.DeleteProductAsync(id);
                return Ok(ApiResponse<bool>.SuccessResult(result, "Product deleted successfully"));
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Error deleting product {id}");
                return BadRequest(ApiResponse<bool>.ErrorResult(ex.Message));
            }
        }
    }
}
