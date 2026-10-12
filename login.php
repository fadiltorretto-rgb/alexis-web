<?php
require __DIR__.'/config.php';
$role = $_GET['role'] ?? 'wisatawan';
if (!in_array($role, ['wisatawan','manager','admin'], true)) $role = 'wisatawan';
if (user() && user()['role'] === $role) redirect(home($role));

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = trim($_POST['username'] ?? ''); $pw = $_POST['password'] ?? '';
    $s = $pdo->prepare('SELECT * FROM users WHERE (username=? OR email=?) AND role=? LIMIT 1');
    $s->execute([$id, $id, $role]); $u = $s->fetch();
    if ($u && $u['status'] === 'aktif' && password_verify($pw, $u['password'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = ['id'=>$u['id'], 'nama'=>$u['nama'], 'role'=>$u['role']];
        redirect(home($role));
    }
    $err = 'Username atau password salah, atau akun tidak aktif.';
}
$fl = flash();
$judul = ['wisatawan'=>'Login Wisatawan','manager'=>'Login Manager','admin'=>'Login Admin'][$role];
?>
<!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($judul) ?> - <?= e(setting('nama_aplikasi','PACIRA')) ?></title>
<link rel="stylesheet" href="<?= url('assets/style.css') ?>"></head>
<body class="role-<?= $role ?>">
<div class="auth"><div class="box">
  <div class="logo"><?= e(setting('nama_aplikasi','PACIRA')) ?></div>
  <h1><?= e($judul) ?></h1>
  <p><?= e(setting('tagline','Explore Destinasi Wisata')) ?></p>
  <?php if ($fl): ?><div class="alert <?= $fl[1] ?>"><?= e($fl[0]) ?></div><?php endif; ?>
  <?php if ($err): ?><div class="alert err"><?= e($err) ?></div><?php endif; ?>
  <form method="post"><?= csrf_field() ?>
    <label for="u">Username atau email</label><input id="u" name="username" required autofocus value="<?= e($_POST['username'] ?? '') ?>">
    <label for="p">Password</label><input id="p" type="password" name="password" required>
    <button class="btn">Login</button>
  </form>
  <?php if ($role === 'wisatawan' && setting('izin_registrasi','1') === '1'): ?>
    <div class="switch">Belum punya akun? <a href="<?= url('register.php') ?>">Daftar</a></div>
  <?php endif; ?>
  <div class="switch">Masuk sebagai:
    <?php foreach (['wisatawan'=>'Wisatawan','manager'=>'Manager','admin'=>'Admin'] as $k=>$v): if ($k !== $role): ?>
      <a href="<?= url('login.php?role='.$k) ?>"><?= $v ?></a>
    <?php endif; endforeach; ?>
  </div>
</div></div>
</body></html>
