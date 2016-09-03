<?php
//-----------------test case for these two seperate functions---------------
// fill_clearance_percent_off();
// delete_all_disabled_datafeedr_product();
// return;
?>
<?php
/*
responsible for generating csv and updating all categories. 
*/
// return;

require __DIR__.'/config.php';
require __DIR__.'/database/dbcontroller.php';
$db_handle=new DBController();	

// $_GET['category_id']=662;//women->top
// $_GET['category_id']=697;//kids->baby clothing
// $_GET['category_id']=672;//women->sweaters
// $_GET['category_id']=844;//headphone

// $category_id=702;
// $this_event_id=do_one_category_only($category_id,$db_handle);

// disable_obsolete_products_and_reindex($db_handle,"lala");
// return;

$do_one_category_only=(isset($_GET['category_id'])&&is_numeric($_GET['category_id']))?$_GET['category_id']:null;

	date_default_timezone_set('America/Los_Angeles');
    $tdate = date("Y-m-d");//cannot have H:i because this function returns different H:i for different categories. also called in datafeedr_updater to set the $report variable.
    $file_name="report/".$tdate.'.html';

if (!is_null($do_one_category_only)){
	$cat_id=$_GET['category_id'];//846
	do_one_category_only($cat_id,$db_handle,$file_name);
	// disable_obsolete_products_and_reindex($db_handle);
}else{
	$result=go_over_all($db_handle,$file_name);
	if ($result) {
		echo "\ngo_over_all() function returned true, so calling last 2 steps to: (1) change all datafeedr product position. (2) disable obsolete products in all categories (3) generate give_magmi_to_delete.csv for all disabled datafeedr products \n";
		return;
		echo "\n========================(1) Changing all datafeedr product position to 300========================\n";
		//---------------(1) change all datafeedr product position-----------------
		$change_position_url="http://www.ipzmall.com/alice/datafeedr_updater/update_position_datafeedr_products_in_category.php";
		$html=curl($change_position_url);
		if (strpos($html, "Success")!==false){
			echo "\nSuccessfully changed all product position.\n";
		}else{
			echo "\nFail: fail to change product position.\n";
		}
			write_report("<div>(clean up step 1) Finished step 1.</div>",$file_name);
		//-----------------(2)-----------------------
		fill_clearance_percent_off();
		fill_denim_type();
		add_4_products_to_cat_854();//"Popular" category.
			write_report("<div>(clean up step 2) Finished step 2.</div>",$file_name);
		//-----------------(3)-----------------------
		disable_obsolete_products_and_reindex($db_handle,$file_name);
			write_report("<div>(clean up step 3) Finished Re-indexing All.</div>",$file_name);
		//-----------------(4)-----------------------
		delete_all_disabled_datafeedr_product();
			write_report("<div>(clean up step 4) Finished Deleting All obsolete products and their children.</div>",$file_name);
		//-----------------(5)-----------------------
		remove_old_csv();
		$msg="Thanks for reading. My task is finished. Bye";
		echo "\n$msg\n";
			write_report("<div style='font-weight:bold;'>$msg</div>",$file_name);
	}else{
		$msg="\ngo_over_all() function returned false, error somewhere, so I did not call functions in clean up steps (1)(2)(3)(4). Bye.\n";
		echo $msg;
			write_report("<div>$msg</div>",$file_name);
	}
	send_report_via_email($file_name);
}

function remove_old_csv(){
	include __DIR__."/lib/remove_old_csv_files.php";
}
function send_report_via_email($file_name){
	require '/usr/share/nginx/www/ipzmall.com/alice/PHPMailer/PHPMailer-5.2.14/send_email_api.php';
	$email_title="datafeedr_updater REPORT";
	$email_body=file_get_contents($file_name);
	$recipents=["pengx077@gmail.com","alice@1661hk.com"];
	$send_email_api=new send_email_api($email_title,$email_body,$recipents);
	$send_email_api->send();
		
}
function curl($url) {
    $ch = curl_init();  // Initialising cURL
    curl_setopt($ch, CURLOPT_URL, $url);    // Setting cURL's URL option with the $url variable passed into the function
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); // Setting cURL's option to return the webpage data
    $data = curl_exec($ch); // Executing the cURL request and assigning the returned data to the $data variable
    curl_close($ch);    // Closing cURL
    return $data;   // Returning the data from the function
}

