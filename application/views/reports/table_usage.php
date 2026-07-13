<div class="container py-3 py-md-4" style="max-width:640px;">
  <a href="<?php echo site_url('reports'); ?>" class="btn btn-sm btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Báo cáo</a>
  <h4 class="fw-bold mb-3">Sử dụng bàn</h4>
  <?php echo form_open('reports/table-usage', array('method'=>'get','class'=>'row g-2 mb-3')); ?>
    <div class="col-auto"><input type="date" name="from" value="<?php echo $from; ?>" class="form-control"></div>
    <div class="col-auto"><input type="date" name="to" value="<?php echo $to; ?>" class="form-control"></div>
    <div class="col-auto"><button class="btn btn-brand">Xem</button></div>
  <?php echo form_close(); ?>
  <div class="table-responsive">
    <table class="table bg-white shadow-sm rounded">
      <thead class="table-light"><tr><th>Bàn</th><th class="text-end">Số lượt</th><th class="text-end">TG trung bình (phút)</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr><td><?php echo htmlspecialchars($r['table_name']); ?></td><td class="text-end"><?php echo $r['sessions_count']; ?></td><td class="text-end"><?php echo round($r['avg_minutes']); ?></td></tr>
      <?php endforeach; ?>
      <?php if (empty($rows)): ?><tr><td colspan="3" class="text-center text-muted py-4">Không có dữ liệu.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
