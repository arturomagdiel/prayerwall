<?php
session_start();
// Check for any output before headers (BOM, whitespace, etc)
if(headers_sent($file, $line)) {
    file_put_contents(__DIR__.'/../../logs/pray_ajax_extra.log', date('Y-m-d H:i:s')."\nHeaders already sent at $file:$line\n", FILE_APPEND);
    // Try to send JSON anyway
    echo json_encode(['error'=>'Headers already sent']);
    exit;
}
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');
ini_set('display_errors', 0); // Oculta errores para evitar HTML
error_reporting(E_ALL);
set_exception_handler(function($e) {
  header('Content-Type: application/json');
  echo json_encode(['error'=>'Fatal error: '.$e->getMessage()]);
  exit;
});
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/functions.php';

if (!is_logged_in()) { echo json_encode(['error'=>'You must be logged in']); exit; }
$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) { echo json_encode(['error'=>'Invalid ID']); exit; }
$uid = current_user()['id'];
$st = $pdo->prepare('SELECT 1 FROM prayer_prays WHERE request_id=? AND user_id=?');
$st->execute([$id, $uid]);
if ($st->fetch()) {
    $st = $pdo->prepare('SELECT like_count FROM prayer_requests WHERE id=?');
    $st->execute([$id]);
    $count = $st->fetchColumn();
    echo json_encode(['success'=>true,'count'=>(int)$count]);
    exit;
}
$st = $pdo->prepare('INSERT INTO prayer_prays (request_id, user_id, ip_address, created_at) VALUES (?,?,?,NOW())');
try {
    $st->execute([$id, $uid, $_SERVER['REMOTE_ADDR'] ?? '']);
    $pdo->prepare('UPDATE prayer_requests SET like_count = like_count + 1 WHERE id=?')->execute([$id]);
} catch (PDOException $e) {
    echo json_encode(['error'=>'Database error: '.$e->getMessage()]);
    exit;
}
$st = $pdo->prepare('SELECT like_count FROM prayer_requests WHERE id=?');
$st->execute([$id]);
$count = $st->fetchColumn();
echo json_encode(['success'=>true,'count'=>(int)$count]);
exit;