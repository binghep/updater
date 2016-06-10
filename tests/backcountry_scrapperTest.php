<?php
require __DIR__.'/../lib/getBackcountryProductSize_api.php';
// use PHPUnit\Framework\TestCase;

/**
* Only takes the datafeedr affliate link as param. 
* Will reroute to real nordstrom product page.
*/

class backcountry_scrapperTest extends PHPUnit_Framework_TestCase{
	
	public function testBothColorAndSizeOption(){
		$url="http://www.backcountry.com/rip-curl-go-wild-maxi-dress-womens?CMP_SKU=RIP00I9&MER=0406&utm_source=CJ&utm_source=Affiliate&mr:trackingCode=0A89AFA8-4EB4-E411-BDDA-BC305BF82376&mr:referralID=NA&AID=10279061&PID=4623576";
		$a=new backcountry_scrapper($url);
		$this->assertNotFalse($a->init_status);

		$conf_sku="RIP00I9-HEGRE";
		$scrapped_attributes=$a->getScrappedAttributes($conf_sku);
		// var_dump($scrapped_attributes);
		$this->assertNotFalse($scrapped_attributes);//if empty will become false. so this already implies it is not empty
	}
	public function testStyleNSizeOption_actuallly_OneSize(){
		$url="http://www.backcountry.com/roxy-lima-beanie-teenie-toddler-girls?CMP_SKU=QKS01YD";
		$a=new backcountry_scrapper($url);
		$this->assertNotFalse($a->init_status);

		$conf_sku="QKS01YD-AZA";
		$scrapped_attributes=$a->getScrappedAttributes($conf_sku);
		$this->assertNotFalse($scrapped_attributes);
	}

	public function testSomething(){
		$this->assertTrue(true,'lala');
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	// public function testOutOfStockOption(){
	// 	$url="http://shop.nordstrom.com/S/4237966?cm_mmc=Linkshare-_-datafeed-_-women:bottoms:pant-_-5084034";
	// 	$a=new nordstrom_scrapper($url);
	// 	$this->assertNotFalse($a->init_status);

	// 	$conf_sku="test";
	// 	$scrapped_attributes=$a->getScrappedAttributes($conf_sku);
	// 	$this->assertFalse($scrapped_attributes);
	// }
}
?>

