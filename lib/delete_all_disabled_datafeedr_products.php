<?php



class delete_all_disabled_datafeedr_products{
	public $output_csv_name;
	public $output_csv_path;
	function __construct() {
		$this->output_csv_name="give_magmi_to_delete.csv";
		$this->output_csv_path=__DIR__."/../../magmi_csv/alice_import/".$this->output_csv_name;
		unlink($this->output_csv_path);
		$this->generate_whole_csv();
		$this->run_magmi_delete_on_csv();
		echo "\n Done Running magmi delete profile on give_magmi_to_delete.csv\n";
	}
	function generate_whole_csv(){
		$this->write_csv("store,sku,magmi:delete");
		require __DIR__.'/../config.php';
		foreach ($filter_strings as $cat_id => $value) {
			echo "\n----------outputing rows for datafeedr cat_id: ",$cat_id,"-----------\n";
			// $name=getCategoryName($cat_id);
			// echo "<td>".$name."</td>";
			// display_status_for_products($cat_id);
			$this->output_csv_rows_for_cat((int)$cat_id);
			// break;
		}
	}
	/*
	Output all skus of disabled products(with is_datafeedr_product=1).
	*/
	function output_csv_rows_for_cat($cat_id){
		require_once __DIR__.'/../../../app/Mage.php';
		Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
		//----------------------------------------
		$categoryId = $cat_id; // a category id that you can get from admin
		$category = Mage::getModel('catalog/category')->load($categoryId);

		$collection = Mage::getModel('catalog/product')
		    ->getCollection()
		    ->addCategoryFilter($category)
		    // ->addAttributeToSelect('is_datafeedr_product')
		    // ->addAttributeToSelect('status')//----this will break status filter. return 0 results
		    // ->addAttributeToSelect('weight')
		    ->addAttributeToFilter('is_datafeedr_product', 1) 
		    ->addAttributeToFilter('status', array('eq' => 2)) //only disabled 
		    ->load();
		  // var_dump($collection->getSelectSql());
		echo "\nNum of disabled products in this category (",$cat_id,"): ", $collection->count(),"\n"; // 0 products
		//-----------------------------------------
		foreach ($collection as $product) {
			// echo $product->sku."<br>";
			$this->write_csv("admin,".$product->sku.",1");
		}
		echo "\nDone writing them to csv\n"; 
	}
	
	function run_magmi_delete_on_csv(){
		// $working_shell_command='php5 /usr/share/nginx/www/ipzmall.com/alice/magmi/cli/magmi.cli.php -profile="pls_do_not_change_me_delete" -mode="create"  -CSV:filename="/usr/share/nginx/www/ipzmall.com/alice/magmi_csv/alice_import/for_reindex.csv"';
		$shell_command='php5 /usr/share/nginx/www/ipzmall.com/alice/magmi/cli/magmi.cli.php -profile="pls_do_not_change_me_delete" -mode="create"  -CSV:filename="/usr/share/nginx/www/ipzmall.com/alice/magmi_csv/alice_import/'.$this->output_csv_name.'"';
		var_dump($shell_command);
		$result=shell_exec($shell_command);
		// echo 'the magmi cli result is : '.$result;
		//result is always null. because magmi did not set this up. but it works.
		//---------------delete the csv--------------------
		// unlink('../magmi_csv/alice_import/'.$output_csv_name);
	}
	function write_csv($object)
	{  
	 	error_log($object."\n", 3, $this->output_csv_path);
	    return true;
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