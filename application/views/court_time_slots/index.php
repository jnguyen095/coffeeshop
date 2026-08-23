<div class="container-fluid py-3 py-md-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Khung giờ & giá sân</h4>
    <a href="<?php echo site_url('court-time-slots/create'); ?>" class="btn btn-brand"><i class="bi bi-plus-lg"></i> Thêm khung giờ</a>
  </div>
  <div class="form-text mb-3">Giá tiền sân được tính theo các khung giờ dưới đây, áp dụng chung cho mọi sân pickleball. Một buổi đặt/chơi có thể chạy qua nhiều khung — hệ thống tự cộng dồn theo tỷ lệ thời lượng rơi vào từng khung.</div>

  <div class="table-responsive">
    <table class="table bg-white shadow-sm rounded align-middle">
      <thead class="table-light"><tr><th class="text-end">Thứ tự</th><th>Tên khung</th><th>Giờ bắt đầu</th><th>Giờ kết thúc</th><th class="text-end">Giá / giờ</th><th>Trạng thái</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($slots as $s): ?>
        <tr>
          <td class="text-end text-muted"><?php echo (int) $s['sort_order']; ?></td>
          <td><?php echo htmlspecialchars($s['label']); ?></td>
          <td><?php echo substr($s['start_time'], 0, 5); ?></td>
          <td><?php echo substr($s['end_time'], 0, 5); ?></td>
          <td class="text-end"><?php echo money_format_vnd($s['price_per_hour']); ?></td>
          <td><span class="badge bg-<?php echo $s['status']==='ACTIVE'?'success':'secondary'; ?>"><?php echo $s['status']==='ACTIVE'?'Đang áp dụng':'Tạm ẩn'; ?></span></td>
          <td class="text-nowrap">
            <a href="<?php echo site_url('court-time-slots/'.$s['id'].'/edit'); ?>" class="btn btn-sm btn-outline-primary">Sửa</a>
            <?php echo form_open('court-time-slots/'.$s['id'].'/delete', array('class'=>'d-inline', 'onsubmit'=>"return confirm('Xóa khung giờ này?');")); ?>
              <button class="btn btn-sm btn-outline-danger">Xóa</button>
            <?php echo form_close(); ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($slots)): ?>
        <tr><td colspan="7" class="text-center text-muted py-4">Chưa có khung giờ nào.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
