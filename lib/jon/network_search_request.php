<?php
// http://ems.1661hk.com/wp-content/plugins/datafeedr-api/libraries/full_example.php
// http://ems.1661hk.com/productExport-test/datafeedr_api.php
// https://v4.datafeedr.com/documentation/136
/**
 * Require the Datafeedr Api Client Library.
 */




require '/usr/share/nginx/www/ipzmall.com/wp/wp-content/plugins/datafeedr-api/libraries/datafeedr.php';
$api=new DatafeedrApi( 'uqzonmoujxgycslwwg9c', 'IAgLDmR00vwERMpewaALXOkvKGcpXHEFHTl0nxLGDScUGimhmgRkoILoIFc97geu' );

// $networks = $api->getNetworks( array( 270 ) );
$networks = $api->getNetworks();

// $keyword=array("AvantLink US","AvantLink US Coupons/Deals","Commission Junction","Commission Junction Coupons/Deals","LinkShare US","LinkShare US Coupons/Deals","PepperJam","PepperJam Coupons/Deals","ShareASale");
$keyword=array("AvantLink US","Commission Junction","Commission Junction Coupons/Deals","LinkShare US","PepperJam","ShareASale");
echo '<pre>';
foreach( $networks as $network ) {
    // echo $network['name'] . '<br />';
    if (in_array($network['name'],$keyword)){
    	echo '<br>-------------------<br>';
    	var_dump($network);
    }
}
echo '</pre>';

?>


<?php

$fields = $api->getFields();
foreach( $fields as $field ) {
  echo $field['name'] . " (".$field['type'].")\n";
}


/*
brand (text) category (text) currency (int) description (text) gender (text) merchant (text) merchant_id (int) name (text) onsale (int) price (int) salediscount (int) saleprice (int) source (text) source_id (int) tags (text) time_created (date) time_updated (date)
*/


/*
AvantLink US: 126
// AvantLink US Coupons/Deals: 127
Commission Junction: 3
Commission Junction Coupons/Deals: 120
LinkShare US: 4
PepperJam: 7
ShareASale: 6
*/