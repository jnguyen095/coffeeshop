<div class="container-fluid py-3 py-md-4">
  <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
    <div>
      <h4 class="fw-bold mb-0">
        <?php if ($order['order_type'] === 'TAKEAWAY'): ?>
          <i class="bi bi-bag-check text-brand"></i> Mang đi
        <?php else: ?>
          <?php echo htmlspecialchars($order['table_name']); ?>
        <?php endif; ?>
        <span class="text-muted fs-6">#<?php echo htmlspecialchars($order['order_no']); ?></span>
      </h4>
      <span class="badge bg-<?php echo order_status_badge($order['status']); ?>"><?php echo $order['status']; ?></span>
    </div>
    <div class="btn-group btn-group-sm flex-wrap">
      <?php if ($order['order_type'] === 'TAKEAWAY'): ?>
        <?php if ($order['status'] === 'OPEN'): ?>
        <?php echo form_open('orders/'.$order['id'].'/checkout', array('class' => 'd-inline')); ?>
          <button class="btn btn-brand"><i class="bi bi-cash-coin"></i> Thanh toán</button>
        <?php echo form_close(); ?>
        <?php else: ?>
        <a href="<?php echo site_url('cashier/'.$order['id']); ?>" class="btn btn-brand"><i class="bi bi-cash-coin"></i> Thu ngân</a>
        <?php endif; ?>
        <a href="<?php echo site_url('takeaway/create'); ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Bán mang đi</a>
      <?php else: ?>
        <a href="<?php echo site_url('tables/'.$order['table_id'].'/transfer'); ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left-right"></i> Chuyển bàn</a>
        <a href="<?php echo site_url('tables/'.$order['table_id'].'/merge'); ?>" class="btn btn-outline-secondary"><i class="bi bi-union"></i> Gộp bàn</a>
        <a href="<?php echo site_url('tables/'.$order['table_id'].'/print-provisional'); ?>" target="_blank" class="btn btn-outline-dark"><i class="bi bi-printer"></i> In tạm tính</a>
        <a href="<?php echo site_url('tables'); ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Sơ đồ bàn</a>
      <?php endif; ?>
    </div>
  </div>

  <?php
    // Tách riêng dịch vụ sân (thuê vợt, thuê trang phục...) khỏi món ăn/uống thường
    // để hiển thị thành một khối riêng — chúng không qua bếp nên không lẫn vào đây.
    $court_items = array();
    $regular_items = array();
    foreach ($items as $it)
    {
        if ($it['court_only']) { $court_items[] = $it; } else { $regular_items[] = $it; }
    }
  ?>

  <div class="row g-3">
    <div class="col-lg-7">
    <?php if ($court_items): ?>
        <div class="card border-0 shadow-sm rounded-4 mb-3">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-cash-coin text-brand"></i> Dịch vụ sân</div>
            <div class="list-group list-group-flush">
                <?php foreach ($court_items as $it): ?>
                    <?php $this->load->view('orders/_item_row', array('it' => $it, 'order' => $order)); ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

      <div class="card border-0 shadow-sm rounded-4 mb-3">
        <div class="card-header bg-white fw-semibold">Món đã gọi</div>
        <div class="list-group list-group-flush">
          <?php foreach ($regular_items as $it): ?>
            <?php $this->load->view('orders/_item_row', array('it' => $it, 'order' => $order)); ?>
          <?php endforeach; ?>
          <?php if (empty($items)): ?>
            <div class="list-group-item text-muted text-center py-4">Chưa có món nào.</div>
          <?php endif; ?>
        </div>
        <div class="card-footer bg-white">
          <div class="d-flex justify-content-between small"><span>Tạm tính</span><span><?php echo money_format_vnd($order['subtotal']); ?></span></div>
          <div class="d-flex justify-content-between small"><span>Giảm giá</span><span>-<?php echo money_format_vnd($order['discount_amount']); ?></span></div>
          <div class="d-flex justify-content-between small"><span>VAT</span><span><?php echo money_format_vnd($order['vat_amount']); ?></span></div>
          <div class="d-flex justify-content-between fw-bold fs-5 mt-1"><span>Tổng cộng</span><span class="text-brand"><?php echo money_format_vnd($order['total_amount']); ?></span></div>
        </div>
      </div>



      <?php if ($tickets && ! in_array($order['status'], array('PAID', 'CANCELLED'), TRUE)): ?>
      <div class="card border-0 shadow-sm rounded-4 mb-3">
        <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
          <span><i class="bi bi-cup-hot text-brand"></i> Trạng thái pha chế</span>
          <span class="small text-muted">Tự cập nhật mỗi 5s</span>
        </div>
        <div class="list-group list-group-flush" id="ticketStatusList">
          <?php foreach (array_reverse($tickets) as $t): ?>
          <div class="list-group-item">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="badge bg-<?php echo kitchen_status_badge($t['status']); ?>"><?php echo kitchen_status_label($t['status']); ?></span>
              <span class="small text-muted"><i class="bi bi-clock"></i> <?php echo date('H:i d/m', strtotime($t['created_at'])); ?></span>
            </div>
            <?php foreach ($t['items'] as $it): ?>
              <div class="d-flex justify-content-between align-items-center py-1">
                <div class="d-flex align-items-center gap-2">
                  <?php if ($it['image']): ?>
                    <img src="<?php echo base_url('assets/'.$it['image']); ?>" style="width:32px;height:32px;object-fit:cover;" class="rounded border flex-shrink-0">
                  <?php else: ?>
                    <div class="d-flex align-items-center justify-content-center bg-light rounded border text-muted flex-shrink-0" style="width:32px;height:32px;"><i class="bi bi-cup-straw"></i></div>
                  <?php endif; ?>
                  <span><?php echo htmlspecialchars($it['product_name']); ?> <span class="text-muted">x<?php echo $it['qty']; ?></span></span>
                </div>
                <span class="badge bg-<?php echo kitchen_status_badge($it['status']); ?>"><?php echo kitchen_status_label($it['status']); ?></span>
              </div>
            <?php endforeach; ?>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <div class="col-lg-5">
      <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger py-2 small"><?php echo $this->session->flashdata('error'); ?></div>
      <?php endif; ?>

      <?php if ($order['status'] === 'OPEN' && $order['table_type'] === 'COURT'): ?>
      <div class="card border-0 shadow-sm rounded-4 mb-3">
        <div class="card-header bg-white fw-semibold"><i class="bi bi-dribbble"></i> Thêm giờ chơi sân</div>
        <div class="card-body">
          <?php echo form_open('orders/'.$order['id'].'/add-timeslot'); ?>
            <div class="row g-2 align-items-end">
              <div class="col-5">
                <label class="form-label small mb-1">Giờ bắt đầu</label>
                <input type="time" name="start_time" class="form-control" required>
              </div>
              <div class="col-5">
                <label class="form-label small mb-1">Giờ kết thúc</label>
                <input type="time" name="end_time" class="form-control" required>
              </div>
              <div class="col-2">
                <button class="btn btn-brand w-100"><i class="bi bi-plus-lg"></i></button>
              </div>
            </div>
            <div class="form-text">Tự tính tiền theo khung giờ (Sáng <?php echo money_format_vnd($order['rate_morning']); ?> / Chiều <?php echo money_format_vnd($order['rate_afternoon']); ?> / Tối <?php echo money_format_vnd($order['rate_evening']); ?>). Không cần đặt lịch trước, có thể thêm nhiều lần nếu chơi thêm giờ.</div>
          <?php echo form_close(); ?>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($order['status'] === 'OPEN'): ?>
      <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white fw-semibold">Thêm món</div>
        <div class="card-body" style="max-height:65vh; overflow-y:auto;">
          <?php echo form_open('orders/'.$order['id'].'/add-item', array('id' => 'addItemForm')); ?>
          <?php foreach ($products_by_category as $cat_name => $products): ?>
            <div class="fw-semibold text-brand mt-2 mb-1"><?php echo htmlspecialchars($cat_name); ?></div>
            <?php foreach ($products as $p): ?>
            <div class="d-flex justify-content-between align-items-center border-bottom py-2">
              <div class="d-flex align-items-center gap-2">
                <?php if ($p['image']): ?>
                  <img src="<?php echo base_url('assets/'.$p['image']); ?>" style="width:40px;height:40px;object-fit:cover;" class="rounded border flex-shrink-0">
                <?php else: ?>
                  <div class="d-flex align-items-center justify-content-center bg-light rounded border text-muted flex-shrink-0" style="width:40px;height:40px;"><i class="bi bi-cup-straw"></i></div>
                <?php endif; ?>
                <div>
                  <div><?php echo htmlspecialchars($p['product_name']); ?></div>
                  <div class="small text-muted"><?php echo money_format_vnd($p['price']); ?></div>
                </div>
              </div>
              <input type="number" min="0" value="0" class="form-control form-control-sm qty-input" style="width:70px;"
                     data-product-id="<?php echo $p['id']; ?>">
            </div>
            <?php endforeach; ?>
          <?php endforeach; ?>
          <div id="hiddenInputs"></div>
          <?php echo form_close(); ?>
        </div>
        <div class="card-footer bg-white">
          <button type="submit" form="addItemForm" class="btn btn-brand w-100" onclick="return buildCartInputs();"><i class="bi bi-send"></i> Gửi bếp</button>
        </div>
      </div>
      <?php else: ?>
      <div class="alert alert-info">Đơn đã chuyển sang thu ngân, không thể thêm món.</div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
