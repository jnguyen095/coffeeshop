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
