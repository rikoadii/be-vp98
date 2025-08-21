<!-- Sidebar -->
<div id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-gray-800 shadow-lg transform -translate-x-full transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0">
    <!-- Logo -->
    <div class="flex items-center justify-center h-16 px-4 bg-gradient-to-r from-blue-600 to-blue-700 dark:from-blue-700 dark:to-blue-800">
        <h1 class="text-xl font-bold text-white">Admin Panel</h1>
    </div>
    
    <!-- Navigation -->
    <nav class="mt-8">
        <div class="px-4 space-y-2">
            <!-- Dashboard -->
            <a href="index.php" class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-blue-50 dark:hover:bg-gray-700 hover:text-blue-600 dark:hover:text-blue-400 transition-colors duration-200 <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'bg-blue-50 dark:bg-gray-700 text-blue-600 dark:text-blue-400' : '' ?>">
                <i class="fas fa-tachometer-alt w-5 h-5 mr-3"></i>
                Dashboard
            </a>
            
            <!-- Teams -->
            <a href="teams.php" class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-blue-50 dark:hover:bg-gray-700 hover:text-blue-600 dark:hover:text-blue-400 transition-colors duration-200 <?= basename($_SERVER['PHP_SELF']) == 'teams.php' ? 'bg-blue-50 dark:bg-gray-700 text-blue-600 dark:text-blue-400' : '' ?>">
                <i class="fas fa-users w-5 h-5 mr-3"></i>
                Teams
            </a>
            
            <!-- Projects -->
            <a href="projects.php" class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-blue-50 dark:hover:bg-gray-700 hover:text-blue-600 dark:hover:text-blue-400 transition-colors duration-200 <?= basename($_SERVER['PHP_SELF']) == 'projects.php' ? 'bg-blue-50 dark:bg-gray-700 text-blue-600 dark:text-blue-400' : '' ?>">
                <i class="fas fa-project-diagram w-5 h-5 mr-3"></i>
                Projects
            </a>
            
            <!-- Categories -->
            <a href="categories.php" class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-blue-50 dark:hover:bg-gray-700 hover:text-blue-600 dark:hover:text-blue-400 transition-colors duration-200 <?= basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'bg-blue-50 dark:bg-gray-700 text-blue-600 dark:text-blue-400' : '' ?>">
                <i class="fas fa-folder-open w-5 h-5 mr-3"></i>
                Categories
            </a>
            
            <!-- Contact -->
            <a href="contact.php" class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-blue-50 dark:hover:bg-gray-700 hover:text-blue-600 dark:hover:text-blue-400 transition-colors duration-200 <?= basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'bg-blue-50 dark:bg-gray-700 text-blue-600 dark:text-blue-400' : '' ?>">
                <i class="fas fa-address-book w-5 h-5 mr-3"></i>
                Contact
            </a>
            
            <!-- Center Image -->
            <a href="center_image.php" class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-blue-50 dark:hover:bg-gray-700 hover:text-blue-600 dark:hover:text-blue-400 transition-colors duration-200 <?= basename($_SERVER['PHP_SELF']) == 'center_image.php' ? 'bg-blue-50 dark:bg-gray-700 text-blue-600 dark:text-blue-400' : '' ?>">
                <i class="fas fa-image w-5 h-5 mr-3"></i>
                Center Image
            </a>
            
            <!-- Child Project -->
            <a href="child_project.php" class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-blue-50 dark:hover:bg-gray-700 hover:text-blue-600 dark:hover:text-blue-400 transition-colors duration-200 <?= basename($_SERVER['PHP_SELF']) == 'child_project.php' ? 'bg-blue-50 dark:bg-gray-700 text-blue-600 dark:text-blue-400' : '' ?>">
                <i class="fas fa-project-diagram w-5 h-5 mr-3"></i>
                Child Project
            </a>
            
            <!-- Profile -->
            <a href="profile.php" class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-blue-50 dark:hover:bg-gray-700 hover:text-blue-600 dark:hover:text-blue-400 transition-colors duration-200 <?= basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'bg-blue-50 dark:bg-gray-700 text-blue-600 dark:text-blue-400' : '' ?>">
                <i class="fas fa-user w-5 h-5 mr-3"></i>
                Profile
            </a>
        </div>
        
        <!-- Divider -->
        <div class="border-t border-gray-200 dark:border-gray-600 mt-8 pt-4">
            <div class="px-4 space-y-2">
                <!-- Logout -->
                <a href="logout.php" class="flex items-center px-4 py-2 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors duration-200">
                    <i class="fas fa-sign-out-alt w-5 h-5 mr-3"></i>
                    Logout
                </a>
            </div>
        </div>
    </nav>
</div>

<!-- Sidebar Overlay for mobile -->
<div id="sidebar-overlay" class="fixed inset-0 z-40 bg-black bg-opacity-50 lg:hidden hidden"></div>
