<?php
require 'config.php';
require 'lib/datafeedr_api.php';
$datafeedr_api=new datafeedr_api("333,21",$filter_strings['662'],true,50);
$datafeedr_api->printProducts(1);
echo '---------------page2----------------';
$datafeedr_api->printProducts(2);
echo '---------------page3-------------';
$datafeedr_api->printProducts(3);
echo '---------------page4-------------';
$datafeedr_api->printProducts(4);