<?php

//fill the fifty_off attribute in 
require_once __DIR__.'/../../../app/Mage.php';
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

class ff{
	public $output_csv_path;
	function __construct() {
		$this->output_csv_path="disable_product.csv";
		$this->run();
	}
	function run(){
		// $this->write_csv("store,sku,magmi:delete");
		$all_category_ids=array();
		//---------------------------------------------
		$categories = Mage::getModel('catalog/category')->getCollection()
		    ->addAttributeToSelect('*')//or you can just add some attributes
		    ->addAttributeToFilter('level', 2);//2 is actually the first level
		$categories->addAttributeToFilter('is_active', 1);//if you want only active categories
		$i=0;
		foreach ($categories as $category){
			// echo "<span class='menu-level-2'>".$category->getName()."</span>";
			$cat_id=$category->getId();
			echo "\n----------running for cat_id: ",$cat_id,"(",$category->getName(),")","-----------\n";
			// $name=getCategoryName($cat_id);
			// echo "<td>".$name."</td>";
			// display_status_for_products($cat_id);
			$this->run_cat((int)$cat_id);
			if ($i==2)break;
			$i++;
		}
	}
	function write_csv($object)
	{  
	 	error_log($object."\n", 3, $this->output_csv_path);
	    return true;
	}
	/*
	find all products matching a condition in a cat_id
	*/
	function run_cat($cat_id){
		// require_once '../../app/Mage.php';
		// Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
		$product_model = Mage::getModel('catalog/product');
		//----------------------------------------
		$categoryId = $cat_id; // a category id that you can get from admin
		$category = Mage::getModel('catalog/category')->load($categoryId);

		$collection = Mage::getModel('catalog/product')
		    ->getCollection()
		    ->addCategoryFilter($category)
		    ->addAttributeToSelect('weight')
		    ->addAttributeToFilter('weight',1)
		    ->addAttributeToFilter('is_datafeedr_product', 1) 
		    // ->addAttributeToFilter('status', array('eq' => 2)) //only disabled
		    // ->addAttributeToFilter('status', array('eq' => 1)) //only enabled 
		    // ->addAttributeToFilter('visibility', 4) //only visible 
		    ->load();
		  // var_dump($collection->getSelectSql());
		echo "\nNum of datafeedr products in this with weight=1 (",$cat_id,"): ", $collection->count(),"\n"; // 0 products

		//-----------------------------------------
		foreach ($collection as $product) {
			echo $product->getSku(),"<br>";
			$product_id=$product_model->getIdBySku($product->sku);
			// echo $product_id,"<br>";
			$weight=$product->getWeight();
			echo $weight,"<br>";
			$categories=$product->getCategoryIds();
			var_dump( $categories);
			if (count($categories)===1 && $categories[0]=="443"){
				echo 'Womens Category Only<br>';
				$this->write_csv("admin,{$product->getSku()},2");
			}
			// if (count($categories)===1 && $categories[0]=="554"){
			// 	echo 'Men Category Only<br>';
			// }
		}
		//-------------------------------------------
		// Mage::getSingleton('catalog/product_action')->updateAttributes($ids_qualified, array('status' => 2), 0);
	}
}





/*

//==========================Other helpful functions: ===========================

function display_status_for_products($cat_id){
	require_once '../../app/Mage.php';
	Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
	//-----------------------------------------
	$categoryId = $cat_id; // a category id that you can get from admin
	$category = Mage::getModel('catalog/category')->load($categoryId);
	$collection = Mage::getModel('catalog/product')
	    ->getCollection()
	    ->addCategoryFilter($category)
	    ->addAttributeToSelect('*')
	//  ->addAttributeToFilter('status', array('gt' => 0)) //filter commented, show all products
	    ->load();

	echo "All products in category: ".$collection->count()."<br>"; // 44 products


	$category = Mage::getModel('catalog/category')->load($categoryId);

	$collection = Mage::getModel('catalog/product')
	    ->getCollection()
	    ->addCategoryFilter($category)
	    ->addAttributeToSelect('*')
	    ->addAttributeToFilter('status', array('eq' => 1)) //show only enabled
	    ->load();

	echo "Enabled:". $collection->count()."<br>"; // 44 products

	$collection = Mage::getModel('catalog/product')
	    ->getCollection()
	    ->addCategoryFilter($category)
	    ->addAttributeToSelect('*')
	    ->addAttributeToFilter('status', array('eq' => 2)) //only disabled 
	    ->load();

	echo "Disabled: ".$collection->count()."<br>"; // 0 products
	//-----------------------------------------

}
*/
/*
function getCategoryName($cat_id){
	require_once '../../app/Mage.php';
	Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
	

	$full_cat_name="";
	$category = new Mage_Catalog_Model_Category();
	$category->load($cat_id);//414 
	// echo ("cat id is".$cat_id);
	if(!$category->getId()) {
		return false;
	}else{
		$full_cat_name.=$category->getName();
	}
	//add parent name if it is level 3 category:
	if ($category->getLevel()==3){
		$category->load($category->parent_id);//414 
		// echo ("cat id is".$cat_id);
		if(!$category->getId()) {
			return false;
		}else{
			$full_cat_name=$category->getName().'/'.$full_cat_name;
		}
	}
	return $full_cat_name;
}
*/