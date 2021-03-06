<?php
/**
 * image_album_statistics -- support functions for "statistics" about images and albums.
 *
 * Supports such statistics as "most popular", "latest", "top rated", etc.
 *
 * C A U T I O N: With 1.0.4.7 the usage to get an specific album changes. You now have to pass the foldername of an album instead the album title.
 *
 * @author Malte Müller (acrylian), Stephen Billard (sbillard)
 * @package plugins
 */

$plugin_description = gettext("Functions that provide various statistics about images and albums in the gallery.");
$plugin_author = "Malte Müller (acrylian), Stephen Billard (sbillard)";
$plugin_version = '1.3.1'; 
$plugin_URL = "http://www.zenphoto.org/documentation/plugins/_".PLUGIN_FOLDER."---image_album_statistics.php.html";

/**
 * Retuns a list of album statistic accordingly to $option
 *
 * @param int $number the number of albums to get
 * @param string $option "popular" for the most popular albums,
 *     "latest" for the latest uploaded, "mostrated" for the most voted,
 *     "toprated" for the best voted
 * 		 "latestupdated" for the latest updated
 *	@param string $albumfolder The name of an album to get only the statistc for its subalbums
 * @return string
 */
function getAlbumStatistic($number=5, $option, $albumfolder='') {
	$passwordcheck = '';
	if (zp_loggedin()) {
		$albumWhere = "WHERE `dynamic`=0";
	} else {
		$albumscheck = query_full_array("SELECT * FROM " . prefix('albums'). " ORDER BY title");
		foreach($albumscheck as $albumcheck) {
			if(!checkAlbumPassword($albumcheck['folder'], $hint)) {
				$albumpasswordcheck= " AND id != ".$albumcheck['id'];
				$passwordcheck = $passwordcheck.$albumpasswordcheck;
			}
		}
		$albumWhere = "WHERE `dynamic`=0 AND `show`=1".$passwordcheck;
	}
	$albumfolder = sanitize_path($albumfolder);
	if(!empty($albumfolder)) {
		$albumWhere .= " AND folder LIKE '".zp_escape_string($albumfolder)."/%'";
	} 
	switch($option) {
		case "popular":
			$sortorder = "hitcounter";
			break;
		case "latest":
			$sortorder = "id";
			break;
		case "mostrated":
			$sortorder = "total_votes"; break;
		case "toprated":
			$sortorder = "(total_value/total_votes)"; break;
		case "latestupdated":
			// get all albums
			$allalbums = query_full_array("SELECT id, title, folder, thumb, `show` FROM " . prefix('albums'). $albumWhere);
			$latestimages = array();

			// get latest image of each album
			foreach($allalbums as $key=>$album) {
				$image = query_single_row("SELECT id, albumid, mtime FROM " . prefix('images'). " WHERE albumid = ".$album['id'] . " AND `show` = 1 ORDER BY `mtime` DESC LIMIT 1");
				if (is_array($image)) {
					$latestimages[$key] = $image['mtime'];
				}
			}
			// sort latest image by mtime
			arsort($latestimages);
			$updatedalbums = array();
			$count = 0;
			foreach($latestimages as $key=>$time) {
				array_push($updatedalbums,$allalbums[$key]);
				$count++;
				if ($count>=$number) break;
			}
			return $updatedalbums;
	}
	$albums = query_full_array("SELECT id, title, folder, thumb FROM " . prefix('albums') . $albumWhere . " ORDER BY ".$sortorder." DESC LIMIT ".zp_escape_string($number));
	return $albums;
}

/**
 * Prints album statistic according to $option as an unordered HTML list
 * A css id is attached by default named '$option_album'
 *
 * @param string $number the number of albums to get
 * @param string $option "popular" for the most popular albums,
 *                  "latest" for the latest uploaded,
 *                  "mostrated" for the most voted,
 *                  "toprated" for the best voted
 * 									"latestupdated" for the latest updated
 * @param bool $showtitle if the album title should be shown
 * @param bool $showdate if the album date should be shown
 * @param bool $showdesc if the album description should be shown
 * @param integer $desclength the length of the description to be shown
 * @param string $showstatistic "hitcounter" for showing the hitcounter (views),
 * 															"rating" for rating,
 * 															"rating+hitcounter" for both.
 * @param integer $width the width/cropwidth of the thumb if crop=true else $width is longest size. (Default 85px)
 * @param integer $height the height/cropheight of the thumb if crop=true else not used.  (Default 85px)
 * @param bool $crop 'true' (default) if the thumb should be cropped, 'false' if not
 */
