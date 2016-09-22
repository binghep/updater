<!DOCTYPE html>
<html>
<head>
	<title></title>
	<style type="text/css">
		body{
			font-family: arial;
	    	font-size: small;
		}
		.right{
			float:right;
		}
	</style>
</head>
<body>
<span class="right">
	<a href="preview_alice_menu.php">Back To Menu</a>
</span>

<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");




	// http://ems.1661hk.com/wp-content/plugins/datafeedr-api/libraries/full_example.php
	// http://ems.1661hk.com/productExport-test/datafeedr_api.php
	// https://v4.datafeedr.com/documentation/136
	/**
	 * Require the Datafeedr Api Client Library.
	 */
	// require_once '/usr/share/nginx/www/ems.1661hk.com/wp-content/plugins/datafeedr-api/libraries/datafeedr.php';
	// require '/usr/share/nginx/www/ipzmall.com/wp/wp-content/plugins/datafeedr-api/libraries/datafeedr.php';
	require '/usr/share/nginx/www/ipzmall.com/alice/datafeedr_updater/lib/datafeedr-api/libraries/datafeedr.php';

	require_once '../../app/Mage.php';
	// Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
	Mage::app();
	require_once 'database/dbcontroller.php';

class datafeedr_preview{
	public $db_handle='';
	public $magento_category_id='';
	public $magento_category_name='';
	public $ancestor_category_ids='';
	public $filter_strings;
	public $additional_filter_strings;

	//save the filter queries for current category:
	function __construct($filter_strings){
		$this->db_handle=new DBController();	
		$result=$this->init_filter_strings($filter_strings);
		if ($result===false){
			return false;//failed 
		}
		return true;
	}

	function echoWelcomeMsg(){
		
	}
	/*
	name contains "men shirt" =====>  
		$search->addFilter('name LIKE "shirt"');//otherwise down jacket show up (and that jacket has shirt as its category)
		$search->addFilter('name LIKE "Men"');
	catagory contains "men"=====>
		$search->addFilter('category !EMPTY');
	merchant is "Backcountry.com"=====>
		$search->addFilter('merchant_id in 29129,33092');    //i found out that from previous full_example.php
	product name doesn't contain "sweater":
		$search->addFilter('name !LIKE "sweater"');
	any field doesn't contain "hooded" =====>	

	// $search->addFilter('name LIKE "Men"');//name like "" must contain one phrase. no |. 
	// $search->addFilter('ANY LIKE "softshell"');
	// $search->addFilter('name LIKE "jacket"');
	// $search->addFilter('ANY !LIKE "sweater"');
	// $search->addFilter('category !LIKE [women]'); //LIKE is the 'contains' query in woo-commerce admin. not the exact match. two words can be anywhere in category		
	*/
	function buildSearchFilter($search,$num_products_per_page,$page_number){
		// $search->addFilter('merchant_id in 29129,33092');//Backcountry.com in two networks.
		$search->addFilter('source_id in 126,3,4,7,6');//5 networks
		$search->addFilter('merchant_id !=33092');
		// $search->addFilter('merchant_id in 1371');//VitaSprings.com in ShareASale network.41260 products
		$search->addFilter( 'image !EMPTY' );
		$search->addFilter('price < 99999900');
		
		foreach ($this->filter_strings as $filter_string) {
			$search->addFilter($filter_string);
		}
		foreach ($this->additional_filter_strings as $filter_string) {
			//$search->addFilter($filter_string);
		}

		$search->addFilter('price > 1000');//$10
		$search->addFilter('saleprice !=0');//otherwise no discount
		
		$search->addFilter('currency = USD');
		// $search->addFilter('salediscount !empty');
		$search->addSort('saleprice');
		//page offset and products per page:
		$product_offset=$num_products_per_page*($page_number-1);
		$search->setOffset($product_offset);
		$search->setLimit($num_products_per_page);
		$search->setFields(
		  array(
		    'name', 
		    'category',
		    'price', 
		    'merchant', 
		    'image', 
		    'description', 
		    'currency', 
		    'source_id', 
		    'merchant_id', 
		    'source', 
		    'brand', 
		    'time_updated',
		    'url',
		    'saleprice',
		    'salediscount',
		    'sku',
		    'gender'
		  )
		);
		// $search->excludeDuplicates('image');
		$search->excludeDuplicates('name | image');
		return $search;
	}

