<?php
session_start();
require_once __DIR__.'/../../includes/db.php';
require_once __DIR__.'/../../includes/functions.php';
header('Content-Type: application/json');
if (!is_admin()) { echo json_encode(['error'=>'Forbidden']); exit; }
$action = $_POST['action'] ?? '';
$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) { echo json_encode(['error'=>'Invalid user id']); exit; }
if ($action === 'ban') {
    $pdo->prepare('UPDATE users SET banned=1 WHERE id=?')->execute([$id]);
    echo json_encode(['success'=>true]); exit;
} elseif ($action === 'unban') {
    $pdo->prepare('UPDATE users SET banned=0 WHERE id=?')->execute([$id]);
    echo json_encode(['success'=>true]); exit;
} elseif ($action === 'edit') {
    $email = trim($_POST['email'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $zipcode = trim($_POST['zipcode'] ?? '');
    $include_location = !empty($_POST['include_location']) ? 1 : 0;
    $enable_email_comm = !empty($_POST['enable_email_comm']) ? 1 : 0;
    $enable_phone_comm = !empty($_POST['enable_phone_comm']) ? 1 : 0;
    $is_admin = !empty($_POST['is_admin']) ? 1 : 0;
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { echo json_encode(['error'=>'Invalid email']); exit; }
    if ($name === '') { echo json_encode(['error'=>'Name required']); exit; }
    $pdo->prepare('UPDATE users SET email=?, first_name=?, phone=?, zipcode=?, include_location=?, enable_email_comm=?, enable_phone_comm=?, is_admin=? WHERE id=?')
        ->execute([$email, $name, $phone, $zipcode, $include_location, $enable_email_comm, $enable_phone_comm, $is_admin, $id]);
    echo json_encode(['success'=>true]); exit;
}
echo json_encode(['error'=>'Unknown action']); exit;
