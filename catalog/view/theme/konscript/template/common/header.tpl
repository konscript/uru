<?php if (isset($_SERVER['HTTP_USER_AGENT']) && !strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 6')) echo '<?xml version="1.0" encoding="UTF-8"?>
'. "\n"; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="
	<?php echo $direction; ?>" lang="
	<?php echo $lang; ?>" xml:lang="<?php echo $lang; ?>">
	<head>
	<title><?php echo $title; ?></title>
	<?php if ($keywords) { ?>
	<meta name="keywords" content="<?php echo $keywords; ?>" />
	<?php } ?>
	<?php if ($description) { ?>
	<meta name="description" content="<?php echo $description; ?>" />
	<?php } ?>
	<base href="<?php echo $base; ?>" />
	<?php if ($icon) { ?>
	<link href="<?php echo $icon; ?>" rel="icon" />
	<?php } ?>
	<?php foreach ($links as $link) { ?>
	<link href="<?php echo str_replace('&', '&amp;', $link['href']); ?>" rel="<?php echo $link['rel']; ?>" />
	<?php } ?>
	<link rel="stylesheet" type="text/css" href="catalog/view/theme/<?php echo $template; ?>/stylesheet/stylesheet.css" />
	<!--[if lt IE 7]>
	<link rel="stylesheet" type="text/css" href="catalog/view/theme/default/stylesheet/ie6.css" />
	<script type="text/javascript" src="catalog/view/javascript/DD_belatedPNG_0.0.8a-min.js"></script>
	<script>
	DD_belatedPNG.fix('img, #header .div3 a, #content .left, #content .right, .box .top');
	</script>
	<![endif]-->
	<?php foreach ($styles as $style) { ?>
	<link rel="<?php echo $style['rel']; ?>" type="text/css" href="<?php echo $style['href']; ?>" media="<?php echo $style['media']; ?>" />
	<?php } ?>
	<script type="text/javascript" src="catalog/view/javascript/jquery/jquery-1.4.4.min.js"></script>
	<script type="text/javascript" src="catalog/view/javascript/jquery/easySlider1.7.js"></script>
	<link rel="stylesheet" type="text/css" href="catalog/view/theme/<?php echo $template; ?>/stylesheet/easySlider.css" />
	<script type="text/javascript" src="catalog/view/javascript/jquery/thickbox/thickbox-compressed.js"></script>
	<link rel="stylesheet" type="text/css" href="catalog/view/javascript/jquery/thickbox/thickbox.css" />
	<script type="text/javascript" src="catalog/view/javascript/jquery/tab.js"></script>
	<?php foreach ($scripts as $script) { ?>
	<script type="text/javascript" src="<?php echo $script; ?>"></script>
	<?php } ?>
	<script type="text/javascript"><!--
	
	$(document).ready(function() {
		$("#slider").easySlider({
			auto: true,
			continuous: true 
		});	
	});
	
	//--></script>
</head>
<body>
<div id="container">
<div id="header">
    <div id="logo">
      <?php if ($logo) { ?>
      <a href="<?php echo str_replace('&', '&amp;', $home); ?>"><img src="<?php echo $logo; ?>" title="<?php echo $store; ?>" alt="<?php echo $store; ?>" /></a>
      <?php } ?>
    </div>
        	
	<div id="headerCart">
		<a href="<?php echo str_replace('&', '&amp;', $cart); ?>">Shopping Cart</a>
		<?php $itemSuffix = $this->cart->countProducts()==1 ? ' item' : ' items'; ?>
		<span><?php echo $this->cart->countProducts().$itemSuffix; ?>, total: <?php echo $subtotal; ?></span>				
    </div>
    
	<?php echo $category; ?>	
	<?php echo $information; ?>
		
	<?php if (isset($common_error)) { ?>
		  <div class="warning"><?php echo $common_error; ?></div>
	<?php } ?>
</div>
<script type="text/javascript"><!--
$('#search input').keydown(function(e) {
	if (e.keyCode == 13) {
		moduleSearch();
	}
});

function moduleSearch() {
	url = 'index.php?route=product/search';
	
	var filter_keyword = $('#filter_keyword').attr('value')
	
	if (filter_keyword) {
		url += '&keyword=' + encodeURIComponent(filter_keyword);
	}
	
	var filter_category_id = $('#filter_category_id').attr('value');
	
	if (filter_category_id) {
		url += '&category_id=' + filter_category_id;
	}
	
	location = url;
}
//--></script>
<script type="text/javascript"><!--
$('.switcher').bind('click', function() {
	$(this).find('.option').slideToggle('fast');
});
$('.switcher').bind('mouseleave', function() {
	$(this).find('.option').slideUp('fast');
}); 
//--></script>
