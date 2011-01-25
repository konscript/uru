<?php 
/*
$quantity = 0;
$sum = 0;

foreach($products as $product){
	$price = substr($product["price"], 0, stripos($product["price"], ".") ); //remove all after "."
	$price = preg_replace('/[^\d\s]/', '',  $price); //remove currency symbols and the like
			
	$quantity += $product["quantity"];
	$sum += (preg_replace('/[^\d\s]/', '', $price))*$product["quantity"];	
}
?>
<div id="headerCart">
	<a href="?route=checkout/cart">Shopping Cart</a>
	<?php $itemSuffix = $quantity==1 ? ' item' : ' items'; ?>
	<span><?php echo $quantity.$itemSuffix; ?>, total: <?php echo $sum; ?></span>				
</div>
<?php
*/
?>