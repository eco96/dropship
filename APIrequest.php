<?php
/**	
 * AUTO UPDATE DETAIL PRODUK TOKOPEDIA DARI SHOPEE (for DROPSHIPPER)
 * 
 * release 2020
 * author @eco.nxn (ig)
 * 
 * DILARANG MEMPUBLIKASIKAN TANPA SEIZIN AUTHOR
 */
date_default_timezone_set("Asia/Jakarta");
error_reporting(0);
require dirname(__FILE__).'/config.php';

class curl {
	private $ch, $result, $error;
	
	/**	
	 * HTTP request
	 * 
	 * @param string $method HTTP request method
	 * @param string $url API request URL
	 * @param array $param API request data
     * @param array $header API request header
	 */
	public function request ($method, $url, $param, $header) {
		curl:
        $ch = curl_init();
        switch ($method){
            case "GET":
                curl_setopt($ch, CURLOPT_POST, false);
                break;
            case "POST":               
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
                break;
            case "PATCH":               
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $param); 
                break;
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:68.0) Gecko/20100101 Firefox/68.0'); 
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        if(is_numeric(strpos($url, "tokopedia.com"))) {
            if (file_exists(dirname(__FILE__).'/cookie/new_cookie.txt')) {
                if(!file_exists(dirname(__FILE__).'/cookie/')) {
                    mkdir(dirname(__FILE__).'/cookie');
                }
                curl_setopt($ch, CURLOPT_COOKIEJAR, dirname(__FILE__).'/cookie/new_cookie.txt');
			    curl_setopt($ch, CURLOPT_COOKIEFILE, dirname(__FILE__).'/cookie/new_cookie.txt');
            } else {
                $cookie = $GLOBALS['config'];
                curl_setopt($ch, CURLOPT_COOKIE, str_replace(["\n", "Cookie: "], "", $cookie['TKPD_COOKIE']));
            }
        }
        $result = curl_exec($ch);
        $error  = curl_error($ch);
        if($error) {
            echo "(!) ".date('H:i')." | Connection Timeout\n";
            sleep(3);
            goto curl;
        }
        curl_close($ch);
        return $result;
    }   
}

class info_products extends curl{

    /**
     * productList Tokopedia
     */
    function info_productList_tokopedia($shopID) { 

        $method   = 'POST';
        $header[] = 'Content-Type: application/json;charset=utf-8';

        $endpoint = 'https://gql.tokopedia.com/';
        
        $pageSize = 300;
        $page = 1;
        loop:
        $param = '[
            {
                "operationName": "ProductList",
                "variables": {
                    "shopID": "'.$shopID.'",
                    "filter": [
                        {
                            "id": "pageSize",
                            "value": [
                                "'.$pageSize.'"
                            ]
                        },
                        {
                            "id": "status",
                            "value": []
                        },
                        {
                            "id": "page",
                            "value": [
                                "'.$page.'"
                            ]
                        }
                    ],
                    "sort": {
                        "id": "DEFAULT",
                        "value": "DESC"
                    },
                    "extraInfo": [
                        "view"
                    ]
                },
                "query": "query ProductList($shopID: String!, $filter: [GoodsFilterInput], $sort: GoodsSortInput) {\n  ProductList(shopID: $shopID, filter: $filter, sort: $sort) {\n    header {\n      processTime\n      messages\n      reason\n      errorCode\n      __typename\n    }\n    data {\n      id\n      name\n      price {\n        min\n        max\n        __typename\n      }\n      stock\n      status\n      minOrder\n      maxOrder\n      weight\n      weightUnit\n      condition\n      isMustInsurance\n      isKreasiLokal\n      isCOD\n      isVariant\n      url\n      sku\n      cashback\n      featured\n      score {\n        total\n        __typename\n      }\n      category {\n        id\n        __typename\n      }\n      menu {\n        id\n        __typename\n      }\n      pictures {\n        urlThumbnail\n        __typename\n      }\n      shop {\n        id\n        __typename\n      }\n      wholesale {\n        minQty\n        __typename\n      }\n      stats {\n        countView\n        countReview\n        countTalk\n        __typename\n      }\n      txStats {\n        sold\n        __typename\n      }\n      __typename\n    }\n    __typename\n  }\n}\n"
            }
        ]';
        
        $info = $this->request ($method, $endpoint, $param, $header); 

        $json[] = json_decode($info);

