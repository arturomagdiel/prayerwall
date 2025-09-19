<?php
require_once __DIR__.'/../../includes/db.php';
require_once __DIR__.'/../../includes/functions.php';
require_once __DIR__.'/../partials/header.php';

$id = (int)($_GET['id'] ?? 0);
$st = $pdo->prepare('SELECT r.*, u.first_name FROM prayer_requests r LEFT JOIN users u ON u.id=r.user_id WHERE r.id=? AND r.is_approved=1');
$st->execute([$id]);
$r = $st->fetch();
if (!$r) {
    echo '<div class="alert alert-warning">Prayer not found or not approved.</div>';
    require_once __DIR__.'/../partials/footer.php'; exit;
}
$stc = $pdo->prepare('SELECT * FROM prayer_comments WHERE request_id=? ORDER BY created_at ASC');
$stc->execute([$id]);
$comments = $stc->fetchAll();

$canned = $pdo->query('SELECT id, text FROM canned_comments ORDER BY id ASC')->fetchAll();
$link = base_url('index.php?view=detail&id='.$r['id']);
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <a class="btn btn-outline-secondary" href="<?= e(base_url('index.php')) ?>">Back</a>
  <div class="d-flex gap-2">
    <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#flagModal">Flag</button>
    <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#shareModal">Share</button>
  </div>
</div>

<div class="card card-prayer mb-3">
  <div class="card-body">
    <div class="prayer-text"><?= nl2br(e($r['content'])) ?></div>
    <div class="d-flex justify-content-between align-items-center mt-3">
      <div class="d-flex align-items-center gap-2">
        <form method="post" action="<?= e(base_url('pages_post.php')) ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="do" value="pray">
          <input type="hidden" name="request_id" value="<?= (int)$r['id'] ?>">
          <button class="btn btn-outline-primary">+ PRAY</button>
        </form>
        <span class="badge text-bg-light count-badge">🙏 <?= (int)$r['like_count'] ?></span>
      </div>
      <div class="text-muted small">
        <?= $r['is_anonymous']? 'Anonymous':'By '.e($r['first_name']?:'User') ?> • <?= e(date('M j, Y', strtotime($r['created_at']))) ?>
      </div>
    </div>
  </div>
</div>

<h2 class="h5">Comments</h2>
<?php foreach ($comments as $c): ?>
  <div class="border rounded p-2 mb-2">
    <?= nl2br(e($c['comment_text'])) ?>
    <div class="text-muted small"><?= e(date('M j, Y g:i a', strtotime($c['created_at']))) ?></div>
  </div>
<?php endforeach; ?>

<?php if (is_logged_in()): ?>
<div class="card mt-3">
  <div class="card-body">
    <form method="post" action="<?= e(base_url('pages_post.php')) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="do" value="add_comment">
      <input type="hidden" name="request_id" value="<?= (int)$r['id'] ?>">
      <div class="mb-2">
        <label class="form-label">Add a comment</label>
        <select class="form-select" name="canned_id" required>
          <option value="">Choose…</option>
          <?php foreach($canned as $opt): ?>
            <option value="<?= (int)$opt['id'] ?>"><?= e($opt['text']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button class="btn btn-teal">Add Comment</button>
    </form>
  </div>
</div>
<?php else: ?>
  <div class="alert alert-secondary mt-3">Please <a href="<?= e(base_url('index.php?view=login')) ?>">log in</a> to add a comment.</div>
<?php endif; ?>

<!-- Share Modal -->
<div class="modal fade" id="shareModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Share</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Copy this link:</p>
        <input class="form-control" value="<?= e($link) ?>" readonly>
      </div>
    </div>
  </div>
</div>

<!-- Flag Modal -->
<div class="modal fade" id="flagModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Choose a Flag</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form method="post" action="<?= e(base_url('pages_post.php')) ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="do" value="flag">
          <input type="hidden" name="request_id" value="<?= (int)$r['id'] ?>">
          <div class="mb-3">
            <select class="form-select" name="reason" required>
              <option value="">Select a reason</option>
              <option>Encourages violence or self-harm</option>
              <option>Hate speech against another group</option>
              <option>Sexually Explicit Content</option>
              <option>Sharing personally identifying information</option>
              <option>Spam or trying to sell something</option>
            </select>
          </div>
          <div class="d-flex justify-content-end gap-2">
            <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
            <button class="btn btn-danger">Add Flag</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php
require_once __DIR__.'/../partials/footer.php';