function printAlbumStatistic($number, $option, $showtitle=false, $showdate=false, $showdesc=false, $desclength=40,$showstatistic='',$width=85,$height=85,$crop=true,$albumfolder='',$firstimglink=false) {
	$albums = getAlbumStatistic($number, $option,$albumfolder);
	echo "\n<div id=\"".$option."_album\">\n";
	echo "<ul>";
	foreach($albums as $album) {
		printAlbumStatisticItem($album, $option,$showtitle, $showdate, $showdesc, $desclength,$showstatistic,$width,$height,$crop,$firstimglink);
	}
	echo "</ul></div>\n";
}

/**
 * A helper function that only prints a item of the loop within printAlbumStatistic()
 * Not for standalone use.
 *
 * @param array $album the array that getAlbumsStatistic() submitted
 * @param string $option "popular" for the most popular albums,
 *                  "latest" for the latest uploaded,
 *                  "mostrated" for the most voted,
 *                  "toprated" for the best voted
 * 									"latestupdated" for the latest updated
 * @param bool $showtitle if the album title should be shown
 * @param bool $showdate if the album date should be shown
 * @param bool $showdesc if the album description should be shown
 * @param integer $desclength the length of the description to be shown
 * @param string $showstatistic "hitcounter" for showing the hitcounter (views),
 * 															"rating" for rating,
 * 															"rating+hitcounter" for both.
 * @param integer $width the width/cropwidth of the thumb if crop=true else $width is longest size. (Default 85px)
 * @param integer $height the height/cropheight of the thumb if crop=true else not used.  (Default 85px)
 * @param bool $crop 'true' (default) if the thumb should be cropped, 'false' if not
 * @param bool $firstimglink 'false' (default) if the album thumb link should lead to the album page, 'true' if to the first image of theh album if the album itself has images 
 */
