<?php
// Admin authentication check
if (!isLoggedIn()) {
    setNotification('Please login to access admin panel.', 'error');
    redirect('?page=login');
}

if (!isAdmin()) {
    setNotification('Access denied. Admin privileges required.', 'error');
    redirect('?page=home');
}

// Get API client
$apiClient = getApiClient();

// Fetch distributors from API
$distributors = [];

try {
    $response = $apiClient->getDistributors();
    // Handle different response structures
    if (is_array($response)) {
        if (isset($response['data']) && is_array($response['data'])) {
            $distributors = $response['data'];
        } else if (isset($response[0]) && is_array($response[0])) {
            $distributors = $response;
        }
    }
    error_log('Successfully fetched distributors data: ' . count($distributors) . ' distributors');
} catch (Exception $e) {
    error_log('Admin distributors API error: ' . $e->getMessage());
    $distributors = []; // Ensure it's always an array
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        try {
            switch ($_POST['action']) {
                case 'add_distributor':
                    $distributorData = [
                        'name' => $_POST['name'],
                        'contactEmail' => $_POST['contactEmail'],
                        'contactPhone' => $_POST['contactPhone'] ?? null,
                        'apiEndpoint' => $_POST['apiEndpoint'],
                        'apiKey' => $_POST['apiKey']
                    ];
                    $apiClient->createDistributor($distributorData);
                    setNotification('Distributor added successfully!', 'success');
                    break;
                    
                case 'update_distributor':
                    $distributorId = (int)$_POST['distributorId'];
                    $distributorData = [
                        'name' => $_POST['name'],
                        'contactEmail' => $_POST['contactEmail'],
                        'contactPhone' => $_POST['contactPhone'] ?? null,
                        'apiEndpoint' => $_POST['apiEndpoint'],
                        'apiKey' => $_POST['apiKey'],
                        'isActive' => isset($_POST['isActive']) ? (bool)$_POST['isActive'] : true
                    ];
                    $apiClient->updateDistributor($distributorId, $distributorData);
                    setNotification('Distributor updated successfully!', 'success');
                    break;
                    
                case 'delete_distributor':
                    $distributorId = (int)$_POST['distributorId'];
                    $apiClient->deleteDistributor($distributorId);
                    setNotification('Distributor deleted successfully!', 'success');
                    break;
                    
                case 'update_status':
                    $distributorId = (int)$_POST['distributorId'];
                    $isActive = $_POST['status'] === 'active';
                    $apiClient->updateDistributor($distributorId, ['isActive' => $isActive]);
                    setNotification("Distributor status updated successfully.", 'success');
                    break;
            }
        } catch (Exception $e) {
            error_log('Distributor action error: ' . $e->getMessage());
            setNotification('Operation failed. Please try again.', 'error');
        }
        redirect('?page=admin-distributors');
    }
}

$statusFilter = $_GET['status'] ?? '';
$searchTerm = $_GET['search'] ?? '';

// Apply filters
$filteredDistributors = is_array($distributors) ? $distributors : [];

if (!empty($statusFilter) && !empty($filteredDistributors)) {
    $filteredDistributors = array_filter($filteredDistributors, function($d) use ($statusFilter) {
        return isset($d['isActive']) && (
            ($statusFilter === 'Active' && $d['isActive']) ||
            ($statusFilter === 'Inactive' && !$d['isActive'])
        );
    });
}

if (!empty($searchTerm) && !empty($filteredDistributors)) {
    $filteredDistributors = array_filter($filteredDistributors, function($distributor) use ($searchTerm) {
        return (isset($distributor['name']) && stripos($distributor['name'], $searchTerm) !== false) ||
               (isset($distributor['contactEmail']) && stripos($distributor['contactEmail'], $searchTerm) !== false);
    });
}

$statusOptions = [
    '' => 'All Statuses',
    'Active' => 'Active',
    'Pending' => 'Pending',
    'Suspended' => 'Suspended'
];
?>

