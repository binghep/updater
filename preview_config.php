<?php 

//step1
$debug=true;
// $magento_categories="828,845";//GPS watches
// $magento_categories="474,831";//Headphone & speaker
// $magento_categories="444,443";

//step2
// $output_csv_name="women_dresses";
// $output_csv_name="women_accessories";

$weight=1;
// $weight=2;

require __DIR__."/database/database.php";

$mysql_table_save_datafeedr="datafeedr_products_for_export";
$mysql_talbe_step2="";


// $simple_products_categories=array(444,559,446,844,845,846,849,850);
$simple_products_categories=array(446,844,845,846,849,850);

// ------------------------------------------------------------------
//Avantlink US: 33092,28511,95445,21731,48639,21631,21734,60388,40566,21659,21666,61261,21755,21691,21739,60626,60383,50107,21724,43864
//translated: 

//Commission Junction: 29129,94835,22262,2025,94836,63080,45369,2084,

//LinkShare US: 50897,53238,46253,62146,12443,95876,43862,43790,63129,11214,44866,52878,885,63627,42681,43793,61805,48925,22747,42797,42796,16647,63546,63815,64779,51639,93568,

//PepperJam:
//63856, 48672,95741,42835,92332,29813,95745,42297,61122,42806,60509,10234,94655,43437,43435,17627,42812,26614,92464,51760,50101

//All of above combined: 
//'merchant_id in 33092,28511,95445,21731,48639,21631,21734,60388,40566,21659,21666,61261,21755,21691,21739,60626,60383,50107,21724,43864,29129,94835,22262,2025,94836,63080,45369,2084,50897,53238,46253,62146,12443,95876,43862,43790,63129,11214,44866,52878,885,63627,42681,43793,61805,48925,22747,42797,42796,16647,63546,63815,64779,51639,93568,63856, 48672,95741,42835,92332,29813,95745,42297,61122,42806,60509,10234,94655,43437,43435,17627,42812,26614,92464,51760,50101'
// ------------------------------------------------------------------
$filter_strings=array();


$filter_strings["855"]=array(
		// 'category LIKE Beauty',
		'merchant LIKE "Nordstrom"',
		// 'name LIKE "Facial Cleanser"',
		// 'name LIKE "Cleansing"',
		// 'name LIKE "foundation"',
		'name LIKE "eye gel"',
		// 'category LIKE "Skin"',
		// 'category LIKE "Men"',
		// 'salediscount>2'
	);
//=========================================Women=================================================

//women accessories:
// $filter_strings["444"]=array('category LIKE "women accessories"',
// 								'ANY !LIKE "bag"',
// 								'ANY !LIKE "wallet"',
// 								'ANY !LIKE "tote"',
// 								'ANY !LIKE "handbag"',
// 								'price > 1500',
// 								'name !LIKE "hair"'
// 								);
// tops:
$filter_strings["662"]=array('merchant LIKE "Backcountry.com"',
							'name LIKE shirts|shirt|blouse|top',
							'category LIKE Women',
							'category LIKE Clothing',
							'category !LIKE Accessories',
							'category !LIKE Hoodie',
							'salediscount>29'
							);
//sweaters:
$filter_strings["672"]=array('merchant LIKE "Backcountry.com"',
							'category !LIKE Kayak',
							'category LIKE Sweaters',
							'category LIKE Women',
							'salediscount>29'
							);
//Dresses:
$filter_strings["462"]=array(//removed first backcountry 33092
							'merchant_id in 28511,95445,21731,48639,21631,21734,60388,40566,21659,21666,61261,21755,21691,21739,60626,60383,50107,21724,43864,29129,94835,22262,2025,94836,63080,45369,2084,50897,53238,46253,62146,12443,95876,43862,43790,63129,11214,44866,52878,885,63627,42681,43793,61805,48925,22747,42797,42796,16647,63546,63815,64779,51639,93568,63856, 48672,95741,42835,92332,29813,95745,42297,61122,42806,60509,10234,94655,43437,43435,17627,42812,26614,92464,51760,50101',
							// 'merchant LIKE "Backcountry.com"',
							'name LIKE dress',
							'category LIKE Women',
							'category LIKE Dress',
							// 'category LIKE Clothing',
							// 'category !LIKE Accessories',
							// 'category !LIKE Hoodie',
							'salediscount>29'
							);
