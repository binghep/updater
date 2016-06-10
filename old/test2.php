 <!DOCTYPE html>
 <html>
 <head>
 	<title></title>
	<script src="http://code.jquery.com/jquery-1.5.js" type="text/javascript"></script>
 </head>
 <body>
 <script type="text/javascript">
 //BC.product.skusCollection
 $(document).ready(function() {
	 var skusCollection = $.parseJSON('{"RIP00I9-HEGRE-XL":{"price":{"high":54.45,"low":27.22,"discount":50},"color":"//content.backcountry.com/images/items/tiny/RIP/RIP00I9/HEGRE.jpg","inventory":1,"isBackorderable":false,"displayPrice":"$27.22","displayName":"Heather Grey, XL","displaySort":10,"isOnSale":true,"isOutlet":false,"size":"XL"},"RIP00I9-HEGRE-L":{"price":{"high":54.45,"low":27.22,"discount":50},"color":"//content.backcountry.com/images/items/tiny/RIP/RIP00I9/HEGRE.jpg","inventory":2,"isBackorderable":false,"displayPrice":"$27.22","displayName":"Heather Grey, L","displaySort":9,"isOnSale":true,"isOutlet":false,"size":"L"}}');


	console.log(skusCollection);
});
</script>