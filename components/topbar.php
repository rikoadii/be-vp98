<!-- Main Content -->
<div class="flex-1 flex flex-col lg:ml-0">
    <!-- Top Navigation -->
    <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700 transition-colors duration-200">
        <div class="flex items-center justify-between px-6 py-4">
            <!-- Mobile menu button -->
            <button id="mobile-menu-button" class="lg:hidden p-2 rounded-md text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                <i class="fas fa-bars text-xl"></i>
            </button>
            
            <!-- Page Title -->
            <div class="flex-1 lg:flex-none">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white transition-colors duration-200"><?= isset($page_title) ? $page_title : 'Dashboard' ?></h1>
            </div>
            
            <!-- User Menu -->
            <div class="flex items-center space-x-4">
                <!-- Theme Toggle Button -->
                <button id="theme-toggle" class="p-2 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors duration-200" title="Toggle theme">
                    <i id="theme-toggle-dark-icon" class="fas fa-moon hidden"></i>
                    <i id="theme-toggle-light-icon" class="fas fa-sun"></i>
                </button>
                
                <!-- User Profile -->
                <div class="relative">
                    <button id="user-menu-button" class="flex items-center space-x-2 p-2 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors duration-200">
                        <?php if (isset($_SESSION['profile']) && $_SESSION['profile']): ?>
                            <img src="uploads/profiles/<?= htmlspecialchars($_SESSION['profile']) ?>" 
                                 alt="Profile" 
                                 class="w-8 h-8 rounded-full object-cover border border-gray-200 dark:border-gray-600">
                        <?php else: ?>
                            <div class="w-8 h-8 bg-blue-600 dark:bg-blue-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-white text-sm"></i>
                            </div>
                        <?php endif; ?>
                        <span class="hidden md:block text-sm font-medium">
                            <?= isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin' ?>
                        </span>
                        <i class="fas fa-chevron-down text-xs"></i>
                    </button>
                    
                    <!-- Dropdown Menu -->
                    <div id="user-menu" class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 hidden z-50 transition-colors duration-200">
                        <div class="py-2">
                            <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                                <i class="fas fa-user-circle mr-2"></i>Profile
                            </a>
                            <a href="settings.php" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                                <i class="fas fa-cog mr-2"></i>Settings
                            </a>
                            <div class="border-t border-gray-200 dark:border-gray-600 my-1"></div>
                            <a href="logout.php" class="block px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors duration-200">
                                <i class="fas fa-sign-out-alt mr-2"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Page Content -->
    <main class="flex-1 p-6">
