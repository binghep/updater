<?php
/**
* This file change all datafeedr products' position to 300
* Output includes "Success" on success.
* Output includes "Fail" on error.
*/
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");

require __DIR__.'/config.php';
require_once __DIR__.'/database/dbcontroller_with_changeDB.php'; 



foreach ($filter_strings as $key => $value) {
	$newlinehtml = "<br>";
	$newlinecommand = "\n";
	echo $newlinecommand . "--------category id is--".$key."--------------" . $newlinecommand;
	//-----------------------------------------------------------
	$cat_id=(int)$key;
	// $name=getCategoryName($cat_id);
	// //------------------------
	// echo "<td>".$name."</td>";
	// //-----------------------------------------------------------
	// display_status_for_products($cat_id);
	update_position_datafeedr_products($cat_id);
	//break;
}
/*
Any product with position 0 replace with 300
*/
function update_position_datafeedr_products($cat_id){
	require_once __DIR__.'/../../app/Mage.php';
	Mage::app();	
	
	$newlinehtml = "<br>";
	$newlinecommand = "\n";
	
	$categoryId = $cat_id; // a category id that you can get from admin
	$category = Mage::getModel('catalog/category')->load($categoryId);
	
	$collection = Mage::getModel('catalog/product')
	    ->getCollection()
	    ->addCategoryFilter($category)
	    ->addAttributeToSelect('*')
	    ->addAttributeToFilter('is_datafeedr_product', 1) //only disabled 
	    ->load();

	//-----------------------------------------
	
	$api = Mage::getSingleton('catalog/category_api');
	$position = 300;
	$i = 0;
	$product_ids = array();
	foreach ($collection as $product) {
		// echo $product->sku."<br>";
		// array_push($skus_to_delete, $product->sku);
		$product_ids[] = $product->getId();
		$i++;
	}
	$string_product_ids = implode(",", $product_ids);
	$db_handle=new DBController();	
	$db_handle->changeDB("magento_ipzmall");
	$query = "Update mgcatalog_category_product set position = " . $position . 
	" where category_id = " . $categoryId . " and product_id in (" . $string_product_ids . ")";
	//$query = "select 1 from mgcatalog_category_product";
	$result=$db_handle->runQuery($query);
	if($result){
		echo "Success: Updated " . $i . " products from category " . $categoryId . $newlinecommand;
	}else{
		echo "Failed: Query Failed" .  $newlinecommand;
	}
}