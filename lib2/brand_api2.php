<?php

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
// header('Content-type: application/json');
echo '<html>';
echo '<head>
		<meta charset=utf-8>
		</head><body>';

class brand_api{
	private $debug;
	public $all_brands;
	function __construct(){
		$this->debug=true;
		$this->all_brands=$this->_get_all_brands();
	}
	public function loadById($brand_id){
		if (empty($brand_id)) {
			$result=array("status"=>false, "error"=>"param not valid");
			return $result;
		}
		//--------verify this brand_id exists and find brand label------------------		
		$brand_label=$this->getBrandLabelFromId($brand_id);
		// var_dump($brand_label);
		if ($brand_label===null){
			$result=array("status"=>false, "error"=>"brand_id is not valid. no such brand. ");
			return $result;	
		}
		//-------build the detail report for this brand-----------------------------
		// require 'database/dbcontroller.php';
		$count=$this->getBrandProductCount($brand_id);
		$image_name=strtolower($brand_label);
		$image_name=str_replace(" ", "_", $image_name);
		// $image_path="https://www.1661usa.com/media/wysiwyg/milano/brand-logos/{$image_name}.png";
		// $image_path="../../media/wysiwyg/milano/brand-logos/{$image_name}.png";
		$image_path="/usr/share/nginx/www/1661hk.com/media/wysiwyg/milano/brand-logos/{$image_name}.png";
		$exist_or_not=file_exists($image_path);
		$image_for_worker="{$image_name}.png";
		$result=array("status"=>true,"total_products"=>$count,"image_path"=>$image_for_worker, "image_exists"=>$exist_or_not);
		return $result;

	}
	//find the brand label according to given brand_id.
	//if not found return null
	function getBrandLabelFromId($brand_id){
		$brand_label=null;
		foreach($this->all_brands as $label=>$id){
			if ($id==$brand_id){
				$brand_label=$label;
			}
		}
		// echo json_encode($result, JSON_FORCE_OBJECT);
		return $brand_label;
	}
	public function loadByBrandLabel($label){
		// $brand_id=$this->_getOptionIDByCode("brands"，$label);
		if (empty($label)) {
			$result=array("status"=>false, "error"=>"param not valid");
			return $result;
		}
		//---------------------------------
		$all_brands=$this->_get_all_brands();

		$brand_id=$all_brands[$brand_label];

		$result=$this->loadById($brand_id);
	}

	/*
	Returns all brands in magento as an array. key is brand label, value is brand id.
	*/
	public function  _get_all_brands(){
		require_once '../../app/Mage.php';
		Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
		//-----------------------------------------------------------------
		$all_brands=array();

		$attrModel   = Mage::getModel('eav/entity_attribute');

		$attrID      = $attrModel->getIdByCode('catalog_product', "brands");
		$attribute   = $attrModel->load($attrID);

		$options     = Mage::getModel('eav/entity_attribute_source_table')
			->setAttribute($attribute)
			->getAllOptions(false);
		
		foreach ($options as $option) {
			// if ($option['label'] == $optionLabel) {
			// 	return $option['value'];
			// }
			$all_brands[$option['label']]=$option['value'];
		}

		return $all_brands;
	}


	/*
		input: 'size','S'
		output: 4

		input: 'size', 'JJ'
		output: false
	*/
	function _getOptionIDByCode($attrCode, $optionLabel) 
	{
		require_once '../../app/Mage.php';
		Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
		//-----------------------------------------------------------------
		$attrModel   = Mage::getModel('eav/entity_attribute');

		$attrID      = $attrModel->getIdByCode('catalog_product', $attrCode);
		$attribute   = $attrModel->load($attrID);

		$options     = Mage::getModel('eav/entity_attribute_source_table')
			->setAttribute($attribute)
			->getAllOptions(false);
		
		foreach ($options as $option) {
			if ($option['label'] == $optionLabel) {
				return $option['value'];
			}
		}

		return false;
	}

	function _getOptionLabelById($attrCode, $optionId) 
	{
		require_once '../../app/Mage.php';
		Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
		//-----------------------------------------------------------------
		$attrModel   = Mage::getModel('eav/entity_attribute');

		$attrID      = $attrModel->getIdByCode('catalog_product', $attrCode);
		$attribute   = $attrModel->load($attrID);
		$options     = Mage::getModel('eav/entity_attribute_source_table')
			->setAttribute($attribute)
			->getAllOptions(false);
		
		foreach ($options as $option) {
			if ($option['value'] == $optionId) {
				return $option['label'];
			}
		}

		return false;
	}


