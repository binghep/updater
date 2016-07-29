<?php
/**
* Only takes the datafeedr affliate link as param. 
* Will reroute to real nordstrom product page.
*/

class nordstrom_scrapper{
	public $php_object;//store the decoded json in backcountry product page JS block.
	public $init_status;
	public $real_url_to_scrape;
	function __construct($nordstrom_url){//or encode($url)
	    //=============Additional rerouting for nordstrom==============
	    // var_dump($nordstrom_url);
	    // $accept_both_manual_and_auto=true;
	    $accept_both_manual_and_auto=false;
	    //=============Finished Additional rerouting for nordstrom==============
		if ($accept_both_manual_and_auto){//accept manual added nordstrom product url pasted from my brwoser bar
		    $redirect_nordstrom_url=$this->get_redirected_nordstrom_url($nordstrom_url);
		    if (!empty($redirect_nordstrom_url)) {//auto url
				$this->real_url_to_scrape=$redirect_nordstrom_url;
		    }else{//manually url
		    	$this->real_url_to_scrape=$nordstrom_url;
		    }
			$this->initialize();
		}else{//only accept auto url
		    $nordstrom_url=$this->get_redirected_nordstrom_url($nordstrom_url);
			if (empty($nordstrom_url)){
		        echo 'param in construct function of nordstrom_scrapper does not have valid param. Exiting...';
		    }else{
			    $this->real_url_to_scrape=$nordstrom_url;
			    // var_dump($bc_url);
			    // $bc_url=urldecode($bc_url);
			    $this->initialize();
			}
		}
	}

