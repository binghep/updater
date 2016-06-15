<?php
return;
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
// header('Content-type: application/json');

require_once __DIR__.'/../../../app/Mage.php';
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
// require_once 'database/dbcontroller.php';


class CategoryProductIterator{
	public $cat_id;
	public function __construct($cat_id){
		$this->cat_id=$cat_id;
    }

    public function getAllSku(){
		$category = new Mage_Catalog_Model_Category();
		$category->load($this->cat_id);//413 

		$productCollection = Mage::getResourceModel('reports/product_collection')
			// ->addAttributeToSelect('*')
			->addAttributeToSelect('sku')
			->addCategoryFilter($category);

		return $productCollection;
    }
	// function getBrandProductCount($brand_id){
	// 		$cat_id=420;
	// 		$category = new Mage_Catalog_Model_Category();
	// 		$category->load($cat_id);//413 

	// 		$productCollection = Mage::getResourceModel('reports/product_collection')
	//          // ->addAttributeToSelect('*')
	//          ->addAttributeToSelect('sku')
	//          ->addAttributeToSelect('brands')
	//          // ->addAttributeToSelect('categories')
	//          ->addAttributeToSelect('name')
	// 		 ->addAttributeToFilter('brands',$brand_id)
	// 		 ->addCategoryFilter($category);

	// 		// $products = Mage::getModel('catalog/product')
	// 		        // ->getCollection()
	// 		        // ->;

	// 		// echo "Brand: ".$brand_id."<br>";

	// 		// echo 'There are '.count($products)." product(s) under this brand.";
	// 		 foreach ($productCollection as $product) {
	// 		 	 echo $product->getSku().'    '. $product->getName().'   '.$product->getBrands().'<br>';
	// 			 // write_log("admin,{$product->getSku()},\"249,338,644,598\"","changCat2.csv");
	// 		 }
	// 		return count($productCollection);

	// }


	function write_log($object,$path)
	{  
		error_log($object."\n", 3, $path);
	    return true;
	}

}


$cat_id=462;
$iterator=new CategoryProductIterator($cat_id);
$productCollection=$iterator->getAllSku();

$output_csv_path="remove_dresses.csv";
foreach ($productCollection as $product) {
	// var_dump($product->getSku());
	$string="admin,\"{$product->getSku()}\",1";
	$iterator->write_log($string,$output_csv_path);
}

// var_dump($iterator->cat_id);
