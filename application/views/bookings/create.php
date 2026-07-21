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
      <select name="table_id" id="tableSelect" class="form-select form-select-lg" required onchange="updateEstimate()">
        <?php foreach ($courts as $c): ?>
          <option value="<?php echo $c['id']; ?>" <?php echo ((string) $prefill_table === (string) $c['id']) ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($c['table_name']); ?>
            (Sáng <?php echo money_format_vnd($c['rate_morning']); ?> / Chiều <?php echo money_format_vnd($c['rate_afternoon']); ?> / Tối <?php echo money_format_vnd($c['rate_evening']); ?>)
          </option>
        <?php endforeach; ?>
      </select>
      <div class="form-check mt-2">
        <input class="form-check-input" type="checkbox" name="auto_assign" value="1" id="autoAssign" onchange="toggleAutoAssign(); updateEstimate();">
        <label class="form-check-label" for="autoAssign">Tự động chọn sân còn trống</label>
      </div>
      <div class="form-text">Hệ thống sẽ tự tìm một sân còn trống trong khung giờ đã chọn, không cần chọn sân cụ thể ở trên.</div>
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
        <input type="time" name="start_time" id="startTime" class="form-control" required
               min="<?php echo $booking_start_time; ?>" max="<?php echo $booking_end_time; ?>"
               value="<?php echo $prefill_start ?: '18:00'; ?>" onchange="updateEstimate()">
      </div>
      <div class="col-6">
        <label class="form-label">Giờ kết thúc</label>
        <input type="time" name="end_time" id="endTime" class="form-control" required
               min="<?php echo $booking_start_time; ?>" max="<?php echo $booking_end_time; ?>"
               value="19:00" onchange="updateEstimate()">
      </div>
    </div>
    <div class="form-text mb-2">Chỉ nhận đặt sân trong khung giờ <?php echo $booking_start_time; ?> - <?php echo $booking_end_time; ?>.</div>

    <div class="alert alert-light border mt-3 mb-0 py-2 small" id="estimateBox">
      <i class="bi bi-cash-coin text-brand"></i> Ước tính mỗi buổi: <strong class="text-brand" id="estimatePerSession">0đ</strong>
      <div id="estimateTotalWrap" class="d-none">Tổng ước tính (<span id="estimateCount">0</span> buổi): <strong class="text-brand" id="estimateTotal">0đ</strong></div>
    </div>

    <div class="mb-3 mt-3">
      <label class="form-label d-block">Lặp lại</label>
      <div class="btn-group w-100" role="group">
        <input type="radio" class="btn-check" name="repeat" id="repeatNone" value="none" checked onchange="toggleRepeat(); updateEstimate();">
        <label class="btn btn-outline-secondary" for="repeatNone">Không lặp</label>
        <input type="radio" class="btn-check" name="repeat" id="repeatWeekly" value="weekly" onchange="toggleRepeat(); updateEstimate();">
        <label class="btn btn-outline-secondary" for="repeatWeekly">Theo tuần</label>
        <input type="radio" class="btn-check" name="repeat" id="repeatMonthly" value="monthly" onchange="toggleRepeat(); updateEstimate();">
        <label class="btn btn-outline-secondary" for="repeatMonthly">Theo tháng</label>
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Ngày <span id="dateFromLabel">đặt</span></label>
      <input type="date" name="date_from" id="dateFrom" class="form-control" required value="<?php echo $prefill_date; ?>" onchange="autoFillDateTo(); updateEstimate();">
    </div>

    <div id="recurringFields" class="d-none">
      <div class="mb-3">
        <label class="form-label d-block">Lặp vào các thứ</label>
        <div class="d-flex flex-wrap gap-2">
          <?php $wd = array(1=>'T2',2=>'T3',3=>'T4',4=>'T5',5=>'T6',6=>'T7',7=>'CN'); foreach ($wd as $num => $label): ?>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="weekdays[]" value="<?php echo $num; ?>" id="wd<?php echo $num; ?>" onchange="updateEstimate()">
            <label class="form-check-label" for="wd<?php echo $num; ?>"><?php echo $label; ?></label>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label">Lặp đến ngày</label>
        <input type="date" name="date_to" id="dateTo" class="form-control" onchange="updateEstimate()">
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
function toggleAutoAssign(){
  var auto = document.getElementById('autoAssign').checked;
  var select = document.getElementById('tableSelect');
  select.disabled = auto;
  select.required = !auto;
}
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