function printAlbumStatisticItem($album, $option, $showtitle=false, $showdate=false, $showdesc=false, $desclength=40,$showstatistic='',$width=85,$height=85,$crop=true,$firstimglink=false) {
	global $_zp_gallery;
	$tempalbum = new Album($_zp_gallery, $album['folder']);
	if($firstimglink && $tempalbum->getNumImages() != 0) {
		$firstimage = $tempalbum->getImages(1); // need only the first so don't get all
		$firstimage = $firstimage[0];
		echo $firstimage;
		$modrewritesuffix = getOption('mod_rewrite_image_suffix');
		$imagepath = htmlspecialchars(rewrite_path("/".$firstimage.$modrewritesuffix,"&amp;image=".$firstimage,false));
	} else {
		$imagepath = "";
	}
	$albumpath = htmlspecialchars(rewrite_path("/".pathurlencode($tempalbum->name).$imagepath, "index.php?album=".pathurlencode($tempalbum->name).$imagepath));
	echo "<li><a href=\"".$albumpath."\" title=\"" . html_encode($tempalbum->getTitle()) . "\">\n";
	$albumthumb = $tempalbum->getAlbumThumbImage();
	$thumb = newImage($tempalbum, $albumthumb->filename);
	if($crop) {
		echo "<img src=\"".htmlspecialchars($albumthumb->getCustomImage(NULL, $width, $height, $width, $height, NULL, NULL, TRUE))."\" alt=\"" . html_encode($albumthumb->getTitle()) . "\" /></a>\n<br />";
	} else {
		echo "<img src=\"".htmlspecialchars($albumthumb->getCustomImage($width, NULL, NULL, NULL, NULL, NULL, NULL, TRUE))."\" alt=\"" . html_encode($albumthumb->getTitle()) . "\" /></a>\n<br />";
	}
	if($showtitle) {
		echo "<h3><a href=\"".$albumpath."\" title=\"" . html_encode($tempalbum->getTitle()) . "\">\n";
		echo $tempalbum->getTitle()."</a></h3>\n";
	}
	if($showdate) {
		if($option === "latestupdated") {
			$filechangedate = filectime(getAlbumFolder().internalToFilesystem($tempalbum->name));
			$latestimage = query_single_row("SELECT mtime FROM " . prefix('images'). " WHERE albumid = ".$tempalbum->getAlbumID() . " AND `show` = 1 ORDER BY id DESC");
			$lastuploaded = query("SELECT COUNT(*) FROM ".prefix('images')." WHERE albumid = ".$tempalbum->getAlbumID() . " AND mtime = ". $latestimage['mtime']);
			$row = mysql_fetch_row($lastuploaded);
			$count = $row[0];
			echo "<p>".sprintf(gettext("Last update: %s"),zpFormattedDate(getOption('date_format'),$filechangedate))."</p>";
			if($count <= 1) {
				$image = gettext("image");
			} else {
				$image = gettext("images");
			}
			echo "<span>".sprintf(gettext('%1$u new %2$s'),$count,$image)."</span>";
		} else {
			echo "<p>". zpFormattedDate(getOption('date_format'),strtotime($tempalbum->getDateTime()))."</p>";
		}
	}
	if($showstatistic === "rating" OR $showstatistic === "rating+hitcounter") {
		$votes = $tempalbum->get("total_votes");
		$value = $tempalbum->get("total_value");
		if($votes != 0) {
			$rating =  round($value/$votes, 1);
		}
		echo "<p>".sprintf(gettext('Rating: %1$u (Votes: %2$u)'),$rating,$tempalbum->get("total_votes"))."</p>";
	}
	if($showstatistic === "hitcounter" OR $showstatistic === "rating+hitcounter") {
		$hitcounter = $tempalbum->get("hitcounter");
		if(empty($hitcounter)) { $hitcounter = "0"; }
		echo "<p>".sprintf(gettext("Views: %u"),$hitcounter)."</p>";
	}
	if($showdesc) {
		echo "<p>".truncate_string($tempalbum->getDesc(), $desclength)."</p>";
	}
	echo "</li>";
}

/**
 * Prints the most popular albums
 *
 * @param string $number the number of albums to get
 * @param bool $showtitle if the album title should be shown
 * @param bool $showdate if the album date should be shown
 * @param bool $showdesc if the album description should be shown
 * @param integer $desclength the length of the description to be shown
 * @param string $showstatistic "hitcounter" for showing the hitcounter (views),
 * 															"rating" for rating,
 * 															"rating+hitcounter" for both.
 * @param integer $width the width/cropwidth of the thumb if crop=true else $width is longest size. (Default 85px)
 * @param integer $height the height/cropheight of the thumb if crop=true else not used.  (Default 85px)
 * @param bool $crop 'true' (default) if the thumb should be cropped, 'false' if not
 * @param bool $firstimglink 'false' (default) if the album thumb link should lead to the album page, 'true' if to the first image of theh album if the album itself has images 
 */
function printPopularAlbums($number=5,$showtitle=false, $showdate=false, $showdesc=false, $desclength=40,$showstatistic='hitcounter',$width=85,$height=85,$crop=true,$albumfolder='',$firstimglink=false) {
	printAlbumStatistic($number,"popular",$showtitle, $showdate, $showdesc, $desclength,$showstatistic,$width,$height,$crop,$albumfolder,$firstimglink);
}

/**
 * Prints the latest albums
 *
 * @param string $number the number of albums to get
 * @param bool $showtitle if the album title should be shown
 * @param bool $showdate if the album date should be shown
 * @param bool $showdesc if the album description should be shown
 * @param integer $desclength the length of the description to be shown
 * @param string $showstatistic "hitcounter" for showing the hitcounter (views),
 * 															"rating" for rating,
 * 															"rating+hitcounter" for both.
 * @param integer $width the width/cropwidth of the thumb if crop=true else $width is longest size. (Default 85px)
 * @param integer $height the height/cropheight of the thumb if crop=true else not used.  (Default 85px)
 * @param bool $crop 'true' (default) if the thumb should be cropped, 'false' if not
 * @param bool $firstimglink 'false' (default) if the album thumb link should lead to the album page, 'true' if to the first image of theh album if the album itself has images 
 */
