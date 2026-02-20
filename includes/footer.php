  </div><!-- /.page-scroll -->
</div><!-- /.main-content -->

<!-- ══ RIGHT PANEL ══ -->
<aside class="right-panel">
  <?php
  $firstFile = null;
  if ($recentFiles && $recentFiles->num_rows > 0) {
      $recentFiles->data_seek(0);
      $firstFile = $recentFiles->fetch_assoc();
  }
  ?>
  <?php if ($firstFile): ?>
  <div class="rp-header">
    <div class="rp-title"><?= htmlspecialchars(substr($firstFile['doc_title'],0,34)) ?><?= strlen($firstFile['doc_title'])>34?'...':'' ?></div>
    <div class="rp-sub"><?= strtoupper($firstFile['file_type']) ?> &nbsp;·&nbsp; <?= formatBytes((int)$firstFile['file_size']) ?></div>
  </div>
  <div class="rp-action-row">
    <div class="rp-action-btn" title="Bookmark">🔖</div>
    <div class="rp-action-btn" title="Edit">✏️</div>
    <div class="rp-action-btn" title="Delete">🗑</div>
    <div class="rp-action-btn" title="Share">📤</div>
    <div class="rp-action-btn" title="Download">⬇️</div>
  </div>
  <?php endif; ?>

  <?php
  if ($recentFiles) {
      $recentFiles->data_seek(0);
      while ($f = $recentFiles->fetch_assoc()):
  ?>
  <div class="rp-file-card">
    <div class="rp-file-name"><?= htmlspecialchars($f['doc_title']) ?></div>
    <div class="rp-file-meta">
      Shared By: <?= htmlspecialchars($f['uploader']) ?><br>
      Size: <?= formatBytes((int)$f['file_size']) ?><br>
      <?= date('M j, Y · g:i A', strtotime($f['created_at'])) ?>
    </div>
    <span class="rp-detail-btn">Details</span>
  </div>
  <?php endwhile; } ?>
</aside>

</div><!-- /.main-wrap -->

<script>
document.getElementById('topbarSearch')?.addEventListener('input', function() {
  const q = this.value.toLowerCase();
  document.querySelectorAll('.doc-card, .data-table tbody tr').forEach(el => {
    el.style.display = el.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
});
</script>
</body>
</html>
