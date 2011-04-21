<?php

/**
 * Functions used to display content in themes.
 * @package functions
 */

// force UTF-8 Ø
global $_zp_gallery;

require_once(dirname(__FILE__).'/functions.php');
require_once(dirname(__FILE__).'/functions-image.php');
require_once(PHPScript('5.0.0', '_template_functions.php'));

$_zp_conf_vars['images_first_page'] = NULL; // insure it is initialized
$_zp_gallery = new Gallery();

//******************************************************************************
//*** Template Functions *******************************************************
//******************************************************************************

/*** Generic Helper Functions *************/
/******************************************/

/**
 * Returns the zenphoto version string
 */
function getVersion() {
	return ZENPHOTO_VERSION. ' ['.ZENPHOTO_RELEASE. ']';
}

/**
 * Prints the zenphoto version string
 */
function printVersion() {
	echo getVersion();
}

/**
 * Prints the admin edit link for albums if the current user is logged-in

 * Returns true if the user is logged in
 * @param string $text text for the link
 * @param string $before text do display before the link
 * @param string $after  text do display after the link
 * @param string $title Text for the HTML title item
 * @param string $class The HTML class for the link
 * @param string $id The HTML id for the link
 * @return bool
 * @since 1.1
 */
function printAdminLink($text, $before='', $after='', $title=NULL, $class=NULL, $id=NULL) {
	if (zp_loggedin()) {
		echo $before;
		printLink(WEBPATH.'/' . ZENFOLDER . '/admin.php', $text, $title, $class, $id);
		echo $after;
		return true;
	}
	return false;
}

/**
 * Prints the admin edit link for subalbums if the current user is logged-in
 * @param string $text text for the link
 * @param string $before text do display before the link
 * @param string $after  text do display after the link
 * @since 1.1
 */
function printSubalbumAdmin($text, $before='', $after='') {
	global $_zp_current_album, $_zp_themeroot;
	if (zp_loggedin()) {
		echo $before;
		printLink(WEBPATH.'/' . ZENFOLDER . '/admin-edit.php?page=edit&album=' . urlencode($_zp_current_album->name), $text, NULL, NULL, NULL);
		echo $after;
	}
}

/**
 * Print any Javascript required by zenphoto. Every theme should include this somewhere in its <head>.
 */
function zenJavascript() {
	global $_zp_current_album, $_zp_plugin_scripts;
	?>
	<script type="text/javascript" src="<?php echo WEBPATH . "/" . ZENFOLDER; ?>/js/jquery.js"></script>
	<script type="text/javascript" src="<?php echo WEBPATH . "/" . ZENFOLDER; ?>/js/zenphoto.js"></script>
	<script type="text/javascript">
		// <!-- <![CDATA[
		var deleteAlbum1 = "<?php echo gettext("Are you sure you want to delete this entire album?"); ?>";
		var deleteAlbum2 = "<?php echo gettext("Are you Absolutely Positively sure you want to delete the album? THIS CANNOT BE UNDONE!"); ?>";
		var deleteImage = "<?php echo gettext("Are you sure you want to delete the image? THIS CANNOT BE UNDONE!"); ?>";
		var deleteArticle = "<?php echo gettext("Are you sure you want to delete this article? THIS CANNOT BE UNDONE!"); ?>";
		var deletePage = "<?php echo gettext("Are you sure you want to delete this page? THIS CANNOT BE UNDONE!"); ?>";
		// ]]> -->
		</script>
		<?php
		if (getOption('edit_in_place')) {
			if (($rights = zp_loggedin()) & (ADMIN_RIGHTS | ALBUM_RIGHTS)) {
				if (in_context(ZP_ALBUM)) {
					$grant = isMyAlbum($_zp_current_album->name, ALBUM_RIGHTS);
				} else {
					$grant = $rights & ADMIN_RIGHTS;
				}
				if ($grant) {
					?>
					<script type="text/javascript">
						// <!-- <![CDATA[
						var zpstrings = {
							/* Used in jquery.editinplace.js */
							'Save' : "<?php echo gettext('Save'); ?>",
							'Cancel' : "<?php echo gettext('Cancel'); ?>",
							'Saving' : "<?php echo gettext('Saving'); ?>",
							'ClickToEdit' : "<?php echo gettext('Click to edit...'); ?>"
						};
						// ]]> -->
					</script>
					<script type="text/javascript" src="<?php echo WEBPATH . "/" . ZENFOLDER; ?>/js/jquery.editinplace.js"></script>
					<?php
				}
			}
		}

	if (is_array($_zp_plugin_scripts)) {
		foreach ($_zp_plugin_scripts as $script) {
			echo $script."\n";
		}
	}
	zp_apply_filter('output_started','front-end');
}

/**
 * Prints the clickable drop down toolbox on any theme page with generic admin helpers
 * @param string $id the html/css theming id
 */
function printAdminToolbox($id='admin') {
	global $_zp_current_album, $_zp_current_image, $_zp_current_search, $_zp_gallery_page;
	if (zp_loggedin()) {
		$zf = WEBPATH."/".ZENFOLDER;
		$dataid = $id . '_data';
		$page = getCurrentPage();
		$redirect = '';
		?>
		<script type="text/javascript">
			// <!-- <![CDATA[
			function newAlbum(folder,albumtab) {
				var album = prompt('<?php echo gettext('New album name?'); ?>', '<?php echo gettext('new album'); ?>');
				if (album) {
					launchScript('<?php echo $zf; ?>/admin-edit.php',['action=newalbum','album='+encodeURIComponent(folder),'name='+encodeURIComponent(album),'albumtab='+albumtab,'XSRFToken=<?php echo getXSRFToken('newalbum'); ?>']);
				}
			}
			// ]]> -->
		</script>
		<?php
		echo '<div id="' .$id. '">'."\n".'<h3><a href="javascript:toggle('. "'" .$dataid."'".');">'.gettext('Admin Toolbox').'</a></h3>'."\n"."\n</div>";
		echo '<div id="' .$dataid. '" style="display: none;">'."\n";

		// open the list--all links go between here and the close of the list below
		echo "<ul style='list-style-type: none;'>";

		// generic link to Admin.php
		echo "<li>";
		printAdminLink(gettext('Admin'), '', "</li>\n");
		// setup for return links
		if (isset($_GET['p'])) {
			$redirect = "&amp;p=" . $_GET['p'];
		}
		if ($page>1) {
			$redirect .= "&amp;page=$page";
		}

		if (zp_loggedin(OPTIONS_RIGHTS)) {
		// options link for all admins with options rights
			echo "<li>";
			printLink($zf . '/admin-options.php?tab=general', gettext("Options"), NULL, NULL, NULL);
			echo "</li>\n";
		}
		zp_apply_filter('admin_toolbox_global');

		$gal = getOption('custom_index_page');
		if (empty($gal) || !file_exists(SERVERPATH.'/'.THEMEFOLDER.'/'.getOption('current_theme').'/'.internalToFilesystem($gal).'.php')) {
			$gal = 'index.php';
		} else {
			$gal .= '.php';
		}
		if ($_zp_gallery_page === $gal) {
		// script is either index.php or the gallery index page
			if (zp_loggedin(ALBUM_RIGHTS)) {
				// admin has edit rights so he can sort the gallery (at least those albums he is assigned)
				?>
				<li>
				<?php echo printLink($zf . '/admin-edit.php?page=edit', gettext("Sort Gallery"), NULL, NULL, NULL); ?>
				</li>
				<?php
			}
			if (zp_loggedin(UPLOAD_RIGHTS)) {
				// admin has upload rights, provide an upload link for a new album
				if (getOption('album_session')) { // XSRF defense requires sessions
					?>
					<li>
						<a href="javascript:newAlbum('',true);" ><?php echo gettext("New Album"); ?></a>
					</li>
					<?php
				}
			}
			zp_apply_filter('admin_toolbox_gallery');
		} else if ($_zp_gallery_page === 'album.php') {
		// script is album.php
			$albumname = $_zp_current_album->name;
			if (isMyAlbum($albumname, ALBUM_RIGHTS)) {
				// admin is empowered to edit this album--show an edit link
				echo "<li>";
				printSubalbumAdmin(gettext('Edit album'), '', "</li>\n");
				if (!$_zp_current_album->isDynamic()) {
					if ($_zp_current_album->getNumAlbums()) {
						?>
						<li>
						<?php echo printLink($zf . '/admin-edit.php?page=edit&album=' . urlencode($albumname).'&tab=subalbuminfo', gettext("Sort subalbums"), NULL, NULL, NULL); ?>
						</li>
						<?php
					}
					if ($_zp_current_album->getNumImages()>0) {
						?>
						<li>
						<?php echo printLink($zf . '/admin-albumsort.php?page=edit&album=' . urlencode($albumname).'&tab=sort', gettext("Sort album images"), NULL, NULL, NULL); ?>
						</li>
						<?php
					}
				}
				// and a delete link
				if (getOption('album_session')) { // XSRF defense requires sessions
					?>
					<li>
						<a href="javascript:confirmDeleteAlbum('<?php echo $zf; ?>/admin-edit.php?page=edit&amp;action=deletealbum&amp;album=<?php echo urlencode(urlencode($albumname)) ?>&amp;XSRFToken=<?php echo getXSRFToken('delete'); ?>');"
								title="<?php echo gettext('Delete the album'); ?>"><?php echo gettext('Delete album'); ?></a>
					</li>
					<?php
				}
			}
			if (isMyAlbum($albumname, UPLOAD_RIGHTS) && !$_zp_current_album->isDynamic()) {
				// provide an album upload link if the admin has upload rights for this album and it is not a dynamic album
				?>
				<li>
					<?php echo printLink($zf . '/admin-upload.php?album=' . urlencode($albumname), gettext("Upload Here"), NULL, NULL, NULL); ?>
				</li>
				<?php
				if (getOption('album_session')) { // XSRF defense requires sessions
					?>
					<li>
						<a href="javascript:newAlbum('<?php echo pathurlencode($albumname); ?>',true);" ><?php echo gettext("New Album Here"); ?></a>
					</li>
					<?php
				}
			}
			// set the return to this album/page
			zp_apply_filter('admin_toolbox_album', $albumname);
			$redirect = "&amp;album=".urlencode($albumname)."&amp;page=$page";

		} else if ($_zp_gallery_page === 'image.php') {
			// script is image.php
			if (!$_zp_current_album->isDynamic()) { // don't provide links when it is a dynamic album
				$albumname = $_zp_current_album->name;
				$imagename = $_zp_current_image->filename;
				if (isMyAlbum($albumname, ALBUM_RIGHTS)) {
					// if admin has edit rights on this album, provide a delete link for the image.
					if (getOption('album_session')) { // XSRF defense requires sessions
						?>
						<li><a href="javascript:confirmDelete('<?php echo $zf; ?>/admin-edit.php?page=edit&amp;action=deleteimage&amp;album=<?php  echo urlencode(urlencode($albumname)); ?>&amp;image=<?php  echo urlencode($imagename); ?>&amp;XSRFToken=<?php echo getXSRFToken('delete'); ?>',deleteImage);"
								title="<?php echo gettext("Delete the image"); ?>"><?php  echo gettext("Delete image"); ?></a></li>
						<?php
					}
					?>
					<li><a href="<?php  echo $zf; ?>/admin-edit.php?page=edit&amp;album=<?php  echo urlencode($albumname); ?>&amp;image=<?php  echo urlencode($imagename); ?>&amp;tab=imageinfo#IT"
								title="<?php  echo gettext('Edit this image'); ?>"><?php  echo gettext('Edit image'); ?></a></li>
					<?php
				}
				// set return to this image page
				zp_apply_filter('admin_toolbox_image', $albumname, $imagename);
				$redirect = "&amp;album=".urlencode($albumname)."&amp;image=".urlencode($imagename);
			}
		} else if (($_zp_gallery_page === 'search.php') && !empty($_zp_current_search->words)) {
			// script is search.php with a search string
			if (zp_loggedin(UPLOAD_RIGHTS)) {
				// if admin has edit rights allow him to create a dynamic album from the search
				echo "<li><a href=\"".$zf."/admin-dynamic-album.php\" title=\"".gettext("Create an album from the search")."\">".gettext("Create Album")."</a></li>";
			}
			zp_apply_filter('admin_toolbox_search');
			$redirect = "&amp;p=search" . $_zp_current_search->getSearchParams() . "&amp;page=$page";
		}

		// zenpage script pages
		if(function_exists('is_NewsArticle')) {
				if (is_NewsArticle()) {
					// page is a NewsArticle--provide zenpage edit, delete, and Add links
					$titlelink = getNewsTitlelink();
					$redirect .= '&amp;title='.urlencode($titlelink);
				}
				if (is_Pages()) {
					// page is zenpage page--provide edit, delete, and add links
					$titlelink = getPageTitlelink();
					$redirect .= '&amp;title='.urlencode($titlelink);
				}
				if (zp_loggedin(ZENPAGE_NEWS_RIGHTS)) {
					// admin has zenpage rights, provide link to the Zenpage admin tab
					echo "<li><a href=\"".$zf.'/'.PLUGIN_FOLDER."/zenpage/admin-news-articles.php\">".gettext("News")."</a></li>";
					if (is_NewsArticle()) {
						// page is a NewsArticle--provide zenpage edit, delete, and Add links
						echo "<li><a href=\"".$zf.'/'.PLUGIN_FOLDER."/zenpage/admin-edit.php?newsarticle&amp;edit&amp;titlelink=".urlencode($titlelink)."\">".gettext("Edit Article")."</a></li>";
						if (getOption('album_session')) { // XSRF defense requires sessions
							?>
							<li><a href="javascript:confirmDelete('<?php echo $zf.'/'.PLUGIN_FOLDER; ?>/zenpage/admin-news-articles.php?del=<?php echo getNewsID(); ?>&amp;XSRFToken=<?php echo getXSRFToken('delete'); ?>',deleteArticle)" title="<?php echo gettext("Delete article"); ?>"><?php echo gettext("Delete Article"); ?></a></li>
							<?php
						}
						echo "<li><a href=\"".$zf.'/'.PLUGIN_FOLDER."/zenpage/admin-edit.php?newsarticle&amp;add\">".gettext("Add Article")."</a></li>";
						zp_apply_filter('admin_toolbox_news', $titlelink);
					}
				}
				if (zp_loggedin(ZENPAGE_PAGES_RIGHTS)) {
					echo "<li><a href=\"".$zf.'/'.PLUGIN_FOLDER."/zenpage/admin-pages.php\">".gettext("Pages")."</a></li>";
					if (is_Pages()) {
						// page is zenpage page--provide edit, delete, and add links
						echo "<li><a href=\"".$zf.'/'.PLUGIN_FOLDER."/zenpage/admin-edit.php?page&amp;edit&amp;titlelink=".urlencode($titlelink)."\">".gettext("Edit Page")."</a></li>";
						if (getOption('album_session')) { // XSRF defense requires sessions
							?>
							<li><a href="javascript:confirmDelete('<?php echo $zf.'/'.PLUGIN_FOLDER; ?>/zenpage/page-admin.php?del=<?php echo getPageID(); ?>&amp;XSRFToken=<?php echo getXSRFToken('delete'); ?>',deletePage)" title="<?php echo gettext("Delete page"); ?>"><?php echo gettext("Delete Page"); ?></a></li>
							<?php
						}
						echo "<li><a href=\"".FULLWEBPATH."/".ZENFOLDER.'/'.PLUGIN_FOLDER."/zenpage/admin-edit.php?page&amp;add\">".gettext("Add Page")."</a></li>";
						zp_apply_filter('admin_toolbox_page', $titlelink);
					}
			}
		}

		// logout link
		if (getOption('server_protocol')=='https') $sec=1; else $sec=0;
		echo "<li><a href=\"".$zf."/admin.php?logout={$sec}{$redirect}\">".gettext("Logout")."</a></li>\n";

		// close the list
		echo "</ul>\n";
		echo "</div>\n";
	}
}

//*** Gallery Index (album list) Context ***
//******************************************

/**
 * Returns the raw title of the gallery.
 *
 * @return string
 */
function getGalleryTitle() {
	global $_zp_gallery;
	return $_zp_gallery->getTitle();
}

/**
 * Returns a text-only title of the gallery.
 *
 * @return string
 */
function getBareGalleryTitle() {
	return html_encode(getGalleryTitle());
}

/**
 * Prints the title of the gallery.
 */
function printGalleryTitle() {
	echo getGalleryTitle();
}

/**
 * Returns the raw description of the gallery.
 *
 * @return string
 */
function getGalleryDesc() {
	global $_zp_gallery;
	return $_zp_gallery->getDesc();
}

/**
 * Returns a text-only description of the gallery.
 *
 * @return string
 */
function getBareGalleryDesc() {
	return html_encode(getGalleryDesc());
}

/**
 * Prints the description of the gallery.
 */
function printGalleryDesc() {
	echo getGalleryDesc();
}

/**
 * Returns the name of the main website as set by the "Website Title" option
 * on the gallery options tab.
 *
 * @return string
 */
function getMainSiteName() {
	return get_language_string(getOption('website_title'));
}

/**
 * Returns the URL of the main website as set by the "Website URL" option
 * on the gallery options tab.
 *
 * @return string
 */
function getMainSiteURL() {
	return getOption('website_url');
}

/**
 * Returns the URL of the main gallery page containing the current album
 *
 * @param bool $relative set to false to get the true index page
 * @return string
 */
function getGalleryIndexURL($relative=true) {
	global $_zp_current_album, $_zp_gallery_page;
	if ($relative && ($_zp_gallery_page != 'index.php')  && in_context(ZP_ALBUM)) {
		$album = getUrAlbum($_zp_current_album);
		$page = $album->getGalleryPage();
	} else {
		$page = 0;
	}
	$gallink1 = '';
	$gallink2 = '';
	$specialpage = false;
	if ($relative && $specialpage = getOption('custom_index_page')) {
		if (file_exists(SERVERPATH.'/'.THEMEFOLDER.'/'.getOption('current_theme').'/'.internalToFilesystem($specialpage).'.php')) {
			$gallink1 = $specialpage.'/';
			$gallink2 = 'p='.$specialpage.'&';
		} else {
			$specialpage = false;
		}
	}
	if ($page > 1) {
		return rewrite_path("/page/".$gallink1.$page, "/index.php?".$gallink2."page=".$page);
	} else {
		if ($specialpage) {
			return rewrite_path('/page/'.$gallink1, '?'.substr($gallink2, 0, -1));
		}
		return WEBPATH . "/";
	}
}

/**
 * Returns the number of albums.
 *
 * @return int
 */
function getNumAlbums() {
	global $_zp_gallery, $_zp_current_album, $_zp_current_search;
	if (in_context(ZP_SEARCH) && is_null($_zp_current_album)) {
		return $_zp_current_search->getNumAlbums();
	} else if (in_context(ZP_ALBUM)) {
		return $_zp_current_album->getNumAlbums();
	} else {
		return $_zp_gallery->getNumAlbums();
	}
}

/**
 * Returns the name of the currently active theme
 *
 * @return string
 */
function getCurrentTheme() {
	global $_zp_gallery, $_zp_current_album;
	$theme = $_zp_gallery->getCurrentTheme();
	if (in_context(ZP_ALBUM)) {
		$parent = getUrAlbum($_zp_current_album);
		$albumtheme = $parent->getAlbumTheme();
		if (!empty($albumtheme)) {
			return $albumtheme;
		}
	}
	return $theme;
}

/*** Album AND Gallery Context ************/
/******************************************/

/**
 * WHILE next_album(): context switches to Album.
 * If we're already in the album context, this is a sub-albums loop, which,
 * quite simply, changes the source of the album list.
 * Switch back to the previous context when there are no more albums.

 * Returns true if there are albums, false if none
 *
 * @param bool $all true to go through all the albums
 * @param string $sorttype overrides default sort type
 * @param string $sortdirection overrides default sort direction
 * @return bool
 * @since 0.6
 */
function next_album($all=false, $sorttype=null, $sortdirection=NULL) {
	global $_zp_albums, $_zp_gallery, $_zp_current_album, $_zp_page, $_zp_current_album_restore, $_zp_current_search;
	if (is_null($_zp_albums)) {
		if (in_context(ZP_SEARCH)) {
			$_zp_albums = $_zp_current_search->getAlbums($all ? 0 : $_zp_page);
		} else if (in_context(ZP_ALBUM)) {
			$_zp_albums = $_zp_current_album->getAlbums($all ? 0 : $_zp_page, $sorttype, $sortdirection);
		} else {
			$_zp_albums = $_zp_gallery->getAlbums($all ? 0 : $_zp_page, $sorttype, $sortdirection);
		}
		if (empty($_zp_albums)) { return false; }
		$_zp_current_album_restore = $_zp_current_album;
		$_zp_current_album = new Album($_zp_gallery, array_shift($_zp_albums));
		save_context();
		add_context(ZP_ALBUM);
		return true;
	} else if (empty($_zp_albums)) {
		$_zp_albums = NULL;
		$_zp_current_album = $_zp_current_album_restore;
		restore_context();
		return false;
	} else {
		$_zp_current_album = new Album($_zp_gallery, array_shift($_zp_albums));
		return true;
	}
}

