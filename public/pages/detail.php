
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


$stc = $pdo->prepare('SELECT c.*, u.first_name FROM prayer_comments c LEFT JOIN users u ON u.id = c.user_id WHERE c.request_id=? ORDER BY c.created_at ASC');
$stc->execute([$id]);
$comments = $stc->fetchAll();


// Saber si el usuario ya oró por este request
$already_prayed = false;
$already_commented = false;
if (is_logged_in()) {
  $uid = current_user()['id'];
  $q = $pdo->prepare('SELECT 1 FROM prayer_prays WHERE user_id=? AND request_id=?');
  $q->execute([$uid, $id]);
  $already_prayed = (bool)$q->fetch();
  // Verificar si ya comentó
  $qc = $pdo->prepare('SELECT 1 FROM prayer_comments WHERE user_id=? AND request_id=?');
  $qc->execute([$uid, $id]);
  $already_commented = (bool)$qc->fetch();
}

$canned = $pdo->query('SELECT id, text FROM canned_comments ORDER BY id ASC')->fetchAll();
$link = base_url('index.php?view=detail&id='.$r['id']);
?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <div>
    <a class="btn btn-outline-secondary d-flex align-items-center gap-2" href="<?= e(base_url('index.php')) ?>">
      <i class="fa-solid fa-arrow-left"></i> BACK
    </a>
  </div>
  <div class="d-flex gap-3 align-items-center">
    <a class="btn btn-link p-0 text-dark" href="<?= e(base_url('index.php')) ?>" title="Home">
      <i class="fa-solid fa-house fa-xl"></i>
    </a>
    <a class="btn btn-link p-0 text-dark" href="<?= e(base_url('index.php?view=account')) ?>" title="Account">
      <i class="fa-solid fa-user-circle fa-xl"></i>
    </a>
    <?php if (is_logged_in()): ?>
      <a class="btn btn-link p-0 text-danger" href="<?= e(base_url('index.php?view=logout')) ?>" title="Logout">
        <i class="fa-solid fa-right-from-bracket fa-xl"></i>
      </a>
    <?php endif; ?>
  </div>
</div>

<div class="card card-prayer mb-3">
  <div class="card-body">
    <?php if ($r['is_answered']): ?>
      <span class="badge bg-info text-dark mb-2" style="font-size:1rem;"><i class="fa-solid fa-party-horn me-1"></i>ANSWERED</span>
    <?php endif; ?>
    <div class="prayer-text"><?= nl2br(e($r['content'])) ?></div>
    <div class="d-flex justify-content-between align-items-center mt-3">
      <div class="d-flex align-items-center gap-2">


        <?php if (is_logged_in()): ?>
          <?php if ($r['user_id'] != current_user()['id'] && !$r['is_answered']): ?>
            <button class="btn px-3 py-1 fw-semibold text-dark pray-btn<?= $already_prayed ? ' pray-btn-disabled' : '' ?>" style="background:#8fd3e8;border-radius:16px;min-width:90px;<?= $already_prayed ? 'opacity:0.6;pointer-events:none;cursor:not-allowed;border:2px solid #b5b5b5;' : '' ?>" data-id="<?= (int)$r['id'] ?>" <?= $already_prayed ? 'disabled' : '' ?>>
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
.flag-share-btn {
  min-width: 110px;
  font-weight: 500;
  border-radius: 16px;
  margin-right: 8px;
  margin-bottom: 8px;
  box-shadow: 0 1px 4px rgba(0,0,0,0.04);
  transition: background 0.2s, color 0.2s;
}
.flag-share-btn.flag {
  background: #ffeaea;
  color: #c00;
  border: 2px solid #ffb3b3;
}
.flag-share-btn.flag:hover {
  background: #ffd6d6;
  color: #a00;
}
.flag-share-btn.share {
  background: #eaf6ff;
  color: #007bff;
  border: 2px solid #b3d8ff;
}
.flag-share-btn.share:hover {
  background: #d6eaff;
  color: #0056b3;
}
</style>
          <?php endif; ?>
        <?php else: ?>
          <?php if (!$r['is_answered']): ?>
            <a class="btn btn-outline-primary" href="<?= e(base_url('index.php?view=login&return=' . urlencode($_SERVER['REQUEST_URI']))) ?>">+ PRAY</a>
          <?php endif; ?>
        <?php endif; ?>

          <span class="badge border d-flex align-items-center px-2 py-1 <?= $r['is_answered'] ? 'bg-light text-secondary' : 'bg-white text-dark' ?>" id="pray-count-<?= (int)$r['id'] ?>" style="border-radius:16px;font-size:1rem;">
            <span style="font-size:1.2em;line-height:1;">❤️🙏</span>
            <span class="ms-1"><?= (int)$r['like_count'] ?></span>
          </span>
          <span class="badge border d-flex align-items-center px-2 py-1 <?= $r['is_answered'] ? 'bg-light text-secondary' : 'bg-white text-dark' ?>" style="border-radius:16px;font-size:1rem;">
            <span style="font-size:1.2em;line-height:1;">💬</span>
            <span class="ms-1"><?= isset($r['comments_count']) ? (int)$r['comments_count'] : count($comments) ?></span>
          </span>
      </div>
      <div class="text-muted small">
        <?= $r['is_anonymous']? 'Anonymous':'By '.e($r['first_name']?:'User') ?> • <?= e(date('M j, Y', strtotime($r['created_at']))) ?>
      </div>
    </div>
  </div>
