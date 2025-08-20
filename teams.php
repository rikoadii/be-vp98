<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include 'db.php';
include 'components/ui-components.php';

$page_title = 'Teams Management';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action == 'create') {
            $name = mysqli_real_escape_string($conn, $_POST['name']);
            $role = mysqli_real_escape_string($conn, $_POST['role']);
            
            // Handle file upload
            $profile = '';
            if (isset($_FILES['profile']) && $_FILES['profile']['error'] == 0) {
                $target_dir = "uploads/teams/";
                // Create directory if it doesn't exist
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                
                $file_extension = strtolower(pathinfo($_FILES["profile"]["name"], PATHINFO_EXTENSION));
                $allowed_extensions = array("jpg", "jpeg", "png", "webp");
                
                if (in_array($file_extension, $allowed_extensions)) {
                    $new_filename = uniqid() . '.' . $file_extension;
                    $target_file = $target_dir . $new_filename;
                    
                    if (move_uploaded_file($_FILES["profile"]["tmp_name"], $target_file)) {
                        $profile = $new_filename;
                    } else {
                        $error = "Error uploading image file.";
                    }
                } else {
                    $error = "Only JPG, JPEG, PNG & WEBP files are allowed.";
                }
            }
            
            if (!isset($error)) {
                $query = "INSERT INTO teams (name, role, profile) VALUES ('$name', '$role', '$profile')";
                if (mysqli_query($conn, $query)) {
                    $success = "Data berhasil ditambahkan!";
                } else {
                    $error = "Error: " . mysqli_error($conn);
                }
            }
        }
        
        if ($action == 'update') {
            $id = $_POST['id'];
            $name = mysqli_real_escape_string($conn, $_POST['name']);
            $role = mysqli_real_escape_string($conn, $_POST['role']);
            
            // Get current image
            $current_query = "SELECT profile FROM teams WHERE id=$id";
            $current_result = mysqli_query($conn, $current_query);
            $current_data = mysqli_fetch_assoc($current_result);
            $profile = $current_data['profile'];
            
            // Handle file upload
            if (isset($_FILES['profile']) && $_FILES['profile']['error'] == 0) {
                $target_dir = "uploads/teams/";
                // Create directory if it doesn't exist
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                
                $file_extension = strtolower(pathinfo($_FILES["profile"]["name"], PATHINFO_EXTENSION));
                $allowed_extensions = array("jpg", "jpeg", "png", "webp");
                
                if (in_array($file_extension, $allowed_extensions)) {
                    $new_filename = uniqid() . '.' . $file_extension;
                    $target_file = $target_dir . $new_filename;
                    
                    if (move_uploaded_file($_FILES["profile"]["tmp_name"], $target_file)) {
                        // Delete old image if exists
                        if ($profile && file_exists($target_dir . $profile)) {
                            unlink($target_dir . $profile);
                        }
                        $profile = $new_filename;
                    } else {
                        $error = "Error uploading image file.";
                    }
                } else {
                    $error = "Only JPG, JPEG, PNG & WEBP files are allowed.";
                }
            }
            
            if (!isset($error)) {
                $query = "UPDATE teams SET name='$name', role='$role', profile='$profile' WHERE id=$id";
                if (mysqli_query($conn, $query)) {
                    $success = "Data berhasil diupdate!";
                } else {
                    $error = "Error: " . mysqli_error($conn);
                }
            }
        }
        
        if ($action == 'delete') {
            $id = $_POST['id'];
            
            // Get image filename before deleting
            $image_query = "SELECT profile FROM teams WHERE id=$id";
            $image_result = mysqli_query($conn, $image_query);
            $image_data = mysqli_fetch_assoc($image_result);
            
            $query = "DELETE FROM teams WHERE id=$id";
            if (mysqli_query($conn, $query)) {
                // Delete image file if exists
                if ($image_data['profile'] && file_exists("uploads/teams/" . $image_data['profile'])) {
                    unlink("uploads/teams/" . $image_data['profile']);
                }
                $success = "Data berhasil dihapus!";
            } else {
                $error = "Error: " . mysqli_error($conn);
            }
        }
    }
}

// Get all teams data
$query = "SELECT * FROM teams ORDER BY id DESC";
$result = mysqli_query($conn, $query);

// Get single team for editing
$edit_team = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $edit_query = "SELECT * FROM teams WHERE id=$edit_id";
    $edit_result = mysqli_query($conn, $edit_query);
    $edit_team = mysqli_fetch_assoc($edit_result);
}

include 'components/header.php';
include 'components/sidebar.php';
include 'components/topbar.php';
?>

<!-- Alert Messages -->
<?php if (isset($success)): ?>
    <?php showAlert('success', $success); ?>
<?php endif; ?>

