<?php
  $prev_period = date('Y-m', strtotime($period.'-01 -1 month'));
  $next_period = date('Y-m', strtotime($period.'-01 +1 month'));
?>
<div class="container-fluid py-3 py-md-4">
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h4 class="fw-bold mb-0"><i class="bi bi-cash-coin"></i> Quản lý lương</h4>
    <div class="d-flex align-items-center gap-2">
      <a href="<?php echo site_url('payroll/admin').'?period='.$prev_period.($keyword ? '&q='.urlencode($keyword) : ''); ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-chevron-left"></i></a>
      <?php echo form_open('payroll/admin', array('method' => 'get', 'class' => 'd-flex gap-2')); ?>
        <input type="month" name="period" value="<?php echo $period; ?>" class="form-control form-control-sm" onchange="this.form.submit()">
      <?php echo form_close(); ?>
      <a href="<?php echo site_url('payroll/admin').'?period='.$next_period.($keyword ? '&q='.urlencode($keyword) : ''); ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-chevron-right"></i></a>
    </div>
  </div>

  <?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success py-2 small"><?php echo $this->session->flashdata('success'); ?></div>
  <?php endif; ?>

  <?php echo form_open('payroll/admin', array('method' => 'get', 'class' => 'row g-2 mb-3')); ?>
    <input type="hidden" name="period" value="<?php echo $period; ?>">
    <div class="col-auto">
      <input type="text" name="q" class="form-control" placeholder="Tìm theo tên đăng nhập hoặc họ tên..." value="<?php echo htmlspecialchars($keyword); ?>" style="min-width:260px;">
    </div>
    <div class="col-auto">
      <button type="submit" class="btn btn-outline-secondary"><i class="bi bi-search"></i> Tìm</button>
    </div>
    <?php if ($keyword): ?>
    <div class="col-auto">
      <a href="<?php echo site_url('payroll/admin').'?period='.$period; ?>" class="btn btn-outline-secondary">Xoá lọc</a>
    </div>
    <?php endif; ?>
  <?php echo form_close(); ?>

  <h6 class="text-muted mb-2"><?php echo payroll_period_label($period); ?></h6>

  <div class="table-responsive">
    <table class="table bg-white shadow-sm rounded align-middle">
      <thead class="table-light">
        <tr>
          <th>Nhân viên</th>
          <th>Vai trò</th>
          <th>Loại lương</th>
          <th class="text-end">Lương tháng</th>
          <th class="text-end">Đã ứng</th>
          <th class="text-end">Còn lại</th>
          <th>Trạng thái</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($rows as $r): $u = $r['user']; $s = $r['salary']; ?>
        <tr>
          <td><?php echo htmlspecialchars($u['fullname']); ?></td>
          <td><?php echo role_label($u['role']); ?></td>
          <td><?php echo $s['salary_type'] === 'HOURLY' ? 'Theo giờ' : 'Cố định'; ?></td>
          <td class="text-end"><?php echo money_format_vnd($s['gross_salary']); ?></td>
          <td class="text-end"><?php echo money_format_vnd($s['advance_amount']); ?></td>
          <td class="text-end fw-semibold"><?php echo money_format_vnd($s['net_salary']); ?></td>
          <td>
            <?php if ($s['paid_status'] === 'PAID'): ?>
              <span class="badge bg-success">Đã chi</span>
            <?php else: ?>
              <span class="badge bg-secondary">Chưa chi</span>
            <?php endif; ?>
          </td>
          <td class="text-nowrap">
            <a href="<?php echo site_url('payroll/hours/'.$u['id']).'?period='.$period; ?>" class="btn btn-sm btn-outline-secondary">Giờ làm</a>
            <a href="<?php echo site_url('payroll/record/'.$u['id']).'?period='.$period; ?>" class="btn btn-sm btn-outline-primary">Dữ liệu tháng</a>
            <a href="<?php echo site_url('payroll/settings/'.$u['id']); ?>" class="btn btn-sm btn-outline-secondary">Cấu hình</a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($rows)): ?>
        <tr><td colspan="8" class="text-center text-muted py-4">Chưa có nhân viên nào.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
