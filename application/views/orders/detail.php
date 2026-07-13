<div class="container-fluid py-3 py-md-4">
  <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
    <div>
      <h4 class="fw-bold mb-0"><?php echo htmlspecialchars($order['table_name']); ?> <span class="text-muted fs-6">#<?php echo htmlspecialchars($order['order_no']); ?></span></h4>
      <span class="badge bg-<?php echo order_status_badge($order['status']); ?>"><?php echo $order['status']; ?></span>
    </div>
    <div class="btn-group btn-group-sm flex-wrap">
      <a href="<?php echo site_url('tables/'.$order['table_id'].'/transfer'); ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left-right"></i> Chuyển bàn</a>
      <a href="<?php echo site_url('tables/'.$order['table_id'].'/merge'); ?>" class="btn btn-outline-secondary"><i class="bi bi-union"></i> Gộp bàn</a>
      <a href="<?php echo site_url('tables/'.$order['table_id'].'/print-provisional'); ?>" target="_blank" class="btn btn-outline-dark"><i class="bi bi-printer"></i> In tạm tính</a>
      <a href="<?php echo site_url('tables'); ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Sơ đồ bàn</a>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-lg-7">
      <div class="card border-0 shadow-sm rounded-4 mb-3">
        <div class="card-header bg-white fw-semibold">Món đã gọi</div>
        <div class="list-group list-group-flush">
          <?php foreach ($items as $it): ?>
          <div class="list-group-item d-flex justify-content-between align-items-center <?php echo $it['status']==='CANCELLED' ? 'opacity-50 text-decoration-line-through' : ''; ?>">
            <div class="d-flex align-items-center gap-2">
              <?php if ($it['image']): ?>
                <img src="<?php echo base_url('assets/'.$it['image']); ?>" style="width:44px;height:44px;object-fit:cover;" class="rounded border flex-shrink-0">
              <?php else: ?>
                <div class="d-flex align-items-center justify-content-center bg-light rounded border text-muted flex-shrink-0" style="width:44px;height:44px;"><i class="bi bi-cup-straw"></i></div>
              <?php endif; ?>
              <div>
                <div class="fw-semibold"><?php echo htmlspecialchars($it['product_name']); ?></div>
                <div class="small text-muted"><?php echo money_format_vnd($it['price']); ?> x <?php echo $it['qty']; ?><?php if ($it['note']): ?> — <?php echo htmlspecialchars($it['note']); ?><?php endif; ?></div>
              </div>
            </div>
            <div class="d-flex align-items-center gap-2">
              <div class="fw-semibold"><?php echo money_format_vnd($it['amount']); ?></div>
              <?php if ($it['status'] === 'ACTIVE' && $order['status'] === 'OPEN'): ?>
              <div class="dropdown">
                <button class="btn btn-sm btn-light" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li>
                    <?php echo form_open('orders/'.$order['id'].'/update-item/'.$it['id'], array('class' => 'px-3 py-1 d-flex gap-1')); ?>
                      <input type="number" name="qty" min="1" value="<?php echo $it['qty']; ?>" class="form-control form-control-sm" style="width:70px;">
                      <button class="btn btn-sm btn-outline-primary">Sửa</button>
                    <?php echo form_close(); ?>
                  </li>
                  <li><hr class="dropdown-divider"></li>
                  <li>
                    <?php echo form_open('orders/'.$order['id'].'/cancel-item/'.$it['id'], array('onsubmit' => "return confirm('Hủy món này?');")); ?>
                      <button class="dropdown-item text-danger">Hủy món</button>
                    <?php echo form_close(); ?>
                  </li>
                </ul>
              </div>
              <?php endif; ?>
            </div>
          </div>
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
    </div>

    <div class="col-lg-5">
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
