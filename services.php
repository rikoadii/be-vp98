<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include 'db.php';
include 'components/ui-components.php';

$page_title = 'Services Management';

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $title = mysqli_real_escape_string($conn, $_POST['title']);
                $subtitle = mysqli_real_escape_string($conn, $_POST['subtitle']);
                $description = mysqli_real_escape_string($conn, $_POST['description']);
                
                if (!empty($title) && !empty($subtitle) && !empty($description)) {
                    $query = "INSERT INTO services (title, subtitle, description) VALUES (?, ?, ?)";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, "sss", $title, $subtitle, $description);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        $_SESSION['success_message'] = "Service created successfully!";
                    } else {
                        $_SESSION['error_message'] = "Error creating service: " . mysqli_error($conn);
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    $_SESSION['error_message'] = "All fields are required!";
                }
                header('Location: services.php');
                exit();
                break;
                
            case 'update':
                $id = (int)$_POST['id'];
                $title = mysqli_real_escape_string($conn, $_POST['title']);
                $subtitle = mysqli_real_escape_string($conn, $_POST['subtitle']);
                $description = mysqli_real_escape_string($conn, $_POST['description']);
                
                if (!empty($title) && !empty($subtitle) && !empty($description)) {
                    $query = "UPDATE services SET title = ?, subtitle = ?, description = ? WHERE id = ?";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, "sssi", $title, $subtitle, $description, $id);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        $_SESSION['success_message'] = "Service updated successfully!";
                    } else {
                        $_SESSION['error_message'] = "Error updating service: " . mysqli_error($conn);
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    $_SESSION['error_message'] = "All fields are required!";
                }
                header('Location: services.php');
                exit();
                break;
                
            case 'delete':
                $id = (int)$_POST['id'];
                
                $query = "DELETE FROM services WHERE id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "i", $id);
                
                if (mysqli_stmt_execute($stmt)) {
                    $_SESSION['success_message'] = "Service deleted successfully!";
                } else {
                    $_SESSION['error_message'] = "Error deleting service: " . mysqli_error($conn);
                }
                mysqli_stmt_close($stmt);
                header('Location: services.php');
                exit();
                break;
        }
    }
}

// Get service for editing
$edit_service = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $edit_query = "SELECT * FROM services WHERE id = ?";
    $edit_stmt = mysqli_prepare($conn, $edit_query);
    mysqli_stmt_bind_param($edit_stmt, "i", $edit_id);
    mysqli_stmt_execute($edit_stmt);
    $edit_result = mysqli_stmt_get_result($edit_stmt);
    $edit_service = mysqli_fetch_assoc($edit_result);
    mysqli_stmt_close($edit_stmt);
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Search functionality
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$where_clause = '';
$params = [];
$param_types = '';

if ($search) {
    $where_clause = "WHERE title LIKE ? OR subtitle LIKE ? OR description LIKE ?";
    $search_term = "%$search%";
    $params = [$search_term, $search_term, $search_term];
    $param_types = 'sss';
}

// Get total records
$count_query = "SELECT COUNT(*) as total FROM services $where_clause";
if ($search) {
    $count_stmt = mysqli_prepare($conn, $count_query);
    mysqli_stmt_bind_param($count_stmt, $param_types, ...$params);
    mysqli_stmt_execute($count_stmt);
    $count_result = mysqli_stmt_get_result($count_stmt);
    $total_records = mysqli_fetch_assoc($count_result)['total'];
    mysqli_stmt_close($count_stmt);
} else {
    $count_result = mysqli_query($conn, $count_query);
    $total_records = mysqli_fetch_assoc($count_result)['total'];
}

$total_pages = ceil($total_records / $limit);

// Get services
$services_query = "SELECT * FROM services $where_clause ORDER BY id DESC LIMIT ? OFFSET ?";
if ($search) {
    $main_params = array_merge($params, [$limit, $offset]);
    $main_param_types = $param_types . 'ii';
    $stmt = mysqli_prepare($conn, $services_query);
    mysqli_stmt_bind_param($stmt, $main_param_types, ...$main_params);
    mysqli_stmt_execute($stmt);
    $services_result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
} else {
    $stmt = mysqli_prepare($conn, $services_query);
    mysqli_stmt_bind_param($stmt, "ii", $limit, $offset);
    mysqli_stmt_execute($stmt);
    $services_result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
}

include 'components/header.php';
include 'components/sidebar.php';
include 'components/topbar.php';
?>

<!-- Page Header -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Services Management</h1>
        <p class="text-gray-600">Manage your services list</p>
    </div>
    <button type="button" onclick="document.getElementById('serviceModal').classList.remove('hidden')" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors duration-200">
        <i class="fas fa-plus mr-2"></i>
        Add Service
    </button>
</div>

<!-- Alert Messages -->
<?php if (isset($_SESSION['success_message'])): ?>
    <?php showAlert('success', $_SESSION['success_message']); ?>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <?php showAlert('error', $_SESSION['error_message']); ?>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>

