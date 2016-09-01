<?php

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");

// http://ems.1661hk.com/wp-content/plugins/datafeedr-api/libraries/full_example.php
// http://ems.1661hk.com/productExport-test/datafeedr_api.php
// https://v4.datafeedr.com/documentation/136
/**
 * Require the Datafeedr Api Client Library.
 */



require __DIR__.'/../../config.php';
// require '/usr/share/nginx/www/ipzmall.com/wp/wp-content/plugins/datafeedr-api/libraries/datafeedr.php';
		require '/usr/share/nginx/www/ipzmall.com/alice/datafeedr_updater/lib/datafeedr-api/libraries/datafeedr.php';

$api=new DatafeedrApi( $datafeedr_id, $datafeedr_key );

// $networks = $api->getNetworks( array( 270 ) );
// $networks = $api->getNetworks();

$search = $api->searchRequest();
// $search->addFilter('merchant LIKE "Backcountry.com"');

// $search->addFilter('merchant_id in 42681');
// $search->addFilter('merchant_id in 33092,29129');

$array=array('merchant_id in 42681,29129',//42681 is nordstrom
							'category !LIKE Kayak',
							'category !LIKE Sweaters',
							'category LIKE Women',
							'category LIKE jacket',
							'saleprice !=0',
							'currency = USD',
							'sku !EMPTY',
							'source_id in 126,3,120,4,7,6',
							'image !EMPTY'
							);

foreach ($array as $a) {
	$search->addFilter($a);
}
// $search->addFilter('source_id in 126,3,120,4,7,6');

$products = $search->execute();
// $merchants = $search->getMerchants();
echo '<pre>';

// print_r($merchants);
var_dump($products);