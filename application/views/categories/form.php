<div class="container py-3 py-md-4" style="max-width:480px;">
  <h4 class="fw-bold mb-3"><?php echo $page_title; ?></h4>
  <?php echo form_open(current_url()); ?>
    <div class="mb-3">
      <label class="form-label">Tên danh mục</label>
      <input type="text" name="name" class="form-control" required value="<?php echo $category ? htmlspecialchars($category['name']) : ''; ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Thứ tự hiển thị</label>
      <input type="number" name="sort_order" class="form-control" value="<?php echo $category ? $category['sort_order'] : 0; ?>">
    </div>
    <div class="mb-3 form-check">
      <input type="checkbox" name="court_only" value="1" class="form-check-input" id="courtOnly" <?php echo ($category && $category['court_only']) ? 'checked' : ''; ?>>
      <label class="form-check-label" for="courtOnly">Chỉ dành cho sân pickleball</label>
      <div class="form-text">Sản phẩm trong danh mục này chỉ hiện khi gọi món cho sân pickleball, không hiện ở bàn cafe thường.</div>
    </div>
    <?php if ($category): ?>
    <div class="mb-3">
      <label class="form-label">Trạng thái</label>
      <select name="status" class="form-select">
        <option value="ACTIVE" <?php echo $category['status']==='ACTIVE'?'selected':''; ?>>Hoạt động</option>
        <option value="INACTIVE" <?php echo $category['status']==='INACTIVE'?'selected':''; ?>>Ẩn</option>
      </select>
    </div>
    <?php endif; ?>
    <div class="d-grid gap-2">
      <button class="btn btn-brand btn-lg">Lưu</button>
      <a href="<?php echo site_url('categories'); ?>" class="btn btn-outline-secondary">Hủy</a>
    </div>
  <?php echo form_close(); ?>
</div>
