<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include 'db.php';
include 'components/ui-components.php';

$page_title = 'Center Image Management';

// Create uploads directory if it doesn't exist
$upload_dir = 'uploads/center_images/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                // Check if center image already exists (only allow 1)
                $check_query = "SELECT COUNT(*) as count FROM center_image";
                $check_result = mysqli_query($conn, $check_query);
                $count = mysqli_fetch_assoc($check_result)['count'];
                
                if ($count > 0) {
                    $_SESSION['error_message'] = "Center image already exists! Only one center image is allowed. Please edit or delete the existing one.";
                    header('Location: center_image.php');
                    exit();
                }
                
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $file = $_FILES['image'];
                    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
                    
                    if (in_array($file['type'], $allowed_types)) {
                        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                        $new_filename = 'center_' . time() . '.' . $file_extension;
                        $upload_path = $upload_dir . $new_filename;
                        
                        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                            $query = "INSERT INTO center_image (image) VALUES (?)";
                            $stmt = mysqli_prepare($conn, $query);
                            mysqli_stmt_bind_param($stmt, "s", $new_filename);
                            
                            if (mysqli_stmt_execute($stmt)) {
                                $_SESSION['success_message'] = "Center image uploaded successfully!";
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
                header('Location: center_image.php');
                exit();
                break;
                
            case 'update':
                $id = (int)$_POST['id'];
                
                // Get current image to delete if new one is uploaded
                $current_query = "SELECT image FROM center_image WHERE id = ?";
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
                        $new_filename = 'center_' . time() . '.' . $file_extension;
                        $upload_path = $upload_dir . $new_filename;
                        
                        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                            $query = "UPDATE center_image SET image = ? WHERE id = ?";
                            $stmt = mysqli_prepare($conn, $query);
                            mysqli_stmt_bind_param($stmt, "si", $new_filename, $id);
                            
                            if (mysqli_stmt_execute($stmt)) {
                                // Delete old image file
                                if ($current_image && file_exists($upload_dir . $current_image['image'])) {
                                    unlink($upload_dir . $current_image['image']);
                                }
                                $_SESSION['success_message'] = "Center image updated successfully!";
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
                    $_SESSION['error_message'] = "Please select a new image file!";
                }
                header('Location: center_image.php');
                exit();
                break;
                
            case 'delete':
                $id = (int)$_POST['id'];
                
                // Get image filename to delete file
                $image_query = "SELECT image FROM center_image WHERE id = ?";
                $image_stmt = mysqli_prepare($conn, $image_query);
                mysqli_stmt_bind_param($image_stmt, "i", $id);
                mysqli_stmt_execute($image_stmt);
                $image_result = mysqli_stmt_get_result($image_stmt);
                $image_data = mysqli_fetch_assoc($image_result);
                mysqli_stmt_close($image_stmt);
                
                $query = "DELETE FROM center_image WHERE id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "i", $id);
                
                if (mysqli_stmt_execute($stmt)) {
                    // Delete image file
                    if ($image_data && file_exists($upload_dir . $image_data['image'])) {
                        unlink($upload_dir . $image_data['image']);
                    }
                    $_SESSION['success_message'] = "Center image deleted successfully!";
                } else {
                    $_SESSION['error_message'] = "Error deleting center image: " . mysqli_error($conn);
                }
                mysqli_stmt_close($stmt);
                header('Location: center_image.php');
                exit();
                break;
        }
    }
}

// Get center image for editing
$edit_image = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $edit_query = "SELECT * FROM center_image WHERE id = ?";
    $edit_stmt = mysqli_prepare($conn, $edit_query);
    mysqli_stmt_bind_param($edit_stmt, "i", $edit_id);
    mysqli_stmt_execute($edit_stmt);
    $edit_result = mysqli_stmt_get_result($edit_stmt);
    $edit_image = mysqli_fetch_assoc($edit_result);
    mysqli_stmt_close($edit_stmt);
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 9; // Show 9 images per page (3x3 grid)
$offset = ($page - 1) * $limit;

// Get total records
$count_query = "SELECT COUNT(*) as total FROM center_image";
$count_result = mysqli_query($conn, $count_query);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $limit);

// Get center images
$images_query = "SELECT * FROM center_image ORDER BY id DESC LIMIT $limit OFFSET $offset";
$images_result = mysqli_query($conn, $images_query);

include 'components/header.php';
include 'components/sidebar.php';
include 'components/topbar.php';
?>

<!-- Page Header -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Center Image Management</h1>
        <p class="text-gray-600">Manage center images for your website</p>
    </div>
    <?php if ($total_records == 0): ?>
    <button type="button" onclick="document.getElementById('imageModal').classList.remove('hidden')" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors duration-200">
        <i class="fas fa-plus mr-2"></i>
        Add Center Image
    </button>
    <?php endif; ?>
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

