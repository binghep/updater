<?php
require __DIR__.'/../lib/nordstrom_scrapper.php';
// use PHPUnit\Framework\TestCase;

/**
* Only takes the datafeedr affliate link as param. 
* Will reroute to real nordstrom product page.
*/

class nordstrom_scrapperTest extends PHPUnit_Framework_TestCase{
	
	// public function testOnlyColorOption(){
	// 	$url="http://shop.nordstrom.com/S/4247661?cm_mmc=Linkshare-_-datafeed-_-infant_unisex:baby_accessories:baby_furniture-_-5091901";
	// 	$a=new nordstrom_scrapper($url);
	// 	// if ($a->init_status===false){
	// 	// 	echo("cannot instantiate scrapper");
	// 	// }
	// 	$this->assertNotFalse($a->init_status);

	// 	$conf_sku="test";
	// 	$scrapped_attributes=$a->getScrappedAttributes($conf_sku);
	// 	// var_dump($nordstrom_scrapper->php_object);
	// 	$this->assertFalse($scrapped_attributes);//because this product only has color. no size option. so skip this product by returning false.
	// }

	/**
	 * if run this test case, must set accept_both_manual_and_auto= true in scrapper. otherwise get error "Trying to get property of non-object" because 
	 */ 
	public function testBothColorAndSizeOption(){
		$url="http://shop.nordstrom.com/s/niczoe-lotus-long-sleeve-top-regular-petite/4332555?cm_mmc=Linkshare-_-datafeed-_-Women%3ATops%3ASweater-_-5152873";//manual url
		$a=new nordstrom_scrapper($url);
		// if ($a->init_status===false){
		// 	echo("cannot instantiate scrapper");
		// }
		$this->assertNotFalse($a->init_status);
		$conf_sku="test";
		$scrapped_attributes=$a->getScrappedAttributes($conf_sku);
		// var_dump($scrapped_attributes);
		$this->assertNotFalse($scrapped_attributes);//if empty will become false. so this already implies it is not empty
	}
	

	/**
	 * if run this test case, must set accept_both_manual_and_auto= true in scrapper. otherwise get error "Trying to get property of non-object" because 
	 */ 
	// public function testBothColorAndSizeOption2(){
	// 	$url="http://shop.nordstrom.com/s/lush-strappy-long-sleeve-woven-blouse/4344299?cm_mmc=Linkshare-_-datafeed-_-Women%3ATops%3ABlouse_Top-_-5162132";//manual url
	// 	$a=new nordstrom_scrapper($url);
	// 	// if ($a->init_status===false){
	// 	// 	echo("cannot instantiate scrapper");
	// 	// }
	// 	$this->assertNotFalse($a->init_status);
	// 	// var_dump($a->real_url_to_scrape);
	// 	// var_dump($a->init_status);
	// 	// var_dump($a->php_object);
	// 	// return;
	// 	$conf_sku="test";
	// 	$scrapped_attributes=$a->getScrappedAttributes($conf_sku);
	// 	// var_dump($scrapped_attributes);
	// 	$this->assertNotFalse($scrapped_attributes);//if empty will become false. so this already implies it is not empty
	// }

	// public function testOutOfStockOption(){
	// 	$url="http://shop.nordstrom.com/S/4237966?cm_mmc=Linkshare-_-datafeed-_-women:bottoms:pant-_-5084034";
	// 	$a=new nordstrom_scrapper($url);
	// 	// if ($a->init_status===false){
	// 	// 	echo("cannot instantiate scrapper");
	// 	// }
	// 	$this->assertNotFalse($a->init_status);

	// 	$conf_sku="test";
	// 	$scrapped_attributes=$a->getScrappedAttributes($conf_sku);
	// 	$this->assertFalse($scrapped_attributes);
	// }

}
?>

