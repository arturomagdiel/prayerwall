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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="<?= e(base_url('assets/custom.css')) ?>" rel="stylesheet">
</head>
<body class="bg-light">
<main class="container my-4">
<!-- No success flash after login -->
<?php if ($m = flash('error')): ?>
  <div class="alert alert-danger"><?= e($m) ?></div>
<?php endif; ?>
