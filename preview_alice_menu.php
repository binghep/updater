<?php
	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
	// session_start();
?>

<head>
	<script src="http://code.jquery.com/jquery-1.5.js" type="text/javascript"></script>
	<style type="text/css">
		span{
			width: 200px;
	    	background: #ECECEC;
	    	border: 1px solid;
	    }
	    .leaf{
	    	background: skyblue;
	    	padding-left: 20px;
	    }
	    body{
	    	line-height: 1.5;
	    }
	</style>
</head>
<body>
<?php

require_once '../../app/Mage.php';
Mage::app();


$categories = Mage::getModel('catalog/category')->getCollection()
    ->addAttributeToSelect('*')//or you can just add some attributes
    ->addAttributeToFilter('level', 2);//2 is actually the first level
if ($_SESSION['user']['login']!=="ddd"){
    $categories->addAttributeToFilter('is_active', 1);//if you want only active categories
}else{
	$categories->addAttributeToFilter('is_active', 1);
}


require_once 'config.php';
echo '<div>';
//echo all root level categories. only add link to datafeedr categories.
foreach ($categories as $category){
	// echo "<span class='menu-level-2'>".$category->getName()."</span>";
	echo "<span class='menu-level-2'>";
	if (!is_null($filter_strings[$category->getId()])){
		echo "<a  href='preview_alice.php?cat_id=",$category->getId(),"'>",$category->getName(),"</a>";
	}else{
		echo $category->getName();
	}
	echo '</span>';
}
echo '</div>';

echo '<div>';
foreach ($categories as $category){
	echo "<span class='spread' style='display:none'>";
	// var_dump($category->getId());
	$second_level_cat_ids=getChildCategories($category->getId());
	foreach ($second_level_cat_ids as $id){
		$cat=getCatObject($id);
		// var_dump($cat);
		if ($cat!==false){
			if (!is_null($filter_strings[$cat->getId()])){
				echo "<div><a  href='preview_alice.php?cat_id=",$cat->getId(),"'>",$cat->getName(),"</a></div>";
			}else{
				echo "<div>",$cat->getName(),"</div>";
			}
			// echo '<div class="leaf">';
			// outputChildCats($cat);
			// echo '</div>';
		}
	}
	echo "</span>";

}
echo '</div>';

/**
 * This function takes in 2nd level cat and display its children cat under it
 * @param  2nd level cat
 * @return null
 */
// function outputChildCats($middle_cat){
// 	$base_url="https://www.1661usa.com/en/";
// 	$leaf_cat_ids=getChildCategories($middle_cat->getId());
// 	foreach ($leaf_cat_ids as $id){
// 		$cat=getCatObject($id);
// 		// var_dump($cat);
// 		if ($cat!==false){
// 			// echo $cat->getName().'<br>';
// 			// var_dump($cat->getUrl());
// 			$_helper= Mage::helper('catalog/category');
// 			$url=$_helper->getCategoryUrl($cat);
// 			$i=strpos($url,'admin/');
// 			// var_dump($i);
// 			$url=substr($url,$i+6);
// 			// var_dump($url);
// 			echo '<a href="preview_alice.php?cat_id='.$cat->getId().'">'.$cat->getName().'</a>';
// 			echo '<a href="'.$base_url.$url.'" style="float: right;">[Link]</a>';
// 			echo '<br>';
// 		}
// 	}
// }

/*
input: category id. e.g. 415
output: the category object. 
		if category with this id does not exist, returns false
*/
function getCatObject($cat_id)
{
	$category = new Mage_Catalog_Model_Category();
	$category->load($cat_id);//414 
	if(!$category->getId()) {
		return false;
	}else{
		return $category;
	}
}
/*
input: parent catagory id
output: an array of child category ids of this parent category.
*/
function getChildCategories($id){
	$cat = Mage::getModel('catalog/category')->load($id);

	/*Returns comma separated ids*/
	$subcats = $cat->getChildren();

	//Print out categories string
	#print_r($subcats);
	return explode(',', $subcats);
	/*
	foreach(explode(',',$subcats) as $subCatid)
	{
		$_category = Mage::getModel('catalog/category')->load($subCatid);
		if($_category->getIsActive())
		{
			$caturl     = $_category->getURL();
			$catname     = $_category->getName();
			// if($_category->getImageUrl())
			// {
			// 	$catimg     = $_category->getImageUrl();
			// }
			// echo '<h2><a href="'.$caturl.'" title="View the products for this category">'.$catname.'</a></h2>';
		}
	}
	*/
}


?>

<script type="text/javascript">
	$(document).ready(function() {
	    $('.menu-level-2').mouseenter(function(){
	    	// go over all the pairs. set the left sides to white, right sides to hidden
	    	$('.menu-level-2').each(function(index){
		    	$(this).css("background-color","#ECECEC");
	    		// $(this).css("display","none");
	    	}); 
	    	$(this).css("background-color","skyblue");

	    	$('.spread').each(function(index){
		    	$(this).css("display","none");
	    	}); 
	    	var hover_index=$(this).index();
	        $('.spread').eq(hover_index).css("display","block");
	    });
	    // $('.menu-level-2').mouseleave(function(){
	    // 	$(this).css("background-color","#ECECEC");
	    // });
  	});
</script>