<div class="admin-distributors">
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-left">
                <h1>🏢 Distributor Management</h1>
                <p>Manage distributor partnerships and relationships</p>
            </div>
            <div class="header-right">
                <button onclick="showDistributorModal()" class="btn btn-primary">+ Add Distributor</button>
                <a href="?page=admin-dashboard" class="btn btn-outline">← Back to Dashboard</a>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">🏢</div>
                <div class="stat-content">
                    <h3><?php echo is_array($distributors) ? count($distributors) : 0; ?></h3>
                    <p>Total Distributors</p>
                    <span class="stat-trend">
                        <?php 
                        if (is_array($distributors)) {
                            $active = count(array_filter($distributors, fn($d) => isset($d['isActive']) && $d['isActive']));
                            echo $active;
                        } else {
                            echo 0;
                        }
                        ?> active
                    </span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📦</div>
                <div class="stat-content">
                    <h3>
                        <?php 
                        if (is_array($distributors)) {
                            $totalOrders = array_sum(array_map(fn($d) => $d['totalOrders'] ?? 0, $distributors));
                            echo number_format($totalOrders);
                        } else {
                            echo 0;
                        }
                        ?>
                    </h3>
                    <p>Total Orders</p>
                    <span class="stat-trend">Across all distributors</span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-content">
                    <h3><?php 
                        $totalValue = 0;
                        if (is_array($distributors)) {
                            $totalValue = array_sum(array_map(fn($d) => (float)($d['totalValue'] ?? (($d['totalOrders'] ?? 0) * 1500)), $distributors));
                        }
                        echo CURRENCY_SYMBOL . ' ' . number_format($totalValue);
                    ?></h3>
                    <p>Total Value</p>
                    <span class="stat-trend">Lifetime orders</span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">⭐</div>
                <div class="stat-content">
                    <h3><?php 
                        $avgRating = 0;
                        if (is_array($distributors) && count($distributors) > 0) {
                            $totalRating = array_sum(array_map(fn($d) => (float)($d['rating'] ?? (($d['successRate'] ?? 0) > 0 ? ($d['successRate'] / 20) : 4.5)), $distributors));
                            $avgRating = $totalRating / count($distributors);
                        }
                        echo number_format($avgRating, 1);
                    ?></h3>
                    <p>Average Rating</p>
                    <span class="stat-trend">Based on performance</span>
                </div>
            </div>
        </div>
        
        <!-- Filters and Search -->
        <div class="filters-section">
            <form method="GET" class="filters-form">
                <input type="hidden" name="page" value="admin-distributors">
                
                <div class="search-input">
                    <input type="text" name="search" placeholder="Search distributors..." 
                           value="<?php echo htmlspecialchars($searchTerm); ?>">
                    <button type="submit" class="search-btn">🔍</button>
                </div>
                
                <div class="filter-select">
                    <select name="status" onchange="this.form.submit()">
                        <?php foreach ($statusOptions as $value => $label): ?>
                            <option value="<?php echo $value; ?>" 
                                    <?php echo $statusFilter === $value ? 'selected' : ''; ?>>
                                <?php echo $label; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <?php if (!empty($statusFilter) || !empty($searchTerm)): ?>
                    <a href="?page=admin-distributors" class="btn btn-outline btn-sm">Clear Filters</a>
                <?php endif; ?>
            </form>
            
            <div class="results-info">
                <span><?php echo count($filteredDistributors); ?> distributors found</span>
            </div>
        </div>
        
        <!-- Distributors Table -->
        <div class="distributors-table-container">
            <?php if (!empty($filteredDistributors)): ?>
                <table class="distributors-table">
                    <thead>
                        <tr>
                            <th>Distributor</th>
                            <th>Contact</th>
                            <th>Location</th>
                            <th>Specialization</th>
                            <th>Performance</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($filteredDistributors as $distributor): 
                            // Ensure distributor is an array
                            if (!is_array($distributor)) {
                                continue;
                            }
                            // Safe data access with defaults
                            $dist_id = $distributor['distributorId'] ?? $distributor['id'] ?? '#N/A';
                            $dist_name = $distributor['name'] ?? $distributor['distributorName'] ?? 'N/A';
                            $dist_person = $distributor['contactPerson'] ?? $distributor['name'] ?? 'N/A';
                            $dist_email = $distributor['email'] ?? $distributor['contactEmail'] ?? 'N/A';
                            $dist_phone = $distributor['phoneNumber'] ?? $distributor['contactPhone'] ?? 'N/A';
                            $dist_city = $distributor['city'] ?? 'N/A';
                            $dist_province = $distributor['province'] ?? $distributor['state'] ?? 'N/A';
                            $dist_address = $distributor['address'] ?? 'N/A';
                            $dist_spec = $distributor['specialization'] ?? 'General';
                            $dist_orders = (int)($distributor['totalOrders'] ?? 0);
                            $dist_value = (float)($distributor['totalValue'] ?? ($dist_orders * 1500));
                            $dist_rating = (float)($distributor['rating'] ?? (($distributor['successRate'] ?? 0) > 0 ? ($distributor['successRate'] / 20) : 4.5));
                            $dist_status = $distributor['status'] ?? $distributor['isActive'] ?? 'Active';
                            $dist_joined = $distributor['joinedDate'] ?? date('Y-m-d');
                        ?>
                            <tr>
                                <td>
                                    <div class="distributor-info">
                                        <strong><?php echo htmlspecialchars($dist_name); ?></strong>
                                        <br>
                                        <small>ID: #<?php echo htmlspecialchars($dist_id); ?></small>
                                        <br>
                                        <small>Since: <?php echo date('M Y', strtotime($dist_joined)); ?></small>
                                    </div>
                                </td>
                                <td>
                                    <div class="contact-info">
                                        <strong><?php echo htmlspecialchars($dist_person); ?></strong>
                                        <br>
                                        <small>📧 <?php echo htmlspecialchars($dist_email); ?></small>
                                        <br>
                                        <small>📞 <?php echo htmlspecialchars($dist_phone); ?></small>
                                    </div>
                                </td>
                                <td>
                                    <div class="location-info">
                                        <strong><?php echo htmlspecialchars($dist_city); ?></strong>
                                        <br>
                                        <small><?php echo htmlspecialchars($dist_province); ?></small>
                                        <br>
                                        <small><?php echo htmlspecialchars($dist_address); ?></small>
                                    </div>
                                </td>
                                <td>
                                    <div class="specialization">
                                        <?php echo htmlspecialchars($dist_spec); ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="performance-info">
                                        <div class="performance-metric">
                                            <span>Orders:</span> <strong><?php echo number_format($dist_orders); ?></strong>
                                        </div>
                                        <div class="performance-metric">
                                            <span>Value:</span> <strong><?php echo CURRENCY_SYMBOL . ' ' . number_format($dist_value); ?></strong>
                                        </div>
                                        <div class="performance-metric">
                                            <span>Rating:</span> 
                                            <div class="rating">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <span class="star <?php echo $i <= round($dist_rating) ? 'filled' : ''; ?>">⭐</span>
                                                <?php endfor; ?>
                                                <span><?php echo number_format($dist_rating, 1); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <form method="POST" class="status-form" style="margin: 0;">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="distributorId" value="<?php echo htmlspecialchars($dist_id); ?>">
                                        <select name="status" onchange="this.form.submit()" 
                                                class="status-select status-<?php echo strtolower($dist_status); ?>">
                                            <?php foreach (['Active', 'Pending', 'Suspended'] as $status): ?>
                                                <option value="<?php echo $status; ?>" 
                                                        <?php echo $dist_status === $status ? 'selected' : ''; ?>>
                                                    <?php echo $status; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button onclick="editDistributor(<?php echo htmlspecialchars(json_encode($distributor)); ?>)" 
                                                class="btn btn-sm btn-outline" title="Edit">✏️</button>
                                        <button onclick="viewPerformance(<?php echo htmlspecialchars($dist_id); ?>)" 
                                                class="btn btn-sm btn-outline" title="View Performance">📊</button>
                                        <button onclick="contactDistributor('<?php echo htmlspecialchars($dist_email); ?>')" 
                                                class="btn btn-sm btn-outline" title="Contact">📧</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">🏢</div>
                    <h3>No distributors found</h3>
                    <p>There are no distributors matching your current filters.</p>
                    <?php if (!empty($statusFilter) || !empty($searchTerm)): ?>
                        <a href="?page=admin-distributors" class="btn btn-primary">Show All Distributors</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Distributor Modal -->
