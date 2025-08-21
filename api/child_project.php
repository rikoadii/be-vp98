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
        // Get specific child project by ID
        $query = "SELECT cp.id, cp.id_parent_project, cp.image, p.name_projects as parent_title, p.description_projects as parent_description 
                  FROM child_project cp 
                  LEFT JOIN projects p ON cp.id_parent_project = p.project_id 
                  WHERE cp.id = ?";
        $stmt = mysqli_prepare($conn, $query);
        
        if (!$stmt) {
            throw new Exception('Database prepare failed: ' . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $child_project = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        if (!$child_project) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Child project not found',
                'error_code' => 'NOT_FOUND'
            ]);
            exit();
        }
        
        // Add full image URL
        $child_project['image_url'] = 'uploads/child_projects/' . $child_project['image'];
        $child_project['full_image_url'] = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . 
                                          '://' . $_SERVER['HTTP_HOST'] . 
                                          dirname($_SERVER['REQUEST_URI']) . '/../uploads/child_projects/' . $child_project['image'];
        
        echo json_encode([
            'success' => true,
            'message' => 'Child project retrieved successfully',
            'data' => $child_project
        ]);
        
    } else {
        // Get all child projects with filtering
        $parent_filter = isset($_GET['parent_project']) ? (int)$_GET['parent_project'] : null;
        $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
        
        // Build query based on filters
        $where_conditions = [];
        $params = [];
        $param_types = '';
        
        if ($parent_filter) {
            $where_conditions[] = "cp.id_parent_project = ?";
            $params[] = $parent_filter;
            $param_types .= 'i';
        }
        
        if ($search) {
            $search_term = "%$search%";
            $where_conditions[] = "(p.name_projects LIKE ? OR p.description_projects LIKE ?)";
            $params[] = $search_term;
            $params[] = $search_term;
            $param_types .= 'ss';
        }
        
        $where_clause = '';
        if (!empty($where_conditions)) {
            $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
        }
        
        // Get total count
        $count_query = "SELECT COUNT(*) as total FROM child_project cp 
                        LEFT JOIN projects p ON cp.id_parent_project = p.project_id 
                        $where_clause";
        
        if (!empty($params)) {
            $count_stmt = mysqli_prepare($conn, $count_query);
            if (!$count_stmt) {
                throw new Exception('Database prepare failed: ' . mysqli_error($conn));
            }
            mysqli_stmt_bind_param($count_stmt, $param_types, ...$params);
            mysqli_stmt_execute($count_stmt);
            $count_result = mysqli_stmt_get_result($count_stmt);
            $total_records = mysqli_fetch_assoc($count_result)['total'];
            mysqli_stmt_close($count_stmt);
        } else {
            $count_result = mysqli_query($conn, $count_query);
            if (!$count_result) {
                throw new Exception('Database query failed: ' . mysqli_error($conn));
            }
            $total_records = mysqli_fetch_assoc($count_result)['total'];
        }
        
        // Get all child projects
        $main_query = "SELECT cp.id, cp.id_parent_project, cp.image, p.name_projects as parent_title, p.description_projects as parent_description 
                       FROM child_project cp 
                       LEFT JOIN projects p ON cp.id_parent_project = p.project_id 
                       $where_clause
                       ORDER BY cp.id DESC";
        
        if (!empty($params)) {
            $stmt = mysqli_prepare($conn, $main_query);
            if (!$stmt) {
                throw new Exception('Database prepare failed: ' . mysqli_error($conn));
            }
            mysqli_stmt_bind_param($stmt, $param_types, ...$params);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
        } else {
            $result = mysqli_query($conn, $main_query);
            if (!$result) {
                throw new Exception('Database query failed: ' . mysqli_error($conn));
            }
        }
        
        $child_projects = [];
        while ($row = mysqli_fetch_assoc($result)) {
            // Add full image URLs
            $row['image_url'] = 'uploads/child_projects/' . $row['image'];
            $row['full_image_url'] = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . 
                                    '://' . $_SERVER['HTTP_HOST'] . 
                                    dirname($_SERVER['REQUEST_URI']) . '/../uploads/child_projects/' . $row['image'];
            $child_projects[] = $row;
        }
        
        if (!empty($params)) {
            mysqli_stmt_close($stmt);
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Child projects retrieved successfully',
            'data' => $child_projects,
            'total_records' => $total_records,
            'filters' => [
                'parent_project' => $parent_filter,
                'search' => $search
            ]
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
