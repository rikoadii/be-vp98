<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include 'db.php';
include 'components/ui-components.php';

$page_title = 'Projects Management';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action == 'create') {
            $name_projects = mysqli_real_escape_string($conn, $_POST['name_projects']);
            $location_projects = mysqli_real_escape_string($conn, $_POST['location_projects']);
            $description_projects = mysqli_real_escape_string($conn, $_POST['description_projects']);
            $is_main = isset($_POST['is_main']) ? 1 : 0;
            $id_categories = mysqli_real_escape_string($conn, $_POST['id_categories']);
            
            // Handle file upload
            $image_project = '';
            if (isset($_FILES['image_project']) && $_FILES['image_project']['error'] == 0) {
                $target_dir = "uploads/projects/";
                // Create directory if it doesn't exist
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                
                $file_extension = strtolower(pathinfo($_FILES["image_project"]["name"], PATHINFO_EXTENSION));
                $image_project = uniqid() . '.' . $file_extension;
                $target_file = $target_dir . $image_project;
                
                // Check if image file is actual image
                $allowed_types = array('jpg', 'jpeg', 'png', 'webp');
                if (in_array($file_extension, $allowed_types)) {
                    if (move_uploaded_file($_FILES["image_project"]["tmp_name"], $target_file)) {
                        // File uploaded successfully
                    } else {
                        $error = "Error uploading image file.";
                    }
                } else {
                    $error = "Only JPG, JPEG, PNG & WEBP files are allowed.";
                }
            }
            
            if (!isset($error) && !empty($name_projects) && !empty($id_categories)) {
                $query = "INSERT INTO projects (name_projects, location_projects, description_projects, is_main, image_project, id_categories) 
                         VALUES ('$name_projects', '$location_projects', '$description_projects', $is_main, '$image_project', '$id_categories')";
                if (mysqli_query($conn, $query)) {
                    $success = "Project successfully added!";
                } else {
                    $error = "Error: " . mysqli_error($conn);
                }
            } elseif (!isset($error)) {
                $error = "Project name and category are required!";
            }
        }
        
        if ($action == 'update') {
            $project_id = $_POST['project_id'];
            $name_projects = mysqli_real_escape_string($conn, $_POST['name_projects']);
            $location_projects = mysqli_real_escape_string($conn, $_POST['location_projects']);
            $description_projects = mysqli_real_escape_string($conn, $_POST['description_projects']);
            $is_main = isset($_POST['is_main']) ? 1 : 0;
            $id_categories = mysqli_real_escape_string($conn, $_POST['id_categories']);
            
            // Handle file upload
            $image_update = '';
            if (isset($_FILES['image_project']) && $_FILES['image_project']['error'] == 0) {
                $target_dir = "uploads/projects/";
                // Create directory if it doesn't exist
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                
                $file_extension = strtolower(pathinfo($_FILES["image_project"]["name"], PATHINFO_EXTENSION));
                $new_image = uniqid() . '.' . $file_extension;
                $target_file = $target_dir . $new_image;
                
                $allowed_types = array('jpg', 'jpeg', 'png', 'webp');
                if (in_array($file_extension, $allowed_types)) {
                    if (move_uploaded_file($_FILES["image_project"]["tmp_name"], $target_file)) {
                        $image_update = ", image_project='$new_image'";
                        
                        // Delete old image
                        $old_image_query = "SELECT image_project FROM projects WHERE project_id=$project_id";
                        $old_image_result = mysqli_query($conn, $old_image_query);
                        $old_image_row = mysqli_fetch_assoc($old_image_result);
                        if ($old_image_row['image_project'] && file_exists("uploads/projects/" . $old_image_row['image_project'])) {
                            unlink("uploads/projects/" . $old_image_row['image_project']);
                        }
                    } else {
                        $error = "Error uploading image file.";
                    }
                } else {
                    $error = "Only JPG, JPEG, PNG & WEBP files are allowed.";
                }
            }
            
            if (!isset($error) && !empty($name_projects) && !empty($id_categories)) {
                $query = "UPDATE projects SET name_projects='$name_projects', location_projects='$location_projects', 
                         description_projects='$description_projects', is_main=$is_main, id_categories='$id_categories'$image_update 
                         WHERE project_id=$project_id";
                if (mysqli_query($conn, $query)) {
                    $success = "Project successfully updated!";
                } else {
                    $error = "Error: " . mysqli_error($conn);
                }
            } elseif (!isset($error)) {
                $error = "Project name and category are required!";
            }
        }
        
        if ($action == 'delete') {
            $project_id = $_POST['project_id'];
            
            // Get image filename before deleting
            $image_query = "SELECT image_project FROM projects WHERE project_id=$project_id";
            $image_result = mysqli_query($conn, $image_query);
            $image_row = mysqli_fetch_assoc($image_result);
            
            $query = "DELETE FROM projects WHERE project_id=$project_id";
            if (mysqli_query($conn, $query)) {
                // Delete image file
                if ($image_row['image_project'] && file_exists("uploads/projects/" . $image_row['image_project'])) {
                    unlink("uploads/projects/" . $image_row['image_project']);
                }
                $success = "Project successfully deleted!";
            } else {
                $error = "Error: " . mysqli_error($conn);
            }
        }
    }
}

// Get all projects data with category names
$query = "SELECT p.*, c.categories_name 
          FROM projects p 
          LEFT JOIN categories c ON p.id_categories = c.id_categories 
          ORDER BY p.project_id DESC";