	function getProduct($page){
		/**
		 * Instantiate the DatafeedrApi Class with the following parameters:
		 * 
		 * @param string    Access ID.
		 * @param string    Secret Key.
		 * @param string    HTTP transport function. (optional) Options: curl, file or socket. Default: curl
		 * @param int       HTTP connection timeout, in seconds. (optional)
		 * @param bool      $returnObjects (optional) if TRUE, responses are objects, otherwise associative arrays.
		 *
		 */

		$api = new DatafeedrApi( 'uqzonmoujxgycslwwg9c', 'IAgLDmR00vwERMpewaALXOkvKGcpXHEFHTl0nxLGDScUGimhmgRkoILoIFc97geu' );
		$debug=true;
		$page_number=is_numeric($page)?$page:1;


		try {

			require_once 'database/dbcontroller.php';
			$db_handle=new DBController();

			$search = $api->searchRequest();

			$num_products_per_page=50;
			// $page_number=10;
			$search = $this->buildSearchFilter($search,$num_products_per_page,$page_number);
			$one_page_products = $search->execute();

			$return_count = count($one_page_products);//50
			echo '<h2>',$search->getFoundCount(), ' total products found.</h2>';
			echo 'View ',$num_products_per_page,' per page.<br>';
				$total_page_number=ceil($search->getFoundCount()/$num_products_per_page);
				// var_dump($total_page_number);//this is not accurate because $search->getFoundCount() includes the products with duplicate images or names. so if this is clothing, the actual pages of results without duplicates are around 1/4 of this total page number.
				for ($i=1; $i <= $total_page_number ; $i++) { 
					# code...
					if ($i==$page_number){
						echo "<span style='padding:4px;'>",$i,"</span>";
					}else{
						echo "<a href='preview_alice.php?cat_id=",$this->magento_category_id,"&p=",$i,"'>  ",$i,"  </a>";
					}
				}
				echo '<br>';
			if ($debug){
				$this->test_process($one_page_products);//only print the products to browser. do not insert anything into database
				return;
			}else{
				// $db_handle->runQuery("truncate mage_temp_alice");
			}
			
			for ($i=1;$i<=$total_page_number;$i++){
				print $i.'  ';
				// $i=78;
				$search = $api->searchRequest();
				$search = $this->buildSearchFilter($search,$num_products_per_page,$i);
				$one_page_products = $search->execute();
				$this->process($one_page_products,$db_handle);
				// break;
			}


			/*
			$status = $api->lastStatus();
			print "Merchant Count: " . $status['merchant_count'] . "<br />";
			print "Network Count: " . $status['network_count'] . "<br />";
			print "Product Count: " . $status['product_count'] . "<br />";
			print "Request Count: " . $status['request_count'] . "<br />";
			print "Maximum Requests: " . $status['max_requests'] . "<br />";
			print "Maximum Length: " . $status['max_length'] . "<br />";
			print '<p>Fields available for networks 3 & 4:<pre>';
			print_r ($api->getFields(array(3,4)));
			print '</pre></p>';
			*/

		} catch( Exception $err ) {
			/**
			 * Handle Errors.
			 */
			echo "ERROR:", "\n";
			echo get_class($err), "\n";
			echo $err->getCode(), "\n";
			echo $err->getMessage(), "\n";
		}
	}
	





	function init_filter_strings($strings){
		if (empty($strings)||!isset($strings)){
			var_dump($strings);
			return false;
		}
		// var_dump($strings);
		echo '<table>';
			echo '<tr>';
				echo '<td>';
					echo 'The Filter Queries for datafeedr:';
					foreach ($strings as $query) {
						echo "<div><input style='min-width: 300px;' type='text' value='",$query,"'></div>";
					}
				echo '</td>';

				echo '<td>';
					echo '<div style="color:grey;">';
						echo '<div>Note: Price and saleprice are in cents (e.g. 1500 means $15)</div>';
						echo '<div>Note: salediscount is percentage (e.g. 29 means 29%) </div>';
						echo '<div>Note: For simplicity, above does not include filter queries working for all categories: 
								<div style="padding-left:30px">
									<div>source_id in 126,3,120,4,7,6 (the 6 networks include linkshare)</div>
									<div>image !EMPTY</div>
									<div>price < 99999900 ($999,999)</div> 
									<div>price > 1000 ($10)</div>
									<div>Sort by price (low to high)</div>
							 	</div>					  
						  	  </div>';
					echo '</div>';
				echo '</td>';
			echo '</tr>';
		echo '</table>';
		if (is_null($strings)){
			echo 'category id is not in $filter_strings in config.php. Exiting...';
			$this->filter_strings=false;
		}else{
			$this->filter_strings=$strings;
		}
	}
	
