<?php
// DEFINE("DIR",);
/*var_dump(getcwd().'/');//string(57) "/usr/share/nginx/www/ipzmall.com/alice/datafeedr_updater/tests/"
var_dump(__FILE__);//string(57) "/usr/share/nginx/www/ipzmall.com/alice/datafeedr_updater/datafeedr_updater.php"
var_dump(__DIR__);//string(56) "/usr/share/nginx/www/ipzmall.com/alice/datafeedr_updater" 
// var_dump("./");
var_dump(__DIR__.'/..');
*/

// return;
// header('Content-type: application/json');
// require_once DIR.'database/dbcontroller.php';

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
	public $magento_cat_id='';
	public $magento_category_name='';
	public $ancestor_cat_ids='';
	public $filter_strings;
	public $report_name='';
	function __construct($cat_id) {
		//======================get category name==================
		// $this->magento_category_name=$category->getName();
		$this->magento_category_name=$this->get_full_cat_name($cat_id);
		if ($this->magento_category_name===false){return false;}
		//=====================write report====================
		$this->report_name=date("Y-m-d");
		$this->write_report("<tr><td>".date("m-d")."</td><td>".date('H:i')."</td><td>".$cat_id."</td><td>".$this->magento_category_name."</td>");
		$this->magento_cat_id=$cat_id;
		//---------------------------------------------------------
		require_once __DIR__.'/database/dbcontroller.php';
		$this->db_handle=new DBController();	
		//=====================get output csv cat ids==============
		$result=$this->init_all_ancestor_cat_ids($cat_id);
		// var_dump($this->ancestor_cat_ids);
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
	/**
	* Returns full cat name (e.g. Men/Bottom, Nutrition/Vitamins and Supplements)
	* On error(when cat id is not valid), return false;
	*/
	protected function get_full_cat_name($cat_id){
		require_once __DIR__.'/../../app/Mage.php';
		Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
		
		$category = new Mage_Catalog_Model_Category();	
		$category->load($cat_id);//414 
		if(!$category->getId()) {
			return false;
		}

		// $cat_id=$category->getId();
		// if($cat_id) {//valid param
		    //----------get parent cat name if level 2 or below--------------
		    $parent_cat = Mage::getModel('catalog/category')
		        ->load($cat_id)
		        ->getParentCategory();

		    $parent_cat_name=is_null($parent_cat)?'':$parent_cat->getName().'+';//replace / by +
		    $parent_cat_id=$parent_cat->getId();
		    if ($parent_cat->getLevel()==1 ||$parent_cat->getLevel()==0 ){$parent_cat_name='';}
		    //----------get grandparent cat name if level 2 or below--------------
		    $grandpa_cat=Mage::getModel('catalog/category')
		        ->load($parent_cat_id)
		        ->getParentCategory();

		    $grandpa_cat_name=is_null($grandpa_cat)?'':$grandpa_cat->getName().'+';
		    // var_dump($grandpa_cat->debug());
		    if ($grandpa_cat->getLevel()==1 ||$grandpa_cat->getLevel()==0){$grandpa_cat_name='';}
		    //-----------------combine all three names--------------------
		    $full_cat_name=$grandpa_cat_name.$parent_cat_name.$category->getName();
		    echo "\n$full_cat_name\n";
		    return $full_cat_name;
		// }
	}
	public function getCurrProductSkus(){
		require_once __DIR__.'/lib/CategoryProductIterator_api.php';
		$iterator=new category_product_iterator($this->magento_cat_id);
		$curr_skus=$iterator->getAllDatafeedrProductSkus();
		return $curr_skus;
	}
	public function getIdsFromSkus($skus){
		require_once __DIR__.'/../../app/Mage.php';
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
		require __DIR__.'/config.php';
		$strings=$filter_strings[''.$this->magento_cat_id];
		var_dump($strings);
		if (is_null($strings)){
			echo 'category id is not in $filter_strings in config.php. Exiting...';
			$this->filter_strings=false;
		}else{
			$this->filter_strings=$strings;
		}
	}
	function write_report($object)
	{
	 	error_log($object, 3, 'report/'.$this->report_name.'.html');
	    return true;
	}
	/*
	If cat is not 2nd or 3rd level, return false.
	Else save the "455,55" style in $this->ancestor_cat_ids and return true.
	*/
	public function init_all_ancestor_cat_ids($cat_id){
		// require_once __DIR__.'/../../app/Mage.php';
		// Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
		
		$category = new Mage_Catalog_Model_Category();
		$category->load($cat_id);//414 
		// echo ("cat id is".$cat_id);
		if(!$category->getId()) {
			return false;
		}else{
			// var_dump($category);
			// var_dump($category->getLevel());
			$ancestor_cat_ids=array();
			$cat_level=$category->getLevel();
			if ($cat_level==2){
				$this->ancestor_cat_ids=''.$category->getId();
				return true;
			}elseif ($cat_level==3){
			// echo ("jdjfjd".$cat_level);
				$cat_array=array();
				array_push($cat_array,$category->getId());
				array_push($cat_array,$category->getData('parent_id'));
				$this->ancestor_cat_ids=implode(",", $cat_array);
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
	// 	require_once DIR.'../../app/Mage.php';
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
		require __DIR__.'/config.php';
		echo ("\n--------running this category: ".$this->magento_category_name." ---".$this->magento_cat_id."--------\n");
		require_once __DIR__.'/lib/datafeedr_api.php';
		$datafeedr_api=new datafeedr_api($this->ancestor_cat_ids,$this->filter_strings,$debug,$num_products_per_page);

		$page_number=1;
		if ($debug==true){
			$datafeedr_api->printProducts($page_number);
			return 1;
		}
		//-----------------create step 1 table-------------------
		$datafeedr_api->insertProducts();//ready for magmi (pw inserted to product link)
		// var_dump($simple_products_categories);
		if (in_array($this->magento_cat_id, $simple_products_categories)){
			//=======================write report================================
			$num_curr_visible_items=count($this->getCurrProductSkus());
			$num_rows_in_step_1_table=null;
			$query="select COUNT(*) as num_rows from {$mysql_table_step_1_datafeedr_results};";
			$result=$this->db_handle->runQuery($query);
			if (is_null($result)){
				$num_rows_in_step_1_table="NONE";
			}else{
				$num_rows_in_step_1_table=$result[0]['num_rows'];
			}
			$this->write_report("<td>".$num_curr_visible_items."</td><td>".$num_rows_in_step_1_table."(in step 1 table)</td><td>N/A</td>");
			//=======================finish writing==============================
			//-----exporting csv from step 1 table then import using magmi-------
			echo ("\n~~~~~~~~~~simple category~~~~~~~~~~~\n");
			require_once __DIR__.'/lib/generate_csv_from_step1_table.php';//if inside $debug is true, then only generate one product in csv.
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
			
			$go_to_url=$product_manager_url."index.php?cat_id=".$this->magento_cat_id."&secret_path=yes";//http://www.ipzmall.com/alice/product_manager/index.php?cat_id=446&secret_path=yes
			$this->curl($go_to_url);
			//-------------------------------------------------------------------------------------------
			sleep(5);
			$this->run_magmi($output_csv_name);			
			
		}else{
			//---------------------create step 2 table from step 1 table-------------
			require_once __DIR__.'/database/dbcontroller.php';
			$db_handle=new DBController();
			$query="truncate {$mysql_table_step_2_scrapped_results}";
			$result=$db_handle->runQuery($query);
			
			$query="select * from {$mysql_table_step_1_datafeedr_results}";
			// var_dump($query);
			$result=$db_handle->runQuery($query);
			// var_dump($result);
			$num_rows_in_step_1_table=count($result);
			foreach ($result as $row) {
				// var_dump($row);
				$datafeedr_buy_link=$row['datafeedr_buy_link'];
				$url=$row['url_for_scrapping'];
				$datafeedr_merchant=$row['datafeedr_merchant'];
				$brand=$row['brand'];//already Title Cased
				echo "\nScrapping url:\n".$url;
				$scrapped_attributes=$this->scrape_attributes($datafeedr_merchant, $url,$row['sku']);

				if ($scrapped_attributes===false){
					echo "\n------scrape failed, skip this conf product------\n"; 
					continue;
				}
				var_dump($scrapped_attributes);

				// echo "\n    |--json encoded scrapped attributes:------\n";
				// $scrapped_attributes=mysqli_escape_string($scrapped_attributes);
				// echo "\n    $scrapped_attributes\n";
				$query=null;
				if ($datafeedr_merchant=="Backcountry.com"){
					$scrapped_attributes=json_encode($scrapped_attributes,JSON_FORCE_OBJECT);

					//string(33) "[{"price":"120"},{"price":"100"}]"  (if not have JSON_FORCE_OBJECT) refer to lib/json_encode.php
					//string(41) "{"0":{"price":"120"},"1":{"price":"100"}}"
					$query="insert into {$mysql_table_step_2_scrapped_results} (cat_id,product_name,sku,product_desc,image_url,datafeedr_buy_link,price,saleprice,scrapped_attributes,brand,url_for_scrapping,datafeedr_merchant) values (\"{$row['cat_id']}\",\"{$row['product_name']}\",\"{$row['sku']}\",\"{$row['product_desc']}\",\"{$row['image_url']}\",\"$datafeedr_buy_link\",\"{$row['price']}\",\"{$row['saleprice']}\",\"$scrapped_attributes\",\"{$brand}\",\"$url\",\"$datafeedr_merchant\")";
				}elseif($datafeedr_merchant=="NORDSTROM.com"){
					//==============Remove ", Size xxx" in name===========
					$conf_product_name=$row['product_name'];
					$pos=strpos($conf_product_name, ", Size ");
					if ($pos!==false){
						$conf_product_name=substr($conf_product_name, 0, $pos);
					}
					//====================================================
					$real_url_to_scrape=$scrapped_attributes['real_url_to_scrape'];
					
					$new_scrapped_attributes=array();
					$new_scrapped_attributes['conf_sku']=$scrapped_attributes['conf_sku'];
					$new_scrapped_attributes['child_attributes']=$scrapped_attributes['child_attributes'];

					var_dump($new_scrapped_attributes);
					$new_scrapped_attributes=json_encode($new_scrapped_attributes,JSON_FORCE_OBJECT);
					$query="insert into {$mysql_table_step_2_scrapped_results} (cat_id,product_name,sku,product_desc,image_url,datafeedr_buy_link,price,saleprice,scrapped_attributes,brand,url_for_scrapping,datafeedr_merchant) values (\"{$row['cat_id']}\",\"{$conf_product_name}\",\"{$row['sku']}\",\"{$row['product_desc']}\",\"{$scrapped_attributes['image']}\",\"$datafeedr_buy_link\",\"{$row['price']}\",\"{$row['saleprice']}\",\"$new_scrapped_attributes\",\"{$brand}\",\"{$real_url_to_scrape}\",\"$datafeedr_merchant\")";
					// var_dump($query);
					// return;
				}
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
			//===========finished creating step 2 table from step 1 table==========
			//=======================write report================================
			$num_curr_visible_items=count($this->getCurrProductSkus());
			$num_rows_in_step_2_table=null;
			$query="select COUNT(*) as num_rows from {$mysql_table_step_2_scrapped_results};";
			$result=$this->db_handle->runQuery($query);
			if (is_null($result)){
				$num_rows_in_step_2_table="NONE";
			}else{
				$num_rows_in_step_2_table=$result[0]['num_rows'];
			}
			$this->write_report("<td>".$num_curr_visible_items."(current visible)</td><td>".$num_rows_in_step_1_table."(in step 1 table)</td><td>".$num_rows_in_step_2_table."(in step 2 table)</td>");
			//=======================finish writing==============================
			//======safety check: if step 2 table is less than 70 rows, do not update. ======
			if (is_null($result)){
				echo "\nERROR, no rows in step 2 table, abort this category. \n";
				return -1;
			}else{
				$num_rows=$result[0]['num_rows'];
				if ($num_rows<30){
					echo "\nERROR, there are only $num_rows rows in step 2 table, abort this category. \n";
					$this->write_report("<td>less than 70 rows in step 2 table, should have aborted but did not.</td>");
					// return -1;
				}
			}
			//==========from step 2 table, generate magmi csv, then call magmi to import==========
			echo ("\n~~~~~~~~~~configurable category~~~~~~~~~~~\n");
			require_once __DIR__.'/lib/generate_csv_from_step2_table.php';
			$a=new generate_csv_from_step2_table($this->magento_category_name);

			if (!is_null($a->error)){
				echo ("\n---cant instantiate generate_csv_from_step2_table object. exit...---\n"); 
				return -1;
			}
			$output_csv_name=$a->run();
			if (empty($output_csv_name) || is_null($output_csv_name)){
				echo "\n---Output CSV path cannot be found---\n";
				return -1;
			}else{
				echo "\n---Output CSV Name: $output_csv_name---\n";
			}
			//---------call product manager to generate images for all products in this category---------
			$go_to_url=$product_manager_url."index.php?cat_id=".$this->magento_cat_id."&secret_path=yes";
			// var_dump($go_to_url);
		$this->curl($go_to_url);
		sleep(10);
			echo "\n-------finished curl product manager to generate images-------\n";
			//-----------------------------------------------------------------
			echo "\nRunning magmi to import csv for this category\n";
		$this->run_magmi($output_csv_name);
			echo "\n-----------Done importing csv with magmi. Returning from datafeedr_updater->run() ---------\n";
		}
		$this->write_report("<td>Finished</td>");
	}
	/**
	* param: 
	* returns array|false
	*/
	function scrape_attributes($datafeedr_merchant,$productUrl,$conf_sku){
		echo ("\n----------scrape_attributes() function-----------\n");
		if ($datafeedr_merchant=="Backcountry.com"){
		    require_once(__DIR__."/lib/backcountry_scrapper_A.php");
		    //-------------use scrapper A----------------------------
		    $scrapper_A=new backcountry_scrapper_A($productUrl);
		    if ($scrapper_A->init_status===true){
		    	// $scrapped_attributes=$scrapper->getScrappedAttributes("BNC006F-BKMAR-S");
				$scrapped_attributes=$scrapper_A->getScrappedAttributes($conf_sku);

				if ($scrapped_attributes!==false){
			   	 	return $scrapped_attributes;
				}else{
					echo "\n---Error in scrape_attributes() function: getScrappedAttributes() returned false---\n";
					return false;
				}
		    }
		    unset($scrapper_A);
		    //----------A is not suitable for this page json structure, use scrapper B----------
		    require_once(__DIR__."/lib/backcountry_scrapper_B.php");
		    $scrapper_B=new backcountry_scrapper_B($productUrl);
			// var_dump($scrapper->php_object);
			if ($scrapper_B->init_status===true){
		    	// $scrapped_attributes=$scrapper->getScrappedAttributes("BNC006F-BKMAR-S");
				$scrapped_attributes=$scrapper_B->getScrappedAttributes($conf_sku);

				if ($scrapped_attributes!==false){
			   	 	return $scrapped_attributes;
				}else{
					echo "\n---Error in scrape_attributes() function: getScrappedAttributes() returned false---\n";
					return false;
				}
		    }else{//neither A nor B works
				echo "\n---Error in datafeedr_updater->scrape_attributes() function: cannot find JS block(e.g. Out of Stock) in neither A nor B scrapper---\n";
				return false;//("cannot instantiate scrapper");
			}
		}elseif ($datafeedr_merchant=="NORDSTROM.com"){
		    require_once(__DIR__."/lib/nordstrom_scrapper.php");
			$scrapper=new nordstrom_scrapper($productUrl);
			if ($scrapper->init_status===false){
				// die("cannot instantiate scrapper");
				echo "\n---Abort this product. In datafeedr_updater->scrape_attributes() function: cannot find JS block(e.g. Out of Stock)--\n";
				return false;//("cannot instantiate scrapper");
			}
			$scrapped_attributes=$scrapper->getScrappedAttributes($conf_sku);
			if ($scrapped_attributes!==false){
		   	 	return $scrapped_attributes;
			}else{
				echo "\n---Abort this product's scrapped data. In scrape_attributes() function: getScrappedAttributes() returned false (Reason 1: this product's scrapped data has no size info, meaning the product has color option only. Reason 2: product not avaiable)---\n";
				return false;
			}
		}
		return false;
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