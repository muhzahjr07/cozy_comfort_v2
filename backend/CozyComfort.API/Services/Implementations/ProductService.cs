using AutoMapper;
using Microsoft.EntityFrameworkCore;
using CozyComfort.API.Data;
using CozyComfort.API.Models.Domain;
using CozyComfort.API.Models.DTOs;
using CozyComfort.API.Repositories;
using CozyComfort.API.Services.Interfaces;
using CozyComfort.API.Exceptions;

namespace CozyComfort.API.Services.Implementations
{
    public class ProductService : IProductService
    {
        private readonly IRepository<Product> _productRepository;
        private readonly IMapper _mapper;
        private readonly ILogger<ProductService> _logger;

        public ProductService(
            IRepository<Product> productRepository,
            IMapper mapper,
            ILogger<ProductService> logger)
        {
            _productRepository = productRepository;
            _mapper = mapper;
            _logger = logger;
        }

        public async Task<PaginatedResponse<ProductSummaryResponse>> GetProductsAsync(ProductSearchRequest request)
        {
            var query = (await _productRepository.GetAllAsync()).AsQueryable();

            // Apply filters
            if (!string.IsNullOrEmpty(request.Search))
            {
                query = query.Where(p => p.Name.Contains(request.Search) || p.Description.Contains(request.Search));
            }

            if (!string.IsNullOrEmpty(request.Category))
            {
                query = query.Where(p => p.Category == request.Category);
            }

            query = query.Where(p => p.IsActive);

            // Apply sorting
            query = request.SortBy?.ToLower() switch
            {
                "name" => query.OrderBy(p => p.Name),
                "category" => query.OrderBy(p => p.Category),
                "oldest" => query.OrderBy(p => p.CreatedAt),
                _ => query.OrderByDescending(p => p.CreatedAt) // Default: newest
            };

            var totalCount = query.Count();
            var products = query.Skip((request.Page - 1) * request.PageSize)
                               .Take(request.PageSize)
                               .ToList();

            var productResponses = _mapper.Map<List<ProductSummaryResponse>>(products);
            
            return PaginatedResponse<ProductSummaryResponse>.Create(productResponses, request.Page, request.PageSize, totalCount);
        }

        public async Task<ProductResponse> GetProductByIdAsync(int productId)
        {
            var product = await _productRepository.GetByIdAsync(productId);
            if (product == null || !product.IsActive)
                throw new NotFoundException($"Product with ID {productId} not found");

            return _mapper.Map<ProductResponse>(product);
        }

        public async Task<ProductResponse> CreateProductAsync(CreateProductRequest request)
        {
            var product = _mapper.Map<Product>(request);
            
            await _productRepository.AddAsync(product);
            await _productRepository.SaveChangesAsync();

            _logger.LogInformation($"Product created: {product.ProductId} - {product.Name}");

            return _mapper.Map<ProductResponse>(product);
        }

        public async Task<ProductResponse> UpdateProductAsync(int productId, CreateProductRequest request)
        {
            var product = await _productRepository.GetByIdAsync(productId);
            if (product == null)
                throw new NotFoundException($"Product with ID {productId} not found");

            _mapper.Map(request, product);
            product.ModifiedAt = DateTime.UtcNow;
            
            _productRepository.Update(product);
            await _productRepository.SaveChangesAsync();

            _logger.LogInformation($"Product updated: {product.ProductId} - {product.Name}");

            return _mapper.Map<ProductResponse>(product);
        }

        public async Task<bool> DeleteProductAsync(int productId)
        {
            var product = await _productRepository.GetByIdAsync(productId);
            if (product == null)
                throw new NotFoundException($"Product with ID {productId} not found");

            product.IsActive = false;
            product.ModifiedAt = DateTime.UtcNow;
            
            _productRepository.Update(product);
            await _productRepository.SaveChangesAsync();

            _logger.LogInformation($"Product soft-deleted: {product.ProductId} - {product.Name}");
            return true;
        }

        public async Task<IEnumerable<string>> GetCategoriesAsync()
        {
            var products = await _productRepository.GetAllAsync();
            return products.Where(p => p.IsActive)
                          .Select(p => p.Category)
                          .Distinct()
                          .OrderBy(c => c)
                          .ToList();
        }

        public async Task<IEnumerable<CategoryStatsResponse>> GetCategoryStatsAsync()
        {
            var products = await _productRepository.GetAllAsync();
            return products.Where(p => p.IsActive)
                          .GroupBy(p => p.Category)
                          .Select(g => new CategoryStatsResponse
                          {
                              Name = g.Key,
                              ProductCount = g.Count()
                          })
                          .OrderBy(c => c.Name)
                          .ToList();
        }
    }
}