function printLatestAlbums($number=5,$showtitle=false, $showdate=false, $showdesc=false, $desclength=40,$showstatistic='',$width=85,$height=85,$crop=true,$albumfolder='',$firstimglink=false) {
	printAlbumStatistic($number,"latest",$showtitle, $showdate, $showdesc, $desclength,$showstatistic,$width,$height,$crop,$albumfolder,$firstimglink);
}

/**
 * Prints the most rated albums
 *
 * @param string $number the number of albums to get
 * @param bool $showtitle if the album title should be shown
 * @param bool $showdate if the album date should be shown
 * @param bool $showdesc if the album description should be shown
 * @param integer $desclength the length of the description to be shown
 * @param string $showstatistic "hitcounter" for showing the hitcounter (views),
 * 															"rating" for rating,
 * 															"rating+hitcounter" for both.
 * @param integer $width the width/cropwidth of the thumb if crop=true else $width is longest size. (Default 85px)
 * @param integer $height the height/cropheight of the thumb if crop=true else not used.  (Default 85px)
 * @param bool $crop 'true' (default) if the thumb should be cropped, 'false' if not
 * @param bool $firstimglink 'false' (default) if the album thumb link should lead to the album page, 'true' if to the first image of theh album if the album itself has images 
 */
function printMostRatedAlbums($number=5,$showtitle=false, $showdate=false, $showdesc=false, $desclength=40,$showstatistic='',$width=85,$height=85,$crop=true,$albumfolder='',$firstimglink=false) {
	printAlbumStatistic($number,"mostrated",$showtitle, $showdate, $showdesc, $desclength,$showstatistic,$width,$height,$crop,$albumfolder,$firstimglink);
}

/**
 * Prints the top voted albums
 *
 * @param string $number the number of albums to get
 * @param bool $showtitle if the album title should be shown
 * @param bool $showdate if the album date should be shown
 * @param bool $showdesc if the album description should be shown
 * @param integer $desclength the length of the description to be shown
 * @param string $showstatistic "hitcounter" for showing the hitcounter (views),
 * 															"rating" for rating,
 * 															"rating+hitcounter" for both.
 * @param integer $width the width/cropwidth of the thumb if crop=true else $width is longest size. (Default 85px)
 * @param integer $height the height/cropheight of the thumb if crop=true else not used.  (Default 85px)
 * @param bool $crop 'true' (default) if the thumb should be cropped, 'false' if not
 * @param bool $firstimglink 'false' (default) if the album thumb link should lead to the album page, 'true' if to the first image of theh album if the album itself has images 
 */
function printTopRatedAlbums($number=5,$showtitle=false, $showdate=false, $showdesc=false, $desclength=40,$showstatistic='',$width=85,$height=85,$crop=true,$albumfolder='',$firstimglink=false) {
	printAlbumStatistic($number,"toprated",$showtitle, $showdate, $showdesc, $desclength,$showstatistic,$width,$height,$crop,$albumfolder,$firstimglink);
}

/**
 * Prints the top voted albums
 *
 * @param string $number the number of albums to get
 * @param bool $showtitle if the album title should be shown
 * @param bool $showdate if the album date should be shown
 * @param bool $showdesc if the album description should be shown
 * @param integer $desclength the length of the description to be shown
 * @param string $showstatistic "hitcounter" for showing the hitcounter (views),
 * 															"rating" for rating,
 * 															"rating+hitcounter" for both.
 * @param integer $width the width/cropwidth of the thumb if crop=true else $width is longest size. (Default 85px)
 * @param integer $height the height/cropheight of the thumb if crop=true else not used.  (Default 85px)
 * @param bool $crop 'true' (default) if the thumb should be cropped, 'false' if not
 * @param bool $firstimglink 'false' (default) if the album thumb link should lead to the album page, 'true' if to the first image of theh album if the album itself has images 
 */
