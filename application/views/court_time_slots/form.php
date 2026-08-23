<div class="container py-3 py-md-4" style="max-width:480px;">
  <h4 class="fw-bold mb-3"><?php echo $page_title; ?></h4>
  <?php if ( ! empty($error)): ?><div class="alert alert-danger py-2 small"><?php echo $error; ?></div><?php endif; ?>
  <?php echo form_open(current_url()); ?>
    <div class="mb-3">
      <label class="form-label">Tên khung giờ</label>
      <input type="text" name="label" class="form-control" required maxlength="100"
             value="<?php echo $slot ? htmlspecialchars($slot['label']) : ''; ?>" placeholder="VD: Khung 1">
    </div>
    <div class="row g-2">
      <div class="col-6">
        <label class="form-label">Giờ bắt đầu</label>
        <input type="time" name="start_time" class="form-control" required
               value="<?php echo $slot ? substr($slot['start_time'], 0, 5) : ''; ?>">
      </div>
      <div class="col-6">
        <label class="form-label">Giờ kết thúc</label>
        <input type="time" name="end_time" class="form-control" required
               value="<?php echo $slot ? substr($slot['end_time'], 0, 5) : ''; ?>">
      </div>
    </div>
    <div class="mb-3 mt-3">
      <label class="form-label">Giá / giờ (đ)</label>
      <input type="number" name="price_per_hour" class="form-control" min="0" step="1000"
             value="<?php echo $slot ? (float) $slot['price_per_hour'] : ''; ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Thứ tự hiển thị</label>
      <input type="number" name="sort_order" class="form-control" value="<?php echo $slot ? (int) $slot['sort_order'] : 0; ?>">
    </div>
    <?php if ($slot): ?>
    <div class="mb-3">
      <label class="form-label">Trạng thái</label>
      <select name="status" class="form-select">
        <option value="ACTIVE" <?php echo $slot['status']==='ACTIVE'?'selected':''; ?>>Đang áp dụng</option>
        <option value="INACTIVE" <?php echo $slot['status']==='INACTIVE'?'selected':''; ?>>Tạm ẩn (không tính giá)</option>
      </select>
    </div>
    <?php endif; ?>
    <div class="d-grid gap-2">
      <button class="btn btn-brand btn-lg">Lưu</button>
      <a href="<?php echo site_url('court-time-slots'); ?>" class="btn btn-outline-secondary">Hủy</a>
    </div>
  <?php echo form_close(); ?>
</div>
