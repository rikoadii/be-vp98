<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include 'db.php';
include 'components/ui-components.php';

$page_title = 'Contact Management';

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $contact = mysqli_real_escape_string($conn, $_POST['contact']);
                
                // Validasi: harus diawali dengan 62
                if (!preg_match('/^62\d+$/', $contact)) {
                    $_SESSION['error_message'] = "Contact number must start with 62 and contain only numbers!";
                    header('Location: contact.php');
                    exit();
                }
                
                // Cek apakah sudah ada contact
                $check_query = "SELECT COUNT(*) as total FROM contact";
                $check_result = mysqli_query($conn, $check_query);
                $total_contacts = mysqli_fetch_assoc($check_result)['total'];
                
                if ($total_contacts > 0) {
                    $_SESSION['error_message'] = "Only one contact is allowed!";
                    header('Location: contact.php');
                    exit();
                }
                
                $query = "INSERT INTO contact (contact) VALUES (?)";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "s", $contact);
                
                if (mysqli_stmt_execute($stmt)) {
                    $_SESSION['success_message'] = "Contact created successfully!";
                } else {
                    $_SESSION['error_message'] = "Error creating contact: " . mysqli_error($conn);
                }
                mysqli_stmt_close($stmt);
                header('Location: contact.php');
                exit();
                break;
                
            case 'update':
                $id = (int)$_POST['id'];
                $contact = mysqli_real_escape_string($conn, $_POST['contact']);
                
                // Validasi: harus diawali dengan 62
                if (!preg_match('/^62\d+$/', $contact)) {
                    $_SESSION['error_message'] = "Contact number must start with 62 and contain only numbers!";
                    header('Location: contact.php');
                    exit();
                }
                
                $query = "UPDATE contact SET contact = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "si", $contact, $id);
                
                if (mysqli_stmt_execute($stmt)) {
                    $_SESSION['success_message'] = "Contact updated successfully!";
                } else {
                    $_SESSION['error_message'] = "Error updating contact: " . mysqli_error($conn);
                }
                mysqli_stmt_close($stmt);
                header('Location: contact.php');
                exit();
                break;
                
            case 'delete':
                $id = (int)$_POST['id'];
                
                $query = "DELETE FROM contact WHERE id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "i", $id);
                
                if (mysqli_stmt_execute($stmt)) {
                    $_SESSION['success_message'] = "Contact deleted successfully!";
                } else {
                    $_SESSION['error_message'] = "Error deleting contact: " . mysqli_error($conn);
                }
                mysqli_stmt_close($stmt);
                header('Location: contact.php');
                exit();
                break;
        }
    }
}

// Get contact for editing
$edit_contact = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $edit_query = "SELECT * FROM contact WHERE id = ?";
    $edit_stmt = mysqli_prepare($conn, $edit_query);
    mysqli_stmt_bind_param($edit_stmt, "i", $edit_id);
    mysqli_stmt_execute($edit_stmt);
    $edit_result = mysqli_stmt_get_result($edit_stmt);
    $edit_contact = mysqli_fetch_assoc($edit_result);
    mysqli_stmt_close($edit_stmt);
}

// Search functionality - removed
$search = '';
$where_clause = '';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get total records
$count_query = "SELECT COUNT(*) as total FROM contact";
$count_result = mysqli_query($conn, $count_query);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $limit);

// Get contacts
$contacts_query = "SELECT * FROM contact ORDER BY id DESC LIMIT $limit OFFSET $offset";
$contacts_result = mysqli_query($conn, $contacts_query);

// Check if contact already exists for button visibility
$has_contact = $total_records > 0;

include 'components/header.php';
include 'components/sidebar.php';
include 'components/topbar.php';
?>

<!-- Page Header -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Contact Management</h1>
        <p class="text-gray-600">Manage your contact list</p>
    </div>
    <?php if (!$has_contact): ?>
    <button type="button" onclick="document.getElementById('contactModal').classList.remove('hidden')" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors duration-200">
        <i class="fas fa-plus mr-2"></i>
        Add Contact
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

<!-- Contact Table -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (mysqli_num_rows($contacts_result) > 0): ?>
                    <?php while ($contact = mysqli_fetch_assoc($contacts_result)): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                            <i class="fas fa-user text-blue-600"></i>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?= htmlspecialchars($contact['contact']) ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button onclick="editContact(<?= htmlspecialchars(json_encode($contact)) ?>)" class="text-blue-600 hover:text-blue-900 transition-colors duration-200">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="deleteContact(<?= $contact['id'] ?>, '<?= htmlspecialchars($contact['contact']) ?>')" class="text-red-600 hover:text-red-900 transition-colors duration-200">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2" class="px-6 py-4 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center py-8">
                                <i class="fas fa-address-book text-4xl text-gray-300 mb-4"></i>
                                <p class="text-lg font-medium">No contacts found</p>
                                <p class="text-sm">Get started by adding your first contact</p>
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

<!-- Contact Modal -->
<div id="contactModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="closeModal()"></div>
        <div class="inline-block w-full max-w-md my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-lg">
            <form id="contactForm" method="POST" action="contact.php">
                <input type="hidden" name="action" id="formAction" value="create">
                <input type="hidden" name="id" id="contactId">
                
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900" id="modalTitle">Add New Contact</h3>
                        <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeModal()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                
                <div class="px-6 py-4">
                    <div class="space-y-4">
                        <div>
                            <label for="contact" class="block text-sm font-medium text-gray-700 mb-2">Contact <span class="text-red-500">*</span></label>
                            <input type="text" id="contact" name="contact" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="628xxxxx" pattern="^62\d+$" title="Contact number must start with 62 and contain only numbers">
                            <p class="mt-1 text-xs text-gray-500">Enter phone number starting with country code (e.g., 628xxxxx)</p>
                        </div>
                    </div>
                </div>
                
                <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors duration-200">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors duration-200">
                        <span id="submitText">Save Contact</span>
                    </button>
                </div>
            </form>
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
                        <h3 class="text-lg font-semibold text-gray-900">Delete Contact</h3>
                        <p class="text-sm text-gray-500 mt-1">Are you sure you want to delete <span id="deleteContactName" class="font-medium"></span>? This action cannot be undone.</p>
                    </div>
                </div>
            </div>
            
            <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors duration-200">
                    Cancel
                </button>
                <form id="deleteForm" method="POST" action="contact.php" class="inline">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="deleteContactId">
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors duration-200">
                        Delete Contact
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function closeModal() {
    document.getElementById('contactModal').classList.add('hidden');
    document.getElementById('contactForm').reset();
    document.getElementById('formAction').value = 'create';
    document.getElementById('contactId').value = '';
    document.getElementById('modalTitle').textContent = 'Add New Contact';
    document.getElementById('submitText').textContent = 'Save Contact';
}

function editContact(contact) {
    document.getElementById('contactModal').classList.remove('hidden');
    document.getElementById('formAction').value = 'update';
    document.getElementById('contactId').value = contact.id;
    document.getElementById('contact').value = contact.contact;
    document.getElementById('modalTitle').textContent = 'Edit Contact';
    document.getElementById('submitText').textContent = 'Update Contact';
}

function deleteContact(id, name) {
    document.getElementById('deleteModal').classList.remove('hidden');
    document.getElementById('deleteContactId').value = id;
    document.getElementById('deleteContactName').textContent = name;
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

// Phone number formatting
document.getElementById('contact').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.startsWith('0')) {
        value = '62' + value.substring(1);
    }
    e.target.value = value;
});
</script>

<?php include 'components/footer.php'; ?>