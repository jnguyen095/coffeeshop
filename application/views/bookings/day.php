<div class="container-fluid py-3 py-md-4">
  <?php $this->load->view('bookings/_nav'); ?>

  <?php
    $prev_date = date('Y-m-d', strtotime($date.' -1 day'));
    $next_date = date('Y-m-d', strtotime($date.' +1 day'));
    $total_minutes = ($day_end_hour - $day_start_hour) * 60;

    // Gom lịch đặt theo sân để dễ render từng cột.
    $by_table = array();
    foreach ($bookings as $b) { $by_table[$b['table_id']][] = $b; }
  ?>

  <div class="d-flex align-items-center gap-2 mb-3">
    <a href="<?php echo site_url('bookings?view=day&date='.$prev_date); ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-chevron-left"></i></a>
    <input type="date" value="<?php echo $date; ?>" class="form-control form-control-sm" style="max-width:180px;"
           onchange="location.href='<?php echo site_url('bookings'); ?>?view=day&date='+this.value">
    <a href="<?php echo site_url('bookings?view=day&date='.$next_date); ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-chevron-right"></i></a>
    <a href="<?php echo site_url('bookings?view=day&date='.date('Y-m-d')); ?>" class="btn btn-sm btn-outline-dark">Hôm nay</a>
    <span class="small text-muted ms-auto d-none d-md-inline">Bấm vào ô trống để đặt lịch nhanh, bấm vào lịch đã đặt để xem chi tiết</span>
  </div>

  <?php if (empty($courts)): ?>
    <div class="alert alert-warning">Chưa có sân nào được cấu hình. Vào <a href="<?php echo site_url('tables/manage'); ?>">Quản lý bàn</a> để thêm sân (loại "Sân pickleball").</div>
  <?php else: ?>

  <div class="calendar-day" style="--calendar-h: 900px;">
    <div class="calendar-gutter" style="height:900px;">
      <?php for ($h = $day_start_hour; $h <= $day_end_hour; $h++):
        $pct = (($h - $day_start_hour) * 60 / $total_minutes) * 100;
      ?>
        <div class="calendar-gutter-label" style="top:<?php echo $pct; ?>%;"><?php echo sprintf('%02d:00', $h); ?></div>
      <?php endfor; ?>
    </div>

    <div class="calendar-courts">
      <?php foreach ($courts as $c): ?>
      <div class="calendar-court-col">
        <div class="calendar-court-header"><?php echo htmlspecialchars($c['table_name']); ?></div>
        <div class="calendar-court-body" style="height:900px;" data-table-id="<?php echo $c['id']; ?>" onclick="handleEmptyClick(event, <?php echo $c['id']; ?>);">
          <?php for ($h = $day_start_hour; $h <= $day_end_hour; $h++):
            $pct = (($h - $day_start_hour) * 60 / $total_minutes) * 100;
          ?>
            <div class="calendar-hour-line" style="top:<?php echo $pct; ?>%;"></div>
          <?php endfor; ?>

          <?php foreach (isset($by_table[$c['id']]) ? $by_table[$c['id']] : array() as $b):
            $start_min = ((int) substr($b['start_time'],0,2) * 60 + (int) substr($b['start_time'],3,2)) - $day_start_hour * 60;
            $end_min   = ((int) substr($b['end_time'],0,2) * 60 + (int) substr($b['end_time'],3,2)) - $day_start_hour * 60;
            $top_pct = max(0, $start_min / $total_minutes * 100);
            $height_pct = max(2, ($end_min - $start_min) / $total_minutes * 100);
          ?>
            <div class="calendar-booking status-<?php echo strtolower($b['status']); ?>"
                 style="top:<?php echo $top_pct; ?>%; height:<?php echo $height_pct; ?>%;"
                 onclick="event.stopPropagation(); showBookingDetail(this);"
                 data-id="<?php echo $b['id']; ?>"
                 data-court="<?php echo htmlspecialchars($c['table_name'], ENT_QUOTES); ?>"
                 data-date="<?php echo date('d/m/Y', strtotime($b['booking_date'])); ?>"
                 data-start="<?php echo substr($b['start_time'],0,5); ?>"
                 data-end="<?php echo substr($b['end_time'],0,5); ?>"
                 data-customer="<?php echo htmlspecialchars($b['customer_name'], ENT_QUOTES); ?>"
                 data-phone="<?php echo htmlspecialchars($b['customer_phone'], ENT_QUOTES); ?>"
                 data-notes="<?php echo htmlspecialchars($b['notes'], ENT_QUOTES); ?>"
                 data-status="<?php echo $b['status']; ?>"
                 data-order-id="<?php echo $b['order_id']; ?>">
              <div class="calendar-booking-name"><?php echo substr($b['start_time'],0,5); ?>-<?php echo substr($b['end_time'],0,5); ?></div>
              <div><?php echo htmlspecialchars($b['customer_name']); ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="small text-muted mt-2">
    <span class="badge bg-primary">Đã đặt</span>
    <span class="badge bg-success">Đang chơi</span>
    <span class="badge bg-secondary">Hoàn tất</span>
  </div>
  <?php endif; ?>
</div>

<?php $this->load->view('bookings/_modal'); ?>

<script>
var DAY_START_HOUR = <?php echo $day_start_hour; ?>;
var DAY_END_HOUR = <?php echo $day_end_hour; ?>;
var CURRENT_DATE = '<?php echo $date; ?>';

function handleEmptyClick(evt, tableId){
  var rect = evt.currentTarget.getBoundingClientRect();
  var fraction = (evt.clientY - rect.top) / rect.height;
  var totalMinutes = (DAY_END_HOUR - DAY_START_HOUR) * 60;
  var minutes = DAY_START_HOUR * 60 + fraction * totalMinutes;
  minutes = Math.round(minutes / 30) * 30; // snap 30'
  var h = Math.floor(minutes / 60);
  var m = minutes % 60;
  var startTime = (h < 10 ? '0' : '') + h + ':' + (m < 10 ? '0' : '') + m;
  location.href = '<?php echo site_url('bookings/create'); ?>?table_id=' + tableId + '&date_from=' + CURRENT_DATE + '&start_time=' + startTime;
}
</script>
