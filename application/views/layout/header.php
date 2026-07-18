<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<title><?php echo isset($page_title) ? $page_title.' - Cafe POS' : 'Cafe POS & KDS'; ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="<?php echo base_url('assets/css/style.css'); ?>" rel="stylesheet">
</head>
<body>
<?php if ( ! empty($current_user)): ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-brand sticky-top">
  <div class="container-fluid">
    <a class="navbar-brand" href="<?php echo site_url('dashboard'); ?>"><i class="bi bi-cup-hot-fill me-1"></i>Cafe POS</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <?php if (in_array($current_user['role'], array('STAFF','CASHIER','ADMIN'), TRUE)): ?>
        <li class="nav-item"><a class="nav-link" href="<?php echo site_url('dashboard'); ?>"><i class="bi bi-speedometer2"></i> Tổng quan</a></li>
        <?php endif; ?>
        <?php if (in_array($current_user['role'], array('STAFF', 'CASHIER','ADMIN'), TRUE)): ?>
        <li class="nav-item"><a class="nav-link" href="<?php echo site_url('tables'); ?>"><i class="bi bi-grid-3x3-gap"></i> Bàn</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo site_url('orders'); ?>"><i class="bi bi-receipt"></i> Đơn hàng</a></li>
        <?php endif; ?>
        <?php if (in_array($current_user['role'], array('STAFF','CASHIER','ADMIN'), TRUE)): ?>
        <li class="nav-item"><a class="nav-link" href="<?php echo site_url('takeaway/create'); ?>"><i class="bi bi-bag-check"></i> Bán mang đi</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo site_url('bookings'); ?>"><i class="bi bi-calendar-check"></i> Lịch sân</a></li>
        <?php endif; ?>
        <?php if (in_array($current_user['role'], array('BARISTA','ADMIN'), TRUE)): ?>
        <li class="nav-item"><a class="nav-link" href="<?php echo site_url('kitchen'); ?>"><i class="bi bi-fire"></i> Bếp (KDS)</a></li>
        <?php endif; ?>
        <?php if (in_array($current_user['role'], array('CASHIER','ADMIN'), TRUE)): ?>
        <li class="nav-item"><a class="nav-link" href="<?php echo site_url('cashier'); ?>"><i class="bi bi-cash-coin"></i> Thu ngân</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo site_url('payments'); ?>"><i class="bi bi-clock-history"></i> LS Thanh toán</a></li>
        <?php endif; ?>
        <?php if ($current_user['role'] === 'ADMIN'): ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown"><i class="bi bi-gear"></i> Quản trị</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="<?php echo site_url('tables/manage'); ?>">Quản lý bàn</a></li>
            <li><a class="dropdown-item" href="<?php echo site_url('categories'); ?>">Danh mục</a></li>
            <li><a class="dropdown-item" href="<?php echo site_url('products'); ?>">Sản phẩm</a></li>
            <li><a class="dropdown-item" href="<?php echo site_url('users'); ?>">Người dùng</a></li>
            <li><a class="dropdown-item" href="<?php echo site_url('reports'); ?>">Báo cáo</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="<?php echo site_url('settings'); ?>"><i class="bi bi-gear"></i> Cài đặt</a></li>
          </ul>
        </li>
        <?php endif; ?>
      </ul>
      <ul class="navbar-nav">
        <?php if (in_array($current_user['role'], array('STAFF','CASHIER','ADMIN'), TRUE)): ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle position-relative" href="#" id="assistBell" data-bs-toggle="dropdown">
            <i class="bi bi-bell-fill"></i>
            <span id="assistCountBadge" class="badge bg-danger rounded-pill d-none">0</span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" style="min-width:280px;" id="assistDropdown">
            <li><div class="px-3 py-2 text-muted small text-center">Không có yêu cầu nào</div></li>
          </ul>
        </li>
        <?php endif; ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($current_user['fullname']); ?>
            <span class="badge bg-light text-dark ms-1"><?php echo role_label($current_user['role']); ?></span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="<?php echo site_url('logout'); ?>"><i class="bi bi-box-arrow-right"></i> Đăng xuất</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>
<?php if (in_array($current_user['role'], array('STAFF','CASHIER','ADMIN'), TRUE)): ?>
<script>
(function(){
  var lastPendingIds = null; // null = first load, don't beep yet
  var TYPE_LABEL = {HELP:'Cần hỗ trợ', PAYMENT:'Yêu cầu thanh toán'};
  var TYPE_ICON = {HELP:'bi-question-circle text-warning', PAYMENT:'bi-credit-card text-success'};

  function beep(){
    try {
      var ctx = new (window.AudioContext || window.webkitAudioContext)();
      var o = ctx.createOscillator(); var g = ctx.createGain();
      o.connect(g); g.connect(ctx.destination);
      o.frequency.value = 880; g.gain.value = 0.15;
      o.start(); o.stop(ctx.currentTime + 0.2);
    } catch (e){}
  }

  function timeAgo(dt){
    var then = new Date(dt.replace(' ','T'));
    var now = new Date();
    var diffMin = Math.floor((now.getTime() - then.getTime())/60000);

    var sameDay = then.getFullYear() === now.getFullYear() && then.getMonth() === now.getMonth() && then.getDate() === now.getDate();

    if (sameDay){
      if (diffMin < 1) return 'Vừa xong';
      if (diffMin < 60) return diffMin+' phút trước';
      var hours = Math.floor(diffMin/60);
      var mins = diffMin % 60;
      return hours+' giờ'+(mins > 0 ? ' '+mins+' phút' : '')+' trước';
    }

    var pad = function(n){ return n < 10 ? '0'+n : n; };
    return pad(then.getDate())+'/'+pad(then.getMonth()+1)+' '+pad(then.getHours())+':'+pad(then.getMinutes());
  }

  function loadAssistance(){
    fetch('<?php echo site_url('api/assistance/pending'); ?>')
      .then(function(r){ return r.json(); })
      .then(function(res){
        if (!res.success) return;
        var calls = res.calls;
        var badge = document.getElementById('assistCountBadge');
        var dropdown = document.getElementById('assistDropdown');

        var newIds = calls.map(function(c){ return c.id; });
        if (lastPendingIds !== null){
          var hasNew = newIds.some(function(id){ return lastPendingIds.indexOf(id) === -1; });
          if (hasNew) beep();
        }
        lastPendingIds = newIds;

        badge.textContent = calls.length;
        badge.classList.toggle('d-none', calls.length === 0);

        if (calls.length === 0){
          dropdown.innerHTML = '<li><div class="px-3 py-2 text-muted small text-center">Không có yêu cầu nào</div></li>';
          return;
        }

        dropdown.innerHTML = calls.map(function(c){
          return '<li><div class="px-3 py-2 d-flex justify-content-between align-items-center border-bottom">'+
            '<div><i class="bi '+TYPE_ICON[c.type]+' me-1"></i><strong>'+c.table_name+'</strong>'+
            '<div class="small text-muted">'+TYPE_LABEL[c.type]+' — '+timeAgo(c.created_at)+'</div></div>'+
            '<button class="btn btn-sm btn-outline-success" onclick="resolveAssistance('+c.id+', event)">Đã xử lý</button>'+
          '</div></li>';
        }).join('');
      });
  }

  window.resolveAssistance = function(id, evt){
    if (evt) evt.stopPropagation();
    fetch('<?php echo site_url('api/assistance'); ?>/'+id+'/resolve', {method:'POST'}).then(function(){ loadAssistance(); });
  };

  loadAssistance();
  setInterval(loadAssistance, 5000);
})();
</script>
<?php endif; ?>
<?php endif; ?>
<main>