/**
 * Returns the number of albums without printing it.
 *
 * @return int
 */
function getCurrentPage() {
	global $_zp_page;
	return $_zp_page;
}

/**
 * Returns a list of all albums decendent from an album
 *
 * @param object $album optional album. If absent the current album is used
 * @return array
 */
function getAllAlbums($album = NULL) {
	global $_zp_current_album, $_zp_gallery;
	if (is_null($album)) $album = $_zp_current_album;
	$list = array();
	$subalbums = $album->getAlbums(0);
	if (is_array($subalbums)) {
		foreach ($subalbums as $subalbum) {
			$list[] = $subalbum;
			$sub = new Album($_zp_gallery, $subalbum);
			$list = array_merge($list, getAllAlbums($sub));
		}
	}
	return $list;
}

function resetCurrentAlbum() {
	global $_zp_images, $_zp_current_album, $_zp_conf_vars;
	$_zp_images = NULL;
	$_zp_current_album->images = NULL;
	$_zp_conf_vars['images_first_page'] =  NULL;
}

/**
 * Returns the number of pages for the current object
 *
 * @param bool $oneImagePage set to true if your theme collapses all image thumbs
 * or their equivalent to one page. This is typical with flash viewer themes
 *
 * @return int
 */
function getTotalPages($oneImagePage=false) {
	global $_zp_conf_vars;
	global $_zp_gallery, $_zp_current_album;
	if (in_context(ZP_ALBUM | ZP_SEARCH)) {
		$albums_per_page = max(1, getOption('albums_per_page'));
		if (in_context(ZP_SEARCH)) {
			$pageCount = ceil(getNumAlbums() / $albums_per_page);
		} else {
			$pageCount = ceil(getNumAlbums() / $albums_per_page);
		}
		$imageCount = getNumImages();
		if ($oneImagePage) {
			$imageCount = min(1, $imageCount);
		}
		$images_per_page = max(1, getOption('images_per_page'));
		$pageCount = ($pageCount + ceil(($imageCount - $_zp_conf_vars['images_first_page']) / $images_per_page));
		return $pageCount;
	} else if (in_context(ZP_INDEX)) {
		if(galleryAlbumsPerPage() != 0) {
			return ceil($_zp_gallery->getNumAlbums() / galleryAlbumsPerPage());
		} else {
			return NULL;
		}
	} else {
		return null;
	}
}

/**
 * Returns the URL of the page number passed as a parameter
 *
 * @param int $page Which page is desired
 * @param int $total How many pages there are.
 * @return int
 */
function getPageURL($page, $total=null) {
	global $_zp_current_album, $_zp_gallery, $_zp_current_search, $_zp_gallery_page;
	if (is_null($total)) { $total = getTotalPages(); }
	if (in_context(ZP_SEARCH)) {
		$searchwords = $_zp_current_search->codifySearchString();
		$searchdate = $_zp_current_search->dates;
		$searchfields = $_zp_current_search->getSearchFields(true);
		$searchpagepath = getSearchURL($searchwords, $searchdate, $searchfields, $page, $_zp_current_search->album_list);
		return $searchpagepath;
	} else {
		if ($specialpage = !in_array($_zp_gallery_page, array('index.php', 'album.php', 'image.php', 'search.php'))) {
			// handle custom page
			$pg = substr($_zp_gallery_page, 0, -4);
			$pagination1 = '/page/'.$pg.'/';
			$pagination2 = 'p='.$pg.'&';
		} else {
			$pagination1 = '/page/';
			$pagination2 = '';
		}
		if ($page <= $total && $page > 0) {
			if (in_context(ZP_ALBUM)) {
				return rewrite_path( pathurlencode($_zp_current_album->name) . (($page > 1) ? "/page/" . $page . "/" : ""),
					"/index.php?album=" . pathurlencode($_zp_current_album->name) . (($page > 1) ? "&page=" . $page : "") );
			} else if (in_context(ZP_INDEX)) {
				if ($page == 1) {
					// Just return the gallery base path for ZP_INDEX (no /page/x)
					if (empty($pagination2)) {
						return rewrite_path('/', '/');
					} else {
						return rewrite_path($pagination1, "/index.php?" . substr($pagination2, 0, -1));
					}
				} else if ($page > 1) {
					return rewrite_path($pagination1 . $page . "/", "/index.php?" . $pagination2 . 'page=' . $page);
				}
			}
		}
		if ($specialpage) {
			return rewrite_path($pagination1, '?'.substr($pagination2, 0, -1));
		}
		return null;
	}
}

/**
 * Returns true if there is a next page
 *
 * @return bool
 */
function hasNextPage() { return (getCurrentPage() < getTotalPages()); }

/**
 * Returns the URL of the next page. Use within If or while loops for pagination.
 *
 * @return string
 */
function getNextPageURL() {
	return getPageURL(getCurrentPage() + 1);
}

/**
 * Prints the URL of the next page.
 *
 * @param string $text text for the URL
 * @param string $title Text for the HTML title
 * @param string $class Text for the HTML class
 * @param string $id Text for the HTML id
 */
function printNextPageLink($text, $title=NULL, $class=NULL, $id=NULL) {
	if (hasNextPage()) {
		printLink(getNextPageURL(), $text, $title, $class, $id);
	} else {
		echo "<span class=\"disabledlink\">$text</span>";
	}
}

/**
 * Returns TRUE if there is a previous page. Use within If or while loops for pagination.
 *
 * @return bool
 */
function hasPrevPage() { return (getCurrentPage() > 1); }

/**
 * Returns the URL of the previous page.
 *
 * @return string
 */
function getPrevPageURL() {
	return getPageURL(getCurrentPage() - 1);
}

/**
 * Returns the URL of the previous page.
 *
 * @param string $text The linktext that should be printed as a link
 * @param string $title The text the html-tag "title" should contain
 * @param string $class Insert here the CSS-class name you want to style the link with
 * @param string $id Insert here the CSS-ID name you want to style the link with
 */
function printPrevPageLink($text, $title=NULL, $class=NULL, $id=NULL) {
	if (hasPrevPage()) {
		printLink(getPrevPageURL(), $text, $title, $class, $id);
	} else {
		echo "<span class=\"disabledlink\">$text</span>";
	}
}

/**
 * Prints a page navigation including previous and next page links
 *
 * @param string $prevtext Insert here the linktext like 'previous page'
 * @param string $separator Insert here what you like to be shown between the prev and next links
 * @param string $nexttext Insert here the linktext like "next page"
 * @param string $class Insert here the CSS-class name you want to style the link with (default is "pagelist")
 * @param string $id Insert here the CSS-ID name if you want to style the link with this
 */
function printPageNav($prevtext, $separator, $nexttext, $class='pagenav', $id=NULL) {
	echo "<div" . (($id) ? " id=\"$id\"" : "") . " class=\"$class\">";
	printPrevPageLink($prevtext, gettext("Previous Page"));
	echo " $separator ";
	printNextPageLink($nexttext, gettext("Next Page"));
	echo "</div>\n";
}

/**
 * Prints a list of all pages.
 *
 * @param string $class the css class to use, "pagelist" by default
 * @param string $id the css id to use
 * @param int $navlen Number of navigation links to show (0 for all pages). Works best if the number is odd.
*/
function printPageList($class='pagelist', $id=NULL, $navlen=9) {
	printPageListWithNav(null, null, false, false, $class, $id, false, $navlen);
}

/**
 * Prints a full page navigation including previous and next page links with a list of all pages in between.
 *
 * @param string $prevtext Insert here the linktext like 'previous page'
 * @param string $nexttext Insert here the linktext like 'next page'
 * @param bool $oneImagePage set to true if there is only one image page as, for instance, in flash themes
 * @param string $nextprev set to true to get the 'next' and 'prev' links printed
 * @param string $class Insert here the CSS-class name you want to style the link with (default is "pagelist")
 * @param string $id Insert here the CSS-ID name if you want to style the link with this
 * @param bool $firstlast Add links to the first and last pages of you gallery
 * @param int $navlen Number of navigation links to show (0 for all pages). Works best if the number is odd.
 */
function printPageListWithNav($prevtext, $nexttext, $oneImagePage=false, $nextprev=true, $class='pagelist', $id=NULL, $firstlast=true, $navlen=9) {
	$total = getTotalPages($oneImagePage);
	$current = getCurrentPage();
	if ($total < 2) {
		$class .= ' disabled_nav';
	}
	if ($navlen == 0)
		$navlen = $total;
	$extralinks = 2;
	if ($firstlast) $extralinks = $extralinks + 2;
	$len = floor(($navlen-$extralinks) / 2);
	$j = max(round($extralinks/2), min($current-$len-(2-round($extralinks/2)), $total-$navlen+$extralinks-1));
	$ilim = min($total, max($navlen-round($extralinks/2), $current+floor($len)));
	$k1 = round(($j-2)/2)+1;
	$k2 = $total-round(($total-$ilim)/2);

	echo "<div" . (($id) ? " id=\"$id\"" : "") . " class=\"$class\">\n";
	echo "<ul class=\"$class\">\n";
	if ($nextprev) {
		echo "<li class=\"prev\">";
		printPrevPageLink($prevtext, gettext("Previous Page"));
		echo "</li>\n";
	}
	if ($firstlast) {
		echo '<li class="'.($current==1?'current':'first').'">';
		printLink(getPageURL(1, $total), 1, gettext("Page 1"));
		echo "</li>\n";
		if ($j>2) {
			echo "<li>";
			printLink(getPageURL($k1, $total), ($j-1>2)?'...':$k1, sprintf(ngettext('Page %u','Page %u',$k1),$k1));
			echo "</li>\n";
		}
	}
	for ($i=$j; $i <= $ilim; $i++) {
		echo "<li" . (($i == $current) ? " class=\"current\"" : "") . ">";
		if ($i == $current) {
			$title = sprintf(ngettext('Page %1$u (Current Page)','Page %1$u (Current Page)', $i),$i);
		} else {
			$title = sprintf(ngettext('Page %1$u','Page %1$u', $i),$i);
		}
		printLink(getPageURL($i, $total), $i, $title);
		echo "</li>\n";
	}
	if ($i < $total) {
		echo "<li>";
		printLink(getPageURL($k2, $total), ($total-$i>1)?'...':$k2, sprintf(ngettext('Page %u','Page %u',$k2),$k2));
		echo "</li>\n";
	}
	if ($firstlast && $i <= $total) {
		echo "\n  <li class=\"last\">";
		printLink(getPageURL($total, $total), $total, sprintf(ngettext('Page {%u}','Page {%u}',$total),$total));
		echo "</li>";
	}
	if ($nextprev) {
		echo "<li class=\"next\">";
		printNextPageLink($nexttext, gettext("Next Page"));
		echo "</li>\n";
	}
	echo "</ul>\n";
	echo "</div>\n";
}

//*** Album Context ************************
//******************************************

/**
 * Sets the album passed as the current album
 *
 * @param object $album the album to be made current
 */
function makeAlbumCurrent($album) {
	global $_zp_current_album;
	$_zp_current_album = $album;
	set_context(ZP_INDEX | ZP_ALBUM);
}

/**
 * Returns the raw title of the current album.
 *
 * @return string
 */
function getAlbumTitle() {
	if(!in_context(ZP_ALBUM)) return false;
	global $_zp_current_album;
	return $_zp_current_album->getTitle();
}

/**
 * Returns a text-only title of the current album.
 *
 * @return string
 */
function getBareAlbumTitle() {
	return html_encode(getAlbumTitle());
}

/**
 * Returns an album title taged with of Not visible or password protected status
 *
 * @return string;
 */
function getAnnotatedAlbumTitle() {
	global $_zp_current_album;
	$title = getBareAlbumTitle();
	$pwd = $_zp_current_album->getPassword();
	if (zp_loggedin() && !empty($pwd)) {
		$title .= "\n".gettext('The album is password protected.');
	}
	if (!$_zp_current_album->getShow()) {
		$title .= "\n".gettext('The album is not published.');
	}
	return $title;
}

/**
 * Prints an encapsulated title of the current album.
 * If you are logged in you can click on this to modify the title on the fly.
 *
 * @param bool   $editable set to true to allow editing (for the admin)
 * @param string $editclass CSS class applied to element if editable
 * @param mixed $messageIfEmpty Either bool or string. If false, echoes nothing when description is empty. If true, echoes default placeholder message if empty. If string, echoes string.
 * @author Ozh
 */
function printAlbumTitle($editable=false, $editclass='', $messageIfEmpty = true) {
	if ( $messageIfEmpty === true ) {
		$messageIfEmpty = gettext('(No title...)');
	}
	printEditable('album', 'title', $editable, $editclass, $messageIfEmpty);
}

/**
 * Gets the 'n' for n of m albums
 *
 * @return int
 */
function albumNumber() {
	global $_zp_current_album, $_zp_current_image, $_zp_current_search, $_zp_gallery, $_zp_dynamic_album;
	$name = $_zp_current_album->getFolder();
	if (in_context(ZP_SEARCH)) {
		$albums = $_zp_current_search->getAlbums();
	} else if (in_context(ZP_ALBUM)) {
		if (is_null($_zp_dynamic_album)) {
			$parent = $_zp_current_album->getParent();
			if (is_null($parent)) {
				$albums = $_zp_gallery->getAlbums();
			} else {
				$albums = $parent->getAlbums();
			}
		} else {
			$albums = $_zp_dynamic_album->getAlbums();
		}
	}
	$c = 0;
	foreach ($albums as $albumfolder) {
		$c++;
		if ($name == $albumfolder) {
			return $c;
		}
	}
	return false;
}

/**
 * Returns an array of the names of the parents of the current album.
 *
 * @param object $album optional album object to use inseted of the current album
 * @return array
 */
function getParentAlbums($album=null) {
	if(!in_context(ZP_ALBUM)) return false;
	global $_zp_current_album, $_zp_current_search, $_zp_gallery;
	$parents = array();
	if (is_null($album)) {
		if (in_context(ZP_SEARCH_LINKED) && !in_context(ZP_ALBUM_LINKED)) {
			$name = $_zp_current_search->dynalbumname;
			if (empty($name)) return $parents;
			$album = new Album($_zp_gallery, $name);
		} else {
			$album = $_zp_current_album;
		}
	}
	while (!is_null($album = $album->getParent())) {
		array_unshift($parents, $album);
	}
	return $parents;
}

/**
 * prints the breadcrumb item for the current images's album
 *
 * @param string $before Text to place before the breadcrumb
 * @param string $after Text to place after the breadcrumb
 * @param string $title Text to be used as the URL title tag
 */
function printAlbumBreadcrumb($before='', $after='', $title=NULL) {
	global $_zp_current_search, $_zp_gallery, $_zp_current_album, $_zp_last_album, $_zp_search_album_list;
	if (is_null($title)) $title = gettext('Album Thumbnails');
	echo $before;
	if (in_context(ZP_SEARCH_LINKED)) {
		$dynamic_album = $_zp_current_search->dynalbumname;
		if (empty($dynamic_album)) {
			if (!is_null($_zp_current_album)) {
				if (in_context(ZP_ALBUM_LINKED) && $_zp_last_album == $_zp_current_album->name) {
					echo "<a href=\"" . htmlspecialchars(getAlbumLinkURL()). "\" title=\"" . html_encode($title) . "\">" . getAlbumTitle() . "</a>";
				} else {
					$after = '';
				}
			} else {
				$after = '';
			}
		} else {
			if (in_context(ZP_IMAGE) && in_context(ZP_ALBUM_LINKED)) {
				$album = $_zp_current_album;
			} else {
				$album = new Album($_zp_gallery, $dynamic_album);
			}
			echo "<a href=\"" . htmlspecialchars(getAlbumLinkURL($album)) . "\">";
			echo $album->getTitle();
			echo '</a>';
		}
	} else {
		echo "<a href=\"" . htmlspecialchars(getAlbumLinkURL()). "\" title=\"" . html_encode($title) . "\">" . getAlbumTitle() . "</a>";
	}
	echo $after;
}

/**
 * Prints the breadcrumb navigation for album, gallery and image view.
 *
 * @param string $before Insert here the text to be printed before the links
 * @param string $between Insert here the text to be printed between the links
 * @param string $after Insert here the text to be printed after the links
 * @param mixed $truncate if not empty, the max lenght of the description.
 * @param string $elipsis the text to append to the truncated description
 */
function printParentBreadcrumb($before = '', $between=' | ', $after = ' | ', $truncate=NULL, $elipsis='...') {
	global $_zp_gallery, $_zp_current_search, $_zp_current_album, $_zp_last_album, $_zp_search_album_list;
	echo $before;
	if (in_context(ZP_SEARCH_LINKED)) {
		$page = $_zp_current_search->page;
		$searchwords = $_zp_current_search->words;
		$searchdate = $_zp_current_search->dates;
		$searchfields = $_zp_current_search->getSearchFields(true);
		$searchpagepath = htmlspecialchars(getSearchURL($searchwords, $searchdate, $searchfields, $page, $_zp_current_search->album_list),ENT_QUOTES);
		$dynamic_album = $_zp_current_search->dynalbumname;
		if (empty($dynamic_album)) {
			echo "<a href=\"" . $searchpagepath . "\" title=\"Return to search\">";
			echo "<em>".gettext("Search")."</em></a>";
			if (is_null($_zp_current_album)) {
				echo $after;
				return;
			} else {
				$parents = getParentAlbums();
				echo $between;
			}
		} else {
			$album = new Album($_zp_gallery, $dynamic_album);
			$parents = getParentAlbums($album);
			if (in_context(ZP_ALBUM_LINKED)) {
				array_push($parents, $album);
			}
		}
		// remove parent links that are not in the search path
		foreach ($parents as $key=>$analbum) {
			$target = $analbum->name;
			if ($target!==$dynamic_album && !in_array($target, $_zp_search_album_list)) {
				unset($parents[$key]);
			}
		}
	} else {
		$parents = getParentAlbums();

	}
	$n = count($parents);
	if ($n > 0) {
		$i = 0;
		foreach($parents as $parent) {
			if ($i > 0) echo $between;
			$url = rewrite_path("/" . pathurlencode($parent->name) . "/", "/index.php?album=" . urlencode($parent->name));
			$desc = $parent->getDesc();
			if (!empty($desc) && $truncate) $desc = truncate_string($string, $length, $elipsis);
			printLink($url, $parent->getTitle(), $desc);
			$i++;
		}
		echo $after;
	}
}

/**
 * Prints a link to the 'main website'
 * Only prints the link if the url is not empty and does not point back the gallery page
 *
 * @param string $before text to precede the link
 * @param string $after text to follow the link
 * @param string $title Title text
 * @param string $class optional css class
 * @param string $id optional css id
 *  */
function printHomeLink($before='', $after='', $title=NULL, $class=NULL, $id=NULL) {
	$site = getOption('website_url');
	if (!empty($site)) {
		if (substr($site,-1) == "/") { $site = substr($site, 0, -1); }
		if (empty($name)) { $name = get_language_string(getOption('website_title')); }
		if (empty($name)) { $name = 'Home'; }
		if ($site != FULLWEBPATH) {
			echo $before;
			printLink($site, $name, $title, $class, $id);
			echo $after;
		}
	}
}

/**
 * Returns the formatted date field of the album
 *
 * @param string $format optional format string for the date
 * @return string
 */
function getAlbumDate($format=null) {
	global $_zp_current_album;
	$d = $_zp_current_album->getDateTime();
	if (empty($d) || ($d == '0000-00-00 00:00:00')) {
		return false;
	}
	if (is_null($format)) {
		return $d;
	}
	return zpFormattedDate($format, strtotime($d));
}

/**
 * Prints the date of the current album and makes it editable in place if applicable
 *
 * @param string $before Insert here the text to be printed before the date.
 * @param string $nonemessage Insert here the text to be printed if there is no date.
 * @param string $format Format string for the date formatting
 * @param bool $editable when true, enables AJAX editing in place
 * @param string $editclass CSS class applied to element if editable
 * @param mixed $messageIfEmpty Either bool or string. If false, echoes nothing when description is empty. If true, echoes default placeholder message if empty. If string, echoes string.
 * @author Ozh
 */
function printAlbumDate($before='', $nonemessage='', $format=null, $editable=false, $editclass='', $messageIfEmpty = true) {
	if (is_null($format)) {
		$format = getOption('date_format');
	}
	$date = getAlbumDate($format);
	if ($date) {
		$date = $before . $date;
	} else {
		$date = '';
		if ($nonemessage != '') {
			$messageIfEmpty = $nonemessage;
		} elseif ( $messageIfEmpty === true ) {
			$messageIfEmpty = gettext('(No date...)');
		}
	}
	printEditable('album', 'date', $editable, $editclass, $messageIfEmpty, false, $date);
}

/**
 * Returns the Location of the album.
 *
 * @return string
 */
