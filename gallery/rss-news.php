<?php
require_once(dirname(__FILE__).'/zp-core/folder-definitions.php');
define('OFFSET_PATH', 0);
require_once(ZENFOLDER . "/template-functions.php");
require_once(ZENFOLDER . "/functions-rss.php");
startRSSCache();
if (!getOption('RSS_articles')) {
	header("HTTP/1.0 404 Not Found");
	header("Status: 404 Not Found");
	include(ZENFOLDER. '/404.php');
	exit();
}
require_once(ZENFOLDER . '/'.PLUGIN_FOLDER . "/image_album_statistics.php");
require_once(ZENFOLDER . '/'.PLUGIN_FOLDER . "/zenpage/zenpage-functions.php");
require_once(ZENFOLDER . '/'.PLUGIN_FOLDER . "/zenpage/zenpage-template-functions.php");
header('Content-Type: application/xml');
$themepath = THEMEFOLDER;
$catlink = getRSSNewsCatOptions("catlink");
$cattitle = getRSSNewsCatOptions("cattitle");
$option = getRSSNewsCatOptions("option");
if (isset($_GET['withimages'])) {
	$option = "withimages";
}
$host = getRSSHost();
$serverprotocol = getOption("server_protocol");
$uri = getRSSURI();
$s = getOption('feed_imagesize'); // un-cropped image size
$locale = getRSSLocale();
$validlocale = getRSSLocaleXML();
$items = getOption("zenpage_rss_items"); // # of Items displayed on the feed
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
<title><?php echo get_language_string(getOption('gallery_title'), $locale)." - News "; ?><?php if(!empty($cattitle)) { echo $cattitle ; } ?></title>
<link><?php echo $serverprotocol."://".$host.WEBPATH; ?></link>
<atom:link href="<?php echo $serverprotocol; ?>://<?php echo $uri; ?>" rel="self" type="application/rss+xml" />
<description><?php echo strip_tags(get_language_string(getOption('Gallery_description'), $locale)); ?></description>
<language><?php echo $validlocale; ?></language>
<pubDate><?php echo date("r", time()); ?></pubDate>
<lastBuildDate><?php echo date("r", time()); ?></lastBuildDate>
<docs>http://blogs.law.harvard.edu/tech/rss</docs>
<generator>Zenpage - A CMS plugin for ZenPhoto</generator>
<?php 
switch ($option) {
	case "category":
		$latest = getLatestNews($items,"none",$catlink); 	
		break;
	case "news":
		$latest = getLatestNews($items,"none"); 	
		break;
	case "withimages":
		$latest = getLatestNews($items,"with_latest_images_date");
		break;
} 
$count = "";
foreach($latest as $item) {
	$count++;
	$category = "";
	$categories = "";
	//get the type of the news item
	switch($item['type']) {
		case 'news':
			$obj = new ZenpageNews($item['titlelink']);
			$title = htmlspecialchars(get_language_string($obj->get('title'),$locale), ENT_QUOTES);
			$link = getNewsURL($obj->getTitlelink());
			$count2 = 0;
			$category = $obj->getCategories();
			foreach($category as $cat){
				$count2++;
				if($count2 != 1) {
					$categories = $categories.", ";
				}
				$categories = $categories.get_language_string($cat['cat_name'], $locale);
			}
			$thumb = "";
			$filename = "";
			if(getOption('zenpage_rss_length') != "") { // empty value means full content!
				$content = shortenContent(get_language_string($obj->get('content'),$locale),getOption('zenpage_rss_length'), $elipsis='...');
			}
			$content = '<![CDATA['.$content.']]>';
			$type = "news";
			$ext = "";
			$album = "";
			break;
		case 'images':
			$albumobj = new Album($_zp_gallery,$item['albumname']);
			$obj = newImage($albumobj,$item['titlelink']);
			$categories = get_language_string($albumobj->get('title'),$locale);
			$title = strip_tags(htmlspecialchars(get_language_string($obj->get('title'),$locale), ENT_QUOTES));
			$link = $obj->getImageLink();
			$type = "image";
			$filename = $obj->getFilename();
			$ext = strtolower(strrchr($filename, "."));
			$album = $albumobj->getFolder();
			$fullimagelink = $host.WEBPATH."/albums/".$album."/".$filename;
			$imagefile = "albums/".$album."/".$filename;
			$mimetype = getMimeType($ext);
			if(getOption('zenpage_rss_length') != "") { // empty value means full content!
				$content = shortenContent(get_language_string($obj->get('desc'),$locale),getOption('zenpage_rss_length'), $elipsis='...');
			}
			if(isImagePhoto($obj)) {
				$content = '<![CDATA[<a title="'.$title.' in '.$categories.'" href="'.$serverprotocol.'://'.$host.$link.'"><img border="0" src="'.$serverprotocol.'://'.$host.WEBPATH.'/'.ZENFOLDER.'/i.php?a='.$album.'&i='.$filename.'&s='.$s.'" alt="'. $title .'"></a>' . $content . ']]>';
			} else {
				$content = '<![CDATA[<a title="'.$title.' in '.$categories.'" href="'.$serverprotocol.'://'.$host.$link.'"><img src="'.$obj->getThumb().'" alt="'.htmlspecialchars($title,ENT_QUOTES).'" /></a>'.$content.']]>';
			}
			//$thumb = "<a href=\"".$link."\" title=\"".htmlspecialchars($title, ENT_QUOTES)."\"><img src=\"".$obj->getThumb()."\" alt=\"".htmlspecialchars($title,ENT_QUOTES)."\" /></a>\n";
			
			break;
		case 'albums':
			break;
	}
	$categories = htmlspecialchars($categories,ENT_QUOTES);			
?>
<item>
	<title><?php echo $title." (".$categories.")"; ?></title>
	<link><?php echo '<![CDATA['.$serverprotocol.'://'.$host.$link.']]>';?></link>
	<description>
	<?php echo $content;	?>
</description>
<?php if(getOption("feed_enclosure") AND !empty($item['thumb'])) { ?>
	<enclosure url="<?php echo $serverprotocol; ?>://<?php echo $fullimagelink; ?>" type="<?php echo $mimetype; ?>" length="<?php echo filesize($imagefile); ?>" />
<?php } ?>
    <category><?php echo $categories; ?>
    </category>
	<guid><?php echo '<![CDATA['.$serverprotocol.'://'.$host.$link.']]>';?></guid>
	<pubDate><?php echo date("r",strtotime($item['date'])); ?></pubDate> 
</item>
<?php
if($count === $items) {
	break;
}
} ?>
</channel>
</rss>
<?php endRSSCache();?>