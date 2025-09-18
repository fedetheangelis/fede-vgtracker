<?php
// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Disable display_errors in production

// Set CORS headers first
$http_origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowed_origins = [
    'https://fefvgtracker.infinityfree.me' => true,
    'http://localhost' => true,
    'http://127.0.0.1' => true,
    'https://fefvgtracker.infinityfree.me' => true,
    'http://fefvgtracker.infinityfree.me' => true
];

// Allow the current origin if it's in the allowed list
$origin = isset($allowed_origins[$http_origin]) ? $http_origin : '';

// For development, you might want to allow any origin - remove in production
if (empty($origin) && (strpos($_SERVER['HTTP_ORIGIN'] ?? '', 'infinityfree.net') !== false || 
                       strpos($_SERVER['HTTP_ORIGIN'] ?? '', 'infinityfree.app') !== false)) {
    $origin = $_SERVER['HTTP_ORIGIN'];
}

// Always set CORS headers
header("Access-Control-Allow-Origin: $http_origin");
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Auth-Token, X-HTTP-Method-Override');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Max-Age: 86400'); // Cache preflight for 24 hours
header('Content-Type: application/json; charset=utf-8');

// Add security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: same-origin');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Set CORS headers for preflight
    header("Access-Control-Allow-Origin: $http_origin");
    header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Auth-Token, X-HTTP-Method-Override');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');
    
    // Handle preflight for credentials
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
        header('Access-Control-Allow-Headers: ' . $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']);
    }
    
    http_response_code(200);
    exit(0);
}

/**
 * Send JSON response with proper headers and exit
 * 
 * @param mixed $data The data to encode as JSON
 * @param int $statusCode HTTP status code (default: 200)
 * @param array $headers Additional headers to send
 * @return void
 */
function sendResponse($data, $statusCode = 200, $headers = []) {
    // Set status code
    http_response_code($statusCode);
    
    // Set default headers
    $defaultHeaders = [
        'Content-Type' => 'application/json; charset=utf-8',
        'Access-Control-Allow-Credentials' => 'true',
        'Cache-Control' => 'no-cache, no-store, must-revalidate',
        'Pragma' => 'no-cache',
        'Expires' => '0'
    ];
    
    // Merge with provided headers
    $responseHeaders = array_merge($defaultHeaders, $headers);
    
    // Set all headers
    foreach ($responseHeaders as $name => $value) {
        header("$name: $value");
    }
    
    // Clean and sanitize data before JSON encoding
    if (is_array($data) || is_object($data)) {
        array_walk_recursive($data, function(&$value) {
            if (is_string($value)) {
                // Remove any invalid UTF-8 characters
                $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
            }
        });
    }
    
    // Prepare response data
    $response = [
        'status' => $statusCode >= 200 && $statusCode < 300 ? 'success' : 'error',
        'data' => $data,
        'timestamp' => date('c')
    ];
    
    // Send response with proper JSON encoding
    echo json_encode($response, 
        JSON_UNESCAPED_UNICODE | 
        JSON_UNESCAPED_SLASHES |
        JSON_INVALID_UTF8_SUBSTITUTE |
        JSON_PARTIAL_OUTPUT_ON_ERROR |
        JSON_PRETTY_PRINT
    );
    
    exit(0);
}

// Include required files
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Get input data based on request method
$input = [];
if ($method === 'GET') {
    $input = $_GET;
} else {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
}

// Simple router
$action = $input['action'] ?? '';
$response = [];
$statusCode = 200;