function getAlbumLocation() {
	global $_zp_current_album;
	return $_zp_current_album->getLocation();
}

/**
 * Prints the location of the album and make it editable
 *
 * @param bool $editable when true, enables AJAX editing in place
 * @param string $editclass CSS class applied to element if editable
 * @param mixed $messageIfEmpty Either bool or string. If false, echoes nothing when description is empty. If true, echoes default placeholder message if empty. If string, echoes string.
 * @author Ozh
 */
function printAlbumLocation($editable=false, $editclass='', $messageIfEmpty = true) {
	if ( $messageIfEmpty === true ) {
		$messageIfEmpty = gettext('(No Location...)');
	}
	printEditable('album', 'location', $editable, $editclass, $messageIfEmpty, !getOption('tinyMCEPresent'));
}

/**
 * Returns the raw description of the current album.
 *
 * @return string
 */
function getAlbumDesc() {
	if(!in_context(ZP_ALBUM)) return false;
	global $_zp_current_album;
	return $_zp_current_album->getDesc();
}

/**
 * Returns a text-only description of the current album.
 *
 * @return string
 */
function getBareAlbumDesc() {
	return html_encode(getAlbumDesc());
}

/**
 * Prints description of the current album and makes it editable in place
 *
 * @param bool $editable when true, enables AJAX editing in place
 * @param string $editclass CSS class applied to element if editable
 * @param mixed $messageIfEmpty Either bool or string. If false, echoes nothing when description is empty. If true, echoes default placeholder message if empty. If string, echoes string.
 * @author Ozh
 */
function printAlbumDesc($editable=false, $editclass='', $messageIfEmpty = true ) {
	if ( $messageIfEmpty === true ) {
		$messageIfEmpty = gettext('(No description...)');
	}
	printEditable('album', 'desc', $editable, $editclass, $messageIfEmpty, !getOption('tinyMCEPresent'));
}



/**
 * Print any album or image data and make it editable in place
 *
 * @param string $context	either 'image' or 'album'
 * @param string $field		the data field to echo & edit if applicable: 'date', 'title', 'place', 'description', ...
 * @param bool   $editable 	when true, enables AJAX editing in place
 * @param string $editclass CSS class applied to element if editable
 * @param mixed  $messageIfEmpty message echoed if no value to print
 * @param bool   $convertBR	when true, converts new line characters into HTML line breaks
 * @param string $override	if not empty, print this string instead of fetching field value from database
 * @param string $label "label" text to print if the field is not empty
 * @since 1.3
 * @author Ozh
 */
function printEditable($context, $field, $editable = false, $editclass = 'editable', $messageIfEmpty = true, $convertBR = false, $override = false, $label='') {

	if (!$context or !$field) {
		trigger_error(gettext('printEditable() incomplete function call.'), E_USER_NOTICE);
		return false;
	}

	switch($context) {
		case 'image':
			global $_zp_current_image;
			$object = $_zp_current_image;
			break;
		case 'album':
			global $_zp_current_album;
			$object = $_zp_current_album;
			break;
		case 'zenpage_page':
			global $_zp_current_zenpage_page;
			$object = $_zp_current_zenpage_page;
			break;
		case 'zenpage_news':
			global $_zp_current_zenpage_news;
			$object = $_zp_current_zenpage_news;
			break;
		default:
			trigger_error(gettext('printEditable() incomplete function call.'), E_USER_NOTICE);
			return false;
	}
	$text = trim( $override !== false ? $override : get_language_string($object->get($field)) );
	$text = zp_apply_filter($context.'_'.$field, $text, $object, $context, $field);
	if ($convertBR) {
		$text = str_replace("\r\n", "\n", $text);
		$text = str_replace("\n", "<br />", $text);
	}

	if (empty($text)) {
		if ( $editable && zp_loggedin() ) {
			if ( $messageIfEmpty === true ) {
				$text = gettext('(...)');
			} elseif ( is_string($messageIfEmpty) ) {
				$text = $messageIfEmpty;
			}
		}
	}
	if (!empty($text)) echo $label;
	if ($editable && getOption('edit_in_place') && zp_loggedin()) {
		// Increment a variable to make sure all elements will have a distinct HTML id
		static $id = 1;
		$id++;
		$class= 'class="' . trim("$editclass zp_editable zp_editable_{$context}_{$field}") . '"';
		echo "<span id=\"editable_{$context}_$id\" $class>" . $text . "</span>\n";
		echo "<script type=\"text/javascript\">editInPlace('editable_{$context}_$id', '$context', '$field');</script>";
	} else {
		$class= 'class="' . "zp_uneditable zp_uneditable_{$context}_{$field}" . '"';
		echo "<span $class>" . $text . "</span>\n";
	}
}



/**
 * Returns the custom_data field of the current album
 *
 * @return string
 */
function getAlbumCustomData() {
	global $_zp_current_album;
	return $_zp_current_album->getCustomData();
}

/**
 * Prints the custom_data field of the current album.
 * Converts and displays line break in the admin field as <br />.
 *
 * @param bool $editable when true, enables AJAX editing in place
 * @param string $editclass CSS class applied to element if editable
 * @param mixed $messageIfEmpty Either bool or string. If false, echoes nothing when description is empty. If true, echoes default placeholder message if empty. If string, echoes string.
 * @author Ozh
 */
function printAlbumCustomData($editable = false, $editclass='', $messageIfEmpty = true) {
	if ( $messageIfEmpty === true ) {
		$messageIfEmpty = gettext('(No data...)');
	}
	printEditable('album', 'custom_data', $editable, $editclass, $messageIfEmpty, !getOption('tinyMCEPresent'));
}

/**
 * Sets the album custom_data field
 *
 * @param string $val The value to be set
 */
function setAlbumCustomData($val) {
	global $_zp_current_album;
	$_zp_current_album->setCustomData($val);
	$_zp_current_album->save();
}

/**
 * A composit for getting album data
 *
 * @param string $field which field you want
 * @return string
 */
function getAlbumData($field) {
	if(!in_context(ZP_IMAGE)) return false;
	global $_zp_album_image;
	return get_language_string($_zp_album_image->get($field));
}

/**
 * Prints arbitrary data from the album object and make it editable if applicable
 *
 * @param string $field the field name of the data desired
 * @param string $label text to label the field
 * @param bool $editable when true, enables AJAX editing in place
 * @param string $editclass CSS class applied to element if editable
 * @param mixed $messageIfEmpty Either bool or string. If false, echoes nothing when description is empty. If true, echoes default placeholder message if empty. If string, echoes string.
 * @author Ozh
 */
function printAlbumData($field, $label='', $editable=false, $editclass='', $messageIfEmpty = true) {
	if ( $messageIfEmpty === true ) {
		$messageIfEmpty = gettext('(No data...)');
	}
	if (empty($editclass)) $editclass = 'metadata';
	printEditable('album', $field, $editable, $editclass, $messageIfEmpty, false, false, $label);
}


/**
 * Returns the album page number of the current image
 *
 * @param object $album optional album object
 * @return integer
 */
function getAlbumPage($album = NULL) {
	global $_zp_current_album, $_zp_current_image, $_zp_current_search, $firstPageImages;
	if (is_null($album)) $album = $_zp_current_album;
	$page = 0;
	if (in_context(ZP_IMAGE) && !in_context(ZP_SEARCH)) {
		if ($_zp_current_album->isDynamic()) {
			$search = $_zp_current_album->getSearchEngine();
			$imageindex = $search->getImageIndex($_zp_current_album->name, $_zp_current_image->filename);
			$numalbums = $search->getNumAlbums();
		} else {
			$imageindex = $_zp_current_image->getIndex();
			$numalbums = $album->getNumAlbums();
		}
		$imagepage = floor(($imageindex - $firstPageImages) / max(1, getOption('images_per_page'))) + 1;
		$albumpages = ceil($numalbums / max(1, getOption('albums_per_page')));
		if ($albumpages == 0 && $firstPageImages > 0) $imagepage++;
		$page = $albumpages + $imagepage;
	}
	return $page;
}

/**
 * Returns the album link url of the current album.
 *
 * @param object $album optional album object
 * @return string
 */
function getAlbumLinkURL($album=NULL) {
	global $_zp_current_album;
	if (is_null($album)) $album = $_zp_current_album;
	$page = getAlbumPage($album);
	if (in_context(ZP_IMAGE) && $page > 1) {
		// Link to the page the current image belongs to.
		$link = rewrite_path("/" . pathurlencode($album->name) . "/page/" . $page,
			"/index.php?album=" . urlencode($album->name) . "&page=" . $page);
	} else {
		$link = rewrite_path("/" . pathurlencode($album->name) . "/",
			"/index.php?album=" . urlencode($album->name));
	}
	return $link;
}

/**
 * Prints the album link url of the current album.
 *
 * @param string $text Insert the link text here.
 * @param string $title Insert the title text here.
 * @param string $class Insert here the CSS-class name with with you want to style the link.
 * @param string $id Insert here the CSS-id name with with you want to style the link.
 */
function printAlbumLink($text, $title, $class=NULL, $id=NULL) {
	printLink(getAlbumLinkURL(), $text, $title, $class, $id);
}

/**
 * Returns the name of the defined album thumbnail image.
 *
 * @return string
 */
function getAlbumThumb() {
	global $_zp_current_album;
	return $_zp_current_album->getAlbumThumb();
}

/**
 * Returns an img src link to the password protect thumb substitute
 *
 * @param string $extra extra stuff to put in the HTML
 * @return string
 */
function getPasswordProtectImage($extra) {
	global $_zp_themeroot;
	$image = $_zp_themeroot.'/images/err-passwordprotected.gif';
	$themedir = SERVERPATH . '/themes/'.basename($_zp_themeroot);
	$imagebase = $themedir.'/images/err-passwordprotected.gif';
	if (file_exists(internalToFilesystem($imagebase))) {
		return '<img src="'.$image.'" '.$extra.' alt="protected" />';
	} else {
		return '<img src="'. WEBPATH . '/' . ZENFOLDER.'/images/err-passwordprotected.gif" '.
						$extra.' alt="protected" />';
	}
}

/**
 * Prints the album thumbnail image.
 *
 * @param string $alt Insert the text for the alternate image name here.
 * @param string $class Insert here the CSS-class name with with you want to style the link.
 * @param string $id Insert here the CSS-id name with with you want to style the link.
 *  */
function printAlbumThumbImage($alt, $class=NULL, $id=NULL) {
	global $_zp_current_album, $_zp_themeroot;
	if (!$_zp_current_album->getShow()) {
		$class .= " not_visible";
	}
	$pwd = $_zp_current_album->getPassword();
	if (zp_loggedin() && !empty($pwd)) {
		$class .= " password_protected";
	}

	$class = trim($class);
	if (!getOption('use_lock_image') || checkAlbumPassword($_zp_current_album->name, $hint)) {
		$html = '<img src="' . htmlspecialchars(getAlbumThumb()) . '" alt="' . html_encode($alt) . '"' .
		(($class) ? ' class="'.$class.'"' : '') . (($id) ? ' id="'.$id.'"' : '') . ' />';
		$html = zp_apply_filter('standard_album_thumb_html', $html);
		echo $html;
	} else {
		if (getOption('thumb_crop')) {
			$s = getOption('thumb_size');
			$w = getOption('thumb_crop_width');
			$h = getOption('thumb_crop_height');
			if ($w > $h) {
				$h = round($h * $s/$w);
				$w = $s;
			} else {
				$w = round($w * $s/$w);
				$h = $s;
			}
			$size = 'width="'.$w.'" height="'.$h.'"';
		} else {
			$size = '';
		}
		echo getPasswordProtectImage($size);
	}
}

/**
 * Returns a link to a custom sized thumbnail of the current album
 *
 * @param int $size the size of the image to have
 * @param int $width width
 * @param int $height height
 * @param int $cropw crop width
 * @param int $croph crop height
 * @param int $cropx crop part x axis
 * @param int $cropy crop part y axis
 * @param bool $gray set true to force grayscale
 *
 * @return string
 */

function getCustomAlbumThumb($size, $width=NULL, $height=NULL, $cropw=NULL, $croph=NULL, $cropx=NULL, $cropy=null, $gray=false) {
	global $_zp_current_album;
	$thumb = $_zp_current_album->getAlbumThumbImage();
	return $thumb->getCustomImage($size, $width, $height, $cropw, $croph, $cropx, $cropy, true, $gray);
}

/**
 * Prints a link to a custom sized thumbnail of the current album
 *
 * See getCustomImageURL() for details.
 *
 * @param string $alt Alt atribute text
 * @param int $size size
 * @param int $width width
 * @param int $height height
 * @param int $cropw cropwidth
 * @param int $croph crop height
 * @param int $cropx crop part x axis
 * @param int $cropy crop part y axis
 * @param string $class css class
 * @param string $id css id
 *
 * @return string
 */
function printCustomAlbumThumbImage($alt, $size, $width=NULL, $height=NULL, $cropw=NULL, $croph=NULL, $cropx=NULL, $cropy=null, $class=NULL, $id=NULL) {
	global $_zp_current_album;
	if (!$_zp_current_album->getShow()) {
		$class .= " not_visible";
	}
	$pwd = $_zp_current_album->getPassword();
	if (zp_loggedin() && !empty($pwd)) {
		$class .= " password_protected";
	}
	$class = trim($class);
	/* set the HTML image width and height parameters in case this image was "imageDefault.png" substituted for no thumbnail then the thumb layout is preserved */
	$sizing = '';
	if (is_null($width)) {
		if (!is_null($cropw) && !is_null($croph)) {
			$s = round($height * ($cropw/$croph));
			if (!empty($s)) $sizing = ' width="'.$s.'"';
		}
	} else {
		$sizing = ' width="'.$width.'"';
	}
	if (is_null($height)) {
		if (!is_null($cropw) && !is_null($croph)) {
			$s = round($width * ($croph/$cropw));
			if (!empty($s)) $sizing = $sizing.' height="'.$s.'"';
		}
	} else {
		$sizing = $sizing.' height="'.$height.'"';
	}
	if (!getOption('use_lock_image') || checkAlbumPassword($_zp_current_album->name, $hint)){
		$html = '<img src="' . htmlspecialchars(getCustomAlbumThumb($size, $width, $height, $cropw, $croph, $cropx, $cropy)). '"' . $sizing . ' alt="' . html_encode($alt) . '"' .
		(($class) ? ' class="'.$class.'"' : '') .	(($id) ? ' id="'.$id.'"' : '') . " />";
		$html = zp_apply_filter('custom_album_thumb_html', $html);
		echo $html;
	} else {
		echo getPasswordProtectImage($sizing);
	}
}

/**
 * Called by ***MaxSpace functions to compute the parameters to be passed to xxCustomyyy functions.
 *
 * @param int $width maxspace width
 * @param int $height maxspace height
 * @param object $image the image in question
 * @param bool $thumb true if for a thumbnail
 */
function getMaxSpaceContainer(&$width, &$height, $image, $thumb=false) {
	global $_zp_gallery;
	$upscale = getOption('image_allow_upscale');
	$imagename = $image->filename;
	if (!isImagePhoto($image) & $thumb) {
		$imgfile = $image->getThumbImageFile();
		$image = zp_imageGet($imgfile);
		$s_width = zp_imageWidth($image);
		$s_height = zp_imageHeight($image);
	} else {
		$s_width = $image->get('width');
		if ($s_width == 0) $s_width = max($width,$height);
		$s_height = $image->get('height');
		if ($s_height == 0) $s_height = max($width,$height);
	}

	$newW = round($height/$s_height*$s_width);
	$newH = round($width/$s_width*$s_height);
	if (DEBUG_IMAGE) debugLog("getMaxSpaceContainer($width, $height, $imagename, $thumb): \$s_width=$s_width; \$s_height=$s_height; \$newW=$newW; \$newH=$newH; \$upscale=$upscale;");
	if ($newW > $width) {
		if ($upscale || $s_height > $newH) {
			$height = $newH;
		} else {
			$height = $s_height;
			$width = $s_width;
		}
	} else {
		if ($upscale || $s_width > $newW) {
			$width = $newW;
		} else {
			$height = $s_height;
			$width = $s_width;
		}
	}
}

	/**
 * Returns a link to a un-cropped custom sized version of the current album thumb within the given height and width dimensions.
	*
 * @param int $width width
 * @param int $height height
 * @return string
 */
function getCustomAlbumThumbMaxSpace($width, $height) {
	global $_zp_current_album;
	$albumthumb = $_zp_current_album->getAlbumThumbImage();
	getMaxSpaceContainer($width, $height, $albumthumb, true);
	return getCustomAlbumThumb(NULL, $width, $height, NULL, NULL, NULL, NULL);
}

/**
 * Prints a un-cropped custom sized album thumb within the given height and width dimensions.
 * Note: a class of 'not_visible' or 'password_protected' will be added as appropriate
 *
 * @param string $alt Alt text for the url
 * @param int $width width
 * @param int $height height
 * @param string $class Optional style class
 * @param string $id Optional style id
 * @param bool $thumbStandin set true to inhibit watermarking
 */
function printCustomAlbumThumbMaxSpace($alt='', $width, $height, $class=NULL, $id=NULL) {
	global $_zp_current_album;
	$albumthumb = $_zp_current_album->getAlbumThumbImage();
	getMaxSpaceContainer($width, $height, $albumthumb, true);
	printCustomAlbumThumbImage($alt, NULL, $width, $height, NULL, NULL, NULL, NULL, $class, $id);
}

/**
 * Returns the next album
 *
 * @return object
 */
function getNextAlbum() {
	global $_zp_current_album, $_zp_current_search, $_zp_gallery;
	if (in_context(ZP_SEARCH) || in_context(ZP_SEARCH_LINKED)) {
		$nextalbum = $_zp_current_search->getNextAlbum($_zp_current_album->name);
	} else if (in_context(ZP_ALBUM)) {
		if ($_zp_current_album->isDynamic()) {
			$search = $_zp_current_album->getSearchEngine();
			$nextalbum = $search->getNextAlbum($_zp_current_album->name);
		} else {
			$nextalbum = $_zp_current_album->getNextAlbum();
		}
	} else {
		return null;
	}
	return $nextalbum;
}

/**
 * Get the URL of the next album in the gallery.
 *
 * @return string
 */
function getNextAlbumURL() {
	$nextalbum = getNextAlbum();
	if ($nextalbum) {
		return rewrite_path("/" . pathurlencode($nextalbum->name),
												"/index.php?album=" . urlencode($nextalbum->name));
	}
	return false;
}

/**
 * Returns the previous album
 *
 * @return object
 */
function getPrevAlbum() {
	global $_zp_current_album, $_zp_current_search;
	if (in_context(ZP_SEARCH) || in_context(ZP_SEARCH_LINKED)) {
		$prevalbum = $_zp_current_search->getPrevAlbum($_zp_current_album->name);
	} else if(in_context(ZP_ALBUM)) {
		if ($_zp_current_album->isDynamic()) {
			$search = $_zp_current_album->getSearchEngine();
			$prevalbum = $search->getPrevAlbum($_zp_current_album->name);
		} else {
			$prevalbum = $_zp_current_album->getPrevAlbum();
		}
	} else {
		return null;
	}
	return $prevalbum;
}

/**
 * Get the URL of the previous album in the gallery.
 *
 * @return string
 */
function getPrevAlbumURL() {
	$prevalbum = getPrevAlbum();
	if ($prevalbum) {
		return rewrite_path("/" . pathurlencode($prevalbum->name),
												"/index.php?album=" . urlencode($prevalbum->name));
	}
	return false;
}

/**
 * Returns true if this page has image thumbs on it
 *
 * @return bool
 */
function isImagePage() {
	global $_zp_page, $firstPageImages;
	$imagestart = getTotalPages(true);
	if (isset($firstPageImages) && $firstPageImages) $imagestart --; // then images start on the last album page.
	return $_zp_page >= $imagestart;
}

/**
 * Returns true if this page has album thumbs on it
 *
 * @return bool
 */
function isAlbumPage() {
	global $_zp_page;
	if (in_context(ZP_SEARCH)) {
		$pageCount = Ceil(getNumAlbums() / getOption('albums_per_page'));
	} else {
		$pageCount = Ceil(getNumAlbums() / getOption('albums_per_page'));
	}
	return ($_zp_page <= $pageCount);
}

/**
 * Returns the number of images in the album.
 *
 * @return int
 */
function getNumImages() {
	global $_zp_current_album, $_zp_current_search;
	if ((in_context(ZP_SEARCH_LINKED) && !in_context(ZP_ALBUM_LINKED)) || in_context(ZP_SEARCH) && is_null($_zp_current_album)) {
		return $_zp_current_search->getNumImages();
	} else {
		if ($_zp_current_album->isDynamic()) {
			$search = $_zp_current_album->getSearchEngine();
			return $search->getNumImages();
		} else {
			return $_zp_current_album->getNumImages();
		}
	}
	return false;
}

/**
* Returns the count of all the images in the album and any subalbums
*
* @param object $album The album whose image count you want
* @return int
* @since 1.1.4
*/

