<?php

class change_category_product_position_api{
	public $magento_category_id='';
	public $magento_category_name='';
	private $category_object='';
	function __construct($cat_id) {
		if (!is_numeric($cat_id)){echo 'error. should be numeric';return false;}

		require_once __DIR__.'/../../../app/Mage.php';
		Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
		
		$category = new Mage_Catalog_Model_Category();	
		$category->load($cat_id);//414 
		if(!$category->getId()) {
			return false;
		}else{
			$this->magento_category_id=$cat_id;
			$this->magento_category_name=$category->getName();
			$this->category_object=$category;
			return true;
		}
	}
	/*Change all products (under this cateogry) postion to $position*/
	public function run($new_position){
		if (!is_numeric($new_position)){
			echo '$new_position must be numeric';
			return false;
		}
		require_once __DIR__.'/../../../app/Mage.php';
		Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
			// var_dump($this->category_object);
		
		$productCollection = Mage::getResourceModel('reports/product_collection')
			// ->addAttributeToSelect('*')
			->addAttributeToSelect('is_datafeedr_product')
			->addAttributeToSelect('sku')
			->addAttributeToFilter('is_datafeedr_product','Yes')
			->addCategoryFilter($this->category_object);

		$datafeedr_product_ids=array();
		var_dump(count($productCollection));
		foreach ($productCollection as $product) {
			array_push($datafeedr_product_ids,$product->getData('entity_id'));
			// var_dump($product->getSku().'  '.$product->getName());
		}
var_dump($datafeedr_product_ids);
		if (!empty($datafeedr_product_ids)){
			echo 'changing the position of '.count($datafeedr_product_ids).' datafeedr products:<br>';
			// Mage::getSingleton('catalog/product_action')->updateAttributes($datafeedr_skus, array('status' => 0), 0);
			$category = Mage::getModel('catalog/category')->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID)->load($this->magento_category_id);
			$products = $category->getProductsPosition();
			foreach ($products as $id=>$value){
				// echo '<pre>';
				// var_dump($id);
				// var_dump($value);
				// echo '</pre>';
				// break;
				if (in_array($id, $datafeedr_product_ids)){
			    	$products[$id] = $new_position;
				}
			}
			// $category->setPostedProducts($products);
			// $category->save();
		}
		return true;
	}
}

// $change_category_product_position_api=new change_category_product_position_api(846);
// $new_position=100;
// $change_category_product_position_api->run($new_position);