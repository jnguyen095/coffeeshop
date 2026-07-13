<div class="container py-3 py-md-4" style="max-width:560px;">
  <a href="<?php echo site_url('bookings'); ?>" class="btn btn-sm btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Lịch đặt sân</a>
  <h4 class="fw-bold mb-3"><i class="bi bi-calendar-plus"></i> Đặt lịch sân</h4>

  <?php if ($error): ?>
    <div class="alert alert-danger py-2 small"><?php echo $error; ?></div>
  <?php endif; ?>

  <?php if ($result): ?>
    <div class="alert alert-success">
      Đã tạo <strong><?php echo count($result['created']); ?></strong> buổi.
      <?php if ($result['created']): ?><div class="small mt-1"><?php echo implode(', ', array_map(function($d){ return date('d/m', strtotime($d)); }, $result['created'])); ?></div><?php endif; ?>
    </div>
    <?php if ($result['skipped']): ?>
      <div class="alert alert-warning">
        Bỏ qua <strong><?php echo count($result['skipped']); ?></strong> buổi do trùng lịch:
        <div class="small mt-1"><?php echo implode(', ', array_map(function($d){ return date('d/m', strtotime($d)); }, $result['skipped'])); ?></div>
      </div>
    <?php endif; ?>
    <a href="<?php echo site_url('bookings'); ?>" class="btn btn-brand w-100 mb-3">Xem lịch</a>
  <?php endif; ?>

  <?php if (empty($courts)): ?>
    <div class="alert alert-warning">Chưa có sân nào được cấu hình. Vào <a href="<?php echo site_url('tables/manage'); ?>">Quản lý bàn</a> để thêm sân (loại "Sân pickleball").</div>
  <?php else: ?>
  <?php echo form_open(current_url(), array('id' => 'bookingForm')); ?>
    <div class="mb-3">
      <label class="form-label">Sân</label>
      <select name="table_id" class="form-select form-select-lg" required>
        <?php foreach ($courts as $c): ?>
          <option value="<?php echo $c['id']; ?>" <?php echo ((string) $prefill_table === (string) $c['id']) ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($c['table_name']); ?>
            (Sáng <?php echo money_format_vnd($c['rate_morning']); ?> / Chiều <?php echo money_format_vnd($c['rate_afternoon']); ?> / Tối <?php echo money_format_vnd($c['rate_evening']); ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="row g-2">
      <div class="col-6">
        <label class="form-label">Tên khách</label>
        <input type="text" name="customer_name" class="form-control" required>
      </div>
      <div class="col-6">
        <label class="form-label">Số điện thoại</label>
        <input type="tel" name="customer_phone" class="form-control">
      </div>
    </div>
    <div class="row g-2 mt-1">
      <div class="col-6">
        <label class="form-label">Giờ bắt đầu</label>
        <input type="time" name="start_time" id="startTime" class="form-control" required value="<?php echo $prefill_start ?: '18:00'; ?>">
      </div>
      <div class="col-6">
        <label class="form-label">Giờ kết thúc</label>
        <input type="time" name="end_time" id="endTime" class="form-control" required value="19:00">
      </div>
    </div>

    <div class="mb-3 mt-3">
      <label class="form-label d-block">Lặp lại</label>
      <div class="btn-group w-100" role="group">
        <input type="radio" class="btn-check" name="repeat" id="repeatNone" value="none" checked onchange="toggleRepeat()">
        <label class="btn btn-outline-secondary" for="repeatNone">Không lặp</label>
        <input type="radio" class="btn-check" name="repeat" id="repeatWeekly" value="weekly" onchange="toggleRepeat()">
        <label class="btn btn-outline-secondary" for="repeatWeekly">Theo tuần</label>
        <input type="radio" class="btn-check" name="repeat" id="repeatMonthly" value="monthly" onchange="toggleRepeat()">
        <label class="btn btn-outline-secondary" for="repeatMonthly">Theo tháng</label>
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Ngày <span id="dateFromLabel">đặt</span></label>
      <input type="date" name="date_from" id="dateFrom" class="form-control" required value="<?php echo $prefill_date; ?>" onchange="autoFillDateTo()">
    </div>

    <div id="recurringFields" class="d-none">
      <div class="mb-3">
        <label class="form-label d-block">Lặp vào các thứ</label>
        <div class="d-flex flex-wrap gap-2">
          <?php $wd = array(1=>'T2',2=>'T3',3=>'T4',4=>'T5',5=>'T6',6=>'T7',7=>'CN'); foreach ($wd as $num => $label): ?>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="weekdays[]" value="<?php echo $num; ?>" id="wd<?php echo $num; ?>">
            <label class="form-check-label" for="wd<?php echo $num; ?>"><?php echo $label; ?></label>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label">Lặp đến ngày</label>
        <input type="date" name="date_to" id="dateTo" class="form-control">
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Ghi chú</label>
      <textarea name="notes" class="form-control" rows="2"></textarea>
    </div>

    <button class="btn btn-brand btn-lg w-100">Đặt lịch</button>
  <?php echo form_close(); ?>
  <?php endif; ?>
</div>

<script>
function toggleRepeat(){
  var repeat = document.querySelector('input[name="repeat"]:checked').value;
  var showRecurring = repeat !== 'none';
  document.getElementById('recurringFields').classList.toggle('d-none', !showRecurring);
  document.getElementById('dateFromLabel').textContent = showRecurring ? 'bắt đầu' : 'đặt';
  if (showRecurring) autoFillDateTo();
}
function autoFillDateTo(){
  var repeat = document.querySelector('input[name="repeat"]:checked').value;
  if (repeat === 'none') return;
  var from = document.getElementById('dateFrom').value;
  if (!from) return;
  var d = new Date(from);
  if (repeat === 'weekly') d.setDate(d.getDate() + 6);
  else d.setMonth(d.getMonth() + 1);
  document.getElementById('dateTo').value = d.toISOString().slice(0,10);
}
</script>
