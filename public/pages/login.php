<?php
require_once __DIR__.'/../../includes/db.php';
require_once __DIR__.'/../../includes/functions.php';
require_once __DIR__.'/../partials/header.php';

if (is_logged_in()) { header('Location: '.base_url('index.php')); exit; }
?>
<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card">
      <div class="card-body">
        <h1 class="h4 mb-3">Log In</h1>
        <form method="post" action="<?= e(base_url('pages_post.php')) ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="do" value="login">
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
          </div>
          <button class="btn btn-teal w-100">Log In</button>
        </form>
        <hr>
        <p class="small text-muted">No account yet? The first login will create one.</p>
      </div>
    </div>
  </div>
</div>
<?php
require_once __DIR__.'/../partials/footer.php';
