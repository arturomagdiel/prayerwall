<?php
require_once __DIR__.'/../../config.php';
require_once __DIR__.'/../../includes/functions.php';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Prayer Wall</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= e(base_url('assets/custom.css')) ?>" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-teal">
  <div class="container">
    <a class="navbar-brand" href="<?= e(base_url('index.php')) ?>">Prayer Wall</a>
    <div class="ms-auto d-flex align-items-center gap-2">
      <?php if (is_logged_in()): ?>
        <span class="text-white small me-2">Hi, <?= e(current_user()['first_name'] ?: 'User') ?></span>
        <a class="btn btn-sm btn-outline-light" href="<?= e(base_url('index.php?view=request')) ?>">Request Prayer</a>
        <a class="btn btn-sm btn-outline-light" href="<?= e(base_url('index.php?view=account')) ?>">Account</a>
        <a class="btn btn-sm btn-warning" href="<?= e(base_url('index.php?view=logout')) ?>">Logout</a>
      <?php else: ?>
        <a class="btn btn-sm btn-outline-light" href="<?= e(base_url('index.php?view=login')) ?>">Login</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<main class="container my-4">
<?php if ($m = flash('success')): ?>
  <div class="alert alert-success"><?= e($m) ?></div>
<?php endif; ?>
<?php if ($m = flash('error')): ?>
  <div class="alert alert-danger"><?= e($m) ?></div>
<?php endif; ?>
