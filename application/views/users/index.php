<div class="container-fluid py-3 py-md-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Người dùng</h4>
    <a href="<?php echo site_url('users/create'); ?>" class="btn btn-brand"><i class="bi bi-plus-lg"></i> Thêm</a>
  </div>
  <div class="table-responsive">
    <table class="table bg-white shadow-sm rounded align-middle">
      <thead class="table-light"><tr><th>Tên đăng nhập</th><th>Họ tên</th><th>Vai trò</th><th>Trạng thái</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($users as $u): ?>
        <tr>
          <td><?php echo htmlspecialchars($u['username']); ?></td>
          <td><?php echo htmlspecialchars($u['fullname']); ?></td>
          <td><?php echo role_label($u['role']); ?></td>
          <td><span class="badge bg-<?php echo $u['status']==='ACTIVE'?'success':'secondary'; ?>"><?php echo $u['status']; ?></span></td>
          <td>
            <a href="<?php echo site_url('users/'.$u['id'].'/edit'); ?>" class="btn btn-sm btn-outline-primary">Sửa</a>
            <?php if ((int)$u['id'] !== (int)$current_user['id']): ?>
            <?php echo form_open('users/'.$u['id'].'/delete', array('class'=>'d-inline', 'onsubmit'=>"return confirm('Vô hiệu hóa tài khoản này?');")); ?>
              <button class="btn btn-sm btn-outline-danger">Vô hiệu hóa</button>
            <?php echo form_close(); ?>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
