<?php
/**	
 * AUTO UPDATE DETAIL PRODUK TOKOPEDIA DARI SHOPEE (for DROPSHIPPER)
 * 
 * release 2020
 * author @eco.nxn (ig)
 * 
 * DILARANG MEMPUBLIKASIKAN TANPA SEIZIN AUTHOR
 * ============================================
 * 
 * 
 * Setting Cookie untuk session login
 * 
 * Firefox (Tutorial): 
 * - Login Tokopedia sampai berhasil
 * - CTRL+SHIFT+E untuk masuk ke inspect element pada menu network/jaringan
 * - Ketik 'tokopedia.com' pada box Filter URLs
 * - Klik salah satu URL tokopedia
 * - Pada bagian 'Request headers (xxx KB)' klik kanan pada Cookie lalu Copy
 * - Paste Cookie dibawah ini
 */ 
$config['TKPD_COOKIE'] = 'PASTE_COOKIE_DISINI';

/**
 * Notifications Telegram (set TRUE / FALSE)
 * Temukan userID kamu dengan chat ke @userinfobot (bot)
 */ 
$config['NOTIFY']    = FALSE;
$config['USER_ID']   = userID_Kamu_Disini; //penerima notifications
$config['BOT_TOKEN'] = 'PASTE_TOKEN_BOT_DISINI'; //diisi jika notify=true

/**
 * Fitur Auto Update (set TRUE / FALSE)
 * Utama (Default):
 * - Update Harga
 * - Update Stock/Status
 * 
 * Tambahan dibawah ini:
 */ 

/**
 * Update Product Full Description + Filtering (Maks 2000 Karakter)
 * DELETE_DESC: untuk menghapus karakter string yang tidak ingin dicantumkan di deskripsi produk seperti nama toko supplier shopee yang biasanya dicantumkan di deskripsi produk mereka
 * REPLACE_DESC: untuk mengganti karakter string di deskripsi produk seperti nama toko supplier diganti menjadi nama toko kamu
 */
$config['DELETE_DESC']  = FALSE; 
$config['DELETE_CHAR']  = 'misal. PESANAN DIKIRIM DI HARI YANG SAMA!!,PESANAN DIPROSES DI HARI YANG SAMA!!'; //Gunakan tanda koma untuk kata lebih dari satu

$config['REPLACE_DESC'] = FALSE; 
$config['REPLACE_CHAR'] = 'misal. shopee-Tokopedia'; //Gunakan tanda strip '-' sebagai 'diganti menjadi', gunakan tanda koma jika kata yang di ubah lebih dari satu

/**
 * Update Expiry Date ONLY pada Product Description (Produk Kesehatan biasanya)
 * untuk update masa expiry date yang paling update (mendeteksi format MM/YYYY)
$config['DELETE_DESC']  = FALSE; 
 * Fitur ini tidak berlaku jika fitur dan REPLACE_DESC diaktifkan
 */
$config['EXP_DATE']     = FALSE; 
?>