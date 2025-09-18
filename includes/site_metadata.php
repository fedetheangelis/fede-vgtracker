<?php
require_once __DIR__ . '/../config/database.php';

function updateSiteMetadata($pdo) {
    try {
        $stmt = $pdo->prepare("UPDATE site_metadata SET last_updated = CURRENT_TIMESTAMP WHERE id = 1");
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Error updating site metadata: " . $e->getMessage());
        return false;
    }
}

function getSiteMetadata($pdo) {
    try {
        $stmt = $pdo->query("SELECT version, last_updated FROM site_metadata WHERE id = 1");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting site metadata: " . $e->getMessage());
        return [
            'version' => '1.0.0',
            'last_updated' => date('Y-m-d H:i:s')
        ];
    }
}