<div id="distributorModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Add Distributor</h2>
            <button onclick="closeDistributorModal()" class="close-btn">×</button>
        </div>
        
        <form id="distributorForm" method="POST" class="modal-body">
            <input type="hidden" id="distributorAction" name="action" value="add_distributor">
            <input type="hidden" id="distributorId" name="distributorId" value="">
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="distributorName">Company Name *</label>
                    <input type="text" id="distributorName" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="contactPerson">Contact Person *</label>
                    <input type="text" id="contactPerson" name="contactPerson" required>
                </div>
                
                <div class="form-group">
                    <label for="distributorEmail">Email *</label>
                    <input type="email" id="distributorEmail" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="distributorPhone">Phone Number *</label>
                    <input type="tel" id="distributorPhone" name="phoneNumber" required>
                </div>
                
                <div class="form-group full-width">
                    <label for="distributorAddress">Address *</label>
                    <textarea id="distributorAddress" name="address" rows="3" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="distributorCity">City *</label>
                    <input type="text" id="distributorCity" name="city" required>
                </div>
                
                <div class="form-group">
                    <label for="distributorProvince">Province *</label>
                    <select id="distributorProvince" name="province" required>
                        <option value="">Select Province</option>
                        <option value="Western Province">Western Province</option>
                        <option value="Central Province">Central Province</option>
                        <option value="Southern Province">Southern Province</option>
                        <option value="Northern Province">Northern Province</option>
                        <option value="Eastern Province">Eastern Province</option>
                        <option value="North Western Province">North Western Province</option>
                        <option value="North Central Province">North Central Province</option>
                        <option value="Uva Province">Uva Province</option>
                        <option value="Sabaragamuwa Province">Sabaragamuwa Province</option>
                    </select>
                </div>
                
                <div class="form-group full-width">
                    <label for="specialization">Specialization</label>
                    <input type="text" id="specialization" name="specialization" 
                            placeholder="e.g., Weighted Blankets, Sherpa Throws, Woolen Blankets">
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" onclick="closeDistributorModal()" class="btn btn-outline">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Distributor</button>
            </div>
        </form>
    </div>
