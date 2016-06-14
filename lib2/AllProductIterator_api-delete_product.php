<?php

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
// header('Content-type: application/json');

require_once __DIR__.'/../../../app/Mage.php';
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
// require_once 'database/dbcontroller.php';


class AllProductIterator{
	public function __construct(){
    }

    public function getAllSku(){
		$productCollection = Mage::getResourceModel('reports/product_collection')
			->addAttributeToSelect('sku');

		return $productCollection;
    }
	


	function write_log($object,$path)
	{  
		error_log($object."\n", 3, $path);
	    return true;
	}

}


$iterator=new AllProductIterator();
$productCollection=$iterator->getAllSku();

$output_csv_path="remove_all_products.csv";
foreach ($productCollection as $product) {
	// var_dump($product->getSku());
	$string="admin,\"{$product->getSku()}\",1";
	$iterator->write_log($string,$output_csv_path);
}

