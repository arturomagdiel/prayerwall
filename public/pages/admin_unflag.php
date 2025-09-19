<?php
session_start();
require_once __DIR__.'/../../includes/db.php';
require_once __DIR__.'/../../includes/functions.php';
header('Content-Type: application/json');
if (!is_admin()) { echo json_encode(['error'=>'Forbidden']); exit; }
$flag_id = (int)($_POST['flag_id'] ?? 0);
if ($flag_id <= 0) { echo json_encode(['error'=>'Invalid flag id']); exit; }
$st = $pdo->prepare('DELETE FROM prayer_flags WHERE id=?');
$st->execute([$flag_id]);
echo json_encode(['success'=>true]);
exit;
