<?php
// return;

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
// header('Content-type: application/json');

// require_once '../../app/Mage.php';
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
			// ->addAttributeToSelect('affliate_product_url')
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


$cat_id=845;
$iterator=new CategoryProductIterator($cat_id);
$productCollection=$iterator->getAllSku();

// $output_csv_path=".csv";
foreach ($productCollection as $product) {
	// var_dump($product->getSku());
	// $string="admin,\"{$product->getSku()}\",1";
	// $iterator->write_log($string,$output_csv_path);


	$affliate_product_url_raw=$product->getData('affliate_product_url');
	$affliate_product_url=getUrl($affliate_product_url_raw);

	echo '<pre>';
	// $bestbuy_keyword="www.bestbuy.com";
	// if (strpos($affliate_product_url,$bestbuy_keyword)!==false){
		var_dump(urldecode($affliate_product_url));
		// $data=scrapeBestBuyPage($affliate_product_url);
	// } 
	echo '</pre>';
}

// var_dump($iterator->cat_id);




function getUrl($product_url_raw)
{
	$product_url=null;
    //If url= is found in stirng, then product url exists in the Raw Url
    if (strpos($product_url_raw, 'url=') !== false) {
		$pos1 = strpos($product_url_raw, "http%");
		// $pos2 = strpos($product_url_raw, "%26") - $pos1;
		$product_url = substr($product_url_raw, $pos1 );
	}else{
		$product_url = $product_url_raw;
	}
	return $product_url;
}


function scrapeBestBuyPage($affliate_product_url){
	// require 'getBestBuySalePrice_api.php';

}

//http%3A%2F%2Fwww.bestbuy.com%2Fsite%2Fjawbone-up3-activity-tracker-heart-rate-sand-twist-gold%2F4328501.p%3Fid%3D1219730400793%26amp%3BskuId%3D4328501