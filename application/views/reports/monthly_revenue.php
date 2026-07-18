<div class="container py-3 py-md-4" style="max-width:640px;">
  <a href="<?php echo site_url('reports'); ?>" class="btn btn-sm btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Báo cáo</a>
  <h4 class="fw-bold mb-3">Doanh thu theo tháng</h4>
  <?php echo form_open('reports/monthly-revenue', array('method'=>'get','class'=>'row g-2 mb-3')); ?>
    <div class="col-auto"><input type="month" name="month" value="<?php echo $month; ?>" class="form-control"></div>
    <div class="col-auto"><button class="btn btn-brand">Xem</button></div>
  <?php echo form_close(); ?>

  <?php
    $total = 0; $total_drink = 0; $total_court = 0;
    foreach ($rows as $r) { $total += $r['revenue']; $total_drink += $r['drink_revenue']; $total_court += $r['court_revenue']; }
  ?>

  <div class="row g-3 mb-3">
    <div class="col-4">
      <div class="card border-0 shadow-sm rounded-4 h-100">
        <div class="card-body text-center">
          <div class="text-muted small">Tổng doanh thu</div>
          <div class="fs-5 fw-bold text-brand mt-1"><?php echo money_format_vnd($total); ?></div>
        </div>
      </div>
    </div>
    <div class="col-4">
      <div class="card border-0 shadow-sm rounded-4 h-100">
        <div class="card-body text-center">
          <div class="text-muted small"><i class="bi bi-cup-hot"></i> Đồ uống / món ăn</div>
          <div class="fs-5 fw-bold text-brand mt-1"><?php echo money_format_vnd($total_drink); ?></div>
        </div>
      </div>
    </div>
    <div class="col-4">
      <div class="card border-0 shadow-sm rounded-4 h-100">
        <div class="card-body text-center">
          <div class="text-muted small"><i class="bi bi-dribbble"></i> Sân pickleball</div>
          <div class="fs-5 fw-bold text-brand mt-1"><?php echo money_format_vnd($total_court); ?></div>
        </div>
      </div>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table bg-white shadow-sm rounded">
      <thead class="table-light"><tr><th>Ngày</th><th class="text-end">Số đơn</th><th class="text-end">Đồ uống</th><th class="text-end">Sân</th><th class="text-end">Tổng</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?php echo date('d/m/Y', strtotime($r['day'])); ?></td>
          <td class="text-end"><?php echo $r['orders_count']; ?></td>
          <td class="text-end"><?php echo money_format_vnd($r['drink_revenue']); ?></td>
          <td class="text-end"><?php echo money_format_vnd($r['court_revenue']); ?></td>
          <td class="text-end fw-semibold"><?php echo money_format_vnd($r['revenue']); ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($rows)): ?><tr><td colspan="5" class="text-center text-muted py-4">Không có dữ liệu.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
  <div class="small text-muted">(*) Đồ uống/Sân tính theo giá trị mặt hàng, chưa gồm giảm giá/VAT nên có thể không cộng khớp cột Tổng tuyệt đối.</div>
</div>
