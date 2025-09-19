<?php
require_once __DIR__.'/../../includes/db.php';
require_once __DIR__.'/../../includes/functions.php';
require_once __DIR__.'/../partials/header.php';
require_login();
if (!is_admin()) { die('Forbidden'); }

$tab = $_GET['tab'] ?? 'pending';
// Add settings tab
// Add settings tab only
$tabs = ['pending','approved','answered','deleted','users','flags','settings'];
if (!in_array($tab, $tabs)) $tab = 'pending';
// Handle settings update
if ($tab === 'settings' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['community_wall_message'])) {
  $msg = trim($_POST['community_wall_message']);
  $st = $pdo->prepare("INSERT INTO app_settings (`key`, `value`) VALUES ('community_wall_message', ?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)");
  $st->execute([$msg]);
  $success = true;
}
// Load current message
if ($tab === 'settings') {
  $msg = $pdo->query("SELECT `value` FROM app_settings WHERE `key`='community_wall_message'")->fetchColumn();
}


// Requests queries
if ($tab === 'pending') {
  $requests = $pdo->query("SELECT r.*, u.first_name, u.email FROM prayer_requests r LEFT JOIN users u ON u.id=r.user_id WHERE r.is_approved=0 AND r.deleted!=1 ORDER BY r.created_at DESC")->fetchAll();
} elseif ($tab === 'approved') {
  $requests = $pdo->query("SELECT r.*, u.first_name, u.email FROM prayer_requests r LEFT JOIN users u ON u.id=r.user_id WHERE r.is_approved=1 AND r.deleted!=1 ORDER BY r.created_at DESC")->fetchAll();
} elseif ($tab === 'answered') {
  $requests = $pdo->query("SELECT r.*, u.first_name, u.email FROM prayer_requests r LEFT JOIN users u ON u.id=r.user_id WHERE r.is_answered=1 AND r.deleted!=1 ORDER BY r.created_at DESC")->fetchAll();
} elseif ($tab === 'deleted') {
  $requests = $pdo->query("SELECT r.*, u.first_name, u.email FROM prayer_requests r LEFT JOIN users u ON u.id=r.user_id WHERE r.deleted=1 ORDER BY r.created_at DESC")->fetchAll();
} elseif ($tab === 'flags') {
  $requests = $pdo->query("SELECT f.*, r.content, r.id as request_id, u.email FROM prayer_flags f LEFT JOIN prayer_requests r ON r.id=f.request_id LEFT JOIN users u ON u.id=f.user_id ORDER BY f.created_at DESC")->fetchAll();
}

// Contar flags y pendientes para resaltar los tabs
$flag_count = $pdo->query("SELECT COUNT(*) FROM prayer_flags")->fetchColumn();
$pending_count = $pdo->query("SELECT COUNT(*) FROM prayer_requests WHERE is_approved=0 AND deleted!=1")->fetchColumn();

// Users query
if ($tab === 'users') {
  $users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
}
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4 mb-0">Prayer Requests Management</h1>
  <a href="/prayerwall/public/calls.php" class="btn btn-outline-primary ms-2" style="min-width:180px;">Calls Management</a>
</div>
<ul class="nav nav-tabs mb-3">
  <li class="nav-item">
    <a class="nav-link<?= $tab==='pending'?' active':'' ?><?= $pending_count > 0 ? ' bg-warning text-dark' : '' ?>" href="?tab=pending">
      Pending Requests<?= $pending_count > 0 ? ' <span class=\'badge bg-light text-dark\'>' . $pending_count . '</span>' : '' ?>
    </a>
  </li>
  <li class="nav-item"><a class="nav-link<?= $tab==='approved'?' active':'' ?>" href="?tab=approved">Approved Requests</a></li>
  <li class="nav-item"><a class="nav-link<?= $tab==='answered'?' active':'' ?>" href="?tab=answered">Answered</a></li>
  <li class="nav-item"><a class="nav-link<?= $tab==='deleted'?' active':'' ?>" href="?tab=deleted">Deleted</a></li>
  <li class="nav-item">
    <a class="nav-link<?= $tab==='flags'?' active':'' ?><?= $flag_count > 0 ? ' bg-danger text-white' : '' ?>" href="?tab=flags">
      Flagged<?= $flag_count > 0 ? ' <span class=\'badge bg-light text-danger\'>' . $flag_count . '</span>' : '' ?>
    </a>
  </li>
  <li class="nav-item"><a class="nav-link<?= $tab==='users'?' active':'' ?>" href="?tab=users">Users</a></li>
  <li class="nav-item"><a class="nav-link<?= $tab==='settings'?' active':'' ?>" href="?tab=settings">Settings</a></li>

