<?php
require_once __DIR__ . '/../app/init.php';
if (current_user()) { redirect('/dashboard.php'); }
$error = '';
if (is_post()) {
  $email = $_POST['email'] ?? '';
  $password = $_POST['password'] ?? '';
  if (login($email, $password)) { redirect('dashboard.php'); }
  $error = 'Invalid login.';
}
?><!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="assets/css/style.css">
<title>Service Routing Portal — Login</title>
</head>
<body>
  <div class="header"><div>Service Routing Portal</div></div>
  <div class="container" style="max-width:520px;">
    <h1>Sign in</h1>
    <?php if ($error): ?><div class="card" style="border-color:#fecaca;color:#991b1b;background:#fef2f2;"><?=$error?></div><?php endif; ?>
    <form method="post">
      <div class="form-row">
        <div style="flex:1">
          <label>Email</label>
          <input type="email" name="email" required>
        </div>
      </div>
      <div class="form-row">
        <div style="flex:1">
          <label>Password</label>
          <input type="password" name="password" required>
        </div>
      </div>
      <div class="form-row">
        <button type="submit">Sign in</button>
      </div>
      <p style="font-size:12px;color:#6b7280;">Demo admin: admin@example.com / Admin@123</p>
    </form>
  </div>
  <footer>© <?=date('Y')?> SRP</footer>
</body>
</html>
