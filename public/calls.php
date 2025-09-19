
<?php
session_start();
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/functions.php';

// Export to CSV (must be before any output)
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
  // Handle date filter for export
  $where = '';
  $params = [];
  if (!empty($_GET['from']) && !empty($_GET['to'])) {
    $where = 'WHERE created_at BETWEEN ? AND ?';
    $params[] = $_GET['from'] . ' 00:00:00';
    $params[] = $_GET['to'] . ' 23:59:59';
  }
  $pdo = $pdo ?? null;
  $calls = $pdo->prepare("SELECT * FROM calls $where ORDER BY created_at DESC");
  $calls->execute($params);
  $calls = $calls->fetchAll();
  header('Content-Type: text/csv');
  header('Content-Disposition: attachment; filename="calls_export.csv"');
  // Clean output buffer to prevent blank line
  if (ob_get_level()) ob_end_clean();
  $out = fopen('php://output', 'w');
  fputcsv($out, ['Name', 'Email', 'Phone', 'Notes', 'Created At']);
  foreach ($calls as $call) {
    fputcsv($out, [$call['name'], $call['email'], $call['phone'], $call['notes'], $call['created_at']]);
  }
  fclose($out);
  exit;
}

require_once __DIR__.'/partials/header.php';
require_login();
if (!is_admin()) { die('Forbidden'); }

// Handle new call submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'], $_POST['email'], $_POST['phone'], $_POST['notes'])) {
  $stmt = $pdo->prepare("INSERT INTO calls (name, email, phone, notes, created_at) VALUES (?, ?, ?, ?, NOW())");
  $stmt->execute([
    trim($_POST['name']),
    trim($_POST['email']),
    trim($_POST['phone']),
    trim($_POST['notes'])
  ]);
  $success = true;
}

// Handle date filter
$where = '';
$params = [];
if (!empty($_GET['from']) && !empty($_GET['to'])) {
  $where = 'WHERE created_at BETWEEN ? AND ?';
  $params[] = $_GET['from'] . ' 00:00:00';
  $params[] = $_GET['to'] . ' 23:59:59';
}

$calls = $pdo->prepare("SELECT * FROM calls $where ORDER BY created_at DESC");
$calls->execute($params);
$calls = $calls->fetchAll();
?>
<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Calls Management</h1>
  <a href="index.php?view=admin" class="btn btn-outline-primary ms-2" style="min-width:220px;">Prayer Request Management</a>
  </div>
  <div class="row">
    <div class="col-md-4">
      <div class="card mb-3">
        <div class="card-body">
          <h5 class="mb-3">Register New Call</h5>
          <?php if (!empty($success)): ?>
            <div class="alert alert-success">Call registered successfully.</div>
          <?php endif; ?>
          <form method="post">
            <div class="mb-3">
              <label class="form-label">Name</label>
              <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Phone</label>
              <input type="text" name="phone" class="form-control">
            </div>
            <div class="mb-3">
              <label class="form-label">Notes</label>
              <textarea name="notes" class="form-control" rows="3"></textarea>
            </div>
            <button class="btn btn-primary">Submit</button>
          </form>
        </div>
      </div>
    </div>
    <div class="col-md-8">
      <div class="card mb-3">
        <div class="card-body">
          <form class="row g-2 mb-3" method="get">
            <div class="col-auto">
              <label class="form-label">From</label>
              <input type="date" name="from" class="form-control" value="<?= e($_GET['from'] ?? '') ?>">
            </div>
            <div class="col-auto">
              <label class="form-label">To</label>
              <input type="date" name="to" class="form-control" value="<?= e($_GET['to'] ?? '') ?>">
            </div>
            <div class="col-auto align-self-end">
              <button class="btn btn-secondary" type="submit">Filter</button>
            </div>
            <div class="col-auto align-self-end">
              <a href="?export=csv<?= !empty($_GET['from']) ? '&from=' . urlencode($_GET['from']) : '' ?><?= !empty($_GET['to']) ? '&to=' . urlencode($_GET['to']) : '' ?>" class="btn btn-success">Export to CSV</a>
            </div>
          </form>
          <div style="max-height: 500px; overflow-y: auto;">
            <table class="table table-bordered table-sm">
              <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Notes</th><th>Created At</th></tr></thead>
              <tbody>
                <?php foreach ($calls as $call): ?>
                  <tr>
                    <td><?= e($call['name']) ?></td>
                    <td><?= e($call['email']) ?></td>
                    <td><?= e($call['phone']) ?></td>
                    <td><?= nl2br(e($call['notes'])) ?></td>
                    <td><?= e($call['created_at']) ?></td>
                  </tr>
                <?php endforeach; ?>
                <?php if (empty($calls)): ?>
                  <tr><td colspan="5" class="text-center text-muted">No calls found.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__.'/partials/footer.php'; ?>