</ul>

<?php if ($tab === 'settings'): ?>

  <div class="card mb-3"><div class="card-body">
    <h5 class="mb-3">Community Wall Message</h5>
    <?php if (!empty($success)): ?>
      <div class="alert alert-success">Message updated successfully.</div>
    <?php endif; ?>
    <form method="post">
      <div class="mb-3">
        <textarea name="community_wall_message" class="form-control" rows="3" required><?= e($msg) ?></textarea>
      </div>
      <button class="btn btn-primary">Save Message</button>
    </form>
  </div></div>
<?php elseif ($tab === 'users'): ?>
  <div class="mb-3 text-end">
    <a href="?tab=users&action=create" class="btn btn-primary btn-sm">Create User</a>
  </div>
  <table class="table table-bordered table-sm">
    <thead><tr><th>Email</th><th>Name</th><th>Admin</th><th>Banned</th><th>Created</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($users as $u): ?>
        <tr>
          <td<?= !empty($u['banned']) ? ' class="text-danger"' : '' ?>><?= e($u['email']) ?></td>
          <td<?= !empty($u['banned']) ? ' class="text-danger"' : '' ?>><?= e($u['first_name']) ?></td>
          <td<?= !empty($u['banned']) ? ' class="text-danger"' : '' ?>><?= $u['is_admin'] ? 'Yes' : 'No' ?></td>
          <td<?= !empty($u['banned']) ? ' class="text-danger"' : '' ?>><?= !empty($u['banned']) ? 'Yes' : 'No' ?></td>
          <td<?= !empty($u['banned']) ? ' class="text-danger"' : '' ?>><?= e($u['created_at']) ?></td>
          <td>
            <button class="btn btn-sm btn-outline-secondary user-edit-btn" 
              data-id="<?= (int)$u['id'] ?>"
              data-email="<?= e($u['email']) ?>"
              data-name="<?= e($u['first_name']) ?>"
              data-phone="<?= e($u['phone']) ?>"
              data-zipcode="<?= e($u['zipcode']) ?>"
              data-include_location="<?= (int)$u['include_location'] ?>"
              data-enable_email_comm="<?= (int)$u['enable_email_comm'] ?>"
              data-enable_phone_comm="<?= (int)$u['enable_phone_comm'] ?>"
              data-is_admin="<?= (int)$u['is_admin'] ?>"
            >Edit</button>
            <?php if ((int)$u['id'] !== 1): ?>
              <?php if (empty($u['banned'])): ?>
                <button class="btn btn-sm btn-outline-danger user-ban-btn" data-id="<?= (int)$u['id'] ?>">Ban</button>
              <?php else: ?>
                <button class="btn btn-sm btn-success user-unban-btn" data-id="<?= (int)$u['id'] ?>">Unban</button>
              <?php endif; ?>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <!-- User edit modal -->
  <div class="modal fade" id="userEditModal" tabindex="-1" aria-labelledby="userEditModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="userEditModalLabel">Edit User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="userEditForm">
          <div class="modal-body">
            <input type="hidden" name="id" id="userEditId">
            <div class="mb-3">
              <label for="userEditEmail" class="form-label">Email</label>
              <input type="email" class="form-control" name="email" id="userEditEmail" required>
            </div>
            <div class="mb-3">
              <label for="userEditName" class="form-label">Name</label>
              <input type="text" class="form-control" name="name" id="userEditName" required>
            </div>
            <div class="mb-3">
              <label for="userEditPhone" class="form-label">Phone</label>
              <input type="text" class="form-control" name="phone" id="userEditPhone">
            </div>
            <div class="mb-3">
              <label for="userEditZipcode" class="form-label">Zipcode</label>
              <input type="text" class="form-control" name="zipcode" id="userEditZipcode">
            </div>
            <div class="form-check mb-2">
              <input class="form-check-input" type="checkbox" name="include_location" id="userEditIncludeLocation" value="1">
              <label class="form-check-label" for="userEditIncludeLocation">Include Location</label>
            </div>
            <div class="form-check mb-2">
              <input class="form-check-input" type="checkbox" name="enable_email_comm" id="userEditEnableEmailComm" value="1">
              <label class="form-check-label" for="userEditEnableEmailComm">Enable Email Communication</label>
            </div>
            <div class="form-check mb-2">
              <input class="form-check-input" type="checkbox" name="enable_phone_comm" id="userEditEnablePhoneComm" value="1">
              <label class="form-check-label" for="userEditEnablePhoneComm">Enable Phone Communication</label>
            </div>
            <div class="form-check mb-2">
              <input class="form-check-input" type="checkbox" name="is_admin" id="userEditIsAdmin" value="1">
              <label class="form-check-label" for="userEditIsAdmin">Is Admin</label>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>

