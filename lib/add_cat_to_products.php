<?php
require_once __DIR__.'/../../../app/Mage.php';
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

class add_cat_to_products{
	function __construct($products,$cat_id_to_add) {
		foreach ($products as $product) {
			// $categories=$product->getCategoryIds();
			// array_push($categories, $cat_id_to_add);
			// $product->setCategoryIds($categories);
			// $product->save();
			$product_id=$product->getId();
			echo $product->getId(),": adding this product id to popular:<br>";
			// var_dump($product->getCategoryIds());
			Mage::getSingleton('catalog/category_api')->assignProduct($cat_id_to_add,$product_id);
			echo "<br>Done<br>";
			// var_dump($product->getCategoryIds());
			// break;
		}
	}
	// function run_all_category(){
	// 	// $this->write_csv("store,sku,magmi:delete");
	// 	$all_category_ids=array();
	// 	//---------------------------------------------
	// 	$categories = Mage::getModel('catalog/category')->getCollection()
	// 	    ->addAttributeToSelect('*')//or you can just add some attributes
	// 	    ->addAttributeToFilter('level', 2);//2 is actually the first level
	// 	$categories->addAttributeToFilter('is_active', 1);//if you want only active categories
	// 	$i=0;
	// 	foreach ($categories as $category){
	// 		// echo "<span class='menu-level-2'>".$category->getName()."</span>";
	// 		$cat_id=$category->getId();
	// 		echo "\n----------running for cat_id: ",$cat_id,"(",$category->getName(),")","-----------\n";
	// 		// $name=getCategoryName($cat_id);
	// 		// echo "<td>".$name."</td>";
	// 		// display_status_for_products($cat_id);
	// 		$this->run_cat((int)$cat_id);
	// 		// if ($i==2)break;
	// 		// $i++;
	// 	}
	// }
	// /*
	// Output all skus of disabled products(with is_datafeedr_product=1).
	// */
	// function run_cat($cat_id){
		
	// 	$product_model = Mage::getModel('catalog/product');
	// 	//----------------------------------------
	// 	$categoryId = $cat_id; // a category id that you can get from admin
	// 	$category = Mage::getModel('catalog/category')->load($categoryId);

	// 	$collection = Mage::getModel('catalog/product')
	// 	    ->getCollection()
	// 	    ->addCategoryFilter($category)
	// 	    ->addAttributeToSelect('price')
	// 	    ->addAttributeToSelect('special_price')
	// 	    // ->addAttributeToSelect('status')//----this will break status filter. return 0 results
	// 	    // ->addAttributeToFilter('is_datafeedr_product', 1) 
	// 	    // ->addAttributeToFilter('status', array('eq' => 2)) //only disabled
	// 	    ->addAttributeToFilter('status', array('eq' => 1)) //only enabled 
	// 	    ->addAttributeToFilter('visibility', 4) //only visible 
	// 	    ->load();
	// 	  // var_dump($collection->getSelectSql());
	// 	echo "\nNum of Visible and enabled products in this category (",$cat_id,"): ", $collection->count(),"\n"; // 0 products
	// 	//-----------------------------------------
	// 	$ids_50_percent_off=array();
	// 	$ids_40_percent_off=array();
	// 	// $ids_30_percent_off=array();
	// 	foreach ($collection as $product) {
	// 		// echo $product->sku,"<br>";
	// 		$product_id=$product_model->getIdBySku($product->sku);
	// 		// echo $product_id,"<br>";
	// 		$price=$product->getPrice();
	// 		$special_price=$product->getSpecialPrice();
	// 		// echo $product->getStatus(),"<br>";
	// 		// echo $product->getVisibility(),"<br>";

	// 		$this_product_discount=1.0-$special_price/$price;
	// 		if ($this_product_discount>0.5){//50 percent OFF
	// 			// echo 'Find one: ',$price,',',$special_price;
	// 			array_push($ids_50_percent_off, $product_id);
	// 		}else if ($this_product_discount>0.4){
	// 			array_push($ids_40_percent_off, $product_id);
	// 		}
	// 	}
	// 	//-------------------------------------------
	// 	echo '-------setting following product ids to clearance_percent_off=50% (option id is ',$this->fifty_off_option_id,')------',PHP_EOL;
	// 	echo "Updated ",count($ids_50_percent_off), " visible enabled products \n";
	// 	Mage::getSingleton('catalog/product_action')->updateAttributes($ids_50_percent_off, array('clearance_percent_off' => $this->fifty_off_option_id), 0);
	// 	//-------------------------------------------
	// 	echo '-------setting following product ids to clearance_percent_off=40% (option id is ',$this->fourty_off_option_id,')------',PHP_EOL;
	// 	// var_dump($ids_40_percent_off);
	// 	echo "Updated ",count($ids_40_percent_off), " visible enabled products \n";
	// 	Mage::getSingleton('catalog/product_action')->updateAttributes($ids_40_percent_off, array('clearance_percent_off' => $this->fourty_off_option_id), 0);
	// 	//-------------------------------------------
	// 	// Mage::getSingleton('catalog/product_action')->updateAttributes($ids_qualified, array('status' => 2), 0);
	// }
}