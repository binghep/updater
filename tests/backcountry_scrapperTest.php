<?php
require __DIR__.'/../lib/backcountry_scrapper_A.php';
require __DIR__.'/../lib/backcountry_scrapper_B.php';

// use PHPUnit\Framework\TestCase;

/**
* Only takes the datafeedr affliate link as param. 
* Will reroute to real nordstrom product page.
*/

class backcountry_scrapperTest extends PHPUnit_Framework_TestCase{
	/**
	 * Scrapper A
	 */
	public function testBothColorAndSizeOption_A(){
		$url="http://www.backcountry.com/rip-curl-go-wild-maxi-dress-womens?CMP_SKU=RIP00I9&MER=0406&utm_source=CJ&utm_source=Affiliate&mr:trackingCode=0A89AFA8-4EB4-E411-BDDA-BC305BF82376&mr:referralID=NA&AID=10279061&PID=4623576";
		$a=new backcountry_scrapper_A($url);
		$this->assertFalse($a->init_status);//because this url only works for scrapper_B ?

		// $conf_sku="RIP00I9-HEGRE";
		// $scrapped_attributes=$a->getScrappedAttributes($conf_sku);
		// // var_dump($scrapped_attributes);
		// $this->assertNotFalse($scrapped_attributes);//if empty will become false. so this already implies it is not empty
	}
	/**
	 * Scrapper B
	 */
	public function testBothColorAndSizeOption_B(){
		$url="http://www.backcountry.com/rip-curl-go-wild-maxi-dress-womens?CMP_SKU=RIP00I9&MER=0406&utm_source=CJ&utm_source=Affiliate&mr:trackingCode=0A89AFA8-4EB4-E411-BDDA-BC305BF82376&mr:referralID=NA&AID=10279061&PID=4623576";
		$a=new backcountry_scrapper_B($url);
		$this->assertNotFalse($a->init_status);

		$conf_sku="RIP00I9-HEGRE";
		$scrapped_attributes=$a->getScrappedAttributes($conf_sku);
		// var_dump($scrapped_attributes);
		$this->assertNotFalse($scrapped_attributes);//if empty will become false. so this already implies it is not empty
	}

	/**
	 * Scrapper A
	 */
	public function testStyleNSizeOption_actuallly_OneSize_A(){
		$url="http://www.backcountry.com/roxy-lima-beanie-teenie-toddler-girls?CMP_SKU=QKS01YD";
		$a=new backcountry_scrapper_A($url);
		$this->assertFalse($a->init_status);//because only works for scrapper B

		// $conf_sku="QKS01YD-AZA";
		// $scrapped_attributes=$a->getScrappedAttributes($conf_sku);
		// $this->assertNotFalse($scrapped_attributes);
	}	
	/**
	 * Scrapper B
	 */
	public function testStyleNSizeOption_actuallly_OneSize_B(){
		$url="http://www.backcountry.com/roxy-lima-beanie-teenie-toddler-girls?CMP_SKU=QKS01YD";
		$a=new backcountry_scrapper_B($url);
		$this->assertNotFalse($a->init_status);

		$conf_sku="QKS01YD-AZA";
		$scrapped_attributes=$a->getScrappedAttributes($conf_sku);
		$this->assertNotFalse($scrapped_attributes);
		// var_dump($scrapped_attributes);
	}

	// public function testSomething(){
	// 	$this->assertTrue(true,'lala');
	// 	$this->markTestIncomplete('This test has not been implemented yet.');
	// }

	/**
	 * Scrapper A
	 */
	public function testThree_A(){
		$url="http://www.backcountry.com/stoic-softshell-jacket-womens?CMP_SKU=SIC000R";
		$a=new backcountry_scrapper_A($url);//ONLY WORKS FOR SCRAPPER A
		$this->assertNotFalse($a->init_status);

		$conf_sku="SIC000R-BK";
		$scrapped_attributes=$a->getScrappedAttributes($conf_sku);
		$this->assertNotFalse($scrapped_attributes);
		// var_dump($scrapped_attributes);
	}

	
	// public function testOutOfStockOption(){
	// 	$url="http://shop.nordstrom.com/S/4237966?cm_mmc=Linkshare-_-datafeed-_-women:bottoms:pant-_-5084034";
	// 	$a=new nordstrom_scrapper($url);
	// 	$this->assertNotFalse($a->init_status);

	// 	$conf_sku="test";
	// 	$scrapped_attributes=$a->getScrappedAttributes($conf_sku);
	// 	$this->assertFalse($scrapped_attributes);
	// }


	// public function testFour_A(){//very few products are not passing scrapper. ignore it for now.
	// 	$url="http://www.backcountry.com/mountain-hardwear-wicked-lite-t-shirt-long-sleeve-womens?CMP_SKU=MHW2602";
	// 	$a=new backcountry_scrapper_A($url);//ONLY WORKS FOR SCRAPPER A
	// 	$this->assertNotFalse($a->init_status);

	// 	$conf_sku="SIC000R-BK";
	// 	$scrapped_attributes=$a->getScrappedAttributes($conf_sku);
	// 	$this->assertNotFalse($scrapped_attributes);
	// }
}
?>

