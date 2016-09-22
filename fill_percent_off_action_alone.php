<?php

	echo "\n---------Filling clearance percent off attribute of all products---------\n";
	require __DIR__.'/lib/fill_clearance_percent_off.php';
	$thirty_off_option_id=1921;
	$fourty_off_option_id=1922;
	$fifty_off_option_id=1923;
	$fill_fifty_percent_off=new fill_clearance_percent_off($thirty_off_option_id,$fourty_off_option_id,$fifty_off_option_id);
	echo "\n-----------Done Filling clearance_percent_off attribute--------\n";