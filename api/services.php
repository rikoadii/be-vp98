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
        // Get specific service by ID
        $query = "SELECT id, title, subtitle, description FROM services WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        
        if (!$stmt) {
            throw new Exception('Database prepare failed: ' . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $service = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        if (!$service) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Service not found',
                'error_code' => 'NOT_FOUND'
            ]);
            exit();
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Service retrieved successfully',
            'data' => $service
        ]);
        
    } else {
        // Get total count
        $count_query = "SELECT COUNT(*) as total FROM services";
        $count_result = mysqli_query($conn, $count_query);
        if (!$count_result) {
            throw new Exception('Database query failed: ' . mysqli_error($conn));
        }
        $total_records = mysqli_fetch_assoc($count_result)['total'];
        
        // Get all services
        $main_query = "SELECT id, title, subtitle, description FROM services ORDER BY id DESC";
        $result = mysqli_query($conn, $main_query);
        if (!$result) {
            throw new Exception('Database query failed: ' . mysqli_error($conn));
        }
        
        $services = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $services[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Services retrieved successfully',
            'data' => $services,
            'total_records' => $total_records
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
