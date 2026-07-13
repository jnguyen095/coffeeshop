<div class="container-fluid py-3 py-md-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Sản phẩm</h4>
    <a href="<?php echo site_url('products/create'); ?>" class="btn btn-brand"><i class="bi bi-plus-lg"></i> Thêm</a>
  </div>
  <div class="table-responsive">
    <table class="table bg-white shadow-sm rounded align-middle">
      <thead class="table-light"><tr><th>Ảnh</th><th>SKU</th><th>Tên</th><th>Danh mục</th><th class="text-end">Giá</th><th>Trạng thái</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($products as $p): ?>
        <tr>
          <td>
            <?php if ($p['image']): ?>
              <img src="<?php echo base_url('assets/'.$p['image']); ?>" style="width:48px;height:48px;object-fit:cover;" class="rounded border">
            <?php else: ?>
              <div class="d-flex align-items-center justify-content-center bg-light rounded border text-muted" style="width:48px;height:48px;"><i class="bi bi-cup-straw"></i></div>
            <?php endif; ?>
          </td>
          <td><?php echo htmlspecialchars($p['sku']); ?></td>
          <td><?php echo htmlspecialchars($p['product_name']); ?></td>
          <td><?php echo htmlspecialchars($p['category_name']); ?></td>
          <td class="text-end"><?php echo money_format_vnd($p['price']); ?></td>
          <td><span class="badge bg-<?php echo $p['status']==='ACTIVE'?'success':'secondary'; ?>"><?php echo $p['status']; ?></span></td>
          <td>
            <a href="<?php echo site_url('products/'.$p['id'].'/edit'); ?>" class="btn btn-sm btn-outline-primary">Sửa</a>
            <?php echo form_open('products/'.$p['id'].'/delete', array('class'=>'d-inline', 'onsubmit'=>"return confirm('Ẩn sản phẩm này?');")); ?>
              <button class="btn btn-sm btn-outline-danger">Ẩn</button>
            <?php echo form_close(); ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
