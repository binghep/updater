<?php	

// return;
	require_once '../../../../app/Mage.php';
	Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
	$product_sku=$_GET['sku'];


	if (empty($product_sku)){
		echo 'No Sku param found in GET variable.';
		exit;
	}
	$obj= Mage::getModel("catalog/product");
	$product_id = Mage::getModel("catalog/product")->getIdBySku( $product_sku ); //- See more at: http://www.techdilate.com/code/magento-get-product-id-by-sku/#sthash.vlHEys6k.dpuf
	$product = $obj->load($product_id); // Enter your Product Id in $product_id
	echo '<pre>';
	var_dump($product);
	echo '</pre>';

	
