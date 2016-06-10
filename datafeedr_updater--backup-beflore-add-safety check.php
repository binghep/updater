<?php

// header('Content-type: application/json');
// require_once 'database/dbcontroller.php';

/*
category-wise datafeedr_updater
Sequence of Action:
			book-keeping: record current category in database. 
crux logic: step_1: for each product from datafeedr. write it to database table : 						step_1_datafeedr_results
			step_2: for each row in step_1_datafeedr_results, 
					 if is datafeedr category{
						run backcountry scrapper to scrape the size in detail 
						store this in database step_2_scrapped_results (record_id, merchant name,conf_sku, child_attributes, description, price, special price, brand, product_url)
						in child_attributes, "{"S":"50","M":"35","L":null}"
						
						generate magmi csv based on step_2 table.
						Call magmi_cli_tool to import csv.
					}else{
						generate magmi csv based on step_1_datafeedr_results.
						Call magmi_cli_tool to import csv.
					}
*/
class datafeedr_updater{
	public $db_handle='';
	public $magento_category_id='';
	public $magento_category_name='';
	public $ancestor_category_ids='';
	public $filter_strings;
	function __construct($cat_id) {
		$this->magento_category_id=$cat_id;
		//======================get category name==================
		require_once '../../app/Mage.php';
		Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
		
		$category = new Mage_Catalog_Model_Category();	
		$category->load($cat_id);//414 
		if(!$category->getId()) {
			return false;
		}else{
			$this->magento_category_name=$category->getName();
		}
		//---------------------------------------------------------
		require_once 'database/dbcontroller.php';
		$this->db_handle=new DBController();	
		//=====================get output csv cat ids==============
		$result=$this->init_all_ancestor_category_ids($cat_id);
		// var_dump($this->ancestor_category_ids);
		// var_dump("result is");
		// var_dump($result);
		if ($result===false){
			return false;
		}
		//=====================get filter strings==================
		$result=$this->init_filter_strings();
		if ($result===false){
			return false;//failed 
		}else{
			return true;//success
		}
	}

	public function getCurrProductSkus(){
		require_once "lib/CategoryProductIterator_api.php";
		$iterator=new category_product_iterator($this->magento_category_id);
		$curr_skus=$iterator->getAllDatafeedrProductSkus();
		return $curr_skus;
	}
	public function getIdsFromSkus($skus){
		require_once '../../app/Mage.php';
		Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
	
		if (count($skus)==0){
			return false;
		}
		$ids=array();
		foreach ($skus as $sku) {
			$id= Mage::getModel('catalog/product')->getIdBySku($sku);
			if (!is_null($id) && $id!==false) {
				array_push($ids, $id);
			}
		}
		return $ids;
	}
	function init_filter_strings(){
		require 'config.php';
		$strings=$filter_strings[''.$this->magento_category_id];
		var_dump($strings);
		if (is_null($strings)){
			echo 'category id is not in $filter_strings in config.php. Exiting...';
			$this->filter_strings=false;
		}else{
			$this->filter_strings=$strings;
		}
	}
	function write_log($object)
	{
	 	error_log($object."\n", 3, 'datafeedr_updater.log');
	    return true;
	}
	/*
	If cat is not 2nd or 3rd level, return false.
	Else save the "455,55" style in $this->ancestor_category_ids and return true.
	*/
	public function init_all_ancestor_category_ids($cat_id){
		require_once '../../app/Mage.php';
		Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
		
		$category = new Mage_Catalog_Model_Category();
		$category->load($cat_id);//414 
		// echo ("cat id is".$cat_id);
		if(!$category->getId()) {
			return false;
		}else{
			// var_dump($category);
			// var_dump($category->getLevel());
			$ancestor_category_ids=array();
			$cat_level=$category->getLevel();
			if ($cat_level==2){
				$this->ancestor_category_ids=''.$category->getId();
				return true;
			}elseif ($cat_level==3){
			// echo ("jdjfjd".$cat_level);
				$cat_array=array();
				array_push($cat_array,$category->getId());
				array_push($cat_array,$category->getData('parent_id'));
				$this->ancestor_category_ids=implode(",", $cat_array);
				return true;
			}//todo: add level 4
			else{
				return false;
			}
		}
	}

	
	// public function updateProductAttribute($productId,$attribute_code,$new_value){
	// 	$attribute_code_allowed_values=array("price","special_price");
	// 	$this->write_log("updating product's $attribute_code value to ".$new_value);
	// 	if (!in_array($attribute_code, $attribute_code_allowed_values)){
	// 		return false;
	// 	}
	// 	require_once '../../app/Mage.php';
	// 	Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
	// 	$array_product=array($productId);
	// 	// var_dump($array_product);
	// 	Mage::getSingleton('catalog/product_action')->updateAttributes($array_product, array($attribute_code => $new_value), 0);
	// }