function getTotalImagesIn($album) {
	global $_zp_gallery;
	$sum = $album->getNumImages();
	$subalbums = $album->getAlbums(0);
	while (count($subalbums) > 0) {
		$albumname = array_pop($subalbums);
		$album = new Album($_zp_gallery, $albumname);
		$sum = $sum + getTotalImagesIn($album);
	}
	return $sum;
}


/**
 * Returns the next image on a page.
 * sets $_zp_current_image to the next image in the album.

 * Returns true if there is an image to be shown
 *
 * @param bool $all set to true disable pagination
 * @param int $firstPageCount the number of images which can go on the page that transitions between albums and images
 * @param string $sorttype overrides the default sort type
 * @param string $sortdirection overrides the default sort direction.
 * @param bool $overridePassword the password check
 * @return bool
 *
 * @return bool
 */
function next_image($all=false, $firstPageCount=NULL, $sorttype=null, $sortdirection=NULL) {
	global $_zp_images, $_zp_current_image, $_zp_current_album, $_zp_page, $_zp_current_image_restore,
				 $_zp_conf_vars, $_zp_current_search, $_zp_gallery;
	if (is_null($firstPageCount)) $firstPageCount = $_zp_conf_vars['images_first_page'];
	$imagePageOffset = getTotalPages(true) - 1; /* gives us the count of pages for album thumbs */
	if ($all) {
		$imagePage = 1;
	} else {
		$_zp_conf_vars['images_first_page'] = $firstPageCount;  /* save this so pagination can see it */
		$imagePage = $_zp_page - $imagePageOffset;
	}
	if ($firstPageCount > 0 && $imagePageOffset > 0) {
		$imagePage = $imagePage + 1;  /* can share with last album page */
	}
	if ($imagePage <= 0) {
		return false;  /* we are on an album page */
	}
	if (is_null($_zp_images)) {
		if (in_context(ZP_SEARCH)) {
			$_zp_images = $_zp_current_search->getImages($all ? 0 : ($imagePage), $firstPageCount, $sorttype, $sortdirection);
		} else {
			$_zp_images = $_zp_current_album->getImages($all ? 0 : ($imagePage), $firstPageCount, $sorttype, $sortdirection);
		}
		if (empty($_zp_images)) { return false; }
		$_zp_current_image_restore = $_zp_current_image;
		$img = array_shift($_zp_images);
		$_zp_current_image = newImage($_zp_current_album, $img);
		save_context();
		add_context(ZP_IMAGE);
		return true;
	} else if (empty($_zp_images)) {
		$_zp_images = NULL;
		$_zp_current_image = $_zp_current_image_restore;
		restore_context();
		return false;
	} else {
		$img = array_shift($_zp_images);
		$_zp_current_image = newImage($_zp_current_album, $img);
		return true;
	}
}

//*** Image Context ************************
//******************************************

/**
 * Sets the image passed as the current image
 *
 * @param object $image the image to become current
 */
function makeImageCurrent($image) {
	if (!is_object($image)) return;
	global $_zp_current_album, $_zp_current_image;
	$_zp_current_image = $image;
	$_zp_current_album = $_zp_current_image->getAlbum();
	set_context(ZP_INDEX | ZP_ALBUM | ZP_IMAGE);
}

/**
 * Returns the raw title of the current image.
 *
 * @return string
 */
function getImageTitle() {
	if(!in_context(ZP_IMAGE)) return false;
	global $_zp_current_image;
	return $_zp_current_image->getTitle();
}

/**
 * Returns a text-only title of the current image.
 *
 * @return string
 */
function getBareImageTitle() {
	return html_encode(getImageTitle());
}

/**
 * Returns the image title taged with not visible annotation.
 *
 * @return string
 */
function getAnnotatedImageTitle() {
	global $_zp_current_image;
	$title = getBareImageTitle();
	if (!$_zp_current_image->getShow()) {
		$title .= "\n".gettext('The image is marked not visible.');
	}
	return $title;
}
/**
 * Prints title of the current image and make it editable in place
 *
 * @param bool   $editable if set to true and the admin is logged in allows editing of the title
 * @param string $editclass CSS class applied to element if editable
 * @param mixed $messageIfEmpty Either bool or string. If false, echoes nothing when description is empty. If true, echoes default placeholder message if empty. If string, echoes string.
 * @author Ozh
 */
function printImageTitle($editable=false, $editclass='editable imageTitleEditable', $messageIfEmpty = true ) {
	if ( $messageIfEmpty === true ) {
		$messageIfEmpty = gettext('(No title...)');
	}
	printEditable('image', 'title', $editable, $editclass, $messageIfEmpty);
}

/**
 * Returns the 'n' of n of m images
 *
 * @return int
 */
function imageNumber() {
	global $_zp_current_image, $_zp_current_search, $_zp_current_album;
	$name = $_zp_current_image->getFileName();
	if (in_context(ZP_SEARCH)  || (in_context(ZP_SEARCH_LINKED) && !in_context(ZP_ALBUM_LINKED))) {
		$folder = $_zp_current_image->album->name;
		$images = $_zp_current_search->getImages();
		$c = 0;
		foreach ($images as $image) {
			$c++;
			if ($name == $image['filename'] && $folder == $image['folder']) {
				return $c;
			}
		}
	} else {
		if ($_zp_current_album->isDynamic()) {
			$search = $_zp_current_album->getSearchEngine();
			$images = $search->getImages();
			$c = 0;
			foreach ($images as $image) {
				$c++;
				if ($name == $image['filename']) {
					return $c;
				}
			}
		} else {
			return $_zp_current_image->getIndex()+1;
		}
	}
	return false;
}

/**
 * Returns the image date of the current image in yyyy-mm-dd hh:mm:ss format.
 * Pass it a date format string for custom formatting
 *
 * @param string $format formatting string for the data
 * @return string
 */
function getImageDate($format=null) {
	if(!in_context(ZP_IMAGE)) return false;
	global $_zp_current_image;
	$d = $_zp_current_image->getDateTime();
	if (empty($d) || ($d == '0000-00-00 00:00:00') ) {
		return false;
	}
	if (is_null($format)) {
		return $d;
	}
	return zpFormattedDate($format, strtotime($d));
}

/**
 * Prints the date of the current album and makes it editable in place if applicable
 *
 * @param string $before Insert here the text to be printed before the date.
 * @param string $nonemessage Insert here the text to be printed if there is no date.
 * @param string $format Format string for the date formatting
 * @param bool $editable when true, enables AJAX editing in place
 * @param string $editclass CSS class applied to element if editable
 * @param mixed $messageIfEmpty Either bool or string. If false, echoes nothing when description is empty. If true, echoes default placeholder message if empty. If string, echoes string.
 * @author Ozh
 */
function printImageDate($before='', $nonemessage='', $format=null, $editable=false, $editclass='', $messageIfEmpty = true) {
	if (is_null($format)) {
		$format = getOption('date_format');
	}
	$date = getImageDate($format);
	if ($date) {
		$date = $before . $date;
	} else {
		$date = '';
		if ($nonemessage != '') {
			$messageIfEmpty = $nonemessage;
		} elseif ( $messageIfEmpty === true ) {
			$messageIfEmpty = gettext('(No date...)');
		}
	}

	printEditable('image', 'date', $editable, $editclass, $messageIfEmpty, false, $date);
}

// IPTC fields
/**
 * Returns the Location field of the current image
 *
 * @return string
 */
function getImageLocation() {
	if(!in_context(ZP_IMAGE)) return false;
	global $_zp_current_image;
	return $_zp_current_image->getLocation();
}

/**
 * Returns the City field of the current image
 *
 * @return string
 */
function getImageCity() {
	if(!in_context(ZP_IMAGE)) return false;
	global $_zp_current_image;
	return $_zp_current_image->getcity();
}

/**
 * Returns the State field of the current image
 *
 * @return string
 */
function getImageState() {
	if(!in_context(ZP_IMAGE)) return false;
	global $_zp_current_image;
	return $_zp_current_image->getState();
}

/**
 * Returns the Country field of the current image
 *
 * @return string
 */
function getImageCountry() {
	if(!in_context(ZP_IMAGE)) return false;
	global $_zp_current_image;
	return $_zp_current_image->getCountry();
}

/**
 * Returns the raw description of the current image.
 * new lines are replaced with <br /> tags
 *
 * @return string
 */
function getImageDesc() {
	if(!in_context(ZP_IMAGE)) return false;
	global $_zp_current_image;
	return $_zp_current_image->getDesc();
}

/**
 * Returns a text-only description of the current image.
 *
 * @return string
 */
function getBareImageDesc() {
	return html_encode(getImageDesc());
}

/**
 * Prints the description of the current image.
 * Converts and displays line breaks set in the admin field as <br />.
 *
 * @param bool $editable set true to allow editing by the admin
 * @param string $editclass CSS class applied to element when editable
 * @param mixed $messageIfEmpty Either bool or string. If false, echoes nothing when description is empty. If true, echoes default placeholder message if empty. If string, echoes string.
 * @author Ozh
 */
function printImageDesc($editable=false, $editclass='', $messageIfEmpty = true) {
	if ( $messageIfEmpty === true ) {
		$messageIfEmpty = gettext('(No description...)');
	}
	printEditable('image', 'desc', $editable, $editclass, $messageIfEmpty, !getOption('tinyMCEPresent'));
}

/**
 * A composit for getting image data
 *
 * @param string $field which field you want
 * @return string
 */
function getImageData($field) {
	if(!in_context(ZP_IMAGE)) return false;
	global $_zp_current_image;
	return get_language_string($_zp_current_image->get($field));
}

/**
 * Returns the custom_data field of the current image
 *
 * @return string
 */
function getImageCustomData() {
	Global $_zp_current_image;
	return $_zp_current_image->getCustomData();
}

/**
 * Prints the custom_data field of the current image.
 * Converts and displays line breaks set in the admin field as <br />.
 *
 * @return string
 */
function printImageCustomData() {
	$data = getImageCustomData();
	$data = str_replace("\r\n", "\n", $data);
	$data = str_replace("\n", "<br />", $data);
	echo $data;
}

/**
 * Sets the image custom_data field
 *
 * @param string $val
 */
function setImageCustomData($val) {
	Global $_zp_current_image;
	$_zp_current_image->setCustomData($val);
	$_zp_current_image->save();
}

/**
 * Prints arbitrary data from the image object and make it editable if applicable
 *
 * @param string $field the field name of the data desired
 * @param string $label text to label the field.
 * @param bool $editable when true, enables AJAX editing in place
 * @param string $editclass CSS class applied to element if editable
 * @param mixed $messageIfEmpty Either bool or string. If false, echoes nothing when description is empty. If true, echoes default placeholder message if empty. If string, echoes string.
 * @author Ozh
 */
function printImageData($field, $label='', $editable=false, $editclass='', $messageIfEmpty = true) {
	if ( $messageIfEmpty === true ) {
		$messageIfEmpty = gettext('(No data...)');
	}
	if (empty($editclass)) $editclass = 'metadata';
	printEditable('image', $field, $editable, $editclass, $messageIfEmpty, false, false, $label);
}

/**
 * Get the unique ID of the current image.
 *
 * @return int
 */
function getImageID() {
	if (!in_context(ZP_IMAGE)) return false;
	global $_zp_current_image;
	return $_zp_current_image->id;
}

/**
 * Print the unique ID of the current image.
 */
function printImageID() {
	if (!in_context(ZP_IMAGE)) return false;
	global $_zp_current_image;
	echo "image_".getImageID();
}

/**
 * Get the sort order of this image.
 *
 * @return string
 */
function getImageSortOrder() {
	if (!in_context(ZP_IMAGE)) return false;
	global $_zp_current_image;
	return $_zp_current_image->getSortOrder();
}

/**
 * Print the sort order of this image.
 */
function printImageSortOrder() {
	if (!in_context(ZP_IMAGE)) return false;
	echo getImageSortOrder();
}

/**
 * True if there is a next image
 *
 * @return bool
 */
function hasNextImage() {
	global $_zp_current_image;
	if (is_null($_zp_current_image)) return false;
	return $_zp_current_image->getNextImage();
}
/**
 * True if there is a previous image
 *
 * @return bool
 */
function hasPrevImage() {
	global $_zp_current_image;
	if (is_null($_zp_current_image)) return false;
	return $_zp_current_image->getPrevImage();
}

/**
 * Returns the url of the next image.
 *
 * @return string
 */
function getNextImageURL() {
	if(!in_context(ZP_IMAGE)) return false;
	global $_zp_current_album, $_zp_current_image;
	if (is_null($_zp_current_image)) return false;
	$nextimg = $_zp_current_image->getNextImage();
	return rewrite_path("/" . pathurlencode($nextimg->album->name) . "/" . urlencode($nextimg->filename) . im_suffix(),
		"/index.php?album=" . urlencode($nextimg->album->name) . "&image=" . urlencode($nextimg->filename));
}

/**
 * Returns the url of the previous image.
 *
 * @return string
 */
function getPrevImageURL() {
	if(!in_context(ZP_IMAGE)) return false;
	global $_zp_current_album, $_zp_current_image;
	if (is_null($_zp_current_image)) return false;
	$previmg = $_zp_current_image->getPrevImage();
	return rewrite_path("/" . pathurlencode($previmg->album->name) . "/" . urlencode($previmg->filename) . im_suffix(),
		"/index.php?album=" . urlencode($previmg->album->name) . "&image=" . urlencode($previmg->filename));
}

/**
* Returns the url of the first image in current album.
*
* @return string
* @author gerben
*/
function getFirstImageURL() {
	global $_zp_current_album;
	if (is_null($_zp_current_album)) return false;
	$firstimg = $_zp_current_album->getImage(0);
	return rewrite_path("/" . pathurlencode($_zp_current_album->name) . "/" . urlencode($firstimg->filename) . im_suffix(),
											"/index.php?album=" . urlencode($_zp_current_album->name) . "&image=" . urlencode($firstimg->filename));
}

/**
* Returns the url of the last image in current album.
*
* @return string
*/
function getLastImageURL() {
	global $_zp_current_album;
	if (is_null($_zp_current_album)) return false;
	$lastimg = $_zp_current_album->getImage($_zp_current_album->getNumImages() - 1);
	return rewrite_path("/" . pathurlencode($_zp_current_album->name) . "/" . urlencode($lastimg->filename) . im_suffix(),
											"/index.php?album=" . urlencode($_zp_current_album->name) . "&image=" . urlencode($lastimg->filename));
}

/**
 * Prints out the javascript to preload the next and previous images
 *
 */
function printPreloadScript() {
	global $_zp_current_image;
	$size = getOption('image_size');
	if (hasNextImage() || hasPrevImage()) {
		echo "<script type=\"text/javascript\">\n// <!-- <![CDATA[\n";
		if (hasNextImage()) {
			$nextimg = $_zp_current_image->getNextImage();
			echo "  nextimg = new Image();\n  nextimg.src = \"" . $nextimg->getSizedImage($size) . "\";\n";
		}
		if (hasPrevImage()) {
			$previmg = $_zp_current_image->getPrevImage();
			echo "  previmg = new Image();\n  previmg.src = \"" . $previmg->getSizedImage($size) . "\";\n";
		}
		echo "</script>\n\\ ]]> -->\n";
	}
}

/**
 * Returns the thumbnail of the previous image.
 *
 * @return string
 */
function getPrevImageThumb() {
	if(!in_context(ZP_IMAGE)) return false;
	global $_zp_current_image;
	if (is_null($_zp_current_image)) return false;
	$img = $_zp_current_image->getPrevImage();
	return $img->getThumb();
}

/**
 * Returns the thumbnail of the next image.
 *
 * @return string
 */
function getNextImageThumb() {
	if(!in_context(ZP_IMAGE)) return false;
	global $_zp_current_image;
	if (is_null($_zp_current_image)) return false;
	$img = $_zp_current_image->getNextImage();
	return $img->getThumb();
}

/**
 * Returns the url of the current image.
 *
 * @return string
 */
function getImageLinkURL() {
	if(!in_context(ZP_IMAGE)) return false;
	global $_zp_current_image;
	if (is_null($_zp_current_image)) return false;
	return $_zp_current_image->getImageLink();
}

/**
 * Prints the link to the current  image.
 *
 * @param string $text text for the link
 * @param string $title title tag for the link
 * @param string $class optional style class for the link
 * @param string $id optional style id for the link
 */
function printImageLink($text, $title, $class=NULL, $id=NULL) {
	printLink(getImageLinkURL(), $text, $title, $class, $id);
}

/**
 * Print the entire <div> for a thumbnail. If we are in sorting mode, then only
 * the image is inserted, if not, then the hyperlink to the image is also added.
 *
 * @author Todd Papaioannou (lucky@luckyspin.org)
 * @since  1.0.0
 */
function printImageDiv() {
	if (!isset($_GET['sortable'])) {
		echo '<a href="'.htmlspecialchars(getImageLinkURL()).'" title="'.html_encode(getImageTitle()).'">';
	}
	printImageThumb(getImageTitle());

	if (!isset($_GET['sortable'])) {
		echo '</a>';
	}
}

/**
 * Returns the Metadata infromation from the current image
 *
 * @param $image optional image object
 * @return array
 */
function getImageMetaData($image=NULL) {
	global $_zp_current_image;
	if (is_null($image)) $image = $_zp_current_image;
	if (is_null($image) || !$image->get('hasMetadata')) return false;
	return $image->getMetaData();
}

/**
 * Prints the Metadata data of the current image, and make each value editable in place if applicable
 *
 * @param string $title title tag for the class
 * @param bool $toggle set to true to get a javascript toggle on the display of the data
 * @param string $id style class id
 * @param string $class style class
 * @param bool $editable set true to allow editing by the admin
 * @param string $editclass CSS class applied to element when editable
 * @param mixed $messageIfEmpty Either bool or string. If false, echoes nothing when description is empty. If true, echoes default placeholder message if empty. If string, echoes string.
 * @author Ozh
 */
function printImageMetadata($title=NULL, $toggle=true, $id='imagemetadata', $class=null, $editable = false, $editclass='', $messageIfEmpty = '') {

	if (false === ($exif = getImageMetaData()))
		return;

	global $_zp_exifvars, $_zp_current_image;

	if (is_null($title))
		$title = gettext('Image Info');

	// Metadata values will be editable only with sufficient privileges
	$editable = ( $editable && zp_loggedin());

	if ( $messageIfEmpty === true ) {
		$messageIfEmpty = gettext('(No data)');
	}

	$dataid = $id . '_data';
	echo "<div" . (($class) ? " class=\"$class\"" : "") . (($id) ? " id=\"$id\"" : "") . ">\n";
	if ($toggle) echo "<a href=\"javascript:toggle('$dataid');\">";
	echo "<span class='metadata_title'>$title</span>";
	if ($toggle) echo "</a>\n";

	echo "<table id=\"$dataid\"" . ($toggle ? " style=\"display: none;\"" : '') . ">\n";
	foreach ($exif as $field => $value) {
		$display = $_zp_exifvars[$field][3];
		if ($display) {
			$label = $_zp_exifvars[$field][2];
			echo "<tr><td class=\"label\">$label:</td><td class=\"value\">";
			printEditable('image', $field, $editable, $editclass, $messageIfEmpty, false, $value);
			echo "</td></tr>\n";
		}
	}
	echo "</table>\n</div>\n";

}

/**
 * Returns an array with the height & width
 *
 * @param int $size size
 * @param int $width width
 * @param int $height height
 * @param int $cw crop width
 * @param int $ch crop height
 * @param int $cx crop x axis
 * @param int $cy crop y axis
 * @return array
 */
function getSizeCustomImage($size, $width=NULL, $height=NULL, $cw=NULL, $ch=NULL, $cx=NULL, $cy=NULL) {
	if(!in_context(ZP_IMAGE)) return false;
	global $_zp_current_album, $_zp_current_image, $_zp_flash_player;
	if (is_null($_zp_current_image)) return false;
	$h = $_zp_current_image->getHeight();
	$w = $_zp_current_image->getWidth();
	if (isImageVideo()) { // size is determined by the player
		return array($w, $h);
	}
	$h = $_zp_current_image->getHeight();
	$w = $_zp_current_image->getWidth();
	$side = getOption('image_use_side');
	$us = getOption('image_allow_upscale');

	$args = getImageParameters(array($size, $width, $height, $cw, $ch, $cx, $cy, null),$_zp_current_album->name);
	@list($size, $width, $height, $cw, $ch, $cx, $cy, $quality, $thumb, $crop, $thumbstandin, $passedWM, $adminrequest, $gray) = $args;
	if (!empty($size)) {
		$dim = $size;
		$width = $height = false;
	} else if (!empty($width)) {
		$dim = $width;
		$size = $height = false;
	} else if (!empty($height)) {
		$dim = $height;
		$size = $width = false;
	} else {
		$dim = 1;
	}

	if ($w == 0) {
		$hprop = 1;
	} else {
		$hprop = round(($h / $w) * $dim);
	}
	if ($h == 0) {
		$wprop = 1;
	} else {
		$wprop = round(($w / $h) * $dim);
	}

	if (($size && ($side == 'longest' && $h > $w) || ($side == 'height') || ($side == 'shortest' && $h < $w))	|| $height) {
		// Scale the height
		$newh = $dim;
		$neww = $wprop;
	} else {
		// Scale the width
		$neww = $dim;
		$newh = $hprop;
	}
	if (!$us && $newh >= $h && $neww >= $w) {
		return array($w, $h);
	} else {
		if ($cw && $cw < $neww) $neww = $cw;
		if ($ch && $ch < $newh) $newh = $ch;
		if ($size && $ch && $cw) { $neww = $cw; $newh = $ch; }
		return array($neww, $newh);
	}
}