</div>


<div class="d-flex gap-2 mb-3 align-items-center justify-content-between">
  <div>
    <?php
      $is_own_request = is_logged_in() && $r['user_id'] == current_user()['id'];
      if (!$is_own_request):
        if (is_logged_in()): ?>
          <button class="btn btn-danger d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#flagModal">
            <i class="fa-solid fa-flag"></i> Flag
          </button>
        <?php else: ?>
          <a class="btn btn-danger d-flex align-items-center gap-2" href="<?= e(base_url('index.php?view=login&return=' . urlencode($_SERVER['REQUEST_URI']))) ?>">
            <i class="fa-solid fa-flag"></i> Flag
          </a>
        <?php endif;
      endif;
    ?>
  </div>
  <div class="d-flex gap-2 align-items-center ms-auto">
    <button id="share-btn" class="btn btn-outline-secondary d-flex align-items-center gap-2" type="button">
      <i class="fa-solid fa-share"></i> Share
    </button>
    <?php if ($is_own_request && !$r['is_answered']): ?>
      <button class="btn btn-success d-flex align-items-center gap-2" id="answered-btn" type="button">
        <i class="fa-solid fa-check"></i> Answered
      </button>
    <?php endif; ?>
  </div>
</div>

<!-- Answered Confirmation Modal -->
<div class="modal fade" id="answeredModal" tabindex="-1" aria-labelledby="answeredModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="answeredModalLabel">Mark as Answered</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>If you mark this request as answered, you will no longer receive prayers or comments for it. Are you sure you want to continue?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <form method="post" action="<?= e(base_url('pages_post.php')) ?>" class="d-inline">
          <?= csrf_field() ?>
          <input type="hidden" name="do" value="mark_answered">
          <input type="hidden" name="request_id" value="<?= (int)$r['id'] ?>">
          <button class="btn btn-success" type="submit">Yes, mark as answered</button>
        </form>
      </div>
    </div>
  </div>
</div>

<div id="share-alert" class="alert alert-success d-none" role="alert" style="position:fixed;top:80px;right:20px;z-index:9999;min-width:220px;">
  <i class="fa-solid fa-check-circle me-2"></i>Link successfully copied to clipboard.
</div>
</div>


<h2 class="h5">Comments</h2>

<?php foreach ($comments as $c): ?>
  <div class="border rounded p-2 mb-2">
    <?= nl2br(e($c['comment_text'])) ?>
    <div class="text-muted small">
      <?php if (!empty($c['first_name'])): ?>
        <strong><?= e($c['first_name']) ?></strong>
      <?php else: ?>
        <strong>Anonymous</strong>
      <?php endif; ?>
      &middot; <?= e(time_ago($c['created_at'])) ?>
    </div>
  </div>
<?php endforeach; ?>

<?php if (is_logged_in() && !$r['is_answered']): ?>
  <?php if (!$is_own_request && !$already_commented): ?>
    <form method="post" action="<?= e(base_url('pages_post.php')) ?>" class="mb-3">
      <?= csrf_field() ?>
      <input type="hidden" name="do" value="add_comment">
      <input type="hidden" name="request_id" value="<?= (int)$r['id'] ?>">
      <div class="mb-2">
        <label for="canned" class="form-label">Quick comment</label>
        <select id="canned" class="form-select mb-2" name="canned_id" required>
          <option value="">Select a quick comment...</option>
          <?php foreach ($canned as $can): ?>
            <option value="<?= (int)$can['id'] ?>"><?= e($can['text']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="d-flex justify-content-end">
        <button class="btn btn-primary">Add comment</button>
      </div>
    </form>
  <?php endif; ?>
<?php elseif (!is_logged_in() && !$r['is_answered']): ?>
  <div class="alert alert-info">You must <a href="<?= e(base_url('index.php?view=login&return=' . urlencode($_SERVER['REQUEST_URI']))) ?>">log in</a> to comment.</div>
<?php endif; ?>
<?php if (is_logged_in() && $is_own_request): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
  var answeredBtn = document.getElementById('answered-btn');
  if (answeredBtn) {
    answeredBtn.addEventListener('click', function() {
      var modal = new bootstrap.Modal(document.getElementById('answeredModal'));
      modal.show();
    });
  }
});
</script>
<?php endif; ?>

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

<?php if (is_logged_in()): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
  var btn = document.querySelector('.pray-btn');
  if (btn) {
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
          document.getElementById('pray-count-' + id).textContent = '🙏 ' + data.count;
        } else if(data && data.error) {
          alert(data.error);
        }
      });
    });
  }

  var shareBtn = document.getElementById('share-btn');
  var shareAlert = document.getElementById('share-alert');
  if (shareBtn) {
    shareBtn.addEventListener('click', function() {
      var link = "<?= e($link) ?>";
      navigator.clipboard.writeText(link).then(function() {
        shareAlert.classList.remove('d-none');
        setTimeout(function() {
          shareAlert.classList.add('d-none');
        }, 2000);
      });
    });
  }
});
</script>
<?php endif; ?>

<?php
require_once __DIR__.'/../partials/footer.php';
?>