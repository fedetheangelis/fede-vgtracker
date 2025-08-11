<?php
function getConnection() {
    return new PDO(
        'mysql:host=sql201.infinityfree.com;dbname=if0_39671895_gametracker',
        'if0_39671895',
        'cYJGqft7NYwo',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
}