<!-- Search -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
    <form method="GET" action="services.php" class="flex gap-4">
        <div class="flex-1">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search services..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                <i class="fas fa-search mr-2"></i>Search
            </button>
            <?php if ($search): ?>
                <a href="services.php" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors duration-200">
                    <i class="fas fa-times mr-2"></i>Clear
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Services Table -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtitle</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (mysqli_num_rows($services_result) > 0): ?>
                    <?php while ($service = mysqli_fetch_assoc($services_result)): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    <?= htmlspecialchars($service['title']) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <?= htmlspecialchars($service['subtitle']) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 max-w-xs truncate">
                                    <?= htmlspecialchars($service['description']) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button onclick="viewService(<?= htmlspecialchars(json_encode($service)) ?>)" class="text-blue-600 hover:text-blue-900 transition-colors duration-200" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button onclick="editService(<?= htmlspecialchars(json_encode($service)) ?>)" class="text-green-600 hover:text-green-900 transition-colors duration-200" title="Edit Service">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="deleteService(<?= $service['id'] ?>, '<?= htmlspecialchars($service['title']) ?>')" class="text-red-600 hover:text-red-900 transition-colors duration-200" title="Delete Service">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center py-8">
                                <i class="fas fa-concierge-bell text-4xl text-gray-300 mb-4"></i>
                                <p class="text-lg font-medium">No services found</p>
                                <?php if ($search): ?>
                                    <p class="text-sm">Try adjusting your search criteria</p>
                                <?php else: ?>
                                    <p class="text-sm">Get started by adding your first service</p>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
    <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6 mt-6 rounded-lg shadow-sm border">
        <div class="flex-1 flex justify-between sm:hidden">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Previous</a>
            <?php endif; ?>
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Next</a>
            <?php endif; ?>
        </div>
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-700">
                    Showing <span class="font-medium"><?= $offset + 1 ?></span> to <span class="font-medium"><?= min($offset + $limit, $total_records) ?></span> of <span class="font-medium"><?= $total_records ?></span> results
                </p>
            </div>
            <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" class="<?= $i == $page ? 'bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50' ?> relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </nav>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Service Modal -->
<div id="serviceModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="closeModal()"></div>
        <div class="inline-block w-full max-w-lg my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-lg">
            <form id="serviceForm" method="POST" action="services.php">
                <input type="hidden" name="action" id="formAction" value="create">
                <input type="hidden" name="id" id="serviceId">
                
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900" id="modalTitle">Add New Service</h3>
                        <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeModal()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                
                <div class="px-6 py-4">
                    <div class="space-y-4">
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Title <span class="text-red-500">*</span></label>
                            <input type="text" id="title" name="title" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter service title">
                        </div>
                        
                        <div>
                            <label for="subtitle" class="block text-sm font-medium text-gray-700 mb-2">Subtitle <span class="text-red-500">*</span></label>
                            <input type="text" id="subtitle" name="subtitle" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter service subtitle">
                        </div>
                        
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description <span class="text-red-500">*</span></label>
                            <textarea id="description" name="description" rows="4" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter service description"></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors duration-200">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors duration-200">
                        <span id="submitText">Save Service</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Service Modal -->
<div id="viewModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="closeViewModal()"></div>
        <div class="inline-block w-full max-w-lg my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Service Details</h3>
                    <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeViewModal()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            
            <div class="px-6 py-4">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                        <p id="viewTitle" class="text-sm text-gray-900 bg-gray-50 p-3 rounded-lg"></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Subtitle</label>
                        <p id="viewSubtitle" class="text-sm text-gray-900 bg-gray-50 p-3 rounded-lg"></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <p id="viewDescription" class="text-sm text-gray-900 bg-gray-50 p-3 rounded-lg"></p>
                    </div>
                </div>
            </div>
            
            <div class="px-6 py-4 border-t border-gray-200 flex justify-end">
                <button type="button" onclick="closeViewModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors duration-200">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="closeDeleteModal()"></div>
        <div class="inline-block w-full max-w-md my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-lg">
            <div class="px-6 py-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Delete Service</h3>
                        <p class="text-sm text-gray-500 mt-1">Are you sure you want to delete <span id="deleteServiceName" class="font-medium"></span>? This action cannot be undone.</p>
                    </div>
                </div>
            </div>
            
            <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors duration-200">
                    Cancel
                </button>
                <form id="deleteForm" method="POST" action="services.php" class="inline">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="deleteServiceId">
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors duration-200">
                        Delete Service
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function closeModal() {
    document.getElementById('serviceModal').classList.add('hidden');
    document.getElementById('serviceForm').reset();
    document.getElementById('formAction').value = 'create';
    document.getElementById('serviceId').value = '';
    document.getElementById('modalTitle').textContent = 'Add New Service';
    document.getElementById('submitText').textContent = 'Save Service';
}

function editService(service) {
    document.getElementById('serviceModal').classList.remove('hidden');
    document.getElementById('formAction').value = 'update';
    document.getElementById('serviceId').value = service.id;
    document.getElementById('title').value = service.title;
    document.getElementById('subtitle').value = service.subtitle;
    document.getElementById('description').value = service.description;
    document.getElementById('modalTitle').textContent = 'Edit Service';
    document.getElementById('submitText').textContent = 'Update Service';
}

function viewService(service) {
    document.getElementById('viewModal').classList.remove('hidden');
    document.getElementById('viewTitle').textContent = service.title;
    document.getElementById('viewSubtitle').textContent = service.subtitle;
    document.getElementById('viewDescription').textContent = service.description;
}

function closeViewModal() {
    document.getElementById('viewModal').classList.add('hidden');
}

function deleteService(id, name) {
    document.getElementById('deleteModal').classList.remove('hidden');
    document.getElementById('deleteServiceId').value = id;
    document.getElementById('deleteServiceName').textContent = name;
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

// Auto hide alerts
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert-auto-hide');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease-out';
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000);
    });
});
</script>

<?php include 'components/footer.php'; ?>
