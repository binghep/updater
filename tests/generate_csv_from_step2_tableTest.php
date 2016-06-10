<?php
require __DIR__.'/../lib/generate_csv_from_step2_table.php';


class generate_csv_from_step2_tableTest extends PHPUnit_Framework_TestCase{
	
	public function testExportCsv(){
		$a=new generate_csv_from_step2_table("Test_category");
		$this->assertNull($a->error);//means no error.
		$output_csv_name=$a->run();
		$this->assertNotNull($output_csv_name);
		$this->assertNotEmpty($output_csv_name);
		echo $output_csv_name;
	}
	// public function testStyleNSizeOption_actuallly_OneSize(){
	// 	$url="http://www.backcountry.com/roxy-lima-beanie-teenie-toddler-girls?CMP_SKU=QKS01YD";
	// 	$a=new backcountry_scrapper($url);
	// 	$this->assertNotFalse($a->init_status);

	// 	$conf_sku="QKS01YD-AZA";
	// 	$scrapped_attributes=$a->getScrappedAttibutes($conf_sku);
	// 	$this->assertNotFalse($scrapped_attributes);
	// }



	// public function testOutOfStockOption(){
	// 	$url="http://shop.nordstrom.com/S/4237966?cm_mmc=Linkshare-_-datafeed-_-women:bottoms:pant-_-5084034";
	// 	$a=new nordstrom_scrapper($url);
	// 	$this->assertNotFalse($a->init_status);

	// 	$conf_sku="test";
	// 	$scrapped_attributes=$a->getScrappedAttibutes($conf_sku);
	// 	$this->assertFalse($scrapped_attributes);
	// }
}
?>

