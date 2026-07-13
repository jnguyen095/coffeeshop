<div class="container py-3 py-md-4" style="max-width:640px;">
  <a href="<?php echo site_url('reports'); ?>" class="btn btn-sm btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Báo cáo</a>
  <h4 class="fw-bold mb-3">Hiệu suất bếp</h4>
  <?php echo form_open('reports/kitchen-performance', array('method'=>'get','class'=>'row g-2 mb-3')); ?>
    <div class="col-auto"><input type="date" name="from" value="<?php echo $from; ?>" class="form-control"></div>
    <div class="col-auto"><input type="date" name="to" value="<?php echo $to; ?>" class="form-control"></div>
    <div class="col-auto"><button class="btn btn-brand">Xem</button></div>
  <?php echo form_close(); ?>
  <div class="table-responsive">
    <table class="table bg-white shadow-sm rounded">
      <thead class="table-light"><tr><th>Ngày</th><th class="text-end">Tổng ticket</th><th class="text-end">Hoàn thành</th><th class="text-end">Tỷ lệ</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): $pct = $r['total'] > 0 ? round($r['completed']/$r['total']*100) : 0; ?>
        <tr><td><?php echo date('d/m/Y', strtotime($r['day'])); ?></td><td class="text-end"><?php echo $r['total']; ?></td><td class="text-end"><?php echo $r['completed']; ?></td><td class="text-end"><?php echo $pct; ?>%</td></tr>
      <?php endforeach; ?>
      <?php if (empty($rows)): ?><tr><td colspan="4" class="text-center text-muted py-4">Không có dữ liệu.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
