<?php 
class generate_csv_from_step2_table{
	public $cat_name;
	public $fp;
	public $error;
	function __construct($cat_name){
		echo "\n---instantiating generate_csv_from_step2_table class---\n";
		if (empty($cat_name)){
	        echo "\n |---Not valid param. Exiting... \n";
	        // return false;
	        $this->error="Not valid param";
	    }else{
	    	$this->cat_name=$cat_name;
		    echo "\r\n----instantiated generate_csv_from_step2_table with $cat_name----\r\n";
	    }
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

	function removeCommaInPrice($price){
	    $new_price=str_replace(",", "", $price);
	    return $new_price;
	}
	//Pulls products from $mysql_table_save_datafeedr to input into CSV
	function exportProductsConf($category_name){
		echo "\n----------exportProductsConf() function----------\n";
	    require_once __DIR__.'/../database/dbcontroller.php';
	    $db_handle=new DBController();  

		require __DIR__.'/../config.php';
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
	        $file_path=__DIR__.'/../../magmi_csv/alice_import/'.$file_name;
	    
	        $this->fp = fopen($file_path, 'w');//append to file in magento root.
	        //create csv first line header.
	        $which="Size:drop_down:1:1";
	        // $which="style_and_size";
	        $this->log("store,type,sku,$which,weight,sizes,name,category_ids,brand,image_external_url,small_image_external_url,thumbnail_external_url,use_external_images,special_price,price,short_description,description,tax_class_id,attribute_set,visibility,status,qty,is_datafeedr_product,datafeedr_merchant,datafeedr_buy_link,url_for_scrapping");
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
            	$this->output_one_row_for_one_conf($one_row,$scrapped_attributes);
	            if ($debug) {break;}
			}
		}
	    return $file_name;
	}


	/**
	 * Param: $sizes contains all size of one product in one array
	 * Returns matching set array if matches one sequence set, return NULL if not matching any sets.
	 * e.g. product contains XS,S as sizes, return array("XS"=>0,"S"=>1,"M"=>2,"L"=>3,"XL"=>4)
	 */ 
	function get_matching_size_set($all_size_sets,$product_size_array){
		foreach ($all_size_sets as $set) {
			$is_in_set=true;
			foreach ($product_size_array as $size) {
				if (!array_key_exists($size, $set)){
					// echo $size," is not in ",implode(",", $set);
					$is_in_set=false;
					break;
				}
			}
			if ($is_in_set){
				echo 'is in one set';
				return $set;
			}
		}
	}
	/**
	* STYLE_SIMPLE_WITH_CUSTOM_ATTRIBUTES
	*/
	function output_one_row_for_one_conf($one_row,$scrapped_attributes){
		echo "\n|-----------output_one_row_for_one_conf() function----------\n";
		$conf_sku=$scrapped_attributes->conf_sku;
		$child_attributes=$scrapped_attributes->child_attributes;
		$array=get_object_vars($child_attributes);//convert object first level 
		$sizes=array_keys($array);
		// $conf_name=;
		$custom_option="";
		$i=0;
		$base_price=null;
		$base_saleprice=null;
		//-------------use the first size's price ans saleprice as base product price and saleprice------
		foreach ($sizes as $size) {
			$data=$array[$size];
			$data=get_object_vars($data);
			$base_price=$data['price'];
			$base_saleprice=$data['saleprice'];
			break;
		}
		$sizes_for_filter=array();
		//-----------add sort if sizes from one product all belong to one of following sets---------------
		$all_size_sets=array(
					array(	
							"XX-Small"=>0,
							"X-Small"=>1,
							"Small"=>2,
							"Medium"=>3,
							"Large"=>4,
							"X-Large"=>5,
							"XX-Large"=>6,

							"XX-Small P"=>7,
							"X-Small P"=>8,
							"Small P"=>9,
							"Medium P"=>10,
							"Large P"=>11,
							"X-Large P"=>12,
							"XX_Large P"=>13,
							"Petite P"=>14,

							"1X"=>15,
							"2X"=>16,
							"3X"=>17,
							),//set index: 0 //nordstrom

					array(  "XS"=>0,
							"S"=>1,
							"M"=>2,
							"L"=>3,
							"XL"=>4,
							"XXL"=>5,
							"3XL"=>6,
							),//set index: 1 //backcountry
					array("00"=>1,
						"0"=>2,
						"2"=>3,
						"4"=>4,
						"6"=>5,
						"8"=>6,
						"10"=>7,
						"12"=>8,
						"14"=>9,
						"16"=>10,
						"18"=>11,

						"00P"=>12,
						"0P"=>13,
						"2P"=>14,
						"4P"=>15,
						"6P"=>16,
						"8P"=>17,
						"10P"=>18,
						"12P"=>19,
						"14P"=>20,
						"16P"=>21,
						"18P"=>22,

						"12W"=>23,
						"14W"=>24,
						"16W"=>25,
						"18W"=>26,
						"20W"=>27,
						"22W"=>28,
						"24W"=>29,

						"14WP"=>30,
						"16WP"=>31,
						"18WP"=>32,
						"20WP"=>33,
						"22WP"=>34,
						"24WP"=>35,  
						)//nordstrom
					,
					array(
						"US 2/UK 6"=>1,
						"US 4/UK 8"=>2,
						"US 6/UK 10"=>3,
						"US 8/UK 12"=>4,
						"US 10/UK 14"=>5,
						"US 12/UK 16"=>6,
						"US 14/UK 18"=>7,
						"US 16/UK 20"=>8,
						)
				  );
		$matching_set=$this->get_matching_size_set($all_size_sets,$sizes);
		//----------------------build size custom option column---------------------------------
		foreach ($sizes as $size) {
			$data=$array[$size];
			$data=get_object_vars($data);//convert stdClass Object to array
			//---add inventory for scrapped json(scrapped_attributes) that does not contain inventory.----
			if (is_null($data["inventory"])){
				$data["inventory"]=20;
			}
			//----------------------------------------------------
        	// $this->outputCsvRowSimple($one_row,$size,$data["inventory"],$data["price"],$data["saleprice"],$conf_name);
			if (!empty($custom_option)){
				$custom_option.="|";
			}
			$saleprice_offset=$data['saleprice']-$base_saleprice;
			
			$size=preg_replace("/[^A-Za-z0-9 .\-\/]/", "", $size);//only allow alphanumeric characters  .-\ and space
			$size=trim($size);
			//--------------------------------------------------
			array_push($sizes_for_filter, $size);
			//--------------------------------------------------
			if ($matching_set!==null){
				// var_dump($sizes_for_filter);
				// var_dump($matching_set);
				$i=$matching_set[$size];//XS is 0 for example.
			}
			$custom_option.="{$size}:fixed:{$saleprice_offset}::{$i}";
			$i++;
    	}
    	$sizes_for_filter=implode(",", $sizes_for_filter);

        //============now process and output=====================
	    $conf_sku=$one_row['sku'];
	    $categories=$one_row['cat_id'];
	    // $categories="249,338,640";
	    $brand = $this->titleCase($one_row['brand']);
	        $image_url=$one_row['image_url'];
	    $desc=$one_row['product_desc'];
	    $brand = str_replace('"', '""', $brand);
	    $desc = str_replace('"', '""', $desc);

	    $saleprice=$this->removeCommaInPrice($one_row['saleprice']);
	    $price=$this->removeCommaInPrice($one_row['price']);

	    // $size = '';
	  	//Check if image url is NULL
	    if (is_null($image_url)||empty($image_url)){
	    	echo 'error image url is null';
	    	return;
	    }

	    // $datafeedr_merchant=;
	    // $datafeedr_buy_link=;
	    // $url_for_scrapping=;

	    require __DIR__.'/../config.php';//$weight
	    $log_row="admin,simple,\"{$conf_sku}\",{$custom_option},{$weight},\"{$sizes_for_filter}\",\"{$one_row["product_name"]}\",\"{$categories}\",{$brand},{$image_url},{$image_url},{$image_url},1,{$base_saleprice},{$base_price},\"{$desc}\",\"{$desc}\",2,Demo Attribute Set,4,1,5000,1,{$one_row['datafeedr_merchant']},{$one_row['datafeedr_buy_link']},{$one_row['url_for_scrapping']}";//stock not important for conf
	 	// var_dump($log_row);
	    // write_log($log_row);
	    $this->log($log_row);
	}

	/**
	* STYLE_CONF
	*/
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
		foreach ($sizes as $size) {
			$data=$array[$size];
			$data=get_object_vars($data);//convert stdClass Object to array
			//---add inventory for scrapped json(scrapped_attributes) that does not contain inventory.----
			if (is_null($data["inventory"])){
				$data["inventory"]=20;
			}
			//----------------------------------------------------
        	$this->outputCsvRowSimple($one_row,$size,$data["inventory"],$data["price"],$data["saleprice"],$conf_name);
            // $csv_row_counter++;
    	}
        $this->outputCsvRowConf($one_row, $conf_name, $one_row['price'],$one_row['saleprice']);
        // $csv_row_counter++;
	}
	// function getUrl($product_url_raw)
	// {
	// 	$product_url=null;
	//     //If url= is found in stirng, then product url exists in the Raw Url
	//     if (strpos($product_url_raw, 'url=') !== false) {
	// 		$pos1 = strpos($product_url_raw, "http%");
	// 		$pos2 = strpos($product_url_raw, "%26") - $pos1;
	// 		$product_url = substr($product_url_raw, $pos1, $pos2);
	// 	}else{
	// 		$product_url = $product_url_raw;
	// 	}
	// 	return $product_url;
	// }


	//formats a row to be inserted as a Simple Product
	function outputCsvRowSimple($assocArray, $size, $inventory, $price, $saleprice){
		echo "\n   |-----------outputCsvRowSimple() function----------\n";
		$conf_name=$assocArray["product_name"];
		$conf_sku=$assocArray["sku"];
	    $name=$conf_name . ", " . $size;
	    $categories=$assocArray['cat_id'];
	    // $categories="249,338,640";
	    $brandRaw=trim($assocArray['brand']);
	    $brand = $this->titleCase($brandRaw);

	       $image_url=$assocArray['image_url'];
	    $saleprice=$this->removeCommaInPrice($saleprice);
	    $price=$this->removeCommaInPrice($price);
	    $product_desc=$assocArray['product_desc'];
	   
	    $name = str_replace('"', '\"', $name);
	    $brand = str_replace('"', '\"', $brand);
	    $product_desc = str_replace('"', '\"', $product_desc);
	    require __DIR__.'/../config.php';
	    // $weight=2;
	    //Check if image url is NULL
	    if (is_null($image_url)||empty($image_url)){
	    	echo "\nerror image url is null\n";
	    	return;
	    }
	    $simple_sku=$conf_sku.'-'.$size;//for backcountry products.
	    
	    

	    // $log_row="admin,simple,\"".$sku.'-'.str_replace('.', '', $size)."\",\"{$size}\",{$weight},,\"{$name}\",\"{$categories}\",{$brand},{$image_url},{$image_url},{$image_url},1,{$special_price},{$price},\"{$product_desc}\",\"{$product_desc}\",0,Demo Attribute Set,1,1,5000,1";
	    $log_row="admin,simple,{$simple_sku},\"{$size}\",{$weight},,\"{$name}\",\"{$categories}\",\"{$brand}\",{$image_url},{$image_url},{$image_url},1,{$saleprice},{$price},\"{$product_desc}\",\"{$product_desc}\",0,Demo Attribute Set,1,1,\"{$inventory}\",1,,,";
	    // write_log($log_row);
	    $this->log($log_row);
	}   
	  
	//formats a row to be inserted as a Configurable Product
	function outputCsvRowConf($assocArray,$conf_name,$price,$saleprice){
		echo "\n   |-----------outputCsvRowConf() function----------\n";
	    // $product_url_raw=$assocArray['product_url'];
	    // $product_url=getUrl($product_url_raw);

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
	    $size_or_style_and_size="size";

	    $datafeedr_merchant=$assocArray['datafeedr_merchant'];
	    $datafeedr_buy_link=$assocArray['datafeedr_buy_link'];
	    $url_for_scrapping=$assocArray['url_for_scrapping'];


	    $log_row="admin,configurable,\"{$conf_sku}\",,,{$size_or_style_and_size},\"{$conf_name}\",\"{$categories}\",{$brand},{$image_url},{$image_url},{$image_url},1,{$saleprice},{$price},\"{$desc}\",\"{$desc}\",0,Demo Attribute Set,4,1,5000,1,{$datafeedr_merchant},{$datafeedr_buy_link},{$url_for_scrapping}";//stock not important for conf
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



