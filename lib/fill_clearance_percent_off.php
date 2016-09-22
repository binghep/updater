<?php

//fill the fifty_off attribute in 

class fill_clearance_percent_off{
	public $thirty_off_option_id;
	public $fourty_off_option_id;
	public $fifty_off_option_id;
	//30%=1921
	//40%=1922
	//50%=1923
	function __construct($thirty_off_option_id,$fourty_off_option_id,$fifty_off_option_id) {
		$this->thirty_off_option_id=$thirty_off_option_id;
		$this->fourty_off_option_id=$fourty_off_option_id;
		$this->fifty_off_option_id=$fifty_off_option_id;

		$this->run_all_category();
	}
	function run_all_category(){
		require_once __DIR__.'/../../../app/Mage.php';
		Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
		// $this->write_csv("store,sku,magmi:delete");
		$all_category_ids=array();
		//---------------------------------------------
		$categories = Mage::getModel('catalog/category')->getCollection()
		    ->addAttributeToSelect('*')//or you can just add some attributes
		    ->addAttributeToFilter('level', 3);//2 is actually the first level
		$categories->addAttributeToFilter('is_active', 1);//if you want only active categories
		$i=0;
		foreach ($categories as $category){
			// echo "<span class='menu-level-2'>".$category->getName()."</span>";
			$cat_id=$category->getId();
			// if ($cat_id<850){continue;}
			echo "\n----------running for cat_id: ",$cat_id,"(",$category->getName(),")","-----------\n";
			// $name=getCategoryName($cat_id);
			// echo "<td>".$name."</td>";
			// display_status_for_products($cat_id);
			$this->run_cat((int)$cat_id);
			// if ($i==2)break;
			// $i++;
		}
	}
	/*
	Output all skus of disabled products(with is_datafeedr_product=1).
	*/
	function run_cat($cat_id){
		require_once __DIR__.'/../../../app/Mage.php';
		Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
		$product_model = Mage::getModel('catalog/product');
		//----------------------------------------
		$categoryId = $cat_id; // a category id that you can get from admin
		$category = Mage::getModel('catalog/category')->load($categoryId);

		$collection = Mage::getModel('catalog/product')
		    ->getCollection()
		    ->addCategoryFilter($category)
		    ->addAttributeToSelect('price')
		    ->addAttributeToSelect('special_price')
		    // ->addAttributeToSelect('status')//----this will break status filter. return 0 results
		    // ->addAttributeToFilter('is_datafeedr_product', 1) 
		    // ->addAttributeToFilter('status', array('eq' => 2)) //only disabled
		    ->addAttributeToFilter('status', array('eq' => 1)) //only enabled 
		    ->addAttributeToFilter('visibility', 4) //only visible 
		    ->load();
		  // var_dump($collection->getSelectSql());
		echo "\nNum of Visible and enabled products in this category (",$cat_id,"): ", $collection->count(),"\n"; // 0 products
		//-----------------------------------------
		$ids_50_percent_off=array();
		$ids_40_percent_off=array();
		$ids_30_percent_off=array();
		foreach ($collection as $product) {
			// echo $product->sku,"<br>";
			$product_id=$product_model->getIdBySku($product->sku);
			// echo $product_id,"<br>";
			$price=$product->getPrice();
			$special_price=$product->getSpecialPrice();
			// echo $product->getStatus(),"<br>";
			// echo $product->getVisibility(),"<br>";

			$this_product_discount=1.0-$special_price/$price;
			if ($this_product_discount>0.5){//50 percent OFF
				// echo 'Find one: ',$price,',',$special_price;
				array_push($ids_50_percent_off, $product_id);
			}else if ($this_product_discount>0.4){
				array_push($ids_40_percent_off, $product_id);
			}else if ($this_product_discount>0.3){
				array_push($ids_30_percent_off, $product_id);
			}
		}
		//-------------------------------------------
		echo '-------setting following product ids to clearance_percent_off=50% (option id is ',$this->fifty_off_option_id,')------',PHP_EOL;
		echo "Updated ",count($ids_50_percent_off), " visible enabled products \n";
		Mage::getSingleton('catalog/product_action')->updateAttributes($ids_50_percent_off, array('clearance_percent_off' => $this->fifty_off_option_id), 0);
		//-------------------------------------------
		echo '-------setting following product ids to clearance_percent_off=40% (option id is ',$this->fourty_off_option_id,')------',PHP_EOL;
		// var_dump($ids_40_percent_off);
		echo "Updated ",count($ids_40_percent_off), " visible enabled products \n";
		Mage::getSingleton('catalog/product_action')->updateAttributes($ids_40_percent_off, array('clearance_percent_off' => $this->fourty_off_option_id), 0);
		//-------------------------------------------
		echo '-------setting following product ids to clearance_percent_off=30% (option id is ',$this->thirty_off_option_id,')------',PHP_EOL;
		// var_dump($ids_40_percent_off);
		echo "Updated ",count($ids_30_percent_off), " visible enabled products \n";
		Mage::getSingleton('catalog/product_action')->updateAttributes($ids_30_percent_off, array('clearance_percent_off' => $this->thirty_off_option_id), 0);
		//-------------------------------------------
		//1666 is the option id. option label is "50%"
		//1665 is option id for option label: "40%"
		
		// Mage::getSingleton('catalog/product_action')->updateAttributes($ids_qualified, array('status' => 2), 0);
	}
}





/*

//==========================Other helpful functions: ===========================

function display_status_for_products($cat_id){
	require_once __DIR__. '../../app/Mage.php';
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
	require_once __DIR__. '../../app/Mage.php';
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