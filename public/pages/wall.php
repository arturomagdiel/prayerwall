<?php

require_once __DIR__.'/../../includes/db.php';
require_once __DIR__.'/../../includes/functions.php';
require_once __DIR__.'/../partials/header.php';

$filter = $_GET['filter'] ?? 'all';
$where = 'WHERE r.is_approved = 1';
$title = 'All Prayer Requests';
if ($filter === 'answered') { $where .= ' AND r.is_answered = 1'; $title='Answered Prayer Requests'; }
if ($filter === 'nopray') { $where .= ' AND r.like_count = 0'; $title='Requests With No Prayers'; }

// Obtener los requests
$stmt = $pdo->query("SELECT r.*, u.first_name FROM prayer_requests r LEFT JOIN users u ON u.id=r.user_id $where ORDER BY r.created_at DESC LIMIT 50");
$rows = $stmt->fetchAll();

// Si está logueado, obtener los ids de requests ya orados por el usuario
$prayed_ids = [];
if (is_logged_in()) {
  $uid = current_user()['id'];
  $ids = array_column($rows, 'id');
  if ($ids) {
    $in = implode(',', array_map('intval', $ids));
    $q = $pdo->query("SELECT request_id FROM prayer_prays WHERE user_id = $uid AND request_id IN ($in)");
    $prayed_ids = array_column($q->fetchAll(), 'request_id');
  }
}
?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <div class="d-flex gap-2">
    <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#filterModal">Filter Prayers</button>
    <a class="btn btn-teal" href="<?= e(base_url('index.php?view=request')) ?>">🙏 Request Prayer</a>
  </div>
  <div class="d-flex gap-3 align-items-center">
    <a class="btn btn-link p-0 text-dark" href="<?= e(base_url('index.php')) ?>" title="Home">
      <i class="fa-solid fa-house fa-xl"></i>
    </a>
    <?php if (!is_admin()): ?>
    <a class="btn btn-link p-0 text-dark" href="<?= e(base_url('index.php?view=account')) ?>" title="Account">
      <i class="fa-solid fa-user-circle fa-xl"></i>
    </a>
    <?php endif; ?>
    <?php if (is_logged_in()): ?>
      <a class="btn btn-link p-0 text-danger" href="<?= e(base_url('index.php?view=logout')) ?>" title="Logout">
        <i class="fa-solid fa-right-from-bracket fa-xl"></i>
      </a>
      <span class="ms-2 small text-muted d-none d-md-inline">Hello, <?= e(current_user()['first_name'] ?: current_user()['email']) ?></span>
    <?php endif; ?>
  </div>
</div>

<?php
$msg = $pdo->query("SELECT `value` FROM app_settings WHERE `key`='community_wall_message'")->fetchColumn();
?>
<div class="alert alert-info">
  <strong>Community Wall Message</strong><br>
  <?= nl2br(e($msg)) ?>
</div>

<?php foreach ($rows as $r): ?>
  <div class="card mb-4 shadow-sm rounded-4">
    <div class="card-body pb-2">
      <?php if (!empty($r['is_answered'])): ?>
        <span class="badge bg-info text-dark mb-2" style="font-size:1rem;"><i class="fa-solid fa-party-horn me-1"></i>ANSWERED</span>
      <?php endif; ?>
      <div class="prayer-text mb-3"><?= nl2br(e(mb_strimwidth($r['content'], 0, 600, '...'))) ?></div>
      <div class="d-flex justify-content-between align-items-end">
        <div class="d-flex align-items-center gap-2">
          <?php if (is_logged_in()): ?>
            <?php $already = in_array($r['id'], $prayed_ids); ?>
            <?php if ($r['user_id'] != current_user()['id'] && !$r['is_answered']): ?>
              <button class="btn px-3 py-1 fw-semibold text-dark pray-btn<?= $already ? ' pray-btn-disabled' : '' ?>" style="background:#8fd3e8;border-radius:16px;min-width:90px;<?= $already ? 'opacity:0.6;pointer-events:none;cursor:not-allowed;border:2px solid #b5b5b5;' : '' ?>" data-id="<?= (int)$r['id'] ?>" <?= $already ? 'disabled' : '' ?>>
                <span class="me-1">➕</span> PRAY
              </button>
