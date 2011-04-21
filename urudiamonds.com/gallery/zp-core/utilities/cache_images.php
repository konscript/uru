<?php
/**
 * This template is used to generate cache images. Running it will process the entire gallery,
 * supplying an album name (ex: loadAlbums.php?album=newalbum) will only process the album named.
 * Passing clear=on will purge the designated cache before generating cache images
 * @package core
 */

// force UTF-8 Ø
define('OFFSET_PATH', 3);
require_once(dirname(dirname(__FILE__)).'/admin-functions.php');
require_once(dirname(dirname(__FILE__)).'/admin-globals.php');
require_once(dirname(dirname(__FILE__)).'/template-functions.php');

$button_text = gettext('Pre-Cache Images');
$button_hint = gettext('Finds newly uploaded images that have not been cached and creates the cached version. It also refreshes the numbers above. If you have a large number of images in your gallery you might consider using the pre-cache image link for each album to avoid swamping your browser.');
$button_icon = 'images/cache1.png';
$button_rights = ADMIN_RIGHTS;
$button_XSRFTag = 'cache_images';

if (isset($_REQUEST['album'])) {
	$localrights = ALBUM_RIGHTS;
} else {
	$localrights = NULL;
}
admin_securityChecks($localrights, $return = currentRelativeURL(__FILE__));

XSRFdefender('cache_images');

function loadAlbum($album) {
	global $gallery, $_zp_current_album, $_zp_current_image;
	$subalbums = $album->getAlbums();
	$started = false;
	$tcount = $count = 0;
	foreach ($subalbums as $folder) {
		$subalbum = new Album($gallery, $folder);
		if (!$subalbum->isDynamic()) {
			$tcount = $tcount + loadAlbum($subalbum);
		}
	}
	$_zp_current_album = $album;
	if (getNumImages() > 0) {
		echo "<br />" . $album->name . ' ';
		while (next_image(true)) {
			$thumb = getImageThumb();
			if (strpos($thumb, 'i.php?') === false) {
				$thumb = NULL;
			}
			if (isImagePhoto($_zp_current_image)) {
				$image = getDefaultSizedImage();
				if (strpos($image, 'i.php?') === false) {
					$image = NULL;
				}
			} else {
				$image = NULL;
				if ($_zp_current_image->objectsThumb == NULL) {
					$thumb = NULL;
				}
			}
			if (!empty($thumb) || !empty($image)) {
				if (!$count) {
					$started = true;
					echo "{ ";
				} else {
					echo ' | ';
				}
			}
			if (!empty($thumb)) echo '<img src="' . $thumb . '" height="8" width="8" /> ';
			if (!empty($image)) echo ' <img src="' . $image . '" height="20" width="20" />';
			if (!empty($thumb) || !empty($image)) echo "\n";
			$count++;
		}
		if ($started) echo ' } ';
		printf(ngettext('[%u image]','[%u images]',$count),$count);
		echo "<br />\n";
	}
	return $count + $tcount;
}

if (isset($_GET['album'])) {
	$alb = $_GET['album'];
} else if (isset($_POST['album'])) {
	$alb = urldecode($_POST['album']);
} else {
	$alb = '';
}
if ($alb) {
	$folder = sanitize_path($alb);
	$object = $folder;
	$tab = 'edit';
	if (!isMyAlbum($folder, ALBUM_RIGHTS)) {
		if (!zp_apply_filter('admin_managed_albums_access',false, $return)) {
			header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin.php');
			exit();
		}
	}
} else {
	$object = '<em>'.gettext('Gallery').'</em>';
	$tab = 'home';
}

printAdminHeader();
echo "\n</head>";
echo "\n<body>";

printLogoAndLinks();
echo "\n" . '<div id="main">';
printTabs($tab);
echo "\n" . '<div id="content">';

$clear = sprintf(gettext('Refreshing cache for %s'), $object);
$count = 0;

$gallery = new Gallery();

if ($alb) {
	echo "\n<h2>".$clear."</h2>";
	$album = new Album($gallery, $folder);
	$count =loadAlbum($album);
} else {
	echo "\n<h2>".$clear."</h2>";
	$albums = $_zp_gallery->getAlbums();
	shuffle($albums);
	foreach ($albums as $folder) {
		$album = new Album($gallery, $folder);
		if (!$album->isDynamic()) {
			$count = $count + loadAlbum($album);
		}
	}
}
echo "\n" . "<br />".sprintf(gettext("Finished: Total of %u images."), $count);

if (isset($_REQUEST['return'])) {
	if (isset($_POST['return'])) {
		$ret = sanitize_path($_POST['return']);
	} else {
		$ret = sanitize_path($_GET['return']);
	}
	if (substr($ret, 0, 1) == '*') {
		if (empty($ret) || $ret == '*.' || $ret == '*/') {
			$r = '/admin-edit.php?page=edit';
		} else {
			$r = '/admin-edit.php?page=edit&amp;album='.urlencode(substr($ret, 1)).'&amp;tab=subalbuminfo';
		}
	} else {
		$r = '/admin-edit.php?page=edit&amp;album='.urlencode($ret);
	}
} else {
	$r = '/admin.php';
}
echo '<p><a href="'.WEBPATH.'/'.ZENFOLDER.$r.'">&laquo; '.gettext("Back").'</a></p>';
echo "\n" . '</div>';
echo "\n" . '</div>';

printAdminFooter();

echo "\n</body>";
echo "\n</head>";
?>
