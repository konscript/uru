<?php if (!defined('WEBPATH')) die(); 
setOption('image_size','500',false); 
setOption('image_use_side','longest',false); 
setOption('thumb_size','75',false); 
setOption('thumb_crop','1',false); 
setOption('thumb_crop_width','75',false); 
setOption('thumb_crop_height','75',false); 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>
	<?php 
	echo getBareGalleryTitle(); 
	if ($_zp_gallery_page == 'index.php') {echo " | ".gettext('URU Pieces');}
	if ($_zp_gallery_page == 'album.php') {echo " | ".getBareAlbumTitle();} 
	if ($_zp_gallery_page == 'image.php') {echo " | ".getBareAlbumTitle(); echo " | ".getBareImageTitle(); }
	if ($_zp_gallery_page == 'contact.php') {echo " | ".gettext('Contact');}
	if ($_zp_gallery_page == 'pages.php') {echo " | ".getBarePageTitle();} 
	if ($_zp_gallery_page == 'archive.php') {echo " | ".gettext('Archive View');}
	if ($_zp_gallery_page == 'password.php') {echo " | ".gettext('Password Required...');}
	if ($_zp_gallery_page == '404.php') {echo " | ".gettext('404 Not Found...');}
	if ($_zp_gallery_page == 'search.php') {echo " | ".gettext('Search: ').getSearchWords();}
	if ($_zp_gallery_page == 'news.php') {echo " | ".gettext('Welcome To URU Diamonds');}
	if (($_zp_gallery_page == 'news.php') && (is_NewsArticle())) {echo " | ".getBareNewsTitle();} 
	?>	
	</title>
	<meta http-equiv="content-type" content="text/html; charset=<?php echo getOption('charset'); ?>" />
	<?php printRSSHeaderLink( "Gallery",gettext('Gallery RSS') ); ?>
	<?php if (in_context(ZP_ALBUM)) { printRSSHeaderLink( "Collection",gettext('This Album Collection') ); } ?> 
	<?php if (function_exists("printZenpageRSSHeaderLink")) { printZenpageRSSHeaderLink("News","", gettext('News RSS'), ""); } ?>
	<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/css/screen.css" type="text/css" media="screen"/>
	<?php if (getOption('color_style') != 'default_orange') { ?>
	<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/css/<?php echo getOption('color_style'); ?>.css" type="text/css" media="screen"/>
	<?php } ?>
	<?php if (getOption('contrast') == 'light') { ?>
	<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/css/light.css" type="text/css" media="screen"/>
	<?php } ?>
	<link rel="shortcut icon" href="<?php echo $_zp_themeroot; ?>/images/favicon.ico" /> 
	<?php zenJavascript(); ?>	
	<script type="text/javascript" src="<?php echo $_zp_themeroot; ?>/js/jquery.opacityrollover.js"></script>
	<script type="text/javascript" src="<?php echo $_zp_themeroot; ?>/js/jquery.history.js"></script>
	<?php if ( (($_zp_gallery_page == 'album.php') || ($_zp_gallery_page == 'search.php')) && ((getOption('nogal'))) ) { ?>
	<script type="text/javascript" src="<?php echo $_zp_themeroot; ?>/js/jquery.galleriffic.js"></script>
	<script type="text/javascript" src="<?php echo $_zp_themeroot; ?>/js/zpgalleriffic.js"></script>
	<?php }  else { ?>
	<script type="text/javascript" src="<?php echo $_zp_themeroot; ?>/js/zpgalleriffic-min.js"></script>
	<?php } ?>
	<script type="text/javascript" src="<?php echo $_zp_themeroot; ?>/js/fadeSliderToggle.js"></script>
	<?php if ( ($_zp_gallery_page == 'image.php') && (getOption('final_link')=='colorbox') ) { ?> 
	<script type="text/javascript" src="<?php echo WEBPATH."/".ZENFOLDER."/js/colorbox/jquery.colorbox-min.js"; ?>"></script>
	<script type="text/javascript">
		$(document).ready(function(){
			$("a[rel='zoom']").colorbox({slideshow:false,transition:'fade',maxHeight:'90%',photo:true });
		});
	</script>
	<?php } ?>	

<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-18673176-5']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>	
</head>

<body>
<div id="main-menu">
	<?php if (
		($_zp_gallery_page == "index.php") ||
		($_zp_gallery_page == "gallery.php") ||
		($_zp_gallery_page == "album.php") ||
		($_zp_gallery_page == "image.php") ||
		($_zp_gallery_page == "search.php")
		)
		{ $galleryactive = 1; }
	?>
	<ul>
		<?php if (getOption('zenpage_homepage') == 'none') { ?>
		<?php if ( (function_exists('getNewsIndexURL')) && ((getNumNews()) > 0) ) { ?>
		<li <?php if ($_zp_gallery_page == "news.php") { ?>class="active" <?php } ?>><a href="<?php echo getNewsIndexURL(); ?>"><?php echo gettext('Home'); ?></a></li>
		<?php } else { ?>
		<li <?php if (getOption('zenpage_homepage') == (getPageTitleLink())) { ?>class="active" <?php } ?>><a href="<?php echo getGalleryIndexURL(false);?>"><?php echo gettext('Home'); ?></a></li>
		<li <?php if (($galleryactive) && ($_zp_gallery_page != "index.php")) { ?>class="active" <?php } ?>><?php printCustomPageURL(gettext('Gallery'),"gallery"); ?></li>
		<?php } ?>
		<li <?php if ($galleryactive) { ?>class="active" <?php } ?>><a href="<?php echo getGalleryIndexURL(true);?>"><?php echo gettext('URU Pieces'); ?></a></li>
		<?php } ?>
	</ul>
	<?php if (function_exists('printPageMenu')) { printPageMenu('list-top','','active','active','',''); } ?>
	<ul>
		<?php if (getOption('show_archive')) { ?>
		<li <?php if ($_zp_gallery_page == "archive.php") { ?>class="active" <?php } ?>><?php printCustomPageURL(gettext('Archive'),"archive"); ?></li>
		<?php } ?>	
		<?php if (function_exists('printContactForm')) { ?>
		<li <?php if ($_zp_gallery_page == "contact.php") { ?>class="active" <?php } ?>><?php printCustomPageURL(gettext('Contact'),"contact"); ?></li>
		<?php } ?>

<li><a href="shop/" title="Shop">Shop</a></li>

	</ul>	
</div>

<div id="page">
	<div id="container" class="clearfix">
		<div id="site-title" class="clearfix">
			<?php if (getOption('use_image_logo')) { ?>
			<a href="../../index.html" title="<?php echo getGalleryTitle();?>"><img id="zplogo" src="images/banniere1.jpg" alt="<?php echo getGalleryTitle();?>" /></a>
			<?php } else { ?>
		  <h1><a href="<?php echo htmlspecialchars(getGalleryIndexURL());?>"><?php echo getGalleryTitle();?></a></h1><br />
			<h2><?php echo getOption('tagline'); ?></h2>
			<?php } ?>
</div>
