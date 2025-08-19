<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

include '../db.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            // Get single project
            $id = intval($_GET['id']);
            $query = "SELECT p.*, c.categories_name 
                     FROM projects p 
                     LEFT JOIN categories c ON p.id_categories = c.id_categories 
                     WHERE p.project_id = $id";
            $result = mysqli_query($conn, $query);
            
            if ($project = mysqli_fetch_assoc($result)) {
                echo json_encode([
                    'success' => true,
                    'data' => $project
                ]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Project not found']);
            }
        } else {
            // Get all projects
            $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
            $category_filter = isset($_GET['category']) ? intval($_GET['category']) : '';
            
            $where_conditions = [];
            if (!empty($search)) {
                $where_conditions[] = "(p.name_projects LIKE '%$search%' OR p.description_projects LIKE '%$search%')";
            }
            if (!empty($category_filter)) {
                $where_conditions[] = "p.id_categories = $category_filter";
            }
            
            $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
            
            // Get projects
            $query = "SELECT p.*, c.categories_name 
                     FROM projects p 
                     LEFT JOIN categories c ON p.id_categories = c.id_categories 
                     $where_clause
                     ORDER BY p.project_id DESC";
            $result = mysqli_query($conn, $query);
            
            $projects = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $projects[] = $row;
            }
            
            echo json_encode([
                'success' => true,
                'data' => $projects
            ]);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?>