<?php else: ?>
  <?php if ($tab === 'flags'): ?>
    <table class="table table-bordered table-sm">
      <thead><tr><th>Reason</th><th>Request</th><th>Flagged By</th><th>Date</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($requests as $r): ?>
          <tr>
            <td><?= e($r['reason'] ?? '(no reason)') ?></td>
            <td><?= nl2br(e($r['content'])) ?></td>
            <td><?= e($r['email']) ?></td>
            <td><?= e($r['created_at']) ?></td>
            <td>
              <button class="btn btn-sm btn-outline-primary view-detail-btn" data-id="<?= (int)($r['request_id']) ?>">View</button>
              <button class="btn btn-sm btn-warning admin-action" data-action="disapprove" data-id="<?= (int)$r['request_id'] ?>">Disapprove</button>
              <button class="btn btn-sm btn-outline-danger admin-action" data-action="delete" data-id="<?= (int)$r['request_id'] ?>">Delete</button>
              <button class="btn btn-sm btn-secondary admin-unflag-btn" data-flag-id="<?= (int)$r['id'] ?>">Unflag</button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <table class="table table-bordered table-sm">
      <thead><tr><th>Request</th><th>User</th><th>Status</th><th>Prayers</th><th>Comments</th><th>Date</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($requests as $r): ?>
          <tr>
            <td><?= nl2br(e($r['content'] ?? $r['reason'])) ?></td>
            <td><?= e($r['first_name'] ?? $r['email']) ?></td>
            <td>
              <?= !empty($r['is_approved']) ? 'Approved' : 'Pending' ?>
              <?= !empty($r['is_answered']) ? '<span class="badge bg-info text-dark ms-1">Answered</span>' : '' ?>
              <?= !empty($r['deleted']) ? '<span class="badge bg-danger ms-1">Deleted</span>' : '' ?>
            </td>
            <td><?= (int)($r['like_count'] ?? 0) ?></td>
            <td><?= (int)($r['comments_count'] ?? 0) ?></td>
            <td><?= e($r['created_at']) ?></td>
            <td>
              <button class="btn btn-sm btn-outline-primary view-detail-btn" data-id="<?= (int)($r['id'] ?? $r['request_id']) ?>">View</button>
              <?php if (empty($r['is_approved'])): ?>
                <button class="btn btn-sm btn-success admin-action" data-action="approve" data-id="<?= (int)$r['id'] ?>">Approve</button>
              <?php else: ?>
                <button class="btn btn-sm btn-warning admin-action" data-action="disapprove" data-id="<?= (int)$r['id'] ?>">Disapprove</button>
              <?php endif; ?>
              <?php if (empty($r['is_answered'])): ?>
                <button class="btn btn-sm btn-outline-info admin-action" data-action="mark_answered" data-id="<?= (int)$r['id'] ?>">Mark Answered</button>
              <?php else: ?>
                <button class="btn btn-sm btn-outline-secondary admin-action" data-action="unmark_answered" data-id="<?= (int)$r['id'] ?>">Unmark Answered</button>
              <?php endif; ?>
              <?php if (empty($r['deleted'])): ?>
                <button class="btn btn-sm btn-outline-danger admin-action" data-action="delete" data-id="<?= (int)$r['id'] ?>">Delete</button>
              <?php else: ?>
                <button class="btn btn-sm btn-success admin-action" data-action="restore" data-id="<?= (int)$r['id'] ?>">Restore</button>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
