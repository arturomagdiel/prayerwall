<?php
// Forzar debug y mostrar errores desde el inicio
ini_set('display_errors', 1);
error_reporting(E_ALL);
echo "Debug: inicio index.php<br>";

require_once __DIR__.'/../includes/db.php';
echo "Debug: después de db.php<br>";
require_once __DIR__.'/../includes/functions.php';
echo "Debug: después de functions.php<br>";

$view = $_GET['view'] ?? 'wall';
echo "Debug: view = $view<br>";

$map = [
  'wall' => __DIR__.'/pages/wall.php',
  'detail' => __DIR__.'/pages/detail.php',
  'request' => __DIR__.'/pages/request.php',
  'account' => __DIR__.'/pages/account.php',
  'login' => __DIR__.'/pages/login.php',
  'logout' => __DIR__.'/pages/logout.php',
];
if (!isset($map[$view])) { $view = 'wall'; }
echo "Debug: require ".$map[$view]."<br>";
require $map[$view];
echo "Debug: fin index.php<br>";
