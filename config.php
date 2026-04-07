<?php
// config.php
// Remplacez les valeurs par celles de votre environnement
define('DB_HOST', 'localhost');
define('DB_NAME', 'blue_horizon_hotel');
define('DB_USER', 'root');
define('DB_PASS', '');

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];
