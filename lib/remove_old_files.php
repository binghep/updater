<?php
  $files = glob(cacheme_directory()."*");
  $now   = time();

  foreach ($files as $file)
    if (is_file($file))
      if ($now - filemtime($file) >= 60 * 60 * 24 * 2) // 2 days
        // unlink($file);
  		echo "removing file:".$file.PHP_EOF;
?>