	/*
	macy's : from linkshare
	US Outdoor Store: from avantlink
	backcountry: from 
	*/
	function add_pw($link){
		//http://click.linksynergy.com/link?id=@@@&offerid=206959.657107840703&type=15&murl=http%3A%2F%2Fwww1.macys.com%2Fshop%2Fproduct%2Fmotherhood-maternity-striped-bodycon-dress%3FID%3D2354669%26PartnerID%3DLINKSHARE%26cm_mmc%3DLINKSHARE-_-91-_-67-_-MP9167
		if (strpos($link, "http://click.linksynergy.com")!==false){// Linkshare
			return str_replace("@@@", "p3nsT1q9jS4", $link);
		//http://classic.avantlink.com/click.php?p=60611&pw=@@@&pt=3&pri=249235&tt=df
		}elseif(strpos($link, "http://classic.avantlink.com/")!==false){// avantlink
			return str_replace("@@@", "186458", $link);
		//http://www.tkqlhce.com/click-@@@-10279061?url=http%3A%2F%2Fwww.backcountry.com%2Froxy-like-its-hot-dress-womens%3FCMP_SKU%3DQKS01M2%26MER%3D0406%26utm_source%3DCJ%26utm_source%3DAffiliate%26mr%3AtrackingCode%3D0973E92C-C8F0-E411-BDDA-BC305BF82376%26mr%3AreferralID%3DNA
		}elseif(strpos($link, "http://www.tkqlhce.com/")!==false){//commission junction
			return str_replace("@@@", "4623576", $link);
		//http://www.anrdoezrs.net/click-@@@-10279061?url=http%3A%2F%2Fwww.backcountry.com%2Froxy-diamond-tank-dress-womens%3FCMP_SKU%3DQKS01KU%26MER%3D0406%26utm_source%3DCJ%26utm_source%3DAffiliate%26mr%3AtrackingCode%3DE5C2B615-F390-E411-9BFE-BC305BF82376%26mr%3AreferralID%3DNA
		}elseif(strpos($link, "utm_source")!==false){//general commission junction
			return str_replace("@@@", "4623576", $link);
		}else{
			return $link;
		}
	}
	function test_process($products)
	{
		// var_dump($products);
		foreach($products as $product) {
			  print "<img src=\"" . $product['image'] . "\" align='left' height='120' />\n";
			  print "<a target='_new' href=\"" . $this->add_pw($product['url']) . "\">" . $product['name'] . "</a><br />";
			  
			  echo '<button style="float:right;" class="exclude_button">Exclude</button>';
			  echo '<span style="display:block">'.$product['sku'].'</span>';

			  // print number_format(($product['price']/100), 2) . " " . $product['currency'] . "<br />";
			  echo '<span style="text-decoration: line-through;">',number_format(($product['price']/100), 2),'</span>';
			  echo '<span style="color:red">',number_format(($product['saleprice']/100), 2),'</span><br />';
			  echo '<span style="color:green">',$product['salediscount'], "% OFF",'</span><br>';
			  echo "<span style='font-weight:bold;color:orange;'>",$product['merchant'] , "</span><br /><br />";
			  echo "<div>",$product['merchant_id'],"</div>";
			  echo $product['description'] , "<br /><br />";
			  // var_dump( $product['category'] );
			  echo '<span style="color:#F40;">"', $product['category'] ,'"</span>';
			  print "<hr />";
		}
	}

	function write_log($object)
	{  
	 	error_log($object."\n", 3, 'here.csv');
	    return true;
	}

}




echo '<h4>Example queries:</h4>';
echo '<div>';
echo "merchant_id in 29129<br>
category LIKE Women<br>
name LIKE Yoga<br>
price > 5500";
echo '</div>';
echo '<hr>';

echo "<br><h3>Please enter query below: </h3>";
echo "<br><br>";
	// var_dump($_POST['filters']);

//--------display 5 text boxes to enter querys------------
echo "<form  method='post'>";
$count_text_box=0;
foreach ($_POST['filters'] as $filter) {			
	echo '<input type="text" name="filters[]" value="'.$filter.'"><br>';
	$count_text_box++;
}
while($count_text_box<5){
	echo '<input type="text" name="filters[]" value=""><br>';
	$count_text_box++;
}
echo '<input type="submit" name="submit" value="Submit">';
echo "</form>";
echo '<hr>';

//----------------process if submitted--------------
if (isset($_POST['submit'])&&isset($_POST['filters'])){
	// var_dump($_POST['filters']);
	$filter_strings=array();
	foreach($_POST['filters'] as $query){
		if (!empty($query)){
			$filter_strings[]=$query;
		}
	}
	if (!empty($filter_strings)){		
		$datafeedr_preview=new datafeedr_preview($filter_strings);
		if (!is_numeric($_POST['p'])){
			$_POST['p']=1;
		}
		$datafeedr_preview->getProduct($_POST['p']);
	}
}





?>




<script src="http://code.jquery.com/jquery-1.12.1.min.js" type="text/javascript"></script>
<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>

<script type="text/javascript">
$(document).ready(function() {
	$(".exclude_button").click(function(){
		var element=this;
		$.ajax({
	   		url: "exclude_api.php",
	   		type:"GET",
	   		dataType: 'json', // jQuery will parse the response as JSON
	   		data: {"cat_id":$("#cat_id").html(),"sku":$(element).next('span').html(),"action":"exclude"},
	    	success: function(result){
	    		console.log(result);
	    		if (result['status']==false){
	    			// $(element).css('background-color', 'rgb(255, 152, 145)');//red
	    			alert(result['error_details']);
	    		}else{
	    			// $(element).css('background-color', '#D8FFDA');//green
	    		}
	    	},
	    	error: function(jqXHR, textStatus, errorThrown) {
        		// report error
    			console.log('ajax request failed: pls check your php file ');
    			var error = jQuery.parseJSON(jqXHR.responseText);
				alert(error.errors.message);
				// $(element).css('background-color', 'rgb(255, 152, 145)');//red
    		}
		}); 
	});
});
</script>