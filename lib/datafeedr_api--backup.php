<?php
// http://ems.1661hk.com/wp-content/plugins/datafeedr-api/libraries/full_example.php
// http://ems.1661hk.com/productExport-test/datafeedr_api.php
// https://v4.datafeedr.com/documentation/136
/**
 * Require the Datafeedr Api Client Library.
 */


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
class datafeedr_api{
	public $debug='';
	// public $page_number='';
	public $num_products_per_page='';
	public $DatafeedrApi='';
	public $db_handle='';
	public $magento_categories='';
	public $filter_strings='';
	function __construct($magento_categories,$filter_strings,$debug=false,$num_products_per_page=50) {
		$this->magento_categories=$magento_categories;
		$this->filter_strings=$filter_strings;
		//default to debug=true if param is not boolean:
		$this->debug=($debug==true|| $debug==false)?$debug:true;
		//default to 1 if param is not numeric:
		// $this->page_number=is_numeric($page_number)?$page_number:1;
		//default to 50 if param is not numeric:
		$this->num_products_per_page=is_numeric($num_products_per_page)?$num_products_per_page:50;
		
		require_once 'database/dbcontroller.php';
		$this->db_handle=new DBController();
		var_dump("here");
		// require '/usr/share/nginx/www/ems.1661hk.com/wp-content/plugins/datafeedr-api/libraries/datafeedr.php';
		require '/usr/share/nginx/www/ipzmall.com/wp/wp-content/plugins/datafeedr-api/libraries/datafeedr.php';

		$this->DatafeedrApi=new DatafeedrApi( 'uqzonmoujxgycslwwg9c', 'IAgLDmR00vwERMpewaALXOkvKGcpXHEFHTl0nxLGDScUGimhmgRkoILoIFc97geu' );
		
	}

	/**
	 * Trigger exception in a "try" block.
	 */

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
		
