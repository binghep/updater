<?php

$json='{"allSkusAreOnSale":false,"allSkusAreOutlet":true}';
var_dump(json_decode($json));
var_dump(json_last_error());//returns int(0) if no error