<div class="container-fluid py-3 py-md-4" style="max-width:1100px;">
  <a href="<?php echo site_url('reports'); ?>" class="btn btn-sm btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Báo cáo</a>
  <h4 class="fw-bold mb-3"><i class="bi bi-dribbble text-brand"></i> Hiệu suất sân pickleball</h4>

  <?php echo form_open('reports/court-performance', array('method' => 'get', 'class' => 'row g-2 mb-3')); ?>
    <div class="col-auto"><input type="date" name="from" value="<?php echo $from; ?>" class="form-control"></div>
    <div class="col-auto"><input type="date" name="to" value="<?php echo $to; ?>" class="form-control"></div>
    <div class="col-auto"><button class="btn btn-brand">Xem</button></div>
  <?php echo form_close(); ?>

  <?php if ( ! $has_courts): ?>
    <div class="alert alert-warning">Chưa có sân nào được cấu hình. Vào <a href="<?php echo site_url('tables/manage'); ?>">Quản lý bàn</a> để thêm sân (loại "Sân pickleball").</div>
  <?php else: ?>

  <?php
    $total_revenue = 0;
    foreach ($revenue_by_court as $r) { $total_revenue += (float) $r['total_revenue']; }

    $total_bookings = 0;
    $status_label = array('BOOKED' => 'Đã đặt', 'CHECKED_IN' => 'Đang chơi', 'COMPLETED' => 'Hoàn tất', 'CANCELLED' => 'Đã hủy', 'NO_SHOW' => 'Không đến');
    foreach ($bookings_by_status as $s) { $total_bookings += (int) $s['total']; }

    $avg_utilization = 0;
    if ($utilization)
    {
        $sum_pct = 0;
        foreach ($utilization as $u) { $sum_pct += $u['utilization_pct']; }
        $avg_utilization = round($sum_pct / count($utilization), 1);
    }

    $total_played_hours = round((array_sum($usage_by_slot)) / 60, 1);
  ?>

  <div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm rounded-4 h-100">
        <div class="card-body text-center">
          <i class="bi bi-cash-stack text-success fs-2"></i>
          <div class="fs-5 fw-bold mt-1"><?php echo money_format_vnd($total_revenue); ?></div>
          <div class="text-muted small">Doanh thu dịch vụ sân</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm rounded-4 h-100">
        <div class="card-body text-center">
          <i class="bi bi-calendar-check text-brand fs-2"></i>
          <div class="fs-5 fw-bold mt-1"><?php echo $total_bookings; ?></div>
          <div class="text-muted small">Tổng số lượt đặt</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm rounded-4 h-100">
        <div class="card-body text-center">
          <i class="bi bi-speedometer2 text-warning fs-2"></i>
          <div class="fs-5 fw-bold mt-1"><?php echo $avg_utilization; ?>%</div>
          <div class="text-muted small">Tỷ lệ lấp đầy trung bình</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm rounded-4 h-100">
        <div class="card-body text-center">
          <i class="bi bi-clock-history text-info fs-2"></i>
          <div class="fs-5 fw-bold mt-1"><?php echo $total_played_hours; ?> giờ</div>
          <div class="text-muted small">Tổng giờ chơi thực tế</div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-lg-6">
      <div class="card border-0 shadow-sm rounded-4 h-100">
        <div class="card-header bg-white fw-semibold">Doanh thu theo sân</div>
        <div class="card-body"><canvas id="chartRevenueByCourt" height="220"></canvas></div>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="card border-0 shadow-sm rounded-4 h-100">
        <div class="card-header bg-white fw-semibold">Xu hướng doanh thu theo ngày</div>
        <div class="card-body"><canvas id="chartRevenueTrend" height="220"></canvas></div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="card border-0 shadow-sm rounded-4 h-100">
        <div class="card-header bg-white fw-semibold">Trạng thái lượt đặt</div>
        <div class="card-body"><canvas id="chartBookingStatus" height="220"></canvas></div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="card border-0 shadow-sm rounded-4 h-100">
        <div class="card-header bg-white fw-semibold">Giờ chơi theo khung giờ</div>
        <div class="card-body"><canvas id="chartUsageBySlot" height="220"></canvas></div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="card border-0 shadow-sm rounded-4 h-100">
        <div class="card-header bg-white fw-semibold">Tỷ lệ lấp đầy từng sân</div>
        <div class="card-body"><canvas id="chartUtilization" height="220"></canvas></div>
      </div>
    </div>
  </div>

  <?php endif; ?>
