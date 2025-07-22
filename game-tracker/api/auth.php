<?php
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Get action from GET, POST, or JSON body
$input = json_decode(file_get_contents('php://input'), true);
$action = $_GET['action'] ?? $_POST['action'] ?? ($input['action'] ?? '');

switch ($action) {
    case 'login':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }
        
        $username = $input['username'] ?? '';
        $password = $input['password'] ?? '';
        
        if (login($username, $password)) {
            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'user' => getCurrentUser()
            ]);
        } else {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'error' => 'Invalid username or password'
            ]);
        }
        break;
        
    case 'logout':
        logout();
        echo json_encode([
            'success' => true,
            'message' => 'Logout successful'
        ]);
        break;
        
    case 'status':
        echo json_encode([
            'success' => true,
            'user' => getCurrentUser()
        ]);
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        break;
}
?>
