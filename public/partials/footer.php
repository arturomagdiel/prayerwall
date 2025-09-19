</main>
<footer class="border-top bg-white">
  <div class="container py-3">
    <div class="d-flex flex-column align-items-center gap-2">
      <div class="d-flex justify-content-center gap-4 mb-2 footer-nav">
        <?php if (!is_admin()): ?>
          <a class="btn btn-link d-flex flex-column align-items-center footer-link" href="<?= e(base_url('index.php')) ?>">
            <i class="fa-solid fa-house fa-2x mb-1"></i>
            <span class="footer-label">Home</span>
          </a>
        <?php endif; ?>
        <?php if (is_logged_in() && !is_admin()): ?>
          <a class="btn btn-link d-flex flex-column align-items-center footer-link" href="<?= e(base_url('index.php?view=account')) ?>">
            <i class="fa-solid fa-user-circle fa-2x mb-1"></i>
            <span class="footer-label">Account</span>
          </a>
        <?php endif; ?>
        <?php if (is_logged_in()): ?>
          <a class="btn btn-link d-flex flex-column align-items-center text-danger footer-link" href="<?= e(base_url('index.php?view=logout')) ?>">
            <i class="fa-solid fa-right-from-bracket fa-2x mb-1"></i>
            <span class="footer-label">Logout</span>
          </a>
        <?php else: ?>
          <a class="btn btn-link d-flex flex-column align-items-center footer-link" href="<?= e(base_url('index.php?view=login')) ?>">
            <i class="fa-solid fa-user-circle fa-2x mb-1"></i>
            <span class="footer-label">Account</span>
          </a>
        <?php endif; ?>
      </div>
</main>
<style>
.footer-link {
  text-decoration: none !important;
}
.footer-label {
  font-size: 0.9rem;
  margin-top: 0.25rem;
}
.footer-nav i {
  margin-bottom: 0.2rem;
}
</style>
  <!-- No user greeting in footer -->
    </div>

    <p class="text-center text-muted small mb-0">&copy; <?= date('Y') ?> Prayer Wall / Calls Management</p>
  </div>
</footer>
</main>
<!-- Font Awesome -->
<script src="https://kit.fontawesome.com/4b8b3b0b2e.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
