<?php
class image_curl_api{
    function __construct(){

    }
    public function getRealUrl($url){
        echo "orginal_url you enterred: ".$url."<br>";

        $web_content=$this->curl($url);

        $keyword="http://cdn.avantlink.com/product-images/";
        $keyword2=".png";

        $start_pos=strpos($web_content,$keyword);
        $end_pos= strpos($web_content, $keyword2);
        
        // var_dump($end_pos);
        $cdn_image_path=false;
        if ($start_pos!==false && $end_pos!==false){
            $cdn_image_path=substr($web_content, $start_pos,$end_pos-$start_pos+4);
        }

        // var_dump($cdn_image_path);
        return $cdn_image_path;
    }
    function curl($url) {
        $ch = curl_init();  // Initialising cURL
        curl_setopt($ch, CURLOPT_URL, $url);    // Setting cURL's URL option with the $url variable passed into the function
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); // Setting cURL's option to return the webpage data
      
        $data = curl_exec($ch); // Executing the cURL request and assigning the returned data to the $data variable
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        // var_dump($header_size);
       $header = substr($response, 0, $header_size);
        // var_dump($header);
        $body = substr($response, $header_size);
        // var_dump($body);

        curl_close($ch);    // Closing cURL
        // return $data;   // Returning the data from the function
        return $data;
    }
 
}