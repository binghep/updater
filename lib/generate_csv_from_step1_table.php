<?php 
class generate_csv_from_step1_table{
    public $cat_name;
    public $fp;
    function __construct($cat_name){
        if (empty($cat_name)){
            echo "\ngenerate_csv_from_step1_table instantiation: not valid param. Exiting...\n";
            return false;
        }else{
            var_dump($cat_name);
            $this->cat_name=$cat_name;
        }
        
        // @$link = mysql_connect($mysql_host,$mysql_user,$mysql_pw); 
        // if (!$link) { 
        //     // die('Could not connect to MySQL: ' . mysql_error()); 
        //     echo 'connection error.<br>';
        //     return false;
        // } 
        // mysql_close($link); 
        return true;
    }
    //carry out the export process:
    public function run(){
        $formated_cat_name=$this->cat_name;
        $formated_cat_name=str_replace(" ", "_", $formated_cat_name);
        $formated_cat_name=str_replace("&", "_", $formated_cat_name);
        $output_csv_name =$this->exportProductsSimple($formated_cat_name);
        return $output_csv_name;
    }
    // public function run_selectively($allowed_skus){
    //     $formated_cat_name=$this->cat_name;
    //     $formated_cat_name=str_replace(" ", "_", $formated_cat_name);
    //     $formated_cat_name=str_replace("&", "_", $formated_cat_name);
    //     echo '--------';
    //     require 'config.php';
    //     $output_csv_name =$this->exportProductsSimple_selectively($formated_cat_name,$allowed_skus);
    //     return $output_csv_name;
    // }
    //Writes a row to csv
    function log($msg){
        //$fp = fopen('magmi_csv/1.csv', 'a');//append
        fwrite($this->fp, $msg."\n");
        //fclose($fp);
    }


