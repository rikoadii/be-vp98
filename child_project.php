<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include 'db.php';
include 'components/ui-components.php';

$page_title = 'Child Project Management';

// Create uploads directory if it doesn't exist
$upload_dir = 'uploads/child_projects/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $id_parent_project = (int)$_POST['id_parent_project'];
                
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $file = $_FILES['image'];
                    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
                    
                    if (in_array($file['type'], $allowed_types)) {
                        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                        $new_filename = 'child_' . time() . '.' . $file_extension;
                        $upload_path = $upload_dir . $new_filename;
                        
                        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                            $query = "INSERT INTO child_project (id_parent_project, image) VALUES (?, ?)";
                            $stmt = mysqli_prepare($conn, $query);
                            mysqli_stmt_bind_param($stmt, "is", $id_parent_project, $new_filename);
                            
                            if (mysqli_stmt_execute($stmt)) {
                                $_SESSION['success_message'] = "Child project created successfully!";
                            } else {
                                $_SESSION['error_message'] = "Error saving to database: " . mysqli_error($conn);
                                unlink($upload_path); // Delete uploaded file if database save fails
                            }
                            mysqli_stmt_close($stmt);
                        } else {
                            $_SESSION['error_message'] = "Error uploading file!";
                        }
                    } else {
                        $_SESSION['error_message'] = "Invalid file type! Only JPG, JPEG, PNG, and WEBP are allowed.";
                    }
                } else {
                    $_SESSION['error_message'] = "Please select an image file!";
                }
                header('Location: child_project.php');
                exit();
                break;
                
            case 'update':
                $id = (int)$_POST['id'];
                $id_parent_project = (int)$_POST['id_parent_project'];
                
                // Get current image to delete if new one is uploaded
                $current_query = "SELECT image FROM child_project WHERE id = ?";
                $current_stmt = mysqli_prepare($conn, $current_query);
                mysqli_stmt_bind_param($current_stmt, "i", $id);
                mysqli_stmt_execute($current_stmt);
                $current_result = mysqli_stmt_get_result($current_stmt);
                $current_image = mysqli_fetch_assoc($current_result);
                mysqli_stmt_close($current_stmt);
                
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $file = $_FILES['image'];
                    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
                    
                    if (in_array($file['type'], $allowed_types)) {
                        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                        $new_filename = 'child_' . time() . '.' . $file_extension;
                        $upload_path = $upload_dir . $new_filename;
                        
                        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                            $query = "UPDATE child_project SET id_parent_project = ?, image = ? WHERE id = ?";
                            $stmt = mysqli_prepare($conn, $query);
                            mysqli_stmt_bind_param($stmt, "isi", $id_parent_project, $new_filename, $id);
                            
                            if (mysqli_stmt_execute($stmt)) {
                                // Delete old image file
                                if ($current_image && file_exists($upload_dir . $current_image['image'])) {
                                    unlink($upload_dir . $current_image['image']);
                                }
                                $_SESSION['success_message'] = "Child project updated successfully!";
                            } else {
                                $_SESSION['error_message'] = "Error updating database: " . mysqli_error($conn);
                                unlink($upload_path); // Delete new file if database update fails
                            }
                            mysqli_stmt_close($stmt);
                        } else {
                            $_SESSION['error_message'] = "Error uploading new file!";
                        }
                    } else {
                        $_SESSION['error_message'] = "Invalid file type! Only JPG, JPEG, PNG, and WEBP are allowed.";
                    }
                } else {
                    // Update only parent project without changing image
                    $query = "UPDATE child_project SET id_parent_project = ? WHERE id = ?";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, "ii", $id_parent_project, $id);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        $_SESSION['success_message'] = "Child project updated successfully!";
                    } else {
                        $_SESSION['error_message'] = "Error updating database: " . mysqli_error($conn);
                    }
                    mysqli_stmt_close($stmt);
                }
                header('Location: child_project.php');
                exit();
                break;
                
            case 'delete':
                $id = (int)$_POST['id'];
                
                // Get image filename to delete file
                $image_query = "SELECT image FROM child_project WHERE id = ?";
                $image_stmt = mysqli_prepare($conn, $image_query);
                mysqli_stmt_bind_param($image_stmt, "i", $id);
                mysqli_stmt_execute($image_stmt);
                $image_result = mysqli_stmt_get_result($image_stmt);
                $image_data = mysqli_fetch_assoc($image_result);
                mysqli_stmt_close($image_stmt);
                
                $query = "DELETE FROM child_project WHERE id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "i", $id);
                
                if (mysqli_stmt_execute($stmt)) {
                    // Delete image file
                    if ($image_data && file_exists($upload_dir . $image_data['image'])) {
                        unlink($upload_dir . $image_data['image']);
                    }
                    $_SESSION['success_message'] = "Child project deleted successfully!";
                } else {
                    $_SESSION['error_message'] = "Error deleting child project: " . mysqli_error($conn);
                }
                mysqli_stmt_close($stmt);
                header('Location: child_project.php');
                exit();
                break;
        }
    }
}

