<?php
// Set content type to JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Include database connection
include '../db.php';

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Only GET requests are supported.',
        'error_code' => 'METHOD_NOT_ALLOWED'
    ]);
    exit();
}

try {
    // Check if specific ID is requested
    $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
    
    if ($id) {
        // Get specific center image by ID
        $query = "SELECT id, image FROM center_image WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        
        if (!$stmt) {
            throw new Exception('Database prepare failed: ' . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $center_image = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        if (!$center_image) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Center image not found',
                'error_code' => 'NOT_FOUND'
            ]);
            exit();
        }
        
        // Add full image URL
        $center_image['image_url'] = 'uploads/center_images/' . $center_image['image'];
        $center_image['full_image_url'] = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . 
                                         '://' . $_SERVER['HTTP_HOST'] . 
                                         dirname($_SERVER['REQUEST_URI']) . '/../uploads/center_images/' . $center_image['image'];
        
        echo json_encode([
            'success' => true,
            'message' => 'Center image retrieved successfully',
            'data' => $center_image
        ]);
        
    } else {
        // Get the single center image (only one should exist)
        $query = "SELECT id, image FROM center_image ORDER BY id DESC LIMIT 1";
        $result = mysqli_query($conn, $query);
        
        if (!$result) {
            throw new Exception('Database query failed: ' . mysqli_error($conn));
        }
        
        $center_image = mysqli_fetch_assoc($result);
        
        if (!$center_image) {
            echo json_encode([
                'success' => true,
                'message' => 'No center image found',
                'data' => null
            ]);
            exit();
        }
        
        // Add full image URLs
        $center_image['image_url'] = 'uploads/center_images/' . $center_image['image'];
        $center_image['full_image_url'] = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . 
                                        '://' . $_SERVER['HTTP_HOST'] . 
                                        dirname($_SERVER['REQUEST_URI']) . '/../uploads/center_images/' . $center_image['image'];
        
        echo json_encode([
            'success' => true,
            'message' => 'Center image retrieved successfully',
            'data' => $center_image
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error: ' . $e->getMessage(),
        'error_code' => 'INTERNAL_ERROR'
    ]);
} finally {
    // Close database connection
    if (isset($conn)) {
        mysqli_close($conn);
    }
}
?>
