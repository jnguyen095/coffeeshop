<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<title>Đăng ký thành công - Pick Angel Park</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  body{
    margin:0; min-height:100vh;
    background: radial-gradient(circle at 50% 0%, #3a2a63 0%, #1c1440 55%, #100b28 100%);
    font-size:18px; color:#3a1f00;
    padding: 1.75rem 1rem;
    display:flex; align-items:center;
  }
  .tt-wrap{ max-width:480px; margin:0 auto; width:100%; }
  .tt-card{
    background:#fffaf0; border-radius:28px;
    box-shadow:0 12px 34px rgba(0,0,0,.4);
    padding:2rem 1.35rem; text-align:center;
  }
  .tt-icon{ width:170px; max-width:65%; display:block; margin:0 auto .5rem; }
  .tt-check{ font-size:3rem; margin-bottom:.25rem; }
  .tt-title{ font-size:1.7rem; font-weight:800; color:#c1272d; line-height:1.3; }
  .tt-msg{ font-size:1.2rem; margin-top:1rem; line-height:1.5; }
  .tt-gift{
    font-size:1.25rem; font-weight:800; color:#c1272d;
    background:#fff3cd; border:2px dashed #e8b34a; border-radius:16px;
    padding:.9rem .75rem; margin:1.25rem 0; line-height:1.5;
  }
  .tt-note{ font-size:1.05rem; color:#7a2e00; margin-top:.5rem; }
  .btn-tt-outline{
    font-size:1.1rem; font-weight:700; padding:.8rem 1rem; border-radius:14px;
    border:2px solid #c1272d; color:#c1272d; background:#fff; display:inline-block; margin-top:1.5rem;
    text-decoration:none;
  }
  .btn-tt-outline:active{ background:#fff3cd; }
</style>
</head>
<body>
  <div class="tt-wrap">
    <div class="tt-card">
      <img src="<?php echo base_url('assets/img/trung-thu-icon-removebg.png'); ?>" alt="Trung thu" class="tt-icon">
      <div class="tt-check"></div>
      <div class="tt-title">🎉 Đăng ký thành công!</div>
      <div class="tt-msg">
        Cảm ơn <strong><?php echo htmlspecialchars($parent_name); ?></strong> đã đăng ký nhận quà Trung Thu cho
        <strong><?php echo (int) $kid_count; ?></strong> bé tại Pick Angel Park 🌕
      </div>
      <div class="tt-gift">🗓 Vui lòng đến nhận quà vào lúc:<br><?php echo $event_label; ?></div>
      <div class="tt-note">Hẹn gặp Ba/Mẹ và các bé trong Đêm hội Trăng Rằm nhé!</div>
    </div>
  </div>
</body>
</html>
