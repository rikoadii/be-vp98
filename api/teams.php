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
            // Get single team member
            $id = intval($_GET['id']);
            $query = "SELECT * FROM teams WHERE id = $id";
            $result = mysqli_query($conn, $query);
            
            if ($team = mysqli_fetch_assoc($result)) {
                echo json_encode([
                    'success' => true,
                    'data' => $team
                ]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Team member not found']);
            }
        } else {
            // Get all team members
            $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
            $role_filter = isset($_GET['role']) ? mysqli_real_escape_string($conn, $_GET['role']) : '';
            
            $where_conditions = [];
            if (!empty($search)) {
                $where_conditions[] = "(name LIKE '%$search%' OR role LIKE '%$search%' OR expertise LIKE '%$search%')";
            }
            if (!empty($role_filter)) {
                $where_conditions[] = "role = '$role_filter'";
            }
            
            $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
            
            // Get teams
            $query = "SELECT * FROM teams $where_clause ORDER BY id ASC";
            $result = mysqli_query($conn, $query);
            
            $teams = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $teams[] = $row;
            }
            
            echo json_encode([
                'success' => true,
                'data' => $teams
            ]);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?>

