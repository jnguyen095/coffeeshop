<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <h4 class="fw-bold mb-0"><i class="bi bi-calendar-check"></i> Lịch đặt sân</h4>
  <a href="<?php echo site_url('bookings/create'); ?>" class="btn btn-brand btn-sm"><i class="bi bi-plus-lg"></i> Đặt lịch mới</a>
</div>

<?php if ($this->session->flashdata('error')): ?>
  <div class="alert alert-danger py-2 small"><?php echo $this->session->flashdata('error'); ?></div>
<?php endif; ?>

<?php $search_qs = ($search !== '') ? '&search='.urlencode($search) : ''; ?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
  <ul class="nav nav-pills mb-0">
    <li class="nav-item"><a class="nav-link <?php echo $view === 'day' ? 'active' : ''; ?>" href="<?php echo site_url('bookings?view=day&date='.$date.$search_qs); ?>">Ngày</a></li>
    <li class="nav-item"><a class="nav-link <?php echo $view === 'week' ? 'active' : ''; ?>" href="<?php echo site_url('bookings?view=week&date='.$date.$search_qs); ?>">Tuần</a></li>
    <li class="nav-item"><a class="nav-link <?php echo $view === 'month' ? 'active' : ''; ?>" href="<?php echo site_url('bookings?view=month&date='.$date.$search_qs); ?>">Tháng</a></li>
  </ul>

  <form method="get" action="<?php echo site_url('bookings'); ?>" class="d-flex align-items-center gap-1">
    <input type="hidden" name="view" value="<?php echo htmlspecialchars($view); ?>">
    <input type="hidden" name="date" value="<?php echo htmlspecialchars($date); ?>">
    <input type="search" name="search" value="<?php echo htmlspecialchars($search); ?>" class="form-control form-control-sm" style="max-width:220px;" placeholder="Tìm tên hoặc SĐT khách...">
    <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button>
    <?php if ($search !== ''): ?>
      <a href="<?php echo site_url('bookings?view='.$view.'&date='.$date); ?>" class="btn btn-sm btn-outline-secondary" title="Xóa tìm kiếm"><i class="bi bi-x-lg"></i></a>
    <?php endif; ?>
  </form>
</div>
