<?php

class backcountry_scrapper{
	// protected $domXpath;
	// public $dropdown_type;//"size" or "style & size"
	public $error;
	public $php_object;//store the decoded json in backcountry product page JS block.
	public $init_status;
	function __construct($bc_url){//or encode($url)
		if (empty($bc_url)){
	        echo 'param in construct function of backcountry scrapper does not have valid param. Exiting...';
	        return false;
	    }
	    // var_dump($bc_url);
	    // $bc_url=urldecode($bc_url);

	    $scraped_website = $this->curl($bc_url);  // Executing our curl function to scrape the webpage http://www.example.com and return the results into the $scraped_website variable
	    echo (strlen($scraped_website));
		//--------------get this js block---------------------------
		 // BC.product.skusCollection = $.parseJSON('{"RIP00I9-HEGRE-XL":{"price":{"high":54.45,"low":27.22,"discount":50},"color":"//content.backcountry.com/images/items/tiny/RIP/RIP00I9/HEGRE.jpg","inventory":1,"isBackorderable":false,"displayPrice":"$27.22","displayName":"Heather Grey, XL","displaySort":10,"isOnSale":true,"isOutlet":false,"size":"XL"},"RIP00I9-HEGRE-L":{"price":{"high":54.45,"low":27.22,"discount":50},"color":"//content.backcountry.com/images/items/tiny/RIP/RIP00I9/HEGRE.jpg","inventory":2,"isBackorderable":false,"displayPrice":"$27.22","displayName":"Heather Grey, L","displaySort":9,"isOnSale":true,"isOutlet":false,"size":"L"}}');
		 //-----------------------------------------------------

	    $pos1=strpos($scraped_website, 'BC.product.skusCollection = $.parseJSON(');
	    // var_dump($pos1);
	    if ($pos1===false){
	    	$this->init_status=false;
	    	return false;
	    }
	    $removed_before=substr($scraped_website,$pos1+strlen('BC.product.skusCollection = $.parseJSON(')+1);
	    $pos2=strpos($removed_before, ');');//); BC.product.title
	    // var_dump($pos2);
	   	if ($pos2===false){
	    	$this->init_status=false;
	    	return false;
	    }
	    $json_in_html=substr($removed_before, 0,$pos2-1);
	    $json_in_html=str_replace('\\"', '"', $json_in_html);
	    $json_in_html=str_replace('\\/', '/', $json_in_html);
	    // var_dump($json_in_html);
	    // echo '<pre>';
	    // var_dump(json_decode($json_in_html));
	    // echo '</pre>';
	    $this->php_object=json_decode($json_in_html);
	    // var_dump($this->php_object);
	    if ($this->php_object===null){
	    	$this->init_status=false;
	    	return false;
	    }else{
	    	// echo '<pre>';
	    	// var_dump($this->php_object);
	    	// echo '</pre>';
	    	$this->init_status=true;
	    	return true;
	    }
	}

