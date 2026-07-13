<div class="container-fluid py-3 py-md-4">
  <?php $this->load->view('bookings/_nav'); ?>

  <?php
    $prev_month = date('Y-m-d', strtotime($month_start.' -1 month'));
    $next_month = date('Y-m-d', strtotime($month_start.' +1 month'));

    $first_weekday = (int) date('N', strtotime($month_start)); // 1=Mon .. 7=Sun
    $grid_start = date('Y-m-d', strtotime($month_start.' -'.($first_weekday - 1).' days'));

    $last_weekday = (int) date('N', strtotime($month_end));
    $grid_end = date('Y-m-d', strtotime($month_end.' +'.(7 - $last_weekday).' days'));

    $today = date('Y-m-d');
    $current_month_num = date('m', strtotime($month_start));

    $weekday_labels = array('T2','T3','T4','T5','T6','T7','CN');

    // Mỗi sân 1 màu riêng để phân biệt nhanh khi nhiều sân dồn chung 1 ngày.
    $court_palette = array('#0d6efd','#20c997','#fd7e14','#6f42c1','#d63384','#0dcaf0','#198754','#dc3545');
    $court_colors = array();
    foreach ($courts as $i => $c) { $court_colors[$c['id']] = $court_palette[$i % count($court_palette)]; }
  ?>

  <div class="d-flex align-items-center gap-2 mb-3">
    <a href="<?php echo site_url('bookings?view=month&date='.$prev_month); ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-chevron-left"></i></a>
    <span class="fw-semibold">Tháng <?php echo date('m/Y', strtotime($month_start)); ?></span>
    <a href="<?php echo site_url('bookings?view=month&date='.$next_month); ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-chevron-right"></i></a>
    <a href="<?php echo site_url('bookings?view=month&date='.date('Y-m-d')); ?>" class="btn btn-sm btn-outline-dark">Tháng này</a>
  </div>

  <div class="row g-1 mb-1">
    <?php foreach ($weekday_labels as $lbl): ?>
      <div class="col text-center small fw-semibold text-muted"><?php echo $lbl; ?></div>
    <?php endforeach; ?>
  </div>

  <?php
    $cursor = $grid_start;
    while (strtotime($cursor) <= strtotime($grid_end)):
  ?>
  <div class="row g-1 mb-1">
    <?php for ($i = 0; $i < 7; $i++): ?>
      <?php
        $day_bookings = isset($by_day[$cursor]) ? $by_day[$cursor] : array();
        $is_other_month = date('m', strtotime($cursor)) !== $current_month_num;
        $is_today = $cursor === $today;
      ?>
      <div class="col">
        <div class="month-day-cell rounded <?php echo $is_today ? 'is-today' : ''; ?> <?php echo $is_other_month ? 'is-other-month' : ''; ?>"
             onclick="location.href='<?php echo site_url('bookings'); ?>?view=day&date=<?php echo $cursor; ?>'">
          <div class="month-day-number"><?php echo date('j', strtotime($cursor)); ?></div>
          <?php
            // Mỗi sân 1 cột riêng trong ngày — không dồn chung 1 danh sách nữa,
            // nên 2 sân cùng giờ sẽ nằm cạnh nhau chứ không chồng lên nhau.
            $by_court_today = array();
            foreach ($day_bookings as $b) { $by_court_today[$b['table_id']][] = $b; }
          ?>
          <?php if ($by_court_today): ?>
          <div class="d-flex gap-1 mt-1">
            <?php foreach ($by_court_today as $table_id => $court_bookings): ?>
              <div class="month-court-col" style="border-top-color:<?php echo $court_colors[$table_id]; ?>;">
                <div class="month-court-label"><?php echo htmlspecialchars($court_bookings[0]['table_code']); ?></div>
                <?php foreach (array_slice($court_bookings, 0, 3) as $b): ?>
                  <div class="month-chip status-<?php echo strtolower($b['status']); ?>"
                       onclick="event.stopPropagation(); showBookingDetail(this);"
                       data-id="<?php echo $b['id']; ?>"
                       data-court="<?php echo htmlspecialchars($b['table_name'], ENT_QUOTES); ?>"
                       data-date="<?php echo date('d/m/Y', strtotime($b['booking_date'])); ?>"
                       data-start="<?php echo substr($b['start_time'],0,5); ?>"
                       data-end="<?php echo substr($b['end_time'],0,5); ?>"
                       data-customer="<?php echo htmlspecialchars($b['customer_name'], ENT_QUOTES); ?>"
                       data-phone="<?php echo htmlspecialchars($b['customer_phone'], ENT_QUOTES); ?>"
                       data-notes="<?php echo htmlspecialchars($b['notes'], ENT_QUOTES); ?>"
                       data-status="<?php echo $b['status']; ?>"
                       data-order-id="<?php echo $b['order_id']; ?>">
                    <?php echo substr($b['start_time'],0,5); ?>
                  </div>
                <?php endforeach; ?>
                <?php if (count($court_bookings) > 3): ?>
                  <div class="month-court-more">+<?php echo count($court_bookings) - 3; ?></div>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
      </div>
      <?php $cursor = date('Y-m-d', strtotime($cursor.' +1 day')); ?>
    <?php endfor; ?>
  </div>
  <?php endwhile; ?>

  <div class="small text-muted mt-2 d-flex flex-wrap align-items-center gap-3">
    <span>
      <span class="badge bg-primary">Đã đặt</span>
      <span class="badge bg-success">Đang chơi</span>
      <span class="badge bg-secondary">Hoàn tất</span>
    </span>
    <span class="d-flex flex-wrap align-items-center gap-2">
      <?php foreach ($courts as $c): ?>
        <span>
          <span style="display:inline-block;width:10px;height:10px;border-radius:2px;background:<?php echo $court_colors[$c['id']]; ?>;"></span>
          <?php echo htmlspecialchars($c['table_name']); ?>
        </span>
      <?php endforeach; ?>
    </span>
    <span>Bấm vào một ngày để xem chi tiết theo giờ</span>
  </div>
</div>

<?php $this->load->view('bookings/_modal'); ?>
