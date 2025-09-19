<?php
session_start();
require_once __DIR__.'/../../includes/db.php';
require_once __DIR__.'/../../includes/functions.php';
header('Content-Type: application/json');
if (!is_admin()) { echo json_encode(['error'=>'Forbidden']); exit; }

$action = $_POST['action'] ?? '';
$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) { echo json_encode(['error'=>'Invalid ID']); exit; }

switch ($action) {
    case 'approve':
        $pdo->prepare('UPDATE prayer_requests SET is_approved=1 WHERE id=?')->execute([$id]);
    echo json_encode(['success'=>true]);
    exit;
        break;
    case 'disapprove':
        $pdo->prepare('UPDATE prayer_requests SET is_approved=0 WHERE id=?')->execute([$id]);
        $pdo->prepare('DELETE FROM prayer_flags WHERE request_id=?')->execute([$id]);
        echo json_encode(['success'=>true]);
        exit;
        break;
    case 'mark_answered':
        $pdo->prepare('UPDATE prayer_requests SET is_answered=1 WHERE id=?')->execute([$id]);
    echo json_encode(['success'=>true]);
    exit;
        break;
    case 'unmark_answered':
        $pdo->prepare('UPDATE prayer_requests SET is_answered=0 WHERE id=?')->execute([$id]);
    echo json_encode(['success'=>true]);
    exit;
        break;
    case 'delete':
        $pdo->prepare('UPDATE prayer_requests SET deleted=1 WHERE id=?')->execute([$id]);
        $pdo->prepare('DELETE FROM prayer_flags WHERE request_id=?')->execute([$id]);
        echo json_encode(['success'=>true]);
        exit;
        break;
    case 'restore':
        $pdo->prepare('UPDATE prayer_requests SET deleted=0 WHERE id=?')->execute([$id]);
    echo json_encode(['success'=>true]);
    exit;
        break;
    case 'ban':
        $pdo->prepare('UPDATE users SET banned=1 WHERE id=?')->execute([$id]);
    echo json_encode(['success'=>true]);
    exit;
        break;
    case 'unban':
        $pdo->prepare('UPDATE users SET banned=0 WHERE id=?')->execute([$id]);
    echo json_encode(['success'=>true]);
    exit;
        break;
    default:
    echo json_encode(['error'=>'Unknown action']);
    exit;
}
