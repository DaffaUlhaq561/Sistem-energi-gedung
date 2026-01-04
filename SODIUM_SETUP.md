# Cara Mengaktifkan Sodium Extension di XAMPP Windows

Sodium extension diperlukan untuk enkripsi API key OpenAI yang lebih aman. Jika Sodium tidak tersedia, sistem akan menggunakan OpenSSL sebagai fallback.

## Langkah-langkah:

### 1. Cek Versi PHP
- Buka XAMPP Control Panel
- Klik tombol "Admin" di sebelah Apache untuk membuka phpinfo
- Atau buka browser dan akses: `http://localhost/dashboard/phpinfo.php`
- Cari informasi tentang PHP version dan architecture (x86 atau x64)

### 2. Download Sodium Extension (jika belum ada)

**Untuk PHP 7.4+ (Recommended):**
- Sodium biasanya sudah termasuk dalam PHP 7.2+ sebagai built-in extension
- Cukup uncomment di php.ini

**Untuk PHP versi lama atau jika tidak ada:**
- Download dari: https://pecl.php.net/package/libsodium
- Atau gunakan OpenSSL sebagai fallback (sudah tersedia di XAMPP)

### 3. Edit php.ini

1. Buka file `php.ini` di folder XAMPP:
   - Lokasi biasanya: `C:\xampp\php\php.ini`
   - Atau klik "Config" di XAMPP Control Panel → pilih "PHP (php.ini)"

2. Cari baris berikut (gunakan Ctrl+F):
   ```
   ;extension=sodium
   ```

3. Hapus tanda `;` di depan `extension=sodium`:
   ```
   extension=sodium
   ```

4. Jika tidak menemukan baris tersebut, tambahkan di bagian extension:
   ```
   extension=sodium
   ```

5. Simpan file (Ctrl+S)

### 4. Restart Apache

- Di XAMPP Control Panel, klik "Stop" pada Apache
- Tunggu beberapa detik
- Klik "Start" pada Apache

### 5. Verifikasi

1. Buka browser dan akses: `http://localhost/dashboard/phpinfo.php`
2. Cari "sodium" (gunakan Ctrl+F)
3. Jika muncul informasi tentang sodium, berarti sudah aktif ✅

Atau buka halaman AI Settings dan pastikan status menunjukkan "Sodium aktif ✅"

## Catatan:

- **Jika Sodium tidak bisa diaktifkan**, sistem akan otomatis menggunakan OpenSSL sebagai fallback
- OpenSSL sudah tersedia di XAMPP dan cukup aman untuk enkripsi API key
- Pastikan extension OpenSSL aktif (biasanya sudah aktif secara default)

## Troubleshooting:

### 1. Cek Status Extension
Akses file `check_extensions.php` di browser:
```
http://localhost/itera4_/check_extensions.php
```
File ini akan menampilkan status semua extension dan membantu diagnose masalah.

### 2. Error: "Unable to load dynamic library 'sodium'"
**Kemungkinan penyebab:**
- File `php_sodium.dll` tidak ada di folder `C:\xampp\php\ext\`
- Architecture tidak match (x86 vs x64)
- PHP version tidak support

**Solusi:**
1. Cek apakah file `php_sodium.dll` ada di `C:\xampp\php\ext\`
2. Jika tidak ada:
   - Untuk PHP 7.2+: Sodium seharusnya sudah built-in, cek di phpinfo
   - Untuk PHP versi lama: Download dari https://pecl.php.net/package/libsodium
   - Pastikan download versi yang sesuai dengan architecture PHP Anda (x86 atau x64)
3. Jika masih error, gunakan OpenSSL sebagai fallback (sudah tersedia di XAMPP)

### 3. Extension tidak muncul setelah restart
**Langkah-langkah:**
1. **Pastikan mengedit php.ini yang benar:**
   - Buka `http://localhost/dashboard/phpinfo.php`
   - Cari "Loaded Configuration File"
   - Itulah file php.ini yang digunakan

2. **Pastikan format benar:**
   ```
   extension=sodium
   ```
   Bukan:
   ```
   ;extension=sodium  (masih ada tanda ;)
   extension = sodium  (ada spasi)
   ```

3. **Cek error log Apache:**
   - Buka XAMPP Control Panel
   - Klik "Logs" di sebelah Apache
   - Cari error terkait sodium
   - Atau cek file: `C:\xampp\apache\logs\error.log`

4. **Cek apakah extension directory benar:**
   - Di phpinfo, cari "extension_dir"
   - Pastikan path-nya benar (biasanya `C:\xampp\php\ext`)

5. **Restart Apache dengan benar:**
   - Stop Apache (tunggu sampai benar-benar stop)
   - Tunggu 5 detik
   - Start Apache lagi
   - Refresh halaman check_extensions.php

### 4. Sodium tidak tersedia di PHP versi lama
Jika Anda menggunakan PHP < 7.2:
- Sodium tidak tersedia secara built-in
- Download dari PECL atau gunakan OpenSSL
- **Rekomendasi:** Upgrade ke PHP 7.4+ atau gunakan OpenSSL

### 5. Verifikasi dengan check_extensions.php
Setelah semua langkah di atas, akses:
```
http://localhost/itera4_/check_extensions.php
```

File ini akan menampilkan:
- Status Sodium (aktif/tidak)
- Status OpenSSL (fallback)
- Status APP_MASTER_KEY
- Test enkripsi/dekripsi
- Daftar semua extension yang ter-load
- Error log terbaru

### 6. Jika semua gagal - Gunakan OpenSSL
**Tidak masalah!** Sistem akan otomatis menggunakan OpenSSL sebagai fallback jika Sodium tidak tersedia. OpenSSL juga aman untuk enkripsi API key.

Untuk memastikan OpenSSL aktif:
1. Buka phpinfo
2. Cari "openssl"
3. Pastikan extension openssl aktif
4. Jika tidak, uncomment `extension=openssl` di php.ini

