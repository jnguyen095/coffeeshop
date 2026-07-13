<div class="container-fluid py-3 py-md-4">
  <h4 class="fw-bold mb-3">Xin chào, <?php echo htmlspecialchars($current_user['fullname']); ?> 👋</h4>

  <div class="row g-3">
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm rounded-4 h-100">
        <div class="card-body text-center">
          <i class="bi bi-grid-3x3-gap text-brand fs-2"></i>
          <div class="fs-4 fw-bold mt-1"><?php echo $status_counts['OPEN']; ?>/<?php echo $tables_total; ?></div>
          <div class="text-muted small">Bàn đang phục vụ</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm rounded-4 h-100">
        <div class="card-body text-center">
          <i class="bi bi-cash-stack text-success fs-2"></i>
          <div class="fs-4 fw-bold mt-1"><?php echo money_format_vnd($today_revenue); ?></div>
          <div class="text-muted small">Doanh thu hôm nay</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm rounded-4 h-100">
        <div class="card-body text-center">
          <i class="bi bi-fire text-danger fs-2"></i>
          <div class="fs-4 fw-bold mt-1"><?php echo $active_tickets; ?></div>
          <div class="text-muted small">Ticket bếp đang xử lý</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm rounded-4 h-100">
        <div class="card-body text-center">
          <i class="bi bi-receipt text-warning fs-2"></i>
          <div class="fs-4 fw-bold mt-1"><?php echo $wait_payment; ?></div>
          <div class="text-muted small">Chờ thanh toán</div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3 mt-1">
    <?php if (in_array($current_user['role'], array('STAFF','ADMIN'), TRUE)): ?>
    <div class="col-6 col-md-3">
      <a href="<?php echo site_url('tables'); ?>" class="btn btn-brand w-100 py-3"><i class="bi bi-grid-3x3-gap d-block fs-3 mb-1"></i>Sơ đồ bàn</a>
    </div>
    <div class="col-6 col-md-3">
      <a href="<?php echo site_url('orders'); ?>" class="btn btn-outline-secondary w-100 py-3"><i class="bi bi-receipt d-block fs-3 mb-1"></i>Đơn hàng</a>
    </div>
    <?php endif; ?>
    <?php if (in_array($current_user['role'], array('CASHIER','ADMIN'), TRUE)): ?>
    <div class="col-6 col-md-3">
      <a href="<?php echo site_url('cashier'); ?>" class="btn btn-outline-success w-100 py-3"><i class="bi bi-cash-coin d-block fs-3 mb-1"></i>Thu ngân</a>
    </div>
    <?php endif; ?>
    <?php if ($current_user['role'] === 'ADMIN'): ?>
    <div class="col-6 col-md-3">
      <a href="<?php echo site_url('reports'); ?>" class="btn btn-outline-dark w-100 py-3"><i class="bi bi-bar-chart d-block fs-3 mb-1"></i>Báo cáo</a>
    </div>
    <?php endif; ?>
  </div>
</div>