try {
    // Connect to database with UTF-8 encoding
    $pdo = getConnection();
    // Set character set to UTF-8
    $pdo->exec("SET NAMES 'utf8mb4'");
    $pdo->exec("SET CHARACTER SET utf8mb4");
    $pdo->exec("SET SESSION collation_connection = 'utf8mb4_unicode_ci'");
    $pdo->exec("USE " . DB_NAME);
    
    switch ($method) {
        case 'GET':
            switch ($action) {
                case 'list':
                    $section = $input['section'] ?? 'played';
                    $sortBy = $input['sort'] ?? 'title';
                    $sortOrder = strtoupper($input['order'] ?? 'ASC');
                    
                    // Validate sort order
                    $sortOrder = in_array($sortOrder, ['ASC', 'DESC']) ? $sortOrder : 'ASC';
                    
                    // Get games with error handling
                    $games = getGamesFromDB($pdo, $section, $sortBy, $sortOrder);
                    $response = [
                        'status' => 'success',
                        'count' => count($games),
                        'games' => $games
                    ];
                    break;
                    
                case 'get':
                    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
                    if (!$id) {
                        throw new Exception('Invalid game ID', 400);
                    }
                    
                    try {
                        $pdo = getGameTrackerConnection();
                        $game = getGameFromDB($pdo, $id);
                        if ($game) {
                            sendResponse(['status' => 'success', 'data' => $game]);
                        } else {
                            throw new Exception('Game not found', 404);
                        }
                    } catch (Exception $e) {
                        throw new Exception('Failed to fetch game: ' . $e->getMessage(), 500);
                    }
                    break;
                    
                default:
                    throw new Exception('Invalid action', 400);
            }
            break;
            
        case 'POST':
            // Get input data
            $input = $_SERVER['CONTENT_TYPE'] === 'application/json' 
                ? json_decode(file_get_contents('php://input'), true) 
                : array_merge($_POST, $_GET);
            
            // Handle delete action first (from any method)
            if ((isset($input['action']) && $input['action'] === 'delete') || 
                (isset($_GET['action']) && $_GET['action'] === 'delete')) {
                
                $gameId = $input['id'] ?? ($_GET['id'] ?? null);
                
                if (empty($gameId)) {
                    throw new Exception('Game ID is required for delete operation', 400);
                }
                
                try {
                    $pdo = getGameTrackerConnection();
                    $pdo->beginTransaction();
                    
                    // First get the game to check if it exists
                    $stmt = $pdo->prepare("SELECT id, title FROM games WHERE id = :id");
                    $stmt->execute([':id' => $gameId]);
                    $game = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$game) {
                        throw new Exception('Game not found', 404);
                    }
                    
                    // Now delete the game
                    $stmt = $pdo->prepare("DELETE FROM games WHERE id = :id");
                    $stmt->execute([':id' => $gameId]);
                    
                    $pdo->commit();
                    
                    // Redirect back to the previous page after successful deletion
                    if (isset($_SERVER['HTTP_REFERER'])) {
                        header('Location: ' . $_SERVER['HTTP_REFERER']);
                        exit();
                    } else {
                        // Fallback to home if no referrer
                        header('Location: /');
                        exit();
                    }
                    
                } catch (Exception $e) {
                    if (isset($pdo)) {
                        $pdo->rollBack();
                    }
                    throw new Exception('Failed to delete game: ' . $e->getMessage(), $e->getCode() ?: 500);
                }
            }
            
            // Handle new game creation or update
            if (!isset($input['id']) || !$input['id']) {
                // This is a new game
                try {
                    $pdo = getGameTrackerConnection();
                    $pdo->beginTransaction();
                    
                    // Handle platforms array for new game
                    if (isset($input['platforms']) && is_array($input['platforms'])) {
                        $input['platform'] = implode(', ', $input['platforms']);
                        unset($input['platforms']);
                    } elseif (isset($input['platforms'])) {
                        $input['platform'] = $input['platforms'];
                        unset($input['platforms']);
                    }
                    
                    // Add the new game
                    $id = addGameToDB($pdo, $input);
                    $pdo->commit();
                    
                    sendResponse([
                        'status' => 'success',
                        'message' => 'Game created successfully',
                        'id' => $id
                    ]);
                    
                } catch (Exception $e) {
                    if (isset($pdo)) {
                        $pdo->rollBack();
                    }
                    throw new Exception('Failed to create game: ' . $e->getMessage(), 500);
                }
            } 
            // Handle update action for existing games
            else {
                // This is an update
                try {
                    $pdo = getGameTrackerConnection();
                    $pdo->beginTransaction();
                    
                    // Remove action and id from the data to update
                    unset($input['action']);
                    $id = $input['id'];
                    unset($input['id']);
                    
                    // Handle platforms array
                    if (isset($input['platforms']) && is_array($input['platforms'])) {
                        $input['platform'] = implode(', ', $input['platforms']);
                        unset($input['platforms']);
                    } elseif (isset($input['platforms'])) {
                        $input['platform'] = $input['platforms'];
                        unset($input['platforms']);
                    }
                    
                    // Prepare update fields and handle empty values
                    $updateFields = [];
                    foreach ($input as $key => $value) {
                        // Convert empty strings to NULL for all fields
                        if ($value === '') {
                            $input[$key] = null;
                        }
                        // Only include non-null values in the update
                        if ($value !== null || $key === 'title' || $key === 'platform') {  // Always include title and platform, even if empty
                            $updateFields[] = "$key = :$key";
                        } else {
                            unset($input[$key]);
                        }
                    }
                    
                    if (empty($updateFields)) {
                        throw new Exception('No fields to update', 400);
                    }
                    
                    $sql = "UPDATE games SET " . implode(', ', $updateFields) . " WHERE id = :id";
                    $stmt = $pdo->prepare($sql);
                    
                    // Bind parameters
                    foreach ($input as $key => $value) {
                        $stmt->bindValue(":$key", $value);
                    }
                    $stmt->bindValue(":id", $id);
                    
                    $stmt->execute();
                    
                    if ($stmt->rowCount() === 0) {
                        // No rows affected, check if game exists
                        $checkStmt = $pdo->prepare("SELECT id FROM games WHERE id = :id");
                        $checkStmt->execute([':id' => $id]);
                        if ($checkStmt->rowCount() === 0) {
                            throw new Exception('Game not found', 404);
                        }
                    }
                    
                    $pdo->commit();
                    
                    sendResponse([
                        'status' => 'success',
                        'message' => 'Game updated successfully',
                        'id' => $id
                    ]);
                    
                } catch (Exception $e) {
                    if (isset($pdo)) {
                        $pdo->rollBack();
                    }
                    throw new Exception('Failed to update game: ' . $e->getMessage(), 500);
                }
            }
            // Handle delete action
            if (isset($input['action']) && $input['action'] === 'delete' && !empty($input['id'])) {
                try {
                    $pdo = getGameTrackerConnection();
                    $pdo->beginTransaction();
                    
                    // First get the game to check if it exists
                    $stmt = $pdo->prepare("SELECT id, title FROM games WHERE id = :id");
                    $stmt->execute([':id' => $input['id']]);
                    $game = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$game) {
                        throw new Exception('Game not found', 404);
                    }
                    
                    // Now delete the game
                    $stmt = $pdo->prepare("DELETE FROM games WHERE id = :id");
                    $stmt->execute([':id' => $input['id']]);
                    
                    $pdo->commit();
                    
                    sendResponse([
                        'status' => 'success',
                        'message' => 'Game deleted successfully',
                        'title' => $game['title'] // Include title in response
                    ]);
                } catch (Exception $e) {
                    if (isset($pdo)) {
                        $pdo->rollBack();
                    }
                    throw new Exception('Failed to delete game: ' . $e->getMessage(), $e->getCode() ?: 500);
                }
            }
            // Handle priority update
            else if (isset($input['action']) && $input['action'] === 'update_priority') {
                if (empty($input['id']) || !isset($input['priority'])) {
                    throw new Exception('ID e priorità sono obbligatori', 400);
                }
                
                $id = filter_var($input['id'], FILTER_VALIDATE_INT);
                $priority = filter_var($input['priority'], FILTER_VALIDATE_INT, [
                    'options' => ['min_range' => 0, 'max_range' => 1000]
                ]);
                
                if ($id === false || $priority === false) {
                    throw new Exception('ID o priorità non validi', 400);
                }
                
                $query = "UPDATE games SET priority = :priority WHERE id = :id";
                $stmt = $pdo->prepare($query);
                $success = $stmt->execute([
                    ':id' => $id,
                    ':priority' => $priority
                ]);
                
                if ($success && $stmt->rowCount() > 0) {
                    $pdo->commit();
                    sendResponse(['status' => 'success', 'message' => 'Priorità aggiornata con successo']);
                } else {
                    throw new Exception('Nessun gioco trovato con questo ID', 404);
                }
            }
            // Handle cover search
            else if (isset($input['action']) && $input['action'] === 'search_cover') {
                if (empty($input['title'])) {
                    throw new Exception('Il titolo è obbligatorio per la ricerca della cover', 400);
                }
                
                $title = trim($input['title']);
                $apiKey = 'b06700683ebe479fa895f1db55b1abb8';
                
                // Prima controlla se esiste già una cover per questo gioco nel database
                try {
                    $pdo = getGameTrackerConnection();
                    $stmt = $pdo->prepare("SELECT cover_url FROM games WHERE title = :title AND cover_url IS NOT NULL LIMIT 1");
                    $stmt->execute([':title' => $title]);
                    $existingCover = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (!empty($existingCover['cover_url'])) {
                        error_log("Trovata cover esistente nel database per: " . $title);
                        sendResponse([
                            'status' => 'success',
                            'data' => [
                                'cover_url' => $existingCover['cover_url'],
                                'game_title' => $title,
                                'source' => 'database'
                            ]
                        ]);
                        return;
                    }
                } catch (Exception $e) {
                    error_log("Errore nel controllo della cover esistente: " . $e->getMessage());
                }
                
                // Se non esiste una cover, cerca su RAWG
                $searchUrl = "https://api.rawg.io/api/games?key={$apiKey}&search=" . urlencode($title) . "&page_size=1";
                
                // Log per debug
                error_log("Titolo cercato: " . $title);
                error_log("URL della richiesta RAWG: " . $searchUrl);
                
                // Inizializza cURL per RAWG
                $ch = curl_init();
                curl_setopt_array($ch, [
                    CURLOPT_URL => $searchUrl,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_SSL_VERIFYPEER => true,
                    CURLOPT_TIMEOUT => 15,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_MAXREDIRS => 5,
                    CURLOPT_HTTPHEADER => [
                        'User-Agent: GameTracker/1.0 (fede.angelis.2003@gmail.com)',
                        'Accept: application/json'
                    ]
                ]);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $error = curl_error($ch);
                curl_close($ch);
                
                // Log della risposta
                error_log("Codice di stato HTTP: " . $httpCode);
                error_log("Risposta API: " . substr($response, 0, 500)); // Primi 500 caratteri della risposta
                
                if ($httpCode !== 200) {
                    error_log("Errore nella richiesta all'API RAWG: $error (HTTP $httpCode)");
                    
                    // Se c'è un errore 404, prova con un titolo più generico
                    if ($httpCode === 404) {
                        $searchTerms = [
                            $title,
                            preg_replace('/\s*\([^)]*\)$/', '', $title), // Rimuove tutto tra parentesi alla fine
                            preg_replace('/\s*\-.*$/', '', $title), // Rimuove tutto dopo un trattino
                            preg_replace('/\s*\:.*$/', '', $title)  // Rimuove tutto dopo i due punti
                        ];
                        
                        $searchTerms = array_unique($searchTerms);
                        
                        foreach ($searchTerms as $term) {
                            $term = trim($term);
                            if (empty($term) || $term === $title) continue;
                            
                            error_log("Tentativo con termine di ricerca alternativo: " . $term);
                            
                            $altUrl = "https://api.rawg.io/api/games?key={$apiKey}&search=" . urlencode($term) . "&page_size=1";
                            curl_setopt($ch, CURLOPT_URL, $altUrl);
                            
                            $altResponse = curl_exec($ch);
                            $altHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                            
                            if ($altHttpCode === 200) {
                                $response = $altResponse;
                                $httpCode = $altHttpCode;
                                $title = $term; // Aggiorna il titolo con quello usato per la ricerca
                                error_log("Trovati risultati con il termine alternativo: " . $term);
                                break;
                            }
                        }
                        
                        if ($httpCode !== 200) {
                            throw new Exception('Nessun risultato trovato per: ' . $title, 404);
                        }
                    } else {
                        throw new Exception('Errore nella richiesta all\'API RAWG: ' . $error . ' (HTTP ' . $httpCode . ')', $httpCode);
                    }
                }
                
                $data = json_decode($response, true);
                
                // Debug: Log della struttura completa della risposta
                error_log("Struttura risposta API: " . print_r($data, true));
                
                // Se la risposta ha status success, verifichiamo se contiene già l'URL della cover
                if (isset($data['status']) && $data['status'] === 'success' && !empty($data['data'])) {
                    error_log("Trovata risposta con status success");
                    
                    // Se c'è già un URL della cover nella risposta, usalo
                    if (!empty($data['data']['cover_url'])) {
                        error_log("Trovato URL cover nella risposta: " . $data['data']['cover_url']);
                        sendResponse([
                            'status' => 'success',
                            'data' => [
                                'cover_url' => $data['data']['cover_url'],
                                'game_title' => $title
                            ]
                        ]);
                        return;
                    }
                    
                    // Se non c'è la cover ma ci sono altri dati, potrebbero essere i risultati
                    if (isset($data['data']['results']) && is_array($data['data']['results']) && !empty($data['data']['results'][0])) {
                        $game = $data['data']['results'][0];
                        $coverUrl = $game['background_image'] ?? $game['image_background'] ?? null;
                        
                        if ($coverUrl) {
                            $coverUrl = strtok($coverUrl, '?');
                            sendResponse([
                                'status' => 'success',
                                'data' => [
                                    'cover_url' => $coverUrl,
                                    'game_title' => $game['name'] ?? $title
                                ]
                            ]);
                            return;
                        }
                    }
                }
                
                // Se siamo qui, proviamo una ricerca diretta all'API RAWG
                error_log("Tentativo di ricerca diretta all'API RAWG");
                $directApiUrl = "https://api.rawg.io/api/games?key={$apiKey}&search=" . urlencode($title) . "&page_size=1";
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $directApiUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'User-Agent: GameTracker/1.0 (fede.angelis.2003@gmail.com)'
                ]);
                
                $directResponse = curl_exec($ch);
                $directHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($directHttpCode === 200) {
                    $directData = json_decode($directResponse, true);
                    error_log("Risposta diretta dall'API RAWG: " . print_r($directData, true));
                    
                    if (!empty($directData['results']) && !empty($directData['results'][0])) {
                        $game = $directData['results'][0];
                        $coverUrl = $game['background_image'] ?? $game['image_background'] ?? null;
                        
                        if ($coverUrl) {
                            $coverUrl = strtok($coverUrl, '?');
                            sendResponse([
                                'status' => 'success',
                                'data' => [
                                    'cover_url' => $coverUrl,
                                    'game_title' => $game['name'] ?? $title
                                ]
                            ]);
                            return;
                        }
                    }
                }
                
                // Se siamo qui, non siamo riusciti a trovare la cover
                $results = [];
                
                error_log("Risultati trovati: " . count($results));
                
                if (empty($results) || empty($results[0])) {
                    error_log("Nessun risultato trovato per: " . $title);
                    error_log("Struttura completa della risposta: " . print_r($data, true));
                    
                    sendResponse([
                        'status' => 'error',
                        'message' => 'Nessun risultato trovato per: ' . $title,
                        'debug' => [
                            'search_term' => $title,
                            'api_response_structure' => array_keys($data),
                            'data_structure' => isset($data['data']) ? array_keys($data['data']) : [],
                            'results_count' => count($results ?? [])
                        ]
                    ]);
                    return;
                }
                
                $game = $results[0];
                error_log("Dettagli del gioco trovato: " . print_r($game, true));
                
                // Prova diversi campi possibili per l'URL dell'immagine
                $coverUrl = $game['background_image'] ?? $game['image_background'] ?? $game['background'] ?? null;
                
                if (!$coverUrl) {
                    error_log("Nessun URL dell'immagine trovato nei campi standard. Campi disponibili: " . print_r(array_keys($game), true));
                    sendResponse([
                        'status' => 'error',
                        'message' => 'Nessuna immagine di copertina trovata per questo gioco',
                        'debug' => [
                            'available_fields' => array_keys($game),
                            'game_data' => $game
                        ]
                    ]);
                    return;
                }
                
                // Pulisci l'URL per rimuovere eventuali parametri non necessari
                $coverUrl = strtok($coverUrl, '?');
                
                sendResponse([
                    'status' => 'success',
                    'data' => [
                        'cover_url' => $coverUrl,
                        'game_title' => $game['name'] ?? $title
                    ]
                ]);
                return;
            }
            // Handle move operation
            else if (isset($input['action']) && $input['action'] === 'move') {
                // Handle move operation
                if (empty($input['id']) || !isset($input['section'])) {
                    throw new Exception('ID and section are required for move operation', 400);
                }
                
                try {
                    $pdo = getGameTrackerConnection();
                    $pdo->beginTransaction();
                    
                    $stmt = $pdo->prepare("UPDATE games SET section = :section WHERE id = :id");
                    $stmt->execute([
                        ':section' => $input['section'],
                        ':id' => $input['id']
                    ]);
                    
                    if ($stmt->rowCount() === 0) {
                        throw new Exception('Game not found or no changes made', 404);
                    }
                    
                    $pdo->commit();
                    sendResponse([
                        'status' => 'success',
                        'message' => 'Game moved successfully'
                    ]);
                } catch (Exception $e) {
                    if (isset($pdo)) {
                        $pdo->rollBack();
                    }
                    throw new Exception('Failed to move game: ' . $e->getMessage(), 500);
                }
            }
            
            // Handle regular POST (add new game)
            // Add authentication check if needed
            // if (!isUserAuthenticated()) {
            //     throw new Exception('Unauthorized', 401);
            // }
            
            // Check if this is a delete action from query string
            $isDeleteAction = ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'delete') ||
                            (isset($input['action']) && $input['action'] === 'delete');
            
            // Only check required fields for non-delete operations
            if (!$isDeleteAction) {
                $requiredFields = ['title', 'section'];
                foreach ($requiredFields as $field) {
                    if (empty($input[$field])) {
                        throw new Exception("Missing required field: $field", 400);
                    }
                }
            }
            
            // Process and validate input data
            $platforms = [];
            if (isset($input['platforms']) && is_array($input['platforms'])) {
                $platforms = $input['platforms'];
            } elseif (isset($input['platform'])) {
                if (is_array($input['platform'])) {
                    $platforms = $input['platform'];
                } else {
                    $platforms = $input['platform'] ? [$input['platform']] : [];
                }
            }
            
            $gameData = [
                'title' => trim($input['title']),
                'platform' => $platforms,
                'section' => in_array($input['section'] ?? 'played', ['played', 'backlog']) ? $input['section'] : 'played',
                'playtime' => $input['playtime'] ?? null,
                'total_score' => isset($input['total_score']) ? (int)$input['total_score'] : null,
                'aesthetic_score' => isset($input['aesthetic_score']) ? (int)$input['aesthetic_score'] : null,
                'ost_score' => isset($input['ost_score']) ? (int)$input['ost_score'] : null,
                'difficulty' => isset($input['difficulty']) ? (int)$input['difficulty'] : null,
                'status' => $input['status'] ?? null,
                'trophy_percentage' => isset($input['trophy_percentage']) ? (int)$input['trophy_percentage'] : null,
                'platinum_date' => $input['platinum_date'] ?? null,
                'replays' => isset($input['replays']) ? (int)$input['replays'] : 0,
                'first_played' => $input['first_played'] ?? null,
                'last_finished' => $input['last_finished'] ?? null,
                'review' => $input['review'] ?? null,
                'cover_url' => $input['cover_url'] ?? null
            ];
            
            // Convert empty strings to null for optional fields
            foreach ($gameData as $key => $value) {
                if ($value === '') {
                    $gameData[$key] = null;
                }
            }
            
            try {
                $pdo = getGameTrackerConnection();
                $pdo->beginTransaction();
                $result = addGameToDB($pdo, $gameData);
                $pdo->commit();
                sendResponse([
                    'status' => 'success', 
                    'message' => 'Game added successfully', 
                    'id' => $result
                ], 201);
            } catch (Exception $e) {
                if (isset($pdo)) {
                    $pdo->rollBack();
                }
                throw new Exception('Failed to add game: ' . $e->getMessage(), 500);
            }
            break;
            
        case 'PUT':
            // Similar to POST but for updates
            break;
            
        case 'DELETE':
            // Handle delete operations
            $gameId = $_GET['id'] ?? null;
            
            if (!$gameId) {
                throw new Exception('Game ID is required', 400);
            }
            
            try {
                $pdo = getGameTrackerConnection();
                $pdo->beginTransaction();
                
                // First check if the game exists
                $checkStmt = $pdo->prepare("SELECT id FROM games WHERE id = :id");
                $checkStmt->execute([':id' => $gameId]);
                
                if ($checkStmt->rowCount() === 0) {
                    throw new Exception('Game not found', 404);
                }
                
                // Delete the game
                $stmt = $pdo->prepare("DELETE FROM games WHERE id = :id");
                $stmt->execute([':id' => $gameId]);
                
                $pdo->commit();
                
                // Send success response
                sendResponse([
                    'status' => 'success',
                    'message' => 'Game deleted successfully',
                    'id' => $gameId
                ]);
            } catch (Exception $e) {
                if (isset($pdo)) {
                    $pdo->rollBack();
                }
                
                $statusCode = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
                throw new Exception('Failed to delete game: ' . $e->getMessage(), $statusCode);
            }
            break;
            
        default:
            throw new Exception('Method not allowed', 405);
    }
    
} catch (Exception $e) {
    $statusCode = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
    $errorMessage = $statusCode >= 500 && !in_array(parse_url($_SERVER['HTTP_REFERER'] ?? '', PHP_URL_HOST), ['localhost', '127.0.0.1'])
        ? 'An internal server error occurred'
        : $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine();
    
    error_log('API Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine() . '\n' . $e->getTraceAsString());
    
    sendResponse([
        'status' => 'error',
        'message' => $errorMessage,
        'code' => $statusCode
    ], $statusCode);
}

// Ensure we're sending JSON
header('Content-Type: application/json; charset=utf-8');

// Clean and sanitize data before JSON encoding
array_walk_recursive($response, function(&$value) {
    if (is_string($value)) {
        // Remove any invalid UTF-8 characters
        $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
    }
});

// Send response with proper JSON encoding
http_response_code($statusCode);
$jsonResponse = json_encode($response, 
    JSON_UNESCAPED_UNICODE | 
    JSON_UNESCAPED_SLASHES |
    JSON_INVALID_UTF8_SUBSTITUTE |
    JSON_PARTIAL_OUTPUT_ON_ERROR
);

// Check for JSON encoding errors
if ($jsonResponse === false) {
    $errorResponse = [
        'status' => 'error',
        'message' => 'Error encoding response',
        'json_error' => json_last_error_msg()
    ];
    http_response_code(500);
    echo json_encode($errorResponse);
} else {
    echo $jsonResponse;
}

// Make sure no other output is sent
exit();

/**
 * Get all games with optional filtering and sorting
 */
function getGamesFromDB($pdo, $section = 'played', $sortBy = 'title', $sortOrder = 'ASC') {
    // Validate and sanitize input
    $section = in_array($section, ['played', 'backlog']) ? $section : 'played';
    $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';
    
    // Define allowed sort columns to prevent SQL injection
    $allowedSortColumns = [
        'id', 'title', 'platform', 'playtime', 'total_score', 'aesthetic_score',
        'ost_score', 'difficulty', 'status', 'trophy_percentage', 'first_played',
        'last_finished', 'created_at', 'priority'
    ];
    
    $sortBy = in_array($sortBy, $allowedSortColumns) ? $sortBy : 'title';
    
    // Force priority sort for backlog
    if ($section === 'backlog' && $sortBy !== 'priority') {
        $orderBy = "priority DESC, title ASC";
    } else {
        $orderBy = "{$sortBy} {$sortOrder}";
    }
    
    try {
        $query = "SELECT * FROM games WHERE section = :section ORDER BY {$orderBy}";
        $stmt = $pdo->prepare($query);
        $stmt->execute([':section' => $section]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        error_log("Database error in getGames: " . $e->getMessage());
        return [];
    }
}

/**
 * Get a single game by ID
 */
function getGameFromDB($pdo, $id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM games WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error in getGame: " . $e->getMessage());
        return false;
    }
}

/**
 * Add a new game
 */
function addGameToDB($pdo, $data) {
    $query = "INSERT INTO games (
        title, 
        platform, 
        section, 
        playtime,
        total_score,
        aesthetic_score,
        ost_score,
        difficulty,
        status,
        trophy_percentage,
        platinum_date,
        replays,
        first_played,
        last_finished,
        review,
        cover_url,
        created_at
    ) VALUES (
        :title, 
        :platform, 
        :section, 
        :playtime,
        :total_score,
        :aesthetic_score,
        :ost_score,
        :difficulty,
        :status,
        :trophy_percentage,
        :platinum_date,
        :replays,
        :first_played,
        :last_finished,
        :review,
        :cover_url,
        NOW()
    )";
    
    // Debug log
    error_log('addGameToDB - Input data: ' . print_r($data, true));

    // Handle platforms array
    $platform = '';
    if (isset($data['platform'])) {
        if (is_array($data['platform'])) {
            $platform = implode(', ', $data['platform']);
        } else {
            $platform = $data['platform'];
        }
    }
    error_log('Processed platform: ' . $platform);
    
    // Ensure UTF-8 encoding for text fields
    $title = isset($data['title']) ? mb_convert_encoding($data['title'], 'UTF-8', 'auto') : null;
    $platform = $platform ? mb_convert_encoding($platform, 'UTF-8', 'auto') : null;
    $status = isset($data['status']) ? mb_convert_encoding($data['status'], 'UTF-8', 'auto') : null;
    $review = isset($data['review']) ? mb_convert_encoding($data['review'], 'UTF-8', 'auto') : null;
    $cover_url = isset($data['cover_url']) ? mb_convert_encoding($data['cover_url'], 'UTF-8', 'auto') : null;
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':title' => $title,
        ':platform' => $platform,
        ':section' => $data['section'] ?? 'played',
        ':playtime' => isset($data['playtime']) ? mb_convert_encoding($data['playtime'], 'UTF-8', 'auto') : null,
        ':total_score' => isset($data['total_score']) ? (int)$data['total_score'] : null,
        ':aesthetic_score' => isset($data['aesthetic_score']) ? (int)$data['aesthetic_score'] : null,
        ':ost_score' => isset($data['ost_score']) ? (int)$data['ost_score'] : null,
        ':difficulty' => isset($data['difficulty']) ? (int)$data['difficulty'] : null,
        ':status' => $status,
        ':trophy_percentage' => isset($data['trophy_percentage']) ? (int)$data['trophy_percentage'] : null,
        ':platinum_date' => isset($data['platinum_date']) ? mb_convert_encoding($data['platinum_date'], 'UTF-8', 'auto') : null,
        ':replays' => isset($data['replays']) ? (int)$data['replays'] : 0,
        ':first_played' => isset($data['first_played']) ? mb_convert_encoding($data['first_played'], 'UTF-8', 'auto') : null,
        ':last_finished' => isset($data['last_finished']) ? mb_convert_encoding($data['last_finished'], 'UTF-8', 'auto') : null,
        ':review' => $review,
        ':cover_url' => $cover_url
    ]);
    
    return $pdo->lastInsertId();
}

// Add other CRUD operations as needed
?>