// Bottom :
$filter_strings["496"]=array('merchant LIKE "Backcountry.com"',
							'category LIKE Women',
							'name !LIKE Denim',
							'category LIKE pants|shorts',
							'category !LIKE Shirts',
							'category !LIKE Tops',
							'category !LIKE Underwear',
							'category !LIKE Jerseys',
							'salediscount>29'
							);
// Outerwears:
$filter_strings["526"]=array('merchant LIKE "Backcountry.com"',
							'category !LIKE Kayak',
							'category !LIKE Sweaters',
							'category LIKE Women',
							'category LIKE jacket',
							'salediscount>29'
							);
// Underwear & Baselayers : 
$filter_strings["530"]=array('merchant LIKE "Backcountry.com"',
							'category LIKE Baselayers',
							'category LIKE Clothing',
							'name LIKE Women',
							'salediscount>29'
							);
// Denim :
$filter_strings["537"]=array('merchant LIKE "Backcountry.com"',
							'category LIKE Women',
							'category LIKE Pant',
							'name LIKE Denim'
							// 'salediscount>9'//remove because only 70 has 30%+ discount
							);
//===========================================Men=================================================
// Accessories
// $filter_strings["559"]=array('merchant !LIKE "G.H. Bass"',
// 								'category LIKE "men accessories"',
// 								'category !LIKE "shoes"',
// 								'name LIKE Hat|Beanie|Sunglasses|Tie|Case|Glove',
// 								'price > 1500',
// 								'salediscount>29'
// 								);
// tops:
$filter_strings["822"]=array('merchant LIKE "Backcountry.com"',
							'category LIKE Men',
							'category LIKE shirts|jersey|tops',
							'category !LIKE Underwear',
							'salediscount>29'
							);
//Sweaters & Hoodies:
$filter_strings["702"]=array('merchant LIKE "Backcountry.com"',
							'category !LIKE Kayak',
							'category LIKE Sweaters',
							'category LIKE Men',
							'salediscount>29'
							);
//Suits :-->to do: use Macys.com scrapper:
// $filter_strings["823"]=array('merchant LIKE "Macys.com"',
// 							'category LIKE "Men"',
// 							'category LIKE "Suit"'
// 							); 
// Bottom :
$filter_strings["629"]=array('merchant LIKE "Backcountry.com"',
							'category LIKE Men',
							'name !LIKE Denim',
							'category LIKE pants|shorts',
							'category !LIKE Shirts',
							'category !LIKE Tops',
							'category !LIKE Underwear',
							'category !LIKE Jerseys',
							'salediscount>29'
							);
// Outerwears:
$filter_strings["728"]=array('merchant LIKE "Backcountry.com"',
							'category !LIKE Kayak',
							'category !LIKE Sweaters',
							'category LIKE Men',
							'category LIKE jacket',
							'salediscount>29'
							);
// Underwear & Baselayers : 
$filter_strings["556"]=array('merchant LIKE "Backcountry.com"',
							'category LIKE Baselayers',
							'category LIKE Clothing',
							'name LIKE Men',
							'salediscount>29'
							);
// Denim :
$filter_strings["782"]=array('merchant LIKE "Backcountry.com"',
							'category LIKE Men',
							'category LIKE Pant',
							'name LIKE Denim'
							);

//===========================================Kids=================================================
// Babies Clothing
$filter_strings["697"]=array('merchant LIKE "Backcountry.com"',
							'category !LIKE Toddler',
							'category LIKE Infant',
							'salediscount>29'
							);
// Toddler Boys Clothing:
$filter_strings["715"]=array('merchant LIKE "Backcountry.com"',
							'name LIKE "Toddler Boy"',
							'salediscount>29'
							);
