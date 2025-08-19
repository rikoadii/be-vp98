<?php
/**
 * Alert Component
 * 
 * @param string $type - success, error, warning, info
 * @param string $message - Alert message
 * @param bool $dismissible - Show close button
 */
function showAlert($type, $message, $dismissible = true) {
    $bgColor = '';
    $textColor = '';
    $icon = '';
    
    switch($type) {
        case 'success':
            $bgColor = 'bg-green-50 border-green-200';
            $textColor = 'text-green-800';
            $icon = 'fas fa-check-circle text-green-400';
            break;
        case 'error':
            $bgColor = 'bg-red-50 border-red-200';
            $textColor = 'text-red-800';
            $icon = 'fas fa-exclamation-circle text-red-400';
            break;
        case 'warning':
            $bgColor = 'bg-yellow-50 border-yellow-200';
            $textColor = 'text-yellow-800';
            $icon = 'fas fa-exclamation-triangle text-yellow-400';
            break;
        case 'info':
            $bgColor = 'bg-blue-50 border-blue-200';
            $textColor = 'text-blue-800';
            $icon = 'fas fa-info-circle text-blue-400';
            break;
    }
    
    echo '<div class="' . $bgColor . ' border rounded-lg p-4 mb-4 alert-auto-hide" role="alert">';
    echo '<div class="flex items-center">';
    echo '<i class="' . $icon . ' mr-3"></i>';
    echo '<div class="flex-1 ' . $textColor . '">' . htmlspecialchars($message) . '</div>';
    
    if ($dismissible) {
        echo '<button type="button" class="ml-4 ' . $textColor . ' hover:' . str_replace('text-', 'text-', $textColor) . ' opacity-75" onclick="this.parentElement.parentElement.remove()">';
        echo '<i class="fas fa-times"></i>';
        echo '</button>';
    }
    
    echo '</div>';
    echo '</div>';
}

/**
 * Card Component
 * 
 * @param string $title - Card title
 * @param string $content - Card content
 * @param string $class - Additional CSS classes
 */
function createCard($title = '', $content = '', $class = '') {
    echo '<div class="bg-white rounded-lg shadow-sm border border-gray-200 ' . $class . '">';
    
    if ($title) {
        echo '<div class="px-6 py-4 border-b border-gray-200">';
        echo '<h3 class="text-lg font-semibold text-gray-900">' . htmlspecialchars($title) . '</h3>';
        echo '</div>';
    }
    
    echo '<div class="p-6">';
    echo $content;
    echo '</div>';
    echo '</div>';
}

/**
 * Button Component
 * 
 * @param string $text - Button text
 * @param string $type - primary, secondary, success, danger, warning
 * @param string $size - sm, md, lg
 * @param string $onclick - JavaScript onclick event
 * @param string $href - Link URL
 * @param string $icon - Font Awesome icon class
 */
function createButton($text, $type = 'primary', $size = 'md', $onclick = '', $href = '', $icon = '') {
    $baseClass = 'inline-flex items-center justify-center font-medium rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2';
    
    // Size classes
    $sizeClass = '';
    switch($size) {
        case 'sm':
            $sizeClass = 'px-3 py-1.5 text-sm';
            break;
        case 'lg':
            $sizeClass = 'px-6 py-3 text-base';
            break;
        default:
            $sizeClass = 'px-4 py-2 text-sm';
    }
    
    // Type classes
    $typeClass = '';
    switch($type) {
        case 'secondary':
            $typeClass = 'bg-gray-100 text-gray-900 hover:bg-gray-200 focus:ring-gray-500';
            break;
        case 'success':
            $typeClass = 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500';
            break;
        case 'danger':
            $typeClass = 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500';
            break;
        case 'warning':
            $typeClass = 'bg-yellow-600 text-white hover:bg-yellow-700 focus:ring-yellow-500';
            break;
        default:
            $typeClass = 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500';
    }
    
    $iconHtml = $icon ? '<i class="' . $icon . ' mr-2"></i>' : '';
    $onclickAttr = $onclick ? 'onclick="' . htmlspecialchars($onclick) . '"' : '';
    
    if ($href) {
        echo '<a href="' . htmlspecialchars($href) . '" class="' . $baseClass . ' ' . $sizeClass . ' ' . $typeClass . '" ' . $onclickAttr . '>';
        echo $iconHtml . htmlspecialchars($text);
        echo '</a>';
    } else {
        echo '<button type="button" class="' . $baseClass . ' ' . $sizeClass . ' ' . $typeClass . '" ' . $onclickAttr . '>';
        echo $iconHtml . htmlspecialchars($text);
        echo '</button>';
    }
}