// Get child project for editing
$edit_child_project = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $edit_query = "SELECT * FROM child_project WHERE id = ?";
    $edit_stmt = mysqli_prepare($conn, $edit_query);
    mysqli_stmt_bind_param($edit_stmt, "i", $edit_id);
    mysqli_stmt_execute($edit_stmt);
    $edit_result = mysqli_stmt_get_result($edit_stmt);
    $edit_child_project = mysqli_fetch_assoc($edit_result);
    mysqli_stmt_close($edit_stmt);
}

// Search functionality
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$parent_filter = isset($_GET['parent_project']) ? (int)$_GET['parent_project'] : '';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12; // Show 12 child projects per page (4x3 grid)
$offset = ($page - 1) * $limit;

// Build query based on filters
if ($parent_filter && !$search) {
    // Only parent filter
    $count_query = "SELECT COUNT(*) as total FROM child_project WHERE id_parent_project = ?";
    $count_stmt = mysqli_prepare($conn, $count_query);
    mysqli_stmt_bind_param($count_stmt, "i", $parent_filter);
    mysqli_stmt_execute($count_stmt);
    $count_result = mysqli_stmt_get_result($count_stmt);
    $total_records = mysqli_fetch_assoc($count_result)['total'];
    mysqli_stmt_close($count_stmt);
    
    $child_projects_query = "SELECT cp.*, p.name_projects as parent_title, p.description_projects as parent_description 
                            FROM child_project cp 
                            LEFT JOIN projects p ON cp.id_parent_project = p.project_id 
                            WHERE cp.id_parent_project = ?
                            ORDER BY cp.id DESC LIMIT ? OFFSET ?";
    $stmt = mysqli_prepare($conn, $child_projects_query);
    mysqli_stmt_bind_param($stmt, "iii", $parent_filter, $limit, $offset);
    mysqli_stmt_execute($stmt);
    $child_projects_result = mysqli_stmt_get_result($stmt);
} elseif ($search && !$parent_filter) {
    // Only search
    $search_term = "%$search%";
    $count_query = "SELECT COUNT(*) as total FROM child_project cp 
                    LEFT JOIN projects p ON cp.id_parent_project = p.project_id 
                    WHERE (p.name_projects LIKE ? OR p.description_projects LIKE ?)";
    $count_stmt = mysqli_prepare($conn, $count_query);
    mysqli_stmt_bind_param($count_stmt, "ss", $search_term, $search_term);
    mysqli_stmt_execute($count_stmt);
    $count_result = mysqli_stmt_get_result($count_stmt);
    $total_records = mysqli_fetch_assoc($count_result)['total'];
    mysqli_stmt_close($count_stmt);
    
    $child_projects_query = "SELECT cp.*, p.name_projects as parent_title, p.description_projects as parent_description 
                            FROM child_project cp 
                            LEFT JOIN projects p ON cp.id_parent_project = p.project_id 
                            WHERE (p.name_projects LIKE ? OR p.description_projects LIKE ?)
                            ORDER BY cp.id DESC LIMIT ? OFFSET ?";
    $stmt = mysqli_prepare($conn, $child_projects_query);
    mysqli_stmt_bind_param($stmt, "ssii", $search_term, $search_term, $limit, $offset);
    mysqli_stmt_execute($stmt);
    $child_projects_result = mysqli_stmt_get_result($stmt);
} elseif ($search && $parent_filter) {
    // Both search and parent filter
    $search_term = "%$search%";
    $count_query = "SELECT COUNT(*) as total FROM child_project cp 
                    LEFT JOIN projects p ON cp.id_parent_project = p.project_id 
                    WHERE cp.id_parent_project = ? 
                    AND (p.name_projects LIKE ? OR p.description_projects LIKE ?)";
    $count_stmt = mysqli_prepare($conn, $count_query);
    mysqli_stmt_bind_param($count_stmt, "iss", $parent_filter, $search_term, $search_term);
    mysqli_stmt_execute($count_stmt);
    $count_result = mysqli_stmt_get_result($count_stmt);
    $total_records = mysqli_fetch_assoc($count_result)['total'];
    mysqli_stmt_close($count_stmt);
    
    $child_projects_query = "SELECT cp.*, p.name_projects as parent_title, p.description_projects as parent_description 
                            FROM child_project cp 
                            LEFT JOIN projects p ON cp.id_parent_project = p.project_id 
                            WHERE cp.id_parent_project = ? 
                            AND (p.name_projects LIKE ? OR p.description_projects LIKE ?)
                            ORDER BY cp.id DESC LIMIT ? OFFSET ?";
    $stmt = mysqli_prepare($conn, $child_projects_query);
    mysqli_stmt_bind_param($stmt, "issii", $parent_filter, $search_term, $search_term, $limit, $offset);
    mysqli_stmt_execute($stmt);
    $child_projects_result = mysqli_stmt_get_result($stmt);
} else {
    // No filters - show all
    $count_query = "SELECT COUNT(*) as total FROM child_project";
    $count_result = mysqli_query($conn, $count_query);
    $total_records = mysqli_fetch_assoc($count_result)['total'];
    
    $child_projects_query = "SELECT cp.*, p.name_projects as parent_title, p.description_projects as parent_description 
                            FROM child_project cp 
                            LEFT JOIN projects p ON cp.id_parent_project = p.project_id 
                            ORDER BY cp.id DESC LIMIT ? OFFSET ?";
    $stmt = mysqli_prepare($conn, $child_projects_query);
    mysqli_stmt_bind_param($stmt, "ii", $limit, $offset);
    mysqli_stmt_execute($stmt);
    $child_projects_result = mysqli_stmt_get_result($stmt);
}

