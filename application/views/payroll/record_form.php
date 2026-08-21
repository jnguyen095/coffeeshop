<div class="container py-3 py-md-4" style="max-width:600px;">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Dữ liệu lương — <?php echo htmlspecialchars($target_user['fullname']); ?></h4>
    <a href="<?php echo site_url('payroll/admin').'?period='.$period; ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Quay lại</a>
  </div>
  <p class="text-muted small mb-3"><?php echo payroll_period_label($period); ?> — Loại lương: <strong><?php echo $settings['salary_type'] === 'HOURLY' ? 'Theo giờ' : 'Cố định'; ?></strong></p>

  <?php echo form_open(current_url().'?period='.$period, array('class' => 'card border-0 shadow-sm rounded-4')); ?>
    <div class="card-body">
      <?php if ($settings['salary_type'] === 'FIXED'): ?>
        <div class="mb-3">
          <label class="form-label">Số ngày nghỉ trong tháng</label>
          <div>
            <span class="fs-5 fw-semibold"><?php echo rtrim(rtrim(number_format($salary['absence_days'], 2, '.', ''), '0'), '.'); ?> ngày</span>
            <a href="<?php echo site_url('payroll/hours/'.$target_user['id']).'?period='.$period; ?>" class="btn btn-sm btn-outline-primary ms-2">Chọn ngày nghỉ</a>
          </div>
          <div class="form-text">Đơn giá/ngày = lương cố định (<?php echo money_format_vnd($salary['fixed_salary']); ?>) ÷ <?php echo $salary['days_in_month']; ?> ngày = <?php echo money_format_vnd($salary['daily_rate']); ?>/ngày.</div>
        </div>
      <?php else: ?>
        <div class="mb-3">
          <label class="form-label">Giờ làm trong tháng</label>
          <div>
            <span class="fs-5 fw-semibold"><?php echo rtrim(rtrim(number_format($salary['total_hours'], 2, '.', ''), '0'), '.'); ?> giờ</span>
            <a href="<?php echo site_url('payroll/hours/'.$target_user['id']).'?period='.$period; ?>" class="btn btn-sm btn-outline-primary ms-2">Nhập giờ làm theo ngày</a>
          </div>
        </div>
      <?php endif; ?>

      <div class="mb-3">
        <label class="form-label">Lương tháng (tính tự động)</label>
        <input type="text" class="form-control" value="<?php echo money_format_vnd($salary['gross_salary']); ?>" disabled>
      </div>

      <div class="mb-3">
        <label class="form-label">Đã ứng lương (đ)</label>
        <input type="number" step="1000" min="0" name="advance_amount" id="advanceInput" class="form-control" value="<?php echo (float) $record['advance_amount']; ?>">
      </div>

      <div class="alert alert-warning d-flex justify-content-between align-items-center mb-3">
        <span class="fw-bold">Tiền lương cần trả</span>
        <span class="fs-4 fw-bold" id="netSalary"><?php echo money_format_vnd($salary['net_salary']); ?></span>
      </div>

      <div class="mb-3">
        <label class="form-label">Trạng thái chi lương</label>
        <select name="paid_status" class="form-select">
          <option value="UNPAID" <?php echo $record['paid_status'] === 'UNPAID' ? 'selected' : ''; ?>>Chưa chi lương</option>
          <option value="PAID" <?php echo $record['paid_status'] === 'PAID' ? 'selected' : ''; ?>>Đã chi lương</option>
        </select>
      </div>

      <div class="mb-0">
        <label class="form-label">Ghi chú</label>
        <input type="text" name="note" class="form-control" value="<?php echo htmlspecialchars((string) $record['note']); ?>">
      </div>
    </div>
    <div class="card-footer bg-white border-0 p-3 pt-0">
      <button type="submit" class="btn btn-brand"><i class="bi bi-check-lg"></i> Lưu</button>
    </div>
  <?php echo form_close(); ?>

  <div class="card border-0 shadow-sm rounded-4 mt-3">
    <div class="card-body">
      <h6 class="fw-bold mb-3"><i class="bi bi-bank"></i> Thông tin thanh toán</h6>
      <?php if ($settings['bank_name'] || $settings['bank_account_number']): ?>
        <table class="table table-sm mb-0">
          <tr><td class="text-muted" style="width:40%;">Ngân hàng</td><td><?php echo htmlspecialchars((string) $settings['bank_name']); ?></td></tr>
          <tr><td class="text-muted">Chi nhánh</td><td><?php echo htmlspecialchars((string) $settings['bank_branch']); ?></td></tr>
          <tr><td class="text-muted">Số tài khoản</td><td class="fw-semibold"><?php echo htmlspecialchars((string) $settings['bank_account_number']); ?></td></tr>
          <tr><td class="text-muted">Chủ tài khoản</td><td><?php echo htmlspecialchars((string) $settings['bank_account_name']); ?></td></tr>
        </table>
      <?php else: ?>
        <p class="text-muted small mb-0">Nhân viên chưa cập nhật thông tin ngân hàng. <a href="<?php echo site_url('payroll/settings/'.$target_user['id']); ?>">Cập nhật ngay</a>.</p>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
(function(){
  var advanceInput = document.getElementById('advanceInput');
  var netSalaryEl = document.getElementById('netSalary');
  var grossSalary = <?php echo (float) $salary['gross_salary']; ?>;

  function formatVnd(n){
    return Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.') + 'đ';
  }
  function recalc(){
    var advance = parseFloat(advanceInput.value) || 0;
    netSalaryEl.textContent = formatVnd(grossSalary - advance);
  }
  advanceInput.addEventListener('input', recalc);
})();
</script>
