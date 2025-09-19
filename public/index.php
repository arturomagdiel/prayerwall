<?php
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/functions.php';


session_start();
$view = $_GET['view'] ?? null;
if (!$view) {
  if (!empty($_SESSION['user']) && !empty($_SESSION['user']['is_admin'])) {
    $view = 'admin';
  } else {
    $view = 'wall';
  }
}

$map = [
  'wall' => __DIR__.'/pages/wall.php',
  'detail' => __DIR__.'/pages/detail.php',
  'request' => __DIR__.'/pages/request.php',
  'account' => __DIR__.'/pages/account.php',
  'login' => __DIR__.'/pages/login.php',
  'logout' => __DIR__.'/pages/logout.php',
  'register' => __DIR__.'/pages/register.php',
  'admin' => __DIR__.'/pages/admin.php',
];
if (!isset($map[$view])) { $view = 'wall'; }
require $map[$view];