</div>

<?php if ($has_courts): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
var BRAND = '#6f4e37';
var PALETTE = ['#6f4e37', '#0d6efd', '#20c997', '#fd7e14', '#6f42c1', '#d63384', '#0dcaf0', '#198754'];

var revenueByCourt = <?php echo json_encode($revenue_by_court, JSON_UNESCAPED_UNICODE); ?>;
new Chart(document.getElementById('chartRevenueByCourt'), {
  type: 'bar',
  data: {
    labels: revenueByCourt.map(function(r){ return r.table_name; }),
    datasets: [{ label: 'Doanh thu', data: revenueByCourt.map(function(r){ return parseFloat(r.total_revenue); }), backgroundColor: BRAND }]
  },
  options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});

var revenueTrend = <?php echo json_encode($revenue_trend, JSON_UNESCAPED_UNICODE); ?>;
new Chart(document.getElementById('chartRevenueTrend'), {
  type: 'line',
  data: {
    labels: revenueTrend.map(function(r){ return r.day; }),
    datasets: [{ label: 'Doanh thu', data: revenueTrend.map(function(r){ return parseFloat(r.total_revenue); }), borderColor: BRAND, backgroundColor: 'rgba(111,78,55,.15)', tension: .3, fill: true }]
  },
  options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});

var statusLabel = <?php echo json_encode($status_label, JSON_UNESCAPED_UNICODE); ?>;
var bookingsByStatus = <?php echo json_encode($bookings_by_status, JSON_UNESCAPED_UNICODE); ?>;
new Chart(document.getElementById('chartBookingStatus'), {
  type: 'doughnut',
  data: {
    labels: bookingsByStatus.map(function(s){ return statusLabel[s.status] || s.status; }),
    datasets: [{ data: bookingsByStatus.map(function(s){ return parseInt(s.total, 10); }), backgroundColor: PALETTE }]
  },
  options: { plugins: { legend: { position: 'bottom' } } }
});

var usageBySlot = <?php echo json_encode($usage_by_slot, JSON_UNESCAPED_UNICODE); ?>;
new Chart(document.getElementById('chartUsageBySlot'), {
  type: 'pie',
  data: {
    labels: ['Sáng (6h-12h)', 'Chiều (12h-18h)', 'Tối (18h-23h)'],
    datasets: [{ data: [usageBySlot.morning/60, usageBySlot.afternoon/60, usageBySlot.evening/60].map(function(h){ return Math.round(h*10)/10; }), backgroundColor: [PALETTE[1], PALETTE[3], PALETTE[4]] }]
  },
  options: { plugins: { legend: { position: 'bottom' }, tooltip: { callbacks: { label: function(ctx){ return ctx.label + ': ' + ctx.parsed + ' giờ'; } } } } }
});

var utilization = <?php echo json_encode($utilization, JSON_UNESCAPED_UNICODE); ?>;
new Chart(document.getElementById('chartUtilization'), {
  type: 'bar',
  data: {
    labels: utilization.map(function(u){ return u.table_name; }),
    datasets: [{ label: 'Tỷ lệ lấp đầy (%)', data: utilization.map(function(u){ return u.utilization_pct; }), backgroundColor: PALETTE[2] }]
  },
  options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, max: 100 } } }
});
</script>
<?php endif; ?>
