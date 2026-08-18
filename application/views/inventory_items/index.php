<div class="container-fluid py-3 py-md-4">
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h4 class="fw-bold mb-0">Sản phẩm kho</h4>
    <?php if ($current_user['role'] === 'ADMIN'): ?>
    <div class="d-flex gap-2">
      <a href="<?php echo site_url('inventory/items/import'); ?>" class="btn btn-outline-secondary"><i class="bi bi-file-earmark-arrow-up"></i> Import Excel</a>
      <a href="<?php echo site_url('inventory/items/create'); ?>" class="btn btn-brand"><i class="bi bi-plus-lg"></i> Thêm sản phẩm</a>
    </div>
    <?php endif; ?>
  </div>

  <?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success py-2 small"><?php echo $this->session->flashdata('success'); ?></div>
  <?php endif; ?>

  <?php echo form_open('inventory/items', array('method' => 'get', 'class' => 'row g-2 mb-3')); ?>
    <div class="col-auto">
      <input type="text" name="q" class="form-control" placeholder="Tìm theo tên hoặc SKU..." value="<?php echo htmlspecialchars($keyword); ?>" style="min-width:220px;">
    </div>
    <div class="col-auto">
      <select name="category_id" class="form-select" onchange="this.form.submit()">
        <option value="">Tất cả danh mục</option>
        <?php foreach ($categories as $c): ?>
          <option value="<?php echo $c['id']; ?>" <?php echo ((string) $category_id === (string) $c['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['name']); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-auto">
      <button type="submit" class="btn btn-outline-secondary"><i class="bi bi-search"></i> Tìm</button>
    </div>
    <div class="col-auto">
      <a href="<?php echo site_url('inventory/items').($low_stock_only ? '' : '?low_stock=1'.($category_id ? '&category_id='.$category_id : '').($keyword ? '&q='.urlencode($keyword) : '')); ?>"
         class="btn <?php echo $low_stock_only ? 'btn-danger' : 'btn-outline-danger'; ?>">
        <i class="bi bi-exclamation-triangle"></i> Sắp hết hàng
      </a>
    </div>
  <?php echo form_close(); ?>

  <div class="table-responsive">
    <table class="table bg-white shadow-sm rounded align-middle">
      <thead class="table-light">
        <tr><th>SKU</th><th>Tên</th><th>Danh mục</th><th>ĐVT</th><th>Bảo quản</th><th class="text-end">Tồn kho</th><th class="text-end">Ngưỡng</th><th>Trạng thái</th><?php if ($current_user['role'] === 'ADMIN'): ?><th></th><?php endif; ?></tr>
      </thead>
      <tbody>
      <?php foreach ($items as $it): $low = $it['qty_on_hand'] < $it['low_stock_threshold']; ?>
        <tr class="<?php echo $low ? 'table-danger' : ''; ?>">
          <td><?php echo htmlspecialchars($it['sku']); ?></td>
          <td><?php echo htmlspecialchars($it['name']); ?></td>
          <td><?php echo htmlspecialchars($it['category_name']); ?></td>
          <td><?php echo htmlspecialchars($it['unit_name']); ?></td>
          <td><?php echo storage_type_label($it['storage_type']); ?></td>
          <td class="text-end fw-semibold"><?php echo rtrim(rtrim(number_format($it['qty_on_hand'], 2, '.', ''), '0'), '.'); ?></td>
          <td class="text-end text-muted"><?php echo rtrim(rtrim(number_format($it['low_stock_threshold'], 2, '.', ''), '0'), '.'); ?></td>
          <td>
            <?php if ($low): ?><span class="badge bg-danger">Sắp hết</span><?php else: ?><span class="badge bg-success">Đủ hàng</span><?php endif; ?>
          </td>
          <?php if ($current_user['role'] === 'ADMIN'): ?>
          <td>
            <a href="<?php echo site_url('inventory/items/'.$it['id'].'/edit'); ?>" class="btn btn-sm btn-outline-primary">Sửa</a>
            <?php echo form_open('inventory/items/'.$it['id'].'/delete', array('class'=>'d-inline', 'onsubmit'=>"return confirm('Ẩn sản phẩm này?');")); ?>
              <button class="btn btn-sm btn-outline-danger">Ẩn</button>
            <?php echo form_close(); ?>
          </td>
          <?php endif; ?>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($items)): ?>
        <tr><td colspan="9" class="text-center text-muted py-4">Chưa có sản phẩm kho nào.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
