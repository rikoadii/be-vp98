<?php
session_start();
include 'db.php';
include 'components/ui-components.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$page_title = 'Profile';
$user_id = $_SESSION['user_id'];

// Get user data
$user_query = "SELECT * FROM users WHERE id = $user_id";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action == 'update_profile') {
            $username = mysqli_real_escape_string($conn, $_POST['username']);
            
            // Handle profile image upload
            $profile_update = '';
            if (isset($_FILES['profile']) && $_FILES['profile']['error'] == 0) {
                $target_dir = "uploads/profiles/";
                
                // Create directory if it doesn't exist
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                
                $file_extension = strtolower(pathinfo($_FILES["profile"]["name"], PATHINFO_EXTENSION));
                $new_profile = uniqid() . '.' . $file_extension;
                $target_file = $target_dir . $new_profile;
                
                $allowed_types = array('jpg', 'jpeg', 'png', 'webp');
                if (in_array($file_extension, $allowed_types)) {
                    if (move_uploaded_file($_FILES["profile"]["tmp_name"], $target_file)) {
                        $profile_update = ", profile='$new_profile'";
                        
                        // Delete old profile image
                        if ($user['profile'] && file_exists("uploads/profiles/" . $user['profile'])) {
                            unlink("uploads/profiles/" . $user['profile']);
                        }
                    } else {
                        $error = "Error uploading profile image.";
                    }
                } else {
                    $error = "Only JPG, JPEG, PNG & WEBP files are allowed.";
                }
            }
            
            if (!isset($error) && !empty($username)) {
                $update_query = "UPDATE users SET username='$username'$profile_update WHERE id=$user_id";
                if (mysqli_query($conn, $update_query)) {
                    $_SESSION['username'] = $username;
                    if ($profile_update) {
                        $_SESSION['profile'] = $new_profile;
                    }
                    $success = "Profile updated successfully!";
                    
                    // Refresh user data
                    $user_result = mysqli_query($conn, $user_query);
                    $user = mysqli_fetch_assoc($user_result);
                } else {
                    $error = "Error: " . mysqli_error($conn);
                }
            } elseif (!isset($error)) {
                $error = "Username is required!";
            }
        }
        
        if ($action == 'update_password') {
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];
            
            if (!empty($current_password) && !empty($new_password) && !empty($confirm_password)) {
                // Verify current password
                if (password_verify($current_password, $user['password'])) {
                    if ($new_password === $confirm_password) {
                        if (strlen($new_password) >= 6) {
                            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                            $password_query = "UPDATE users SET password='$hashed_password' WHERE id=$user_id";
                            
                            if (mysqli_query($conn, $password_query)) {
                                $success = "Password updated successfully!";
                            } else {
                                $error = "Error updating password: " . mysqli_error($conn);
                            }
                        } else {
                            $error = "New password must be at least 6 characters long!";
                        }
                    } else {
                        $error = "New passwords do not match!";
                    }
                } else {
                    $error = "Current password is incorrect!";
                }
            } else {
                $error = "All password fields are required!";
            }
        }
    }
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

<div class="max-w-4xl mx-auto">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Profile Overview Card -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Profile Overview</h3>
                </div>
                <div class="p-6 text-center">
                    <div class="mb-4">
                        <?php if ($user['profile']): ?>
                            <img src="uploads/profiles/<?= htmlspecialchars($user['profile']) ?>" 
                                 alt="Profile" 
                                 class="w-24 h-24 rounded-full mx-auto object-cover border-4 border-gray-200 shadow-sm">
                        <?php else: ?>
                            <div class="w-24 h-24 bg-blue-100 rounded-full mx-auto flex items-center justify-center border-4 border-gray-200">
                                <i class="fas fa-user text-blue-600 text-2xl"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <h4 class="text-xl font-semibold text-gray-900 mb-1">
                        <?= htmlspecialchars($user['username']) ?>
                    </h4>
                    <p class="text-sm text-gray-500 mb-4">Administrator</p>
                </div>
            </div>
        </div>

        <!-- Profile Settings -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Update Profile Form -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-user-edit mr-2"></i>
                        Update Profile
                    </h3>
                </div>
                <div class="p-6">
                    <form method="POST" enctype="multipart/form-data" class="space-y-4">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <?php 
                        createInput('username', 'Username', 'text', $user['username'], true, 'Enter your username');
                        createInput('profile', 'Profile Image', 'file', $user['profile'], false);
                        ?>
                        
                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center px-6 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200">
                                <i class="fas fa-save mr-2"></i>
                                Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Update Password Form -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-lock mr-2"></i>
                        Change Password
                    </h3>
                </div>
                <div class="p-6">
                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="action" value="update_password">
                        
                        <div>
                            <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">
                                Current Password <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-lock text-gray-400"></i>
                                </div>
                                <input type="password" id="current_password" name="current_password" required
                                       class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                                       placeholder="Enter current password">
                            </div>
                        </div>
                        
                        <div>
                            <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">
                                New Password <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-key text-gray-400"></i>
                                </div>
                                <input type="password" id="new_password" name="new_password" required
                                       class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                                       placeholder="Enter new password (min. 6 characters)">
                            </div>
                        </div>
                        
                        <div>
                            <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">
                                Confirm New Password <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-key text-gray-400"></i>
                                </div>
                                <input type="password" id="confirm_password" name="confirm_password" required
                                       class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                                       placeholder="Confirm new password">
                            </div>
                        </div>
                        
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <i class="fas fa-info-circle text-yellow-400 mt-0.5 mr-3"></i>
                                <div class="text-yellow-800 text-sm">
                                    <p class="font-medium mb-1">Password Requirements:</p>
                                    <ul class="list-disc list-inside space-y-1">
                                        <li>Minimum 6 characters long</li>
                                        <li>Use a strong, unique password</li>
                                        <li>Don't reuse old passwords</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center px-6 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors duration-200">
                                <i class="fas fa-shield-alt mr-2"></i>
                                Update Password
                            </button>
                        </div>
                    </form>
                </div>
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
</script>

<?php include 'components/footer.php'; ?>