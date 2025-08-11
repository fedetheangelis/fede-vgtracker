<?php
function updateSiteTimestamp() {
    $file = __DIR__ . '/../site_metadata.json';
    $data = [
        'last_updated' => date('Y-m-d H:i:s'),
    ];
    
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
    return $data;
}

function getSiteMetadata() {
    $file = __DIR__ . '/../site_metadata.json';
    if (!file_exists($file)) {
        return updateSiteTimestamp();
    }
    
    $data = json_decode(file_get_contents($file), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return updateSiteTimestamp();
    }
    
    return $data;
}
?>