	/**
	* 	Returns an array with size as key. an array containing 2 prices for each key.
	*				array(4) {
	*				  [25]=>
	*				  array(2) {
	*				    ["price"]=>
	*				    float(68)
	*				    ["saleprice"]=>
	*				    float(40.8)
	*				  }
	*				  [26]=>
	*				  array(2) {
	*				    ["price"]=>
	*				    float(68)
	*				    ["saleprice"]=>
	*				    float(40.8)
	*				}
	*  On error, return false.
	*/
	public function getScrappedAttributes($conf_sku){
	    // var_dump($simple_sku);
	    // $pos_2nd_dash=strrpos($simple_sku, "-");
	    // // var_dump($pos_2nd_dash);
	    // if ($pos_2nd_dash===false){
	    // 	echo 'no dash';
	    // 	return false;
	    // }
	    // $conf_sku=substr($simple_sku, 0, $pos_2nd_dash);
	    if (empty($conf_sku)){
	    	echo "\nError: param $conf_sku is empty\n";
	    	return false;
	    	// var_dump($conf_sku);
		}
	    // if (property_exists($this->php_object,$simple_sku)){//eigj-XL
	    // echo 'sku exists.';
    	$array=get_object_vars ($this->php_object);
    	// echo '<pre>';
    	// var_dump($array);
    	// echo '</pre>';
    	$scrapped_attributes=array();
    	foreach ($array as $simple_sku=>$simple_object) {//record all on sale chidren products in same configurable, with price.
    		// if (strpos($sku, $conf_sku)===0 && $array[$sku]->isOnSale==true){
    		if (strpos($simple_sku, $conf_sku)===0 && $simple_object->inventory>0){
    			// echo 'is on sale';
    			// echo $array[$sku]->price->high;
    			// echo '<br>';
    			// echo $array[$sku]->price->low;
    			// echo '<br>';
    			// echo $array[$sku]->size;
    			// echo '<br>-------------------<br>';
    			$size='';
    			if (property_exists($simple_object,"size")){
    				$size=$simple_object->size;
    			}else{
    			// if (is_null($size)){
					$size="One Size";
    				//if no size in $array[$sku]. means this is One Size.var_dump $array:
    				/*array(1) {
					  ["QKS01YD-AZA-ONESIZ"]=>
					  object(stdClass)#3 (9) {
					    ["price"]=>
					    object(stdClass)#4 (3) {
					      ["high"]=>
					      float(21.95)
					      ["low"]=>
					      float(12.07)
					      ["discount"]=>
					      int(45)
					    }
					    ["color"]=>
					    string(63) "//content.backcountry.com/images/items/tiny/QKS/QKS01YD/AZA.jpg"
					    ["inventory"]=>
					    int(12)
					    ["isBackorderable"]=>
					    bool(false)
					    ["displayPrice"]=>
					    string(6) "$12.07"
					    ["displayName"]=>
					    string(16) "Azalea, One Size"
					    ["displaySort"]=>
					    int(1)
					    ["isOnSale"]=>
					    bool(true)
					    ["isOutlet"]=>
					    bool(false)
					  }
					}*/
    			}
    			$scrapped_attributes[$size]=array(
    										"price"=>$simple_object->price->high,
    										"saleprice"=>$simple_object->price->low,
    										"inventory"=>$simple_object->inventory
    										);
    		}
    	}
    	if (!$this->sanitizeResult($scrapped_attributes)) {
    		//this is Style & Size option. 
    		return false;
    	}
		$first_key=key($scrapped_attributes);
		if (is_numeric($first_key)){
			reset($scrapped_attributes);
			ksort($scrapped_attributes);//Sort an associative array in ascending order, according to the key
		}
    	$scrapped_attributes=array("conf_sku"=>$conf_sku,"child_attributes"=>$scrapped_attributes);
    	return $scrapped_attributes;//or empty array 
	}

	private function sanitizeResult($scrapped_attributes){
		return !empty($scrapped_attributes);
	}
	/*
		input: all options nodes (inner most) for the 'size' or 'styles or size' dropdown. (the xpath_selector matches all options text node in the dropdown)
		output: the array containing all options texts under backcountry dropdown.
		on error:  if can't find, return -1.
	*/
	private function getDomNodeValues($xpath_selector){
		$elements = $this->domXpath->query($xpath_selector);
		$values=array();
		if ($elements->length==0){
			return -1;
		}else{
			foreach ($elements as $element) {
			// echo "<br/>[". $element->nodeName. "]";
				$nodes = $element->childNodes;
				foreach ($nodes as $node) {
				  	// echo $node->nodeValue. "\n";
					array_push($values, $node->nodeValue);
				}
			}
			return $values;
		}
	}
	/*
		output: the array containing all image urls on backcountry product view page. when you open the page full screen, the dots under image slider has a one-to-one relationship with the options.

		e.g. http://www.backcountry.com/honey-stinger-organic-energy-gels-24-pack?skid=HON0012-ACA-ONESIZ&ti=UExQIENhdDpOdXRyaXRpb246MToxNTpjYXQxMDAyMDc3MTU=

		on error:  if can't find, return -1.
	*/
	private  function getDomNode_Image_attribute($xpath_selector){
		$elements = $this->domXpath->query($xpath_selector);
		$values=array();
		if ($elements->length==0){
			echo 'images not found in DOM<br>';
			return -1;
		}else{
			foreach ($elements as $element) {
				// echo "<br/>[". $element->nodeName. "]";
				// echo "<br/>[". $element->getAttribute('data-large-img'). "]";
				array_push($values, 'http:'.$element->getAttribute('data-large-img'));
			}
			return $values;
		}
	}
    // Defining the basic cURL function    
    private function curl($url) {
        $ch = curl_init();  // Initialising cURL
        curl_setopt($ch, CURLOPT_URL, $url);    // Setting cURL's URL option with the $url variable passed into the function
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); // Setting cURL's option to return the webpage data
        $data = curl_exec($ch); // Executing the cURL request and assigning the returned data to the $data variable
        curl_close($ch);    // Closing cURL
        return $data;   // Returning the data from the function
    }


}
?>

