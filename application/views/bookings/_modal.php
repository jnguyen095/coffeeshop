<!-- Chi tiết lịch đặt (dùng chung cho cả 3 kiểu xem ngày/tuần/tháng) -->
<div class="modal fade" id="bookingModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalCourtName"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="fw-bold fs-5 mb-2" id="modalTime"></div>
        <div class="mb-1"><i class="bi bi-person"></i> <span id="modalCustomer"></span></div>
        <div class="mb-1"><i class="bi bi-telephone"></i> <span id="modalPhone"></span></div>
        <div class="mb-2 text-muted small" id="modalNotes"></div>
        <span class="badge" id="modalStatus"></span>
      </div>
      <div class="modal-footer" id="modalActions"></div>
    </div>
  </div>
</div>

<form id="checkinForm" method="post" class="d-none">
  <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
</form>
<form id="cancelForm" method="post" class="d-none">
  <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
</form>

<script>
var STATUS_LABEL = {BOOKED:'Đã đặt', CHECKED_IN:'Đang chơi', COMPLETED:'Hoàn tất', CANCELLED:'Đã hủy', NO_SHOW:'Không đến'};
var STATUS_COLOR = {BOOKED:'primary', CHECKED_IN:'success', COMPLETED:'secondary', CANCELLED:'secondary', NO_SHOW:'danger'};

function showBookingDetail(el){
  var d = el.dataset;
  document.getElementById('modalCourtName').textContent = d.court;
  document.getElementById('modalTime').textContent = d.date + '  ' + d.start + ' - ' + d.end;
  document.getElementById('modalCustomer').textContent = d.customer || '—';
  document.getElementById('modalPhone').textContent = d.phone || '—';
  document.getElementById('modalNotes').textContent = d.notes || '';
  var statusEl = document.getElementById('modalStatus');
  statusEl.textContent = STATUS_LABEL[d.status] || d.status;
  statusEl.className = 'badge bg-' + (STATUS_COLOR[d.status] || 'secondary');

  var actions = '';
  if (d.status === 'BOOKED'){
    actions += '<button type="button" class="btn btn-success" onclick="submitBookingAction(\'checkinForm\',' + d.id + ')">Check-in</button>';
    actions += '<button type="button" class="btn btn-outline-danger" onclick="if(confirm(\'Hủy lịch đặt này?\')) submitBookingAction(\'cancelForm\',' + d.id + ')">Hủy lịch</button>';
  } else if (d.status === 'CHECKED_IN' && d.orderId){
    actions += '<a href="<?php echo site_url('orders'); ?>/' + d.orderId + '" class="btn btn-outline-primary">Xem đơn</a>';
  }
  document.getElementById('modalActions').innerHTML = actions;

  bootstrap.Modal.getOrCreateInstance(document.getElementById('bookingModal')).show();
}

function submitBookingAction(formId, bookingId){
  var form = document.getElementById(formId);
  form.action = '<?php echo site_url('bookings'); ?>/' + bookingId + '/' + (formId === 'checkinForm' ? 'checkin' : 'cancel');
  form.submit();
}
</script>
