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
        <div class="mb-2"><i class="bi bi-cash-coin"></i> Tiền sân ước tính: <strong class="text-brand" id="modalFee"></strong></div>
        <div class="mb-2 small" id="modalPayment"></div>
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
<form id="cancelGroupForm" method="post" class="d-none">
  <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
</form>

<script>
var STATUS_LABEL = {BOOKED:'Đã đặt', CHECKED_IN:'Đang chơi', COMPLETED:'Hoàn tất', CANCELLED:'Đã hủy', NO_SHOW:'Không đến'};
var STATUS_COLOR = {BOOKED:'primary', CHECKED_IN:'success', COMPLETED:'secondary', CANCELLED:'secondary', NO_SHOW:'danger'};

function fmtMoney(n){ return Math.round(n).toLocaleString('vi-VN') + 'đ'; }

// Vai trò BOOKING chỉ quản lý lịch đặt sân, không được check-in (mở bàn/đơn hàng) hay xem đơn.
var CAN_MANAGE_ORDER = <?php echo ($current_user['role'] !== 'BOOKING') ? 'true' : 'false'; ?>;

function showBookingDetail(el){
  var d = el.dataset;
  document.getElementById('modalCourtName').textContent = d.court;
  document.getElementById('modalTime').textContent = d.date + '  ' + d.start + ' - ' + d.end;
  document.getElementById('modalCustomer').textContent = d.customer || '—';
  document.getElementById('modalPhone').textContent = d.phone || '—';
  document.getElementById('modalNotes').textContent = d.notes || '';
  document.getElementById('modalFee').textContent = fmtMoney(d.fee || 0);

  var paymentEl = document.getElementById('modalPayment');
  if (d.isPaid === 'YES'){
    var parts = ['<span class="badge bg-success">Đã thanh toán</span>'];
    if (d.paymentAmount) parts.push(fmtMoney(d.paymentAmount));
    if (d.paymentOrderNo) parts.push('Order #' + d.paymentOrderNo);
    paymentEl.innerHTML = parts.join(' · ');
  } else {
    paymentEl.innerHTML = '<span class="badge bg-warning text-dark">Chưa thanh toán</span>';
  }

  var statusEl = document.getElementById('modalStatus');
  statusEl.textContent = STATUS_LABEL[d.status] || d.status;
  statusEl.className = 'badge bg-' + (STATUS_COLOR[d.status] || 'secondary');

  var actions = '';
  if (d.status === 'BOOKED'){
    if (CAN_MANAGE_ORDER){
      actions += '<button type="button" class="btn btn-success" onclick="submitBookingAction(\'checkinForm\',' + d.id + ')">Check-in</button>';
    }
    actions += '<a href="<?php echo site_url('bookings'); ?>/' + d.id + '/edit" class="btn btn-outline-secondary">Sửa</a>';
    if (d.group){
      actions += '<div class="btn-group">' +
        '<button type="button" class="btn btn-outline-danger dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">Hủy lịch</button>' +
        '<ul class="dropdown-menu dropdown-menu-end">' +
          '<li><a class="dropdown-item" href="#" onclick="event.preventDefault(); if(confirm(\'Hủy buổi này?\')) submitBookingAction(\'cancelForm\',' + d.id + ');">Chỉ buổi này</a></li>' +
          '<li><a class="dropdown-item" href="#" onclick="event.preventDefault(); if(confirm(\'Hủy buổi này và mọi buổi sau nó trong chuỗi?\')) submitCancelScoped(\'onward\',' + d.id + ');">Buổi này trở về sau</a></li>' +
          '<li><a class="dropdown-item" href="#" onclick="event.preventDefault(); if(confirm(\'Hủy buổi này và mọi buổi trước nó trong chuỗi?\')) submitCancelScoped(\'backward\',' + d.id + ');">Buổi này trở về trước</a></li>' +
          '<li><hr class="dropdown-divider"></li>' +
          '<li><a class="dropdown-item text-danger" href="#" onclick="event.preventDefault(); if(confirm(\'Hủy toàn bộ chuỗi lặp lại này?\')) submitGroupCancel(\'' + d.group + '\');">Toàn bộ chuỗi</a></li>' +
        '</ul>' +
      '</div>';
    } else {
      actions += '<button type="button" class="btn btn-outline-danger" onclick="if(confirm(\'Hủy lịch đặt này?\')) submitBookingAction(\'cancelForm\',' + d.id + ')">Hủy lịch</button>';
    }
  } else if (d.status === 'CHECKED_IN' && d.orderId && CAN_MANAGE_ORDER){
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

function submitGroupCancel(groupId){
  var form = document.getElementById('cancelGroupForm');
  form.action = '<?php echo site_url('bookings/group'); ?>/' + groupId + '/cancel';
  form.submit();
}

function submitCancelScoped(scope, bookingId){
  var form = document.getElementById('cancelForm');
  form.action = '<?php echo site_url('bookings'); ?>/' + bookingId + '/cancel-' + scope;
  form.submit();
}
</script>
