<?php
/**
 * Static HTML Cache
 *
 * Used to cache Theme pages (i.e. those pages launched by the Zenphoto index.php script.)
 *
 * Exceptions to this are the password.php and 404.php pages, any page listed in the
 * Excluded pages option, and any page whose script makes a call on the
 * static_cache_html_disable_cache() function. NOTE: this function only prevents the
 * creation of a cache image of the page being viewed. If there is already an existing
 * cached page and none of the other exclusions are in effect, the cached page will be
 * shown.
 *
 * In addition, caching does not occur for pages viewed by Zenphoto users if the user has
 * ADMIN privileges or if he is the manager of an album being viewed or whose images are
 * being viewed. Likewise, Zenpage News and Pages are not cached when viewed by the author.
 *
 * @author Malte Müller (acrylian)
 * @package plugins
 */

if (!defined('OFFSET_PATH')) define('OFFSET_PATH', 3);
require_once(dirname(dirname(__FILE__)).'/functions.php');

$plugin_is_filter = 5;
$plugin_description = gettext("Adds static HTML cache functionality to Zenphoto.");
$plugin_author = "Malte Müller (acrylian)";
$plugin_version = '1.3.1';
$plugin_URL = "http://www.zenphoto.org/documentation/plugins/_".PLUGIN_FOLDER."---static_html_cache.php.html";
$option_interface = new staticCache();
$_zp_HTML_cache = $option_interface; // register as the HTML cache handler

zp_register_filter('admin_utilities_buttons', 'static_cache_html_purgebutton');

// insure that we have the folders available for the cache
if (!defined('STATIC_CACHE_FOLDER')) {
	define("STATIC_CACHE_FOLDER","cache_html");
}

$cache_path = SERVERPATH.'/'.STATIC_CACHE_FOLDER."/";
if (!file_exists($cache_path)) {
	if (!mkdir($cache_path, CHMOD_VALUE)) {
		die(gettext("Static HTML Cache folder could not be created. Please try to create it manually via FTP with chmod 0777."));
	}
}
$cachesubfolders = array("index", "albums","images","pages");
foreach($cachesubfolders as $cachesubfolder) {
	$folder = $cache_path.$cachesubfolder.'/';
	if (!file_exists($folder)) {
		if(!mkdir($folder, CHMOD_VALUE)) {
			die(gettext("Static HTML Cache folder could not be created. Please try to create it manually via FTP with chmod 0777."));
		}
	}
}

if (isset($_GET['action']) && $_GET['action']=='clear_html_cache' && zp_loggedin(ADMIN_RIGHTS)) {
	$_zp_HTML_cache->clearHTMLCache();
	header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin.php?action=external&msg='.gettext('HTML cache cleared.'));
	exit();
}

/**
 * Plugin option handling class
 *
 */
class staticCache {

	var $startmtime;
	var $disable = false; // manual disable caching a page

	function staticCache() {
		setOptionDefault('static_cache_expire', 86400);
		setOptionDefault('static_cache_excludedpages', 'search.php/,contact.php/,register.php/');
	}

	function getOptionsSupported() {
		return array(	gettext('Static HTML cache expire') => array('key' => 'static_cache_expire', 'type' => OPTION_TYPE_TEXTBOX,
										'desc' => gettext("When the cache should expire in seconds. Default is 86400 seconds (1 day  = 24 hrs * 60 min * 60 sec).")),
									gettext('Excluded pages') => array('key' => 'static_cache_excludedpages', 'type' => OPTION_TYPE_TEXTBOX,
										'desc' => gettext("The list of pages to excluded from cache generation. Pages that can be excluded are custom theme pages including Zenpage pages (these optionally more specific by titlelink) and the standard theme files image.php (optionally by image file name), album.php (optionally by album folder name) or index.php.<br /> If you want to exclude a page completely enter <em>page-filename.php/</em>. <br />If you want to exclude a page by a specific title, image filename, or album folder name enter <em>pagefilename.php/titlelink or image filename or album folder</em>. Separate several entries by comma.")),
		);
	}

	function handleOption($option, $currentValue) {
	}