function write_report($object,$file_name)
{  
	// echo "$object<br>";
 	error_log("\n".$object."\n", 3, $file_name);
    return true;
}
function fill_clearance_percent_off(){
	echo "\n---------Filling clearance percent off attribute of all products---------\n";
	require __DIR__.'/lib/fill_clearance_percent_off.php';
	$thirty_off_option_id=1921;
	$fourty_off_option_id=1922;
	$fifty_off_option_id=1923;
	$fill_fifty_percent_off=new fill_clearance_percent_off($thirty_off_option_id,$fourty_off_option_id,$fifty_off_option_id);
	echo "\n-----------Done Filling clearance_percent_off attribute--------\n";
}
function fill_denim_type(){
	echo "\n---------Filling denim_type attribute---------\n";
	require __DIR__.'/lib/fill_denim_type.php';
	//--------------------------test case 1-----------------------------
	$denim_type=array("straight"=>"1953",
						"Skinny"=>"1952",
						"Boyfriend"=>"1951",
						"Bootcut"=>"1950",
						"Flare"=>"1949",
						"DISTRESSED"=>"1947"
						);
	$fill_denim_type=new fill_denim_type($denim_type);
	echo "\n-----------Done Filling denim_type attribute--------\n";
}
/**
* Add 4 products from Women->Dresses(cat_id: 462) to Popular category(854).
*/
function add_4_products_to_cat_854(){
	require_once __DIR__.'/../../app/Mage.php';
	Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
	require __DIR__.'/lib/add_cat_to_products.php';
	echo "\n=================Adding 4 Dresses to Popular Category=================\n";
	//------------test case 1:pass 4 products to class constructor---------------
	$cat_id_of_popular_category=854;//854 is cat id for "popular" category.
	$cat_id_of_dresses_category=462;//854 is cat id for "popular" category.
	$category = Mage::getModel('catalog/category')->load($cat_id_of_dresses_category);
	$product_per_page=4;
	$p=1;
	$four_product_collection = Mage::getModel('catalog/product')
			    ->getCollection()
			    ->addCategoryFilter($category)
			    // ->addAttributeToSelect("CategoryIds")
			    ->addAttributeToFilter('status', array('eq' => 1)) //only enabled 
			    ->addAttributeToFilter('visibility', 4) //only visible 
			    ->setPageSize($product_per_page)
				->setCurPage($p)
			    ->load();
	// foreach ($four_product_collection as $product) {
	// 	echo $product->getSku(),"<br>";
	// }
	$add_cat_to_products=new add_cat_to_products($four_product_collection,$cat_id_of_popular_category);

	echo "\n============Finished Adding 4 Dresses to Popular Category===========\n";
}
// write_report("\"admin,cn,en\",\"$sku\",\"-$small_image\"");


// go_over_all($db_handle);

