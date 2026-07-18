<div class="container-fluid py-3 py-md-4">
  <h4 class="fw-bold mb-3"><i class="bi bi-bar-chart"></i> Báo cáo</h4>
  <div class="row g-3">
    <?php
    $reports = array(
      array('daily-revenue', 'bi-calendar-day', 'Doanh thu theo ngày'),
      array('monthly-revenue', 'bi-calendar-month', 'Doanh thu theo tháng'),
      array('top-products', 'bi-award', 'Sản phẩm bán chạy'),
      array('table-usage', 'bi-grid-3x3-gap', 'Sử dụng bàn'),
      array('kitchen-performance', 'bi-fire', 'Hiệu suất bếp'),
      array('payment-summary', 'bi-cash-coin', 'Tổng hợp thanh toán'),
      array('court-performance', 'bi-dribbble', 'Hiệu suất sân pickleball'),
    );
    foreach ($reports as $r): ?>
    <div class="col-6 col-md-4 col-lg-3">
      <a href="<?php echo site_url('reports/'.$r[0]); ?>" class="text-decoration-none">
        <div class="card border-0 shadow-sm rounded-4 h-100">
          <div class="card-body text-center">
            <i class="bi <?php echo $r[1]; ?> text-brand fs-2"></i>
            <div class="mt-2 fw-semibold text-dark"><?php echo $r[2]; ?></div>
          </div>
        </div>
      </a>
    </div>
    <?php endforeach; ?>
  </div>
</div>
