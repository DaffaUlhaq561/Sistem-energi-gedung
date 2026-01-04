# Cara Set APP_MASTER_KEY

APP_MASTER_KEY diperlukan untuk mengenkripsi dan mendekripsi API key OpenAI yang disimpan di database.

## Opsi 1: Environment Variable (Paling Aman)

### Windows (XAMPP):
1. Buat file `.htaccess` di root folder aplikasi (folder `itera4_`)
2. Tambahkan baris berikut (ganti `your-secret-key-here` dengan key yang kuat):
```apache
SetEnv APP_MASTER_KEY "your-secret-key-here-minimal-32-karakter"
```

### Linux/Apache:
Tambahkan di file `.htaccess`:
```apache
SetEnv APP_MASTER_KEY "your-secret-key-here"
```

Atau di virtual host configuration:
```apache
<VirtualHost *:80>
    SetEnv APP_MASTER_KEY "your-secret-key-here"
</VirtualHost>
```

## Opsi 2: PHP Define (Kurang Aman, Hanya untuk Development)

Edit file `config.php` dan uncomment baris berikut:
```php
define('APP_MASTER_KEY', 'your-secret-key-here-minimal-32-karakter');
```

**PENTING:** Jangan commit file `config.php` yang berisi APP_MASTER_KEY ke repository publik!

## Opsi 3: System Environment Variable

### Windows:
```cmd
setx APP_MASTER_KEY "your-secret-key-here"
```

### Linux/Mac:
```bash
export APP_MASTER_KEY="your-secret-key-here"
```

Tambahkan ke `~/.bashrc` atau `~/.profile` untuk permanen.

## Catatan Keamanan

- Gunakan key yang kuat (minimal 32 karakter, campuran huruf, angka, simbol)
- Jangan commit APP_MASTER_KEY ke repository publik
- Lebih aman menggunakan environment variable daripada define di PHP
- Setelah set APP_MASTER_KEY, restart web server (Apache/XAMPP)

## Verifikasi

Setelah set APP_MASTER_KEY, buka halaman `admin/ai_settings.php` dan pastikan status menunjukkan "APP_MASTER_KEY ada ✅"

