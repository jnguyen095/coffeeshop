<div class="container py-3 py-md-4" style="max-width:560px;">
  <a href="<?php echo site_url('bookings').'?date='.$booking['booking_date']; ?>" class="btn btn-sm btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Lịch đặt sân</a>
  <h4 class="fw-bold mb-3"><i class="bi bi-calendar-check"></i> Sửa lịch đặt sân</h4>

  <?php if ($error): ?>
    <div class="alert alert-danger py-2 small"><?php echo $error; ?></div>
  <?php endif; ?>

  <div class="alert alert-light border py-2 small mb-3">
    Ngày: <strong><?php echo date('d/m/Y', strtotime($booking['booking_date'])); ?></strong>
    (không thể đổi ngày khi sửa — muốn đổi ngày, hãy hủy và đặt lại)
  </div>

  <?php echo form_open(current_url(), array('id' => 'bookingEditForm')); ?>
    <div class="mb-3">
      <label class="form-label">Sân</label>
      <select name="table_id" id="tableSelect" class="form-select form-select-lg" required>
        <?php foreach ($courts as $c): ?>
          <option value="<?php echo $c['id']; ?>" <?php echo ((string) $booking['table_id'] === (string) $c['id']) ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($c['table_name']); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="row g-2">
      <div class="col-6">
        <label class="form-label">Tên khách</label>
        <input type="text" name="customer_name" class="form-control" required value="<?php echo htmlspecialchars($booking['customer_name']); ?>">
      </div>
      <div class="col-6">
        <label class="form-label">Số điện thoại</label>
        <input type="tel" name="customer_phone" class="form-control" value="<?php echo htmlspecialchars((string) $booking['customer_phone']); ?>">
      </div>
    </div>
    <div class="row g-2 mt-1">
      <div class="col-6">
        <label class="form-label">Giờ bắt đầu</label>
        <input type="time" name="start_time" class="form-control" required
               min="<?php echo $booking_start_time; ?>" max="<?php echo $booking_end_time; ?>"
               value="<?php echo substr($booking['start_time'], 0, 5); ?>">
      </div>
      <div class="col-6">
        <label class="form-label">Giờ kết thúc</label>
        <input type="time" name="end_time" class="form-control" required
               min="<?php echo $booking_start_time; ?>" max="<?php echo $booking_end_time; ?>"
               value="<?php echo substr($booking['end_time'], 0, 5); ?>">
      </div>
    </div>
    <div class="form-text mb-3">Chỉ nhận đặt sân trong khung giờ <?php echo $booking_start_time; ?> - <?php echo $booking_end_time; ?>.</div>

    <div class="mb-3">
      <label class="form-label">Ghi chú</label>
      <textarea name="notes" class="form-control" rows="2"><?php echo htmlspecialchars((string) $booking['notes']); ?></textarea>
    </div>

    <div class="mb-3">
      <label class="form-label d-block">Thanh toán</label>
      <div class="btn-group w-100" role="group">
        <input type="radio" class="btn-check" name="is_paid" id="paidNo" value="NO" <?php echo $booking['is_paid'] === 'YES' ? '' : 'checked'; ?> onchange="togglePaidFields()">
        <label class="btn btn-outline-secondary" for="paidNo">Chưa thanh toán</label>
        <input type="radio" class="btn-check" name="is_paid" id="paidYes" value="YES" <?php echo $booking['is_paid'] === 'YES' ? 'checked' : ''; ?> onchange="togglePaidFields()">
        <label class="btn btn-outline-secondary" for="paidYes">Đã thanh toán</label>
      </div>
    </div>

    <div id="paidFields" class="row g-2 mb-3 <?php echo $booking['is_paid'] === 'YES' ? '' : 'd-none'; ?>">
      <div class="col-6">
        <label class="form-label">Số order</label>
        <input type="text" name="payment_order_no" class="form-control" value="<?php echo htmlspecialchars((string) $booking['payment_order_no']); ?>">
      </div>
      <div class="col-6">
        <label class="form-label">Số tiền đã thu</label>
        <input type="number" step="1000" min="0" name="payment_amount" class="form-control" value="<?php echo $booking['payment_amount'] !== NULL ? (float) $booking['payment_amount'] : ''; ?>">
      </div>
    </div>
    <?php if ($booking['booking_group_id']): ?>
      <div class="form-text mt-n2 mb-3">Thông tin thanh toán chỉ áp dụng cho buổi này, không áp dụng cho cả chuỗi.</div>
    <?php endif; ?>

    <?php if ($booking['booking_group_id']): ?>
    <div class="mb-3">
      <label class="form-label d-block">Phạm vi cập nhật</label>
      <div class="btn-group w-100" role="group">
        <input type="radio" class="btn-check" name="scope" id="scopeSingle" value="single" checked>
        <label class="btn btn-outline-secondary" for="scopeSingle">Chỉ buổi này</label>
        <input type="radio" class="btn-check" name="scope" id="scopeGroup" value="group">
        <label class="btn btn-outline-secondary" for="scopeGroup">Toàn bộ chuỗi (<?php echo $group_count; ?> buổi)</label>
      </div>
      <div class="form-text">Chọn "Toàn bộ chuỗi" sẽ áp dụng sân/giờ/thông tin khách mới cho mọi buổi còn lại trong chuỗi lặp lại này (mỗi buổi vẫn giữ nguyên ngày riêng). Buổi nào bị trùng lịch với sân/giờ mới sẽ được giữ nguyên, không bị sửa.</div>
    </div>
    <?php endif; ?>

    <button class="btn btn-brand btn-lg w-100">Lưu thay đổi</button>
  <?php echo form_close(); ?>

  <div class="card border-0 shadow-sm rounded-4 mt-4">
    <div class="card-body">
      <h6 class="fw-bold mb-1"><i class="bi bi-journal-text"></i> Nhật ký ghi chú</h6>
      <div class="form-text mb-3">
        <?php echo $booking['booking_group_id'] ? 'Ghi chú dùng chung cho cả chuỗi lặp lại — thêm ở đây sẽ hiện ra khi xem bất kỳ buổi nào trong chuỗi.' : 'Ghi chú cho lịch đặt này.'; ?>
      </div>

      <?php if (empty($notes_log)): ?>
        <p class="text-muted small mb-3">Chưa có ghi chú nào.</p>
      <?php else: ?>
        <div class="mb-3" style="max-height:280px; overflow-y:auto;">
          <?php foreach ($notes_log as $n): ?>
            <div class="border-bottom pb-2 mb-2">
              <div class="small"><?php echo nl2br(htmlspecialchars($n['note'])); ?></div>
              <div class="text-muted" style="font-size:.75rem;">
                <?php echo htmlspecialchars($n['created_by_name'] ?: 'Hệ thống'); ?> · <?php echo date('d/m/Y H:i', strtotime($n['created_at'])); ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php echo form_open('bookings/'.$booking['id'].'/add-note', array('class' => 'd-flex gap-2')); ?>
        <input type="text" name="note" class="form-control" placeholder="Thêm ghi chú..." required maxlength="500">
        <button type="submit" class="btn btn-outline-brand flex-shrink-0"><i class="bi bi-plus-lg"></i> Thêm</button>
      <?php echo form_close(); ?>
    </div>
  </div>
</div>

<script>
function togglePaidFields(){
  var yes = document.getElementById('paidYes').checked;
  document.getElementById('paidFields').classList.toggle('d-none', !yes);
}
</script>
