PACIRA - Explore Destinasi Wisata (PHP + MySQL, XAMPP)

CARA PASANG
1. Jalankan XAMPP, Start "Apache" dan "MySQL".
2. Ekstrak folder "pacira" ke  C:\xampp\htdocs\pacira
3. Buka http://localhost/phpmyadmin -> tab Import -> pilih database.sql -> Go.
   (database pacira_db dibuat otomatis beserta data contoh)
4. Buka http://localhost/pacira

AKUN DEMO (password semua: password)
- Wisatawan : wisatawan   -> /login.php?role=wisatawan
- Manager   : manager     -> /login.php?role=manager
- Admin     : admin       -> /login.php?role=admin

PENGATURAN KONEKSI: config.php (DB_USER, DB_PASS, BASE_URL).
Jika nama folder bukan "pacira", ubah BASE_URL.