function printLatestUpdatedAlbums($number=5,$showtitle=false, $showdate=false, $showdesc=false, $desclength=40,$showstatistic='',$width=85,$height=85,$crop=true,$albumfolder='',$firstimglink=false) {
	printAlbumStatistic($number,"latestupdated",$showtitle, $showdate, $showdesc, $desclength,$showstatistic,$width,$height,$crop,$albumfolder,$firstimglink);
}

/**
 * Returns a list of image statistic according to $option
 *
 * @param string $number the number of images to get
 * @param string $option "popular" for the most popular images,
 *                       "latest" for the latest uploaded,
 *                       "latest-date" for the latest uploaded, but fetched by date,
 * 											 "latest-mtime" for the latest uploaded, but fetched by mtime,
 *                       "mostrated" for the most voted,
 *                       "toprated" for the best voted
 * @param string $albumfolder foldername of an specific album
 * @param bool $collection only if $albumfolder is set: true if you want to get statistics from this album and all of its subalbums
 * @return string
 */
function getImageStatistic($number, $option, $albumfolder='',$collection=false) {
	global $_zp_gallery;
	if (zp_loggedin()) {
		$albumWhere = " AND albums.folder != ''";
		$imageWhere = "";
		$passwordcheck = "";
	} else {
		$passwordcheck = '';
		$albumscheck = query_full_array("SELECT * FROM " . prefix('albums'). " ORDER BY title");
		foreach($albumscheck as $albumcheck) {
			if(!checkAlbumPassword($albumcheck['folder'], $hint)) {
				$albumpasswordcheck= " AND albums.id != ".$albumcheck['id'];
				$passwordcheck = $passwordcheck.$albumpasswordcheck;
			}
		}
		$albumWhere = " AND albums.folder != '' AND albums.show=1".$passwordcheck;
		$imageWhere = " AND images.show=1";
	}
	$is_dynamicalbum = false;
	if(!empty($albumfolder)) {
		$alb = new Album($_zp_gallery,$albumfolder); // create album object for dynamic check
		if($alb->isDynamic()) {
			$is_dynamicalbum = true;
		} 
		if($collection) {
			$specificalbum = " albums.folder LIKE '".zp_escape_string($albumfolder)."%' AND ";
		} else {
			$specificalbum = " albums.folder = '".zp_escape_string($albumfolder)."' AND ";
		}
	} else {
		$specificalbum = "";
	}
	switch ($option) {
		case "popular":
			$sortorder = "images.hitcounter"; break;
		case "latest-date":
			$sortorder = "images.date"; break;
		case "latest-mtime":
			$sortorder = "images.mtime"; break;
		case "latest":
			$sortorder = "images.id"; break;
		case "mostrated":
			$sortorder = "images.total_votes"; break;
		case "toprated":
			$sortorder = "(images.total_value/images.total_votes)"; break;
		default: 
			$sortorder = 'id'; break;
	}
	$imageArray = array();
	if(!empty($albumfolder) AND $is_dynamicalbum) {
		for( $i = 0, $len = $number; $i < $len; $i++) {
			array_push($imageArray, $alb->getImage($i));
		}
	} else { 
		$images = query_full_array("SELECT images.albumid, images.filename AS filename, images.mtime as mtime, images.title AS title, " .
 															"albums.folder AS folder, images.show, albums.show, albums.password FROM " .
		prefix('images') . " AS images, " . prefix('albums') . " AS albums " .
															" WHERE ".$specificalbum."images.albumid = albums.id " . $imageWhere . $albumWhere .
															" AND albums.folder != ''".
															" ORDER BY ".$sortorder." DESC LIMIT ".zp_escape_string($number));
		foreach ($images as $imagerow) {
			$filename = $imagerow['filename'];
			$albumfolder2 = $imagerow['folder'];
			$desc = $imagerow['title'];
			// Album is set as a reference, so we can't re-assign to the same variable!
			$image = newImage(new Album($_zp_gallery, $albumfolder2), $filename);
			$imageArray [] = $image;
		}
	}
	
	return $imageArray;
}

