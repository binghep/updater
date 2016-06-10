<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
echo '<pre>';

require 'lib/getNordstromProductSize_api.php';
//Note: url is the url_for_scrapping in step1 table. 
// $url="http://shop.nordstrom.com/S/4247661?cm_mmc=Linkshare-_-datafeed-_-infant_unisex:baby_accessories:baby_furniture-_-5091901"; //Only has color option.
// $url="http://shop.nordstrom.com/S/4237966?cm_mmc=Linkshare-_-datafeed-_-women:bottoms:pant-_-5084034";//Out of stock
$url="http://shop.nordstrom.com/S/3977168?cm_mmc=Linkshare-_-datafeed-_-plus_women:bottoms:pant-_-1063136";//both color and size
$nordstrom_scrapper=new nordstrom_scrapper($url);
// //which reroutes to:
// // "http://shop.nordstrom.com/s/city-chic-tough-girl-stretch-skinny-jeans-light-denim-plus-size/3977168?cm_mmc=Linkshare-_-datafeed-_-plus_women%3Abottoms%3Apant-_-1063136"
if ($nordstrom_scrapper->init_status===false){
	die("cannot instantiate scrapper");
}
$conf_sku="test";
$scrapped_attributes=$nordstrom_scrapper->getScrappedAttibutes($conf_sku);
// var_dump($nordstrom_scrapper->php_object);
if ($scrapped_attributes!==false && !empty($scrapped_attributes)){
		// var_dump($scrapped_attributes);
	 	return $scrapped_attributes;
}else{
	echo "\n---Abort this product's scrapped data. in scrape_attributes() function: getScrappedAttibutes() returned false (e.g. this product's scrapped data has no size info, meaning the product has color option only)---\n";
	return false;
}

//==================================================
/*
require_once("lib/getNordstromProductSize_api.php");
$scrapper=new nordstrom_scrapper($productUrl);
if ($scrapper->init_status===false){
	// die("cannot instantiate scrapper");
	echo "\n---Error in datafeedr_updater->scrape_attributes() function: cannot find JS block(e.g. Out of Stock)--\n";
	return false;//("cannot instantiate scrapper");
}
$scrapped_attributes=$scrapper->getScrappedAttibutes($conf_sku);
if ($scrapped_attributes!==false && !empty($scrapped_attributes)){
	 	return $scrapped_attributes;
}else{
	echo "\n---Error in scrape_attributes() function: getScrappedAttibutes() returned false---\n";
	return false;
}
*/
//==================================================







//-------------test1-pass--------------
// $scrapper=new nordstrom_scrapper("http://shop.nordstrom.com/s/lush-lauren-long-sleeve-shift-dress/4149469?cm_mmc=Linkshare-_-datafeed-_-women%3Adresses%3Adress-_-5020927&siteId=p3nsT1q9jS4-I1jmXTyS1yFf6QooB8zz4w");
// if ($scrapper->init_status===false){
// 	die("cannot instantiate scrapper");
// }
// $scrapped_attributes=$scrapper->getScrappedAttibutes("4149469");
// echo '----------------------';
// if ($scrapped_attributes!==false && !empty($scrapped_attributes)){
// 	var_dump($scrapped_attributes);
// }


//-------------test2-pass--------------
// $scrapper=new nordstrom_scrapper("http://shop.nordstrom.com/s/astr-lace-trim-shift-dress/4370955?cm_mmc=Linkshare-_-datafeed-_-women%3Adresses%3Adress-_-121473_2&siteId=p3nsT1q9jS4-i1x6YETYQW8z1SUsEYvZgg");
// if ($scrapper->init_status===false){
// 	die("cannot instantiate scrapper");
// }
// $scrapped_attributes=$scrapper->getScrappedAttibutes("4149469");
// echo '----------------------';
// if ($scrapped_attributes!==false && !empty($scrapped_attributes)){
// 	var_dump($scrapped_attributes);
// }
//-------------test3-pass-------------
// $scrapper=new nordstrom_scrapper("http://shop.nordstrom.com/s/jella-c-midi-dress/4199254?cm_mmc=Linkshare-_-datafeed-_-juniors_women%3Adresses%3Adress-_-5057812&siteId=p3nsT1q9jS4-BUCaQ7w_I4Gxl.y.55oRMA");
// if ($scrapper->init_status===false){
// 	die("cannot instantiate scrapper");
// }
// $scrapped_attributes=$scrapper->getScrappedAttibutes("4149469");
// echo '----------------------';
// if ($scrapped_attributes!==false && !empty($scrapped_attributes)){
// 	var_dump($scrapped_attributes);
// }
//-------------test4-pass-------------
// $scrapper=new nordstrom_scrapper("http://shop.nordstrom.com/s/amuse-society-dani-sleeveless-knit-dress/4301161?cm_mmc=Linkshare-_-datafeed-_-women%3Adresses%3Adress-_-5128447&siteId=p3nsT1q9jS4-7mnqY8GxDkSIJjZpydt7cg");
// if ($scrapper->init_status===false){
// 	die("cannot instantiate scrapper");
// }
// $scrapped_attributes=$scrapper->getScrappedAttibutes("4149469");
// echo '----------------------';
// if ($scrapped_attributes!==false && !empty($scrapped_attributes)){
// 	var_dump($scrapped_attributes);
// }
//------------test 5-pass-----------------
// $scrapper=new nordstrom_scrapper("http://shop.nordstrom.com/s/jrpesil-dress/4320570?cm_mmc=Linkshare-_-datafeed-_-plus_women%3Adresses%3Adress-_-5143954&siteId=p3nsT1q9jS4-46fjqAr3eB24V5nEK0JCJA");
// if ($scrapper->init_status===false){
// 	die("cannot instantiate scrapper");
// }
// $scrapped_attributes=$scrapper->getScrappedAttibutes("4149469");
// echo '----------------------';
// if ($scrapped_attributes!==false && !empty($scrapped_attributes)){
// 	var_dump($scrapped_attributes);
// }
//-------------test 6--Out of Stock. pass-------------------
// $scrapper=new nordstrom_scrapper("http://shop.nordstrom.com/s/matty-m-asymmetrical-shift-dress-with-side-tie/4250995?cm_mmc=Linkshare-_-datafeed-_-women%3Adresses%3Adress-_-5093303&siteId=p3nsT1q9jS4-ard5eMeCVSDDNyfLVUSZLQ");
// if ($scrapper->init_status===false){
// 	die("cannot instantiate scrapper");
// }
// $scrapped_attributes=$scrapper->getScrappedAttibutes("4149469");
// echo '----------------------';
// if ($scrapped_attributes!==false && !empty($scrapped_attributes)){
// 	var_dump($scrapped_attributes);
// }
//-------------test 7-Real url from datafeedr-pass--------------------
// $scrapper=new nordstrom_scrapper("http://shop.nordstrom.com/S/3977168?cm_mmc=Linkshare-_-datafeed-_-plus_women:bottoms:pant-_-1063136");
// //which reroutes to:
// // "http://shop.nordstrom.com/s/city-chic-tough-girl-stretch-skinny-jeans-light-denim-plus-size/3977168?cm_mmc=Linkshare-_-datafeed-_-plus_women%3Abottoms%3Apant-_-1063136"
// if ($scrapper->init_status===false){
// 	die("cannot instantiate scrapper");
// }
// $scrapped_attributes=$scrapper->getScrappedAttibutes("4149469");
// echo '----------------------';
// if ($scrapped_attributes!==false && !empty($scrapped_attributes)){
// 	var_dump($scrapped_attributes);
// }

