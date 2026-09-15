<div class="container-fluid py-3 py-md-4">
  <h4 class="fw-bold mb-3"><i class="bi bi-cup-straw"></i> Pha chế</h4>

  <?php echo form_open('pha-che', array('method' => 'get', 'class' => 'mb-3')); ?>
    <div class="input-group">
      <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
      <input type="text" name="q" class="form-control" placeholder="Tìm công thức..." value="<?php echo htmlspecialchars($keyword); ?>">
      <?php if ($keyword !== ''): ?>
        <a href="<?php echo site_url('pha-che'); ?>" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
      <?php endif; ?>
    </div>
  <?php echo form_close(); ?>

  <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3">
    <?php foreach ($recipes as $r): ?>
      <div class="col">
        <a href="<?php echo site_url('pha-che/'.$r['id']); ?>" class="text-decoration-none">
          <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center align-items-center py-4">
              <i class="bi bi-cup-hot-fill fs-1 text-brand mb-2"></i>
              <div class="fw-semibold text-dark"><?php echo htmlspecialchars($r['name']); ?></div>
            </div>
          </div>
        </a>
      </div>
    <?php endforeach; ?>
    <?php if (empty($recipes)): ?>
      <div class="col-12">
        <p class="text-muted text-center py-4">
          <?php echo $keyword !== '' ? 'Không tìm thấy công thức nào khớp "'.htmlspecialchars($keyword).'".' : 'Chưa có công thức nào.'; ?>
        </p>
      </div>
    <?php endif; ?>
  </div>
</div>