/**
 * Prints image statistic according to $option as an unordered HTML list
 * A css id is attached by default named accordingly'$option'
 *
 * @param string $number the number of albums to get
 * @param string $option "popular" for the most popular images,
 *                       "latest" for the latest uploaded,
 *                       "latest-date" for the latest uploaded, but fetched by date,
 * 											 "latest-mtime" for the latest uploaded, but fetched by mtime,
 *                       "mostrated" for the most voted,
 *                       "toprated" for the best voted
 * @param string $albumfolder foldername of an specific album
 * @param bool $showtitle if the image title should be shown
 * @param bool $showdate if the image date should be shown
 * @param bool $showdesc if the image description should be shown
 * @param integer $desclength the length of the description to be shown
 * @param string $showstatistic "hitcounter" for showing the hitcounter (views),
 * 															"rating" for rating,
 * 															"rating+hitcounter" for both.
 * @param integer $width the width/cropwidth of the thumb if crop=true else $width is longest size. (Default 85px)
 * @param integer $height the height/cropheight of the thumb if crop=true else not used.  (Default 85px)
 * @param bool $crop 'true' (default) if the thumb should be cropped, 'false' if not
 * @param bool $collection only if $albumfolder is set: true if you want to get statistics from this album and all of its subalbums
 * 
 * @return string
 */
function printImageStatistic($number, $option, $albumfolder='', $showtitle=false, $showdate=false, $showdesc=false, $desclength=40,$showstatistic='',$width=85,$height=85,$crop=true,$collection=false) {
	$images = getImageStatistic($number, $option, $albumfolder,$collection);
	echo "\n<div id=\"$option\">\n";
	echo "<ul>";
	foreach ($images as $image) {
		echo "<li><a href=\"" . htmlspecialchars($image->getImageLink())."\" title=\"" . html_encode($image->getTitle()) . "\">\n";
		if($crop) {
			echo "<img src=\"".htmlspecialchars($image->getCustomImage(NULL, $width, $height, $width, $height, NULL, NULL, TRUE))."\" alt=\"" . html_encode($image->getTitle()) . "\" /></a>\n";
		} else {
			echo "<img src=\"".htmlspecialchars($image->getCustomImage($width, NULL, NULL, NULL, NULL, NULL, NULL, TRUE))."\" alt=\"" . html_encode($image->getTitle()) . "\" /></a>\n";
		}
		if($showtitle) {
			echo "<h3><a href=\"".htmlspecialchars($image->getImageLink())."\" title=\"" . html_encode($image->getTitle()) . "\">\n";
			echo $image->getTitle()."</a></h3>\n";
		}
		if($showdate) {
			echo "<p>". zpFormattedDate(getOption('date_format'),strtotime($image->getDateTime()))."</p>";
		}
		if($showstatistic === "rating" OR $showstatistic === "rating+hitcounter") {
			$votes = $image->get("total_votes");
			$value = $image->get("total_value");
			if($votes != 0) {
				$rating =  round($value/$votes, 1);
			}
			echo "<p>".sprintf(gettext('Rating: %1$u (Votes: %2$u)'),$rating,$votes)."</p>";
		}
		if($showstatistic === "hitcounter" OR $showstatistic === "rating+hitcounter") {
			$hitcounter = $image->get("hitcounter");
			if(empty($hitcounter)) { $hitcounter = "0"; }
			echo "<p>".sprintf(gettext("Views: %u"),$hitcounter)."</p>";
		}
		if($showdesc) {
			echo "<p>".truncate_string($image->getDesc(), $desclength)."</p>";
		}
		echo "</li>";
	}
	echo "</ul></div>\n";
}

/**
 * Prints the most popular images
 *
 * @param string $number the number of images to get
 * @param string $albumfolder folder of an specific album
 * @param bool $showtitle if the image title should be shown
 * @param bool $showdate if the image date should be shown
 * @param bool $showdesc if the image description should be shown
 * @param integer $desclength the length of the description to be shown
* @param string $showstatistic "hitcounter" for showing the hitcounter (views),
 * 															"rating" for rating,
 * 															"rating+hitcounter" for both.
 * @param integer $width the width/cropwidth of the thumb if crop=true else $width is longest size. (Default 85px)
 * @param integer $height the height/cropheight of the thumb if crop=true else not used.  (Default 85px)
 * @param bool $crop 'true' (default) if the thumb should be cropped, 'false' if not
 * @param bool $collection only if $albumfolder is set: true if you want to get statistics from this album and all of its subalbums
 */
