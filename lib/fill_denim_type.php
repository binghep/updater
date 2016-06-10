<?php

//fill the denim_type attribute in 

class fill_denim_type{
	public $denim_type;
	function __construct($denim_type) {
		$this->denim_type=$denim_type;
		// var_dump($denim_type);
		$this->run_cat(537);
		$this->run_cat(782);
	}
	/*
	Go through all products(with is_datafeedr_product=1).
	*/
	function run_cat($cat_id){
		echo "\n------------running category_id ",$cat_id,"------------\n";
		require_once __DIR__.'/../../../app/Mage.php';
		Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
		$product_model = Mage::getModel('catalog/product');
		//----------------------------------------
		$categoryId = $cat_id; // a category id that you can get from admin
		$category = Mage::getModel('catalog/category')->load($categoryId);

		$collection = Mage::getModel('catalog/product')
		    ->getCollection()
		    ->addCategoryFilter($category)
		    ->addAttributeToSelect('name')
		    // ->addAttributeToSelect('status')//----this will break status filter. return 0 results
		    ->addAttributeToFilter('is_datafeedr_product', 1) 
		    // ->addAttributeToFilter('status', array('eq' => 2)) //only disabled
		    ->addAttributeToFilter('status', array('eq' => 1)) //only enabled 
		    ->addAttributeToFilter('visibility', 4) //only visible 
		    ->load();
		  // var_dump($collection->getSelectSql());
		echo "\nNum of Visible and enabled products in this category (",$cat_id,"): ", $collection->count(),"\n"; // 0 products
		//-----------------------------------------
		//--------build an array (key is option id, value is array of product ids)----
		$array=array();
		foreach ($this->denim_type as $keyword=>$option_id) {
			$array[$option_id]=array();
		}
		$i=0;
		foreach ($collection as $product) {
			// echo $product->sku,"<br>";
			// return;
			$product_id=$product_model->getIdBySku($product->sku);
			// echo $product_id,"<br>";
			$name=$product->getName();
			// var_dump($name);
			// echo $product->getStatus(),"<br>";
			// echo $product->getVisibility(),"<br>";

			foreach ($this->denim_type as $keyword => $option_id) {
				if (stripos($name, $keyword)!==false){
					// $array[$option_id][]=$product_id;
					$array[$option_id][$product_id]=$name;
					break;
				}
			}
			// if ($i++==14){break;}
		}
		//-------------------------------------------
		echo '<pre>';
		// var_dump($array);
		// return;
		foreach ($array as $option_id => $associative_array) {
			$ids_for_this_option_id=array_keys($associative_array);
			// var_dump($ids_for_this_option_id);
			echo '-------setting following product ids to option_id: ',$option_id,')------',PHP_EOL;
			echo "Updated ",count($ids_for_this_option_id), " visible enabled products \n";
			Mage::getSingleton('catalog/product_action')->updateAttributes($ids_for_this_option_id, array('denim_type' => $option_id), 0);		
		}
		// Mage::getSingleton('catalog/product_action')->updateAttributes($ids_qualified, array('status' => 2), 0);
	}
}




