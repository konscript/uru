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
  <div class="div1">
    <div class="div2">
      <?php if ($logo) { ?>
      <a href="<?php echo str_replace('&', '&amp;', $home); ?>"><img src="<?php echo $logo; ?>" title="<?php echo $store; ?>" alt="<?php echo $store; ?>" /></a>
      <?php } ?>
    </div>
    <div class="div3">
	      <div class="div7">
	        <?php if ($currencies) { ?>
	        <form action="<?php echo str_replace('&', '&amp;', $action); ?>" method="post" enctype="multipart/form-data" id="currency_form">
	          <div class="switcher">
	            <?php foreach ($currencies as $currency) { ?>
	            <?php if ($currency['code'] == $currency_code) { ?>
	            <div class="selected"><a><?php echo $currency['title']; ?></a></div>
	            <?php } ?>
	            <?php } ?>
	            <div class="option">
	              <?php foreach ($currencies as $currency) { ?>
	              <a onclick="$('input[name=\'currency_code\']').attr('value', '<?php echo $currency['code']; ?>'); $('#currency_form').submit();"><?php echo $currency['title']; ?></a>
	              <?php } ?>
	            </div>
	          </div>
	          <div style="display: inline;">
	            <input type="hidden" name="currency_code" value="" />
	            <input type="hidden" name="redirect" value="<?php echo $redirect; ?>" />
	          </div>
	        </form>
	        <?php } ?>
	        <?php if ($languages) { ?>
	        <form action="<?php echo str_replace('&', '&amp;', $action); ?>" method="post" enctype="multipart/form-data" id="language_form">
	          <div class="switcher">
	            <?php foreach ($languages as $language) { ?>
	            <?php if ($language['code'] == $language_code) { ?>
	            <div class="selected"><a><img src="image/flags/<?php echo $language['image']; ?>" alt="<?php echo $language['name']; ?>" />&nbsp;&nbsp;<?php echo $language['name']; ?></a></div>
	            <?php } ?>
	            <?php } ?>
	            <div class="option">
	              <?php foreach ($languages as $language) { ?>
	              <a onclick="$('input[name=\'language_code\']').attr('value', '<?php echo $language['code']; ?>'); $('#language_form').submit();"><img src="image/flags/<?php echo $language['image']; ?>" alt="<?php echo $language['name']; ?>" />&nbsp;&nbsp;<?php echo $language['name']; ?></a>
	              <?php } ?>
	            </div>
	          </div>
	          <div>
	            <input type="hidden" name="language_code" value="" />
	            <input type="hidden" name="redirect" value="<?php echo $redirect; ?>" />
	          </div>
	        </form>
	        <?php } ?>
	      </div>     
    
    
        <div id="search">
          <div class="div8"><?php //echo $entry_search; ?>&nbsp;</div>
          <div class="div9">
            <?php if ($keyword) { ?>
            <input type="text" value="<?php echo $keyword; ?>" id="filter_keyword" />
            <?php } else { ?>
            <input type="text" value="<?php echo $text_keyword; ?>" id="filter_keyword" onclick="this.value = '';" onkeydown="this.style.color = '#fff'" style="color: #aaa;" />
            <?php } ?>

          </div>
          <div class="div10">&nbsp;&nbsp;<a onclick="moduleSearch();"><img src="catalog/view/theme/konscript/image/search.png"/></a> <a href="<?php echo str_replace('&', '&amp;', $advanced); ?>"></a></div>
        </div>   
    </div>
<div id="mainMenu">
	<a href="index.php?route=common/home">HOME</a>
	<a href="index.php?route=account/login">Log in</a>
	<a href="index.php?route=account/account">Account</a>
	<a href="index.php?route=checkout/cart">Basket</a>
	<a href="index.php?route=checkout/shipping">Checkout</a>
</div>

<?php if (isset($common_error)) { ?>
	  <div class="warning"><?php echo $common_error; ?></div>
<?php } ?>

    <div class="div5">    
     	<?php 
     		$route = isset($_GET["route"]) ? $_GET["route"] : false;
			if(!$route || $route=="common/home"){
		?>
		
		<div id="slider">
			<ul>								
				<li><a href=""><img src="catalog/view/theme/<?php echo $template; ?>/image/diamond7.jpg" alt="Css Template Preview" /></a></li>
				<li><a href=""><img src="catalog/view/theme/<?php echo $template; ?>/image/diamond.jpg" alt="Css Template Preview" /></a></li>	
				<li><a href=""><img src="catalog/view/theme/<?php echo $template; ?>/image/diamond6.jpg" alt="Css Template Preview" /></a></li>
				<li><a href=""><img src="catalog/view/theme/<?php echo $template; ?>/image/diamond4.png" alt="Css Template Preview" /></a></li>			
				<li><a href=""><img src="catalog/view/theme/<?php echo $template; ?>/image/diamond2.jpg" alt="Css Template Preview" /></a></li>
				<li><a href=""><img src="catalog/view/theme/<?php echo $template; ?>/image/diamond5.jpg" alt="Css Template Preview" /></a></li>			
				<li><a href=""><img src="catalog/view/theme/<?php echo $template; ?>/image/diamond3.jpg" alt="Css Template Preview" /></a></li>		
			</ul>
		</div>		
			
		<?php } ?>
    </div>
  </div>
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
