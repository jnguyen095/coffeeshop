<div class="container-fluid py-3 py-md-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">🌕 Đăng ký quà Trung Thu</h4>
    <a href="<?php echo site_url('trung-thu'); ?>" target="_blank" class="btn btn-outline-secondary btn-sm"><i class="bi bi-box-arrow-up-right"></i> Xem form đăng ký</a>
  </div>

  <div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm rounded-4 h-100">
        <div class="card-body text-center">
          <i class="bi bi-person-check text-brand fs-3"></i>
          <div class="fs-4 fw-bold mt-1"><?php echo $total_count; ?></div>
          <div class="text-muted small">Tổng lượt đăng ký</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm rounded-4 h-100">
        <div class="card-body text-center">
          <i class="bi bi-emoji-smile text-brand fs-3"></i>
          <div class="fs-4 fw-bold mt-1"><?php echo $total_kids; ?></div>
          <div class="text-muted small">Tổng số bé</div>
        </div>
      </div>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table bg-white shadow-sm rounded align-middle">
      <thead class="table-light"><tr><th>Thời gian đăng ký</th><th>Tên Ba/Mẹ</th><th>Số điện thoại</th><th class="text-end">Số bé</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($registrations as $r): ?>
        <tr>
          <td class="text-muted small"><?php echo date('d/m/Y H:i', strtotime($r['created_at'])); ?></td>
          <td><?php echo htmlspecialchars($r['parent_name']); ?></td>
          <td><?php echo htmlspecialchars($r['phone']); ?></td>
          <td class="text-end"><?php echo (int) $r['kid_count']; ?></td>
          <td class="text-nowrap">
            <a href="<?php echo site_url('trung-thu/admin/'.$r['id'].'/edit'); ?>" class="btn btn-sm btn-outline-primary">Sửa</a>
            <?php echo form_open('trung-thu/admin/'.$r['id'].'/delete', array('class' => 'd-inline', 'onsubmit' => "return confirm('Xóa đăng ký này?');")); ?>
              <button class="btn btn-sm btn-outline-danger">Xóa</button>
            <?php echo form_close(); ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($registrations)): ?>
        <tr><td colspan="5" class="text-center text-muted py-4">Chưa có đăng ký nào.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
