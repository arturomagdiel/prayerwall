<?php
require_once __DIR__.'/../../includes/db.php';
require_once __DIR__.'/../../includes/functions.php';
require_once __DIR__.'/../partials/header.php';

$filter = $_GET['filter'] ?? 'all';
$where = 'WHERE r.is_approved = 1';
$title = 'All Prayer Requests';
if ($filter === 'answered') { $where .= ' AND r.is_answered = 1'; $title='Answered Prayer Requests'; }
if ($filter === 'nopray') { $where .= ' AND r.like_count = 0'; $title='Requests With No Prayers'; }

$stmt = $pdo->query("SELECT r.*, u.first_name FROM prayer_requests r LEFT JOIN users u ON u.id=r.user_id $where ORDER BY r.created_at DESC LIMIT 50");
$rows = $stmt->fetchAll();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4 mb-0">Prayer Wall</h1>
  <div>
    <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#filterModal">Filter Prayers</button>
    <a class="btn btn-teal" href="<?= e(base_url('index.php?view=request')) ?>">🙏 Request Prayer</a>
  </div>
</div>

<div class="alert alert-info">
  <strong>Community Wall Message</strong><br>
  We ask that no last names are added, no specific locations, no personal contact information, and no hate speech or offensive language.
</div>

<?php foreach ($rows as $r): ?>
  <div class="card card-prayer mb-3">
    <div class="card-body">
      <div class="prayer-text">
        <?= nl2br(e(mb_strimwidth($r['content'], 0, 600, '...'))) ?>
      </div>
      <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="d-flex align-items-center gap-2">
          <form method="post" action="<?= e(base_url('pages_post.php')) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="do" value="pray">
            <input type="hidden" name="request_id" value="<?= (int)$r['id'] ?>">
            <button class="btn btn-sm btn-outline-primary">+ PRAY</button>
          </form>
          <span class="badge text-bg-light count-badge">🙏 <?= (int)$r['like_count'] ?></span>
          <span class="badge text-bg-light">💬 <?= (int)$r['comments_count'] ?></span>
        </div>
        <a class="btn btn-sm btn-outline-secondary" href="<?= e(base_url('index.php?view=detail&id='.$r['id'])) ?>">View</a>
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
<?php
require_once __DIR__.'/../partials/footer.php';
