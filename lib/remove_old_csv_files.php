<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
$csv_dir="/usr/share/nginx/www/ipzmall.com/alice/magmi_csv/alice_import/";

//To find all the files in the directory /path/to/directory with a .csv file extension, you can do this:
$files = glob($csv_dir."*.csv");//glob() returns the filenames into an array and supports pattern matching
// var_dump($files);  // an empty array if folder does not exist.
// return;


$now   = time();

echo "<pre>";
// var_dump($files);

foreach ($files as $file)
  if (is_file($file))
    if ($now - filemtime($file) >= 60 * 60 * 24 * 5) // 5 days //filemtime: last modified
		if ($file!=="/usr/share/nginx/www/ipzmall.com/alice/magmi_csv/alice_import/pls_do_not_change_me.for_reindex.csv" && $file!=="/usr/share/nginx/www/ipzmall.com/alice/magmi_csv/alice_import/peng6716.csv"){
			echo "removing file:".$file."<br>";
      		unlink($file);
		}
?>