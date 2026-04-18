<?php
require_once __DIR__ . '/config.php';

function getPDO(): PDO {
    static $pdo = null;
    global $options;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('Erreur de connexion à la base de données.');
        }
    }
    return $pdo;
}