$total_pages = ceil($total_records / $limit);

// Get all parent projects for dropdown
$parent_projects_query = "SELECT project_id as id, name_projects as title FROM projects ORDER BY name_projects ASC";
$parent_projects_result = mysqli_query($conn, $parent_projects_query);

include 'components/header.php';
include 'components/sidebar.php';
include 'components/topbar.php';
?>

<!-- Page Header -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Child Project Management</h1>
        <p class="text-gray-600">Manage child projects and their parent relationships</p>
    </div>
    <button type="button" onclick="document.getElementById('childProjectModal').classList.remove('hidden')" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors duration-200">
        <i class="fas fa-plus mr-2"></i>
        Add Child Project
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

<!-- Search and Filter -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
    <!-- Debug Info (remove in production) -->
    <?php if (isset($_GET['debug'])): ?>
        <div class="mb-4 p-3 bg-gray-100 text-sm text-gray-700 rounded">
            <strong>Debug Info:</strong><br>
            Search: '<?= htmlspecialchars($search) ?>'<br>
            Parent Filter: <?= $parent_filter ?: 'None' ?><br>
            Total Records: <?= $total_records ?><br>
            Current Page: <?= $page ?><br>
        </div>
    <?php endif; ?>
    
    <form method="GET" action="child_project.php" class="flex flex-col lg:flex-row gap-4" id="filterForm">
        <div class="flex-1">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by parent project title or description..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div class="w-full lg:w-64">
            <select name="parent_project" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="submitFilterForm()">
                <option value="">All Parent Projects</option>
                <?php 
                mysqli_data_seek($parent_projects_result, 0);
                while ($parent = mysqli_fetch_assoc($parent_projects_result)): 
                ?>
                    <option value="<?= $parent['id'] ?>" <?= $parent_filter == $parent['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($parent['title']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                <i class="fas fa-search mr-2"></i>Search
            </button>
            <?php if ($search || $parent_filter): ?>
                <a href="child_project.php" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors duration-200">
                    <i class="fas fa-times mr-2"></i>Clear
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Child Projects Gallery -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <?php if (mysqli_num_rows($child_projects_result) > 0): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php while ($child_project = mysqli_fetch_assoc($child_projects_result)): ?>
                <div class="group relative bg-gray-50 rounded-lg overflow-hidden border border-gray-200 hover:border-gray-300 transition-colors duration-200">
                    <!-- Image -->
                    <div class="aspect-w-16 aspect-h-9 bg-gray-200">
                        <img src="<?= $upload_dir . htmlspecialchars($child_project['image']) ?>" 
                             alt="Child Project Image" 
                             class="w-full h-48 object-cover">
                    </div>
                    
                    <!-- Project Info -->
                    <div class="p-4">
                        <div class="space-y-2">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900 line-clamp-2">
                                    <?= htmlspecialchars($child_project['parent_title'] ?: 'No Parent Project') ?>
                                </h3>
                                <?php if ($child_project['parent_description']): ?>
                                    <p class="text-xs text-gray-500 mt-1 line-clamp-2">
                                        <?= htmlspecialchars($child_project['parent_description']) ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Actions Overlay -->
                    <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center space-x-2">
                        <button onclick="viewImage('<?= $upload_dir . htmlspecialchars($child_project['image']) ?>', '<?= htmlspecialchars($child_project['parent_title'] ?: 'No Parent') ?>')" 
                                class="p-2 bg-white text-gray-700 rounded-lg hover:bg-gray-100 transition-colors duration-200"
                                title="View Image">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button onclick="editChildProject(<?= htmlspecialchars(json_encode($child_project)) ?>)" 
                                class="p-2 bg-white text-blue-600 rounded-lg hover:bg-blue-50 transition-colors duration-200"
                                title="Edit Child Project">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="deleteChildProject(<?= $child_project['id'] ?>, '<?= htmlspecialchars($child_project['parent_title'] ?: 'Child Project') ?>')" 
                                class="p-2 bg-white text-red-600 rounded-lg hover:bg-red-50 transition-colors duration-200"
                                title="Delete Child Project">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        
        <!-- Show filter info if parent filter is active -->
        <?php if ($parent_filter): ?>
            <?php
            // Get parent project name for display
            $parent_info_query = "SELECT name_projects FROM projects WHERE project_id = ?";
            $parent_info_stmt = mysqli_prepare($conn, $parent_info_query);
            mysqli_stmt_bind_param($parent_info_stmt, "i", $parent_filter);
            mysqli_stmt_execute($parent_info_stmt);
            $parent_info_result = mysqli_stmt_get_result($parent_info_stmt);
            $parent_info = mysqli_fetch_assoc($parent_info_result);
            mysqli_stmt_close($parent_info_stmt);
            ?>
            <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                <p class="text-sm text-blue-700">
                    <i class="fas fa-filter mr-2"></i>
                    Showing child projects for: <strong><?= htmlspecialchars($parent_info['name_projects']) ?></strong>
                    (<?= $total_records ?> result<?= $total_records != 1 ? 's' : '' ?>)
                </p>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="text-center py-12">
            <div class="flex flex-col items-center justify-center">
                <i class="fas fa-project-diagram text-6xl text-gray-300 mb-4"></i>
                <?php if ($search || $parent_filter): ?>
                    <p class="text-xl font-medium text-gray-500 mb-2">No child projects found</p>
                    <p class="text-gray-400 mb-6">Try adjusting your search criteria or filter</p>
                    <a href="child_project.php" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors duration-200">
                        <i class="fas fa-times mr-2"></i>Clear Filters
                    </a>
                <?php else: ?>
                    <p class="text-xl font-medium text-gray-500 mb-2">No child projects found</p>
                    <p class="text-gray-400 mb-6">Get started by creating your first child project</p>
                    <button type="button" onclick="document.getElementById('childProjectModal').classList.remove('hidden')" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors duration-200">
                        <i class="fas fa-plus mr-2"></i>
                        Add First Child Project
                    </button>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
    <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6 mt-6 rounded-lg shadow-sm border">
        <div class="flex-1 flex justify-between sm:hidden">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&parent_project=<?= $parent_filter ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Previous</a>
            <?php endif; ?>
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&parent_project=<?= $parent_filter ?>" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Next</a>
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
                        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&parent_project=<?= $parent_filter ?>" class="<?= $i == $page ? 'bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50' ?> relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </nav>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Child Project Modal -->
<div id="childProjectModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="closeModal()"></div>
        <div class="inline-block w-full max-w-md my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-lg">
            <form id="childProjectForm" method="POST" action="child_project.php" enctype="multipart/form-data">
                <input type="hidden" name="action" id="formAction" value="create">
                <input type="hidden" name="id" id="childProjectId">
                
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900" id="modalTitle">Add Child Project</h3>
                        <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeModal()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                
                <div class="px-6 py-4">
                    <div class="space-y-4">
                        <div>
                            <label for="id_parent_project" class="block text-sm font-medium text-gray-700 mb-2">
                                Parent Project <span class="text-red-500">*</span>
                            </label>
                            <select id="id_parent_project" name="id_parent_project" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select Parent Project</option>
                                <?php 
                                mysqli_data_seek($parent_projects_result, 0);
                                while ($parent = mysqli_fetch_assoc($parent_projects_result)): 
                                ?>
                                    <option value="<?= $parent['id'] ?>">
                                        <?= htmlspecialchars($parent['title']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label for="image" class="block text-sm font-medium text-gray-700 mb-2">
                                Project Image <span class="text-red-500">*</span>
                            </label>
                            <input type="file" id="image" name="image" required accept="image/jpeg,image/jpg,image/png,image/webp" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="previewImage(this, 'imagePreview')">
                            <p class="mt-1 text-xs text-gray-500">Supported formats: JPG, JPEG, PNG, WEBP </p>
                        </div>
                        
                        <div id="imagePreview" class="hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Preview</label>
                            <img id="previewImg" src="" alt="Preview" class="w-full h-48 object-cover rounded-lg border border-gray-300">
                        </div>
                    </div>
                </div>
                
                <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors duration-200">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors duration-200">
                        <span id="submitText">Create Child Project</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Image Modal -->
<div id="viewModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-75" onclick="closeViewModal()"></div>
        <div class="inline-block w-full max-w-4xl my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900" id="viewModalTitle">View Child Project Image</h3>
                    <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeViewModal()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="p-6">
                <img id="viewImage" src="" alt="Child Project Image" class="w-full h-auto max-h-96 object-contain mx-auto rounded-lg">
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
                        <h3 class="text-lg font-semibold text-gray-900">Delete Child Project</h3>
                        <p class="text-sm text-gray-500 mt-1">Are you sure you want to delete child project from <span id="deleteChildProjectName" class="font-medium"></span>? This action cannot be undone.</p>
                    </div>
                </div>
            </div>
            
            <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors duration-200">
                    Cancel
                </button>
                <form id="deleteForm" method="POST" action="child_project.php" class="inline">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="deleteChildProjectId">
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors duration-200">
                        Delete Child Project
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function submitFilterForm() {
    console.log('Filter form submitted');
    document.getElementById('filterForm').submit();
}

function closeModal() {
    document.getElementById('childProjectModal').classList.add('hidden');
    document.getElementById('childProjectForm').reset();
    document.getElementById('formAction').value = 'create';
    document.getElementById('childProjectId').value = '';
    document.getElementById('modalTitle').textContent = 'Add Child Project';
    document.getElementById('submitText').textContent = 'Create Child Project';
    document.getElementById('imagePreview').classList.add('hidden');
    document.getElementById('image').required = true;
}

function editChildProject(childProject) {
    document.getElementById('childProjectModal').classList.remove('hidden');
    document.getElementById('formAction').value = 'update';
    document.getElementById('childProjectId').value = childProject.id;
    document.getElementById('id_parent_project').value = childProject.id_parent_project;
    document.getElementById('modalTitle').textContent = 'Edit Child Project';
    document.getElementById('submitText').textContent = 'Update Child Project';
    document.getElementById('image').required = false;
    
    // Show current image preview
    const preview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    previewImg.src = 'uploads/child_projects/' + childProject.image;
    preview.classList.remove('hidden');
}

function viewImage(imageSrc, parentTitle) {
    document.getElementById('viewModal').classList.remove('hidden');
    document.getElementById('viewImage').src = imageSrc;
    document.getElementById('viewModalTitle').textContent = 'Child Project: ' + parentTitle;
}

function closeViewModal() {
    document.getElementById('viewModal').classList.add('hidden');
}

function deleteChildProject(id, parentTitle) {
    document.getElementById('deleteModal').classList.remove('hidden');
    document.getElementById('deleteChildProjectId').value = id;
    document.getElementById('deleteChildProjectName').textContent = parentTitle;
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    const previewImg = document.getElementById('previewImg');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.classList.remove('hidden');
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.classList.add('hidden');
    }
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