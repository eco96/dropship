<?php
/**	
 * AUTO UPDATE DETAIL PRODUK TOKOPEDIA DARI SHOPEE (for DROPSHIPPER)
 * 
 * release 2020
 * author @eco.nxn (ig)
 * 
 * DILARANG MEMPUBLIKASIKAN TANPA SEIZIN AUTHOR
 */
require dirname(__FILE__).'/APIrequest.php';

$update = new update();

if(!file_exists(dirname(__FILE__).'/data')) {
	mkdir(dirname(__FILE__).'/data');
}

if(!file_exists(dirname(__FILE__).'/cookie')) {
	mkdir(dirname(__FILE__).'/cookie');
}

$akun = $update->shopProfile_tokopedia(); 
if($akun == FALSE) {
	echo "(!) Detail Toko Tidak ditemukan!";
	die();
} else {
	$shopID     = $akun->info->shopId; 
	$shopDomain = $akun->info->shopDomain;
	$ownerId    = $akun->owner->ownerId;
}

while(TRUE) {
	echo "\n\n".chr(27).chr(91).'H'.chr(27).chr(91).'J'."\n\e[1;36;40mAUTO UPDATE PRODUCTS PHP_v".phpversion()." \e[0m\e[1;37;40m[ Date : ".date('d-m-Y H:i')." GMT+7]\e[0m\e[1;36;40m____________________by @eco.nxn\e[0m\n\n";

	$update->updating($config, $shopDomain, $shopID);

	if(date('G') <= 6) {
		$sleep_time_m = 20; 
	} elseif(date('G') <= 9) {
		$sleep_time_m = 5; 
	} else {
		$sleep_time_m = 10; 
	}
	
	$sleep_time_s=$sleep_time_m*60;
	$det=0;
	$men=$sleep_time_m*1;
	for($q=1; $q<=$sleep_time_s;$q++) {
		if($det<>0) {
			$det = $det-1;
		}
	
		print "\r\r[Time: ".date('H:i:s')." GMT+7] Waiting for ".str_pad($men, 2, "0",  STR_PAD_LEFT).":".str_pad($det, 2, "0",  STR_PAD_LEFT)." ";
		sleep(1);
		if($det==0) {
			$det=60;
			$men=$men-1;
		}
	}
}

?>