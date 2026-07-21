<div class="container py-3 py-md-4" style="max-width:480px;">
  <h4 class="fw-bold mb-3"><i class="bi bi-gear"></i> Cài đặt hệ thống</h4>

  <?php if ($error): ?>
    <div class="alert alert-danger py-2 small"><?php echo $error; ?></div>
  <?php endif; ?>

  <div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white fw-semibold">Thuế VAT</div>
    <div class="card-body">
      <?php echo form_open(current_url()); ?>
        <div class="mb-3">
          <label class="form-label">Thuế suất VAT (%)</label>
          <div class="input-group">
            <input type="number" name="vat_percent" class="form-control form-control-lg" min="0" max="100" step="0.1"
                   value="<?php echo $vat_percent; ?>" required>
            <span class="input-group-text">%</span>
          </div>
          <div class="form-text">Áp dụng cho tất cả đơn hàng (bàn cafe, mang đi, sân pickleball) kể từ lần tính lại tổng tiền tiếp theo.</div>
        </div>
        <button class="btn btn-brand btn-lg w-100">Lưu thay đổi</button>
      <?php echo form_close(); ?>
    </div>
  </div>

  <div class="card border-0 shadow-sm rounded-4 mt-3">
    <div class="card-header bg-white fw-semibold">Giờ nhận đặt sân</div>
    <div class="card-body">
      <?php echo form_open(current_url()); ?>
        <input type="hidden" name="form" value="booking_hours">
        <div class="row g-2">
          <div class="col-6">
            <label class="form-label">Giờ bắt đầu</label>
            <input type="time" name="booking_start_time" class="form-control form-control-lg"
                   value="<?php echo $booking_start_time; ?>" required>
          </div>
          <div class="col-6">
            <label class="form-label">Giờ kết thúc</label>
            <input type="time" name="booking_end_time" class="form-control form-control-lg"
                   value="<?php echo $booking_end_time; ?>" required>
          </div>
        </div>
        <div class="form-text">Khung giờ trong ngày cho phép đặt/hiển thị lịch sân. Mặc định 06:00 - 22:00.</div>
        <button class="btn btn-brand btn-lg w-100 mt-3">Lưu thay đổi</button>
      <?php echo form_close(); ?>
    </div>
  </div>
</div>
