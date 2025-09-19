<?php
session_start();
require_once __DIR__.'/../../includes/db.php';
require_once __DIR__.'/../../includes/functions.php';
if (!is_admin()) { http_response_code(403); exit('Forbidden'); }
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) exit('Invalid ID');
$st = $pdo->prepare('SELECT r.*, u.first_name, u.email FROM prayer_requests r LEFT JOIN users u ON u.id=r.user_id WHERE r.id=?');
$st->execute([$id]);
$r = $st->fetch();
if (!$r) exit('Request not found');
$stc = $pdo->prepare('SELECT c.*, u.first_name FROM prayer_comments c LEFT JOIN users u ON u.id = c.user_id WHERE c.request_id=? ORDER BY c.created_at ASC');
$stc->execute([$id]);
$comments = $stc->fetchAll();
?>
<div class="mb-2">
  <strong>Request:</strong><br>
  <div class="border rounded p-2 mb-2 bg-light"><?= nl2br(e($r['content'])) ?></div>
  <div class="mb-2 small text-muted">
    <span><strong>User:</strong> <?= e($r['first_name'] ?: 'User') ?> (<?= e($r['email']) ?>)</span> |
    <span><strong>Date:</strong> <?= e($r['created_at']) ?></span> |
    <span><strong>Status:</strong> <?= $r['is_approved'] ? 'Approved' : 'Pending' ?><?= $r['is_answered'] ? ' / Answered' : '' ?><?= $r['deleted'] ? ' / Deleted' : '' ?></span>
  </div>
</div>
<div class="mb-2">
  <strong>Comments:</strong>
  <?php if ($comments): ?>
    <?php foreach ($comments as $c): ?>
      <div class="border rounded p-2 mb-2">
        <?= nl2br(e($c['comment_text'])) ?>
        <div class="text-muted small">By <?= e($c['first_name'] ?: 'User') ?> • <?= e($c['created_at']) ?></div>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <div class="text-muted">No comments.</div>
  <?php endif; ?>
</div>
