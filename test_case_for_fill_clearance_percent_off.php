<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");

require 'lib/fill_clearance_percent_off.php';
//--------------------------test case 1-----------------------------
	//30%=1921
	//40%=1922
	//50%=1923
// $option_id=1920;
$thirty_off_option_id=1921;
$fourty_off_option_id=1922;
$fifty_off_option_id=1923;
$fill_fifty_percent_off=new fill_clearance_percent_off($thirty_off_option_id,$fourty_off_option_id,$fifty_off_option_id);
