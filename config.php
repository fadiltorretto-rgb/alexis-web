<?php
/* ===== Konfigurasi PACIRA (XAMPP) ===== */
session_start();

const DB_HOST = 'localhost';
const DB_NAME = 'pacira_db';
const DB_USER = 'root';   // default XAMPP
const DB_PASS = '';       // default XAMPP kosong
const BASE_URL = '/pacira'; // sesuaikan dengan nama folder di htdocs

try {
    $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4', DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $ex) {
    http_response_code(500);
    die('<h3>Koneksi database gagal</h3><p>Pastikan MySQL di XAMPP sudah Start dan file <code>database.sql</code> sudah di-import lewat phpMyAdmin.</p><small>'.htmlspecialchars($ex->getMessage()).'</small>');
}

/* ===== Helper umum ===== */
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function url($p = '') { return BASE_URL.'/'.ltrim($p, '/'); }
function redirect($p) { header('Location: '.url($p)); exit; }
function user() { return $_SESSION['user'] ?? null; }
function home($role) { return ['wisatawan'=>'wisatawan/beranda.php','manager'=>'manager/dashboard.php','admin'=>'admin/dashboard.php'][$role] ?? 'login.php'; }
function require_role($role) { if (!user() || user()['role'] !== $role) redirect('login.php?role='.$role); }
function rupiah($n) { return 'Rp '.number_format((float)$n, 0, ',', '.'); }
function tgl($d) { return $d ? date('d/m/Y', strtotime($d)) : '-'; }

function flash($m = null, $t = 'ok') {
    if ($m !== null) { $_SESSION['flash'] = [$m, $t]; return; }
    $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f;
}

function csrf_field() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return '<input type="hidden" name="csrf" value="'.$_SESSION['csrf'].'">';
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(419);
        die('Sesi formulir kedaluwarsa. Kembali ke halaman sebelumnya, muat ulang, lalu coba lagi.');
    }
}

function setting($k, $d = '') {
    global $pdo; static $c = null;
    if ($c === null) { $c = $pdo->query('SELECT kunci,nilai FROM pengaturan')->fetchAll(PDO::FETCH_KEY_PAIR); }
    return $c[$k] ?? $d;
}