var TICKET_STATUS_LABEL = {NEW:'Mới', PREPARING:'Đang pha chế', COMPLETED:'Hoàn thành'};
var TICKET_STATUS_COLOR = {NEW:'danger', PREPARING:'warning', COMPLETED:'success'};
var ORDER_BASE_URL = '<?php echo base_url(); ?>';

function escapeHtml(s){ var d = document.createElement('div'); d.textContent = s || ''; return d.innerHTML; }

function fmtTicketTime(dt){
  var d = new Date(dt.replace(' ', 'T'));
  var pad = function(n){ return n < 10 ? '0'+n : n; };
  return pad(d.getHours())+':'+pad(d.getMinutes())+' '+pad(d.getDate())+'/'+pad(d.getMonth()+1);
}

function renderTicketStatus(tickets){
  var list = document.getElementById('ticketStatusList');
  if (!list) return;
  var html = '';
  tickets.slice().reverse().forEach(function(t){
    html += '<div class="list-group-item">';
    html += '<div class="d-flex justify-content-between align-items-center mb-2">'+
      '<span class="badge bg-'+TICKET_STATUS_COLOR[t.status]+'">'+TICKET_STATUS_LABEL[t.status]+'</span>'+
      '<span class="small text-muted"><i class="bi bi-clock"></i> '+fmtTicketTime(t.created_at)+'</span>'+
    '</div>';
    t.items.forEach(function(it){
      var thumb = it.image
        ? '<img src="'+ORDER_BASE_URL+'assets/'+it.image+'" style="width:32px;height:32px;object-fit:cover;" class="rounded border flex-shrink-0">'
        : '<div class="d-flex align-items-center justify-content-center bg-light rounded border text-muted flex-shrink-0" style="width:32px;height:32px;"><i class="bi bi-cup-straw"></i></div>';
      html += '<div class="d-flex justify-content-between align-items-center py-1">'+
        '<div class="d-flex align-items-center gap-2">'+thumb+'<span>'+escapeHtml(it.product_name)+' <span class="text-muted">x'+it.qty+'</span></span></div>'+
        '<span class="badge bg-'+TICKET_STATUS_COLOR[it.status]+'">'+TICKET_STATUS_LABEL[it.status]+'</span>'+
      '</div>';
    });
    html += '</div>';
  });
  list.innerHTML = html;
}

function pollTicketStatus(){
  fetch('<?php echo site_url("orders/".$order['id']."/ticket-status"); ?>')
    .then(function(r){ return r.json(); })
    .then(function(res){ if (res.success) renderTicketStatus(res.tickets); });
}

if (document.getElementById('ticketStatusList')){
  setInterval(pollTicketStatus, 5000);
}

function buildCartInputs(){
  var container = document.getElementById('hiddenInputs');
  container.innerHTML = '';
  var inputs = document.querySelectorAll('.qty-input');
  var count = 0;
  inputs.forEach(function(inp){
    var qty = parseInt(inp.value, 10) || 0;
    if (qty > 0){
      count++;
      container.innerHTML += '<input type="hidden" name="product_id[]" value="'+inp.dataset.productId+'">';
      container.innerHTML += '<input type="hidden" name="qty[]" value="'+qty+'">';
      container.innerHTML += '<input type="hidden" name="note[]" value="">';
    }
  });
  if (count === 0){ alert('Vui lòng chọn ít nhất 1 món.'); return false; }
  return true;
}
</script>
