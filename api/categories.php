<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

include '../db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (isset($_GET['id'])) {
        // Get single category
        $id = intval($_GET['id']);
        $query = "SELECT * FROM categories WHERE id_categories = $id";
        $result = mysqli_query($conn, $query);
        
        if ($category = mysqli_fetch_assoc($result)) {
            echo json_encode([
                'success' => true,
                'data' => $category
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Category not found']);
        }
    } else {
        // Get all categories
        $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
        
        $where_clause = '';
        if (!empty($search)) {
            $where_clause = "WHERE categories_name LIKE '%$search%'";
        }
        
        // Get categories
        $query = "SELECT * FROM categories $where_clause ORDER BY id_categories DESC";
        $result = mysqli_query($conn, $query);
        
        $categories = [];
        while ($row = mysqli_fetch_assoc($result)) {
            // Count projects in this category
            $project_count_query = "SELECT COUNT(*) as project_count FROM projects WHERE id_categories = " . $row['id_categories'];
            $project_count_result = mysqli_query($conn, $project_count_query);
            $row['project_count'] = mysqli_fetch_assoc($project_count_result)['project_count'];
            
            $categories[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'data' => $categories
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>
