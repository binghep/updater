<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");

require 'lib/fill_denim_type.php';
//--------------------------test case 1-----------------------------
// $option_id=1920;
$denim_type=array("straight"=>"1953",
					"Skinny"=>"1952",
					"Boyfriend"=>"1951",
					"Bootcut"=>"1950",
					"Flare"=>"1949",
					"DISTRESSED"=>"1947"
					);
// var_dump($denim_type);
// return;
$fill_denim_type=new fill_denim_type($denim_type);
