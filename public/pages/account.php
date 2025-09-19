<?php
require_once __DIR__.'/../../includes/db.php';
require_once __DIR__.'/../../includes/functions.php';
require_once __DIR__.'/../partials/header.php';
require_login();

$u = current_user();

$cntReq = $pdo->prepare('SELECT COUNT(*) c FROM prayer_requests WHERE user_id=?');
$cntReq->execute([$u['id']]); $cntReq = (int)$cntReq->fetch()['c'];

$cntPray = $pdo->prepare('SELECT COUNT(*) c FROM prayer_prays WHERE user_id=?');
$cntPray->execute([$u['id']]); $cntPray = (int)$cntPray->fetch()['c'];

$cntCom = $pdo->prepare('SELECT COUNT(*) c FROM prayer_comments WHERE user_id=?');
$cntCom->execute([$u['id']]); $cntCom = (int)$cntCom->fetch()['c'];
?>
<ul class="nav nav-tabs" id="acctTabs" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#settings" type="button" role="tab">Account Settings</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#activity" type="button" role="tab">Your Activity</button>
  </li>
  <?php if(is_admin()): ?>
  <li class="nav-item" role="presentation">
    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#admin" type="button" role="tab">Admin</button>
  </li>
  <?php endif; ?>
</ul>
<div class="tab-content border border-top-0 p-3 bg-white">
  <div class="tab-pane fade show active" id="settings" role="tabpanel">
    <form method="post" action="<?= e(base_url('pages_post.php')) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="do" value="save_account">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">First Name</label>
          <input class="form-control" name="first_name" value="<?= e($u['first_name']) ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Email</label>
          <input class="form-control" value="<?= e($u['email']) ?>" disabled>
        </div>
        <div class="col-md-6">
          <label class="form-label">Zipcode</label>
          <input class="form-control" name="zipcode" value="<?= e($u['zipcode']) ?>">
        </div>
        <div class="col-md-6 d-flex align-items-center">
          <div class="form-check form-switch me-3">
            <input class="form-check-input" type="checkbox" name="include_location" value="1" <?= $u['include_location']?'checked':'' ?>>
            <label class="form-check-label">Include Location in Prayer Requests</label>
          </div>
        </div>
        <div class="col-12 d-flex gap-4">
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="enable_email_comm" value="1" <?= $u['enable_email_comm']?'checked':'' ?>>
            <label class="form-check-label">Enable Email Communication</label>
          </div>
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="enable_phone_comm" value="1" <?= $u['enable_phone_comm']?'checked':'' ?>>
            <label class="form-check-label">Enable Phone Communication</label>
          </div>
        </div>
      </div>
      <div class="mt-3">
        <button class="btn btn-teal">Save Account</button>
      </div>
    </form>
  </div>
  <div class="tab-pane fade" id="activity" role="tabpanel">
    <div class="row g-3">
      <div class="col-md-4">
        <a class="text-decoration-none" href="#">
          <div class="card text-center">
            <div class="card-body">
              <div class="display-6">📝</div>
              <div class="h5">Prayer Requests</div>
              <div class="h4 mb-0"><?= $cntReq ?></div>
            </div>
          </div>
        </a>
      </div>
      <div class="col-md-4">
        <a class="text-decoration-none" href="#">
          <div class="card text-center">
            <div class="card-body">
              <div class="display-6">🙏</div>
              <div class="h5">Prayers</div>
              <div class="h4 mb-0"><?= $cntPray ?></div>
            </div>
          </div>
        </a>
      </div>
      <div class="col-md-4">
        <a class="text-decoration-none" href="#">
          <div class="card text-center">
            <div class="card-body">
              <div class="display-6">💬</div>
              <div class="h5">Comments</div>
              <div class="h4 mb-0"><?= $cntCom ?></div>
            </div>
          </div>
        </a>
      </div>
    </div>
  </div>
  <?php if(is_admin()): ?>
  <div class="tab-pane fade" id="admin" role="tabpanel">
    <h5>Pending approvals</h5>
    <?php
      $ps = $pdo->query('SELECT r.*, u.first_name, u.email FROM prayer_requests r LEFT JOIN users u ON u.id=r.user_id WHERE r.is_approved=0 ORDER BY r.created_at DESC')->fetchAll();
      foreach($ps as $p):
    ?>
    <div class="card mb-2">
      <div class="card-body">
        <div class="small text-muted mb-2">By <?= e($p['first_name'] ?: $p['email']) ?> • <?= e($p['created_at']) ?></div>
        <div class="prayer-text"><?= nl2br(e($p['content'])) ?></div>
        <form method="post" action="<?= e(base_url('pages_post.php')) ?>" class="mt-2">
          <?= csrf_field() ?>
          <input type="hidden" name="do" value="approve">
          <input type="hidden" name="request_id" value="<?= (int)$p['id'] ?>">
          <div class="d-flex gap-2">
            <button class="btn btn-success btn-sm">Approve</button>
            <button formaction="<?= e(base_url('pages_post.php')) ?>" name="reject" value="1" class="btn btn-outline-danger btn-sm">Reject</button>
          </div>
        </form>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
<?php
require_once __DIR__.'/../partials/footer.php';