/**
 * Input Field Component
 * 
 * @param string $name - Input name
 * @param string $label - Input label
 * @param string $type - Input type
 * @param string $value - Input value
 * @param bool $required - Required field
 * @param string $placeholder - Placeholder text
 * @param array $options - Options for select dropdown
 */
function createInput($name, $label, $type = 'text', $value = '', $required = false, $placeholder = '', $options = []) {
    $requiredAttr = $required ? 'required' : '';
    $placeholderAttr = $placeholder ? 'placeholder="' . htmlspecialchars($placeholder) . '"' : '';
    
    echo '<div class="mb-4">';
    echo '<label for="' . htmlspecialchars($name) . '" class="block text-sm font-medium text-gray-700 mb-2">';
    echo htmlspecialchars($label);
    if ($required) echo ' <span class="text-red-500">*</span>';
    echo '</label>';
    
    if ($type === 'textarea') {
        echo '<textarea id="' . htmlspecialchars($name) . '" name="' . htmlspecialchars($name) . '" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200" ' . $requiredAttr . ' ' . $placeholderAttr . '>';
        echo htmlspecialchars($value);
        echo '</textarea>';
    } elseif ($type === 'file') {
        echo '<input type="file" id="' . htmlspecialchars($name) . '" name="' . htmlspecialchars($name) . '" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200" ' . $requiredAttr . ' accept="image/jpeg,image/jpg,image/png,image/webp" onchange="previewImage(this, \'' . htmlspecialchars($name) . '_preview\')">';
        echo '<div id="' . htmlspecialchars($name) . '_preview" class="mt-3">';
        echo '<p class="text-xs text-gray-500">Supported formats: JPG, JPEG, PNG, WEBP</p>';
        echo '</div>';
    } elseif ($type === 'select') {
        echo '<select id="' . htmlspecialchars($name) . '" name="' . htmlspecialchars($name) . '" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200" ' . $requiredAttr . '>';
        echo '<option value="">' . ($placeholder ? htmlspecialchars($placeholder) : 'Select an option') . '</option>';
        foreach ($options as $optionValue => $optionLabel) {
            $selected = ($value == $optionValue) ? 'selected' : '';
            echo '<option value="' . htmlspecialchars($optionValue) . '" ' . $selected . '>' . htmlspecialchars($optionLabel) . '</option>';
        }
        echo '</select>';
    } elseif ($type === 'checkbox') {
        echo '<div class="flex items-center">';
        echo '<input type="checkbox" id="' . htmlspecialchars($name) . '" name="' . htmlspecialchars($name) . '" value="1" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2" ' . ($value ? 'checked' : '') . '>';
        echo '<label for="' . htmlspecialchars($name) . '" class="ml-2 text-sm text-gray-700">' . htmlspecialchars($placeholder) . '</label>';
        echo '</div>';
    } else {
        echo '<input type="' . htmlspecialchars($type) . '" id="' . htmlspecialchars($name) . '" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars($value) . '" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200" ' . $requiredAttr . ' ' . $placeholderAttr . '>';
    }
    
    echo '</div>';
}

/**
 * Modal Component
 * 
 * @param string $id - Modal ID
 * @param string $title - Modal title
 * @param string $content - Modal content
 * @param string $size - sm, md, lg, xl
 */
function createModal($id, $title, $content, $size = 'md') {
    $sizeClass = '';
    switch($size) {
        case 'sm':
            $sizeClass = 'max-w-md';
            break;
        case 'lg':
            $sizeClass = 'max-w-4xl';
            break;
        case 'xl':
            $sizeClass = 'max-w-6xl';
            break;
        default:
            $sizeClass = 'max-w-2xl';
    }
    
    echo '<div id="' . htmlspecialchars($id) . '" class="fixed inset-0 z-50 hidden overflow-y-auto">';
    echo '<div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">';
    echo '<div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="document.getElementById(\'' . htmlspecialchars($id) . '\').classList.add(\'hidden\')"></div>';
    echo '<div class="inline-block w-full ' . $sizeClass . ' my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-lg">';
    
    // Header
    echo '<div class="px-6 py-4 border-b border-gray-200">';
    echo '<div class="flex items-center justify-between">';
    echo '<h3 class="text-lg font-semibold text-gray-900">' . htmlspecialchars($title) . '</h3>';
    echo '<button type="button" class="text-gray-400 hover:text-gray-600" onclick="document.getElementById(\'' . htmlspecialchars($id) . '\').classList.add(\'hidden\')">';
    echo '<i class="fas fa-times"></i>';
    echo '</button>';
    echo '</div>';
    echo '</div>';
    
    // Content
    echo '<div class="px-6 py-4">';
    echo $content;
    echo '</div>';
    
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
?>
