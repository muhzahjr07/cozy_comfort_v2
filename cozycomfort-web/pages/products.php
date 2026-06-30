<?php
// Get API client
$apiClient = getApiClient();

// Get filters from URL
$category = getParam('category', '');
$minPrice = getParam('minPrice', '');
$maxPrice = getParam('maxPrice', '');
$search = getParam('search', '');
$sort = getParam('sort', 'newest');
$currentPage = (int)getParam('p', 1); // Use 'p' for pagination to avoid conflict with routing 'page'

// Build API parameters - use correct property names from OpenAPI spec
$params = [
    'Page' => $currentPage,  // Capital P for API
    'PageSize' => ITEMS_PER_PAGE,
    'SortBy' => $sort
];

if ($category) $params['Category'] = $category;
if ($minPrice) $params['MinPrice'] = $minPrice;
if ($maxPrice) $params['MaxPrice'] = $maxPrice;
if ($search) $params['Search'] = $search;

// Get categories from API dynamically
$categories = [];
try {
    $categoriesResponse = $apiClient->getProductCategories();
    if (is_array($categoriesResponse)) {
        $categories = $categoriesResponse;
    }
} catch (Exception $e) {
    error_log('Categories fetch error in products page: ' . $e->getMessage());
}

// Get products from API
$response = $apiClient->getProducts($params);

// Extract products properly from response

$products = [];
$pagination = [];

if (is_array($response) && isset($response['data']) && is_array($response['data'])) {
    $dataSection = $response['data'];
    
    // Check if this has the pagination wrapper
    if (isset($dataSection['data']) && is_array($dataSection['data'])) {
        $products = $dataSection['data'];
        $pagination = [
            'pageNumber' => $dataSection['pageNumber'] ?? 1,
            'pageSize' => $dataSection['pageSize'] ?? ITEMS_PER_PAGE,
            'totalCount' => $dataSection['totalCount'] ?? 0,
            'totalPages' => $dataSection['totalPages'] ?? 1,
            'hasPreviousPage' => $dataSection['hasPreviousPage'] ?? false,
            'hasNextPage' => $dataSection['hasNextPage'] ?? false
        ];
    } elseif (count($dataSection) > 0 && is_array($dataSection[0]) && isset($dataSection[0]['id'])) {
        // Direct array of products
        $products = $dataSection;
        $pagination = [
            'pageNumber' => 1,
            'pageSize' => ITEMS_PER_PAGE,
            'totalCount' => count($products),
            'totalPages' => 1,
            'hasPreviousPage' => false,
            'hasNextPage' => false
        ];
    }
}
?>

<div class="products-page">
    <div class="container">
        <div class="page-header">
            <h1>Products</h1>
            <form method="GET" class="search-form">
                <input type="hidden" name="page" value="products">
                <input type="text" name="search" placeholder="Search products..." 
                       value="<?php echo htmlspecialchars($search); ?>" class="search-input">
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
        </div>

        <div class="products-content">
            <!-- Sidebar filters -->
            <aside class="sidebar">
                <h3>Filters</h3>
                
                <form method="GET" id="filterForm">
                    <input type="hidden" name="page" value="products">
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                    
                    <div class="filter-group">
                        <h4>Category</h4>
                        <select name="category" onchange="submitFilters()">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $category === $cat ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <h4>Price Range</h4>
                        <input type="number" name="minPrice" placeholder="Min" value="<?php echo htmlspecialchars($minPrice); ?>">
                        <input type="number" name="maxPrice" placeholder="Max" value="<?php echo htmlspecialchars($maxPrice); ?>">
                        <button type="button" onclick="submitFilters()" class="btn btn-sm">Apply</button>
                    </div>

                    <div class="filter-group">
                        <h4>Sort By</h4>
                        <select name="sort" onchange="submitFilters()">
                            <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest</option>
                            <option value="price-low" <?php echo $sort === 'price-low' ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price-high" <?php echo $sort === 'price-high' ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="rating" <?php echo $sort === 'rating' ? 'selected' : ''; ?>>Rating</option>
                            <option value="popular" <?php echo $sort === 'popular' ? 'selected' : ''; ?>>Most Popular</option>
                        </select>
                    </div>
                </form>
            </aside>

            <!-- Products grid -->
            <main class="products-main">
                <div class="products-header">
                    <p class="results-count">
                        <?php if (isset($pagination['totalCount'])): ?>
                            Showing <?php echo count($products); ?> of <?php echo $pagination['totalCount']; ?> products
                        <?php endif; ?>
                    </p>
                </div>
                
                <div class="products-grid">
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $product): ?>
                            <?php include 'components/product-card.php'; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-products">
                            <p>No products found matching your criteria.</p>
                            <a href="?page=products" class="btn btn-primary">View All Products</a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Pagination -->
                <?php if (isset($pagination['totalPages']) && $pagination['totalPages'] > 1): ?>
                    <div class="pagination">
                        <?php 
                        $currentPage = $pagination['pageNumber'] ?? 1;
                        $totalPages = $pagination['totalPages'];
                        $baseUrl = "?page=products" . ($search ? "&search=" . urlencode($search) : "") . 
                                  ($category ? "&category=" . urlencode($category) : "") .
                                  ($minPrice ? "&minPrice=" . urlencode($minPrice) : "") .
                                  ($maxPrice ? "&maxPrice=" . urlencode($maxPrice) : "") .
                                  ($sort ? "&sort=" . urlencode($sort) : "");
                        ?>
                        
                        <?php if ($pagination['hasPreviousPage']): ?>
                            <a href="<?php echo $baseUrl . '&page=' . ($currentPage - 1); ?>" class="page-link">&larr; Previous</a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                            <a href="<?php echo $baseUrl . '&page=' . $i; ?>" 
                               class="page-link <?php echo $i == $currentPage ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($pagination['hasNextPage']): ?>
                            <a href="<?php echo $baseUrl . '&page=' . ($currentPage + 1); ?>" class="page-link">Next &rarr;</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
</div>

<script>
function submitFilters() {
    document.getElementById('filterForm').submit();
}
</script>
