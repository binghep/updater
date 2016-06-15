<?php

$test=json_encode(array(
					"0"=>array("price"=>"120"),
					"1"=>array("price"=>"100")
				      )
			);


var_dump($test);

//=====================================================

$test=json_encode(array(
					"0"=>array("price"=>"120"),
					"1"=>array("price"=>"100")
				      )
			,JSON_FORCE_OBJECT);

var_dump($test);
//=====================================================















$test=json_encode(array(
					"S"=>array("price"=>"120"),
					"M"=>array("price"=>"100")
				      )
			);


var_dump($test);