<?php endif; ?>

<!-- Admin Action Confirmation Modal -->
<div class="modal fade" id="adminActionModal" tabindex="-1" aria-labelledby="adminActionModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="adminActionModalLabel">Confirm Action</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="adminActionModalBody">
        Are you sure you want to perform this action?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="adminActionConfirmBtn">Yes, continue</button>
      </div>
    </div>
  </div>
</div>
<!-- Modal para detalle de request -->
<div class="modal fade" id="requestDetailModal" tabindex="-1" aria-labelledby="requestDetailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="requestDetailModalLabel">Request Detail</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="requestDetailModalBody">
        <div class="text-center text-muted">Loading...</div>
      </div>
    </div>
  </div>
</div>
<script>
let adminAction = null, adminId = null, adminBtn = null;
const actionMessages = {
  approve: 'Approve this request?',
  disapprove: 'Disapprove this request?',
  mark_answered: 'Mark this request as answered? No more prayers or comments will be allowed.',
  unmark_answered: 'Unmark this request as answered?',
  delete: 'Delete this request? It will be hidden from users.',
  restore: 'Restore this request?',
  ban: 'Ban this user? They will not be able to log in.',
  unban: 'Unban this user?'
};
document.addEventListener('DOMContentLoaded', function() {
  // User edit handler
  const userEditModalEl = document.getElementById('userEditModal');
  let userEditModalInstance = null;
  document.body.addEventListener('click', function(e) {
    if (e.target.classList.contains('user-edit-btn')) {
      document.getElementById('userEditId').value = e.target.getAttribute('data-id');
      document.getElementById('userEditEmail').value = e.target.getAttribute('data-email');
      document.getElementById('userEditName').value = e.target.getAttribute('data-name');
      document.getElementById('userEditPhone').value = e.target.getAttribute('data-phone') || '';
      document.getElementById('userEditZipcode').value = e.target.getAttribute('data-zipcode') || '';
      document.getElementById('userEditIncludeLocation').checked = e.target.getAttribute('data-include_location') == '1';
      document.getElementById('userEditEnableEmailComm').checked = e.target.getAttribute('data-enable_email_comm') == '1';
      document.getElementById('userEditEnablePhoneComm').checked = e.target.getAttribute('data-enable_phone_comm') == '1';
      document.getElementById('userEditIsAdmin').checked = e.target.getAttribute('data-is_admin') == '1';
      if (userEditModalInstance) userEditModalInstance.hide();
      userEditModalInstance = new bootstrap.Modal(userEditModalEl);
      userEditModalInstance.show();
    }
    if (e.target.classList.contains('user-ban-btn')) {
      if (!confirm('Ban this user?')) return;
      fetch('pages/admin_user_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=ban&id=' + encodeURIComponent(e.target.getAttribute('data-id'))
      })
      .then(r => r.json())
      .then(data => { if (data && data.success) location.reload(); else alert(data.error||'Error'); })
      .catch(() => alert('Network error'));
    }
    if (e.target.classList.contains('user-unban-btn')) {
      if (!confirm('Unban this user?')) return;
      fetch('pages/admin_user_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=unban&id=' + encodeURIComponent(e.target.getAttribute('data-id'))
      })
      .then(r => r.json())
      .then(data => { if (data && data.success) location.reload(); else alert(data.error||'Error'); })
      .catch(() => alert('Network error'));
    }
  });

  var userEditForm = document.getElementById('userEditForm');
  if (userEditForm) {
    userEditForm.addEventListener('submit', function(e) {
      e.preventDefault();
      const form = e.target;
      const data = new URLSearchParams(new FormData(form)).toString();
      fetch('pages/admin_user_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=edit&' + data
      })
      .then(r => r.json())
      .then(data => {
        if (data && data.success) location.reload();
        else alert(data.error||'Error');
      })
      .catch(() => alert('Network error'));
    });
  }
  document.body.addEventListener('click', function(e) {
    if (e.target.classList.contains('admin-unflag-btn')) {
      var flagId = e.target.getAttribute('data-flag-id');
      if (!confirm('Remove this flag?')) return;
      fetch('pages/admin_unflag.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'flag_id=' + encodeURIComponent(flagId)
      })
      .then(r => r.json())
      .then(data => {
        if (data && data.success) location.reload();
        else alert(data.error || 'Error');
      })
      .catch(() => alert('Network error'));
    }
  });
  // Delegar para soportar View en tablas recargadas (solo un manejador)
  const requestDetailModalEl = document.getElementById('requestDetailModal');
  let requestDetailModalInstance = null;
  document.body.addEventListener('click', function(e) {
    if (e.target.classList.contains('view-detail-btn')) {
      var id = e.target.getAttribute('data-id');
      var body = document.getElementById('requestDetailModalBody');
      body.innerHTML = '<div class="text-center text-muted">Loading...</div>';
      // Cerrar instancia previa si existe
      if (requestDetailModalInstance) {
        requestDetailModalInstance.hide();
      }
      requestDetailModalInstance = new bootstrap.Modal(requestDetailModalEl);
      requestDetailModalInstance.show();
      fetch('pages/admin_request_detail.php?id=' + encodeURIComponent(id))
        .then(r => r.text())
        .then(html => { body.innerHTML = html; })
        .catch(() => { body.innerHTML = '<div class="text-danger">Error loading detail.</div>'; });
    }
  });
  const modalEl = document.getElementById('adminActionModal');
  const confirmBtn = document.getElementById('adminActionConfirmBtn');
  const modalBody = document.getElementById('adminActionModalBody');
  let modal = new bootstrap.Modal(modalEl);

  document.querySelectorAll('.admin-action').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      adminAction = btn.getAttribute('data-action');
      adminId = btn.getAttribute('data-id');
      adminBtn = btn;
      modalBody.textContent = actionMessages[adminAction] || 'Are you sure?';
      confirmBtn.disabled = false;
      confirmBtn.textContent = 'Yes, continue';
      modal.show();
    });
  });

  confirmBtn.addEventListener('click', function() {
    if (!adminAction || !adminId) return;
    confirmBtn.disabled = true;
    confirmBtn.textContent = 'Processing...';
    fetch('pages/admin_ajax.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'action=' + encodeURIComponent(adminAction) + '&id=' + encodeURIComponent(adminId)
    })
    .then(async r => {
      let data;
      try {
        data = await r.json();
      } catch (e) {
        throw new Error('parse');
      }
      return data;
    })
    .then(data => {
      if (data && data.success) {
        confirmBtn.textContent = 'Success!';
        modalBody.textContent = 'Action completed successfully.';
        setTimeout(() => {
          var modalInstance = bootstrap.Modal.getInstance(modalEl);
          modalInstance.hide();
          location.reload();
        }, 700);
      } else {
        confirmBtn.disabled = false;
        confirmBtn.textContent = 'Yes, continue';
        modalBody.textContent = (data && data.error) ? data.error : 'Error';
      }
    })
    .catch((err) => {
      confirmBtn.disabled = false;
      confirmBtn.textContent = 'Yes, continue';
      if (err.message === 'parse') {
        modalBody.textContent = 'Respuesta inesperada del servidor.';
      } else {
        modalBody.textContent = 'Network error.';
      }
    });
  });

  // Limpiar variables al cerrar el modal
  modalEl.addEventListener('hidden.bs.modal', function() {
    adminAction = null;
    adminId = null;
    adminBtn = null;
    confirmBtn.disabled = false;
    confirmBtn.textContent = 'Yes, continue';
    modalBody.textContent = 'Are you sure you want to perform this action?';
  });
});
</script>
<?php require_once __DIR__.'/../partials/footer.php'; ?>
