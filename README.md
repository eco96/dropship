# Bot Auto Update Produk Shopee ke Tokopedia
**Fungsi: untuk melakukan pengecekan detail produk dan otomatis update dari Supplier Shopee ke Toko kamu di Tokopedia yang terintegrasi ke Telegram sebagai pesan notifikasi**

# Tutorial Konfigurasi

**Lakukan konfigurasi pada file `config.php` terlebih dahulu.**
- `$config['TKPD_COOKIE']` cookie session login akun tokopedia kamu
- `$config['NOTIFY']` fitur notifikasi ke telegram
- `$config['DELETE_DESC']` fitur update deskripsi produk full + Filtering menghapus karakter yang tidak ingin di cantumkan didalamnya (Maks 2000 Karakter)
- `$config['REPLACE_DESC']` fitur update deskripsi produk full + Filtering mengganti karakter yang tercantumkan didalamnya (Maks 2000 Karakter)
- `$config['EXP_DATE']` fitur update hanya tanggal expiry pada deskripsi produk yang mendeteksi format MM/YYYY

**Setelah selesai konfigurasi lalu jalankan file `run.php` dengan command `php run.php` di terminal. Kemudian file list produk akan muncul di path `data/shop_domain.CSV`, lalu lengkapi semua kolom yang dibutuhkan kemudian simpan tetap dengan format `.CSV`.**

**Cara menemukan `ShopID` dan `ItemID` Shopee:**

Misal. URL Produk Shopee: `https://shopee.co.id/XXXXXXXXXXXXXX-i.20481265.6927707104`

ShopID: 20481265

ItemID: 6927707104

**Cara menemukan URL Gambar Shopee**
- Klik Kanan gambar utama 
- Klik `View Background Image`
- Kemudian Copy URL

**Pengaturan sudah selesai. Jalankan `run.php` sesuai yang kamu butuhkan, disarankan running 24 jam dengan RDP atau Cloud9 untuk terus memeriksa update produk dari supplier Shopee selama 24 jam penuh.**
