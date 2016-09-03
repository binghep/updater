<?php 

//step1
$debug=false;
// $magento_categories="828,845";//GPS watches
// $magento_categories="474,831";//Headphone & speaker
// $magento_categories="444,443";

//step2
// $output_csv_name="women_dresses";
// $output_csv_name="women_accessories";

$weight=0.9;
// $weight=2;

require __DIR__."/database/database.php";

$mysql_table_step_1_datafeedr_results="step_1_datafeedr_results";
$mysql_table_step_2_scrapped_results="step_2_scrapped_results";


// $simple_products_categories=array(444,559,446,844,845,846,849,850);
$simple_products_categories=array(446,844,845,846,849,850,863,864,866,869,871,872,884,885,886,890);
// $ommit_categeries=array(446,844,845,846,849,850);
$ommit_cat_threshold=850; //if cat id < 850, ommit
//-------------production------------
// $max_num_products_needed=600;
// $num_products_per_page=50;
//-------------testing------------
$max_num_products_needed=500;//1,000 API requests/month   100 products/request
$num_products_per_page=100;

// $max_num_products_needed=20;
// $num_products_per_page=10;


// $magmi_csv_folder="/usr/share/nginx/www/ipzmall.com/alice/magmi_csv/";
// $root_dir="/usr/share/nginx/www/ipzmall.com/alice/datafeedr_updater/";
// ------------------------------------------------------------------
$filter_strings=array();


// $filter_strings["855"]=array(
// 		// 'category LIKE Beauty',
// 		'merchant LIKE "Nordstrom"',
// 		// 'name LIKE "Facial Cleanser"',
// 		// 'name LIKE "Cleansing"',
// 		// 'name LIKE "foundation"',
// 		'name LIKE "eye gel"',
// 		// 'category LIKE "Skin"',
// 		// 'category LIKE "Men"',
// 		// 'salediscount>2'
// 	);


//
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
$filter_strings["662"]=array(
							'merchant_id in 42681,29129',
							// 'name LIKE shirts|shirt|blouse|top',
							'category LIKE Women',
							// 'category LIKE Clothing',
							'category LIKE Tops',//women tops are two category keywords for both merchants, but need to exclude following subcategories.
							'category !LIKE Bra',
							'category !LIKE Sweater',
							'category !LIKE Bikini',
							'category !LIKE Swimwear',
							// 'salediscount>29'
							);
//sweaters:
$filter_strings["672"]=array('merchant_id in 42681,29129',
							'name LIKE Sweater',
							'category LIKE Women',
							// 'salediscount>29'
							);
//Dresses:
$filter_strings["462"]=array('merchant_id in 42681',//29129
							'name !LIKE Skirt',
							'category LIKE Women',
							'category LIKE Dresses',
							// 'salediscount>29'
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
							// 'salediscount>29'
							);

// Outerwears:
$filter_strings["526"]=array('merchant_id in 42681,29129',//42681 is nordstrom
							'category !LIKE Kayak',
							'category !LIKE Sweaters',
							'category LIKE Women',
							'category LIKE jacket',
							// 'salediscount>29'
							);
// Underwear & Baselayers : 
$filter_strings["530"]=array('merchant_id in 42681,29129',//29129 is commision junction's backcountry.com
							// 'category LIKE Baselayers',
							// 'category LIKE Clothing',
							'category LIKE Women',
							'category LIKE underwear',
							'category !LIKE PLUS',
							'category !LIKE Accessory'
							// 'category !LIKE Bra',
							// 'salediscount>29'
							);