<?php if (isset($error)): ?>
    <?php showAlert('error', $error); ?>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Form Column -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">
                    <?= $edit_team ? 'Edit Team' : 'Add New Team' ?>
                </h3>
            </div>
            <div class="p-6">
                <form method="POST" enctype="multipart/form-data" class="space-y-4">
                    <input type="hidden" name="action" value="<?= $edit_team ? 'update' : 'create' ?>">
                    <?php if ($edit_team): ?>
                        <input type="hidden" name="id" value="<?= $edit_team['id'] ?>">
                    <?php endif; ?>
                    
                    <?php 
                    createInput('name', 'Name', 'text', $edit_team ? $edit_team['name'] : '', true, 'Enter team member name');
                    createInput('profile', 'Profile', 'file', $edit_team ? $edit_team['profile'] : '', false, '');
                    createInput('role', 'Role', 'text', $edit_team ? $edit_team['role'] : '', true, 'Enter role/position');
                    ?>
                    
                    <div class="flex space-x-3">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200">
                            <i class="fas <?= $edit_team ? 'fa-save' : 'fa-plus' ?> mr-2"></i>
                            <?= $edit_team ? 'Update Team' : 'Add Team' ?>
                        </button>
                        
                        <?php if ($edit_team): ?>
                            <a href="teams.php" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-900 text-sm font-medium rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors duration-200">
                                <i class="fas fa-times mr-2"></i>
                                Cancel
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Table Column -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Teams List</h3>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table id="teamsTable" class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th class="px-6 py-3">Name</th>
                                <th class="px-6 py-3">Profile</th>
                                <th class="px-6 py-3">Role</th>
                                <th class="px-6 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($result) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                    <tr class="bg-white border-b hover:bg-gray-50">
                                        <td class="px-6 py-4"><?= htmlspecialchars($row['name']) ?></td>
                                        <td class="px-6 py-4">
                                            <?php if ($row['profile']): ?>
                                                <img src="uploads/teams<?= htmlspecialchars($row['profile']) ?>" 
                                                     alt="Profile" 
                                                     class="w-12 h-12 object-cover rounded-full border border-gray-300 cursor-pointer hover:opacity-80 transition-opacity" 
                                                     onclick="showImageModal('uploads/<?= htmlspecialchars($row['profile']) ?>', '<?= htmlspecialchars($row['name']) ?>')">
                                            <?php else: ?>
                                                <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-user text-gray-400"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <?= htmlspecialchars($row['role']) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex space-x-2">
                                                <a href="teams.php?edit=<?= $row['id'] ?>" class="inline-flex items-center px-3 py-1.5 bg-yellow-100 text-yellow-800 text-xs font-medium rounded-lg hover:bg-yellow-200 transition-colors duration-200">
                                                    <i class="fas fa-edit mr-1"></i>
                                                    Edit
                                                </a>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this team?')">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-800 text-xs font-medium rounded-lg hover:bg-red-200 transition-colors duration-200">
                                                        <i class="fas fa-trash mr-1"></i>
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <i class="fas fa-users text-4xl text-gray-300 mb-2"></i>
                                            <p>No teams found</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div id="imageModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black bg-opacity-75">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center">
        <div class="inline-block w-full max-w-3xl my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 id="modalTitle" class="text-lg font-semibold text-gray-900">Profile</h3>
                    <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeImageModal()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="p-6 text-center">
                <img id="modalImage" src="" alt="Profile" class="max-w-full max-h-96 mx-auto rounded-lg shadow-lg">
            </div>
        </div>
    </div>
</div>

<style>
.image-preview-container {
    position: relative;
    display: inline-block;
}

.image-preview-remove {
    position: absolute;
    top: -8px;
    right: -8px;
    background: #ef4444;
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    cursor: pointer;
    border: 2px solid white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.image-preview-remove:hover {
    background: #dc2626;
}
</style>

<script>
// Image preview function
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.innerHTML = `
                <p class="text-sm text-gray-600 mb-2">Preview:</p>
                <div class="image-preview-container">
                    <img src="${e.target.result}" alt="Image preview" class="w-24 h-24 object-cover rounded-lg border-2 border-blue-200 shadow-sm">
                    <div class="image-preview-remove" onclick="clearImagePreview('${input.id}', '${previewId}')" title="Remove image">
                        <i class="fas fa-times"></i>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-2">Click the × to remove</p>
            `;
        }
        
        reader.readAsDataURL(input.files[0]);
    } else {
        // If no file selected, clear preview
        const currentImage = preview.querySelector('img');
        if (currentImage && !currentImage.src.includes('uploads/')) {
            preview.innerHTML = '';
        }
    }
}

// Clear image preview function
function clearImagePreview(inputId, previewId) {
    document.getElementById(inputId).value = '';
    document.getElementById(previewId).innerHTML = '';
}

// Show image modal function
function showImageModal(imageSrc, title) {
    document.getElementById('modalImage').src = imageSrc;
    document.getElementById('modalTitle').textContent = title + ' - Profile';
    document.getElementById('imageModal').classList.remove('hidden');
}

// Close image modal function
function closeImageModal() {
    document.getElementById('imageModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('imageModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeImageModal();
    }
});

$(document).ready(function() {
    $('#teamsTable').DataTable({
        responsive: true,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        language: {
            search: "Search teams:",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "No entries found",
            infoFiltered: "(filtered from _MAX_ total entries)",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        }
    });
});
</script>

<?php include 'components/footer.php'; ?>
