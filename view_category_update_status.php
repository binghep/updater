<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");

?>
<style type="text/css">
	table, tr, td {
	    padding: 5px 5px;
	    border: 1px solid #B3B3B3 !important;
	}
	table{
		border-collapse: collapse;
	}
</style>
<table>
<?php
require 'config.php';
require_once 'database/dbcontroller.php';


$db_handle=new DBController();	
echo "<tr><th>Category ID</th><th>Category Name</th><th> Category Last Update Time</th><th>Action</th></tr>";
foreach ($filter_strings as $key => $value) {
	echo "<tr>";
	echo "<td>".$key."</td>";
	//-----------------------------------------------------------
	$cat_id=(int)$key;
	$name=getCategoryName($cat_id);
	//------------------------
	echo "<td>".$name."</td>";
	//-----------------------------------------------------------
	$query="select max(history_id),cat_id,last_update from mage_datafeedr_auto_update_history where cat_id=".$key." group by cat_id";
	$result=$db_handle->runQuery($query);
	// var_dump($result);
	if (is_null($result)){
		echo '<td>No Such Record</td>';
		echo '<td>N/A</td>';
	}else{
		echo "<td>".$result[0]['last_update']."</td>";
		echo '<td>
		<a href="index-one-category-only-no-reindex.php?category_id='.$key.'">Update This Category</a><br>
		<a href="remove_disabled_products_in_category.php?category_id='.$key.'">Remove Disabled Products in this category</a><br>
		</td>';
	}
	echo "</tr>";
}

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
?>
</table>