	/**
	 * Checks if the current page should be excluded from caching.
	 * Pages that can be excluded are custom pages included Zenpage pages (these optionally more specific by titlelink)
	 * and the standard theme pages image.php (optionally by image file name), album.php (optionally by album folder name)
	 * or index.php
	 *
	 * @return bool
	 *
	 */
	function checkIfAllowedPage() {
		global $_zp_gallery_page, $_zp_current_image, $_zp_current_album, $_zp_current_zenpage_page,
						$_zp_current_zenpage_news, $_zp_current_admin_obj;
		if ($this->disable || zp_loggedin(ADMIN_RIGHTS)) {
			return false;	// don't cache pages the admin views!
		}
		if(!isset($_GET['p'])) {
			switch ($_zp_gallery_page) {
				case "index.php":
					$title = "";
					break;
				case "image.php": // does it really makes sense to exclude images and albums?
					if (zp_loggedin() && isMyAlbum($_zp_current_album->name, LIST_ALBUM_RIGHTS)) {
						return true; // it is his album, no caching!
					}
					$title = $_zp_current_image->filename;
					break;
				case "album.php":
					if (zp_loggedin() && isMyAlbum($_zp_current_album->name, LIST_ALBUM_RIGHTS)) {
						return true; // it is his album, no caching!
					}
					$title = $_zp_current_album->name;
					break;
				case ZENPAGE_PAGES.'.php':
					if (zp_loggedin() && $_zp_current_admin_obj->getUser() == $_zp_current_zenpage_page->getAuthor()) {
						return false;	// don't cache author's pages
					}

					break;
				case ZENPAGE_NEWS.'.php':
					if (zp_loggedin() && $_zp_current_admin_obj->getUser() == $_zp_current_zenpage_news->getAuthor()) {
						return false;	// don't cache author's pages
					}
					break;
			}
		} else {
			if(isset($_GET['title'])) {
				$title = sanitize($_GET['title']);
			} else {
				$title = "";
			}
		}
		$pages = getOption("static_cache_excludedpages");
		$excludeList = explode(",",$pages);
		foreach($excludeList as $item) {
			$page_to_exclude = explode("/",$item);
			if ($_zp_gallery_page === trim($page_to_exclude[0])) {
				$exclude = trim($page_to_exclude[1]);
				if(empty($exclude) || $title === $exclude) {
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * Starts the caching: Gets either an already cached file if existing or starts the output buffering.
	 *
	 * Place this function on zenphoto's root index.php file in line 75 right after the plugin loading loop
	 *
	 */
	function startHTMLCache() {
		global $_zp_gallery_page;
		if($this->checkIfAllowedPage()) {
			$cachefilepath = $this->createCacheFilepath();
			if (!empty($cachefilepath)) {
				$this->startmtime = microtime(true);
				$cachefilepath = STATIC_CACHE_FOLDER."/".$cachefilepath;
				if(file_exists($cachefilepath) AND !isset($_POST['comment']) AND time()-filemtime($cachefilepath) < getOption("static_cache_expire")) { // don't use cache if comment is posted
					echo file_get_contents($cachefilepath); // PHP >= 4.3
					$end = microtime(true); $final = $end - $this->startmtime; $final = round($final,4);
					echo "\n<!-- ".sprintf(gettext("Cached content served by static_html_cache in %u seconds"),$final)." -->";
					exit();
				} else {
					$this->deleteStaticCacheFile($cachefilepath);
					ob_start();
				}
			}
		}
	}

	/**
	 * Ends the caching: Ends the output buffering  and writes the html cache file from the buffer
	 *
	 * Place this function on zenphoto's root index.php file in the absolute last line
	 *
	 */
	function endHTMLCache() {
		if($this->checkIfAllowedPage()) {
			$cachefilepath = $this->createCacheFilepath();
			if(!empty($cachefilepath)) {
				$cachefilepath = STATIC_CACHE_FOLDER."/".$cachefilepath;
				// Display speed information.
				$end = microtime(true); $final = $end - $this->startmtime; $final = round($final, 4);
				echo "\n<!-- ".sprintf(gettext("Content generated dynamically in %u seconds"),$final)." -->";
				// End
				$pagecontent = ob_get_clean();
				if ($fh = fopen($cachefilepath,"w")) {
					fputs($fh, $pagecontent);
					fclose($fh);
				}
				echo $pagecontent;
			}
		}
		@ob_end_clean(); // clean up any dangling output buffer
	}

	/**
	 * Creates the path and filename of the page to be cached.
	 *
	 * @return string
	 */
	function createCacheFilepath() {
		global $_zp_current_image, $_zp_current_album, $_zp_gallery_page;

		// just make sure these are really empty
		$cachefilepath = "";
		$album = "";
		$image = "";
		$searchfields = "";
		$words = "";
		$date = "";
		$title = ""; // zenpage support
		$category = ""; // zenpage support

		// get page number
		if(isset($_GET['page'])) {
			$page = "_".sanitize($_GET['page']);
		} else {
			$page = "_1";
		}
		if(isset($_REQUEST['locale'])) {
			$locale = "_".sanitize($_REQUEST['locale']);
		} else {
			$locale = "_".sanitize(getOption("locale"));
		}

		// index.php
		if($_zp_gallery_page === "index.php") {
			$cachesubfolder = "index";
			$cachefilepath = $cachesubfolder."/index".$page.$locale.".html";
		}

		// album.php/image.php
		if($_zp_gallery_page === "album.php" OR $_zp_gallery_page === "image.php") {
			$cachesubfolder = "albums";
			$album = $_zp_current_album->name;
			$album = str_replace("/","_",$album);
			if(isset($_zp_current_image)) {
				$cachesubfolder = "images";
				$image = "-".$_zp_current_image->filename;
				$page = "";
			}
			$cachefilepath = $cachesubfolder."/".$album.$image.$page.$locale.".html";
		}

		// custom pages except error page and search
		if(isset($_GET['p'])) {
			$cachesubfolder = "pages";
			$custompage = $_zp_gallery_page;
			if(isset($_GET['title'])) {
				$title = "-".sanitize($_GET['title']);
			}
			if(isset($_GET['category'])) {
				$category = "-".sanitize($_GET['category']);
			}
			$cachefilepath = $cachesubfolder."/".$custompage.$category.$title.$page.$locale.".html";
		}
		return $cachefilepath;
	}

	/**
	 * Deletes a cache file
	 *
	 * @param string $cachefilepath Path to the cache file to be deleted
	 */
	function deleteStaticCacheFile($cachefilepath) {
		if(file_exists($cachefilepath)) {
			@unlink($cachefilepath);
		}
	}

	/**
	 * Cleans out the cache folder. (Adpated from the zenphoto image cache)
	 *
	 * @param string $cachefolder the sub-folder to clean
	 */
	function clearHTMLCache($folder='') {
		$cachesubfolders = array("index", "albums","images","pages");
		foreach($cachesubfolders as $cachesubfolder) {
			$cachefolder = "../../".STATIC_CACHE_FOLDER."/".$cachesubfolder;
			if (is_dir($cachefolder)) {
				$handle = opendir($cachefolder);
				while (false !== ($filename = readdir($handle))) {
					$fullname = $cachefolder . '/' . $filename;
					if (is_dir($fullname) && !(substr($filename, 0, 1) == '.')) {
						if (($filename != '.') && ($filename != '..')) {
							$this->clearHTMLCache($fullname);
							rmdir($fullname);
						}
					} else {
						if (file_exists($fullname) && !(substr($filename, 0, 1) == '.')) {
							unlink($fullname);
						}
					}
				}
				closedir($handle);
			}
		}
		//clearstatcache();
	}
} // class

/**
 * creates the Utilities button to purge the static html cache
 * @param array $buttons
 * @return array
 */
function static_cache_html_purgebutton($buttons) {
	$buttons[] = array(
								'enable'=>true,
								'button_text'=>gettext('Purge HTML cache'),
								'formname'=>'clearcache_button',
								'action'=>PLUGIN_FOLDER.'/static_html_cache.php?action=clear_html_cache',
								'icon'=>'images/edit-delete.png',
								'title'=>gettext('Clear the static HTML cache. HTML pages will be re-cached as they are viewed.'),
								'alt'=>'',
								'hidden'=> '<input type="hidden" name="action" value="clear_html_cache">',
								'rights'=> ADMIN_RIGHTS
								);
	return $buttons;
}

/**
 * call to disable caching a page
 */
function static_cache_html_disable_cache() {
	global $_zp_HTML_cache;
	if(is_object($_zp_HTML_cache)) {
		$_zp_HTML_cache->disable = true;
	}
}

?>