<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include 'db.php';
include 'components/ui-components.php';

$page_title = 'Dashboard';

// Get statistics
$stats = [];

// Count teams
$teams_query = "SELECT COUNT(*) as total FROM teams";
$teams_result = mysqli_query($conn, $teams_query);
$stats['teams'] = mysqli_fetch_assoc($teams_result)['total'];

// Count categories
$categories_query = "SELECT COUNT(*) as total FROM categories";
$categories_result = mysqli_query($conn, $categories_query);
$stats['categories'] = mysqli_fetch_assoc($categories_result)['total'];

// Get recent teams
$recent_teams_query = "SELECT * FROM teams ORDER BY id DESC LIMIT 5";
$recent_teams_result = mysqli_query($conn, $recent_teams_query);

include 'components/header.php';
include 'components/sidebar.php';
include 'components/topbar.php';
?>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <!-- Total Teams Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-blue-600 text-xl"></i>
                </div>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total Teams</p>
                <p class="text-2xl font-bold text-gray-900"><?= $stats['teams'] ?></p>
            </div>
        </div>
    </div>
    
    <!-- Categories Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-folder-open text-purple-600 text-xl"></i>
                </div>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Categories</p>
                <p class="text-2xl font-bold text-gray-900"><?= $stats['categories'] ?></p>
            </div>
        </div>
    </div>
    
    <!-- Projects Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-project-diagram text-green-600 text-xl"></i>
                </div>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Projects</p>
                <p class="text-2xl font-bold text-gray-900">0</p>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent Teams -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Recent Teams</h3>
                <a href="teams.php" class="text-sm text-blue-600 hover:text-blue-800 font-medium">View All</a>
            </div>
        </div>
        <div class="p-6">
            <?php if (mysqli_num_rows($recent_teams_result) > 0): ?>
                <div class="space-y-4">
                    <?php while ($team = mysqli_fetch_assoc($recent_teams_result)): ?>
                        <div class="flex items-center space-x-4 p-3 rounded-lg border border-gray-100 hover:bg-gray-50 transition-colors duration-200">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user text-blue-600"></i>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">
                                    <?= htmlspecialchars($team['name']) ?>
                                </p>
                                <p class="text-sm text-gray-500 truncate">
                                    <?= htmlspecialchars($team['role']) ?>
                                </p>
                            </div>
                            <div class="flex-shrink-0">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Active
                                </span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <i class="fas fa-users text-4xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500">No teams yet</p>
                    <a href="teams.php" class="mt-2 inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors duration-200">
                        <i class="fas fa-plus mr-2"></i>
                        Add First Team
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 gap-4">
                <a href="teams.php" class="flex items-center p-4 rounded-lg border border-gray-200 hover:border-blue-300 hover:bg-blue-50 transition-colors duration-200 group">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-blue-100 group-hover:bg-blue-200 rounded-lg flex items-center justify-center transition-colors duration-200">
                            <i class="fas fa-users text-blue-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-900">Manage Teams</p>
                        <p class="text-sm text-gray-500">Add, edit or remove team members</p>
                    </div>
                    <div class="ml-auto">
                        <i class="fas fa-chevron-right text-gray-400 group-hover:text-blue-600 transition-colors duration-200"></i>
                    </div>
                </a>
                
                <a href="categories.php" class="flex items-center p-4 rounded-lg border border-gray-200 hover:border-purple-300 hover:bg-purple-50 transition-colors duration-200 group">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-purple-100 group-hover:bg-purple-200 rounded-lg flex items-center justify-center transition-colors duration-200">
                            <i class="fas fa-folder-open text-purple-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-900">Manage Categories</p>
                        <p class="text-sm text-gray-500">Add, edit or remove categories</p>
                    </div>
                    <div class="ml-auto">
                        <i class="fas fa-chevron-right text-gray-400 group-hover:text-purple-600 transition-colors duration-200"></i>
                    </div>
                </a>
                
                <a href="projects.php" class="flex items-center p-4 rounded-lg border border-gray-200 hover:border-green-300 hover:bg-green-50 transition-colors duration-200 group">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-green-100 group-hover:bg-green-200 rounded-lg flex items-center justify-center transition-colors duration-200">
                            <i class="fas fa-project-diagram text-green-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-900">Manage Projects</p>
                        <p class="text-sm text-gray-500">Create and manage projects</p>
                    </div>
                    <div class="ml-auto">
                        <i class="fas fa-chevron-right text-gray-400 group-hover:text-green-600 transition-colors duration-200"></i>
                    </div>
                </a>
                
            </div>
        </div>
    </div>
</div>


<?php include 'components/footer.php'; ?>