	/*
	if $debug is false, only print out products for review, like before.
	else, finish step1, step2, and step3:
		1. step1: get product data from datafeedr api, then insert into step_1_datafeedr_results table
		2. step2: determine the category is simple product category or configurable product category, then instantiate corresponding api object, which scrape(configurable category) and generate a magmi csv. 
		3. step3: import the generated magmi csv.
	*/
	public function run(){
		echo ("\n--------running this category: ".$this->magento_category_name." ---".$this->magento_category_id."--------\n");
		require 'config.php';//has $simple_products_categories.
		require_once 'lib/datafeedr_api.php';
		$datafeedr_api=new datafeedr_api($this->ancestor_category_ids,$this->filter_strings,$debug,$num_products_per_page);

		$page_number=1;
		if ($debug==true){
			$datafeedr_api->printProducts($page_number);
			return 1;
		}
		//-----------------create step 1 table-------------------
		$datafeedr_api->insertProducts();//ready for magmi (pw inserted to product link)
		if (in_array($this->magento_category_id, $simple_products_categories)){
			//-----exporting csv from step 1 table then import using magmi-------
			echo ("\n~~~~~~~~~~simple category~~~~~~~~~~~\n");
			require_once 'lib/generate_csv_from_step1_table.php';//if inside $debug is true, then only generate one product in csv.
			$csv_exporter=new generate_csv_from_step1_table($this->magento_category_name);
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
			//---------call product manager to generate images for all products in this category---------
			
			$go_to_url=$product_manager_url."index.php?cat_id=".$this->magento_category_id."&secret_path=yes";//http://www.ipzmall.com/alice/product_manager/index.php?cat_id=446&secret_path=yes
			$this->curl($go_to_url);
			//-------------------------------------------------------------------------------------------
			sleep(5);
			$this->run_magmi($output_csv_name);			
			
		}else{
			//---------------------create step 2 table from step 1 table-------------
			require_once 'database/dbcontroller.php';
			$db_handle=new DBController();
			$query="truncate {$mysql_table_step_2_scrapped_results}";
			$result=$db_handle->runQuery($query);
			
			$query="select * from {$mysql_table_step_1_datafeedr_results}";
			// var_dump($query);
			$result=$db_handle->runQuery($query);
			// var_dump($result);
			foreach ($result as $row) {
				// var_dump($row);
				$datafeedr_buy_link=$row['datafeedr_buy_link'];
				$url=$row['url_for_scrapping'];
				$datafeedr_merchant=$row['datafeedr_merchant'];
				$brand=$row['brand'];//already Title Cased
				$scrapped_attributes=$this->scrape_attributes($url,$row['sku']);
				if (empty($scrapped_attributes) || $scrapped_attributes===false){echo "\n------scrape failed, skip this conf product------\n"; continue;}
				// var_dump($scrapped_attributes);
				$scrapped_attributes=json_encode($scrapped_attributes);
				echo "\n    |--json encoded scrapped attributes:------\n";
				// $scrapped_attributes=mysqli_escape_string($scrapped_attributes);
				echo "\n    $scrapped_attributes\n";
				$query="insert into {$mysql_table_step_2_scrapped_results} (cat_id,product_name,sku,product_desc,image_url,datafeedr_buy_link,price,saleprice,scrapped_attributes,brand,url_for_scrapping,datafeedr_merchant) values (\"{$row['cat_id']}\",\"{$row['product_name']}\",\"{$row['sku']}\",\"{$row['product_desc']}\",\"{$row['image_url']}\",\"$datafeedr_buy_link\",\"{$row['price']}\",\"{$row['saleprice']}\",\"$scrapped_attributes\",\"{$brand}\",\"$url\",\"$datafeedr_merchant\")";
				//,"13.17","{"conf_sku":"HUR00G2-BK","child_attributes":{"XL":{"price":21.95,"saleprice":13.17}}}","Hurley","
				//-----------make json string surrounded by single quote. otherwise mysql cannot recognise double quotes in json string.--------------
				$query=str_replace('"{', "'{", $query);
				$query=str_replace('}"', "}'", $query);

				// var_dump($query);
				$result=$db_handle->runQuery($query);
				if ($result===false){
					echo 'insert into step2 table failed.<br>';
				}
				// break;
			}
			//-----------finished creating step 2 table from step 1 table-----------
			//return;
			//--from step 2 table, generate magmi csv, then call magmi to import---
			echo ("\n~~~~~~~~~~configurable category~~~~~~~~~~~\n");
			require_once 'lib/generate_csv_from_step2_table.php';
			$csv_exporter=new generate_csv_from_step2_table($this->magento_category_name);

			if ($csv_exporter==-1){
				echo ("\n---cant instantiate generate_csv_from_step2_table object. exit...---\n"); 
				return -1;
			}
			$output_csv_name=$csv_exporter->run();
			if (empty($output_csv_name) || is_null($output_csv_name)){
				echo "\n---Output CSV path cannot be found---\n";
				return -1;
			}else{
				echo "\n---Output CSV Name: $output_csv_name---\n";
			}
			//---------call product manager to generate images for all products in this category---------
			$go_to_url=$product_manager_url."index.php?cat_id=".$this->magento_category_id."&secret_path=yes";
			// var_dump($go_to_url);
			echo "\n-------finished curl product manager to generate images-------\n";
			$this->curl($go_to_url);
			//-----------------------------------------------------------------
			// return 1;
			sleep(6);
			echo "\nRunning magmi to import csv for this category\n";
			$this->run_magmi($output_csv_name);
			echo "\n-----------Done. Returning from datafeedr_updater->run() ---------\n";
		}
	}
	