	/*
		output: total number of products under this brand
	*/
	function getBrandProductCount($brand_id){
			require_once '../../app/Mage.php';
			Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
			//-----------------------------------------------------------------
			// $cat_id=420;
			// $category = new Mage_Catalog_Model_Category();
			// $category->load($cat_id);//413 

			$productCollection = Mage::getResourceModel('reports/product_collection')
	         // ->addAttributeToSelect('sku')
	         ->addAttributeToSelect('brands')
	         // ->addAttributeToSelect('name')
			 ->addAttributeToFilter('brands',$brand_id);
			 // ->addCategoryFilter($category);

			// echo 'There are '.count($products)." product(s) under this brand.";
			/* foreach ($productCollection as $product) {
			 	 echo $product->getSku().'    '. $product->getName().'   '.$product->getBrands().'<br>';
				 // write_log("admin,{$product->getSku()},\"249,338,644,598\"","changCat2.csv");
			 }
			 */
			return count($productCollection);

	}

}


// $brand_label="DC";
// $brand_label="ASCIS";


// $result=array("status"=>"success",
// 				"brand_label"=>$brand_label,
// 				"brand_id"=>$brand_id,
// 				"total_num_products"=>$count
// 	);
// echo json_encode($result, JSON_FORCE_OBJECT);



function write_log($object,$path)
{  
	error_log($object."\n", 3, $path);
    return true;
}


$vip_brands=array();

$brand_api=new brand_api();
// var_dump($brand_api->all_brands);
$i=0;
$batch_amount=30;
foreach($brand_api->all_brands as $brand_label => $brand_id){
	// var_dump($brand_id);
	$result=$brand_api->loadById($brand_id);
	if (is_null($result)){
		echo 'Error. cant find brand. exiting.';
		exit;
	}
	// var_dump($result['image_path']);
	
	// if ($result['image_exists']==true) {
	// 	var_dump($result); 
	// 	break;
	// }
	if ($result['total_products']>100 && $result['image_exists']===false){
		array_push($vip_brands,$result);
	}
	$i++;
	if ($i>$batch_amount) break;
}


// var_dump($vip_brands);
echo '<table>';
echo  "<tr><th>Brand Name</th>  <th>  Total number of products of this brand  </th><th> image name </th><th>baidu link</th></tr>";
foreach ($vip_brands as $vip_brand) {
	$brand_name=str_replace("_", " ", $vip_brand['image_path']);
	$brand_name=substr($brand_name, 0, strlen($brand_name)-4);
	$baidu_url="http://image.baidu.com/search/index?tn=baiduimage&cl=2&sf=1&nc=1&z=&se=1&istype=2&ie=utf-8&word={$brand_name}+logo+png";
	echo "<tr><td>".$brand_name. '</td><td>'. $vip_brand['total_products']."</td><td>".$vip_brand['image_path']."</td><td><a href=\"{$baidu_url}\">搜索此品牌logo</a></td></tr>";
}
echo '</table>';

























/*
input: "At Paddles"
output: "AT Paddles" which is in databse. If not found, return false.

ignore cases. return the version in database.  
*/
function getFromDatabase($brand_name,$db_handle){
	$result=$db_handle->runQuery("select * from alice_unique_brands where `brand`=\"".$brand_name."\"");
	if (!is_null($result) && !empty($result)){
		return $result[0]['brand'];
	}
}

/*
	Input: adidas
	Output: Adidas (Capitalize first Character)
*/
function format($option){
	return titleCase($option);
}

//formats brand name - Capitalize first letter of each word, excludes capitalization of words listed in the array
function titleCase($string, $delimiters = array(" ", "-"), $exceptions = array("and", "to", "of", "by")){
    /*
     * Exceptions in lower case are words you don't want converted
     * Exceptions all in upper case are any words you don't want converted to title case
     *   but should be converted to upper case, e.g.:
     *   king henry viii or king henry Viii should be King Henry VIII
     */
    $string = mb_convert_case($string, MB_CASE_TITLE, "UTF-8");
    foreach ($delimiters as $dlnr => $delimiter) {
        $words = explode($delimiter, $string);
        $newwords = array();
        foreach ($words as $wordnr => $word) {
            if (in_array(mb_strtoupper($word, "UTF-8"), $exceptions)) {
                // check exceptions list for any words that should be in upper case
                $word = mb_strtoupper($word, "UTF-8");
            } elseif (in_array(mb_strtolower($word, "UTF-8"), $exceptions)) {
                // check exceptions list for any words that should be in upper case
                $word = mb_strtolower($word, "UTF-8");
            } elseif (!in_array($word, $exceptions)) {
                // convert to uppercase (non-utf8 only)
                $word = ucfirst($word);
            }
            array_push($newwords, $word);
        }
        $string = join($delimiter, $newwords);
   }//foreach
   return $string;
}