/**
 * Returns an array [width, height] of the default-sized image.
 *
 * @param int $size override the 'image_zize' option
 *
 * @return array
 */
function getSizeDefaultImage($size=NULL) {
	if (is_null($size)) $size = getOption('image_size');
	return getSizeCustomImage($size);
}

/**
 * Returns an array [width, height] of the original image.
 *
 * @return array
 */
function getSizeFullImage() {
	global $_zp_current_image;
	if (is_null($_zp_current_image)) return false;
	return array($_zp_current_image->getWidth(), $_zp_current_image->getHeight());
}

/**
 * The width of the default-sized image (in printDefaultSizedImage)
 *
 * @param int $size override the 'image_zize' option
 *
 * @return int
 */
function getDefaultWidth($size=NULL) {
	$size_a = getSizeDefaultImage($size);
	return $size_a[0];
}

/**
 * Returns the height of the default-sized image (in printDefaultSizedImage)
 *
 * @param int $size override the 'image_zize' option
 *
 * @return int
 */
function getDefaultHeight($size=NULL) {
	$size_a = getSizeDefaultImage($size);
	return $size_a[1];
}

/**
 * Returns the width of the original image
 *
 * @return int
 */
function getFullWidth() {
	global $_zp_current_image;
	if (is_null($_zp_current_image)) return false;
	return $_zp_current_image->getWidth();
}

/**
 * Returns the height of the original image
 *
 * @return int
 */
function getFullHeight() {
	global $_zp_current_image;
	if (is_null($_zp_current_image)) return false;
	return $_zp_current_image->getHeight();
}

/**
 * Returns true if the image is landscape-oriented (width is greater than height)
 *
 * @return bool
 */
function isLandscape() {
	if (getFullWidth() >= getFullHeight()) return true;
	return false;
}

/**
 * Returns the url to the default sized image.
 *
 * @return string
 */
function getDefaultSizedImage() {
	global $_zp_current_image;
	if (is_null($_zp_current_image)) return false;
	return $_zp_current_image->getSizedImage(getOption('image_size'));
}

/**
 * Show video player with video loaded or display the image.
 *
 * @param string $alt Alt text
 * @param string $class Optional style class
 * @param string $id Optional style id
 */
function printDefaultSizedImage($alt, $class=NULL, $id=NULL) {
	global $_zp_current_image;
	if (is_null($_zp_current_image)) return;
	if (!$_zp_current_image->getShow()) {
		$class .= " not_visible";
	}
	$album = $_zp_current_image->getAlbum();
	$pwd = $album->getPassword();
	if (zp_loggedin() && !empty($pwd)) {
		$class .= " password_protected";
	}
	if (isImagePhoto()) { //Print images
		$html = '<img src="' . htmlspecialchars(getDefaultSizedImage()) . '" alt="' . html_encode($alt) . '"' .
			' width="' . getDefaultWidth() . '" height="' . getDefaultHeight() . '"' .
			(($class) ? " class=\"$class\"" : "") .
			(($id) ? " id=\"$id\"" : "") . " />";
		$html = zp_apply_filter('standard_image_html', $html);
		echo $html;
	} else { // better be a plugin class then
		echo $_zp_current_image->getBody();
	}
}

/**
 * Returns the url to the thumbnail of the current image.
 *
 * @return string
 */
function getImageThumb() {
	global $_zp_current_image;
	if (is_null($_zp_current_image)) return false;
	return $_zp_current_image->getThumb();
}
/**
 * @param string $alt Alt text
 * @param string $class optional class tag
 * @param string $id optional id tag
 */
function printImageThumb($alt, $class=NULL, $id=NULL) {
	global $_zp_current_image;
	if (is_null($_zp_current_image)) return;
	if (!$_zp_current_image->getShow()) {
		$class .= " not_visible";
	}
	$album = $_zp_current_image->getAlbum();
	$pwd = $album->getPassword();
	if (zp_loggedin() && !empty($pwd)) {
		$class .= " password_protected";
	}
	if (getOption('thumb_crop') && !is_null($cy = $_zp_current_image->get('thumbY'))) {
		$ts = getOption('thumb_size');
		$sw = getOption('thumb_crop_width');
		$sh = getOption('thumb_crop_height');
		$cx = $_zp_current_image->get('thumbX');
		$cw = $_zp_current_image->get('thumbW');
		$ch = $_zp_current_image->get('thumbH');
		// upscale to thumb_size proportions
		if ($sw == $sh) { // square crop, set the size/width to thumbsize
			$sw = $sh = $ts;
		} else {
			if ($sw > $sh) {
				$r = $ts/$sw;
				$sw = $ts;
				$sh = $sh * $r;
			} else {
				$r = $ts/$sh;
				$sh = $ts;
				$sh = $r * $sh;
			}
		}
		$url = getCustomImageURL(NULL, $sw, $sh, $cw, $ch, $cx, $cy, true);
	} else {
		$url = getImageThumb();
	}

	$h = getOption('thumb_crop_height');
	if (!empty($h)) {
		$h = " height=\"$h\"";
	}
	$w = getOption('thumb_crop_width');
	if (!empty($w)) {
		$w = " width=\"$w\"";
	}
	$class = trim($class);
	$html = "<img src=\"" . htmlspecialchars($url) . "\" alt=\"" . html_encode($alt) . "\"" .
	((getOption('thumb_crop')) ? $w.$h : "") .
	(($class) ? " class=\"$class\"" : "") .
	(($id) ? " id=\"$id\"" : "") . " />";
	$html = zp_apply_filter('standard_image_thumb_html',$html);
	echo $html;
}

/**
 * Returns the url to original image.
 * It will return a protected image is the option "protect_full_image" is set
 *
 * @return string
 */
function getFullImageURL() {
	global $_zp_current_image;
	if (is_null($_zp_current_image)) return false;
	$outcome = getOption('protect_full_image');
	if ($outcome == 'No access') return NULL;
	if (isImageVideo()) {		// Download, Protected View, and Unprotected access all allowed
		// Search for a high quality version of the video
		$album = $_zp_current_image->getAlbum();
		$folder = $album->getFolder();
		$video = $_zp_current_image->getFileName();
		$video = substr($video, 0, strrpos($video,'.'));
		foreach(array(".ogg",".OGG",".avi",".AVI",".wmv",".WMV") as $ext) {
			if(file_exists(internalToFilesystem(getAlbumFolder().$folder."/".$video.$ext))) {
				return getAlbumFolder(WEBPATH). $folder."/".$video.$ext;
			}
		}
		return getUnprotectedImageURL();
	} else if (isImagePhoto()) { // normal image
		if ($outcome == 'Unprotected') {
			return getUnprotectedImageURL();
		} else {
			return getProtectedImageURL();
		}
	}
	return NULL;
}

/**
 * Returns the "raw" url to the image in the albums folder
 *
 * @return string
 *
 */
function getUnprotectedImageURL() {
	global $_zp_current_image;
	if (is_null($_zp_current_image)) return false;
	return $_zp_current_image->getFullImage();
}
/**
 * Returns an url to the password protected/watermarked current image
 *
 * @param object $image optional image object overrides the current image
 * @param string $disposal set to override the 'protect_full_image' option
 * @return string
 **/
function getProtectedImageURL($image=NULL, $disposal=NULL) {
	global $_zp_current_image;
	if ($disposal == 'No access') return NULL;
	if (is_null($image)) {
		if(!in_context(ZP_IMAGE)) return false;
		if (is_null($_zp_current_image)) return false;
		$image = $_zp_current_image;
	}
	$album = $image->getAlbum();
	$wmt = $image->getWatermark();
	if (!empty($wmt) || !($image->getWMUse() & WATERMARK_FULL)) {
		$wmt = NULL;
	}
	$args = array('FULL', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $wmt, NULL);
	$cache_file = getImageCacheFilename($album->name, $image->filename, $args);
	$cache_path = SERVERCACHE.$cache_file;
	if (empty($disposal) && file_exists($cache_path)) {
		return WEBPATH.'/'.CACHEFOLDER.pathurlencode(imgSrcURI($cache_file));
	} else {
		$params = '&q='.getOption('full_image_quality');
		$watermark_use_image = $image->getWatermark();
		if (empty($watermark_use_image) || !($image->getWMUse() & WATERMARK_FULL)) {
			$watermark_use_image = getAlbumInherited($album->name, 'watermark', $id);
		}
		if (empty($watermark_use_image)) {
			$watermark_use_image = getOption('fullimage_watermark');
		}
		if (!empty($watermark_use_image)) {
			$params .= '&wmk='.$watermark_use_image;
		}
		if ($disposal) {
			$params .= '&dsp='.$disposal;
		}
		return WEBPATH.'/'.ZENFOLDER .'/full-image.php?a='.urlencode($album->name).'&i='.urlencode($image->filename).$params;
	}
}

/**
 * Returns a link to the current image custom sized to $size
 *
 * @param int $size The size the image is to be
 */
function getSizedImageURL($size) {
	getCustomImageURL($size);
}

/**
 * Returns the url to the  image in that dimensions you define with this function.
 *
 * $size, $width, and $height are used in determining the final image size.
 * At least one of these must be provided. If $size is provided, $width and
 * $height are ignored. If both $width and $height are provided, the image
 * will have those dimensions regardless of the original image height/width
 * ratio. (Yes, this means that the image may be distorted!)

 * The $crop* parameters determine the portion of the original image that
 * will be incorporated into the final image.

 * $cropw and $croph "sizes" are typically proportional. That is you can
 * set them to values that reflect the ration of width to height that you
 * want for the final image. Typically you would set them to the fincal
 * height and width. These values will always be adjusted so that they are
 * not larger than the original image dimensions.

 * The $cropx and $cropy values represent the offset of the crop from the
 * top left corner of the image. If these values are provided, the $croph
 * and $cropw parameters are treated as absolute pixels not proportions of
 * the image. If cropx and cropy are not provided, the crop will be
 * "centered" in the image.

 * When $corpx and $cropy are not provided the crop is offset from the top
 * left proportionally to the ratio of the final image size and the crop
 * size.

 * Some typical croppings:

 * $size=200, $width=NULL, $height=NULL, $cropw=200, $croph=100,
 * $cropx=NULL, $cropy=NULL produces an image cropped to a 2x1 ratio which
 * will fit in a 200x200 pixel frame.

 * $size=NUL, $width=200, $height=NULL, $cropw=200, $croph=100, $cropx=100,
 * $cropy=10 will will take a 200x100 pixel slice from (10,100) of the
 * picture and create a 200x100 image

 * $size=NUL, $width=200, $height=100, $cropw=200, $croph=120, $cropx=NULL,
 * $cropy=NULL will produce a (distorted) image 200x100 pixels from a 1x0.6
 * crop of the image.


 * $size=NUL, $width=200, $height=NULL, $cropw=180, $croph=120, $cropx=NULL, $cropy=NULL
 * will produce an image that is 200x133 from a 1.5x1 crop that is 5% from the left
 * and 15% from the top of the image.
 *
 * @param int $size the size of the image to have
 * @param int $width width
 * @param int $height height
 * @param int $cropw cropwidth
 * @param int $croph crop height
 * @param int $cropx crop part x axis
 * @param int $cropy crop part y axis
 * @param bool $thumbStandin set true to inhibit watermarking
 * @param bool $gray set true to force to grayscale
 * @return string
 */
function getCustomImageURL($size, $width=NULL, $height=NULL, $cropw=NULL, $croph=NULL, $cropx=NULL, $cropy=NULL, $thumbStandin=false, $gray=false) {
	global $_zp_current_image;
	if (is_null($_zp_current_image)) return false;
	return $_zp_current_image->getCustomImage($size, $width, $height, $cropw, $croph, $cropx, $cropy, $thumbStandin, $gray);
}

/**
 * Print normal video or custom sized images.
 * Note: a class of 'not_visible' or 'password_protected' will be added as appropriate
 *
 * Notes on cropping:
 *
 * The $crop* parameters determine the portion of the original image that will be incorporated
 * into the final image. The w and h "sizes" are typically proportional. That is you can set them to
 * values that reflect the ration of width to height that you want for the final image. Typically
 * you would set them to the fincal height and width.
 *
 * @param string $alt Alt text for the url
 * @param int $size size
 * @param int $width width
 * @param int $height height
 * @param int $cropw crop width
 * @param int $croph crop height
 * @param int $cropx crop x axis
 * @param int $cropy crop y axis
 * @param string $class Optional style class
 * @param string $id Optional style id
 * @param bool $thumbStandin set true to inhibit watermarking
 * @param bool $gray set true to force grayscale

 * */
function printCustomSizedImage($alt, $size, $width=NULL, $height=NULL, $cropw=NULL, $croph=NULL, $cropx=NULL, $cropy=NULL, $class=NULL, $id=NULL, $thumbStandin=false, $gray=false) {
	global $_zp_current_image;
	if (is_null($_zp_current_image)) return;
	if (!$_zp_current_image->getShow()) {
		$class .= " not_visible";
	}
	$album = $_zp_current_image->getAlbum();
	$pwd = $album->getPassword();
	if (zp_loggedin() && !empty($pwd)) {
		$class .= " password_protected";
	}
	if ($size) {
		$dims = getSizeCustomImage($size);
		$sizing = ' width="'.$dims[0].'" height="'.$dims[1].'"';
	} else {
		$sizing = '';
		if ($width) $sizing .= ' width="'.$width.'"';
		if ($height) $sizing .= ' height="'.$height.'"';
	}
	if ($id) $id = ' id="'.$id.'"';
	if ($class) $id .= ' class="'.$class.'"';
	if (isImagePhoto() || $thumbStandin) {
		$html = '<img src="' . htmlspecialchars(getCustomImageURL($size, $width, $height, $cropw, $croph, $cropx, $cropy, $thumbStandin, $gray)) . '"' .
			' alt="' . html_encode($alt) . '"' .
			$id .
			$sizing .
			' />';
		$html = zp_apply_filter('custom_image_html', $html, $thumbStandin);
		echo $html;
	} else { // better be a plugin
		echo $_zp_current_image->getBody();
	}
}

/**
 * Returns a link to a un-cropped custom sized version of the current image within the given height and width dimensions.
 * Use for sized images.
 *
 * @param int $width width
 * @param int $height height
 * @return string
 */
function getCustomSizedImageMaxSpace($width, $height) {
	global $_zp_current_image;
	if (is_null($_zp_current_image)) return false;
	getMaxSpaceContainer($width, $height, $_zp_current_image);
	return getCustomImageURL(NULL, $width, $height);
}

/**
 * Returns a link to a un-cropped custom sized version of the current image within the given height and width dimensions.
 * Use for sized thumbnails.
 *
 * @param int $width width
 * @param int $height height
 * @return string
 */
function getCustomSizedImageThumbMaxSpace($width, $height) {
	global $_zp_current_image;
	if (is_null($_zp_current_image)) return false;
	getMaxSpaceContainer($width, $height, $_zp_current_image, true);
	return getCustomImageURL(NULL, $width, $height, NULL, NULL, NULL, NULL, true);
}

/**
 * Creates image thumbnails which will fit un-cropped within the width & height parameters given
 *
 * @param string $alt Alt text for the url
 * @param int $width width
 * @param int $height height
 * @param string $class Optional style class
 * @param string $id Optional style id
	*/
function printCustomSizedImageThumbMaxSpace($alt='',$width,$height,$class=NULL,$id=NULL) {
	global $_zp_current_image;
	if (is_null($_zp_current_image)) return;
	getMaxSpaceContainer($width, $height, $_zp_current_image, true);
	printCustomSizedImage($alt, NULL, $width, $height, NULL, NULL, NULL, NULL, $class, $id, true);
}

/**
 * Print normal video or un-cropped within the given height and width dimensions. Use for sized images or thumbnails in an album.
 * Note: a class of 'not_visible' or 'password_protected' will be added as appropriate
 *
 * @param string $alt Alt text for the url
 * @param int $width width
 * @param int $height height
 * @param string $class Optional style class
 * @param string $id Optional style id
 */
function printCustomSizedImageMaxSpace($alt='',$width,$height,$class=NULL,$id=NULL, $thumb=false) {
	global $_zp_current_image;
	if (is_null($_zp_current_image)) return;
	getMaxSpaceContainer($width, $height, $_zp_current_image, $thumb);
	printCustomSizedImage($alt, NULL, $width, $height, NULL, NULL, NULL, NULL, $class, $id, $thumb);
}


/**
 * Prints link to an image of specific size
 * @param int $size how big
 * @param string $text URL text
 * @param string $title URL title
 * @param string $class optional URL class
 * @param string $id optional URL id
 */
function printSizedImageLink($size, $text, $title, $class=NULL, $id=NULL) {
	printLink(getSizedImageURL($size), $text, $title, $class, $id);
}

/**

* Retuns the count of comments on the current image
*
* @return int
*/
function getCommentCount() {
	global $_zp_current_image, $_zp_current_album, $_zp_current_zenpage_page, $_zp_current_zenpage_news;
	if (in_context(ZP_IMAGE) && in_context(ZP_ALBUM)) {
		if (is_null($_zp_current_image)) return false;
		return $_zp_current_image->getCommentCount();
	} else if (!in_context(ZP_IMAGE) && in_context(ZP_ALBUM)) {
		if (is_null($_zp_current_album)) return false;
		return $_zp_current_album->getCommentCount();
	}
	if(function_exists('is_News')) {
		if(is_News()) {
			return $_zp_current_zenpage_news->getCommentCount();
		}
		if(is_Pages()) {
			return $_zp_current_zenpage_page->getCommentCount();
		}
	}
}

/**
 * Returns true if neither the album nor the image have comments closed
 *
 * @return bool
 */
function getCommentsAllowed() {
	global $_zp_current_image, $_zp_current_album;
	if (in_context(ZP_IMAGE)) {
		if (is_null($_zp_current_image)) return false;
		return $_zp_current_image->getCommentsAllowed();
	} else {
		return $_zp_current_album->getCommentsAllowed();
	}
}

/**
 * Iterate through comments; use the ZP_COMMENT context.
 * Return true if there are more comments
 * @param  bool $desc set true for desecnding order
 *
 * @return bool
 */
function next_comment($desc=false) {
	global $_zp_current_image, $_zp_current_album, $_zp_current_comment, $_zp_comments, $_zp_current_zenpage_page, $_zp_current_zenpage_news;
	//ZENPAGE: comments support
	if (is_null($_zp_current_comment)) {
		if (in_context(ZP_IMAGE) AND in_context(ZP_ALBUM)) {
			if (is_null($_zp_current_image)) return false;
			$_zp_comments = $_zp_current_image->getComments(false, false, $desc);
		} else if (!in_context(ZP_IMAGE) AND in_context(ZP_ALBUM)) {
			$_zp_comments = $_zp_current_album->getComments(false, false, $desc);
		}
		if(function_exists('is_NewsArticle')) {
			if (is_NewsArticle()) {
				$_zp_comments = $_zp_current_zenpage_news->getComments(false, false, $desc);
			}
			if(is_Pages()) {
				$_zp_comments = $_zp_current_zenpage_page->getComments(false, false, $desc);
			}
		}
		if (empty($_zp_comments)) { return false; }
	} else if (empty($_zp_comments)) {
		$_zp_comments = NULL;
		$_zp_current_comment = NULL;
		rem_context(ZP_COMMENT);
		return false;
	}
	$_zp_current_comment = array_shift($_zp_comments);
	if ($_zp_current_comment['anon']) {
		$_zp_current_comment['email'] = $_zp_current_comment['name'] = '<'.gettext("Anonymous").'>';
	}
	add_context(ZP_COMMENT);
	return true;
}

/**
 * Returns the data from the last comment posted
 * @param bool $numeric Set to true for old themes to get 0->6 indices rather than descriptive ones
 *
 * @return array
 */
function getCommentStored($numeric=false) {
	global $_zp_comment_stored;
	$stored = array('name'=>$_zp_comment_stored[0],'email'=>$_zp_comment_stored[1],
							 'website'=>$_zp_comment_stored[2],'comment'=>$_zp_comment_stored[3],
							 'saved'=>$_zp_comment_stored[4],'private'=>$_zp_comment_stored[5],
							 'anon'=>$_zp_comment_stored[6],'custom'=>$_zp_comment_stored[7]);
	if ($numeric) {
		return Array_merge($stored);
	}
	return $stored;
}