	function scrape_attributes($productUrl,$conf_sku){
		echo ("\n----------scrape_attributes() function-----------\n");
	    require_once("lib/getBackcountryProductSize_api.php");
	    $scrapper=new backcountry_scrapper($productUrl);
		// var_dump($scrapper->php_object);	    
		if ($scrapper->init_status===false){
			echo "\n---Error in datafeedr_updater->scrape_attributes() function: cannot find JS block(e.g. Out of Stock)---\n";
			return false;//("cannot instantiate scrapper");
		}
		// $scrapped_attributes=$scrapper->getScrappedAttibutes("BNC006F-BKMAR-S");
		$scrapped_attributes=$scrapper->getScrappedAttibutes($conf_sku);
		if ($scrapped_attributes!==false && !empty($scrapped_attributes)){
			// echo '<pre>';
			// var_dump($scrapped_attributes);
			// echo '</pre>';
	   	 	return $scrapped_attributes;
		}else{
			echo "\n---Error in scrape_attributes() function: getScrappedAttibutes() returned false---\n";
			return false;
		}

	}
	function curl($url) {
	    $ch = curl_init();  // Initialising cURL
	    curl_setopt($ch, CURLOPT_URL, $url);    // Setting cURL's URL option with the $url variable passed into the function
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); // Setting cURL's option to return the webpage data
	    $data = curl_exec($ch); // Executing the cURL request and assigning the returned data to the $data variable
	    curl_close($ch);    // Closing cURL
	    return $data;   // Returning the data from the function
	}

	public function run_magmi($output_csv_name){
		if (!empty($output_csv_name) && $output_csv_name!==false){
			// $working_shell_command='php5 /usr/share/nginx/www/ipzmall.com/alice/magmi/cli/magmi.cli.php -profile="pls_do_not_change_me" -mode="create"  -CSV:filename="/usr/share/nginx/www/ipzmall.com/alice/magmi_csv/alice_import/for_reindex.csv"';
			$shell_command='php5 /usr/share/nginx/www/ipzmall.com/alice/magmi/cli/magmi.cli.php -profile="pls_do_not_change_me" -mode="create"  -CSV:filename="/usr/share/nginx/www/ipzmall.com/alice/magmi_csv/alice_import/'.$output_csv_name.'"';
			var_dump($shell_command);
			$result=shell_exec($shell_command);
			// echo 'the magmi cli result is : '.$result;
			//result is always null. because magmi did not set this up. but it works.
			//---------------delete the csv--------------------
			// unlink('../magmi_csv/alice_import/'.$output_csv_name);
		}
	}
	public function run_magmi_reindex_all(){
		$csv_name="pls_do_not_change_me.for_reindex.csv";
		$shell_command='php5 /usr/share/nginx/www/ipzmall.com/alice/magmi/cli/magmi.cli.php -profile="pls_do_not_change_me_reindex" -mode="create"  -CSV:filename="/usr/share/nginx/www/ipzmall.com/alice/magmi_csv/alice_import/'.$csv_name.'"';
			var_dump($shell_command);
			$result=shell_exec($shell_command);
			echo '---just reindexed all---';
	}
}


?>