<style>
.pray-btn[disabled], .pray-btn-disabled {
  opacity: 0.6 !important;
  pointer-events: none !important;
  cursor: not-allowed !important;
  border: 2px solid #b5b5b5 !important;
  background: #e0e0e0 !important;
  color: #888 !important;
}
</style>
            <?php endif; ?>
          <?php else: ?>
            <?php if (!$r['is_answered']): ?>
              <a class="btn px-3 py-1 fw-semibold text-dark" style="background:#8fd3e8;border-radius:16px;min-width:90px;" href="<?= e(base_url('index.php?view=login&return=' . urlencode($_SERVER['REQUEST_URI']))) ?>">
                <span class="me-1">➕</span> PRAY
              </a>
            <?php endif; ?>
          <?php endif; ?>
          <span class="badge border bg-white text-dark d-flex align-items-center px-2 py-1 ms-2" style="border-radius:16px;font-size:1rem;">
            <span style="font-size:1.2em;line-height:1;">❤️🙏</span>
            <span class="ms-1 pray-count" id="pray-count-<?= (int)$r['id'] ?>"><?= (int)$r['like_count'] ?></span>
          </span>
          <span class="badge border d-flex align-items-center px-2 py-1 <?= $r['is_answered'] ? 'bg-light text-secondary' : 'bg-white text-dark' ?>" style="border-radius:16px;font-size:1rem;">
            <span style="font-size:1.2em;line-height:1;">💬</span>
            <span class="ms-1"><?= (int)$r['comments_count'] ?></span>
          </span>
        </div>
        <a class="btn px-3 py-1 fw-semibold text-dark ms-2" style="background:#8fd3e8;border-radius:16px;min-width:90px;" href="<?= e(base_url('index.php?view=detail&id='.$r['id'])) ?>">VIEW</a>
      </div>
      <div class="d-flex justify-content-end align-items-center mt-3">
        <div class="me-2 text-end">
          <div class="fw-bold small"><?= $r['is_anonymous'] ? 'Anonymous' : e($r['first_name'] ?: 'User') ?></div>
          <div class="text-muted small"><?= e(date('M j, Y', strtotime($r['created_at']))) ?> (<?= time_ago($r['created_at']) ?>)</div>
        </div>
        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center" style="width:38px;height:38px;">
          <i class="fa-solid fa-user fa-lg text-secondary"></i>
        </div>
      </div>
    </div>
  </div>
<?php endforeach; ?>

<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Filter Prayers</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form method="get">
          <div class="form-check">
            <input class="form-check-input" type="radio" name="filter" id="f1" value="all" <?= $filter==='all'?'checked':'' ?>>
            <label class="form-check-label" for="f1">All Prayer Requests</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="filter" id="f2" value="answered" <?= $filter==='answered'?'checked':'' ?>>
            <label class="form-check-label" for="f2">Answered Prayer Requests</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="filter" id="f3" value="nopray" <?= $filter==='nopray'?'checked':'' ?>>
            <label class="form-check-label" for="f3">Requests With No Prayers</label>
          </div>
          <div class="mt-3">
            <button class="btn btn-teal">Apply</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php if (is_logged_in()): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.pray-btn').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      if (btn.disabled) return;
      var id = btn.getAttribute('data-id');
      btn.disabled = true;
      fetch('pray_ajax.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + encodeURIComponent(id)
      })
      .then(r => r.json())
      .then(data => {
        if (data && data.success) {
          document.getElementById('pray-count-' + id).textContent = data.count;
        } else if(data && data.error) {
          alert(data.error);
        }
      });
    });
  });
});
</script>
<?php endif; ?>

<?php
require_once __DIR__.'/../partials/footer.php';
