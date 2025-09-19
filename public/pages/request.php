<?php
require_once __DIR__.'/../../includes/db.php';
require_once __DIR__.'/../../includes/functions.php';
require_once __DIR__.'/../partials/header.php';
require_login();
?>
<h1 class="h4">Request Prayer</h1>
<form method="post" action="<?= e(base_url('pages_post.php')) ?>" class="card">
  <?= csrf_field() ?>
  <input type="hidden" name="do" value="create_request">
  <div class="card-body">
    <div class="mb-3">
      <label class="form-label">Share your request</label>
      <textarea maxlength="1000" class="form-control" name="content" rows="6" required></textarea>
      <div class="form-text">1000 characters max.</div>
    </div>
    <div class="form-check form-switch mb-3">
      <input class="form-check-input" type="checkbox" role="switch" id="anon" name="is_anonymous" value="1">
      <label class="form-check-label" for="anon">Post Anonymously</label>
    </div>
    <div class="alert alert-warning">
      <strong>Note:</strong> Prayers require moderator approval before appearing on the wall.
    </div>
    <button class="btn btn-teal">Request Prayer</button>
  </div>
</form>
<?php
require_once __DIR__.'/../partials/footer.php';
