<div class="container py-3 py-md-4" style="max-width:480px;">
  <h4 class="fw-bold mb-3"><?php echo $page_title; ?></h4>
  <?php if ( ! empty($error)): ?><div class="alert alert-danger py-2 small"><?php echo $error; ?></div><?php endif; ?>
  <?php echo form_open(current_url()); ?>
    <div class="mb-3">
      <label class="form-label">Mã bàn</label>
      <input type="text" name="table_code" class="form-control" required maxlength="20"
             value="<?php echo $table ? htmlspecialchars($table['table_code']) : ''; ?>" placeholder="VD: T13">
    </div>
    <div class="mb-3">
      <label class="form-label">Tên bàn</label>
      <input type="text" name="table_name" class="form-control" required maxlength="100"
             value="<?php echo $table ? htmlspecialchars($table['table_name']) : ''; ?>" placeholder="VD: Bàn 13">
    </div>
    <div class="mb-3">
      <label class="form-label">Sức chứa (số chỗ ngồi)</label>
      <input type="number" name="capacity" class="form-control" required min="1" max="50"
             value="<?php echo $table ? (int) $table['capacity'] : 4; ?>">
    </div>
    <div class="mb-3">
      <label class="form-label d-block">Loại</label>
      <div class="btn-group w-100" role="group">
        <input type="radio" class="btn-check" name="table_type" id="typeCafe" value="CAFE" onchange="toggleHourlyRate()"
               <?php echo ( ! $table || $table['table_type'] === 'CAFE') ? 'checked' : ''; ?>>
        <label class="btn btn-outline-secondary" for="typeCafe"><i class="bi bi-cup-hot"></i> Bàn cafe</label>
        <input type="radio" class="btn-check" name="table_type" id="typeCourt" value="COURT" onchange="toggleHourlyRate()"
               <?php echo ($table && $table['table_type'] === 'COURT') ? 'checked' : ''; ?>>
        <label class="btn btn-outline-secondary" for="typeCourt"><i class="bi bi-dribbble"></i> Sân pickleball</label>
      </div>
    </div>
    <div class="d-none" id="hourlyRateGroup">
      <label class="form-label d-block mb-1">Giá thuê / giờ theo khung giờ (đ)</label>
      <div class="row g-2 mb-3">
        <div class="col-4">
          <label class="form-label small text-muted mb-1"><i class="bi bi-sunrise"></i> Sáng (06h-12h)</label>
          <input type="number" name="rate_morning" class="form-control" min="0" step="1000"
                 value="<?php echo $table ? (int) $table['rate_morning'] : 0; ?>">
        </div>
        <div class="col-4">
          <label class="form-label small text-muted mb-1"><i class="bi bi-sun"></i> Chiều (12h-18h)</label>
          <input type="number" name="rate_afternoon" class="form-control" min="0" step="1000"
                 value="<?php echo $table ? (int) $table['rate_afternoon'] : 0; ?>">
        </div>
        <div class="col-4">
          <label class="form-label small text-muted mb-1"><i class="bi bi-moon-stars"></i> Tối (18h-23h)</label>
          <input type="number" name="rate_evening" class="form-control" min="0" step="1000"
                 value="<?php echo $table ? (int) $table['rate_evening'] : 0; ?>">
        </div>
      </div>
    </div>
    <div class="d-grid gap-2">
      <button class="btn btn-brand btn-lg">Lưu</button>
      <a href="<?php echo site_url('tables/manage'); ?>" class="btn btn-outline-secondary">Hủy</a>
    </div>
  <?php echo form_close(); ?>
</div>
<script>
function toggleHourlyRate(){
  var isCourt = document.getElementById('typeCourt').checked;
  document.getElementById('hourlyRateGroup').classList.toggle('d-none', !isCourt);
}
toggleHourlyRate();
</script>
