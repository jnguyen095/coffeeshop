<div class="container py-3 py-md-4" style="max-width:640px;">
  <a href="<?php echo site_url('reports'); ?>" class="btn btn-sm btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Báo cáo</a>
  <h4 class="fw-bold mb-3">Doanh thu theo tháng</h4>
  <?php echo form_open('reports/monthly-revenue', array('method'=>'get','class'=>'row g-2 mb-3')); ?>
    <div class="col-auto"><input type="month" name="month" value="<?php echo $month; ?>" class="form-control"></div>
    <div class="col-auto"><button class="btn btn-brand">Xem</button></div>
  <?php echo form_close(); ?>

  <?php $total = 0; foreach ($rows as $r) $total += $r['revenue']; ?>
  <div class="alert alert-light border fw-bold">Tổng doanh thu tháng: <span class="text-brand"><?php echo money_format_vnd($total); ?></span></div>

  <div class="table-responsive">
    <table class="table bg-white shadow-sm rounded">
      <thead class="table-light"><tr><th>Ngày</th><th class="text-end">Số đơn</th><th class="text-end">Doanh thu</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr><td><?php echo date('d/m/Y', strtotime($r['day'])); ?></td><td class="text-end"><?php echo $r['orders_count']; ?></td><td class="text-end"><?php echo money_format_vnd($r['revenue']); ?></td></tr>
      <?php endforeach; ?>
      <?php if (empty($rows)): ?><tr><td colspan="3" class="text-center text-muted py-4">Không có dữ liệu.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