/*** Comment Context **********************/
/******************************************/

/**
 * Returns the comment author's name
 *
 * @return string
 */
function getCommentAuthorName() { global $_zp_current_comment; return $_zp_current_comment['name']; }

/**
 * Returns the comment author's email
 *
 * @return string
 */
function getCommentAuthorEmail() { global $_zp_current_comment; return $_zp_current_comment['email']; }
/**
 * Returns the comment author's website
 *
 * @return string
 */
function getCommentAuthorSite() { global $_zp_current_comment; return $_zp_current_comment['website']; }
/**
 * Prints a link to the author

 *
 * @param string $title URL title tag
 * @param string $class optional class tag
 * @param string $id optional id tag
 */
function printCommentAuthorLink($title=NULL, $class=NULL, $id=NULL) {
	global $_zp_current_comment;
	$site = $_zp_current_comment['website'];
	$name = $_zp_current_comment['name'];
	if ($_zp_current_comment['anon']) {
		$name = substr($name, 1, strlen($name)-2); // strip off the < and >
	}
	$namecoded = htmlspecialchars($_zp_current_comment['name'],ENT_QUOTES);
	if (empty($site)) {
		echo $namecoded;
	} else {
		if (is_null($title)) {
			$title = "Visit ".$name;
		}
		printLink($site, $namecoded, $title, $class, $id);
	}
}

/**
 * Returns a formatted date and time for the comment.
 * Uses the "date_format" option for the formatting unless
 * a format string is passed.
 *
 * @param string $format 'strftime' date/time format
 * @return string
 */
function getCommentDateTime($format = NULL) {
	if (is_null($format)) {
		$format = getOption('date_format');
	}
	global $_zp_current_comment;
	return myts_date($format, $_zp_current_comment['date']);
}

/**
 * Returns the body of the current comment
 *
 * @return string
 */
function getCommentBody() {
	global $_zp_current_comment;
	return str_replace("\n", "<br />", stripslashes($_zp_current_comment['comment']));
}

/**
 * Creates a link to the admin comment edit page for the current comment
 *
 * @param string $text Link text
 * @param string $before text to go before the link
 * @param string $after text to go after the link
 * @param string $title title text
 * @param string $class optional css clasee
 * @param string $id optional css id
 */
function printEditCommentLink($text, $before='', $after='', $title=NULL, $class=NULL, $id=NULL) {
	global $_zp_current_comment;
	if (zp_loggedin(COMMENT_RIGHTS)) {
		echo $before;
		printLink(WEBPATH . '/' . ZENFOLDER . '/admin-comments.php?page=editcomment&id=' . $_zp_current_comment['id'], $text, $title, $class, $id);
		echo $after;
	}
}

/**
 * Creates an URL for to download of a zipped copy of the current album
 *
 * NOTE: This operation will include ALL files within the album without regard for published status
 *
 */
function printAlbumZip(){
	global $_zp_current_album;
	if (!is_null($_zp_current_album) && !$_zp_current_album->isDynamic()) {
		echo'<a href="' . WEBPATH . '/' . ZENFOLDER . "/album-zip.php?album=" . pathurlencode($_zp_current_album->name) .
			'" title="'.gettext('Download Zip of the Album').'">'.gettext('Download a zip file of this album').'</a>';
	}
}

/**
 * Gets latest comments for images and albums
 *
 * @param int $number how many comments you want.
 * @param string $type	"all" for all latest comments of all images and albums
 * 											"image" for the lastest comments of one specific image
 * 											"album" for the latest comments of one specific album
 * @param int $itemID the ID of the element to get the comments for if $type != "all"
 */
function getLatestComments($number,$type="all",$itemID="") {
	$itemID = sanitize_numeric($itemID);
	$passwordcheck1 = "";
	$passwordcheck2 = "";
	if (!zp_loggedin(ADMIN_RIGHTS)) {
		$albumscheck = query_full_array("SELECT * FROM " . prefix('albums'). " ORDER BY title");
		foreach ($albumscheck as $albumcheck) {
			if(!checkAlbumPassword($albumcheck['folder'], $hint)) {
				$albumpasswordcheck1= " AND i.albumid != ".$albumcheck['id'];
				$albumpasswordcheck2= " AND a.id != ".$albumcheck['id'];
				$passwordcheck1 = $passwordcheck1.$albumpasswordcheck1;
				$passwordcheck2 = $passwordcheck2.$albumpasswordcheck2;
			}
		}
	}
	switch ($type) {
		case "image":
			$whereImages = " WHERE i.show = 1 AND i.id = ".$itemID." AND c.ownerid = ".$itemID." AND i.albumid = a.id AND c.private = 0 AND c.inmoderation = 0 AND (c.type IN (".zp_image_types("'") ."))".$passwordcheck1;
			break;
		case "album":
			$whereAlbums = " WHERE a.show = 1 AND a.id = ".$itemID." AND c.ownerid = ".$itemID." AND c.private = 0 AND c.inmoderation = 0 AND c.type = 'albums'".$passwordcheck2;
			break;
		case "all":
			$whereImages = " WHERE i.show = 1 AND c.ownerid = i.id AND i.albumid = a.id AND c.private = 0 AND c.inmoderation = 0 AND (c.type IN (".zp_image_types("'") ."))".$passwordcheck1;
			$whereAlbums = " WHERE a.show = 1 AND c.ownerid = a.id AND c.private = 0 AND c.inmoderation = 0 AND c.type = 'albums'".$passwordcheck2;
			break;
	}
	$comments_images = array();
	$comments_albums = array();
	if ($type === "all" OR $type === "image") {
		$comments_images = query_full_array("SELECT c.id, i.title, i.filename, a.folder, a.title AS albumtitle, c.name, c.type, c.website,"
		. " c.date, c.anon, c.comment FROM ".prefix('comments')." AS c, ".prefix('images')." AS i, ".prefix('albums')." AS a "
		. $whereImages
		. " ORDER BY c.id DESC LIMIT $number");
	}
	if ($type === "all" OR $type === "album") {
		$comments_albums = query_full_array("SELECT c.id, a.folder, a.title AS albumtitle, c.name, c.type, c.website,"
		. " c.date, c.anon, c.comment FROM ".prefix('comments')." AS c, ".prefix('albums')." AS a "
		. $whereAlbums
		. " ORDER BY c.id DESC LIMIT $number");
	}
	$comments = array();
	foreach ($comments_albums as $comment) {
		$comments[$comment['id']] = $comment;
	}
	foreach ($comments_images as $comment) {
		$comments[$comment['id']] = $comment;
	}
	krsort($comments);
	return array_slice($comments, 0, $number);
}


/**
 * Prints out latest comments for images and albums
 *
 * @param int $number how many comments you want.
 * @param string $shorten the number of characters to shorten the comment display
 * @param string $type	"all" for all latest comments of all images and albums
 * 											"image" for the lastest comments of one specific image
 * 											"album" for the latest comments of one specific album
 * @param int $itemID the ID of the element to get the comments for if $type != "all"
 */
function printLatestComments($number, $shorten='123',$type="all",$itemID="") {
	if(getOption('mod_rewrite')) {
		$albumpath = "/"; $imagepath = "/"; $modrewritesuffix = getOption('mod_rewrite_image_suffix');
	} else {
		$albumpath = "/index.php?album="; $imagepath = "&amp;image="; $modrewritesuffix = "";
	}
	$comments = getLatestComments($number,$type,$itemID);
	echo "<ul id=\"showlatestcomments\">\n";
	foreach ($comments as $comment) {
		if($comment['anon'] === "0") {
			$author = " ".gettext("by")." ".$comment['name'];
		} else {
			$author = "";
		}
		$album = $comment['folder'];
		if($comment['type'] != "albums" AND $comment['type'] != "news" AND $comment['type'] != "pages") { // check if not comments on albums or Zenpage items
			$imagetag = $imagepath.$comment['filename'].$modrewritesuffix;
		} else {
			$imagetag = "";
		}
		$date = $comment['date'];
		$albumtitle = get_language_string($comment['albumtitle']);
		$title = '';
		if($comment['type'] != 'albums') {
			if ($comment['title'] == "") $title = ''; else $title = get_language_string($comment['title']);
		}
		$website = $comment['website'];
		$shortcomment = truncate_string($comment['comment'], $shorten);
		if(!empty($title)) {
			$title = ": ".$title;
		}
		echo "<li><a href=\"".WEBPATH.$albumpath.$album.$imagetag."\" class=\"commentmeta\">".$albumtitle.$title.$author."</a><br />\n";
		echo "<span class=\"commentbody\">".$shortcomment."</span></li>";
	}
	echo "</ul>\n";
}

/**
 * returns the hitcounter for the current page or for the object passed
 *
 * @param object $obj the album or page object for which the hitcount is desired
 * @return string
 */
function getHitcounter($obj=NULL) {
	global $_zp_current_album, $_zp_current_image, $_zp_gallery_page, $_zp_current_zenpage_news, $_zp_current_zenpage_page, $_zp_current_category;
	if (is_null($obj)) {
		switch ($_zp_gallery_page) {
			case 'album.php':
				$obj = $_zp_current_album;
				break;
			case 'image.php':
				$obj = $_zp_current_image;
				break;
			case ZENPAGE_PAGES.'.php':
				$obj = $_zp_current_zenpage_page;
				break;
			case ZENPAGE_NEWS.'.php':
				if (in_context(ZP_ZENPAGE_NEWS_CATEGORY)) {
					$hc = query_single_row("SELECT hitcounter FROM ".prefix('zenpage_news_categories')." WHERE cat_link = '".$_zp_current_category."'");
					return $hc["hitcounter"];
				} else {
					$obj = $_zp_current_zenpage_news;
					if (is_null($obj)) return 0;
				}
				break;
			default:
				$page = stripSuffix($_zp_gallery_page);
				return getOption('Page-Hitcounter-'.$page);
				break;
		}
	}
	return $obj->get('hitcounter');
}

/**
 * Returns a where clause for filter password protected album.
 * Used by the random images functions.
 *
 * If the viewer is not logged in as ADMIN this function fails all password protected albums.
 * It does not check to see if the viewer has credentials for an album.
 *
 * @return string
 */

function getProtectedAlbumsWhere() {
	$result = query_single_row("SELECT MAX(LENGTH(folder) - LENGTH(REPLACE(folder, '/', ''))) AS max_depth FROM " . prefix('albums') );
	$max_depth = $result['max_depth'];

	$sql = "SELECT level0.id FROM " . prefix('albums') . " AS level0 ";
				$where = " WHERE (level0.password > ''";
	$i = 1;
	while ($i <= $max_depth) {
		$sql = $sql . " LEFT JOIN " . prefix('albums') . "AS level" . $i . " ON level" . $i . ".id = level" . ($i - 1) . ".parentid";
		$where = $where . " OR level" . $i . ".password > ''";
		$i++;
	}
	$sql = $sql . $where . " )";

	$result = query_full_array($sql);
	if ($result) {
		$albumWhere = prefix('albums') . ".id not in (";
		foreach ($result as $row) {
			$albumWhere = $albumWhere . $row['id'] . ", ";
		}
		$albumWhere = substr($albumWhere, 0, -2) . ')';
	} else {
		$albumWhere = '';
	}

	if (!empty($albumWhere)) $albumWhere = ' AND '.$albumWhere;
	return $albumWhere;
}

/**
 * Returns a randomly selected image from the gallery. (May be NULL if none exists)
 * @param bool $daily set to true and the picture changes only once a day.
 *
 * @return object
 */
function getRandomImages($daily = false) {
	global $_zp_gallery;
	if ($daily) {
		$potd = unserialize(getOption('picture_of_the_day'));
		if (date('Y-m-d', $potd['day']) == date('Y-m-d')) {
			$album = new Album($_zp_gallery, $potd['folder']);
			$image = newImage($album, $potd['filename']);
			if ($image->exists)	return $image;
		}
	}
	if (zp_loggedin()) {
		$albumWhere = '';
		$imageWhere = '';
	} else {
		$albumWhere = " AND " . prefix('albums') . ".show=1" . getProtectedAlbumsWhere() ;
		$imageWhere = " AND " . prefix('images') . ".show=1";
	}
	$c = 0;
	while ($c < 10) {
		$result = query_single_row('SELECT COUNT(*) AS row_count ' .
																' FROM '.prefix('images'). ', '.prefix('albums').
																' WHERE ' . prefix('albums') . '.folder!="" AND '.prefix('images').'.albumid = ' .
																prefix('albums') . '.id ' .    $albumWhere . $imageWhere );
		$rand_row = rand(0, $result['row_count']-1);

		$result = query_single_row('SELECT '.prefix('images').'.filename, '.prefix('albums').'.folder ' .
																' FROM '.prefix('images').', '.prefix('albums') .
																' WHERE '.prefix('images').'.albumid = '.prefix('albums').'.id  ' . $albumWhere .
																$imageWhere . ' LIMIT ' . $rand_row . ', 1');

		$imageName = $result['filename'];
		if (is_valid_image($imageName)) {
			$album = new Album($_zp_gallery, $result['folder']);
			$image = newImage($album, $imageName );
			if ($daily) {
				$potd = array('day' => time(), 'folder' => $result['folder'], 'filename' => $imageName);
				setOption('picture_of_the_day', serialize($potd));
			}
			return $image;
		}
		$c++;
	}
	return NULL;
}

/**
 * Returns  a randomly selected image from the album or its subalbums. (May be NULL if none exists)
 *
 * @param string $rootAlbum optional album folder from which to get the image.
 *
 * @return object
 */
function getRandomImagesAlbum($rootAlbum=null,$showunpublished=false) {
	global $_zp_current_album, $_zp_gallery, $_zp_current_search;
	if (empty($rootAlbum)) {
		$album = $_zp_current_album;
	} else {
		$album = new Album($_zp_gallery, $rootAlbum);
	}
	if ($album->isDynamic()) {
		$search = $album->getSearchEngine();
		$images = $search->getImages(0);
		$image = NULL;
		shuffle($images);
		while (count($images) > 0) {
			$randomImage = array_pop($images);
			if (is_valid_image($randomImage['filename'])) {
				$image = newImage(new Album(new Gallery(), $randomImage['folder']), $randomImage['filename']);
				return $image;
			}
		}
	} else {
		$albumfolder = $album->getFolder();
		if (isMyAlbum($albumfolder, LIST_ALBUM_RIGHTS) || $showunpublished) {
			$imageWhere = '';
			$albumNotWhere = '';
			$albumInWhere = '';
		} else {
			$imageWhere = " AND " . prefix('images'). ".show=1";
			$albumNotWhere = getProtectedAlbumsWhere();
			$albumInWhere = prefix('albums') . ".show=1";
		}

		$query = "SELECT id FROM " . prefix('albums') . " WHERE ";
		if ($albumInWhere) $query .= $albumInWhere.' AND ';
		$query .= "folder LIKE '" . zp_escape_string($albumfolder) . "%'";
		$result = query_full_array($query);
		if (is_array($result) && count($result) > 0) {
			$albumInWhere = prefix('albums') . ".id in (";
			foreach ($result as $row) {
				$albumInWhere = $albumInWhere . $row['id'] . ", ";
			}
			$albumInWhere =  ' AND '.substr($albumInWhere, 0, -2) . ')';
		} else {
			return NULL;
		}
		$c = 0;
		while ($c < 10) {
			$result = query_single_row('SELECT COUNT(*) AS row_count ' .
				' FROM '.prefix('images'). ', '.prefix('albums').
				' WHERE ' . prefix('albums') . '.folder!="" AND '.prefix('images').'.albumid = ' .
				prefix('albums') . '.id ' . $albumInWhere . $albumNotWhere . $imageWhere );
			$rand_row = rand(0, $result['row_count']-1);

			$result = query_single_row('SELECT '.prefix('images').'.filename, '.prefix('albums').'.folder ' .
				' FROM '.prefix('images').', '.prefix('albums') .
				' WHERE '.prefix('images').'.albumid = '.prefix('albums').'.id  ' . $albumInWhere .  $albumNotWhere .
				$imageWhere . ' LIMIT ' . $rand_row . ', 1');

			$imageName = $result['filename'];
			if (is_valid_image($imageName)) {
				$image = newImage(new Album(new Gallery(), $result['folder']), $imageName );
				return $image;
			}
			$c++;
		}

	}
	return null;
}

/**
 * Puts up random image thumbs from the gallery
 *
 * @param int $number how many images
 * @param string $class optional class
 * @param string $option what you want selected: all for all images, album for selected ones from an album
 * @param string $rootAlbum optional album from which to get the images
 * @param integer $width the width/cropwidth of the thumb if crop=true else $width is longest size.
 * @param integer $height the height/cropheight of the thumb if crop=true else not used
 * @param bool $crop 'true' (default) if the thumb should be cropped, 'false' if not
 */
function printRandomImages($number=5, $class=null, $option='all', $rootAlbum='',$width=100,$height=100,$crop=true) {
	if (!is_null($class)) {
		$class = ' class="' . $class . '"';
	}
	echo "<ul".$class.">";
	for ($i=1; $i<=$number; $i++) {
		echo "<li>\n";
		switch($option) {
			case "all":
				$randomImage = getRandomImages(); break;
			case "album":
				$randomImage = getRandomImagesAlbum($rootAlbum); break;
		}
		if (is_object($randomImage) && $randomImage->exists) {
			$randomImageURL = htmlspecialchars(getURL($randomImage));
			echo '<a href="' . $randomImageURL . '" title="'.sprintf(gettext('View image: %s'), html_encode($randomImage->getTitle())) . '">';
			if($crop) {
				echo "<img src=\"".htmlspecialchars($randomImage->getCustomImage(NULL, $width, $height, $width, $height, NULL, NULL, TRUE))."\" alt=\"" . html_encode($randomImage->getTitle()) . "\" />\n";
			} else {
				echo "<img src=\"".htmlspecialchars($randomImage->getCustomImage($width, NULL, NULL, NULL, NULL, NULL, NULL, TRUE))."\" alt=\"" . html_encode($randomImage->getTitle()) . "\" />\n";
			}
			echo "</a>";
		}
		echo "</li>\n";
	}
	echo "</ul>";
}

/**
 * Returns a list of tags for either an image or album, depends on the page called from
 *
 * @return string
 * @since 1.1
 */
function getTags() {
	if(in_context(ZP_IMAGE)) {
		global $_zp_current_image;
		return $_zp_current_image->getTags();
	} else if (in_context(ZP_ALBUM)) {
		global $_zp_current_album;
		return $_zp_current_album->getTags();
	} else if(in_context(ZP_ZENPAGE_PAGE)) {
		global $_zp_current_zenpage_page;
		return $_zp_current_zenpage_page->getTags();
	} else if(in_context(ZP_ZENPAGE_NEWS_ARTICLE)) {
		global $_zp_current_zenpage_news;
		return $_zp_current_zenpage_news->getTags();
	}
}

/**
 * Prints a list of tags, editable by admin
 *
 * @param string $option links by default, if anything else the
 *               tags will not link to all other photos with the same tag
 * @param string $preText text to go before the printed tags
 * @param string $class css class to apply to the div surrounding the UL list
 * @param string $separator what charactor shall separate the tags
 * @param bool   $editable true to allow admin to edit the tags
 * @param string $editclass CSS class applied to editable element if editable
 * @param mixed $messageIfEmpty Either bool or string. If false, echoes nothing when description is empty. If true, echoes default placeholder message if empty. If string, echoes string.
 * @since 1.1
 */
function printTags($option='links', $preText=NULL, $class='taglist', $separator=', ', $editable=TRUE, $editclass='', $messageIfEmpty = true ) {
	global $_zp_current_search;
	$singletag = getTags();
	$tagstring = implode(', ', $singletag);
	if ($tagstring === '' or $tagstring === NULL ) {
		$preText = '';
		if ( $messageIfEmpty === true && $editable && zp_loggedin() ) {
			$tagstring = gettext('(No tags...)');
		} elseif ( is_string($messageIfEmpty) ) {
			$tagstring = $messageIfEmpty;
		}
	}
	if(in_context(ZP_IMAGE)) {
		$object = "image";
	} else if (in_context(ZP_ALBUM)) {
		$object = "album";
	} else if(in_context(ZP_ZENPAGE_PAGE)) {
		$object = "zenpage_page";
	} else if(in_context(ZP_ZENPAGE_NEWS_ARTICLE)) {
		$object = "zenpage_news";
	}
	if ($editable && zp_loggedin()) {
		printEditable($object, '_update_tags', true, $editclass, $tagstring);
	} else {
		if (count($singletag) > 0) {
			if (!empty($preText)) {
				echo "<span class=\"tags_title\">".$preText."</span>";
			}
			echo "<ul class=\"".$class."\">\n";
			if (is_object($_zp_current_search)) {
				$albumlist = $_zp_current_search->album_list;
			} else {
				$albumlist = NULL;
			}
			$ct = count($singletag);
			$x = 0;
			if (getOption('search_space_is_or')) {
				$match = "/[ &|!'\"`,()]/";
			} else {
				$match = "/[&|!'\"`,()]/";
			}
			foreach ($singletag as $atag) {
				if (preg_match($match,$atag)) {
					if (strpos($atag,'"')===false) {
						$latag = '"'.$atag.'"';
					} else {
						$latag = "'".$atag."'";
					}
				} else {
					$latag = $atag;
				}
				if (++$x == $ct) { $separator = ""; }
				if ($option === "links") {
					$links1 = "<a href=\"".htmlspecialchars(getSearchURL($latag, '', 'tags', 0, 0, $albumlist),ENT_QUOTES)."\" title=\"".html_encode($atag)."\" rel=\"nofollow\">";
					$links2 = "</a>";
				}
				echo "\t<li>".$links1.$atag.$links2.$separator."</li>\n";
			}
			echo "</ul>";
		} else {
			echo "$tagstring";
		}
	}
}