// Denim :
$filter_strings["537"]=array('merchant_id in 42681,29129',//29129
							'category LIKE Women',
							'category LIKE Pant|Pants',
							'category !LIKE Tights',//backcountry tights
							'name LIKE Jean',
							// 'name LIKE straight',//39 results
							// 'name LIKE skinny',//635 results
							// 'name LIKE Boyfriend',//100 results
							// 'name LIKE Bootcut',//95 results
							// 'name LIKE Flare',//118 results
							// 'name LIKE DISTRESSED',//56 results
							// 'name LIKE Denim'
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
$filter_strings["822"]=array('merchant_id in 42681',//much better looking than backcountry pictures
							'category LIKE Men',
							'category LIKE shirts|jersey|tops',
							'category !LIKE Underwear',
							// 'salediscount>29'
							);
//Sweaters & Hoodies:
$filter_strings["702"]=array('merchant_id in 42681,29129',
							// 'category !LIKE Kayak',
							'category LIKE Sweater|Hoodie',
							'category LIKE Men',
							// 'salediscount>29'
							);
//Suits :-->to do: use Macys.com scrapper:
// $filter_strings["823"]=array('merchant LIKE "Macys.com"',
// 							'category LIKE "Men"',
// 							'category LIKE "Suit"'
// 							); 
// Bottom :
$filter_strings["629"]=array('merchant_id in 42681,29129',
							'category LIKE Men',
							'name !LIKE Denim',

							'category LIKE pants|shorts',
							'category !LIKE Shirts',
							'category !LIKE Short',
							'category !LIKE Tops',
							'category !LIKE Underwear',
							'category !LIKE Jerseys',
							'category !LIKE Denim',
							// 'salediscount>29'
							);
// Outerwears:
$filter_strings["728"]=array('merchant LIKE "Backcountry.com"',
							'category !LIKE Kayak',
							'category !LIKE Sweaters',
							'category LIKE Men',
							'category LIKE jacket',
							// 'salediscount>29'
							);
// Underwear & Baselayers : 
$filter_strings["556"]=array('merchant_id in 42681,29129',
							// 'category LIKE Baselayers',
							// 'category LIKE Clothing',
							'category LIKE Men',
							'category LIKE underwear',
							// 'salediscount>29'
							);
// Denim :
$filter_strings["782"]=array('merchant_id in 42681',//remove backcountry. no people in image(to ugly)
							'category LIKE Men',
							'category LIKE Pant',
							'name LIKE Jean',
							);

//===========================================Kids=================================================
// Babies Clothing
$filter_strings["697"]=array('merchant_id in 42681,29129',
							'category !LIKE Toddler',
							'category LIKE Infant',
							'category !LIKE Games'
							// 'salediscount>29'
							);
// Toddler Boys Clothing:
$filter_strings["715"]=array('merchant_id in 42681,29129',
							'name LIKE "Toddler Boy"',
							'category !LIKE Shoes',
							// 'salediscount>29'
							);
// Toddler Girls Clothing
$filter_strings["735"]=array('merchant_id in 42681,29129',
							'name LIKE "Toddler Girl"',
							'category !LIKE Shoes',
							// 'salediscount>29'
							);
// Boys Clothing :
$filter_strings["790"]=array('merchant_id in 42681,29129',
							'name LIKE "Boy"',
							'name !LIKE "Boys and Arrows"',
							'name !LIKE Toddler',
							'name !LIKE Women',
							'category !LIKE Infant',
							'category !LIKE Shoes',
							// 'category LIKE "Clothing"',
							// 'salediscount>29'
							);
// Girls Clothing :
$filter_strings["826"]=array('merchant_id in 42681,29129',
							'category LIKE "Girl"',
							'name !LIKE Toddler',
							'name !LIKE Women',
							'category !LIKE Infant',
							'category !LIKE Women',
							'category !LIKE Shoes',
							'category !LIKE Socks',
							// 'category LIKE "Clothing"',
							// 'salediscount>29'
							);
//===========================================Bags & Wallet=================================================
//Bags & Wallet
$filter_strings["446"]=array(
							'merchant_id in 42681,29129',
							// 'category LIKE Bag|Wallet',
							
							// 'name like wallet',
							'category LIKE Bag|Wallet',
							'category LIKE Women|Men',

							// 'category LIKE Clothing',
							'category !LIKE Yoga',
							// 'name !LIKE Umbrella',
							// 'name LIKE Bag|Tote|Wallet',
							'price > 1500',
							// 'salediscount>29'
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
							'name !LIKE DecalGirl',//too many different color
							'ANY !LIKE Refurb',
							'name !LIKE Adapter',
							'name !LIKE Wire',
							'price > 2500',
							// 'salediscount>29'
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
							// 'salediscount>29'
							);
// GPS watches
$filter_strings["846"]=array('name LIKE GPS|Gps|gps',
							'name LIKE Watch|watch',
							'ANY !LIKE Refurb',
							'category !LIKE "Refurbished"',
							'price > 1500',
							// 'salediscount>29'
							);


//===========================================Nutrition=================================================
// Vitamins & Supplements
$filter_strings["849"]=array(
							// 'merchant LIKE A1Supplements|Vitamin',
							// 'merchant LIKE "Vitamin World"',
							'merchant_id != 851',
							'name LIKE Vitamin|supplement|Calcium|Gummi|Zinc|Chew|Amino|Protein|Iron|Magnesium|Selenium',
							// 'category !LIKE Accessories',
							'category LIKE Nutrition|Vitamin|Mineral',
						
							// 'salediscount>2'
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
							// 'merchant LIKE "Vitamin World"',
							'merchant_id != 851',//vitamin world
							'merchant_id != 62315',//opensky a lot of bars
							'merchant_id != 38626',//opensky a lot of bars

							'name !LIKE Chia',
							// 'name like Snack',
							'category LIKE Snacks',
							'category !LIKE Sugar',
							// 'salediscount>2'
							);
//Chia Seeds:only 9 products. better not do this. cause we have more xiomega.
// $filter_strings["851"]=array('merchant LIKE "Vitamin World"',
							// 'name LIKE Chia'
							// );






// -----------------------Camp & Hike--------------------------------------


//Camp & Hike->Backpacks:done
$filter_strings["863"]=array(
							// 'name LIKE Osprey',
							'merchant_id in 29129',
							'category LIKE "Backpacking Backpacks"',
							);


//Camp & Hike->tents:done
$filter_strings["864"]=array(
							
							// 'name LIKE Osprey',
							// 'name LIKE Pack',
							'merchant_id in 29129', //remove it get more products,but other merchants might not have link in datafeedr
							// 'name LIKE "Big Agnes"',
							'category LIKE "Tent"',
							'category LIKE "Camp"',
							'name LIKE "Tent"',
							'name !LIKE "Footprint"',
							'any !LIKE Cloth',
							'merchant !LIKE "UnbeatableSale.com"',
							'price > 5500',
							);

//Camp & Hike->sleeping back:done
$filter_strings["865"]=array(
							
							// 'name LIKE Osprey',
							// 'name LIKE Pack',
							'merchant_id in 29129',//remove it get better products, but no affiliate link or scrapper to scrape size.
							// 'merchant !LIKE "UnbeatableSale.com"',
							// 'merchant !LIKE "Gearbest"',
							// 'merchant !LIKE "Cabela"',
							// 'merchant !LIKE "Slumberjack"', //no image
							// 'merchant !LIKE "ABaby.com"',
							// 'merchant !LIKE "KnifeCountryUSA.com"',
							// 'merchant !LIKE "SwissOutpost and Swiss Knife Depot"',
							// 'merchant !LIKE "ProBoardShop.com"',

							'category !LIKE "Sacks"',
							'category LIKE "sleeping bag"',
							'name !LIKE Blankets',
							'name !LIKE Trekker',
							'name !LIKE Wildkin',
							'category !LIKE "Blue Ridge Firearms"',
							'price > 6500',
							
						
							);







//Camp & Hike->pads & Hammocks: done  -- might not have affiliate link
$filter_strings["866"]=array(
							// 'merchant_id in 42681,29129',
							'category LIKE Camp',
							'category LIKE Pads|Hammocks',
							'name LIKE Pad|Hammocks',
							'category !LIKE Kids',
							'name !LIKE Hid',
							'price > 5500',
							// 'salediscount>29'
							);
// -----------------------Climb--------------------------------------

//Climb->Climbing Shoes:done
$filter_strings["868"]=array(
							'merchant_id in 29129',//if want more than 347 products, can remove this restriction after adding more scrapper(<100 products per new scrapper).
							'category LIKE Shoes',
							'category !LIKE Kids',
							'name LIKE Climbing Shoes',
							// 'name LIKE Bag|Tote|Wallet',
							'price > 4500',
							// 'salediscount>29'
							);


//Climb->Climbing Harness:done
$filter_strings["869"]=array(
							// 'merchant_id in 42681,29129',
							'category LIKE "Climbing Harness"',
							'name !LIKE Pants',
							'category !LIKE Kids',
							'price > 4500',
							// 'salediscount>29'
							);


//Cycle->Bikes:done
$filter_strings["871"]=array(
							// 'merchant_id in 42681,29129',
							'category LIKE "Bike"',
							'category !LIKE Accessories',
							'category !LIKE Clothing',
							'category LIKE Complete',
							'category !LIKE Kids',
							'merchant_id in 29129',//only backcountry.com has complete bikes category
							'price > 27000',
							// 'salediscount>29'
							);

//Cycle->Bike Helmets:done
$filter_strings["872"]=array(
							// 'merchant_id in 42681,29129',
							'merchant_id in 29129',
							'category LIKE "Bike Helmets"',
							// 'category LIKE Pads|Hammocks',
							'name LIKE Helmet',
							'category !LIKE Kids',
							// 'price > 27000',
							// 'salediscount>29'
							);
//Cycle->Cycling Clothing:done //just no shoes //must be backcountry because clothing has size
$filter_strings["873"]=array(
							// 'merchant_id in 42681,29129',
							// 'merchant LIKE "Competitive Cyclist"', //since this one has same products as backcountry.com
							'merchant_id in 29129', //backcountry.com has 9000 products and not as good as above. can be a 2nd choice when 1st choice was gone.
							'category LIKE Cycling|Bike|Cycle|Bycicle',
							'category LIKE Clothing',
							'category !LIKE Kids',
							'category !LIKE Shoes',		
							'category !LIKE Accessories',					 
							'name !LIKE Shoe',							 
							'name !LIKE Booties',							 
							'price > 3800',
							// 'salediscount>29'
							);
//Cycle->Cycling Shoes:done //must be backcountry because shoes have size
$filter_strings["874"]=array(
							// 'merchant_id in 42681,29129',
							// 'merchant LIKE "Competitive Cyclist"', //since this one has same products as backcountry.com
							'merchant_id in 29129', //backcountry.com has 9000 products and not as good as above. can be a 2nd choice when 1st choice was gone.
							'category LIKE Cycling|Bike|Cycle|Bycicle',
							'category LIKE Shoes',
							'category !LIKE Kids',
							'category !LIKE Covers',							
							'price > 3800',
							// 'salediscount>29'
							);
// -----------------------------Run------------------------------------
//Run->Men's Running Clothing: 
$filter_strings["876"]=array(
							// 'merchant_id in 42681,29129',
							'merchant_id in 29129',
							'category LIKE Men',
							'category LIKE Performance',
							'category !LIKE Nutrition',							 							
							'price > 1500',
							// 'salediscount>29'
							);
//Run->Men's Running Shoes: 
$filter_strings["877"]=array(
							// 'merchant_id in 42681,29129',
							'merchant_id in 29129', 
							'category LIKE Run',
							'category LIKE Men',
							// 'category LIKE Clothing',							
							'category LIKE Shoes',
							'category !LIKE Snowshoes',
							'price > 3800',
							// 'salediscount>29'
							);

//Run->Women's Running Clothing: 
$filter_strings["878"]=array(
							// 'merchant_id in 42681,29129',
							'merchant_id in 29129', 
							'category LIKE Women',
							'category LIKE Performance',
							'category !LIKE Nutrition',	

							'category !LIKE Bras',
							'category !LIKE Footwear',
							'name !LIKE Bra',

							'price > 1500',
							// 'salediscount>29'
							);
//Run->Women's Running Shoes: 
$filter_strings["879"]=array(
							// 'merchant_id in 42681,29129',
							'merchant_id in 29129', 
							'category LIKE Run',
							'category LIKE Women',
							// 'category LIKE Clothing',							
							'category LIKE Shoes',
							'category !LIKE Snowshoes',
							'price > 3800',
							// 'salediscount>29'
							);

//------------------------------snow-------------------------------------
//Snow->Downhill Skiing
$filter_strings["881"]=array(
							'merchant_id in 29129', 
							'category LIKE Ski',
							// 'category LIKE Women',
							'category LIKE Clothing',							
							'category !LIKE Shoes',
							'category !LIKE Hats',
							'category !LIKE Kids',
							'category !LIKE Toddler',
							// 'category !LIKE Base Layers',
							'category !LIKE Helmet',
							'category !LIKE Gloves',
							'price > 3800',
							// 'salediscount>29'
							);
//Snow->Downhill Ski Clothing
$filter_strings["881"]=array(
							'merchant_id in 29129', 
							'category LIKE Ski',
							// 'category LIKE Women',
							'category LIKE Clothing',							
							'category !LIKE Shoes',
							'category !LIKE Hats',
							'category !LIKE Kids',
							'category !LIKE Toddler',
							// 'category !LIKE Base Layers',
							'category !LIKE Helmet',
							'category !LIKE Gloves',
							'price > 3800',
							// 'salediscount>29'
							);
//Snow->Snowboard Clothing
$filter_strings["882"]=array(
							'merchant_id in 29129', 
							'category LIKE Snowboard',
							// 'category LIKE Women',
							'category LIKE Clothing',							
							'category !LIKE Shoes',
							'category !LIKE Hats',
							'category !LIKE Kids',
							'category !LIKE Toddler',
							// 'category !LIKE Base Layers',
							'category !LIKE Helmet',
							'category !LIKE Gloves',
							'price > 3800',
							// 'salediscount>29'
							);
//---------------------------Travel---------------------------
//Travel->Luggage:884
$filter_strings["884"]=array(
							// 'merchant_id in 29129', 
							'category LIKE Luggage',
							'category !LIKE Accessories',
							'category !LIKE Carry',//carry on luggage
							'category !LIKE Suitcases',
							'category !LIKE Sets',

							'name !LIKE Suitcase',
							'name !LIKE Set',
							// 'name !LIKE Wildkin',//look too girlish
							'name !LIKE Carry-On',
							'name LIKE Luggage|Duffel',

							'merchant !LIKE UnbeatableSale.com',//some looks cheap although is not cheap
							'merchant !LIKE OpenSky',
							'merchant !LIKE Shoebuy.com', //not look like sport luggage. look like work luggage/alice's luggage
							'merchant !LIKE Boscov',//Boscov's Department Stores: image not clear
							'price > 6000',
							// 'salediscount>29'
							);
//Travel->Day Bags:(actually day packs)
$filter_strings["885"]=array(
							// 'merchant_id in 29129', 
							'category LIKE Daypack',
							'category !LIKE Slings',
							'category !LIKE School',

							// 'name LIKE Daypack',

							// 'merchant !LIKE UnbeatableSale.com',//some looks cheap although is not cheap
							// 'merchant !LIKE OpenSky',
							// 'merchant !LIKE Shoebuy.com', //not look like sport luggage. look like work luggage/alice's luggage
							// 'merchant !LIKE Boscov',//Boscov's Department Stores: image not clear
							// 'price > 6000',
							// 'salediscount>29'
							);

//Travel->Car Racks:
$filter_strings["886"]=array(
							// 'merchant_id in 29129', 
							'category LIKE Car',
							'category LIKE Racks',
							// 'category !LIKE School',

							'name like Yakima|Thule',
							// 'name LIKE Daypack',

							'merchant !LIKE REI.com',//image not available
							// 'merchant !LIKE OpenSky',
							// 'merchant !LIKE Shoebuy.com', //not look like sport luggage. look like work luggage/alice's luggage
							// 'merchant !LIKE Boscov',//Boscov's Department Stores: image not clear
							'price > 7000',
							// 'salediscount>29'
							);

//---------------------------Yoga---------------------------
//Yoga->Women's Yoga Clothing: 
$filter_strings["888"]=array(
							'merchant_id in 29129', 
							'category LIKE Women',
							'name LIKE Yoga',
							'category !LIKE Bras',
							// 'category !LIKE Footwear',

							'price > 1500',
							// 'salediscount>29'
							);
//Yoga->Men's Yoga Clothing: //is actually t-shirt, cannot find keyword for not tight pants
$filter_strings["889"]=array(
							'merchant_id in 42681,29129',
							// 'merchant_id in 29129,4', 
							'category LIKE Men',
							// 'category LIKE Casual',
							// 'category LIKE ',
							'name LIKE T-Shirt|Hoodie',
							// 'name LIKE prAna',
							// 'name LIKE Shirt|Shorts|Top|Jacket|Pant|Pants|Hoodie',
							'price > 2500',
							// 'salediscount>29'
							);

//Yoga->Yoga Gear: 
$filter_strings["890"]=array(
							// 'merchant_id in 42681,29129',
							// 'merchant_id in 29129,4', 
							'category LIKE Yoga',
							'category LIKE Accessories',
							'merchant !LIKE OpenSky',
							// 'name LIKE prAna',
							// 'name LIKE Shirt|Shorts|Top|Jacket|Pant|Pants|Hoodie',
							'price > 500',
							// 'salediscount>29'
							);





// //for testing
// $filter_strings["850"]=array(
// 							// 'merchant_id in 42681,29129',
// 							'category LIKE "Bike"',
// 							// 'category LIKE Pads|Hammocks',
// 							'name LIKE Bike',
							
							
// 							// 'category LIKE Women|Men',

// 							// 'category LIKE Clothing',
// 							'category !LIKE Kids',
// 							// 'name LIKE Climbing Harness',
// 							// 'name LIKE Bag|Tote|Wallet',
// 							'price > 27000',
// 							// 'salediscount>29'
// 							);