<div class="container-fluid py-3 py-md-4">
  <?php $this->load->view('bookings/_nav'); ?>

  <?php
    $prev_week = date('Y-m-d', strtotime($week_start.' -7 days'));
    $next_week = date('Y-m-d', strtotime($week_start.' +7 days'));

    $days = array();
    for ($i = 0; $i < 7; $i++) $days[] = date('Y-m-d', strtotime($week_start.' +'.$i.' days'));

    // Gom theo [table_id][date] => [bookings...]
    $grid = array();
    foreach ($bookings as $b) { $grid[$b['table_id']][$b['booking_date']][] = $b; }

    $weekday_labels = array('Thứ 2','Thứ 3','Thứ 4','Thứ 5','Thứ 6','Thứ 7','Chủ nhật');
    $search_qs = ($search !== '') ? '&search='.urlencode($search) : '';
  ?>

  <div class="d-flex align-items-center gap-2 mb-3">
    <a href="<?php echo site_url('bookings?view=week&date='.$prev_week.$search_qs); ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-chevron-left"></i></a>
    <span class="fw-semibold small"><?php echo date('d/m', strtotime($week_start)); ?> - <?php echo date('d/m/Y', strtotime($week_end)); ?></span>
    <a href="<?php echo site_url('bookings?view=week&date='.$next_week.$search_qs); ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-chevron-right"></i></a>
    <a href="<?php echo site_url('bookings?view=week&date='.date('Y-m-d').$search_qs); ?>" class="btn btn-sm btn-outline-dark">Tuần này</a>
  </div>

  <?php if (empty($courts)): ?>
    <div class="alert alert-warning">Chưa có sân nào được cấu hình.</div>
  <?php else: ?>
  <div class="table-responsive">
    <table class="table table-bordered bg-white" style="min-width:900px;">
      <thead class="table-light">
        <tr>
          <th style="width:100px;">Sân</th>
          <?php foreach ($days as $i => $d): ?>
            <th class="text-center <?php echo $d === date('Y-m-d') ? 'bg-brand text-white' : ''; ?>">
              <?php echo $weekday_labels[$i]; ?><br><span class="small"><?php echo date('d/m', strtotime($d)); ?></span>
            </th>
          <?php endforeach; ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($courts as $c): ?>
        <tr class="week-court-row">
          <td class="fw-semibold"><?php echo htmlspecialchars($c['table_name']); ?></td>
          <?php foreach ($days as $d): ?>
            <td onclick="location.href='<?php echo site_url('bookings/create'); ?>?table_id=<?php echo $c['id']; ?>&date_from=<?php echo $d; ?>'" style="cursor:pointer;">
              <?php foreach (isset($grid[$c['id']][$d]) ? $grid[$c['id']][$d] : array() as $b): ?>
                <span class="week-chip status-<?php echo strtolower($b['status']); ?>"
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
                      data-order-id="<?php echo $b['order_id']; ?>"
                      data-fee="<?php echo (int) $b['estimated_fee']; ?>">
                  <div class="week-chip-line"><?php echo substr($b['start_time'],0,5); ?>-<?php echo substr($b['end_time'],0,5); ?> <?php echo htmlspecialchars(mb_strimwidth($b['customer_name'], 0, 10, '…')); ?></div>
                  <?php if ( ! empty($b['customer_phone'])): ?>
                    <div class="week-chip-line week-chip-phone"><?php echo htmlspecialchars($b['customer_phone']); ?></div>
                  <?php endif; ?>
                </span>
              <?php endforeach; ?>
            </td>
          <?php endforeach; ?>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="small text-muted mt-2">
    <span class="badge bg-primary">Đã đặt</span>
    <span class="badge bg-success">Đang chơi</span>
    <span class="badge bg-secondary">Hoàn tất</span>
    — Bấm vào ô trống để đặt lịch nhanh cho ngày đó
  </div>
  <?php endif; ?>
</div>

<?php $this->load->view('bookings/_modal'); ?>