/**
 * Either prints all of the galleries tgs as a UL list or a cloud
 *
 * @param string $option "cloud" for tag cloud, "list" for simple list
 * @param string $class CSS class
 * @param string $sort "results" for relevance list, "abc" for alphabetical, blank for unsorted
 * @param bool $counter TRUE if you want the tag count within brackets behind the tag
 * @param bool $links set to TRUE to have tag search links included with the tag.
 * @param int $maxfontsize largest font size the cloud should display
 * @param int $maxcount the floor count for setting the cloud font size to $maxfontsize
 * @param int $mincount the minimum count for a tag to appear in the output
 * @param int $limit set to limit the number of tags displayed to the top $numtags
 * @param int $minfontsize minimum font size the cloud should display
 * @since 1.1
 */
function printAllTagsAs($option,$class='',$sort='abc',$counter=FALSE,$links=TRUE,$maxfontsize=2,$maxcount=50,$mincount=10, $limit=NULL,$minfontsize=0.8) {
	global $_zp_current_search;

	$option = strtolower($option);
	if ($class != "") { $class = "class=\"".$class."\""; }
	$tagcount = getAllTagsCount();
	if (!is_array($tagcount)) { return false; }
	if ($sort == "results") {
			arsort($tagcount);
	}
	if (!is_null($limit)) {
		$tagcount = array_slice($tagcount, 0, $limit);
	}
	echo "<ul ".$class.">\n";
	foreach ($tagcount as $key=>$val) {
		if(!$counter) {
			$counter = "";
		} else {
			$counter = " (".$val.") ";
		}
		if ($option == "cloud") { // calculate font sizes, formula from wikipedia
			if ($val <= $mincount) {
				$size = $minfontsize;
			} else {
				$size = min(max(round(($maxfontsize*($val-$mincount))/($maxcount-$mincount), 2), $minfontsize), $maxfontsize);
			}
			$size = str_replace(',','.', $size);
			$size = " style=\"font-size:".$size."em;\"";
		} else {
			$size = '';
		}
		if ($val >= $mincount) {
			if(!$links) {
				echo "\t<li$size>".$key.$counter."</li>\n";
			} else {
				if (is_object($_zp_current_search)) {
					$albumlist = $_zp_current_search->album_list;
				} else {
					$albumlist = NULL;
				}
				if (getOption('search_space_is_or')) {
					$match = "/[ &|!'\"`,()]/";
				} else {
					$match = "/[&|!'\"`,()]/";
				}
				if (preg_match($match,$key)) {
					if (strpos($key,'"')===false) {
						$quote = '"';
					} else {
						$quote = "'";
					}
				} else {
					$quote = '';
				}
				echo "\t<li><a href=\"".
					htmlspecialchars(getSearchURL($quote.$key.$quote, '', 'tags', 0, 0, $albumlist),ENT_QUOTES)."\"$size rel=\"nofollow\">".
					$key.$counter."</a></li>\n";
			}
		}

	} // while end
	echo "</ul>\n";
}

/**
 * Retrieves a list of all unique years & months from the images in the gallery
 *
 * @param string $order set to 'desc' for the list to be in descending order
 * @return array
 */
function getAllDates($order='asc') {
	$alldates = array();
	$cleandates = array();
	$sql = "SELECT `date` FROM ". prefix('images');
	if (!zp_loggedin()) {
		$sql .= " WHERE `show` = 1";
	}
	$hidealbums = getNotViewableAlbums();
	if (!is_null($hidealbums)) {
		if (zp_loggedin()) {
			$sql .= ' WHERE ';
		} else {
			$sql .= ' AND ';
		}
		foreach ($hidealbums as $id) {
			$sql .= '`albumid`!='.$id.' AND ';
		}
		$sql = substr($sql, 0, -5);
	}
	$result = query_full_array($sql);
	foreach($result as $row){
		$alldates[] = $row['date'];
	}
	foreach ($alldates as $adate) {
		if (!empty($adate)) {
			$cleandates[] = substr($adate, 0, 7) . "-01";
		}
	}
	$datecount = array_count_values($cleandates);
	if ($order == 'desc') {
		krsort($datecount);
	} else {
		ksort($datecount);
	}
	return $datecount;
}
/**
 * Prints a compendum of dates and links to a search page that will show results of the date
 *
 * @param string $class optional class
 * @param string $yearid optional class for "year"
 * @param string $monthid optional class for "month"
 * @param string $order set to 'desc' for the list to be in descending order
 */
function printAllDates($class='archive', $yearid='year', $monthid='month', $order='asc') {
	global $_zp_current_search;
	if (!empty($class)){ $class = "class=\"$class\""; }
	if (!empty($yearid)){ $yearid = "class=\"$yearid\""; }
	if (!empty($monthid)){ $monthid = "class=\"$monthid\""; }
	$datecount = getAllDates($order);
	$lastyear = "";
	echo "\n<ul $class>\n";
	$nr = 0;
	while (list($key, $val) = each($datecount)) {
		$nr++;
		if ($key == '0000-00-01') {
			$year = "no date";
			$month = "";
		} else {
			$dt = strftime('%Y-%B', strtotime($key));
			$year = substr($dt, 0, 4);
			$month = substr($dt, 5);
		}

		if ($lastyear != $year) {
			$lastyear = $year;
			if($nr != 1) {  echo "</ul>\n</li>\n";}
			echo "<li $yearid>$year\n<ul $monthid>\n";
		}
		if (is_object($_zp_current_search)) {
			$albumlist = $_zp_current_search->album_list;
		} else {
			$albumlist = NULL;
		}
		echo "<li><a href=\"".htmlspecialchars(getSearchURl('', substr($key, 0, 7), '', 0, $albumlist),ENT_QUOTES)."\" rel=\"nofollow\">$month ($val)</a></li>\n";
	}
	echo "</ul>\n</li>\n</ul>\n";
}

/**
 * Produces the url to a custom page (e.g. one that is not album.php, image.php, or index.php)
 *
 * @param string $linktext Text for the URL
 * @param string $page page name to include in URL
 * @param string $q query string to add to url
 * @param string $album optional album for the page
 * @return string
 */
function getCustomPageURL($page, $q='', $album='') {
	global $_zp_current_album;
	$result = '';
	if (getOption('mod_rewrite')) {
		if (!empty($album)) {
			$album = '/'.urlencode($album);
		}
		$result .= WEBPATH.$album."/page/$page";
		if (!empty($q)) { $result .= "?$q"; }
	} else {
		if (!empty($album)) {
			$album = "&album=$album";
		}
		$result .= WEBPATH."/index.php?p=$page".$album;
		if (!empty($q)) { $result .= "&$q"; }
	}
	return $result;
}

/**
 * Prints the url to a custom page (e.g. one that is not album.php, image.php, or index.php)
 *
 * @param string $linktext Text for the URL
 * @param string $page page name to include in URL
 * @param string $q query string to add to url
 * @param string $prev text to insert before the URL
 * @param string $next text to follow the URL
 * @param string $class optional class
 */
function printCustomPageURL($linktext, $page, $q='', $prev='', $next='', $class=NULL) {
	if (!is_null($class)) {
		$class = 'class="' . $class . '"';
	}
	echo $prev."<a href=\"".htmlspecialchars(getCustomPageURL($page, $q))."\" $class title=\"".html_encode($linktext)."\">".htmlspecialchars($linktext,ENT_QUOTES)."</a>".$next;
}

/**
 * Returns the URL to an image (This is NOT the URL for the image.php page)
 *
 * @param object $image the image
 * @return string
 */
function getURL($image) {
	if (getOption('mod_rewrite')) {
		return WEBPATH . "/" . pathurlencode($image->getAlbumName()) . "/" . urlencode($image->filename);
	} else {
		return WEBPATH . "/index.php?album=" . pathurlencode($image->getAlbumName()) . "&image=" . urlencode($image->filename);
	}
}
/**
 * Returns the record number of the album in the database
 * @return int
 */
function getAlbumId() {
	global $_zp_current_album;
	if (is_null($_zp_current_album)) { return null; }
	return $_zp_current_album->getAlbumId();
}

/**
 * Prints a RSS link
 *
 * @param string $option type of RSS: "Gallery" feed for latest images of the whole gallery
 * 																		"Album" for latest images only of the album it is called from
 * 																		"Collection" for latest images of the album it is called from and all of its subalbums
 * 																		"Comments" for all comments of all albums and images
 * 																		"Comments-image" for latest comments of only the image it is called from
 * 																		"Comments-album" for latest comments of only the album it is called from
 * 																		"AlbumsRSS" for latest albums
 * 																		"AlbumsRSScollection" only for latest subalbums with the album it is called from
 * @param string $prev text to before before the link
 * @param string $linktext title of the link
 * @param string $next text to appear after the link
 * @param bool $printIcon print an RSS icon beside it? if true, the icon is zp-core/images/rss.gif
 * @param string $class css class
 * @param string $lang optional to display a feed link for a specific language. Enter the locale like "de_DE" (the locale must be installed on your Zenphoto to work of course). If empty the locale set in the admin option or the language selector (getOption('locale') is used.
 * @since 1.1
 */
function printRSSLink($option, $prev, $linktext, $next, $printIcon=true, $class=null,$lang='') {
	global $_zp_current_album;
	if ($printIcon) {
		$icon = ' <img src="' . FULLWEBPATH . '/' . ZENFOLDER . '/images/rss.gif" alt="RSS Feed" />';
	} else {
		$icon = '';
	}
	if (!is_null($class)) {
		$class = 'class="' . $class . '"';
	}
	if(empty($lang)) {
		$lang = getOption("locale");
	}
	switch($option) {
		case "Gallery":
			if (getOption('RSS_album_image')) {
				echo $prev."<a $class href=\"".WEBPATH."/rss.php?lang=".$lang."\" title=\"".gettext("Latest images RSS")."\" rel=\"nofollow\">".$linktext."$icon</a>".$next;
			}
			break;
		case "Album":
			if (getOption('RSS_album_image')) {
				echo $prev."<a $class href=\"".WEBPATH."/rss.php?albumtitle=".urlencode(getAlbumTitle())."&amp;albumname=".urlencode($_zp_current_album->getFolder())."&amp;lang=".$lang."\" title=\"".gettext("Latest images of this album RSS")."\" rel=\"nofollow\">".$linktext."$icon</a>".$next;
				break;
			}
		case "Collection":
			if (getOption('RSS_album_image')) {
				echo $prev."<a $class href=\"".WEBPATH."/rss.php?albumtitle=".urlencode(getAlbumTitle())."&amp;folder=".urlencode($_zp_current_album->getFolder())."&amp;lang=".$lang."\" title=\"".gettext("Latest images of this album and its subalbums RSS")."\" rel=\"nofollow\">".$linktext."$icon</a>".$next;
			}
			break;
		case "Comments":
			if (getOption('RSS_comments')) {
				echo $prev."<a $class href=\"".WEBPATH."/rss-comments.php?type=gallery&amp;lang=".$lang."\" title=\"".gettext("Latest comments RSS")."\" rel=\"nofollow\">".$linktext."$icon</a>".$next;
			}
			break;
		case "Comments-image":
			if (getOption('RSS_comments')) {
				echo $prev."<a $class href=\"".WEBPATH."/rss-comments.php?id=".getImageID()."&amp;title=".urlencode(getImageTitle())."&amp;type=image&amp;lang=".$lang."\" title=\"".gettext("Latest comments RSS for this image")."\" rel=\"nofollow\">".$linktext."$icon</a>".$next;
			}
			break;
		case "Comments-album":
			if (getOption('RSS_comments')) {
				echo $prev."<a $class href=\"".WEBPATH."/rss-comments.php?id=".getAlbumID()."&amp;title=".urlencode(getAlbumTitle())."&amp;type=album&amp;lang=".$lang."\" title=\"".gettext("Latest comments RSS for this album")."\" rel=\"nofollow\">".$linktext."$icon</a>".$next;
			}
			break;
		case "AlbumsRSS":
			if (getOption('RSS_album_image')) {
				echo $prev."<a $class href=\"".WEBPATH."/rss.php?lang=".$lang."&amp;albumsmode\" title=\"".gettext("Latest albums RSS")."\" rel=\"nofollow\">".$linktext."$icon</a>".$next;
			}
			break;
		case "AlbumsRSScollection":
			if (getOption('RSS_album_image')) {
				echo $prev."<a $class href=\"".WEBPATH."/rss.php?folder=".urlencode($_zp_current_album->getFolder())."&amp;lang=".$lang."&amp;albumsmode\" title=\"".gettext("Latest albums RSS")."\" rel=\"nofollow\">".$linktext."$icon</a>".$next;
			}
			break;
	}
}

/**
 * Returns the RSS link for use in the HTML HEAD
 *
 * @param string $option type of RSS: "Gallery" feed for the whole gallery
 * 																		"Album" for only the album it is called from
 * 																		"Collection" for the album it is called from and all of its subalbums
 * 																		 "Comments" for all comments
 * 																		"Comments-image" for comments of only the image it is called from
 * 																		"Comments-album" for comments of only the album it is called from
 * @param string $linktext title of the link
 * @param string $lang optional to display a feed link for a specific language. Enter the locale like "de_DE" (the locale must be installed on your Zenphoto to work of course). If empty the locale set in the admin option or the language selector (getOption('locale') is used.
 *
 *
 * @return string
 * @since 1.1
 */
function getRSSHeaderLink($option, $linktext='', $lang='') {
	global $_zp_current_album;
	$host = htmlentities($_SERVER["HTTP_HOST"], ENT_QUOTES, 'UTF-8');
	(secureServer()) ? $serverprotocol = "https://" : $serverprotocol = "http://";
	if(empty($lang)) {
		$lang = getOption("locale");
	}
	switch($option) {
		case "Gallery":
			if (getOption('RSS_album_image')) {
				return "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"".html_encode($linktext)."\" href=\"".$serverprotocol.$host.WEBPATH."/rss.php?lang=".$lang."\" />\n";
			}
		case "Album":
			if (getOption('RSS_album_image')) {
				return "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"".html_encode($linktext)."\" href=\"".$serverprotocol.$host.WEBPATH."/rss.php?albumtitle=".urlencode(getAlbumTitle())."&amp;albumname=".urlencode($_zp_current_album->getFolder())."&amp;lang=".$lang."\" />\n";
			}
		case "Collection":
			if (getOption('RSS_album_image')) {
				return "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"".html_encode($linktext)."\" href=\"".$serverprotocol.$host.WEBPATH."/rss.php?albumtitle=".urlencode(getAlbumTitle())."&amp;folder=".urlencode($_zp_current_album->getFolder())."&amp;lang=".$lang."\" />\n";
			}
		case "Comments":
			if (getOption('RSS_comments')) {
				return "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"".html_encode($linktext)."\" href=\"".$serverprotocol.$host.WEBPATH."/rss-comments.php?lang=".$lang."\" />\n";
			}
		case "Comments-image":
			if (getOption('RSS_comments')) {
				return "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"".html_encode($linktext)."\" href=\"".$serverprotocol.$host.WEBPATH."/rss-comments.php?id=".getImageID()."&amp;title=".urlencode(getImageTitle())."&amp;type=image&amp;lang=".$lang."\" />\n";
			}
		case "Comments-album":
			if (getOption('RSS_comments')) {
				return "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"".html_encode($linktext)."\" href=\"".$serverprotocol.$host.WEBPATH."/rss-comments.php?id=".getAlbumID()."&amp;title=".urlencode(getAlbumTitle())."&amp;type=album&amp;lang=".$lang."\" />\n";
			}
		case "AlbumsRSS":
			if (getOption('RSS_album_image')) {
				return "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"".html_encode($linktext)."\" href=\"".$serverprotocol.$host.WEBPATH."/rss.php?lang=".$lang."&amp;albumsmode\" />\n";
			}

	}
}

/**
 * Prints the RSS link for use in the HTML HEAD
 *
 * @param string $option type of RSS (Gallery, Album, Collection, Comments)
 * @param string $linktext title of the link
 * @param string $lang optional to display a feed link for a specific language. Enter the locale like "de_DE" (the locale must be installed on your Zenphoto to work of course). If empty the locale set in the admin option or the language selector (getOption('locale') is used.
 *
 * @since 1.1.6
 */
function printRSSHeaderLink($option, $linktext, $lang='') {
	echo getRSSHeaderLink($option, $linktext);
}


//*** Search functions *******************************************************
//****************************************************************************

/**
 * tests if a search page is an "archive" page
 *
 * @return bool
 */
function isArchive() {
	return isset($_REQUEST['date']);
}

/**
 * Returns a search URL
 *
 * @param string $words the search words target
 * @param string $dates the dates that limit the search
 * @param int $fields the fields on which to search
 * @param int $page the page number for the URL
 * @param array $inalbums the list of albums to search
 * @return string
 * @since 1.1.3
 */
function getSearchURL($words, $dates, $fields, $page, $inalbums='') {
	if ($mr = getOption('mod_rewrite')) {
		$url = WEBPATH."/page/search/";
	} else {
		$url = WEBPATH."/index.php?p=search";
	}
	if (!empty($fields) && empty($dates)) {
		if (!is_array($fields)) $fields = explode(',',$fields);
		if ($mr && count($fields)==1 && $fields[0]=='tags') {
				$url .= "tags/";
				$urls = '';
		} else {
			$search = new SearchEngine();
			$urls = $search->getSearchFieldsText($fields, 'searchfields=');
		}
	}

	if (!empty($words)) {
		if($mr) {
			$url .= urlencode($words);
		} else {
			$url .= "&words=".urlencode($words);
		}
	}
	if (!empty($dates)) {
		if($mr) {
			$url .= "archive/$dates";
		} else {
			$url .= "&date=$dates";
		}
	}
	if ($page > 1) {
		if ($mr) {
			$url .= "/$page";
		} else {
			$url .= "&page=$page";
		}
	}
	if (!empty($urls)) {
		if ($mr) {
			$url .= '?'.$urls;
		} else {
			$url .= '&'.$urls;
		}

	}
	if (!empty($inalbums)) {
		if ($mr&empty($urls)) {
			$url .= '?';
		} else {
			$url .= '&';
		}
		$url .= 'inalbums='.urlencode(implode(',',$inalbums));
	}
	return $url;
}

/**
 * Prints the search form
 *
 * Search works on a list of tokens entered into the search form.
 *
 * Tokens may be part of boolean expressions using &, |, !, and parens. (Comma is retained as a synonom of | for
 * backwords compatibility.)
 *
 * Tokens may be enclosed in quotation marks to create exact pattern matches or to include the boolean operators and
 * parens as part of the tag..
 *
 * @param string $prevtext text to go before the search form
 * @param string $id css id for the search form, default is 'search'
 * @param string $buttonSource optional path to the image for the button
 * @param string $buttontext optional text for the button ("Search" will be the default text)
 * @param string $iconsource optional theme based icon for the search fields toggle
 * @param array $query_fields override selection for enabled fields with this list
 * @param array $album_list optional array of albums to search
 * @since 1.1.3
 */
