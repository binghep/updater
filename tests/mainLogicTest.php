<?php
require __DIR__.'/../config.php';
require_once __DIR__. '/../datafeedr_updater.php';
require_once __DIR__. '/../database/dbcontroller.php';
require_once __DIR__. '/../lib/datafeedr_api.php';
require_once __DIR__. '/../lib/generate_csv_from_step1_table.php';//if inside $debug is true, then only generate one product in csv.

class mainLogicTest extends PHPUnit_Framework_TestCase{
	
	protected $datafeedr_updater;
	protected $datafeedr_api;
	protected $db_handle;
	protected function setUp(){
		$db_handle=new DBController();	
		// write_report("------------Start Running category ".$cat_id."--------------",$report_name);
		$cat_id=849;
		$history_table="mage_datafeedr_auto_update_history";
		$query="select * from {$history_table} where cat_id=".$cat_id;
		$result=$db_handle->runQuery($query);
		if (is_null($result)){
			echo 'category id not in database. exiting.';
			return false;
		}
		//------------------------instantiate datafeedr object-------------------------
		$this->datafeedr_updater=new datafeedr_updater($cat_id);
		if ($this->datafeedr_updater===false){
			echo 'error in datafeedr_updater instantiation...';
			return false;
		}
		//------------------------start exceuting this category------------------------
		$this_event_id='';
		if ($debug==false){
			//-------------------step1: add a record to history table----------------
			$query="insert into {$history_table} (cat_id,last_update) values ('{$cat_id}',NOW())";
			// var_dump($query);
			$result=$db_handle->runQuery($query);
			if (!$result){
				echo 'cannot insert event row into {$history_table} table. exit';
				return false;
			}

			//-------------step2: get the id of the row you just inserted, log it in log file:-----------
			$query="select max(history_id) as id from {$history_table};";
			// var_dump($query);
			$result=$db_handle->runQuery($query);
			$this_event_id=$result[0]['id'];
			// var_dump($this_event_id);
			// write_report('$this_event_id is '.$this_event_id,$report_name);
			// return -1;
		}else{
			echo 'This is debug mode.<br>';
			// return null;
		}

		//----------------------------Crux is in one line--------------------
		// $result=$datafeedr_updater->run();//will finish 2 tables if configurable category, finish 1 table if simple category. Also will generate csv and call magmi to import.

	}
	//$datafeedr_updater->run();
	public function testRunFunctionPartOne(){
		//==============Unit Test Only==========================
		$num_products_per_page=2;
		$max_num_products_needed=1;
		//======================================================
		echo ("\n--------running this category: ".$this->datafeedr_updater->magento_category_name." ---".$this->datafeedr_updater->magento_cat_id."--------\n");
		// require __DIR__.'../config.php';//has $simple_products_categories.
		$this->datafeedr_api=new datafeedr_api($this->datafeedr_updater->ancestor_cat_ids,$this->datafeedr_updater->filter_strings,$debug,$num_products_per_page);

		$page_number=1;
		if ($debug==true){
			$this->datafeedr_api->printProducts($page_number);
			return 1;
		}
		//-----------------create step 1 table-------------------
		$this->datafeedr_api->insertProducts();//ready for magmi (pw inserted to product link)
	}
	//simple category: just generate csv for 1 product.
	public function testRunFunctionPartTwo(){
		//-----exporting csv from step 1 table then import using magmi-------
		echo ("\n~~~~~~~~~~simple category~~~~~~~~~~~\n");
		$csv_exporter=new generate_csv_from_step1_table($this->datafeedr_updater->magento_category_name);
		if ($csv_exporter==false){
			echo "\ncant instantiate generate_csv_from_step1_table object.exit.\n";
			return -1;
		}

		$output_csv_name=$csv_exporter->run();
		if (empty($output_csv_name) || is_null($output_csv_name)){
			echo "\nOutput CSV path cannot be found.\n";
			return -1;
		}else{
			echo "\nOutput CSV Name: $output_csv_name\n";
		}
	}
	// public function testBothColorAndSizeOption(){
	// 	$url="http://shop.nordstrom.com/S/3977168?cm_mmc=Linkshare-_-datafeed-_-plus_women:bottoms:pant-_-1063136";
	// 	$a=new nordstrom_scrapper($url);
	// 	// if ($a->init_status===false){
	// 	// 	echo("cannot instantiate scrapper");
	// 	// }
	// 	$this->assertNotFalse($a->init_status);

	// 	$conf_sku="test";
	// 	$scrapped_attributes=$a->getScrappedAttibutes($conf_sku);
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
	// 	$scrapped_attributes=$a->getScrappedAttibutes($conf_sku);
	// 	$this->assertFalse($scrapped_attributes);
	// }
}
?>

