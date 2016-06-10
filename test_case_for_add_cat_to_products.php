<?php

/**
* Find 4 products in Dresses. 
* Call add_cat_to_products class to add Popular cat id to these four products.
*/
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");

require_once '../../app/Mage.php';
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
require 'lib/add_cat_to_products.php';
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