var COURT_RATES = {
  <?php foreach ($courts as $c): ?>
  <?php echo $c['id']; ?>: {morning: <?php echo (float) $c['rate_morning']; ?>, afternoon: <?php echo (float) $c['rate_afternoon']; ?>, evening: <?php echo (float) $c['rate_evening']; ?>},
  <?php endforeach; ?>
};
var SLOTS = {
  morning:   {start: 6*60,  end: 12*60},
  afternoon: {start: 12*60, end: 18*60},
  evening:   {start: 18*60, end: 23*60}
};

function timeToMinutes(t){
  var parts = t.split(':');
  return parseInt(parts[0], 10) * 60 + parseInt(parts[1], 10);
}

function fmtMoney(n){ return Math.round(n).toLocaleString('vi-VN') + 'đ'; }

function calcFee(rates, startTime, endTime){
  var start = timeToMinutes(startTime);
  var end = timeToMinutes(endTime);
  var fee = 0;
  for (var key in SLOTS){
    var slot = SLOTS[key];
    var overlap = Math.max(0, Math.min(end, slot.end) - Math.max(start, slot.start));
    fee += (overlap / 60) * rates[key];
  }
  return Math.round(fee);
}

function countOccurrences(weekdays, dateFrom, dateTo){
  if (!weekdays.length || !dateFrom || !dateTo) return 0;
  var count = 0;
  var cursor = new Date(dateFrom + 'T00:00:00');
  var end = new Date(dateTo + 'T00:00:00');
  while (cursor <= end){
    var dow = cursor.getDay() === 0 ? 7 : cursor.getDay(); // 1=Mon..7=Sun, giống PHP date('N')
    if (weekdays.indexOf(dow) !== -1) count++;
    cursor.setDate(cursor.getDate() + 1);
  }
  return count;
}

function updateEstimate(){
  var startTime = document.getElementById('startTime').value;
  var endTime = document.getElementById('endTime').value;

  if (document.getElementById('autoAssign').checked){
    document.getElementById('estimatePerSession').textContent = 'Tùy sân được tự động chọn';
    document.getElementById('estimateTotalWrap').classList.add('d-none');
    return;
  }

  var tableId = document.getElementById('tableSelect').value;
  var rates = COURT_RATES[tableId];
  if (!rates || !startTime || !endTime || endTime <= startTime){
    document.getElementById('estimatePerSession').textContent = '0đ';
    document.getElementById('estimateTotalWrap').classList.add('d-none');
    return;
  }

  var perSession = calcFee(rates, startTime, endTime);
  document.getElementById('estimatePerSession').textContent = fmtMoney(perSession);

  var repeat = document.querySelector('input[name="repeat"]:checked').value;
  if (repeat === 'none'){
    document.getElementById('estimateTotalWrap').classList.add('d-none');
    return;
  }

  var weekdays = [];
  document.querySelectorAll('input[name="weekdays[]"]:checked').forEach(function(cb){ weekdays.push(parseInt(cb.value, 10)); });
  var dateFrom = document.getElementById('dateFrom').value;
  var dateTo = document.getElementById('dateTo').value;
  var count = countOccurrences(weekdays, dateFrom, dateTo);

  document.getElementById('estimateCount').textContent = count;
  document.getElementById('estimateTotal').textContent = fmtMoney(perSession * count);
  document.getElementById('estimateTotalWrap').classList.remove('d-none');
}

updateEstimate();
</script>
