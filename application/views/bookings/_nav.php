<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <h4 class="fw-bold mb-0"><i class="bi bi-calendar-check"></i> Lịch đặt sân</h4>
  <a href="<?php echo site_url('bookings/create'); ?>" class="btn btn-brand btn-sm"><i class="bi bi-plus-lg"></i> Đặt lịch mới</a>
</div>

<?php if ($this->session->flashdata('error')): ?>
  <div class="alert alert-danger py-2 small"><?php echo $this->session->flashdata('error'); ?></div>
<?php endif; ?>

<ul class="nav nav-pills mb-3">
  <li class="nav-item"><a class="nav-link <?php echo $view === 'day' ? 'active' : ''; ?>" href="<?php echo site_url('bookings?view=day&date='.$date); ?>">Ngày</a></li>
  <li class="nav-item"><a class="nav-link <?php echo $view === 'week' ? 'active' : ''; ?>" href="<?php echo site_url('bookings?view=week&date='.$date); ?>">Tuần</a></li>
  <li class="nav-item"><a class="nav-link <?php echo $view === 'month' ? 'active' : ''; ?>" href="<?php echo site_url('bookings?view=month&date='.$date); ?>">Tháng</a></li>
</ul>
