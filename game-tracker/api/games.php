<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        handleGet();
        break;
    case 'POST':
        handlePost();
        break;
    case 'PUT':
        handlePut();
        break;
    case 'DELETE':
        handleDelete();
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}

function handleGet() {
    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'list':
                $section = $_GET['section'] ?? 'played';
                $sortBy = $_GET['sort'] ?? 'title';
                $sortOrder = $_GET['order'] ?? 'ASC';
                
                $games = getGames($section, $sortBy, $sortOrder);
                echo json_encode(['success' => true, 'games' => $games]);
                break;
                
            case 'get':
                if (isset($_GET['id'])) {
                    $game = getGame($_GET['id']);
                    if ($game) {
                        echo json_encode(['success' => true, 'game' => $game]);
                    } else {
                        http_response_code(404);
                        echo json_encode(['error' => 'Game not found']);
                    }
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'ID required']);
                }
                break;
                
            case 'search_cover':
                if (isset($_GET['title'])) {
                    $coverUrl = searchGameCover($_GET['title']);
                    echo json_encode(['success' => true, 'cover_url' => $coverUrl]);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Title required']);
                }
                break;
                
            case 'find_missing_covers':
                requireAdmin(); // Only allow admins to trigger this
                $result = findAndUpdateMissingCovers();
                echo json_encode($result);
                break;
                
            default:
                http_response_code(400);
                echo json_encode(['error' => 'Invalid action']);
                break;
        }
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Action required']);
    }
}

function handlePost() {
    global $input;
    
    if (isset($input['action'])) {
        switch ($input['action']) {
            case 'add':
                requireAdmin(); // Require admin authentication
                if (isset($input['game'])) {
                    $result = addGame($input['game']);
                    if ($result) {
                        echo json_encode(['success' => true, 'message' => 'Game added successfully']);
                    } else {
                        http_response_code(500);
                        echo json_encode(['error' => 'Failed to add game']);
                    }
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Game data required']);
                }
                break;
                
            case 'move':
                requireAdmin(); // Require admin authentication
                if (isset($input['id']) && isset($input['section'])) {
                    $result = moveGameToSection($input['id'], $input['section']);
                    if ($result) {
                        echo json_encode(['success' => true, 'message' => 'Game moved successfully']);
                    } else {
                        http_response_code(500);
                        echo json_encode(['error' => 'Failed to move game']);
                    }
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'ID and section required']);
                }
                break;
                
            case 'search_cover':
                requireAdmin(); // Require admin authentication
                if (isset($input['id']) && isset($input['title'])) {
                    $coverUrl = searchGameCover($input['title']);
                    if ($coverUrl) {
                        // Update the game with the new cover URL
                        $game = getGame($input['id']);
                        if ($game) {
                            $game['cover_url'] = $coverUrl;
                            $result = updateGame($input['id'], $game);
                            if ($result) {
                                echo json_encode([
                                    'success' => true, 
                                    'message' => 'Cover found and updated',
                                    'cover_url' => $coverUrl
                                ]);
                            } else {
                                http_response_code(500);
                                echo json_encode(['error' => 'Failed to update game with new cover']);
                            }
                        } else {
                            http_response_code(404);
                            echo json_encode(['error' => 'Game not found']);
                        }
                    } else {
                        echo json_encode([
                            'success' => false, 
                            'message' => 'No cover found for this game'
                        ]);
                    }
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'ID and title required']);
                }
                break;
                
            default:
                http_response_code(400);
                echo json_encode(['error' => 'Invalid action']);
                break;
        }
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Action required']);
    }
}

function handlePut() {
    global $input;
    
    requireAdmin(); // Require admin authentication
    
    if (isset($input['action']) && $input['action'] === 'update') {
        if (isset($input['id']) && isset($input['game'])) {
            $result = updateGame($input['id'], $input['game']);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Game updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to update game']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'ID and game data required']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
    }
}

function handleDelete() {
    global $input;
    
    requireAdmin(); // Require admin authentication
    
    if (isset($input['id'])) {
        $result = deleteGame($input['id']);
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Game deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete game']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'ID required']);
    }
}
?>
