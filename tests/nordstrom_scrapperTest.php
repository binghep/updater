<?php
require __DIR__.'/../lib/nordstrom_scrapper.php';
// use PHPUnit\Framework\TestCase;

/**
* Only takes the datafeedr affliate link as param. 
* Will reroute to real nordstrom product page.
*/

class nordstrom_scrapperTest extends PHPUnit_Framework_TestCase{
	
	public function testOnlyColorOption(){
		$url="http://shop.nordstrom.com/S/4247661?cm_mmc=Linkshare-_-datafeed-_-infant_unisex:baby_accessories:baby_furniture-_-5091901";
		$a=new nordstrom_scrapper($url);
		// if ($a->init_status===false){
		// 	echo("cannot instantiate scrapper");
		// }
		$this->assertNotFalse($a->init_status);

		$conf_sku="test";
		$scrapped_attributes=$a->getScrappedAttributes($conf_sku);
		// var_dump($nordstrom_scrapper->php_object);
		$this->assertFalse($scrapped_attributes);//because this product only has color. no size option. so skip this product by returning false.
	}

	
	public function testBothColorAndSizeOption(){
		$url="http://shop.nordstrom.com/S/3977168?cm_mmc=Linkshare-_-datafeed-_-plus_women:bottoms:pant-_-1063136";
		$a=new nordstrom_scrapper($url);
		// if ($a->init_status===false){
		// 	echo("cannot instantiate scrapper");
		// }
		$this->assertNotFalse($a->init_status);

		$conf_sku="test";
		$scrapped_attributes=$a->getScrappedAttributes($conf_sku);
		$this->assertNotFalse($scrapped_attributes);//if empty will become false. so this already implies it is not empty
	}

	public function testOutOfStockOption(){
		$url="http://shop.nordstrom.com/S/4237966?cm_mmc=Linkshare-_-datafeed-_-women:bottoms:pant-_-5084034";
		$a=new nordstrom_scrapper($url);
		// if ($a->init_status===false){
		// 	echo("cannot instantiate scrapper");
		// }
		$this->assertNotFalse($a->init_status);

		$conf_sku="test";
		$scrapped_attributes=$a->getScrappedAttributes($conf_sku);
		$this->assertFalse($scrapped_attributes);
	}
}
?>