function printPopularImages($number=5, $albumfolder='', $showtitle=false, $showdate=false, $showdesc=false, $desclength=40,$showstatistic='',$width=85,$height=85,$crop=true,$collection=false) {
	printImageStatistic($number, "popular",$albumfolder, $showtitle, $showdate, $showdesc, $desclength,$showstatistic,$width,$height,$crop,$collection);
}

/**
 * Prints the n top rated images
 *
 * @param int $number The number if images desired
 * @param string $albumfolder folder of an specific album
 * @param bool $showtitle if the image title should be shown
 * @param bool $showdate if the image date should be shown
 * @param bool $showdesc if the image description should be shown
 * @param integer $desclength the length of the description to be shown
 * @param string $showstatistic "hitcounter" for showing the hitcounter (views),
 * 															"rating" for rating,
 * 															"rating+hitcounter" for both.
 * @param integer $width the width/cropwidth of the thumb if crop=true else $width is longest size. (Default 85px)
 * @param integer $height the height/cropheight of the thumb if crop=true else not used.  (Default 85px)
 * @param bool $crop 'true' (default) if the thumb should be cropped, 'false' if not
 * @param bool $collection only if $albumfolder is set: true if you want to get statistics from this album and all of its subalbums
 */
function printTopRatedImages($number=5, $albumfolder="", $showtitle=false, $showdate=false, $showdesc=false, $desclength=40,$showstatistic='',$width=85,$height=85,$crop=true,$collection=false) {
	printImageStatistic($number, "toprated",$albumfolder, $showtitle, $showdate, $showdesc, $desclength,$showstatistic,$width,$height,$crop,$collection);
}


/**
 * Prints the n most rated images
 *
 * @param int $number The number if images desired
 * @param string $albumfolder folder of an specific album
 * @param bool $showtitle if the image title should be shown
 * @param bool $showdate if the image date should be shown
 * @param bool $showdesc if the image description should be shown
 * @param integer $desclength the length of the description to be shown
 * @param string $showstatistic "hitcounter" for showing the hitcounter (views),
 * 															"rating" for rating,
 * 															"rating+hitcounter" for both.
 * @param integer $width the width/cropwidth of the thumb if crop=true else $width is longest size. (Default 85px)
 * @param integer $height the height/cropheight of the thumb if crop=true else not used.  (Default 85px)
 * @param bool $crop 'true' (default) if the thumb should be cropped, 'false' if not
 * @param bool $collection only if $albumfolder is set: true if you want to get statistics from this album and all of its subalbums
 */
function printMostRatedImages($number=5, $albumfolder='', $showtitle=false, $showdate=false, $showdesc=false, $desclength=40,$showstatistic='',$width=85,$height=85,$crop=true,$collection=false) {
	printImageStatistic($number, "mostrated", $albumfolder, $showtitle, $showdate, $showdesc, $desclength, $showstatistic,$width,$height,$crop,$collection);
}

/**
 * Prints the latest images by ID (the order zenphoto recognized the images on the filesystem)
 *
 * @param string $number the number of images to get
 * @param string $albumfolder folder of an specific album
 * @param bool $showtitle if the image title should be shown
 * @param bool $showdate if the image date should be shown
 * @param bool $showdesc if the image description should be shown
 * @param integer $desclength the length of the description to be shown
* @param string $showstatistic "hitcounter" for showing the hitcounter (views),
 * 															"rating" for rating,
 * 															"rating+hitcounter" for both.
 * @param integer $width the width/cropwidth of the thumb if crop=true else $width is longest size. (Default 85px)
 * @param integer $height the height/cropheight of the thumb if crop=true else not used.  (Default 85px)
 * @param bool $crop 'true' (default) if the thumb should be cropped, 'false' if not
 * @param bool $collection only if $albumfolder is set: true if you want to get statistics from this album and all of its subalbums
 */
function printLatestImages($number=5, $albumfolder='', $showtitle=false, $showdate=false, $showdesc=false, $desclength=40, $showstatistic='',$width=85,$height=85,$crop=true,$collection=false) {
	printImageStatistic($number, "latest", $albumfolder, $showtitle, $showdate, $showdesc, $desclength, $showstatistic,$width,$height,$crop,$collection);
}