//==========================do one category only==========================
/*
Update the category products. For new skus in datafeedr result, create products and all product_manager. 
For skus not in datafeedr result anymore, insert them in mage_products_action table. In disable_obsolete_products_and_reindex() function, the rows assiociated with this event_id will be processed (magento products are to be disabled, and  reindex will be carried out). If you want to delete disabled products, please go to admin: .

On error, return false.
If debug is not turned off, return null.
*/
function do_one_category_only($cat_id,$db_handle,$report_name){
	//-------------truncate to be disabled table---------
	echo "do one category only? ";
	var_dump(isset($_GET['category_id']));
	if (isset($_GET['category_id'])){
		$query="truncate mage_products_action;";
		$result=$db_handle->runQuery($query);
		if ($result===false){
			echo "\nTruncate mage_products_action failed\n";
		}else{
			echo "\nSuccess: Truncate mage_products_action. \n";
		}
	}
	//---------------------------------------------------
	var_dump($cat_id);
	// write_report("------------Start Running category ".$cat_id."--------------",$report_name);
	require __DIR__.'/config.php';

	// var_dump($weight);
	require_once __DIR__.'/datafeedr_updater.php';
	//----------------sanitize the cat_id, make sure it is allowed-----------------
	$history_table="mage_datafeedr_auto_update_history";
	/*$query="select * from {$history_table} where cat_id=".$cat_id;
	$result=$db_handle->runQuery($query);
	// var_dump($result);
	if (is_null($result)){
		echo 'category id not in database. exiting.';
		return false;
	}*/
	//------------------------instantiate datafeedr object-------------------------
	$datafeedr_updater=new datafeedr_updater($cat_id);
	if ($datafeedr_updater===false){
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
	$result=$datafeedr_updater->run();//will finish 2 tables if configurable category, finish 1 table if simple category. Also will generate csv and call magmi to import.
	//==========================clean up==========================
	echo "\n======================Finished importing for cat_id: ",$cat_id,". Recording obsolete skus======================\n";
	//--------------record skus that should be deleted later on in mage_products_action table---------------
	$curr_skus=$datafeedr_updater->getCurrProductSkus();//skus from all visible products in category
	echo "\nNumber of Current visible and enabled skus: ",count($curr_skus),"\n";
	var_dump($curr_skus);
	$action_table="mage_products_action";
	
	$msg="inserting skus (to be deleted) to action_table for this event";
	echo "\n$msg\n";
	$count_inserted=0;
	// $skus_to_disable=array();
	//------set table to check based on whether the curr category is simple or configurable category------
	require __DIR__.'/config.php';
	
	$table_to_check="";
	if (in_array($cat_id, $simple_products_categories)){
		$table_to_check=$mysql_table_step_1_datafeedr_results;	
	}else{
		$table_to_check=$mysql_table_step_2_scrapped_results;	
	}
	// var_dump($simple_products_categories);
	// var_dump(in_array($cat_id, $simple_products_categories));
	// var_dump($mysql_table_step_2_scrapped_results);
	echo ("checking current visible enabled skus in cat_id ($cat_id) against this table:　$table_to_check\n");
	//-----record obsolete sku by finding current enabled visible sku but not in step1 or step2 table-----
	foreach ($curr_skus as $curr_sku) {
		//if $curr_sku does not exist in step 2 table sku column, then obsolete:
		$query="select sku from {$table_to_check} where sku='{$curr_sku}'";	;
		
		// var_dump($query);
		$result=$db_handle->runQuery($query);
		// var_dump($result);
		if (!is_null($result)){//still exist in datafeedr
			// echo 'continue';
			continue;
		}
		echo ("obselete:$curr_sku\n");
		// echo 'truncating mage_products_to_be_deleted;';
		$query="insert into {$action_table} (sku,category_id,disabled,deleted,auto_update_event_id,action) values ('{$curr_sku}','{$cat_id}',0,0,'{$this_event_id}','TO_BE_DELETED')";
		// var_dump($query);
		$result=$db_handle->runQuery($query);
		if ($result===true){
			$count_inserted++;
			// array_push($skus_to_disable, $curr_sku);
		}else{
			echo "\nFailed to run insert query\n";
		}
	}
	echo "\nRecorded [",$count_inserted, "] obsolete visible products' skus in database(mage_products_action table). \n";
	write_report("<td>".$count_inserted."</td>",$report_name);
	//-------------step4.2: If no skus should be disabled, which means the datafeedr did not change product results for the category query. Log it.-----------
	if ($count_inserted===0){
		$msg="Step 6: No product to be disabled in this category (datafeedr returned same product set, but all products were updated in case there are changes in attributes).";
		// write_report($msg,$file_name);
		echo "\n$msg\n";
		write_report("<td>No obsolete visible products.</td>",$report_name);
	}
	
	//----------------------------finish and return-------------------------------------------------------
	echo "\n$","this_event_id is $this_event_id\n";
	echo "\n---------------Finished do_one_category_only() function-----------------\n";
	return $this_event_id;
}


/*
new_sku: abc-M
curr_sku: abc
but they are the same. I just stripped the part that denotes the size ('-M') in 'sku' and 'product name' in exportWithScrapper_conf_api.php, if both the sku (columnbia-shirt-XL) and product name(e.g. "xxx shirt, XL" ) are trailed with the same.

e.g. curr_skus: HUR00PJ-DARHEAOBS
				HUR00PJ-DARHEAOBS-XXL
				HUR00PJ-DARHEAOBS-XL
				HUR00PJ-DARHEAOBS-L
new_skus has:   HUR00PJ-DARHEAOBS-L
*/

// function is_in_new_sku_array($curr_sku,$new_skus){
// 	foreach ($new_skus as $new_sku) {
// 		if (isTheSameSku($new_sku,$curr_sku)){
// 			return true;
// 		}
// 	}
// 	return false;
// }

// function is_in_current_sku_array($new_sku, $curr_skus){
// 	foreach ($curr_skus as $curr_sku) {
// 		if (isTheSameSku($new_sku,$curr_sku)){
// 			return true;
// 		}
// 	}
// 	return false;
// }

// function isTheSameSku($new_sku,$curr_sku){
// 	$pos=strpos($new_sku,$curr_sku.'-');
// 	if ($pos===false){
// 		return false;
// 	}elseif($pos==0){
// 		return true;
// 	}else{
// 		return false;
// 	}
// }
//==========================regular, go through all datafeedr categories=============
function go_over_all($db_handle,$file_name){
	//================write report===============================================
	write_report("<!DOCTYPE html>
						<html>
						<head>
							<style>
								table {
								    border-collapse: collapse;
								}
								table, th, td {
								    border: 1px solid black;
								}
							</style>
						</head>
						<body>
						<table>
						<th>Date</th><th>Time</th><th>Cat Id</th><th>Cat Name</th><th># of Visible Datafeedr Products (before Update) </th><th>In Step 1 Table</th><th>In Step 2 Table</th><th>status</th><th>Num obsolete visible recorded in Database</th>",$file_name);
	
	//===========================================================================
	echo "\n========================Step 1: GO OVER ALL=====================\n";
	//-------------truncate to be disabled table---------
	$query="truncate mage_products_action;";
	$result=$db_handle->runQuery($query);
	if ($result===false){
		echo "\nTruncate mage_products_action failed\n";
	}else{
		echo "\nSuccess: Truncate mage_products_action. \n";
	}
	//-------------------------------------------------------
	require __DIR__.'/config.php';	
	$no_error=true;
	foreach ($filter_strings as $cat_id => $strings) {
		if ($cat_id<=$ommit_cat_threshold){
			continue;
		}
		$cat_id=(int)$cat_id;
		$this_event_id=do_one_category_only($cat_id,$db_handle,$file_name);
		// var_dump($this_event_id);
		/*if (is_null($result) || $result==false){
			echo '<br>breaking out of loop<br>';
			break;
		}*/
		if (!is_numeric($this_event_id)){
			echo "\nbreaking out of loop\n";
			echo "\nEvent id is not numeric, exit. \n";
			$no_error=false;
			break;
		}
		echo "\n------------Delimiter---Finished category ",$cat_id,"--------------\nLet me sleep [10 seconds] before doing next category...\n";	
		sleep(10);
	}


	return $no_error;
}
/*
This function is called after go_over_all() function. This function is going to disable magento products with sku recorded in mage_products_action as TO_BE_DELETED. Then, this function reindex all magmi options( except the last one: tags), which will make new products show up in front-end, and make the disabled products hidden.  
*/
function disable_obsolete_products_and_reindex($db_handle,$file_name){
	echo ("\n========================(2): DISABLE and REINDEX=====================\n");
	require __DIR__.'/config.php';
	require_once __DIR__.'/datafeedr_updater.php';
	//--------------set up a dummy category $datafeedr_updater---------------------------------
	if ($debug===false){
		$cat_id=849;//vitamins & supplements
		$datafeedr_updater=new datafeedr_updater($cat_id);
		if ($datafeedr_updater===false){
			echo "\nError in datafeedr_updater instantiation...\n";
			break;
		}
		//-----------disable the products in mage_products_action table------------------
		// $query="select * from mage_products_action where auto_update_event_id='{$this_event_id}'";
		$query="select * from mage_products_action where action='TO_BE_DELETED' and disabled=0 and deleted=0 ";
		$result=$db_handle->runQuery($query);
		if (!is_null($result)){
			$skus_to_disable=array();
			foreach ($result as $row_id => $row) {
				$sku=$row['sku'];
				// $child_skus=get_child_skus($sku);
				array_push($skus_to_disable, $sku);
			}
			//build $final_ids_to_disable array:
			if (!empty($skus_to_disable)){
				$ids_to_disable=$datafeedr_updater->getIdsFromSkus($skus_to_disable);
				// echo "\nids_to_disable: ",$ids_to_disable;
				$final_ids_to_disable=array();
				//also add child ids for each product if the product is configurable:
				foreach ($ids_to_disable as $id) {
					array_push($final_ids_to_disable,$id);
					//add child ids
					$child_ids=get_child_ids($id);
					// var_dump($child_ids);
					if (!empty($child_ids) && $child_ids!==false){
						foreach ($child_ids as $child_id) {
							array_push($final_ids_to_disable,$child_id);
						}
					}
				}
				$msg="\n --------Using mage, now disabling ". count($ids_to_disable)." visible products and ".(count($final_ids_to_disable)-count($ids_to_disable)). " children(if any) of these products---------\n";
				echo $msg;
				// write_report(count($ids_to_disable)." visible items with ".count($final_ids_to_disable)-count($ids_to_disable)." invisible children items",$file_name);
				echo "\n[",implode(",", $final_ids_to_disable),"]\n";

				if (!empty($final_ids_to_disable)){//reindex might not be prompt! so need to use another
					echo "\nSwitching the magento products' status to disabled(2)...\n";
					Mage::getSingleton('catalog/product_action')->updateAttributes($final_ids_to_disable, array('status' => 2), 0);
					// var_dump($ids_to_disable);
					// var_dump($final_ids_to_disable);
					echo "\nDone\n";
				}
			}
		}
		//---------------set all rows' disabled column to 1----------------
		$query="update mage_products_action SET disabled=1 where action='TO_BE_DELETED' and disabled=0 and deleted=0 ";
		$result=$db_handle->runQuery($query);
		if ($result==true){
			// echo "Updated mage_products_action table - \'disabled\' column to 1 <br>";
			$msg="\nUpdate disabled column from 0 to 1 in mage_products_action table\n";
			echo $msg;
		}else{
			$msg="\nError: cannot update disabled column from 0 to 1 in mage_products_action table\n"; 
			echo $msg;
		}
		//------------------------reindex all except tags---------------------------------------
		echo "\n-------------calling magmi to reindex all-----------------\n";
		$datafeedr_updater->run_magmi_reindex_all();
	}
}

/**
* This function finds all disabled datafeedr=1 products, and generate csv, 
* and run csv in magmi: pls_do_not_change_me_delete profile to delete them.
*/
function delete_all_disabled_datafeedr_product(){
	require_once __DIR__.'/lib/delete_all_disabled_datafeedr_products.php';
	$deleter=new delete_all_disabled_datafeedr_products;
	
}
/**
* 
*/

//function get_child_skus($sku){
	// require_once '../../app/Mage.php';
	// Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

	// $id=Mage::getModel('catalog/product')->getIdBySku($sku);
	// if (is_null($id) || $id==false) {return false;}
	// $product = Mage::getModel('catalog/product')->load($id); 
	// $childProducts = Mage::getModel('catalog/product_type_configurable')
	//                     ->getUsedProducts(null,$product);
	// $child_skus=array();
	// foreach($childProducts as $child) {
	//     print_r($child->getName());  // You can use any of the magic get functions on this object to get the value
	//     print_r($child->getData('entity_id'));
	//     array_push($child_skus,$child->getData('entity_id'));
	// }
//}
/*
Returns all child products ids of the magento product. 
input: magento product id
output: array containing all child product ids. if this product does not have child product. return false;
*/
function get_child_ids($id){
	require_once __DIR__.'/../../app/Mage.php';
	Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
	$childProducts_ids = Mage::getModel('catalog/product_type_configurable')
                    ->getChildrenIds($id);
	if (!empty($childProducts_ids) && $childProducts_ids!==false){
    	return $childProducts_ids[0];
	}else{
		return false;
	}
}

