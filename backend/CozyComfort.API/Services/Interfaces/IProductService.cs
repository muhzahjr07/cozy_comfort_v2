using CozyComfort.API.Models.DTOs;

namespace CozyComfort.API.Services.Interfaces
{
    public interface IProductService
    {
        Task<PaginatedResponse<ProductSummaryResponse>> GetProductsAsync(ProductSearchRequest request);
        Task<ProductResponse> GetProductByIdAsync(int productId);
        Task<ProductResponse> CreateProductAsync(CreateProductRequest request);
        Task<ProductResponse> UpdateProductAsync(int productId, CreateProductRequest request);
        Task<bool> DeleteProductAsync(int productId);
        Task<IEnumerable<string>> GetCategoriesAsync();
        Task<IEnumerable<CategoryStatsResponse>> GetCategoryStatsAsync();
    }
}