/* ===== Data destinasi ===== */
function daftar_destinasi($pdo, $o = []) {
    $w = []; $p = [];
    if (empty($o['semua'])) $w[] = "d.status='aktif'";
    if (!empty($o['id'])) { $w[] = 'd.id=?'; $p[] = $o['id']; }
    if (!empty($o['q'])) { $w[] = '(d.nama LIKE ? OR d.lokasi LIKE ?)'; $p[] = '%'.$o['q'].'%'; $p[] = '%'.$o['q'].'%'; }
    if (!empty($o['kat'])) { $w[] = 'd.kategori=?'; $p[] = $o['kat']; }
    $ord = ($o['order'] ?? '') === 'rating' ? 'rating DESC, jml_ulasan DESC' : 'd.nama ASC';
    $lim = !empty($o['limit']) ? ' LIMIT '.(int)$o['limit'] : '';
    $where = $w ? 'WHERE '.implode(' AND ', $w) : '';
    $s = $pdo->prepare("SELECT d.*, COALESCE(ROUND(AVG(u.rating),1),0) rating, COUNT(u.id) jml_ulasan
        FROM destinasi d LEFT JOIN ulasan u ON u.destinasi_id=d.id $where GROUP BY d.id ORDER BY $ord$lim");
    $s->execute($p);
    return $s->fetchAll();
}

function kategori_list() { return ['Pantai','Gunung','Budaya','Alam','Kuliner']; }
function emoji_kat($k) { return ['Pantai'=>'🏖️','Gunung'=>'🏔️','Budaya'=>'🏛️','Alam'=>'🌿','Kuliner'=>'🍜'][$k] ?? '📍'; }

function cover($d, $cls = '') {
    if (!empty($d['gambar'])) return '<img class="cover '.$cls.'" src="'.url('uploads/'.e($d['gambar'])).'" alt="'.e($d['nama']).'">';
    return '<div class="cover ph '.$cls.'">'.emoji_kat($d['kategori']).'</div>';
}

function stars($n) {
    $n = (int)round($n); $s = '<span class="stars" aria-label="Rating '.$n.' dari 5">';
    for ($i = 1; $i <= 5; $i++) $s .= $i <= $n ? '★' : '<i>★</i>';
    return $s.'</span>';
}

function kartu_destinasi($d) {
    $h = '<a class="card dest" href="'.url('wisatawan/detail.php?id='.$d['id']).'">'.cover($d);
    $h .= '<div class="dest-body"><strong>'.e($d['nama']).'</strong><small>'.e($d['lokasi']).'</small>';
    $h .= '<div class="row">'.stars($d['rating']).' <small>'.($d['jml_ulasan'] ? number_format($d['rating'],1).' ('.$d['jml_ulasan'].')' : 'Belum ada ulasan').'</small></div>';
    $h .= '<span class="price">'.rupiah($d['harga_tiket']).'</span></div></a>';
    return $h;
}

/* ===== Grafik kunjungan (SVG, tanpa library) ===== */
function kunjungan_bulanan($pdo, $n = 6) {
    $out = [];
    for ($i = $n - 1; $i >= 0; $i--) {
        $d = new DateTime('first day of this month'); $d->modify("-$i month");
        $out[$d->format('Y-m')] = ['label' => $d->format('M'), 'v' => 0];
    }
    $rows = $pdo->query("SELECT DATE_FORMAT(tanggal,'%Y-%m') ym, COUNT(*) c FROM kunjungan WHERE status<>'dibatalkan' GROUP BY ym")->fetchAll();
    foreach ($rows as $r) if (isset($out[$r['ym']])) $out[$r['ym']]['v'] = (int)$r['c'];
    return $out;
}
function svg_line($data, $color) {
    $w = 480; $h = 170; $p = 32; $vals = array_column($data, 'v'); $max = max(max($vals), 1); $n = count($data);
    $pts = []; $lbl = ''; $i = 0;
    foreach ($data as $d) {
        $x = $p + ($n > 1 ? $i * ($w - 2*$p) / ($n - 1) : 0);
        $y = $h - $p - ($d['v'] / $max) * ($h - 2*$p);
        $pts[] = round($x,1).','.round($y,1);
        $lbl .= '<circle cx="'.round($x,1).'" cy="'.round($y,1).'" r="4" fill="'.$color.'"/>'
              . '<text x="'.round($x,1).'" y="'.round($y-9,1).'" text-anchor="middle" font-size="12" fill="#334155">'.$d['v'].'</text>'
              . '<text x="'.round($x,1).'" y="'.($h-10).'" text-anchor="middle" font-size="12" fill="#64748b">'.e($d['label']).'</text>';
        $i++;
    }
    return '<svg viewBox="0 0 '.$w.' '.$h.'" class="chart" role="img" aria-label="Grafik kunjungan per bulan">'
         . '<line x1="'.$p.'" y1="'.($h-$p).'" x2="'.($w-$p).'" y2="'.($h-$p).'" stroke="#cbd5e1"/>'
         . '<polyline points="'.implode(' ', $pts).'" fill="none" stroke="'.$color.'" stroke-width="3" stroke-linejoin="round"/>'.$lbl.'</svg>';
}

function aktivitas_terbaru($pdo, $limit = 6) {
    return $pdo->query("SELECT u.nama, d.nama dest, k.status, k.created_at FROM kunjungan k
        JOIN users u ON u.id=k.user_id JOIN destinasi d ON d.id=k.destinasi_id ORDER BY k.id DESC LIMIT ".(int)$limit)->fetchAll();
}
