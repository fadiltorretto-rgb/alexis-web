<?php
require __DIR__.'/config.php';
require __DIR__.'/includes/layout.php';
if (!user()) redirect('login.php');
$role = user()['role'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION = [];
    session_destroy();
    header('Location: '.url('login.php?role='.$role));
    exit;
}
page_start('Logout', $role, '');
?>
<div class="card" style="max-width:420px;margin:40px auto;text-align:center">
  <div style="font-size:2.4rem">🚪</div>
  <h2>Keluar dari akun?</h2>
  <p class="hint">Anda perlu login lagi untuk mengakses halaman ini.</p>
  <form method="post" class="row" style="justify-content:center"><?= csrf_field() ?>
    <button class="btn">Ya, logout</button>
    <a class="btn ghost" href="<?= url(home($role)) ?>">Batal</a>
  </form>
</div>
<?php page_end();
