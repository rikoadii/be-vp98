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
        // Get specific contact by ID
        $query = "SELECT id, contact FROM contact WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        
        if (!$stmt) {
            throw new Exception('Database prepare failed: ' . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $contact = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        if (!$contact) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Contact not found',
                'error_code' => 'NOT_FOUND'
            ]);
            exit();
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Contact retrieved successfully',
            'data' => $contact
        ]);
        
    } else {
        // Get the single contact (only one should exist)
        $query = "SELECT id, contact FROM contact ORDER BY id DESC LIMIT 1";
        $result = mysqli_query($conn, $query);
        
        if (!$result) {
            throw new Exception('Database query failed: ' . mysqli_error($conn));
        }
        
        $contact = mysqli_fetch_assoc($result);
        
        if (!$contact) {
            echo json_encode([
                'success' => true,
                'message' => 'No contact found',
                'data' => null
            ]);
            exit();
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Contact retrieved successfully',
            'data' => $contact
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
