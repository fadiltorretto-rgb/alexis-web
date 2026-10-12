<?php
require __DIR__.'/config.php';
if (setting('izin_registrasi','1') !== '1') { flash('Pendaftaran akun sedang ditutup.', 'err'); redirect('login.php'); }
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? ''); $un = trim($_POST['username'] ?? ''); $em = trim($_POST['email'] ?? ''); $pw = $_POST['password'] ?? '';
    if ($nama === '' || $un === '' || $em === '') $err = 'Semua kolom wajib diisi.';
    elseif (!preg_match('/^[A-Za-z0-9_.]{3,30}$/', $un)) $err = 'Username 3-30 karakter: huruf, angka, titik, atau garis bawah.';
    elseif (!filter_var($em, FILTER_VALIDATE_EMAIL)) $err = 'Format email tidak valid.';
    elseif (strlen($pw) < 6) $err = 'Password minimal 6 karakter.';
    else {
        $c = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username=? OR email=?'); $c->execute([$un, $em]);
        if ($c->fetchColumn() > 0) $err = 'Username atau email sudah terdaftar.';
        else {
            $pdo->prepare("INSERT INTO users (nama,username,email,password,role) VALUES (?,?,?,?, 'wisatawan')")
                ->execute([$nama, $un, $em, password_hash($pw, PASSWORD_DEFAULT)]);
            flash('Akun berhasil dibuat. Silakan login.');
            redirect('login.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Daftar - <?= e(setting('nama_aplikasi','PACIRA')) ?></title>
<link rel="stylesheet" href="<?= url('assets/style.css') ?>"></head>
<body class="role-wisatawan">
<div class="auth"><div class="box">
  <div class="logo"><?= e(setting('nama_aplikasi','PACIRA')) ?></div>
  <h1>Daftar Wisatawan</h1>
  <?php if ($err): ?><div class="alert err"><?= e($err) ?></div><?php endif; ?>
  <form method="post"><?= csrf_field() ?>
    <label>Nama lengkap</label><input name="nama" required value="<?= e($_POST['nama'] ?? '') ?>">
    <label>Username</label><input name="username" required value="<?= e($_POST['username'] ?? '') ?>">
    <label>Email</label><input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>">
    <label>Password</label><input type="password" name="password" required minlength="6">
    <button class="btn">Buat akun</button>
  </form>
  <div class="switch">Sudah punya akun? <a href="<?= url('login.php') ?>">Login</a></div>
</div></div>
</body></html>
