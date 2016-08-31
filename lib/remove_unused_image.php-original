<?php


require_once __DIR__.'/../../../app/Mage.php';
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
require_once __DIR__.'/../config.php';


require_once Mage::getBaseDir('lib').'/alice/dbcontroller.php';
$db_handle=new DBController();

//step1: insert all used image path to global_link_distribution.all_used_images table.
// makeTable();
//step2: go over all images under product image dir, if not in database, unlink:
//iterateThroughImageFolder();
//==============finished running===================
function makeTable(){
	global $db_handle;
	$productCollection = Mage::getResourceModel('reports/product_collection')
				    // ->addAttributeToSelect('*')
				    ->addAttributeToSelect('sku')
				    ->addAttributeToSelect('name')
				    ->addAttributeToSelect('image')
				    ->addAttributeToSelect('use_external_images')
				    ->addAttributeToSelect('image_external_url');
				    // ->addAttributeToFilter('visibility',4);//should not use this because on homepage, simple products with no visiblity are shown.
	// $productCollection->addCategoryFilter($category);

	$count=0;
	foreach ($productCollection as $product) {
		echo $product->getName()."<br>";
		//----------get the real image path of product with ext images------
		$_helper = Mage::helper('ExtImages/Image');
		if ($product->getData('use_external_images')) {
			echo '----(1)product w ext image---<br>';
			$imageUrl = $product->getData('image_external_url');
			$actual_image=$_helper->initProd($product->getName(),$imageUrl)->curl_get_image();//e.g. '/f/i/filson-down-cruiser-jacket---mens-dark-tan-l-f6247b75d2e646e82835309125ace9a6.jpg'
			// var_dump($actual_image);
			$query="select * from all_used_images where image_path='$actual_image'";
			$result=$db_handle->runQuery($query);
			if (is_null($result)){
				$result=insertToDatabase($actual_image);
				if (!$result) echo 'insert failed.<br>';
			}
			//$full_path="/usr/share/nginx/www/awesomejiayi.space/media/catalog/product".$actual_image;
			// var_dump(file_exists($full_path));
		}else{
			echo '----(2)normal product---<br>';
			$imagePath=$product->getData('image');
			if (!is_null($imagePath)&&!empty($imagePath) && $imagePath!=="no_selection") {
				$result = insertToDatabase($imagePath);
				if (!$result) echo 'insert failed.<br>';
	//					var_dump($imagePath);
			}
		}
		//------------------------------------------------------------------
		// if ($count>30) break;
		$count++;
	}
	//-----------finished recording all downloaded images--------------
}




//====================helper functions===================================
	function listFolderFiles($dir){
	    $ffs = scandir($dir);
	    echo '<ul>';
	    foreach($ffs as $ff){//ff is either file name or folder name
	    	if ($ff=="cache") {
	    		continue;//skip cache folder
	    	}
	        if($ff != '.' && $ff != '..'){
	            echo '<li>'.$ff;// $ff: headphone.jpg
	            $absolute_ff_path=$dir.'/'.$ff;
	            if(is_dir($absolute_ff_path)) {
	            	//$ff is a folder: 
	            	listFolderFiles($absolute_ff_path);
	            }else{
	            	//$ff is file: 
	            	//check if this file is in database table.
	            	//var_dump($dir);// "/usr/share/nginx/www/ipzmall.com/media/catalog/product/1/9"
	            	$absolute_file_path=$dir.'/'.$ff;
	            	echo '<br>';
	            	var_dump($absolute_file_path);
	            	echo '<br>';

	            	$relative_file_path=str_replace("/usr/share/nginx/www/ipzmall.com/media/catalog/product", "", $absolute_file_path);
	            	var_dump($relative_file_path);
	            	
	            	if (!is_in_database($relative_file_path)){
	            		echo ("<br> -->is Not In Database.<br>");
	            		if (file_exists($absolute_file_path)){
	            			unlink($absolute_file_path);
	            		}
	            	}
	        		// break;//do once for each leaf folder
	            }
	            echo '</li>';
	        }
	    }
	    echo '</ul>';
	}
	function is_in_database($image){
		global $db_handle;
		$query="select * from all_used_images where image_path='$image'";
		$result=$db_handle->runQuery($query);
		var_dump($query);
		return !is_null($result);
	}
	function iterateThroughImageFolder(){
		// $image_dir="/usr/share/nginx/www/awesomejiayi.space/media/catalog/product/";
		$image_dir="/usr/share/nginx/www/ipzmall.com/media/catalog/product";
		listFolderFiles($image_dir);
	}
	/**
	* Return bool
	*/
	function insertToDatabase($image){
		global $db_handle;
		$query="insert into `all_used_images` (`image_path`) VALUES ('$image')";
		$result=$db_handle->runQuery($query);
		return $result;
	}









//========================backup of By Cat version=================================
/*
$cat_id = $this->getRequest()->getParam('cat_id');
		if (!is_numeric($cat_id)){
			echo 'not valid';
			return false;
		}

		$category = new Mage_Catalog_Model_Category();
		$category->load($cat_id);//414 

		require_once Mage::getBaseDir('lib').'/alice/dbcontroller.php';
		$this->db_handle=new DBController();

		$productCollection = Mage::getResourceModel('reports/product_collection')
					    // ->addAttributeToSelect('*')
					    ->addAttributeToSelect('sku')
					    ->addAttributeToSelect('name')
					    ->addAttributeToSelect('image')
					    ->addAttributeToSelect('use_external_images')
					    ->addAttributeToSelect('image_external_url');
		$productCollection->addCategoryFilter($category);

		$count=0;
		foreach ($productCollection as $product) {
			echo $product->getName()."<br>";
			//----------get the real image path of product with ext images------
			$_helper = Mage::helper('ExtImages/Image');
			if ($product->getData('use_external_images')) {
				$imageUrl = $product->getData('image_external_url');
				$actual_image=$_helper->initProd($product->getName(),$imageUrl)->curl_get_image();//e.g. '/f/i/filson-down-cruiser-jacket---mens-dark-tan-l-f6247b75d2e646e82835309125ace9a6.jpg'
				// var_dump($actual_image);
				$query="select * from all_used_images where image_path='$actual_image'";
				$result=$this->db_handle->runQuery($query);
				if (is_null($result)){
					$result=$this->insertToDatabase($actual_image);
					if (!$result) echo 'insert failed.<br>';
				}
				//$full_path="/usr/share/nginx/www/awesomejiayi.space/media/catalog/product".$actual_image;
				// var_dump(file_exists($full_path));
			}else{
				echo '-------';
				$imagePath=$product->getData('image');
				if (!is_null($imagePath)&&!empty($imagePath) && $imagePath!=="no_selection") {
					$result = $this->insertToDatabase($imagePath);
					if (!$result) echo 'insert failed.<br>';
//					var_dump($imagePath);
				}
			}
			//------------------------------------------------------------------
			// if ($count>30) break;
			$count++;
		}
		//-----------finished recording all downloaded images--------------
		// $this->iterateThroughImageFolder();

		// $query="select * from workers";
		// $result=$db_handle->runQuery($query);
		// var_dump($result);
*/