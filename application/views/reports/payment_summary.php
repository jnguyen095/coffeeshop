<div class="container py-3 py-md-4" style="max-width:560px;">
  <a href="<?php echo site_url('reports'); ?>" class="btn btn-sm btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Báo cáo</a>
  <h4 class="fw-bold mb-3">Tổng hợp thanh toán</h4>
  <?php echo form_open('reports/payment-summary', array('method'=>'get','class'=>'row g-2 mb-3')); ?>
    <div class="col-auto"><input type="date" name="from" value="<?php echo $from; ?>" class="form-control"></div>
    <div class="col-auto"><input type="date" name="to" value="<?php echo $to; ?>" class="form-control"></div>
    <div class="col-auto"><button class="btn btn-brand">Xem</button></div>
  <?php echo form_close(); ?>
  <div class="table-responsive">
    <table class="table bg-white shadow-sm rounded">
      <thead class="table-light"><tr><th>Phương thức</th><th class="text-end">Số GD</th><th class="text-end">Tổng tiền</th></tr></thead>
      <tbody>
      <?php $grand=0; foreach ($rows as $r): $grand += $r['total_amount']; ?>
        <tr><td><?php echo $r['payment_method']; ?></td><td class="text-end"><?php echo $r['total_count']; ?></td><td class="text-end"><?php echo money_format_vnd($r['total_amount']); ?></td></tr>
      <?php endforeach; ?>
      <?php if (empty($rows)): ?><tr><td colspan="3" class="text-center text-muted py-4">Không có dữ liệu.</td></tr><?php endif; ?>
      </tbody>
      <?php if (!empty($rows)): ?><tfoot><tr class="fw-bold"><td>Tổng</td><td></td><td class="text-end"><?php echo money_format_vnd($grand); ?></td></tr></tfoot><?php endif; ?>
    </table>
  </div>
</div>
