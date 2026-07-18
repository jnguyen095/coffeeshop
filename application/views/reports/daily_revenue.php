<div class="container py-3 py-md-4" style="max-width:480px;">
  <a href="<?php echo site_url('reports'); ?>" class="btn btn-sm btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Báo cáo</a>
  <h4 class="fw-bold mb-3">Doanh thu theo ngày</h4>
  <?php echo form_open('reports/daily-revenue', array('method'=>'get','class'=>'row g-2 mb-3')); ?>
    <div class="col-auto"><input type="date" name="date" value="<?php echo $date; ?>" class="form-control"></div>
    <div class="col-auto"><button class="btn btn-brand">Xem</button></div>
  <?php echo form_close(); ?>
  <div class="card border-0 shadow-sm rounded-4">
    <div class="card-body text-center py-5">
      <div class="text-muted">Doanh thu ngày <?php echo date('d/m/Y', strtotime($date)); ?></div>
      <div class="display-6 fw-bold text-brand mt-2"><?php echo money_format_vnd($revenue); ?></div>
    </div>
  </div>

  <div class="row g-3 mt-1">
    <div class="col-6">
      <div class="card border-0 shadow-sm rounded-4 h-100">
        <div class="card-body text-center">
          <i class="bi bi-cup-hot text-brand fs-3"></i>
          <div class="fs-5 fw-bold mt-1"><?php echo money_format_vnd($drink_revenue); ?></div>
          <div class="text-muted small">Đồ uống / món ăn</div>
        </div>
      </div>
    </div>
    <div class="col-6">
      <div class="card border-0 shadow-sm rounded-4 h-100">
        <div class="card-body text-center">
          <i class="bi bi-dribbble text-brand fs-3"></i>
          <div class="fs-5 fw-bold mt-1"><?php echo money_format_vnd($court_revenue); ?></div>
          <div class="text-muted small">Sân pickleball</div>
        </div>
      </div>
    </div>
  </div>
  <div class="small text-muted mt-2">(*) Đồ uống/Sân tính theo giá trị mặt hàng, chưa gồm giảm giá/VAT nên có thể không cộng khớp Tổng doanh thu tuyệt đối.</div>
</div>