/**
 * Prints the latest images by date order (date taken order)
 *
 * @param string $number the number of images to get
 * @param string $albumfolder folder of an specific album
 * @param bool $showtitle if the image title should be shown
 * @param bool $showdate if the image date should be shown
 * @param bool $showdesc if the image description should be shown
 * @param integer $desclength the length of the description to be shown
 * @param string $showstatistic "hitcounter" for showing the hitcounter (views),
 * 															"rating" for rating,
 * 															"rating+hitcounter" for both.
 * @param integer $width the width/cropwidth of the thumb if crop=true else $width is longest size. (Default 85px)
 * @param integer $height the height/cropheight of the thumb if crop=true else not used.  (Default 85px)
 * @param bool $crop 'true' (default) if the thumb should be cropped, 'false' if not
 * @param bool $collection only if $albumfolder is set: true if you want to get statistics from this album and all of its subalbums
 */
function printLatestImagesByDate($number=5, $albumfolder='', $showtitle=false, $showdate=false, $showdesc=false, $desclength=40,$showstatistic='',$width=85,$height=85,$crop=true,$collection=false) {
	printImageStatistic($number, "latest-date", $albumfolder, $showtitle, $showdate, $showdesc, $desclength,$showstatistic,$width,$height,$crop,$collection);
}

/**
 * Prints the latest images by mtime order (date uploaded order)
 *
 * @param string $number the number of images to get
 * @param string $albumfolder folder of an specific album
 * @param bool $showtitle if the image title should be shown
 * @param bool $showdate if the image date should be shown
 * @param bool $showdesc if the image description should be shown
 * @param integer $desclength the length of the description to be shown
 * @param string $showstatistic "hitcounter" for showing the hitcounter (views),
 * 															"rating" for rating,
 * 															"rating+hitcounter" for both.
 * @param integer $width the width/cropwidth of the thumb if crop=true else $width is longest size. (Default 85px)
 * @param integer $height the height/cropheight of the thumb if crop=true else not used.  (Default 85px)
 * @param bool $crop 'true' (default) if the thumb should be cropped, 'false' if not
 * @param bool $collection only if $albumfolder is set: true if you want to get statistics from this album and all of its subalbums
 */
function printLatestImagesByMtime($number=5, $albumfolder='', $showtitle=false, $showdate=false, $showdesc=false, $desclength=40,$showstatistic='',$width=85,$height=85,$crop=true,$collection=false) {
	printImageStatistic($number, "latest-date", $albumfolder, $showtitle, $showdate, $showdesc, $desclength,$showstatistic,$width,$height,$crop,$collection);
}



/**
 * A little helper function that checks if an image or album is to be considered 'new' within the time range set in relation to getImageDate()/getAlbumDate()
 * Returns true or false.
 *
 * @param string $mode What to check "image" or "album".
 * @param integer $timerange The time range the item should be considered new. Default is 604800 (unix time seconds = ca. 7 days)
 * @return bool
 */
function checkIfNew($mode="image",$timerange=604800) {
	$currentdate = date("U");
	switch($mode) {
		case "image":
			$itemdate = getImageDate("%s");
			break;
		case "album":
			$itemdate = getAlbumDate("%s");
			break;
	}
	$newcheck = $currentdate - $itemdate;
	if($newcheck < $timerange) {
		return TRUE;
	} else {
		return FALSE;
	}
}
/**
 * Gets the number of all subalbums of all subalbum levels of either the current album or $albumobj
 *
 * @param object $albumobj Optional album object to check
 * @param string $pre Optional text you want to print before the number
 * @return bool
 */
function getNumAllSubalbums($albumobj,$pre='') {
	global $_zp_gallery, $_zp_current_album;
	if(is_null($albumobj)) {
		$albumobj = $_zp_current_album;
	}
	$count = '';
	$albums = getAllAlbums($_zp_current_album);
	if(count($albums) != 0) {
		$count = '';
		foreach ($albums as $album) {
			$count++;
		}
		return $pre.$count;
	} else {
		return false;
	}
}
?>