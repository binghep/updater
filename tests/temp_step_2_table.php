<?php 
class generate_csv_from_step2_table{
	public $cat_name;
	public $fp;
	function __construct($cat_name){
		echo "\n---instantiating generate_csv_from_step2_table class---\n";
		if (empty($cat_name)){
	        echo "\n |---Not valid param. Exiting... \n";
	        return false;
	    }else{
	    	$this->cat_name=$cat_name;
		    echo "\r\n----instantiated generate_csv_from_step2_table with $cat_name----\r\n";
	    }
	    
     //    @$link = mysql_connect($mysql_host,$mysql_user,$mysql_pw); 
	    // if (!$link) { 
	    //     // die('Could not connect to MySQL: ' . mysql_error()); 
	    // 	echo 'connection error.<br>';
	    // 	return -1;
	    // } 
	    // mysql_close($link); 
	    return true;
	}
	//carry out the export process:
	public function run(){
		$csv_name=$this->cat_name;
	    $csv_name=str_replace(" ", "_", $csv_name);
	    $csv_name=str_replace("&", "_", $csv_name);
	    echo '--------';
	    require __DIR__.'/../config.php';
	    $output_csv_name =$this->exportProductsConf($csv_name);
	    return $output_csv_name;
	}
	
	//Writes a row to csv
	function log($msg){
		//$this->fp = fopen('magmi_csv/1.csv', 'a');//append
		fwrite($this->fp, $msg."\n");
		//fclose($this->fp);
	}

	// function write_log($object)
	// {  
	//     require 'config.php';
	//     // error_log($object."\r\n", 3, 'drink_mix.csv');
	//  	error_log($object."\r\n", 3, $output_csv_name);
	//     return true;
	// }
	// write_log(""\""admin,cn,en\"",\""$sku\"",\""-$small_image\"""");


	//Pull sizes from Merchant
	//param is already decoded.
	//var_dump($productUrl);  http://www.backcountry.com/nuun-all-day-8-pack?CMP_SKU=NUU0015
	// function getBackCountryDataArray($productUrl){
		
	//     require_once("lib/getBackcountryProductSize_api.php");
	//     $backcountry_scrapper=new backcountry_scrapper($productUrl);
	//     $data =$backcountry_scrapper->getData();
	//     // var_dump($data);
	//     return $data;
	// }
	function removeCommaInPrice($price){
	    $new_price=str_replace(",", "", $price);
	    return $new_price;
	}
	//Pulls products from $mysql_table_save_datafeedr to input into CSV
	function exportProductsConf($category_name){
		echo "\n----------exportProductsConf() function----------\n";
	    require_once 'database/dbcontroller.php';
	    $db_handle=new DBController();  

		require 'config.php';
        $query = "select * from ".$mysql_table_step_2_scrapped_results;
	    $result=$db_handle->runQuery($query);
	    // var_dump(count($result));
	    echo "\n-----step_2 table has ".count($result)." rows-----\n";
	    if (is_null($result)){
	        echo "\ntable is empty. error. exiting.\n";
	        return false;
	    }

	    $arrayFull = array();
	    $arrayUnique = array();
	    $file_name="";
	    if (!is_null($result) && count($result) >0) {
	        date_default_timezone_set('America/Los_Angeles');
	        $tdate = date("Y-m-d_h-i-s");

	        $file_name=$category_name.'_'.$tdate.'.csv';
	        $file_path='../magmi_csv/alice_import/'.$file_name;
	    
	        $this->fp = fopen($file_path, 'w');//append to file in magento root.
	        //create csv first line header.
	        $which="Size:drop_down:1:1";
	        // $which="style_and_size";
	        $this->log("store,type,sku,$which,weight,name,category_ids,brand,image_external_url,small_image_external_url,thumbnail_external_url,use_external_images,special_price,price,short_description,description,tax_class_id,attribute_set,visibility,status,qty,is_datafeedr_product,datafeedr_merchant,datafeedr_buy_link,url_for_scrapping");
	        // write_log("store,type,sku,$which,weight,configurable_attributes,name,category_ids,brands,image_external_url,small_image_external_url,thumbnail_external_url,use_external_images,special_price,price,short_description,description,tax_class_id,attribute_set,visibility,status,manage_stock");
	        
	        $debug=false;
	        $csv_row_counter=0;
	        $num_csv_counter=1;

	        foreach ($result as $key => $one_row) {
	            //------this chunk will not function because we are importing 200 product per category at maximum.------- 
	            // if ($csv_row_counter>1250){
	            //     $csv_row_counter=0;
	            //     fclose($this->fp);
	            //     $filename='magmi_csv/'.$category_name.'_'.++$num_csv_counter.'.csv';
	            //     $this->fp = fopen($filename, 'w');//append to file in magento root.
	            //     log2($this->fp,"store,type,sku,$which,weight,configurable_attributes,name,category_ids,brand,image_external_url,small_image_external_url,thumbnail_external_url,use_external_images,special_price,price,short_description,description,tax_class_id,attribute_set,visibility,status,manage_stock");
	            // }
	            //---------------------------------------------------------------------------------------	           
	            $scrapped_attributes_string=$one_row['scrapped_attributes'];
	            $scrapped_attributes=json_decode($scrapped_attributes_string);
	            echo "\n---fetched scrapped_attributes from database---<br>\n";
	            // var_dump($scrapped_attributes);
	            // $conf_sku=$scrapped_attributes->conf_sku;
	            if (empty($scrapped_attributes) || is_null($scrapped_attributes->conf_sku)){
	            	echo "\n---error. json_decoded scrapped_attributes is empty. skipping...<br>\n";
	            	continue;
	            }
	            //------passed validation------------
            	$this->ouput_all_rows_for_one_conf($one_row,$scrapped_attributes);
	            if ($debug) {break;}
			}
		}
	    return $file_name;
	}
	
