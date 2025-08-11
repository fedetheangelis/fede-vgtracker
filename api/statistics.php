<?php
header('Content-Type: application/json');
require_once '../config/database.php';

// Function to get all games from the database
function getAllGames($pdo) {
    $stmt = $pdo->query("SELECT * FROM games ORDER BY title");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get status distribution
function getStatusDistribution($pdo) {
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM games WHERE status IS NOT NULL AND status != '' GROUP BY status ORDER BY count DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get platform distribution with support for multiple platforms per game
function getPlatformDistribution($pdo) {
    try {
        // Get all platforms from both sections
        $query = "
            SELECT 'games' as source, platform 
            FROM games 
            WHERE platform IS NOT NULL AND platform != ''
            AND (section = 'played' OR section = 'backlog')
        ";
        
        $stmt = $pdo->query($query);
        $allPlatforms = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Debug: log the raw data we got from the database
        error_log("Raw platform data from DB: " . print_r($allPlatforms, true));
        
        // Check if we got any data at all
        if (empty($allPlatforms)) {
            error_log("No platform data found in the database");
        }
        
        // Define the platforms we want to track (in the exact order we want them displayed)
        $platforms = [
            'DIGITALE' => 0,
            'FISICO' => 0,
            'PS1' => 0,
            'PS2' => 0,
            'PS3' => 0,
            'PS4' => 0,
            'PS5' => 0,
            'PC' => 0,
            'SWITCH' => 0,
            '3DS' => 0,
            'GBA' => 0,
            'WII' => 0
        ];
        
        // Process each platform string
        foreach ($allPlatforms as $row) {
            $platformStr = trim($row['platform']);
            if (empty($platformStr)) continue;
            
            // Split by comma and process each platform
            $platformList = explode(',', $platformStr);
            
            foreach ($platformList as $platform) {
                $platform = trim(strtoupper($platform));
                if (empty($platform)) continue;
                
                // Directly increment the platform counter if it exists
                if (array_key_exists($platform, $platforms)) {
                    $platforms[$platform]++;
                }
            }
        }
        
        // Debug: log the platform counts before conversion
        error_log("Platform counts before conversion: " . print_r($platforms, true));
        
        // Convert to the expected format (array of associative arrays)
        $result = [];
        foreach ($platforms as $platform => $count) {
            $result[] = [
                'platform' => $platform,
                'count' => $count
            ];
        }
        
        // Debug: log the final result
        error_log("Final platform result: " . print_r($result, true));
        
        return $result;
        
    } catch (Exception $e) {
        $errorMsg = "Error in getPlatformDistribution: " . $e->getMessage() . "\n" . $e->getTraceAsString();
        error_log($errorMsg);
        return [];
    }
}

// Function to get difficulty distribution
function getDifficultyDistribution($pdo) {
    $stmt = $pdo->query("SELECT difficulty, COUNT(*) as count FROM games WHERE difficulty IS NOT NULL AND difficulty != '' GROUP BY difficulty ORDER BY difficulty");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get top difficult games
function getTopDifficultGames($pdo, $limit = 15) {
    $stmt = $pdo->prepare("SELECT id, title, platform, difficulty, status FROM games WHERE difficulty IS NOT NULL AND difficulty != '' ORDER BY difficulty + 0 DESC, title LIMIT :limit");
    $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get vote distribution in bins of 10
function getVoteDistribution($pdo) {
    try {
        // Initialize bins from 0 to 100 in steps of 10
        $bins = array_fill(0, 11, 0); // 0, 10, 20, ..., 100
        
        // Get all total_scores that are not null
        $stmt = $pdo->query("
            SELECT total_score 
            FROM games 
            WHERE total_score IS NOT NULL 
            AND total_score >= 0 
            AND total_score <= 100
        ");
        
        $scores = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Count scores in each bin
        foreach ($scores as $score) {
            // Calculate the bin index (0-10)
            $binIndex = min(floor($score / 10), 10);
            $bins[$binIndex]++;
        }
        
        // Format the result
        $result = [];
        for ($i = 0; $i <= 10; $i++) {
            $rangeStart = $i * 10;
            $rangeEnd = ($i === 10) ? 100 : ($rangeStart + 9);
            $label = $i === 0 ? "0-9" : ($i === 10 ? "100" : "$rangeStart-$rangeEnd");
            
            $result[] = [
                'range' => $label,
                'start' => $rangeStart,
                'end' => $rangeEnd,
                'count' => $bins[$i]
            ];
        }
        
        return $result;
        
    } catch (Exception $e) {
        error_log('Error in getVoteDistribution: ' . $e->getMessage());
        error_log('Stack trace: ' . $e->getTraceAsString());
        return [];
    }
}

// Function to get top games by playtime
function getTopPlaytimeGames($pdo, $limit = 15) {
    $games = $pdo->query("
        SELECT id, title, platform, playtime, status 
        FROM games 
        WHERE playtime IS NOT NULL 
        AND playtime != ''
        AND (section = 'played' OR section = 'backlog')
        ORDER BY title
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    // Process each game to calculate total playtime
    foreach ($games as &$game) {
        $game['total_playtime'] = 0;
        if (!empty($game['playtime'])) {
            // Calculate total playtime using the same logic as the frontend
            $playtime = $game['playtime'];
            $playtime = preg_replace('/\s+/', '', $playtime);
            $playtime = str_replace(',', '.', $playtime);
            $playtime = preg_replace('/[^\d.+]/', '', $playtime);
            
            $parts = explode('+', $playtime);
            $total = 0;
            foreach ($parts as $part) {
                if (is_numeric($part)) {
                    $total += (float)$part;
                }
            }
            
            $game['total_playtime'] = (int)round($total);
        }
    }
    
    // Sort by total playtime descending
    usort($games, function($a, $b) {
        return $b['total_playtime'] - $a['total_playtime'];
    });
    
    // Return only the top $limit games
    return array_slice($games, 0, $limit);
}

// Function to get games played by year distribution
function getPlayedByYear($pdo) {
    try {
        error_log('Starting getPlayedByYear function');
        
        // Get all games with a non-empty "first_played" field
        $query = "
            SELECT first_played 
            FROM games 
            WHERE first_played IS NOT NULL 
            AND first_played != ''
            AND (section = 'played' OR section = 'backlog')
        ";
        
        error_log('Executing query: ' . $query);
        $stmt = $pdo->query($query);
        
        if ($stmt === false) {
            $error = $pdo->errorInfo();
            throw new Exception('Query failed: ' . $error[2]);
        }
        
        $years = [];
        $rowCount = 0;
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $rowCount++;
            $dateStr = trim($row['first_played']);
            error_log("Processing row $rowCount: '$dateStr'");
            
            if (empty($dateStr)) continue;
            
            // Try to extract year using regex patterns
            if (preg_match('/(?:^|~|\\b)(\d{4})\b/', $dateStr, $matches)) {
                $year = (int)$matches[1];
                error_log("  - Found year: $year");
                
                if ($year >= 1970 && $year <= (int)date('Y') + 1) { // Basic validation
                    if (!isset($years[$year])) {
                        $years[$year] = 0;
                    }
                    $years[$year]++;
                    error_log("  - Valid year: $year, count: " . $years[$year]);
                } else {
                    error_log("  - Year $year outside valid range (1970-" . (date('Y') + 1) . ")");
                }
            } else {
                error_log("  - No year found in: '$dateStr'");
            }
        }
        
        error_log("Processed $rowCount rows, found " . count($years) . " unique years");
        error_log('Years data: ' . print_r($years, true));
        
        return $years;
    
        // Sort years in ascending order
        ksort($years);
        
        // Convert to array of objects for the chart
        $result = [];
        foreach ($years as $year => $count) {
            $result[] = [
                'year' => (string)$year,
                'count' => $count
            ];
        }
        
        error_log('Final result: ' . print_r($result, true));
        return $result;
        
    } catch (Exception $e) {
        error_log('Error in getPlayedByYear: ' . $e->getMessage());
        error_log('Stack trace: ' . $e->getTraceAsString());
        return [];
    }
}

try {
    $pdo = getGameTrackerConnection();
    
    // Get all statistics
    $statusDistribution = getStatusDistribution($pdo);
    $platformDistribution = getPlatformDistribution($pdo);
    $difficultyDistribution = getDifficultyDistribution($pdo);
    $topDifficultGames = getTopDifficultGames($pdo);
    $topPlaytimeGames = getTopPlaytimeGames($pdo);
    $playedByYear = getPlayedByYear($pdo);
    $voteDistribution = getVoteDistribution($pdo);
    
    // Prepare response
    $response = [
        'success' => true,
        'data' => [
            'status' => $statusDistribution,
            'platform' => $platformDistribution,
            'difficulty' => $difficultyDistribution,
            'topDifficultGames' => $topDifficultGames,
            'topPlaytimeGames' => $topPlaytimeGames,
            'playedByYear' => $playedByYear,
            'voteDistribution' => $voteDistribution
        ]
    ];
    
    // Log the response for debugging
    error_log('Statistics response: ' . json_encode($response, JSON_PRETTY_PRINT));
    
    echo json_encode($response);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
