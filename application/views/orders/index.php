<div class="container-fluid py-3 py-md-4">
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h4 class="fw-bold mb-0">Danh sách đơn hàng</h4>
    <div class="btn-group btn-group-sm">
      <a href="<?php echo site_url('orders'); ?>" class="btn btn-outline-secondary <?php echo ! $status ? 'active' : ''; ?>">Tất cả</a>
      <a href="<?php echo site_url('orders?status=OPEN'); ?>" class="btn btn-outline-primary <?php echo $status==='OPEN' ? 'active' : ''; ?>">Đang mở</a>
      <a href="<?php echo site_url('orders?status=WAIT_PAYMENT'); ?>" class="btn btn-outline-warning <?php echo $status==='WAIT_PAYMENT' ? 'active' : ''; ?>">Chờ TT</a>
      <a href="<?php echo site_url('orders?status=PAID'); ?>" class="btn btn-outline-success <?php echo $status==='PAID' ? 'active' : ''; ?>">Đã TT</a>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-hover align-middle bg-white rounded shadow-sm">
      <thead class="table-light">
        <tr><th>Mã đơn</th><th>Bàn</th><th class="text-end">Tổng tiền</th><th>Trạng thái</th><th>Thời gian</th><th></th></tr>
      </thead>
      <tbody>
      <?php foreach ($orders as $o): ?>
        <tr>
          <td><?php echo htmlspecialchars($o['order_no']); ?></td>
          <td><?php echo htmlspecialchars($o['table_name']); ?></td>
          <td class="text-end"><?php echo money_format_vnd($o['total_amount']); ?></td>
          <td><span class="badge bg-<?php echo order_status_badge($o['status']); ?>"><?php echo $o['status']; ?></span></td>
          <td class="small text-muted"><?php echo date('d/m H:i', strtotime($o['created_at'])); ?></td>
          <td><a href="<?php echo site_url('orders/'.$o['id']); ?>" class="btn btn-sm btn-outline-primary">Xem</a></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($orders)): ?>
        <tr><td colspan="6" class="text-center text-muted py-4">Không có đơn hàng nào.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
