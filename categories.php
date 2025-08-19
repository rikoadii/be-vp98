<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include 'db.php';
include 'components/ui-components.php';

$page_title = 'Categories Management';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action == 'create') {
            $categories_name = mysqli_real_escape_string($conn, $_POST['categories_name']);
            
            if (!empty($categories_name)) {
                $query = "INSERT INTO categories (categories_name) VALUES ('$categories_name')";
                if (mysqli_query($conn, $query)) {
                    $success = "Category successfully added!";
                } else {
                    $error = "Error: " . mysqli_error($conn);
                }
            } else {
                $error = "Category name cannot be empty!";
            }
        }
        
        if ($action == 'update') {
            $id = $_POST['id_categories'];
            $categories_name = mysqli_real_escape_string($conn, $_POST['categories_name']);
            
            if (!empty($categories_name)) {
                $query = "UPDATE categories SET categories_name='$categories_name' WHERE id_categories=$id";
                if (mysqli_query($conn, $query)) {
                    $success = "Category successfully updated!";
                } else {
                    $error = "Error: " . mysqli_error($conn);
                }
            } else {
                $error = "Category name cannot be empty!";
            }
        }
        
        if ($action == 'delete') {
            $id = $_POST['id_categories'];
            
            $query = "DELETE FROM categories WHERE id=$id";
            if (mysqli_query($conn, $query)) {
                $success = "Category successfully deleted!";
            } else {
                $error = "Error: " . mysqli_error($conn);
            }
        }
    }
}

// Get all categories data
$query = "SELECT * FROM categories ORDER BY id_categories DESC";
$result = mysqli_query($conn, $query);

// Get single category for editing
$edit_category = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $edit_query = "SELECT * FROM categories WHERE id_categories=$edit_id";
    $edit_result = mysqli_query($conn, $edit_query);
    $edit_category = mysqli_fetch_assoc($edit_result);
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
                    <?= $edit_category ? 'Edit Category' : 'Add New Category' ?>
                </h3>
            </div>
            <div class="p-6">
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="<?= $edit_category ? 'update' : 'create' ?>">
                    <?php if ($edit_category): ?>
                        <input type="hidden" name="id_categories" value="<?= $edit_category['id_categories'] ?>">
                    <?php endif; ?>
                    
                    <?php 
                    createInput('categories_name', 'Category Name', 'text', $edit_category ? $edit_category['categories_name'] : '', true, 'Enter category name');
                    ?>
                    
                    <div class="flex space-x-3">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200">
                            <i class="fas <?= $edit_category ? 'fa-save' : 'fa-plus' ?> mr-2"></i>
                            <?= $edit_category ? 'Update Category' : 'Add Category' ?>
                        </button>
                        
                        <?php if ($edit_category): ?>
                            <a href="categories.php" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-900 text-sm font-medium rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors duration-200">
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
                <h3 class="text-lg font-semibold text-gray-900">Categories List</h3>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table id="categoriesTable" class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th class="px-6 py-3">Category Name</th>
                                <th class="px-6 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($result) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                    <tr class="bg-white border-b hover:bg-gray-50">
                                        <td class="px-6 py-4"><?= htmlspecialchars($row['categories_name']) ?></td>
                                        <td class="px-6 py-4">
                                            <div class="flex space-x-2">
                                                <a href="categories.php?edit=<?= $row['id_categories'] ?>" class="inline-flex items-center px-3 py-1.5 bg-yellow-100 text-yellow-800 text-xs font-medium rounded-lg hover:bg-yellow-200 transition-colors duration-200">
                                                    <i class="fas fa-edit mr-1"></i>
                                                    Edit
                                                </a>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this category?')">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id_categories" value="<?= $row['id_categories'] ?>">
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
                                    <td colspan="3" class="px-6 py-8 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <i class="fas fa-folder-open text-4xl text-gray-300 mb-2"></i>
                                            <p>No categories found</p>
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

<script>
$(document).ready(function() {
    $('#categoriesTable').DataTable({
        responsive: true,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        language: {
            search: "Search categories:",
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