function printSearchForm($prevtext=NULL, $id='search', $buttonSource=NULL, $buttontext='', $iconsource=NULL, $query_fields=NULL, $album_list=NULL) {
	global $_zp_adminJS_loaded;
	if(empty($buttontext)) {
		$buttontext = gettext("Search");
	} else {
		$buttontext = sanitize($buttontext);
	}
	$zf = WEBPATH."/".ZENFOLDER;
	$dataid = $id . '_data';
	$searchwords = (isset($_POST['words']) ? html_encode(sanitize($_REQUEST['words'],0),false) : '');

	if (empty($buttonSource)) {
		$type = 'submit';
		$buttontext = 'value="'.$buttontext.'"';
	} else {
		$buttonSource = 'src="' . $buttonSource . '" alt="'.$buttontext.'"';
		$buttontext = 'title="'.$buttontext.'"';
		$type = 'image';
	}
	if (empty($iconsource)) {
		$iconsource = WEBPATH.'/'.ZENFOLDER.'/images/searchfields_icon.png';
	}
	if (getOption('mod_rewrite')) { $searchurl = '/page/search/'; } else { $searchurl = "/index.php?p=search"; }
	$engine = new SearchEngine();
	$fields = $engine->allowedSearchFields();
	if (!$_zp_adminJS_loaded) {
		$_zp_adminJS_loaded = true;
		?>
		<script type="text/javascript" src="<?php echo WEBPATH.'/'.ZENFOLDER; ?>/js/admin.js"></script>
		<?php
	}
	?>
	<div id="search"><!-- search form -->

		<form method="post" action="<?php echo WEBPATH.$searchurl; ?>" id="search_form">
			<?php echo $prevtext; ?>
			<input type="text" name="words" value="<?php  echo $searchwords; ?>" id="search_input" size="10" />
			<?php if(count($fields) > 1) { ?>
				<a href="javascript:toggle('searchextrashow');"><img src="<?php echo $iconsource; ?>" alt="<?php echo gettext('select search fields'); ?>" id="searchfields_icon" /></a>
			<?php } ?>
			<input type="<?php echo $type; ?>" <?php echo $buttontext; ?> class="pushbutton" id="search_submit" <?php echo $buttonSource; ?> />
			<input type="hidden" name="inalbums" value="<?php if (empty($album_list)) echo ''; else echo implode(',', $album_list); ?>" />
			<br />
			<?php
			if (count($fields) > 1) {
				natcasesort($fields);
				$fields = array_flip($fields);
				if (is_null($query_fields)) {
					$query_fields = $engine->parseQueryFields();
				} else{
					if (!is_array($query_fields)) {
						$query_fields = $engine->numericFields($query_fields);
					}
				}
				if (count($query_fields)==0) {
					$query_fields = array_flip($engine->allowedSearchFields());
				}

				?>
				<ul style="display:none;" id="searchextrashow">
				<?php
				foreach ($fields as $display=>$key) {
					echo '<li><label><input id="SEARCH_'.$key.'" name="SEARCH_'.$key.'" type="checkbox"';
					if (in_array($key,$query_fields)) {
						echo ' checked="checked" ';
					}
					echo ' value="'.$key.'"  /> ' . $display . "</label></li>"."\n";
				}
				?>
				</ul>
				<?php
			}
			?>
		</form>
	</div><!-- end of search form -->
	<?php
}

/**
 * Returns the a sanitized version of the search string
 *
 * @return string
 * @since 1.1
 */
function getSearchWords() {
	global $_zp_current_search;
	if (!in_context(ZP_SEARCH)) return '';
	return $_zp_current_search->codifySearchString('&quot;');
}

/**
 * Returns the date of the search
 *
 * @param string $format formatting of the date, default 'F Y'
 * @return string
 * @since 1.1
 */
function getSearchDate($format='%B %Y') {
	if (in_context(ZP_SEARCH)) {
		global $_zp_current_search;
		$date = $_zp_current_search->getSearchDate();
		if (empty($date)) { return ""; }
		if ($date == '0000-00') { return gettext("no date"); };
		$dt = strtotime($date."-01");
		return zpFormattedDate($format, $dt);
	}
	return false;
}

define("IMAGE", 1);
define("ALBUM", 2);
/**
 * Checks to see if comment posting is allowed for an image/album

 * Returns true if comment posting should be allowed
 *
 * @param int $what the degree of control desired allowed values: ALBUM, IMAGE, and ALBUM+IMAGE
 * @return bool
 */
function openedForComments($what=3) {
	global $_zp_current_image, $_zp_current_album;
	$result = true;
	if (IMAGE & $what) { $result = $result && $_zp_current_image->getCommentsAllowed(); }
	if (ALBUM & $what) { $result = $result && $_zp_current_album->getCommentsAllowed(); }
	return $result;
}

/**
 * Finds the name of the themeColor option selected on the admin options tab

 * Returns a path and name of the theme css file. Returns the value passed for defaultcolor if the
 * theme css option file does not exist.
 *
 * @param string &$zenCSS path to the css file
 * @param string &$themeColor name of the css file
 * @param string $defaultColor name of the default css file
 * @return string
 * @since 1.1
 */
function getTheme(&$zenCSS, &$themeColor, $defaultColor) {
	global $_zp_themeroot;
	$themeColor = getOption('Theme_colors');
	$zenCSS = $_zp_themeroot . '/styles/' . $themeColor . '.css';
	$unzenCSS = str_replace(WEBPATH, '', $zenCSS);
	if (!file_exists(SERVERPATH . internalToFilesystem($unzenCSS))) {
		$zenCSS = $_zp_themeroot. "/styles/" . $defaultColor . ".css";
		return ($themeColor == '');
	} else {
		return true;
	}
}

/**
 * Passed # of album columns, # of image columns of the theme.
 *
 * NOTE: The values for these numbers of columns are determined by the theme
 * CSS. They should be set to how many images or albums are displayed in a row.
 * If you get this wrong, your theme will not behave as you expect.
 *
 * Updates (non-persistent) images_per_page and albums_per_page so that the rows are filled.
 *
 * This means that the value you set for the images per page and albums per page options may
 * not be the same as what actually gets shown. First, they will be rounded to be an even multiple
 * rows. So, if you have 6 columns of album thumbs your albums per page shown will be a multiple of
 * six (assuming that there are enough albums.) Second, there may be a page where both image and
 * album thumbs are shown--the "transition" page. Fewer than images_per_page will appear
 * on this page.
 *
 * The "non-persistent" update means that the actual values for these options are not changed. Just
 * the values that will be used for the display of the particular page.
 *
 * Returns # of images that will go on the album/image transition page.
 *
 * When you have albums containing both subalbums and images there may be a page where the album
 * thumbnails do not fill the page. This function returns a count of the images that can be used to
 * fill out this transition page. The return value should be passed as the second parameter to
 * next_image() so that the page is filled out with the proper number of images. If you do not
 * pass this parameter it is assumed that album thumbs and image thumbs are not to be placed on
 * the same (transition) page. (If you do not wish to have an album/image transition page you need
 * not use this function at all.)
 *
 * This function (combined with the parameter to next_image) impacts the pagination computations
 * zenphoto makes. For this reason, it is important to make identical calls to the function from
 * your theme's index.php, albums.php and image.php pages. Otherwise page and breadcrumb navigation
 * may not be correct.
 *
 * @param int $albumColumns number of album columns on the page
 * @param int $imageColumns number of image columns on the page
 * @return int
 * @since 1.1
 */
function normalizeColumns($albumColumns, $imageColumns) {
	global $_zp_current_album, $firstPageImages;
	$albcount = max(1, getOption('albums_per_page'));
	if (($albcount % $albumColumns) != 0) {
		setOption('albums_per_page', $albcount = ((floor($albcount / $albumColumns) + 1) * $albumColumns), false);
	}
	$imgcount = max(1, getOption('images_per_page'));
	if (($imgcount % $imageColumns) != 0) {
		setOption('images_per_page', $imgcount = ((floor($imgcount / $imageColumns) + 1) * $imageColumns), false);
	}
	if (in_context(ZP_ALBUM | ZP_SEARCH)) {
		if (in_context(ZP_SEARCH)) {
			$count = getNumAlbums();
		} else {

			$count = GetNumAlbums();

		}
		if ($count == 0) {
			return 0;
		}
		$rowssused = ceil(($count % $albcount) / $albumColumns);     /* number of album rows unused */
		$leftover = floor(max(1, getOption('images_per_page')) / $imageColumns) - $rowssused;
		$_zp_conf_vars['images_first_page'] = $firstPageImages = max(0, $leftover * $imageColumns);  /* number of images that fill the leftover rows */
		if ($firstPageImages == $imgcount) {
			return 0;
		} else {
			return $firstPageImages;
		}
	}
	return false;
}

//************************************************************************************************
// album password handling
//************************************************************************************************

/**
 * Checks if the album is password protected
 * @param object $album
 */
function isProtectedAlbum($album=NULL) {
	global $_zp_current_album;
	if (is_null($album)) $album = $_zp_current_album;
	$hash = $album->getPassword();
	if (empty($hash)) {
		$album = $album->getParent();
		while (!is_null($album) && empty($hash)) {
			$hash = $album->getPassword();
			$album = $album->getParent();
		}
	}
	return $hash;
}

/**
 * returns the auth type of a guest login
 *
 * @param string $hint
 * @param string $show
 * @return string
 */
function checkForGuest(&$hint, &$show) {
	global $_zp_gallery, $_zp_current_zenpage_page, $_zp_current_category, $_zp_current_zenpage_news;
	$authType = zp_apply_filter('checkForGuest', NULL);
	if (!is_null($authType)) return $authType;
	if (in_context(ZP_SEARCH)) {  // search page
		$hash = getOption('search_password');
		$show = (getOption('search_user') != '');
		$hint = get_language_string(getOption('search_hint'));
		$authType = 'zp_search_auth';
		if (empty($hash)) {
			$hash = $_zp_gallery->getPassword();
			$show = $_zp_gallery->getUser() != '';
			$hint = $_zp_gallery->getPasswordHint();
			$authType = 'zp_gallery_auth';
		}
		if (!empty($hash) && zp_getCookie($authType) == $hash) {
			return $authType;
		}
	} else if (!is_null($_zp_current_zenpage_news)) {
		if (!checkNewsAccess($_zp_current_zenpage_news, $hint, $show)) {
			return false;
		}
		$authType = checkNewsCategoryPassword($_zp_current_category, $hint, $show);
		return $authType;
	} else if (isset($_GET['album'])) {  // album page
		list($album, $image) = rewrite_get_album_image('album','image');
		if ($authType = checkAlbumPassword($album, $hint)) {
			return $authType;
		} else {
			$alb = new Album($_zp_gallery, $album);
			$show = $alb->getUser() != '';
			return false;
		}
	} else {  // other page
		$hash = $_zp_gallery->getPassword();
		$show = $_zp_gallery->getUser() != '';
		$hint = $_zp_gallery->getPasswordHint();
		if (!empty($hash) && zp_getCookie('zp_gallery_auth') == $hash) {
			return 'zp_gallery_auth';
		}
	}
	if (empty($hash)) return 'zp_unprotected';
	return false;
}

/**
 * Checks to see if a password is needed
 * displays a password form if log-on is required
 *
 * Returns true if a login form has been displayed
 *
 * The password protection is hereditary. This normally only impacts direct url access to an album or image since if
 * you are going down the tree you will be stopped at the first place a password is required.
 *
 * If the gallery is password protected then every album & image will require that password.
 *
 * If an album is password protected then all subalbums and images treed below that album will require
 * the password. If there are multiple passwords in the tree and you direct link, the password that is
 * required will be that of the nearest parent that has a password. (The gallery is the ur-parrent to all
 * albums.)
 *
 * @param string $hint the password hint
 * @param bool $show whether there is a user associated with the password.
 * @return bool
 * @since 1.1.3
 */
function checkforPassword(&$hint, &$show) {
	global $_zp_current_album, $_zp_current_search, $_zp_gallery, $_zp_gallery_page,
				$_zp_current_zenpage_page, $_zp_current_zenpage_news;
	if (getOption('gallery_page_unprotected_'.stripSuffix($_zp_gallery_page))) return false;
	if (zp_loggedin()) {
		$fail = zp_apply_filter('isMyItem', false);
		if (!$fail) return false;
		switch ($_zp_gallery_page) {
			case 'album.php':
			case 'image.php':
				if (isMyAlbum($_zp_current_album->name, LIST_ALBUM_RIGHTS)) return false;
				break;
			default:
				return false;
		}
	}
	$authType = checkForGuest($hint, $show);
	if ($authType) return false;	// a guest is logged in
	return true;
}

/**
 * Returns a redirection link for the password form
 *
 * @return string
 */
function getPageRedirect() {
	global $_zp_login_error, $_zp_password_form_printed, $_zp_current_search, $_zp_gallery_page,
					$_zp_current_album, $_zp_current_image;
	switch($_zp_gallery_page) {
		case 'index.php':
			$action = '/index.php';
			break;
		case 'album.php':
			$action = '/index.php?userlog=1&album='.urlencode($_zp_current_album->name);
			break;
		case 'image.php':
			$action = '/index.php?userlog=1&album='.urlencode($_zp_current_album->name).'&image='.urlencode($_zp_current_image->filename);
			break;
		case ZENPAGE_PAGES.'.php':
			$action = '/index.php?userlog=1&p='.ZENPAGE_PAGES.'&title='.urlencode(getPageTitlelink());
			break;
		case ZENPAGE_NEWS.'.php':
			$action = '/index.php?userlog=1&p='.ZENPAGE_NEWS;
			$title = getNewsTitlelink();
			if (!empty($title)) $action .= '&title='.urlencode(getNewsTitlelink());
			break;
		default:
		if (in_context(ZP_SEARCH)) {
			$action = '/index.php?userlog=1&p=search' . $_zp_current_search->getSearchParams();
		} else {
			$action = '/index.php?userlog=1&p='.substr($_zp_gallery_page, 0, -4);
		}
	}
	return WEBPATH.$action;
}
/**
 * Prints the album password form
 *
 * @param string $hint hint to the password
 * @param bool $showProtected set false to supress the password protected message
 * @param bool $showuser set true to force the user name filed to be present
 * @param string $redirect optional URL to send the user to after successful login
 *
 *@since 1.1.3
 */
function printPasswordForm($_password_hint, $_password_showProtected=true, $_password_showuser=NULL, $_password_redirect=NULL) {
	global $_zp_login_error, $_zp_password_form_printed, $_zp_current_search, $_zp_gallery_page,
					$_zp_current_album, $_zp_current_image, $theme, $_zp_current_zenpage_page;
	if ($_zp_password_form_printed) return;
	if (is_null($_password_showuser)) $_password_showuser = getOption('login_user_field');
	if (is_null($_password_redirect)) $_password_redirect = getPageRedirect();
	$_zp_password_form_printed = true;
	if ($_zp_login_error) {
		?>
		<div class="errorbox" id="message">
			<h2><?php echo gettext("There was an error logging in."); ?></h2>
			<br />
			<?php echo gettext("Check your user and password and try again."); ?>
		</div>
		<?php
	}
	if ($_password_showProtected && !$_zp_login_error) {
		?>
		<p>
			<?php echo gettext("The page you are trying to view is password protected."); ?>
		</p>
		<?php
	}
	$passwordform = SERVERPATH.'/'.THEMEFOLDER.'/'.$theme.'/password_form.php';
	if (file_exists($passwordform)) {
		include($passwordform);
	} else {
		include (SERVERPATH.'/'.ZENFOLDER.'/password_form.php');
	}

}

/**
 * Shell for calling the installed captcha generator.
 * Returns a captcha string and captcha image URI
 *
 * @param string $img the captcha image URI
 * @return string
 */
function generateCaptcha(&$img) {
	global $_zp_captcha;
	return $_zp_captcha->generateCaptcha($img);
}

/**
 * Simple captcha for comments.
 *
 * Prints a captcha entry field for a form such as the comments form.
 * @param string $preText lead-in text
 * @param string $midText text that goes between the captcha image and the input field
 * @param string $postText text that closes the captcha
 * @param int $size the text-width of the input field
 * @since 1.1.4
 **/
function printCaptcha($preText='', $midText='', $postText='', $size=4) {
	global $_zp_captcha;
	if (getOption('Use_Captcha')) {
		$captchaCode = $_zp_captcha->generateCaptcha($img);
		$inputBox =  "<input type=\"text\" id=\"code\" name=\"code\" size=\"" . $size . "\" class=\"captchainputbox\" />";
		$captcha = "<input type=\"hidden\" name=\"code_h\" value=\"" . $captchaCode . "\" />" .
						"<label for=\"code\"><img src=\"" . $img . "\" alt=\"Code\" style=\"vertical-align:bottom\"/></label>&nbsp;";

		echo $preText;
		echo $captcha;
		echo $midText;
		echo $inputBox;
		echo $postText;
	}
}

/**
 * prints the zenphoto logo and link
 *
 */
function printZenphotoLink() {
	echo gettext("Powered by <a href=\"http://www.zenphoto.org\" title=\"A simpler web photo album\"><span id=\"zen-part\">zen</span><span id=\"photo-part\">PHOTO</span></a>");
}

/**
 * Returns truncated html formatted content
 *
 * @param string $articlecontent the source string
 * @param int $shorten new size
 * @param string $shortenindicator
 * @return string
 */
function shortenContent($articlecontent, $shorten, $shortenindicator) {
	$short = substr($articlecontent, 0, $shorten);
	$short2 = sanitize($short.'</p>');
	if (strlen($articlecontent) > $shorten && ($l2 = strlen($short2)) < $shorten)	{
		$c = 0;
		$l1 = $shorten;
		$delta = $shorten-$l2;
		while ($l2 < $shorten && $c++ < 5) {
			$open = strrpos($short, '<');
			if ($open > strrpos($short, '>')) {
				$l1 = strpos($articlecontent,'>',$l1+1)+$delta;
			} else {
				$l1 = $l1 + $delta;
			}
			$short = substr($articlecontent, 0, $l1);
			$short2 = sanitize($short.'</p>');
			$l2 = strlen($short2);
		}
		$shorten = $l1;
	}
	$short = truncate_string($articlecontent, $shorten, '');
	// drop open tag strings
	$open = strrpos($short, '<');
	if ($open > strrpos($short, '>')) {
		$short = substr($short, 0, $open);
	}
	// drop unbalanced tags
	$short = sanitize($short.'</p>', 1);
	// insert the elipsis
	$i = strrpos($short, '</p>');
	if ($i == strlen($short) - 4) {
		$short = substr($short, 0, -4).' '.$shortenindicator.'</p>';
	} else {
		$short .= ' '.$shortenindicator;
	}
	return $short;
}

/**
 * Expose some informations in a HTML comment
 *
 * @param string $obj the path to the page being loaded
 * @param array $plugins list of activated plugins
 * @param string $theme The theme being used
 * @param string $filters list of the activated filters
 */
function exposeZenPhotoInformations( $obj = '', $plugins = '', $theme = '', $filters = '' ) {
	global $zenpage_version, $_zp_filters;

	$a = basename($obj);
	if ($a != 'full-image.php') {
		if (defined('RELEASE')) {
			$official = 'Official Build';
		} else {
			$official = 'SVN';
		}
		echo "\n<!-- zenphoto version " . ZENPHOTO_VERSION . " [" . ZENPHOTO_RELEASE . "] ($official)";
		echo " THEME: " . $theme . " (" . $a . ") { memory: ".INI_GET('memory_limit')." }";
		echo ' PLUGINS: ';
		if (count($plugins) > 0) {
			sort($plugins);
			foreach ($plugins as $plugin) {
				echo $plugin.' ';
			}
		} else {
			echo 'none ';
		}
		echo 'FILTERS: ';
		if (count($filters) > 0) {
			ksort($filters);
			foreach ($filters as $filter=>$array_of_priority) {
				foreach ($array_of_priority as $priority=>$array_of_filters) {
					echo "$filter [";
					foreach ($array_of_filters as $data) {
						echo "$priority: ".$data['script'].'=>'.$data['function'];
					}
					echo '] ';
				}
			}
		} else {
			echo 'none ';
		}
		echo " -->";
	}
}

/**
 * Gets the content of a codeblock for an image, album or Zenpage newsarticle or page.
 * Additionally you can print codeblocks of a published or un-published specific Zenpage page (not news artcle!) by request directly.
 *
 * Note: Echoing this array's content does not execute it. Also no special chars will be escaped.
 * Use printCodeblock() if you need to execute script code.
 *
 * Note: Meant for script code this field is not multilingual.
 *
 * @param int $number The codeblock you want to get
 * @param string $titlelink The titlelink of a specific page you want to get the codeblock of (only for Zenpage pages!)
 *
 * @return string
 */
function getCodeblock($number=0,$titlelink='') {
	global $_zp_current_album, $_zp_current_image, $_zp_current_zenpage_news, $_zp_current_zenpage_page;
	$getcodeblock = '';
	if (empty($titlelink)) {
		if (in_context(ZP_ALBUM)) {
			$getcodeblock = $_zp_current_album->getCodeblock();
		}
		if (in_context(ZP_IMAGE)) {
			$getcodeblock = $_zp_current_image->getCodeblock();
		}
		if(getOption('zp_plugin_zenpage')) {
			if(is_News()) {
				//if(get_class($_zp_current_zenpage_news) == "ZenpageNews") { // formerly to prevent calling on non news CombiNews items
				$hint = $show = '';
				if (checkNewsAccess($_zp_current_zenpage_news, $hint, $show)) {
					$getcodeblock = $_zp_current_zenpage_news->getCodeblock();
				} else {
					$getcodeblock = '';
				}
				//}
			}
			if(is_Pages()) {
				$getcodeblock = $_zp_current_zenpage_page->getCodeblock();
			}
		}
	}	else { // direct page request
		if(getOption('zp_plugin_zenpage')) {
			$page = new ZenpagePage($titlelink);
			$getcodeblock = $page->getCodeblock();
		}
	}
	if (empty($getcodeblock)) return '';
	$codeblock = unserialize($getcodeblock);
	return $codeblock[$number];
}
?>