        if(isset($json[count($json)-1][0]->data->ProductList->data[0])) {
            $page = $page+1;
            $pageSize = $pageSize+300;
            goto loop; 
        } else {
            if($page > 1) { 
                return $json[count($json)-2][0]->data->ProductList->data;
            } else {
                return FALSE;
            }
        }         
    }

    /**
     * detail Product Tokopedia
     */
    function detail_product_tokopedia($shopDomain, $productKey) { 

        $method   = 'POST';
        $header[] = 'Content-Type: application/json;charset=utf-8';

        $endpoint = 'https://gql.tokopedia.com/';
        
        $param = '[
            {
                "operationName": "PDPInfoQuery",
                "variables": {
                    "shopDomain": "'.$shopDomain.'",
                    "productKey": "'.$productKey.'"
                },
                "query": "query PDPInfoQuery($shopDomain: String, $productKey: String) {\n  getPDPInfo(productID: 0, shopDomain: $shopDomain, productKey: $productKey) {\n    basic {\n      id\n      shopID\n      name\n      alias\n      price\n      priceCurrency\n      lastUpdatePrice\n      description\n      minOrder\n      maxOrder\n      status\n      weight\n      weightUnit\n      condition\n      url\n      sku\n      gtin\n      isKreasiLokal\n      isMustInsurance\n      isEligibleCOD\n      isLeasing\n      catalogID\n      needPrescription\n      __typename\n    }\n    category {\n      id\n      name\n      title\n      breadcrumbURL\n      isAdult\n      detail {\n        id\n        name\n        breadcrumbURL\n        __typename\n      }\n      __typename\n    }\n    pictures {\n      picID\n      fileName\n      filePath\n      description\n      isFromIG\n      width\n      height\n      urlOriginal\n      urlThumbnail\n      url300\n      status\n      __typename\n    }\n    preorder {\n      isActive\n      duration\n      timeUnit\n      __typename\n    }\n    wholesale {\n      minQty\n      price\n      __typename\n    }\n    videos {\n      source\n      url\n      __typename\n    }\n    campaign {\n      campaignID\n      campaignType\n      campaignTypeName\n      originalPrice\n      discountedPrice\n      isAppsOnly\n      isActive\n      percentageAmount\n      stock\n      originalStock\n      startDate\n      endDate\n      endDateUnix\n      appLinks\n      hideGimmick\n      __typename\n    }\n    stats {\n      countView\n      countReview\n      countTalk\n      rating\n      __typename\n    }\n    txStats {\n      txSuccess\n      txReject\n      itemSold\n      __typename\n    }\n    cashback {\n      percentage\n      __typename\n    }\n    variant {\n      parentID\n      isVariant\n      __typename\n    }\n    stock {\n      useStock\n      value\n      stockWording\n      __typename\n    }\n    menu {\n      name\n      __typename\n    }\n    __typename\n  }\n}\n"
            }
        ]';
        
        $detail = $this->request ($method, $endpoint, $param, $header);

        $json = json_decode($detail);

        if(isset($json[0]->data->getPDPInfo->basic)) {
            return $json[0]->data->getPDPInfo->basic;
        } else {
            return FALSE;     
        }         
    }

    /**
     * detail Product Shopee
     */
    function detail_product_shopee($itemid, $shopid) { 

        $method   = 'GET';

        $endpoint = 'https://shopee.co.id/api/v2/item/get?itemid='.$itemid.'&shopid='.$shopid;
        
        $detail = $this->request ($method, $endpoint, $param=NULL, $header=NULL);

        $json = json_decode($detail);

        return $json;         
    }
}

class update extends info_products {

    /**
     * profil Tokopedia
     */
    function shopProfile_tokopedia() { 

        $method   = 'POST';
        $header[] = 'Content-Type: application/json;charset=utf-8';

        $endpoint = 'https://gql.tokopedia.com/';
        
        $param = '[
            {
                "operationName": "ShopProfileQuery",
                "variables": {},
                "query": "query ShopProfileQuery {\n  userShopInfo {\n    info {\n      shopId: shop_id\n      shopDomain: shop_domain\n      shopName: shop_name\n      shopAvatar: shop_avatar\n      isOfficial: shop_is_official\n      shopScore: shop_score\n      __typename\n    }\n    owner {\n      ownerId: owner_id\n      isPowerMerchant: is_gold_merchant\n      isOwnerOfShop: is_seller\n      pmStatus: pm_status\n      __typename\n    }\n    __typename\n  }\n}\n"
            }
        ]';
        
        $profile = $this->request ($method, $endpoint, $param, $header); 

