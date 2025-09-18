<?php
header('Content-Type: application/json');

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Require admin authentication for TSV import
try {
    error_log("TSV Import Debug - Checking admin auth...");
    requireAdmin();
    error_log("TSV Import Debug - Admin auth passed");
} catch (Exception $e) {
    error_log("TSV Import Debug - Admin auth failed: " . $e->getMessage());
    throw $e;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (!isset($_FILES['tsv_file'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No file uploaded']);
    exit;
}

$file = $_FILES['tsv_file'];

// Check for upload errors
if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'File upload error: ' . $file['error']]);
    exit;
}

// Check file type
$allowedTypes = ['text/tab-separated-values', 'text/plain', 'application/octet-stream'];
$fileInfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($fileInfo, $file['tmp_name']);
finfo_close($fileInfo);

// Also check file extension
$fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($mimeType, $allowedTypes) && !in_array($fileExtension, ['tsv', 'txt'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid file type. Please upload a TSV file.']);
    exit;
}

// Process the TSV file
try {
    // Debug: Log file info
    error_log("TSV Import Debug - File: " . $file['name'] . ", Size: " . $file['size'] . ", Temp: " . $file['tmp_name']);
    
    $results = importFromTSV($file['tmp_name']);
    
    // Debug: Log results
    error_log("TSV Import Debug - Results: " . json_encode($results));
    
    if ($results['success'] > 0 || empty($results['errors'])) {
        echo json_encode([
            'success' => true,
            'message' => "Importati {$results['success']} giochi con successo",
            'imported_count' => $results['success'],
            'errors' => $results['errors']
        ]);
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Nessun gioco importato',
            'errors' => $results['errors']
        ]);
    }
} catch (Exception $e) {
    error_log("TSV Import Exception: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Import failed: ' . $e->getMessage()]);
}
?>