<!-- Info Notice for Center Image Limit -->
<?php if ($total_records > 0): ?>
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <div class="flex items-center">
            <i class="fas fa-info-circle text-blue-600 mr-3"></i>
            <div class="text-blue-700">
                <p class="font-medium">Center Image Management</p>
                <p class="text-sm mt-1">Only one center image is allowed. You can edit or replace the existing image below.</p>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Image Gallery -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <?php if (mysqli_num_rows($images_result) > 0): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php while ($image = mysqli_fetch_assoc($images_result)): ?>
                <div class="bg-gray-50 rounded-lg overflow-hidden border border-gray-200 hover:border-gray-300 transition-colors duration-200">
                    <!-- Image -->
                    <div class="aspect-w-16 aspect-h-9 bg-gray-200">
                        <img src="<?= $upload_dir . htmlspecialchars($image['image']) ?>" 
                             alt="Center Image" 
                             class="w-full h-48 object-cover cursor-pointer"
                             onclick="viewImage('<?= $upload_dir . htmlspecialchars($image['image']) ?>')">
                    </div>
                    
                    <!-- Actions -->
                    <div class="p-4">
                        <div class="flex items-center justify-between">
                            
                            <div class="flex space-x-2">
                                <button onclick="viewImage('<?= $upload_dir . htmlspecialchars($image['image']) ?>')" 
                                        class="p-2 text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors duration-200"
                                        title="View Image">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button onclick="editImage(<?= htmlspecialchars(json_encode($image)) ?>)" 
                                        class="p-2 text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors duration-200"
                                        title="Edit Image">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deleteImage(<?= $image['id'] ?>, '<?= htmlspecialchars($image['image']) ?>')" 
                                        class="p-2 text-gray-600 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200"
                                        title="Delete Image">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-12">
            <div class="flex flex-col items-center justify-center">
                <i class="fas fa-image text-6xl text-gray-300 mb-4"></i>
                <p class="text-xl font-medium text-gray-500 mb-2">No center images found</p>
                <p class="text-gray-400 mb-6">Get started by uploading your first center image</p>
                <button type="button" onclick="document.getElementById('imageModal').classList.remove('hidden')" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors duration-200">
                    <i class="fas fa-plus mr-2"></i>
                    Add First Image
                </button>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
    <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6 mt-6 rounded-lg shadow-sm border">
        <div class="flex-1 flex justify-between sm:hidden">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Previous</a>
            <?php endif; ?>
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?= $page + 1 ?>" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Next</a>
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
                        <a href="?page=<?= $i ?>" class="<?= $i == $page ? 'bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50' ?> relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </nav>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Image Modal -->
<div id="imageModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="closeModal()"></div>
        <div class="inline-block w-full max-w-md my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-lg">
            <form id="imageForm" method="POST" action="center_image.php" enctype="multipart/form-data">
                <input type="hidden" name="action" id="formAction" value="create">
                <input type="hidden" name="id" id="imageId">
                
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900" id="modalTitle">Add Center Image</h3>
                        <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeModal()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                
                <div class="px-6 py-4">
                    <div class="space-y-4">
                        <div>
                            <label for="image" class="block text-sm font-medium text-gray-700 mb-2">
                                Center Image <span class="text-red-500">*</span>
                            </label>
                            <input type="file" id="image" name="image" required accept="image/jpeg,image/jpg,image/png,image/webp" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="previewImage(this, 'imagePreview')">
                            <p class="mt-1 text-xs text-gray-500">Supported formats: JPG, JPEG, PNG, WEBP</p>
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
                        <span id="submitText">Upload Image</span>
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
                    <h3 class="text-lg font-semibold text-gray-900">View Center Image</h3>
                    <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeViewModal()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="p-6">
                <img id="viewImage" src="" alt="Center Image" class="w-full h-auto max-h-96 object-contain mx-auto rounded-lg">
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
                        <h3 class="text-lg font-semibold text-gray-900">Delete Center Image</h3>
                        <p class="text-sm text-gray-500 mt-1">Are you sure you want to delete <span id="deleteImageName" class="font-medium"></span>? This action cannot be undone.</p>
                    </div>
                </div>
            </div>
            
            <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors duration-200">
                    Cancel
                </button>
                <form id="deleteForm" method="POST" action="center_image.php" class="inline">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="deleteImageId">
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors duration-200">
                        Delete Image
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function closeModal() {
    document.getElementById('imageModal').classList.add('hidden');
    document.getElementById('imageForm').reset();
    document.getElementById('formAction').value = 'create';
    document.getElementById('imageId').value = '';
    document.getElementById('modalTitle').textContent = 'Add Center Image';
    document.getElementById('submitText').textContent = 'Upload Image';
    document.getElementById('imagePreview').classList.add('hidden');
    document.getElementById('image').required = true;
}

function editImage(image) {
    document.getElementById('imageModal').classList.remove('hidden');
    document.getElementById('formAction').value = 'update';
    document.getElementById('imageId').value = image.id;
    document.getElementById('modalTitle').textContent = 'Update Center Image';
    document.getElementById('submitText').textContent = 'Update Image';
    document.getElementById('image').required = true;
    
    // Show current image preview
    const preview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    previewImg.src = 'uploads/center_images/' + image.image;
    preview.classList.remove('hidden');
}

function viewImage(imageSrc) {
    document.getElementById('viewModal').classList.remove('hidden');
    document.getElementById('viewImage').src = imageSrc;
}

function closeViewModal() {
    document.getElementById('viewModal').classList.add('hidden');
}

function deleteImage(id, imageName) {
    document.getElementById('deleteModal').classList.remove('hidden');
    document.getElementById('deleteImageId').value = id;
    document.getElementById('deleteImageName').textContent = imageName;
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