        $json = json_decode($profile);

        if(!empty($json[0]->data->userShopInfo->info->shopId)) { 
            return $json[0]->data->userShopInfo;
        } else {
            return FALSE;     
        }         
    }

    /**
     * update product price Tokopedia
     */
    function update_price_tokopedia($productID, $shopID, $price) { 

        $method   = 'POST';
        $header[] = 'Content-Type: application/json;charset=utf-8';

        $endpoint = 'https://gql.tokopedia.com/';
        
        $param = '[
            {
                "operationName": "ProductUpdateV3",
                "variables": {
                    "input": {
                        "productID": "'.$productID.'",
                        "shop": {
                            "id": "'.$shopID.'"
                        },
                        "price": '.$price.'
                    }
                },
                "query": "mutation ProductUpdateV3($input: ProductInputV3!) {\n  ProductUpdateV3(input: $input) {\n    header {\n      messages\n      reason\n      errorCode\n      __typename\n    }\n    isSuccess\n    __typename\n  }\n}\n"
            }
        ]';
        
        price:
        $price = $this->request ($method, $endpoint, $param, $header);

        $json = json_decode($price);

        if($json[0]->data->ProductUpdateV3->isSuccess == TRUE) {
            return TRUE;
        } else {
            if(isset($json[0]->errors[0]->message)) {
                $error_msg = $json[0]->errors[0]->message;
            } else {
                $error_msg = $json[0]->data->ProductUpdateV3->header->messages[0];
            }
            
            echo "      [".date('H:i')."] \e[1;33;40m[ERROR]\e[0m ".$error_msg."!\n";    
        }         
    }

    /**
     * update product stock Tokopedia
     */
    function update_stock_tokopedia($productID, $shopID, $new_stock) { 

        $method   = 'POST';
        $header[] = 'Content-Type: application/json;charset=utf-8';

        $endpoint = 'https://gql.tokopedia.com/';
        
        $param = '[
            {
                "operationName": "ProductUpdateV3",
                "variables": {
                    "input": {
                        "productID": "'.$productID.'",
                        "shop": {
                            "id": "'.$shopID.'"
                        },
                        "stock": 50,
                        "status": "'.$new_stock.'"
                    }
                },
                "query": "mutation ProductUpdateV3($input: ProductInputV3!) {\n  ProductUpdateV3(input: $input) {\n    header {\n      messages\n      reason\n      errorCode\n      __typename\n    }\n    isSuccess\n    __typename\n  }\n}\n"
            }
        ]';
        
        stock:
        $stock = $this->request ($method, $endpoint, $param, $header);

        $json = json_decode($stock);

        if($json[0]->data->ProductUpdateV3->isSuccess == TRUE) {
            return TRUE;
        } else {
            if(isset($json[0]->errors[0]->message)) {
                $error_msg = $json[0]->errors[0]->message;
            } else {
                $error_msg = $json[0]->data->ProductUpdateV3->header->messages[0];
            }
             
            echo "      [".date('H:i')."] \e[1;33;40m[ERROR]\e[0m ".$error_msg."!\n";    
        }         
    }

    /**
     * update product description Tokopedia
     */
    function update_description_tokopedia($productID, $productDesc) { 

        $method   = 'PATCH';
        $header[] = 'Content-Type: application/json';
        $header[] = 'Source-Type: desktop';
        $header[] = 'Origin: https://seller.tokopedia.com';

        $endpoint = 'https://tome.tokopedia.com/v3/product/'.$productID;
        
        $param = json_encode([
            "description" => $productDesc
        ]);

        description:
        $description = $this->request ($method, $endpoint, $param, $header); 

        $json = json_decode($description);

        if(isset($json->data->id)) {
            return TRUE;
        } else {
            if(isset($json->header->messages[0])) {
                $error_msg = $json->header->messages[0];
            } else {
                $error_msg = $json->header->messages;
            }
             
            echo "      [".date('H:i')."] \e[1;33;40m[ERROR]\e[0m ".$error_msg."!\n"; 
            return FALSE;   
        }         
    }

    /**
     * Telegram Notifications
     */
    function send_message($config, $text) {

        $method   = 'POST';
        $header[] = 'Content-type: application/x-www-form-urlencoded';

        $endpoint = 'https://api.telegram.org/bot'.$config['BOT_TOKEN'].'/sendMessage';
        
        $param = http_build_query(['chat_id' => $config['USER_ID'], 'text' => $text]);
        
        $send_message = $this->request ($method, $endpoint, $param, $header);

        $json = json_decode($send_message);
        if($json->ok==true) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Updating Tokopedia Product
     */
    function updating($config, $shopDomain, $shopID) {

        $file_produklist = dirname(__FILE__)."/data/".$shopDomain.".CSV";

        if(file_exists($file_produklist)) {
            $produklist = explode("\n",str_replace("\r","",file_get_contents($file_produklist)));
        } else {
            productList_tokopedia:
            $productList_tokopedia = $this->info_productList_tokopedia($shopID);

            if($productList_tokopedia == FALSE) {
                echo "(!) ".date('H:i')." | Gagal mengambil list produk tokopedia, pastikan sudah login!\n";
                sleep(3);
                goto productList_tokopedia;
            } else {

                $productList_content = "tokopedia_item_id;tokopedia_item_name;tokopedia_item_key;shopee_shop_id (diisi);shopee_item_id (diisi);shopee_item_name (diisi);shopee_image_url_1 (diisi);profit (diisi)\n";

                foreach ($productList_tokopedia as $productList_value) {
                    $tkpd_item_id   = $productList_value->id;
                    $tkpd_item_name = $productList_value->name;

                    $exp_item_url   = explode("/", $productList_value->url);
                    $tkpd_item_key  = $exp_item_url[4];

                    $productList_content = $productList_content.$tkpd_item_id.";".$tkpd_item_name.";".$tkpd_item_key."\n";
                }

                if(!file_exists(dirname(__FILE__).'/data/')) {
                    mkdir(dirname(__FILE__).'/data');
                }
                $fh = fopen($file_produklist, "w");
                fwrite($fh, $productList_content);
                fclose($fh);   

                echo "(i) Atur list produk terlebih dahulu di ".$file_produklist."\n\n";
                die();
            }
        }

        $i=1;
        $no_urut=1;
        $produk_ready=0;
        while($i<count($produklist)) {

            if(isset($show_produk)) {
                unset($show_produk);
            }
    
            read_file:
            $file_produklist  = dirname(__FILE__)."/data/".$shopDomain.".CSV";
            $produklist       = explode("\n",str_replace("\r","",file_get_contents($file_produklist)));
            $produkrow        = explode(";", $produklist[$i]);
    
            $tkpd_item_id   = $produkrow[0];
            $tkpd_item_name = $produkrow[1];
            $tkpd_item_key  = $produkrow[2];
            $shop_id        = $produkrow[3];
            $item_id        = $produkrow[4];
            $item_name      = $produkrow[5];
            $img_url_1      = $produkrow[6];
            $profit         = $produkrow[7];

            if(!isset($shop_id)) {
				$i++;
				continue;
		    }

            detail_product_tkpd:
            $detail_product_tkpd = $this->detail_product_tokopedia($shopDomain, $tkpd_item_key); //Get produk tokopedia

            if($detail_product_tkpd == FALSE) {
                echo "(!) ".date('H:i')." | Gagal mendapatkan detail produk tokopedia!\n";
                sleep(3);
                goto detail_product_tkpd;
            } 

            $tkpd_product_name    = $detail_product_tkpd->name;
            $tkpd_product_price   = $detail_product_tkpd->price;
            $tkpd_product_status  = $detail_product_tkpd->status;
            $tkpd_product_description = $detail_product_tkpd->description;

            $tkpd_desc_encode = ["&#39;"=>"'", "&#34;"=>'"', "&amp;"=>"&", "&gt;"=>">"];
            $tkpd_product_desc_filt = $tkpd_product_description;
            foreach ($tkpd_desc_encode as $key => $tkpd_desc_encode_value) {
                $tkpd_product_desc_filt = str_replace($key, $tkpd_desc_encode_value, $tkpd_product_desc_filt);
            }

            if($config['EXP_DATE'] == TRUE && $config['DELETE_DESC'] == FALSE && $config['REPLACE_DESC'] == FALSE) {

                if(stripos($tkpd_product_description, "/20")) {
                
                    preg_match_all('/ (.*?)\/20/', $tkpd_product_description, $exp_date);
                    preg_match_all('/\/20(.*?) /', $tkpd_product_description, $exp_year);
    
                    $exp = substr($exp_date[1][0], -2)."/20".substr($exp_year[1][0], 0,2);
    
                    preg_match_all("/(Updated (.*?))/U", $tkpd_product_description, $updated);
    
                    $exp_with_date = '('.$updated[1][0];
                }
            }
    
            detail_product_shopee:
            $detail_product_shopee = $this->detail_product_shopee($item_id, $shop_id); //Get produk Shopee

            if($detail_product_shopee == FALSE) {
                echo "(!) ".date('H:i')." | Gagal mendapatkan detail produk shopee!\n";
                sleep(3);
                goto detail_product_shopee;
            } 

            $modif=0;

            if($detail_product_shopee->item==null) {
                if($tkpd_product_status == 'ACTIVE') {
                        $modif=1;
                        $modif_stock=1;
                        $modif_price=0;
                        $new_stock= 'INACTIVE';
                }
            } else {
                $id_item      = $detail_product_shopee->item->itemid;
                $name_item    = $detail_product_shopee->item->name;
                $price        = ($detail_product_shopee->item->price)/100000;
                $stock_item   = $detail_product_shopee->item->stock;
                $status_item  = $detail_product_shopee->item->status;
                $img_1_item   = $detail_product_shopee->item->images[0];
                $description_item  = $detail_product_shopee->item->description;

                $tkpd_price_sell = $price+$profit; //harga jual tokopedia

                if($config['DELETE_DESC'] == TRUE && $config['REPLACE_DESC'] == TRUE) {

                    $exp_del_char = explode(",", $config['DELETE_CHAR']);

                    $desc_filtered_1 = str_replace("\r", "", $description_item);
                    foreach ($exp_del_char as $del_char_value) {
                        $desc_filtered_1 = str_ireplace($del_char_value, "", $desc_filtered_1);
                    }

                    $exp_repl_char = explode(",", $config['REPLACE_CHAR']);
                    
                    $desc_filtered_2 = $desc_filtered_1;
                    foreach ($exp_repl_char as $repl_char_value) {
                        $exp_bef_aft_char = explode("-", $repl_char_value);
      
                        $desc_filtered_2 = str_ireplace($exp_bef_aft_char[0], $exp_bef_aft_char[1], $desc_filtered_2);   
                    }

                    if(strlen($desc_filtered_2) > 2000) {
                        if($tkpd_product_desc_filt == substr($desc_filtered_2,0,2000)) {    
							$modif_description=0;
                        } else {
                            $modif=1;
                            $modif_description=1;
                            $filtered_description = substr($desc_filtered_2,0,2000);
                        }
                    } else {
                        if($tkpd_product_desc_filt == $desc_filtered_2) {    
							$modif_description=0;
                        } else { 
                            $modif=1;
                            $modif_description=1;
                            $filtered_description = $desc_filtered_2;
                        }
                    }
                    $config['EXP_DATE'] = FALSE;  

                } elseif($config['DELETE_DESC'] == TRUE && $config['REPLACE_DESC'] == FALSE) {

                    $exp_del_char = explode(",", $config['DELETE_CHAR']);

                    $desc_filtered = str_replace("\r", "", $description_item);
                    foreach ($exp_del_char as $del_char_value) {
                        $desc_filtered = str_ireplace($del_char_value, "", $desc_filtered);
                    }

                    if(strlen($desc_filtered) > 2000) {
                        if($tkpd_product_desc_filt == substr($desc_filtered,0,2000)) {    
							$modif_description=0;
                        } else {
                            $modif=1;
                            $modif_description=1;
                            $filtered_description = substr($desc_filtered,0,2000);
                        }
                    } else {
                        if($tkpd_product_desc_filt == $desc_filtered) {    
							$modif_description=0;
                        } else {
                            $modif=1;
                            $modif_description=1;
                            $filtered_description = $desc_filtered;
                        }
                    }
                    $config['EXP_DATE'] = FALSE;

                } elseif($config['DELETE_DESC'] == FALSE && $config['REPLACE_DESC'] == TRUE) {

                    $exp_repl_char = explode(",", $config['REPLACE_CHAR']);
                    
                    $desc_filtered = str_replace("\r", "", $description_item);
                    foreach ($exp_repl_char as $repl_char_value) {
                        $exp_bef_aft_char = explode("-", $repl_char_value);
      
                        $desc_filtered = str_ireplace($exp_bef_aft_char[0], $exp_bef_aft_char[1], $desc_filtered);   
                    }

                    if(strlen($desc_filtered) > 2000) {
                        if($tkpd_product_desc_filt == substr($desc_filtered,0,2000)) {    
							$modif_description=0;
                        } else {
                            $modif=1;
                            $modif_description=1;
                            $filtered_description = substr($desc_filtered,0,2000);
                        }
                    } else {
                        if($tkpd_product_desc_filt == $desc_filtered) {    
							$modif_description=0;
                        } else {
                            $modif=1;
                            $modif_description=1;
                            $filtered_description = $desc_filtered;
                        }
                    }
                    $config['EXP_DATE'] = FALSE;

                } 

                if($config['EXP_DATE'] == TRUE && $config['DELETE_DESC'] == FALSE && $config['REPLACE_DESC'] == FALSE) {

                    if(stripos($description_item, "/20")) {
                        preg_match_all('/ (.*?)\/20/', $description_item, $expiry_date);
                        preg_match_all('/\/20(.*?)\n/', $description_item, $expiry_year);
   
                        if(empty($expiry_year[1][0])) {
                            preg_match_all('/\/20(.*?) /', $description_item, $expiry_year);
                        }
   
                        $expiry = substr($expiry_date[1][0], -2)."/20".substr($expiry_year[1][0], 0,2);
                       
                    }

                    if(!empty($exp)  && !empty($expiry)) {
                        if($exp == $expiry) {
                            $modif_exp=0;
                        } else {
                            $modif=1;
                            $modif_exp=1;
                        }
                    } elseif(empty($exp) && !empty($expiry)) {
                        $modif=1;
                        $modif_exp=1;
                    } elseif(!empty($exp) && empty($expiry)) {
                        $modif=1;
                        $modif_exp=1;
                    } else {
                        $modif_exp=0;
                    }
                }

                if($tkpd_product_price<>$tkpd_price_sell) {
                    $modif=1;
                    $modif_price=1;
                }

                if($status_item == 1) {
					if($tkpd_product_status== 'WAREHOUSE') {
						if($stock_item>=1) {
							$modif=1;
							$modif_stock=1;
							$new_stock= 'ACTIVE';
							$show_produk = 1;
						} else {
							$modif_stock=0;
						}
					} else {
						if($stock_item==0) {
							$modif=1;
							$modif_stock=1;
							$new_stock= 'INACTIVE';
						} else {
							$modif_stock=0;
						}
					}
				} else {
					if($tkpd_product_status== 'WAREHOUSE') {
						$modif_stock=0;
					} else {		
						$modif=1;
						$modif_stock=1;
						$new_stock= 'INACTIVE';
					}
                }
            }

            if(!isset($show_produk)) {
                if($tkpd_product_status== 'ACTIVE') {
                    $show_produk = 1;
                }
            }

            if($show_produk==1) {
                $produk_ready = $produk_ready+$show_produk;
            }
            
            if($modif==1) {

                update:
                echo "\e[1;37;40m[".str_pad($i, 3, "0",  STR_PAD_LEFT)."]\e[0m [".date('H:i')."] \e[1;37;40m[UPDATE]\e[0m Produk ".$tkpd_product_name."\n";
                
                if($modif_price==1) { //update harga

                    $this->update_price_tokopedia($tkpd_item_id, $shopID, $tkpd_price_sell);

                    check_product_tkpd_1:
                    $check_product_tkpd = $this->detail_product_tokopedia($shopDomain, $tkpd_item_key); //Cek harga produk tokopedia

                    if($check_product_tkpd == FALSE) {
                        echo "      [".date('H:i')."] Gagal recheck harga produk tokopedia!\n";
                        sleep(3);
                        goto check_product_tkpd_1;
                    } 

                    $tkpd_new_price   = $check_product_tkpd->price;

                    if($tkpd_price_sell==$tkpd_new_price) {
                        echo "      [".date('H:i')."] \e[1;36;40m[SUKSES]\e[0m Berhasil Update Harga \e[1;36;40m[IDR ".number_format($tkpd_product_price,0,',','.')." => IDR ".number_format($tkpd_new_price,0,',','.')."]\e[0m\n";

                        if($tkpd_product_price>$tkpd_new_price) {
                            $range = "TURUN";
                        } elseif($tkpd_product_price<$tkpd_new_price) {
                            $range = "NAIK";
                        } else {
                            $range = "SAMA";
                        }

                        if($config['NOTIFY'] == TRUE) {
                            $text = strtoupper($domain)."\n[UPDATE HARGA] ".$range." - IDR ".number_format($tkpd_product_price,0,',','.')." => IDR ".number_format($tkpd_new_price,0,',','.')." Produk ".$tkpd_product_name;
                            $send_message = $this->send_message($config, $text);
                            if($send_message==true) {
                                echo "      [".date('H:i')."] Notification message has been sent.\n";
                            }
                        }  

                    } else {
                        echo "      [".date('H:i')."] \e[1;33;40m[GAGAL!]\e[0m Gagal Update Harga Produk Menjadi \e[1;36;40mIDR ".number_format($tkpd_new_price,0,',','.')."\e[0m\n";

                        if($config['NOTIFY'] == TRUE) {
                            $text = "Failed! When updating [PRODUCT PRICE].";
                            $send_message = $this->send_message($config, $text);
                            if($send_message==true) {
                                echo "      [".date('H:i')."] Notification message has been sent.\n";
                            }
                        }      
                    }
                } //end update harga

                if($modif_stock==1) { //update stock/status
                    
                    $this->update_stock_tokopedia($tkpd_item_id, $shopID, $new_stock);

                    check_product_tkpd_2:
                    $check_product_tkpd = $this->detail_product_tokopedia($shopDomain, $tkpd_item_key); //Cek stock produk tokopedia

                    if($check_product_tkpd == FALSE) {
                        echo "      [".date('H:i')."] Gagal recheck stock produk tokopedia!\n";
                        sleep(3);
                        goto check_product_tkpd_2;
                    } 

                    if($new_stock == 'ACTIVE') {
                        $tkpd_new_stock = 'ACTIVE';
                        $stc  = "\e[1;36;40mTERSEDIA\e[0m";
                        $text = strtoupper($shopDomain)."\n[UPDATE STOCK] TERSEDIA - Produk ".$tkpd_product_name;
                    } else {
                        $tkpd_new_stock = 'WAREHOUSE';
                        $stc  = "\e[1;36;40mKOSONG\e[0m";
                        $text = strtoupper($shopDomain)."\n[UPDATE STOCK] HABIS - Produk ".$tkpd_product_name;
                    }

                    if($check_product_tkpd->status == $tkpd_new_stock) {
                        echo "      [".date('H:i')."] \e[1;36;40m[SUKSES]\e[0m Berhasil Update Status Produk Menjadi ".$stc."\n";

                        if($config['NOTIFY'] == TRUE) {
                            $send_message = $this->send_message($config, $text);
                            if($send_message==true) {
                                echo "      [".date('H:i')."] Notification message has been sent.\n";
                            }
                        }
                        
                    } else {
                        echo "      [".date('H:i')."] \e[1;33;40m[GAGAL!]\e[0m Gagal Update Status Produk Menjadi ".$stc."! ".$json_stock->errors."\n";

                        if($config['NOTIFY'] == TRUE) {
                            $text = "Failed! When updating [PRODUCT STATUS].";
                            $send_message = $this->send_message($config, $text);
                            if($send_message==true) {
                                echo "      [".date('H:i')."] Notification message has been sent.\n";
                            }
                        } 
                    }
                } //end update stock/status

                if($modif_description==1) { //update description
                    
                    $update_description_tokopedia = $this->update_description_tokopedia($tkpd_item_id, $filtered_description);

                    if($update_description_tokopedia == TRUE) {
                        echo "      [".date('H:i')."] \e[1;36;40m[SUKSES]\e[0m Berhasil Update Deskripsi Produk\n";

                        if($config['NOTIFY'] == TRUE) {
                            $text = strtoupper($shopDomain)."\n[UPDATE PRODUCT DESCRIPTION] Produk ".$tkpd_product_name;
                            $send_message = $this->send_message($config, $text);
                            if($send_message==true) {
                                echo "      [".date('H:i')."] Notification message has been sent.\n";
                            }
                        }
                        
                    } else {
                        echo "      [".date('H:i')."] \e[1;33;40m[GAGAL!]\e[0m Gagal Update Deskripsi Produk\n";

                        if($config['NOTIFY'] == TRUE) {
                            $text = "Failed! When updating [PRODUCT DESCRIPTION].";
                            $send_message = $this->send_message($config, $text);
                            if($send_message==true) {
                                echo "      [".date('H:i')."] Notification message has been sent.\n";
                            }
                        }  
                    }
                } //end update description

                if($config['EXP_DATE'] == TRUE && $config['DELETE_DESC'] == FALSE && $config['REPLACE_DESC'] == FALSE) {
                    if($modif_exp==1) { //update expiry date

                        $date_update = '';

                        if($exp_with_date == "(") {
                            $old_expiry = str_replace("\r","",$exp); 
                        } else {
                            $old_expiry = str_replace("\r","",$exp).' '.$exp_with_date; 
                        }

                        $new_expiry = str_replace("\r","",$expiry); 

                        if(!empty($new_expiry)) {
                            $new_expiry = str_replace("\r","",$expiry).' (Updated '.date('d-m-Y').')';
                        }

                        if(!empty($exp)  && !empty($expiry)) {
                            $productDesc = str_replace($old_expiry, $new_expiry, $tkpd_product_description);

                        } elseif(empty($exp) && !empty($expiry)) {
                            $productDesc = "EXP. DATE: ".$new_expiry."\n\n".$tkpd_product_description;

                        } elseif(!empty($exp) && empty($expiry)) {
                            $productDesc = str_replace($old_expiry, $new_expiry, $tkpd_product_description);
                        }
                        
                        $this->update_description_tokopedia($tkpd_item_id, $productDesc);

                        check_product_tkpd_4:
                        $check_product_tkpd = $this->detail_product_tokopedia($shopDomain, $tkpd_item_key); //Cek stock produk tokopedia

                        if($check_product_tkpd == FALSE) {
                            echo "      [".date('H:i')."] Gagal recheck expiry date produk tokopedia!\n";
                            sleep(3);
                            goto check_product_tkpd_4;
                        } 

                        $tkpd_new_description = $check_product_tkpd->description;

                        if(stripos($tkpd_new_description, $new_expiry)) {
                            echo "      [".date('H:i')."] \e[1;36;40m[SUKSES]\e[0m Berhasil Update Expiry Date \e[1;36;40m[".$old_expiry."]\e[0m Menjadi \e[1;36;40m[".$new_expiry."]\e[0m\n";

                            if($config['NOTIFY'] == TRUE) {
                                $text = strtoupper($shopDomain)."\n[UPDATE EXP. DATE] ".$new_expiry." Produk ".$tkpd_product_name;
                                $send_message = $this->send_message($config, $text);
                                if($send_message==true) {
                                    echo "      [".date('H:i')."] Notification message has been sent.\n";
                                }
                            }
                            
                        } else {
                            echo "      [".date('H:i')."] \e[1;33;40m[GAGAL!]\e[0m Gagal Update Expiry Date \e[1;36;40m[".$old_expiry."]\e[0m Menjadi \e[1;36;40m[".$new_expiry."]\e[0m\n";

                            if($config['NOTIFY'] == TRUE) {
                                $text = "Failed! When updating [PRODUCT EXP. DATE].";
                                $send_message = $this->send_message($config, $text);
                                if($send_message==true) {
                                    echo "      [".date('H:i')."] Notification message has been sent.\n";
                                }
                            }  
                        }

                        unset($old_expiry);
					    unset($new_expiry);
                    } //end update expiry date
                }
    
            } else {
                if($tkpd_product_status == 'ACTIVE') {
                    $tkpd_product_status = "\e[1;36;40m".$tkpd_product_status."\e[0m   ";   
                }

                if($config['EXP_DATE'] == TRUE) {
                    echo "\e[1;37;40m[".str_pad($i, 3, "0",  STR_PAD_LEFT)."]\e[0m [".date('H:i')."] [".$tkpd_product_status." - IDR ".number_format($tkpd_product_price,0,',','.')." - EXP.".$exp."] Produk ".$tkpd_product_name.".\n";
                } else {
                    echo "\e[1;37;40m[".str_pad($i, 3, "0",  STR_PAD_LEFT)."]\e[0m [".date('H:i')."] [".$tkpd_product_status." - IDR ".number_format($tkpd_product_price,0,',','.')."] Produk ".$tkpd_product_name.".\n";
                }
                
            } //end modif

            if(!empty($img_url_1)) {
                $exp_img_url_1   = explode("/", $img_url_1);

                if($img_1_item != $exp_img_url_1[4]) {
                    echo "      [".date('H:i')."] \e[1;33;40m[UPDATE]\e[0m Gambar Produk Telah Update Menjadi \e[1;36;40m[https://cf.shopee.co.id/file/".$img_1_item."]\e[0m\n";
                }
            }

            echo "\n";

            if($config['EXP_DATE'] == TRUE) {
                unset($exp_date);
                unset($exp_year);
                unset($expiry_date);
                unset($expiry_year);
                unset($exp);
                unset($expiry);
                unset($exp_with_date);
            }

            $i++;
            flush();
            ob_flush();
        }
        
        echo "ACTIVE PRODUCTS: ".$produk_ready." | WAREHOUSE PRODUCTS: ".(($i-2)-$produk_ready)."\n\n";
    } 
}
?>