// Toddler Girls Clothing
$filter_strings["735"]=array('merchant LIKE "Backcountry.com"',
							'name LIKE "Toddler Girl"',
							'salediscount>29'
							);
// Boys Clothing :
$filter_strings["790"]=array('merchant LIKE "Backcountry.com"',
							'name LIKE "Boy"',
							'name !LIKE "Boys and Arrows"',
							'name !LIKE Toddler',
							'name !LIKE Women',
							'category !LIKE Infant',
							'category LIKE "Clothing"',
							'salediscount>29'
							);
// Girls Clothing :
$filter_strings["826"]=array('merchant LIKE "Backcountry.com"',
							'category LIKE "Girl"',
							'name !LIKE Toddler',
							'name !LIKE Women',
							'category !LIKE Infant',
							'category !LIKE Women',
							'category LIKE "Clothing"',
							'salediscount>29'
							);
//===========================================Bags & Wallet=================================================
//Bags & Wallet
$filter_strings["446"]=array('category LIKE Bag|Wallet',
							'category LIKE Clothing',
							'category !LIKE Yoga',
							'name !LIKE Umbrella',
							'name LIKE Bag|Tote|Wallet',
							'price > 1500',
							'salediscount>29'
							);
//===========================================Electronics=================================================
// Headphone & Speakers
$filter_strings["844"]=array('category !LIKE "Refurbished"',
							'category !LIKE "Book"',
							'category !LIKE "Cable"',
							'category LIKE Speaker|Headphone',
							'name LIKE headphone|headset|speaker',
							'name !LIKE Headbands',
							'name !LIKE Cable',
							'name !LIKE Replacement',
							'ANY !LIKE Refurb',
							'name !LIKE Adapter',
							'price > 1500',
							'salediscount>29'
							);
// Activity trackers
$filter_strings["845"]=array('name LIKE "tracker"',
							'name LIKE "activity"',
							'name !LIKE "jacket"',
							'name !LIKE "pant"',
							'category !LIKE "Apparel"',
							'ANY !LIKE Refurb',
							'category !LIKE "Refurbished"',
							'price > 1500',
							'salediscount>29'
							);
// GPS watches
$filter_strings["846"]=array('name LIKE GPS|Gps|gps',
							'name LIKE Watch|watch',
							'ANY !LIKE Refurb',
							'category !LIKE "Refurbished"',
							'price > 1500',
							'salediscount>29'
							);


//===========================================Nutrition=================================================
// Vitamins & Supplements
$filter_strings["849"]=array(
							// 'merchant LIKE A1Supplements|Vitamin',
							'merchant LIKE "Vitamin World"',
							'name LIKE Vitamin|supplement|Calcium|Gummi|Zinc|Chew|Amino|Protein|Iron|Magnesium|Selenium',
							'name !LIKE Lotion',
							// 'category !LIKE Accessories',
							'category !LIKE Oil',
							'category !LIKE Splitters',
							'category !LIKE Beauty',
							'category !LIKE Lip', 	
							'category !LIKE Cream',
							'category !LIKE Conditioner',
							'category !LIKE Hair',
							'category !LIKE Shampoo',
							'category !LIKE Deodorant',  	 
							'category !LIKE Pets',  	 
							'name !LIKE Donation',
							'salediscount>2'
							// 'category LIKE Vitamin|Mineral'
							);
// Vitamins & Supplements
// $filter_strings["849"]=array(
// 							// 'merchant LIKE A1Supplements|Vitamin',
// 							'merchant LIKE "Vitamin World"',
// 							// 'sku = 0070054472'
// 							'name != "Pure Protein Bars"'
// 						);
//Drinks & Snacks
$filter_strings["850"]=array(
							'merchant LIKE "Vitamin World"',
							'name !LIKE Chia',
							'category LIKE Snacks|Bars',
							'salediscount>2'
							);
//Chia Seeds:only 9 products. better not do this. cause we have more xiomega.
// $filter_strings["851"]=array('merchant LIKE "Vitamin World"',
							// 'name LIKE Chia'
							// );