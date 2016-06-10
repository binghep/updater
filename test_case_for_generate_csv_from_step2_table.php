<?php

//===========================test cases=========================
require_once 'lib/generate_csv_from_step2_table.php';
$csv_exporter=new generate_csv_from_step2_table("womens_top-alice");

if ($csv_exporter===false){
	die ('cant instantiate generate_csv_from_step2_table object. exit.'); 
	
}
$output_csv_name=$csv_exporter->run();
if (empty($output_csv_name) || is_null($output_csv_name)){
	die("Output CSV path cannot be found.");
}else{
	echo ("Output CSV Name: $output_csv_name");
}
//---------call product manager to generate images for all products in this category---------
// $go_to_url=$product_manager_url."index.php?cat_id=".$this->magento_category_id."&secret_path=yes";
// var_dump($go_to_url);