	public function initialize(){
		 $scraped_website = $this->curl($this->real_url_to_scrape);  // Executing our curl function to scrape the webpage http://www.example.com and return the results into the $scraped_website variable
	    // echo (strlen($scraped_website));
		//--------------get this js block---------------------------
		 //<script>React.render(React.createElement(product_desktop, {"initialData":{"Model":{"StyleModel":{"Id":4149469,"Name":"'Lauren' Long Sleeve Shift Dress","Title":"Lush 'Lauren' Long Sleeve Shift Dress","Number":"5020927","Description":"<p>A stretch-knit shift dress features a 
		 //-----------------------------------------------------
	   	$js_start_style=array("<script>React.render(React.createElement(product_desktop, ","<script>React.render(React.createElement(ProductDesktop, ");
	   	// foreach ($js_start_style as $key => $value) {
	   	// 	var_dump(strlen($value));//58 and 57
	   	// }
	   	// $length=[58,57];
	   	// return;
	   	$pos1=false;
	   	foreach ($js_start_style as $style) {
	   		// var_dump(strlen($style));
		    $pos1=strpos($scraped_website, $style);
		    if ($pos1===false){
		    	continue;
		    }else{
				$pos1=$pos1+strlen($style);
			    break;
	   		}
	   	}
	   	if ($pos1===false){
	    	$this->init_status=false;
	    	return;
	    }
		$removed_before=substr($scraped_website,$pos1);

		$pos2=strpos($removed_before, "), document.getElementById( 'main' ));</script>");	   		
	    //a few new line then :
	    /*
	    <script>
	    window.nord = window.nord || {};
	    window.nord.recommendations = {
	        environment: "Production",
	        isMobileRequest: false,
	        pageType: "ProductPage",
        */
	    // var_dump($pos2);
	   	if ($pos2===false){
	    	$this->init_status=false;
	    	return;
	    }
	    $json_in_html=substr($removed_before, 0,$pos2);
	    // $json_in_html=str_replace('\\"', '"', $json_in_html);
	    // $json_in_html=str_replace('\\/', '/', $json_in_html);
	    // var_dump($json_in_html);
	    // write_log($json_in_html);
	    // return;

	    // echo '<pre>';
	    // var_dump(json_decode($json_in_html));
	    // echo '</pre>';
	    // return;
	    $this->php_object=json_decode($json_in_html);
	    // var_dump($this->php_object);
	    if ($this->php_object===null){
	    	$this->init_status=false;
	    }else{
	    	// echo '<pre>';
	    	// var_dump($this->php_object);
	    	// echo '</pre>';
	    	$this->init_status=true;
	    }
	}
	/**
	* param: part of the url found in datafeedr_buy_link
	* output: $final_url the scrapable url that the param url redirects to.
	*/
	public function get_redirected_nordstrom_url($ns_url_from_datafeedr){
		// var_dump($ns_url_from_datafeedr);
		if (empty($ns_url_from_datafeedr)){
	        echo 'param empty. Exiting...';
	        return false;
	    }

	    $scraped_website = $this->curl($ns_url_from_datafeedr);  
	    // echo (strlen($scraped_website));
	    // var_dump($scraped_website);
		//========additional logic to find redirect url for nordstrom========
	    // var_dump($scraped_website);
	    $redirect_url_start_pos=strpos($scraped_website, '<a href="');
	    if ($redirect_url_start_pos===false){
	    	return false;
	    }
	    $redirect_url_start_pos+=9;
	    // echo '-------------------';
	    // var_dump($redirect_url_start_pos);
	    $redirect_url_end_pos=strpos($scraped_website, '">here');
	    if ($redirect_url_end_pos===false){
	    	return false;
	    }
	    $redirect_url=substr($scraped_website, $redirect_url_start_pos,$redirect_url_end_pos-$redirect_url_start_pos);
	    $final_url="http://shop.nordstrom.com".$redirect_url;
	   	// var_dump($final);
	   	return $final_url;
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
		if (empty($conf_sku)){
		   	echo "\nError: param $conf_sku is empty\n";
		   	return false;
		}
		//try{
			// $object=null;
			// echo $object->ju;
			// echo here;
		//}catch(Exception $e){
			// echo $e->getMessage();
		//}
		// return;
		
		$StyleModel=$this->php_object->initialData->Model->StyleModel;
		// $StyleModel=$this->php_object;
		// var_dump($StyleModel);
		$DefaultMedia=$StyleModel->DefaultMedia;
		//--------------for Not Availabel products------------------------
		if (empty($StyleModel->ChoiceGroups)){
			echo "\nSkip this product. Product is out of stock\n";
			return false;
		}
		//----------------------------------------------------------------
		$original_price=$StyleModel->ChoiceGroups[0]->Price->OriginalPrice;
		$current_price=$StyleModel->ChoiceGroups[0]->Price->CurrentPrice;
		// var_dump($StyleModel->ChoiceGroups[0]->Price);
		// return;
		//-------------------get default color name and image------------
		$default_color_product_image=$DefaultMedia->ImageMediaUri->Large;
		$default_color=$DefaultMedia->ColorName;
		// var_dump($DefaultMedia);
		// var_dump($default_color_product_image);
		// var_dump($default_color);

		// return;
    	// $array=get_object_vars ($this->php_object);
	    // if (property_exists($this->php_object,$simple_sku)){//eigj-XL
    	$scrapped_attributes=array();
    	//---------------double check default color is correct------------
    	$default_color_2=$StyleModel->DefaultColor;
    	// var_dump($default_color_2);
    	if ($default_color!=$default_color_2){
    		echo 'Error: Their fault. default color does not match. exiting. \n';
    		return;
    	}
    	//--------------get all simple products belonging to this color------
    	$all_simple_objects=$StyleModel->Skus;
    	// var_dump($all_simple_objects);

    	foreach ($all_simple_objects as $simple_object) {//record all chidren products in same configurable, with price.
    		if ($simple_object->Color==$default_color && $simple_object->IsAvailable===true){
    			// var_dump($simple_object->Size);
    			if (is_null($simple_object->Size)){continue;}//only has color, no size
    			// echo ($simple_object->Price." - ".$simple_object->OriginalPrice."\n");//2nd one is 0 for some reason:$31.90 - 0
    			$price=$this->removeDollarSign($original_price);
    			$saleprice=$this->removeDollarSign($simple_object->Price);//is zero for 1 product
    			if ($saleprice==0){
	    			$saleprice=$this->removeDollarSign($current_price);
    			}

    			//--------------log abnormal-------------------
    			if ($price==0 || $saleprice==0){
    				echo "Error: Some logic error in scrapper causing some price to be zero, price($price) - saleprice($saleprice)\n";
    			}
    			$scrapped_attributes[$simple_object->Size]=array(
    										"price"=>$price,
    										"saleprice"=>$saleprice
    										);
    		}
    	}
    	//-------------------------------------------------------
    	if (empty($scrapped_attributes)){
    		//did not find sku with size. should be a product with color option only.
    		return false;
    	}
    	//-------------------------------------------------------
		$first_key=key($scrapped_attributes);
		if (is_numeric($first_key)){
			reset($scrapped_attributes);
			ksort($scrapped_attributes);//Sort an associative array in ascending order, according to the key
		}
    	$scrapped_attributes=array("conf_sku"=>$conf_sku,
    								"real_url_to_scrape"=>$this->real_url_to_scrape,
    								"color"=>$default_color,
    								"image"=>$default_color_product_image,
    								"child_attributes"=>$scrapped_attributes);

    	if (!$this->sanitizeResult($scrapped_attributes)){//valid and nonempty
    		return false;
    	}
    	return $scrapped_attributes;
	}
	private function removeDollarSign($price){
		// echo str_replace("$", "", $price);
		return str_replace("$", "", $price);
	}
	private function sanitizeResult($scrapped_attributes){
		if (is_null($scrapped_attributes['color'] || is_null($scrapped_attributes['image']))){
			return false;
		}

		$child_attributes=$scrapped_attributes['child_attributes'];
		if (empty($child_attributes)){
			//is empty array. 
			return false;
		}

		$sizes=array_keys($child_attributes);
		foreach ($sizes as $size) {
			// var_dump($child_attributes[$size]['price']);
			// var_dump($child_attributes[$size]['saleprice']);
			if (!is_numeric($child_attributes[$size]['price'])
					|| !is_numeric($child_attributes[$size]['saleprice'])
				){
				echo 'false';
				return false;
			}
		}
		return true;
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