$result = mysqli_query($conn, $query);

// Get categories for dropdown
$categories_query = "SELECT * FROM categories ORDER BY categories_name";
$categories_result = mysqli_query($conn, $categories_query);
$categories = [];
while ($cat = mysqli_fetch_assoc($categories_result)) {
    $categories[$cat['id_categories']] = $cat['categories_name'];
}

// Get single project for editing
$edit_project = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $edit_query = "SELECT * FROM projects WHERE project_id=$edit_id";
    $edit_result = mysqli_query($conn, $edit_query);
    $edit_project = mysqli_fetch_assoc($edit_result);
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
                    <?= $edit_project ? 'Edit Project' : 'Add New Project' ?>
                </h3>
            </div>
            <div class="p-6">
                <form method="POST" enctype="multipart/form-data" class="space-y-4">
                    <input type="hidden" name="action" value="<?= $edit_project ? 'update' : 'create' ?>">
                    <?php if ($edit_project): ?>
                        <input type="hidden" name="project_id" value="<?= $edit_project['project_id'] ?>">
                    <?php endif; ?>
                    
                    <?php 
                    createInput('name_projects', 'Project Name', 'text', $edit_project ? $edit_project['name_projects'] : '', true, 'Enter project name');
                    createInput('location_projects', 'Location', 'text', $edit_project ? $edit_project['location_projects'] : '', false, 'Enter project location');
                    createInput('description_projects', 'Description', 'textarea', $edit_project ? $edit_project['description_projects'] : '', false, 'Enter project description');
                    createInput('id_categories', 'Category', 'select', $edit_project ? $edit_project['id_categories'] : '', true, 'Select a category', $categories);
                    createInput('image_project', 'Project Image', 'file', $edit_project ? $edit_project['image_project'] : '', false);
                    createInput('is_main', 'Main Project', 'checkbox', $edit_project ? $edit_project['is_main'] : '', false, 'Mark as main project');
                    ?>
                    
                    <div class="flex space-x-3">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200">
                            <i class="fas <?= $edit_project ? 'fa-save' : 'fa-plus' ?> mr-2"></i>
                            <?= $edit_project ? 'Update Project' : 'Add Project' ?>
                        </button>
                        
                        <?php if ($edit_project): ?>
                            <a href="projects.php" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-900 text-sm font-medium rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors duration-200">
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
                <h3 class="text-lg font-semibold text-gray-900">Projects List</h3>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table id="projectsTable" class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th class="px-6 py-3">Image</th>
                                <th class="px-6 py-3">Project Name</th>
                                <th class="px-6 py-3">Location</th>
                                <th class="px-6 py-3">Category</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($result) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                    <tr class="bg-white border-b hover:bg-gray-50">
                                        <td class="px-6 py-4">
                                            <?php if ($row['image_project']): ?>
                                                <img src="uploads/projects/<?= htmlspecialchars($row['image_project']) ?>" alt="Project Image" class="w-12 h-12 object-cover rounded-lg border cursor-pointer" onclick="showImageModal('uploads/projects/<?= htmlspecialchars($row['image_project']) ?>', '<?= htmlspecialchars($row['name_projects']) ?>')">
                                            <?php else: ?>
                                                <div class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center">
                                                    <i class="fas fa-image text-gray-400"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="font-medium text-gray-900"><?= htmlspecialchars($row['name_projects']) ?></div>
                                            <?php if ($row['description_projects']): ?>
                                                <div class="text-sm text-gray-500"><?= htmlspecialchars(substr($row['description_projects'], 0, 50)) ?>...</div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4"><?= htmlspecialchars($row['location_projects']) ?></td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <?= htmlspecialchars($row['categories_name']) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <?php if ($row['is_main']): ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <i class="fas fa-star mr-1"></i>
                                                    Main
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    Regular
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex space-x-2">
                                                <a href="projects.php?edit=<?= $row['project_id'] ?>" class="inline-flex items-center px-3 py-1.5 bg-yellow-100 text-yellow-800 text-xs font-medium rounded-lg hover:bg-yellow-200 transition-colors duration-200">
                                                    <i class="fas fa-edit mr-1"></i>
                                                    Edit
                                                </a>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this project?')">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="project_id" value="<?= $row['project_id'] ?>">
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
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <i class="fas fa-project-diagram text-4xl text-gray-300 mb-2"></i>
                                            <p>No projects found</p>
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
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 id="modalTitle" class="text-lg font-semibold text-gray-900">Project Image</h3>
                <button onclick="closeImageModal()" class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="p-6">
                <img id="modalImage" src="" alt="Project Image" class="w-full h-auto max-h-96 object-contain rounded-lg">
            </div>
        </div>
    </div>
</div>

<script>
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `
                <p class="text-sm text-gray-600 mb-2">Image preview:</p>
                <div class="image-preview-container">
                    <img src="${e.target.result}" alt="Preview" class="w-24 h-24 object-cover rounded-lg border-2 border-gray-200 shadow-sm">
                </div>
            `;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Show image modal function
function showImageModal(imageSrc, title) {
    document.getElementById('modalImage').src = imageSrc;
    document.getElementById('modalTitle').textContent = title + ' - Project Image';
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
    $('#projectsTable').DataTable({
        responsive: true,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        language: {
            search: "Search projects:",
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