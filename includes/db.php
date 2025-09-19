<?php
if (!file_exists(__DIR__.'/../config.php')) {
    die('Missing config.php. Please copy config.sample.php to config.php and edit credentials.');
}
require_once __DIR__.'/../config.php';

// Ahora es seguro usar las constantes
if (APP_DEBUG) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

try {
    $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4', DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    // Si es una petición AJAX, devolver JSON
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['error'=>'Error de conexión a la base de datos: '.$e->getMessage()]);
        exit;
    } else {
        die('DB connection failed: ' . $e->getMessage());
    }
}
