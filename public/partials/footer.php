</main>
<footer class="border-top bg-white">
  <div class="container py-3">
    <div class="d-flex justify-content-around">
      <a class="btn btn-link" href="<?= e(base_url('index.php')) ?>">Home</a>
      <a class="btn btn-link disabled" tabindex="-1" aria-disabled="true">Journal</a>
      <a class="btn btn-link" href="<?= e(base_url('index.php?view=account')) ?>">Account</a>
    </div>
    <p class="text-center text-muted small mb-0">&copy; <?= date('Y') ?> Prayer Wall</p>
  </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