	function ouput_all_rows_for_one_conf($one_row,$scrapped_attributes){
		echo "\n|-----------ouput_all_rows_for_one_conf() function----------\n";
		$conf_sku=$scrapped_attributes->conf_sku;
		$child_attributes=$scrapped_attributes->child_attributes;
		$array=get_object_vars($child_attributes);//convert object first level attributes into array. key(string)=>value(still object);
		// var_dump($array);//see below example output
		/*
array(3) {
  ["S"]=>
  object(stdClass)#159 (3) {
    ["price"]=>
    float(24.95)
    ["saleprice"]=>
    float(11.23)
    ["inventory"]=>
    int(3)
  }
  ["L"]=>
  object(stdClass)#158 (3) {
    ["price"]=>
    float(24.95)
    ["saleprice"]=>
    float(11.23)
    ["inventory"]=>
    int(1)
  }
  ["M"]=>
  object(stdClass)#157 (3) {
    ["price"]=>
    float(24.95)
    ["saleprice"]=>
    float(11.23)
    ["inventory"]=>
    int(3)
  }
}
		*/
		
		$sizes=array_keys($array);
		$conf_name=$one_row["product_name"];

        $this->outputCsvRowConf($one_row,$sizes, $conf_name, $one_row['price'],$one_row['saleprice']);
        // $csv_row_counter++;
	}

	  
	//formats a row to be inserted as a Configurable Product
	function outputCsvRowConf($assocArray,$sizes,$conf_name,$price,$saleprice){
		echo "\n   |-----------outputCsvRowConf_Jon_Style() function----------\n";
	    // $product_url_raw=$assocArray['product_url'];
	    // $product_url=getUrl($product_url_raw);
		require 'config.php';
	    $one_row = $assocArray;
	    $conf_sku=$one_row['sku'];
	    $categories=$one_row['cat_id'];
	    // $categories="249,338,640";
	    $brandRaw=$one_row['brand'];
	    $brand = $this->titleCase($brandRaw);
	        $image_url=$one_row['image_url'];
	    $desc=$one_row['product_desc'];

	    $saleprice=$this->removeCommaInPrice($saleprice);
	    $price=$this->removeCommaInPrice($price);

	    $brand = str_replace('"', '""', $brand);
	    $desc = str_replace('"', '""', $desc);

	    $size = '';
	  	//Check if image url is NULL
	    if (is_null($image_url)||empty($image_url)){
	    	echo 'error image url is null';
	    	return;
	    }
	    // $size_or_style_and_size="size";

	    $datafeedr_merchant=$assocArray['datafeedr_merchant'];
	    $datafeedr_buy_link=$assocArray['datafeedr_buy_link'];
	    $url_for_scrapping=$assocArray['url_for_scrapping'];

	    $custom_options='';
	    foreach ($sizes as $size) {
	    	//format it, remove any char not numeric or alphabetic
	    	$custom_options.="{$s}"
	    }

	    $log_row="admin,configurable,\"{$conf_sku}\",{$custom_options},{$weight},\"{$conf_name}\",\"{$categories}\",{$brand},{$image_url},{$image_url},{$image_url},1,{$saleprice},{$price},\"{$desc}\",\"{$desc}\",0,Demo Attribute Set,4,1,5000,1,{$datafeedr_merchant},{$datafeedr_buy_link},{$url_for_scrapping}";//stock not important for conf
	 	// var_dump($log_row);
	    // write_log($log_row);
	    $this->log($log_row);
	}

	//formats brand name - Capitalize first letter of each word, excludes capitalization of words listed in the array
	function titleCase($string, $delimiters = array(" ", "-"), $exceptions = array("and", "to", "of", "by")){
	    /*
	     * Exceptions in lower case are words you don't want converted
	     * Exceptions all in upper case are any words you don't want converted to title case
	     *   but should be converted to upper case, e.g.:
	     *   king henry viii or king henry Viii should be King Henry VIII
	     */
	    $string = mb_convert_case($string, MB_CASE_TITLE, "UTF-8");
	    foreach ($delimiters as $dlnr => $delimiter) {
	        $words = explode($delimiter, $string);
	        $newwords = array();
	        foreach ($words as $wordnr => $word) {
	            if (in_array(mb_strtoupper($word, "UTF-8"), $exceptions)) {
	                // check exceptions list for any words that should be in upper case
	                $word = mb_strtoupper($word, "UTF-8");
	            } elseif (in_array(mb_strtolower($word, "UTF-8"), $exceptions)) {
	                // check exceptions list for any words that should be in upper case
	                $word = mb_strtolower($word, "UTF-8");
	            } elseif (!in_array($word, $exceptions)) {
	                // convert to uppercase (non-utf8 only)
	                $word = ucfirst($word);
	            }
	            array_push($newwords, $word);
	        }
	        $string = join($delimiter, $newwords);
	   }//foreach
	   return $string;
	}
}
?> 



<?php