	*/
	protected function buildSearchFilter($page_number){
		$search = $this->DatafeedrApi->searchRequest();
		// $search->addFilter('merchant_id in 29129,33092');//Backcountry.com in two networks.
		// $search->addFilter('source_id in 126,127,3,4,7,9,120,124');//8 networks
// $search->addFilter('source_id in 126,3,4,7,9');//5 networks--working for 1st and 2nd time import
		$search->addFilter('source_id in 126,3,120,4,7,6');
		$search->addFilter('merchant_id !=33092');
		// $search->addFilter('merchant_id in 1371');//VitaSprings.com in ShareASale network.41260 products
		$search->addFilter( 'image !EMPTY' );
		$search->addFilter('price < 99999900');
		$search->addFilter('saleprice !=0');//otherwise no discount
		$search->addFilter('sku !EMPTY');//otherwise no sku
		foreach ($this->filter_strings as $filter_string) {
			$search->addFilter($filter_string);
		}
		// $search->addFilter('category !EMPTY');
		$search->addFilter('currency = USD');//not working for VitaSprings.com
		// $search->addSort('-price');
		$search->addSort('price');
		// $search->setOffset(20);
		// $search->setLimit(10);
		$product_offset=$this->num_products_per_page*($page_number-1);
		$search->setOffset($product_offset);
		$search->setLimit($this->num_products_per_page);
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
		    'salediscount',
		    'saleprice',
		    'onsale',
		    'sku',
		    'gender'
		  )
		);
		// $search->excludeDuplicates('image');
		$search->excludeDuplicates('name|image|description');
		return $search;
	}
	/*
		Debug is false. insert all query results to step_1_datafeedr_results table
	*/
	public function insertProducts(){
		if ($this->debug===true) return;
		try {
			// $page_number=1;
			$search = $this->buildSearchFilter(1);
			$one_page_products = $search->execute();

			$return_count = count($one_page_products);
			print '<h2>'.$return_count . ' of ' . $search->getFoundCount() . ' total products found.</h2>';
				// var_dump($search->getFoundCount());
				$total_page_number=ceil($search->getFoundCount()/$this->num_products_per_page);
				// var_dump($total_page_number);//this is not accurate because $search->getFoundCount() includes the products with same image, name, or descirption. so if this is clothing, the actual pages of results without duplicates are around 1/4 of this total page number.
				$max_num_products_needed=200;
				$page_number_limit=$max_num_products_needed/$this->num_products_per_page;//process 4 pages maximum

				$total_page_number=min($total_page_number,$page_number_limit);

				require 'config.php';
				$this->db_handle->runQuery("truncate {$mysql_table_step_1_datafeedr_results}");
			
				for ($i=1;$i<=$total_page_number;$i++){
					print $i.'  ';
					// $i=78;
					$search = $this->buildSearchFilter($i);
					$one_page_products = $search->execute();
					$this->insert_one_page_products($one_page_products);
					break;
				}
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
	/*
		returns all products on this page. consider the $debug and $page_number, and the filter info. 
	*/
	public function printProducts($page_number){
		if ($this->debug===false) return;	
		try {
			// $page_number=1;
			// var_dump($page_number);
			$search = $this->buildSearchFilter($page_number);
			$one_page_products = $search->execute();

			$return_count = count($one_page_products);
			print '<h2>'.$return_count . ' of ' . $search->getFoundCount() . ' total products found.</h2>';
				// var_dump($search->getFoundCount());
				$total_page_number=ceil($search->getFoundCount()/$this->num_products_per_page);
				// var_dump($total_page_number);//this is not accurate because $search->getFoundCount() includes the products with duplicate images or names. so if this is clothing, the actual pages of results without duplicates are around 1/4 of this total page number.
				
				$page_number_limit=200/$this->num_products_per_page;//4 pages
				if ($total_page_number>$page_number_limit){
					$total_page_number=$page_number_limit;
				}


				for ($i=1; $i <= $total_page_number ; $i++) { 
					# code...
					echo "<a href='index.php?p=".$i."'>  ".$i."  </a>";
				}
				echo '<br>';
				$this->print_one_page_products($one_page_products);//only print the products to browser. do not insert anything into database
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
	/**
	* as opposed to print
	*/
	function insert_one_page_products($products)
	{
		if ($this->debug===true){return;}
		// var_dump(count($products));
		foreach($products as $product) {
			$this->insert_to_database($product);
		}
	}
	/**
	* as opposed to insert
	*/
	function print_one_page_products($products)
	{	
		if ($this->debug===false){return;}
		// var_dump(count($products));
		/*foreach($products as $product) {
			print "<img src=\"" . $product['image'] . "\" align='left' height='60' />\n";
			print "<a href=\"" . $product['url'] . "\">" . $product['name'] . "</a><br />";
			print number_format(($product['price']/100), 2) . " " . $product['currency'] . "<br />";
			if (!is_null($product['saleprice'])){
				echo "<div style='color:red'>".number_format(($product['saleprice']/100), 2)."</div>";
				echo "<div style='color:red'>".$product['salediscount'].' percent off. </div>';
				// echo "<div style='color:green'>".$product['url']."</div>";
			}
			print $product['merchant'] . "<br />";
			print $product['description'] . "<br />";
			var_dump( $product['category'] );

			// echo '<pre>';
			// var_dump($product);
			// echo '</pre>';
			print "<hr />";
		}*/
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
			  echo $product['description'] , "<br /><br />";
			  // var_dump( $product['category'] );
			  echo '<span style="color:#F40;">"', $product['category'] ,'"</span>';
			  print "<hr />";
		}
	}



	// function test_process($products)
	// {
	// 	// var_dump($products);
	// 	foreach($products as $product) {
	// 		  print "<img src=\"" . $product['image'] . "\" align='left' height='60' />\n";
	// 		  print "<a href=\"" . $product['url'] . "\">" . $product['name'] . "</a><br />";
	// 		  print number_format(($product['price']/100), 2) . " " . $product['currency'] . "<br />";
	// 		  print $product['merchant'] . "<br />";
	// 		  print $product['description'] . "<br />";
	// 		  var_dump( $product['category'] );
	// 		  print "<hr />";
	// 	}
	// }

	function write_log($object)
	{  
	 	error_log($object."\n", 3, 'here.csv');
	    return true;
	}

	function getDirectUrl($product_url_raw)
	{
		$product_url=null;
	    //If url= is found in stirng, then product url exists in the Raw Url
	    if (strpos($product_url_raw, 'url=') !== false) {
			$pos1 = strpos($product_url_raw, "http%");
			$pos2 = strpos($product_url_raw, "%26") - $pos1;
			$product_url = substr($product_url_raw, $pos1, $pos2);
		}else{
			$product_url = $product_url_raw;
		}
		return $product_url;
	}
	protected function insert_to_database($product){
		echo "\n--------datafeedr_api.php: insert_to_database() function--------\n";
		// var_dump($product);
		require 'config.php';
		$sku=$product['sku'];
		$image_url=$product['image'];
		$price=number_format(($product['price']/100), 2);
		$saleprice=number_format(($product['saleprice']/100), 2);
		$saleprice=isset($product['saleprice'])?$saleprice:$regular_price;
		$brand=$product['brand'];
		$datafeedr_buy_link=$this->add_pw($product['url']);
		$product_name=$product['name'];
		$product_name_raw=$product_name;
		if ($product["merchant"]=="Backcountry.com"){
			//------remove size (e.g. , XL) after backcountry product name------
			$product_name = strpos($product_name, ",") ? substr($product_name, 0, strpos($product_name, ",")) : $product_name;
			$product_name = str_replace('"', '\"', $product_name);
			//-------remove "-XL" from backcountry skus:------------------------
			$possible_size=strpos($product_name_raw, ",") ? substr($product_name_raw, strpos($product_name_raw, ", ")+2, strlen($product_name_raw)) : '';//e.g. "XL","L"
			if (!empty($possible_size)){
				//check if the sku has trailing possible size. 
				$position=strpos($sku,"-".$possible_size);
				// var_dump($position);//10
				// var_dump(strlen($possible_size));//2
					// var_dump(strlen($sku));//13
				if ($position!==false && ($position+strlen($possible_size)+1==strlen($sku))){//remove the trailing -XL in sku
					$sku=substr($sku, 0, $position);
					// var_dump($sku);
				}
			}
			//-----------------------------------------------------------------
		}
		$product_desc=$product['description'];
		$datafeedr_merchant=$product['merchant'];
		$url_for_scrapping=urldecode($this->getDirectUrl($datafeedr_buy_link));


		// $magento_categories="249,338,644,598";
		// $magento_categories="80,361,597";//drink mix
		// $magento_categories="80,356,597";//bars
		// $magento_categories="80,645,597";//gels
		$magento_categories=$this->magento_categories;
		

		$insert_query="insert into {$mysql_table_step_1_datafeedr_results} (sku,image_url,price,saleprice,brand,datafeedr_buy_link,product_name,product_desc,cat_id,datafeedr_merchant,url_for_scrapping) values (\"$sku\",\"$image_url\",\"$price\",\"$saleprice\",\"$brand\",\"$datafeedr_buy_link\",\"$product_name\",\"$product_desc\",\"$magento_categories\",\"$datafeedr_merchant\",\"$url_for_scrapping\")";
		echo "\n$insert_query\n";
		$result=$this->db_handle->runQuery($insert_query);
		// var_dump($db_handle->getError());
		// $result=$db_handle->runQuery("select * from mage_categories;");
		// var_dump($result);
	}
	/*
	macy's : from linkshare
	US Outdoor Store: from avantlink
	backcountry: from 
	*/
	protected function add_pw($link){
		//http://click.linksynergy.com/link?id=@@@&offerid=206959.657107840703&type=15&murl=http%3A%2F%2Fwww1.macys.com%2Fshop%2Fproduct%2Fmotherhood-maternity-striped-bodycon-dress%3FID%3D2354669%26PartnerID%3DLINKSHARE%26cm_mmc%3DLINKSHARE-_-91-_-67-_-MP9167
		if (strpos($link, "http://click.linksynergy.com")!==false){// Linkshare
			return str_replace("@@@", "p3nsT1q9jS4", $link);
		//http://classic.avantlink.com/click.php?p=60611&pw=@@@&pt=3&pri=249235&tt=df
		}elseif(strpos($link, "http://classic.avantlink.com/")!==false){// avantlink
			return str_replace("@@@", "186458", $link);
		//http://www.tkqlhce.com/click-@@@-10279061?url=http%3A%2F%2Fwww.backcountry.com%2Froxy-like-its-hot-dress-womens%3FCMP_SKU%3DQKS01M2%26MER%3D0406%26utm_source%3DCJ%26utm_source%3DAffiliate%26mr%3AtrackingCode%3D0973E92C-C8F0-E411-BDDA-BC305BF82376%26mr%3AreferralID%3DNA
		// }elseif(strpos($link, "http://www.tkqlhce.com/")!==false){//commission junction
		// 	return str_replace("@@@", "4623576", $link);
		//http://www.anrdoezrs.net/click-@@@-10279061?url=http%3A%2F%2Fwww.backcountry.com%2Froxy-diamond-tank-dress-womens%3FCMP_SKU%3DQKS01KU%26MER%3D0406%26utm_source%3DCJ%26utm_source%3DAffiliate%26mr%3AtrackingCode%3DE5C2B615-F390-E411-9BFE-BC305BF82376%26mr%3AreferralID%3DNA
		}elseif(strpos($link, "utm_source")!==false){//general commission junction
			return str_replace("@@@", "4623576", $link);
		}else{
			return $link;
		}
	}

}


?>