<?php

class category_product_iterator{
	public $cat_id;
	public function __construct($cat_id){
		$this->cat_id=$cat_id;
    }

  //   public function getAllSkus(){
		// $category = new Mage_Catalog_Model_Category();
		// $category->load($this->cat_id);//413 

		// $productCollection = Mage::getResourceModel('reports/product_collection')
		// 	// ->addAttributeToSelect('*')
		// 	->addAttributeToSelect('sku')
		// 	->addCategoryFilter($category);

		// return $productCollection;
  //   }

    //returns all visible products' skus in category. also, has to be datafeedr products
    public function getAllDatafeedrProductSkus(){
    	require_once __DIR__.'/../../../app/Mage.php';
		Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
		
		$category = new Mage_Catalog_Model_Category();
		$category->load($this->cat_id);//413 

		$productCollection = Mage::getResourceModel('reports/product_collection')
			// ->addAttributeToSelect('*')
			->addAttributeToSelect('sku')
			->addAttributeToSelect('is_datafeedr_product')
			->addAttributeToSelect('status')
			->addAttributeToFilter('is_datafeedr_product','1')
			->addAttributeToFilter('status','1')//enabled
			->addAttributeToFilter('visibility',4)
			->addCategoryFilter($category);
		$all_skus=array();
		foreach ($productCollection as $product) {
			array_push($all_skus,$product->getData('sku'));
		}
		return $all_skus;
    }
    public function getAllSkus(){
    	require_once __DIR__.'/../../../app/Mage.php';
		Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
		
		$category = new Mage_Catalog_Model_Category();
		$category->load($this->cat_id);//413 

		$productCollection = Mage::getResourceModel('reports/product_collection')
			// ->addAttributeToSelect('*')
			->addAttributeToSelect('sku')
			->addCategoryFilter($category);
		$all_skus=array();
		foreach ($productCollection as $product) {
			array_push($all_skus,$product->getData('sku'));
		}
		return $all_skus;
    }
    public function getAllIds(){
    	require_once __DIR__.'/../../../app/Mage.php';
		Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
		
		$category = new Mage_Catalog_Model_Category();
		$category->load($this->cat_id);//413 

		$productCollection = Mage::getResourceModel('reports/product_collection')
			// ->addAttributeToSelect('*')
			// ->addAttributeToSelect('sku')
			->addCategoryFilter($category);
		$all_ids=array();
		foreach ($productCollection as $product) {
			array_push($all_ids,$product->getData('entity_id'));
		}
		return $all_ids;
    }
    public function getSkuNameAndSalePrice(){
    	require_once __DIR__.'/../../../app/Mage.php';
		Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
		
		$category = new Mage_Catalog_Model_Category();
		$category->load($this->cat_id);//413 

		$productCollection = Mage::getResourceModel('reports/product_collection')
			// ->addAttributeToSelect('*')
			->addAttributeToSelect('sku')
			->addAttributeToSelect('special_price')
			->addAttributeToSelect('name')
			->addCategoryFilter($category);
		$data=array();
		foreach ($productCollection as $product) {
			array_push($data,array("sku"=>$product->getData('sku'), "name"=>$product->getData('name'), "special_price"=>$product->getSpecialPrice()));
		}
		return $data;
    }
    /*
    public function change_attribute($attribute_name,$attribute_value){
    	require_once '../../../app/Mage.php';
    	// var_dump(Mage_Core_Model_App::ADMIN_STORE_ID);
		Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

    	$all_ids=$this->getAllIds();
    	var_dump($all_ids);
    	//-------------------method 1:------------------- 
    	if (!empty($all_ids)){
    		$attribute_name="is_datafeedr_product";
    		$attribute_value="1";//"Yes" will be converted to "0"
    		echo 'changing the '.$attribute_name.' to '.$attribute_value.':<br>';
			Mage::getSingleton('catalog/product_action')->updateAttributes($all_ids, array($attribute_name => $attribute_value), 0);
			// Mage::getSingleton('catalog/product_action')->updateAttributes($all_ids, array("is_datafeedr_product" => "Yes"), 0);
    		
    	}
    	//-------------------method 2:------------------- 
   //  	foreach ($all_ids as $product_id) {
   //  		$product = Mage::getModel('catalog/product')->load($product_id);
   //  		// var_dump($product);
   //  		// break;
			// $resource = $product->getResource();

			// $attribute_code="is_datafeedr_product";
			// $value="1";
			// $product->setData($attribue_code, $value);
			// $resource->saveAttribute($product, $attribute_code);
   //  	}
    }
    */
}



//---------------------------the following code change all datafeedr leaf categories' product is_datafeedr_product to 1---------------------
// require 'config.php';

// foreach ($filter_strings as $key => $value) {
// 	// var_dump($key);
// 	$category_product_iterator=new category_product_iterator((int)$key);
// 	$category_product_iterator->change_attribute("","");
// 	// break;
// }