</div>

<style>
.admin-distributors {
    background-color: #f8f9fa;
    min-height: 100vh;
    padding: 2rem 0;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.page-header h1 {
    margin: 0;
    font-size: 2rem;
    color: #333;
}

.page-header p {
    margin: 0.5rem 0 0 0;
    color: #6c757d;
}

.header-right {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 3rem;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.stat-icon {
    font-size: 3rem;
    opacity: 0.8;
}

.stat-content h3 {
    margin: 0;
    font-size: 2rem;
    font-weight: 700;
    color: #333;
}

.stat-content p {
    margin: 0.5rem 0;
    color: #6c757d;
    font-weight: 500;
}

.stat-trend {
    color: #28a745;
    font-size: 0.9rem;
    font-weight: 600;
}

.filters-section {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.filters-form {
    display: flex;
    gap: 1rem;
    align-items: center;
    flex-wrap: wrap;
}

.search-input {
    position: relative;
    display: flex;
}

.search-input input {
    padding: 0.75rem 3rem 0.75rem 1rem;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 1rem;
    width: 250px;
}

.search-btn {
    position: absolute;
    right: 0;
    top: 0;
    height: 100%;
    background: transparent;
    border: none;
    padding: 0 1rem;
    cursor: pointer;
}

.filter-select select {
    padding: 0.75rem 1rem;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 1rem;
    background: white;
    cursor: pointer;
}

.distributors-table-container {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.distributors-table {
    width: 100%;
    border-collapse: collapse;
}

.distributors-table th {
    background: #f8f9fa;
    padding: 1.25rem 1rem;
    text-align: left;
    font-weight: 600;
    color: #333;
    border-bottom: 2px solid #e9ecef;
}

.distributors-table td {
    padding: 1.25rem 1rem;
    border-bottom: 1px solid #e9ecef;
    vertical-align: top;
}

.distributor-info, .contact-info, .location-info {
    line-height: 1.4;
}

.specialization {
    color: #6c757d;
    font-size: 0.9rem;
    font-style: italic;
}

.performance-info {
    font-size: 0.9rem;
}

.performance-metric {
    margin-bottom: 0.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.performance-metric span {
    color: #6c757d;
}

.rating {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.star {
    font-size: 0.8rem;
}

.star.filled {
    color: #ffc107;
}

.status-select {
    padding: 0.5rem 0.75rem;
    border: none;
    border-radius: 6px;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
}

.status-active { background: #d4edda; color: #155724; }
.status-pending { background: #fff3cd; color: #856404; }
.status-suspended { background: #f8d7da; color: #721c24; }

.action-buttons {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.action-buttons .btn {
    padding: 0.5rem;
    min-width: auto;
    border-radius: 6px;
}

.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
}

.modal-content {
    background-color: white;
    margin: 2% auto;
    border-radius: 12px;
    width: 90%;
    max-width: 700px;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    margin: 0;
    color: #333;
}

.close-btn {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #6c757d;
}

.modal-body {
    padding: 1.5rem;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
    margin-bottom: 1rem;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #333;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 1rem;
}

.form-group textarea {
    resize: vertical;
}

.modal-footer {
    padding: 1.5rem;
    border-top: 1px solid #e9ecef;
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: #6c757d;
}

.empty-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .distributors-table-container {
        overflow-x: auto;
    }
    
    .distributors-table {
        min-width: 1000px;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function showDistributorModal(distributor = null) {
    const modal = document.getElementById('distributorModal');
    const form = document.getElementById('distributorForm');
    const title = document.getElementById('modalTitle');
    
    if (distributor) {
        // Edit mode
        title.textContent = 'Edit Distributor';
        document.getElementById('distributorAction').value = 'update_distributor';
        document.getElementById('distributorId').value = distributor.distributorId;
        document.getElementById('distributorName').value = distributor.name;
        document.getElementById('contactPerson').value = distributor.contactPerson;
        document.getElementById('distributorEmail').value = distributor.email;
        document.getElementById('distributorPhone').value = distributor.phoneNumber;
        document.getElementById('distributorAddress').value = distributor.address;
        document.getElementById('distributorCity').value = distributor.city;
        document.getElementById('distributorProvince').value = distributor.province;
        document.getElementById('specialization').value = distributor.specialization;
    } else {
        // Add mode
        title.textContent = 'Add Distributor';
        document.getElementById('distributorAction').value = 'add_distributor';
        form.reset();
    }
    
    modal.style.display = 'block';
}

function editDistributor(distributor) {
    showDistributorModal(distributor);
}

function closeDistributorModal() {
    document.getElementById('distributorModal').style.display = 'none';
}

function viewPerformance(distributorId) {
    alert(`Viewing performance metrics for Distributor #${distributorId}.\n\nThis would open a detailed performance dashboard with:\n- Order history\n- Revenue trends\n- Customer satisfaction\n- Delivery performance`);
}

function contactDistributor(email) {
    if (confirm(`Send email to ${email}?`)) {
        window.location.href = `mailto:${email}?subject=Cozy Comfort Partnership&body=Dear Partner,%0D%0A%0D%0A`;
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('distributorModal');
    if (event.target === modal) {
        closeDistributorModal();
    }
}
</script>
