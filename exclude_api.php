<?php
/**
 * input 3 param: $sku, $cat_id,$action=exclude|restore   
 * output: status: true|false
 */

// if output json status='false.', then action failed. 
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");

// require_once '../../app/Mage.php';
// Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
header('Content-type: application/json');

require_once 'database/dbcontroller.php';
$db_handle=new DBController();	
$supported_action=["exclude","restore"];

$action = $_GET['action'];
$sku = $_GET['sku'];
$cat_id=$_GET['cat_id'];


if (!is_numeric($cat_id) || !in_array($action,$supported_action)){
	$response=array(
				"status"=>false,
				"error_details"=>"param not valid",
				"action"=>$action,
				"sku"=>$sku,
				"cat_id"=>$cat_id
				);
	die(json_encode($response,JSON_FORCE_OBJECT));
}

$result="";
if ($action=="exclude"){
	$result=insert_to_database($cat_id,$sku,$db_handle);		
	echoResultInJsonDie($result,"insert to sql");
}elseif($action=="restore"){
	$result=remove_from_database($cat_id,$sku,$db_handle);
	echoResultInJsonDie($result,"delete from sql");
}

/**
 * echo the json based on the result of insert action
 * @param  bool $result Insert result runQuery() returned
 * @param  string $description_on_error Insert|Delete result from runQuery()
 * @return null         will die() after outputing json
 */
function echoResultInJsonDie($result,$description_on_error){
	if ($result===true){
		$response=array("status"=>true,
						"action"=>$_GET['action'],
						"sku"=>$_GET['sku'],
						"cat_id"=>$_GET['cat_id']);
		die(json_encode($response,JSON_FORCE_OBJECT));
	}else{
		$response=array("status"=>false,
						"error_details"=>"db_handle returns false upon ".$description_on_error,
						"action"=>$_GET['action'],
						"sku"=>$_GET['sku'],
						"cat_id"=>$_GET['cat_id']
						);
		die(json_encode($response,JSON_FORCE_OBJECT));
	}
}
/**
 * it inserts one row to datafeedr_exclude table under global_link_distribution database
 * @param  int $cat_id    magento cat id that we perform datafeedr import on
 * @param  string $sku       $sku from datafeedr
 * @param  DBController $db_handle 
 * @return bool            insert result from $db_handle. true or false
 */
function insert_to_database($cat_id,$sku,$db_handle){
	$query="insert into datafeedr_exclude (cat_id,sku) values ('".addslashes($cat_id)."','".addslashes($sku)."')";
	$result=$db_handle->runQuery($query);
	return $result;
}

/**
 * it removes all matching rows from datafeedr_exclude table under global_link_distribution database
 * @param  int $cat_id    magento cat id that we perform datafeedr import on
 * @param  string $sku       $sku from datafeedr
 * @param  DBController $db_handle 
 * @return bool            delete result from $db_handle. true or false
 */
function remove_from_database($cat_id,$sku,$db_handle){
	$query="delete from datafeedr_exclude where cat_id=".addslashes($cat_id)." and sku=".addslashes($sku);
	$result=$db_handle->runQuery($query);
	return $result;
}