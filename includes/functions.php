<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/update_timestamp.php';

// RAWG API configuration
define('RAWG_API_KEY', 'b06700683ebe479fa895f1db55b1abb8');
define('RAWG_API_BASE', 'https://api.rawg.io/api');

// Get all games from a specific section
function getGames($section = 'played', $sortBy = 'title', $sortOrder = 'ASC') {
    $pdo = getGameTrackerConnection();
    
    $validSortColumns = ['title', 'platform', 'playtime', 'total_score', 'aesthetic_score', 
                        'ost_score', 'difficulty', 'status', 'trophy_percentage', 
                        'first_played', 'last_finished', 'created_at'];
    
    if (!in_array($sortBy, $validSortColumns)) {
        $sortBy = 'title';
    }
    
    $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';
    
    // Special sorting for backlog section
    if ($section === 'backlog') {
        $query = "SELECT * FROM games WHERE section = ? ORDER BY COALESCE(priority, 0) DESC, title ASC";
        $stmt = $pdo->prepare($query);
    } else {
        $query = "SELECT * FROM games WHERE section = ? ORDER BY $sortBy $sortOrder";
        $stmt = $pdo->prepare($query);
    }
    
    $stmt->execute([$section]);
    
    // Update the site timestamp
    updateSiteTimestamp();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Add a new game
function addGame($data) {
    $pdo = getGameTrackerConnection();
    
    $platforms = isset($data['platform']) && is_array($data['platform']) 
                ? implode(', ', $data['platform']) 
                : (isset($data['platform']) ? $data['platform'] : '');
    
    // Only include priority if we're adding to the backlog
    $includePriority = (isset($data['section']) && $data['section'] === 'backlog') || 
                      (isset($data['priority']) && $data['section'] === 'backlog');
    
    $priorityField = $includePriority ? 'priority, ' : '';
    $priorityValue = $includePriority ? '?, ' : '';
    
    $sql = "
        INSERT INTO games (
            title, platform, playtime, total_score, aesthetic_score, ost_score,
            difficulty, status, trophy_percentage, platinum_date, replays,
            first_played, last_finished, review, cover_url, section" . 
            ($includePriority ? ', priority' : '') . 
            ", created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?" .
            ($includePriority ? ', ?' : '') . 
            ", NOW())
    ";
    
    $stmt = $pdo->prepare($sql);
    
    $params = [
        $data['title'],
        $platforms,
        $data['playtime'] ?? null,
        $data['total_score'] ?? null,
        $data['aesthetic_score'] ?? null,
        $data['ost_score'] ?? null,
        $data['difficulty'] ?? null,
        $data['status'] ?? null,
        $data['trophy_percentage'] ?? null,
        $data['platinum_date'] ?? null,
        $data['replays'] ?? 0,
        $data['first_played'] ?? null,
        $data['last_finished'] ?? null,
        $data['review'] ?? null,
        $data['cover_url'] ?? null,
        $data['section'] ?? 'played'
    ];
    
    // Add priority to params if needed
    if ($includePriority) {
        array_splice($params, 16, 0, [$data['priority'] ?? 0]);
    }
    
    $result = $stmt->execute($params);
    
    if ($result) {
        // Update the site timestamp only if the insertion was successful
        updateSiteTimestamp();
    }
    
    return $result;
}

// Update a game
function updateGame($id, $data) {
    $pdo = getGameTrackerConnection();
    
    $platforms = isset($data['platform']) && is_array($data['platform']) 
                ? implode(', ', $data['platform']) 
                : (isset($data['platform']) ? $data['platform'] : '');
    
    // Only include priority in the update if we're in the backlog section
    $includePriority = (isset($data['section']) && $data['section'] === 'backlog') || 
                      (isset($data['priority']) && $data['section'] === 'backlog');
    
    $priorityField = $includePriority ? ', priority = ?' : '';
    
    $stmt = $pdo->prepare("
        UPDATE games SET 
            title = ?, platform = ?, playtime = ?, total_score = ?, aesthetic_score = ?, 
            ost_score = ?, difficulty = ?, status = ?, trophy_percentage = ?, 
            platinum_date = ?, replays = ?, first_played = ?, last_finished = ?, 
            review = ?, cover_url = ?, section = ?
            $priorityField
        WHERE id = ?
    ");
    
    $params = [
        $data['title'],
        $platforms,
        $data['playtime'] ?? null,
        $data['total_score'] ?? null,
        $data['aesthetic_score'] ?? null,
        $data['ost_score'] ?? null,
        $data['difficulty'] ?? null,
        $data['status'] ?? null,
        $data['trophy_percentage'] ?? null,
        $data['platinum_date'] ?? null,
        $data['replays'] ?? 0,
        $data['first_played'] ?? null,
        $data['last_finished'] ?? null,
        $data['review'] ?? null,
        $data['cover_url'] ?? null,
        $data['section'] ?? 'played'
    ];
    
    // Add priority to params if needed
    if ($includePriority) {
        $params[] = $data['priority'] ?? 0;
    }
    
    // Add ID as the last parameter
    $params[] = $id;
    
    $result = $stmt->execute($params);
    
    if ($result) {
        // Update the site timestamp only if the update was successful
        updateSiteTimestamp();
    }
    
    return $result;
}

// Delete a game
function deleteGame($id) {
    $pdo = getGameTrackerConnection();
    $stmt = $pdo->prepare("DELETE FROM games WHERE id = ?");
    $result = $stmt->execute([$id]);
    
    if ($result) {
        // Update the site timestamp only if the deletion was successful
        updateSiteTimestamp();
    }
    
    return $result;
}

// Get a single game by ID
function getGame($id) {
    $pdo = getGameTrackerConnection();
    $stmt = $pdo->prepare("SELECT *, COALESCE(priority, 0) as priority FROM games WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Search for game cover using RAWG API
function searchGameCover($title) {
    $url = RAWG_API_BASE . '/games?key=' . RAWG_API_KEY . '&search=' . urlencode($title) . '&page_size=1';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_USERAGENT, 'GameTracker/1.0');
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 && $response) {
        $data = json_decode($response, true);
        if (isset($data['results'][0]['background_image'])) {
            $imageUrl = $data['results'][0]['background_image'];
            // Add crop parameters for consistent sizing
            if (strpos($imageUrl, 'media.rawg.io') !== false) {
                $imageUrl = str_replace('media.rawg.io/media/', 'media.rawg.io/media/crop/600/400/', $imageUrl);
            }
            return $imageUrl;
        }
    }
    
    return null;
}

// Process TSV import
function importFromTSV($filePath) {
    $results = ['success' => 0, 'errors' => []];
    
    if (!file_exists($filePath)) {
        $results['errors'][] = 'File non trovato';
        return $results;
    }
    
    $file = fopen($filePath, 'r');
    if (!$file) {
        $results['errors'][] = 'Impossibile aprire il file';
        return $results;
    }
    
    // Read header row to map columns
    $headers = fgetcsv($file, 0, "\t");
    if (!$headers) {
        $results['errors'][] = 'File TSV non valido';
        fclose($file);
        return $results;
    }
    
    // Map common column names (case insensitive)
    $columnMap = [];
    foreach ($headers as $index => $header) {
        $header = strtolower(trim($header));
        switch ($header) {
            case 'titolo':
            case 'title':
                $columnMap['title'] = $index;
                break;
            case 'piattaforma':
            case 'platform':
                $columnMap['platform'] = $index;
                break;
            case 'ore di gioco':
            case 'playtime':
                $columnMap['playtime'] = $index;
                break;
            case 'voto totale':
            case 'total_score':
                $columnMap['total_score'] = $index;
                break;
            case 'voto aesthetic':
            case 'aesthetic_score':
                $columnMap['aesthetic_score'] = $index;
                break;
            case 'voto ost':
            case 'ost_score':
                $columnMap['ost_score'] = $index;
                break;
            case 'difficoltà':
            case 'difficulty':
                $columnMap['difficulty'] = $index;
                break;
            case 'stato':
            case 'status':
                $columnMap['status'] = $index;
                break;
            case '% trofei':
            case 'trophy_percentage':
                $columnMap['trophy_percentage'] = $index;
                break;
            case 'platino/masterato in':
            case 'platinum_date':
                $columnMap['platinum_date'] = $index;
                break;
            case 'replay completati':
            case 'replays':
                $columnMap['replays'] = $index;
                break;
            case 'prima volta giocato':
            case 'first_played':
                $columnMap['first_played'] = $index;
                break;
            case 'ultima volta finito':
            case 'last_finished':
                $columnMap['last_finished'] = $index;
                break;
            case 'recensione':
            case 'review':
                $columnMap['review'] = $index;
                break;
            case 'link copertina':
            case 'cover_url':
                $columnMap['cover_url'] = $index;
                break;
        }
    }
    
    // Process data rows
    $rowNumber = 1;
    while (($row = fgetcsv($file, 0, "\t")) !== false) {
        $rowNumber++;
        
        if (empty($row) || count($row) < 2) {
            continue; // Skip empty rows
        }
        
        $gameData = ['section' => 'played']; // Default to played section
        
        // Extract data based on column mapping
        foreach ($columnMap as $field => $index) {
            if (isset($row[$index])) {
                $value = trim($row[$index]);
                if ($value !== '') {
                    $gameData[$field] = $value;
                }
            }
        }
        
        // Title is required
        if (empty($gameData['title'])) {
            $results['errors'][] = "Riga $rowNumber: Titolo mancante";
            continue;
        }
        
        // Convert numeric fields
        $numericFields = ['total_score', 'aesthetic_score', 'ost_score', 'difficulty', 'trophy_percentage', 'replays'];
        foreach ($numericFields as $field) {
            if (isset($gameData[$field]) && is_numeric($gameData[$field])) {
                $gameData[$field] = (int)$gameData[$field];
            }
        }
        
        try {
            if (addGame($gameData)) {
                $results['success']++;
            } else {
                $results['errors'][] = "Riga $rowNumber: Errore durante l'inserimento";
            }
        } catch (Exception $e) {
            $results['errors'][] = "Riga $rowNumber: " . $e->getMessage();
        }
    }
    
    fclose($file);
    return $results;
}

// Move game between sections
function moveGameToSection($id, $section) {
    $pdo = getGameTrackerConnection();
    
    // If moving to backlog, keep the current priority or set to default (0)
    // If moving away from backlog, set priority to NULL
    if ($section === 'backlog') {
        $stmt = $pdo->prepare("UPDATE games SET section = ?, priority = COALESCE(priority, 0) WHERE id = ?");
    } else {
        $stmt = $pdo->prepare("UPDATE games SET section = ?, priority = NULL WHERE id = ?");
    }
    
    return $stmt->execute([$section, $id]);
}

// Find and update missing game covers with rate limiting
function findAndUpdateMissingCovers() {
    $pdo = getGameTrackerConnection();
    
    // Find games with no cover URL or empty cover URL
    $stmt = $pdo->query("SELECT id, title FROM games WHERE cover_url IS NULL OR cover_url = ''");
    $games = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($games)) {
        return ['success' => true, 'message' => 'No games with missing covers found', 'updated' => []];
    }
    
    $updated = [];
    $failed = [];
    $rateLimit = 2; // Max 2 requests per second
    $delay = 1000000 / $rateLimit; // Microseconds to wait between requests
    
    foreach ($games as $game) {
        try {
            $coverUrl = searchGameCover($game['title']);
            
            if ($coverUrl) {
                // Update the game with the found cover URL
                $updateStmt = $pdo->prepare("UPDATE games SET cover_url = ? WHERE id = ?");
                if ($updateStmt->execute([$coverUrl, $game['id']])) {
                    $updated[] = [
                        'id' => $game['id'],
                        'title' => $game['title'],
                        'cover_url' => $coverUrl
                    ];
                } else {
                    $failed[] = [
                        'id' => $game['id'],
                        'title' => $game['title'],
                        'error' => 'Database update failed'
                    ];
                }
            } else {
                $failed[] = [
                    'id' => $game['id'],
                    'title' => $game['title'],
                    'error' => 'No cover found'
                ];
            }
            
            // Respect rate limiting
            usleep($delay);
            
        } catch (Exception $e) {
            $failed[] = [
                'id' => $game['id'],
                'title' => $game['title'],
                'error' => $e->getMessage()
            ];
            
            // In case of error, wait a bit longer before next request
            usleep($delay * 2);
        }
    }
    
    return [
        'success' => true,
        'message' => sprintf('Processed %d games', count($games)),
        'updated' => $updated,
        'failed' => $failed,
        'stats' => [
            'total' => count($games),
            'updated' => count($updated),
            'failed' => count($failed)
        ]
    ];
}

?>