    /*Pulls products from $mysql_table_step_1_datafeedr_results to input into CSV
    returns false on error. 
    returns CSV name on success
    */
    function exportProductsSimple($category_name){
        require __DIR__.'/../config.php';
        require_once __DIR__.'/../database/dbcontroller.php';
        $db_handle=new DBController();  
        // var_dump($mysql_table_step_1_datafeedr_results);
        $query = "select * from ".$mysql_table_step_1_datafeedr_results;
        $result=$db_handle->runQuery($query);
        $arrayFull = array();
        $arrayUnique = array();
        $file_name='';
        if (!is_null($result) && count($result) >0) {
            date_default_timezone_set('America/Los_Angeles');
            $tdate = date("Y-m-d_h-i-s");
            // $filename='/usr/share/nginx/www/ems.1661hk.com/productExport-test/magmi_csv/'.$category_name.'_'.$tdate.'.csv';
            $file_name=$category_name.'_'.$tdate.'.csv';
            $file_path=__DIR__.'/../../magmi_csv/alice_import/'.$file_name;
            // $file_path="/usr/share/nginx/www/ipzmall.com/alice/magmi_csv/alice_import/".$file_name;
            // var_dump($file_path);
            // var_dump($file_path);
            $this->fp = fopen($file_path, 'w');//append to file in magento root.
            //create csv first line header.
            $this->log("store,type,sku,weight,name,category_ids,brand,image_external_url,small_image_external_url,thumbnail_external_url,use_external_images,special_price,price,short_description,description,tax_class_id,attribute_set,visibility,status,qty,is_datafeedr_product,datafeedr_merchant,datafeedr_buy_link,url_for_scrapping");
            // write_log("store,type,sku,$which,weight,configurable_attributes,name,category_ids,brands,image_external_url,small_image_external_url,thumbnail_external_url,use_external_images,special_price,price,short_description,description,tax_class_id,attribute_set,visibility,status,manage_stock");
            
            $debug=false;

            foreach ($result as $key => $one_row) {
                echo 'one';
                $nameRaw=$one_row['product_name'];
                $nameUnique = strpos($nameRaw, ",") ? substr($nameRaw, 0, strpos($nameRaw, ",")) : $nameRaw;
                $one_row['product_name']=$nameUnique;
                $sku=$one_row['sku'];
                // $product_url_raw=$one_row['product_url']; 
                // $product_url=getUrl($product_url_raw);
                $this->outputCsvassocArray($one_row);
                if ($debug) {break;}
            }
            return $file_name;
        }else{
            return false;
        }
    }
    // function curl($url) {
    //     $ch = curl_init();  // Initialising cURL
    //     curl_setopt($ch, CURLOPT_URL, $url);    // Setting cURL's URL option with the $url variable passed into the function
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); // Setting cURL's option to return the webpage data
    //     $data = curl_exec($ch); // Executing the cURL request and assigning the returned data to the $data variable
    //     curl_close($ch);    // Closing cURL
    //     return $data;   // Returning the data from the function
    // }


    // function getUrl($product_url_raw)
    // {
    //     $product_url=$product_url_raw;
    //     if (strpos($product_url, 'userID=@@@') !== false) {
    //         $product_url = str_replace("userID=@@@", "userID=1205490", $product_url);
    //     }
    //     return $product_url;
    // }


    //formats a row to be inserted as a Simple Product
    function outputCsvassocArray($assocArray){
        require __DIR__.'/../config.php';
        // $product_url_raw=$assocArray['product_url'];
        // $product_url=getUrl($product_url_raw);
        $sku=$assocArray['sku'];
        $name=$assocArray['product_name'];
        $categories=$assocArray['cat_id'];
        // $categories="249,338,640";
        $brandRaw=trim($assocArray['brand']);
        $brand = $this->titleCase($brandRaw);
        $image_url=$this->process($assocArray['image_url']);
        $special_price=$this->removeCommaInPrice($assocArray['saleprice']);//some rare merchants put 1. 
        // $special_price=$assocArray['regular_price'];
        $price=$this->removeCommaInPrice($assocArray['price']);
        $desc=$this->processDescription($assocArray['product_desc']);

        $datafeedr_merchant=$assocArray['datafeedr_merchant'];
	    $datafeedr_buy_link=$assocArray['datafeedr_buy_link'];
        // $url_for_scrapping=$assocArray['url_for_scrapping'];
       
        $name = str_replace('"', '""', $name);
        $brand = str_replace('"', '""', $brand);
        if ($brand=="Default Brand"){
            $brand="";
        }
        $desc = str_replace('"', '""', $desc);
        // $weight=2;
        //Check if image url is NULL
        if (is_null($image_url)||empty($image_url)){
            echo 'error image url is null';

            return;
        }
        // var_dump("admin,simple,\"".$sku.'-'.str_replace('.', '', $size)."\",\"{$size}\",{$weight},,\"{$name}\",\"{$categories}\",{$brand},{$image_url},{$image_url},{$image_url},1,{$special_price},{$price},\"{$desc}\",\"{$desc}\",0,Demo Attribute Set,1,1,0");
        $log_row="admin,simple,\"".$sku."\",{$weight},\"{$name}\",\"{$categories}\",\"{$brand}\",\"{$image_url}\",\"{$image_url}\",\"{$image_url}\",1,{$special_price},{$price},\"{$desc}\",\"{$desc}\",0,Default,4,1,5000,1,{$datafeedr_merchant},{$datafeedr_buy_link},";
        // write_log($log_row);
        // var_dump($log_row);
        $this->log($log_row);
    }   
      
    /*
    Convert to real cdn image url if the image url is 
    "http://i2.avlws.com/52/l1113866.png" or 
    "http://i1.avlws.com/3733/l13.png"
    */
    function processDescription($desc){
        return str_replace("\n", "<br>", $desc);
    }

    function process($image_url){
        if (strpos($image_url,"avlws.com/")===false){
            return $image_url;
        }else{
            require_once __DIR__.'/../lib/image_curl_api.php';
            $image_curl_api=new image_curl_api();
            return $image_curl_api->getRealUrl($image_url);
        }
    }  
    function removeCommaInPrice($price){
        $new_price=str_replace(",", "", $price);
        return $new_price;
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