<?php
//-------------test1-pass--------------
// $scrapper=new backcountry_scrapper("http://www.backcountry.com/rip-curl-go-wild-maxi-dress-womens?CMP_SKU=RIP00I9&MER=0406&utm_source=CJ&utm_source=Affiliate&mr:trackingCode=0A89AFA8-4EB4-E411-BDDA-BC305BF82376&mr:referralID=NA&AID=10279061&PID=4623576");
// if ($scrapper->init_status===false){
// 	die("cannot instantiate scrapper");
// }
// $scrapped_attributes=$scrapper->getScrappedAttibutes("RIP00I9-HEGRE-L");
// if ($scrapped_attributes!==false && !empty($scrapped_attributes)){
// 	var_dump($scrapped_attributes);
// }


//-----------test2-pass----------------
// $scrapper=new backcountry_scrapper("http://www.backcountry.com/kavu-layla-dress-womens?CMP_SKU=KAV004T&MER=0406&utm_source=CJ&utm_source=Affiliate&mr:trackingCode=A20B1690-B4C8-E411-BDDA-BC305BF82376&mr:referralID=NA&AID=10279061&PID=4623576");

// if ($scrapper->init_status===false){
// 	die("cannot instantiate scrapper");
// }
// $scrapped_attributes=$scrapper->getScrappedAttibutes("KAV004T-SOU-L");
// if ($scrapped_attributes!==false && !empty($scrapped_attributes)){
// 	var_dump($scrapped_attributes);
// }
//-----------test3-pass-----------------
// $scrapper=new backcountry_scrapper("http://www.backcountry.com/free-people-roller-crop-denim-pant-womens?CMP_SKU=FRP0006&MER=0406&utm_source=CJ&utm_source=Affiliate&mr:trackingCode=293332C3-4817-E511-80F1-005056944E17&mr:referralID=NA&AID=10279061&PID=4623576");

// if ($scrapper->init_status===false){
// 	die("cannot instantiate scrapper");
// }
// $scrapped_attributes=$scrapper->getScrappedAttibutes("FRP0006-ELLWAS-S25");
// if ($scrapped_attributes!==false && !empty($scrapped_attributes)){
// 	echo '<pre>';
// 	var_dump($scrapped_attributes);
// 	echo '</pre>';
// }
//----------test4-pass---------------------
// $scrapper=new backcountry_scrapper("http://www.backcountry.com/bench-up-and-coming-dress-womens?CMP_SKU=BNC006F&MER=0406&utm_source=CJ&utm_source=Affiliate&mr:trackingCode=15B59FF7-F16C-E511-80F3-005056944E17&mr:referralID=NA&AID=10279061&PID=4623576");

// if ($scrapper->init_status===false){
// 	die("cannot instantiate scrapper");
// }
// $scrapped_attributes=$scrapper->getScrappedAttibutes("BNC006F-BKMAR-S");
// if ($scrapped_attributes!==false && !empty($scrapped_attributes)){
// 	echo '<pre>';
// 	var_dump($scrapped_attributes);
// 	echo '</pre>';
// }


//----------test5-pass---------------------
// $scrapper=new backcountry_scrapper("http://www.backcountry.com/patagonia-morning-glory-dress-womens?CMP_SKU=PAT00RC&MER=0406&utm_source=CJ&utm_source=Affiliate&mr:trackingCode=58C7FF26-3C97-E411-9BFE-BC305BF82376&mr:referralID=NA&AID=10279061&PID=4623576");

// if ($scrapper->init_status===false){
// 	die("cannot instantiate scrapper");
// }
// $scrapped_attributes=$scrapper->getScrappedAttibutes("PAT00RC-DIARD");
// if ($scrapped_attributes!==false && !empty($scrapped_attributes)){
// 	echo '<pre>';
// 	var_dump($scrapped_attributes);
// 	echo '</pre>';
// }

//----test6: special: one size product has no size attribute in scrapped $simple_object--------------
// $scrapper=new backcountry_scrapper("http://www.backcountry.com/roxy-lima-beanie-teenie-toddler-girls?CMP_SKU=QKS01YD");

// if ($scrapper->init_status===false){
// 	die("\nCannot instantiate scrapper\n");
// }
// $scrapped_attributes=$scrapper->getScrappedAttibutes("QKS01YD-AZA");
// if ($scrapped_attributes!==false && !empty($scrapped_attributes)){
// 	echo '<pre>';
// 	var_dump($scrapped_attributes);
// 	echo '</pre>';
// }
//----test7: special: out of stock product does not have js block desired--------------
// $scrapper=new backcountry_scrapper("http://www.backcountry.com/hello-apparel-hello-floral-t-shirt-short-sleeve-toddler-girls?CMP_SKU=HLO0006");

// if ($scrapper->init_status===false){
// 	die("cannot instantiate scrapper");
// }
//----------------------------------------------------------------