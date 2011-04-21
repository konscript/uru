<?php

// force UTF-8 Ø

/**
 * stores all the default values for options
 * @package setup
 */

require(CONFIGFILE);

zp_apply_filter('log_setup', true, 'install', '');

setOption('zenphoto_release', ZENPHOTO_RELEASE);

//clear out old admin user and cleartext password
unset($_zp_conf_vars['adminuser']);
unset($_zp_conf_vars['adminpass']);
$admin = getOption('adminuser');
if (!empty($admin)) {   // transfer the old credentials and then remove them
	$sql = 'DELETE FROM '.prefix('options').' WHERE `name`="adminuser"';
	query($sql);
	$sql = 'DELETE FROM '.prefix('options').' WHERE `name`="adminpass"';
	query($sql);
	$sql = 'DELETE FROM '.prefix('options').' WHERE `name`="admin_name"';
	query($sql);
	$sql = 'DELETE FROM '.prefix('options').' WHERE `name`="admin_email"';
	query($sql);
}

if ($_zp_authority->version != ($oldv = getOption('libauth_version'))) {
	if (!empty($oldv)) {
		$_zp_authority->migrateAuth(getOption('libauth_version'));
	}
	setOption('libauth_version',$_zp_authority->version);
}

// old zp-config.php opitons. preserve them
$conf = $_zp_conf_vars;
gettext($str = "Gallery");
setOptionDefault('gallery_title', getAllTranslations($str));
setOptionDefault('website_title', "");
setOptionDefault('website_url', "");
setOptionDefault('time_offset', 0);
if (isset($_GET['mod_rewrite'])) {
	if ($_GET['mod_rewrite'] == 'ON') {
		?>
		<script type="text/javascript">
			// <!-- <![CDATA[
			$.ajax({
				type: 'POST',
				url: '<?php echo WEBPATH; ?>/page/setup_set-mod_rewrite?z'
			});
			// ]]> -->
		</script>
		<?php
	} else {
		setOption('mod_rewrite', 0);
	}
}
setOptionDefault('mod_rewrite_image_suffix', ".php");
setOptionDefault('server_protocol', "http");
setOptionDefault('charset', "UTF-8");
setOptionDefault('image_quality', 85);
setOptionDefault('thumb_quality', 75);
setOptionDefault('image_size', 595);
if (!getOption('image_use_longest_side') === '0') {
	setOptionDefault('image_use_side', 'width');
} else {
	setOptionDefault('image_use_side', 'longest');
}
setOptionDefault('image_allow_upscale', 0);
setOptionDefault('thumb_size', 100);
setOptionDefault('thumb_crop', 1);
setOptionDefault('thumb_crop_width', 85);
setOptionDefault('thumb_crop_height', 85);
setOptionDefault('thumb_sharpen', 0);
setOptionDefault('image_sharpen', 0);
setOptionDefault('albums_per_page', 5);
setOptionDefault('images_per_page', 15);

setOptionDefault('gallery_password', '');
setOptionDefault('gallery_hint', NULL);
setOptionDefault('search_password', '');
setOptionDefault('search_hint', NULL);
setOptionDefault('gmaps_apikey', "");
setOptionDefault('album_session', 0);

if (getOption('perform_watermark')) {
	$v = str_replace('.png', "", basename(getOption('watermark_image')));
	setoptionDefault('fullimage_watermark', $v);
}

setOptionDefault('watermark_h_offset', 90);
setOptionDefault('watermark_w_offset', 90);
setOptionDefault('watermark_scale', 5);
setOptionDefault('watermark_allow_upscale', 1);
setOptionDefault('perform_video_watermark', 0);

if (getOption('perform_video_watermark')) {
	$v = str_replace('.png', "", basename(getOption('video_watermark_image')));
	setoptionDefault('Video_watermark', $v);
}

setOptionDefault('spam_filter', 'none');
setOptionDefault('email_new_comments', 1);
setOptionDefault('gallery_sorttype', 'ID');
setOptionDefault('gallery_sortdirection', '0');
setOptionDefault('image_sorttype', 'Filename');
setOptionDefault('image_sortdirection', '0');
setOptionDefault('hotlink_protection', '1');
setOptionDefault('current_theme', 'default');
setOptionDefault('feed_items', 10); // options for standard images rss
setOptionDefault('feed_imagesize', 240);
setOptionDefault('feed_sortorder', 'latest');
setOptionDefault('feed_items_albums', 10); // options for albums rss
setOptionDefault('feed_imagesize_albums', 240);
setOptionDefault('feed_sortorder_albums', 'latest');
setOptionDefault('feed_enclosure', '0');
setOptionDefault('feed_mediarss', '0');
setOptionDefault('feed_cache', '1');
setOptionDefault('feed_cache_expire', 86400);
setOptionDefault('search_fields', 'title,desc,tags,file,location,city,state,country,content,author');

$a =	"a => (href =>() title =>() target=>() class=>() id=>())\n" .
 			"abbr =>(class=>() id=>() title =>())\n" .
 			"acronym =>(class=>() id=>() title =>())\n" .
 			"b => (class=>() id=>() )\n" .
 			"blockquote =>(class=>() id=>() cite =>())\n" .
			"br => (class=>() id=>() )\n" .
			"code => (class=>() id=>() )\n" .
 			"em => (class=>() id=>() )\n" .
 			"i => (class=>() id=>() ) \n" .
 			"strike => (class=>() id=>() )\n" .
 			"strong => (class=>() id=>() )\n" .
 			"ul => (class=>() id=>())\n" .
 			"ol => (class=>() id=>())\n" .
 			"li => (class=>() id=>())\n" .
			"p => (class=>() id=>() style=>())\n" .
			"h1=>(class=>() id=>() style=>())\n" .
			"h2=>(class=>() id=>() style=>())\n" .
			"h3=>(class=>() id=>() style=>())\n" .
			"h4=>(class=>() id=>() style=>())\n" .
			"h5=>(class=>() id=>() style=>())\n" .
			"h6=>(class=>() id=>() style=>())\n" .
			"pre=>(class=>() id=>() style=>())\n" .
			"address=>(class=>() id=>() style=>())\n" .
			"span=>(class=>() id=>() style=>())\n".
			"div=>(class=>() id=>() style=>())\n".
			"img=>(class=>() id=>() style=>() src=>() title=>() alt=>() width=>() height=>())\n"
			;
setOption('allowed_tags_default', $a);
setOptionDefault('allowed_tags', $a);
setOptionDefault('style_tags',
"abbr => (title => ())\n" .
 "acronym => (title => ())\n" .
 "b => ()\n" .
 "em => ()\n" .
 "i => () \n" .
 "strike => ()\n" .
 "strong => ()\n");
setOptionDefault('comment_name_required', 1);
setOptionDefault('comment_email_required', 1);
setOptionDefault('comment_web_required', 0);
setOptionDefault('Use_Captcha', false);
setOptionDefault('full_image_quality', 75);
setOptionDefault('persistent_archive', 0);

if (getOption('protect_full_image') === '0') {
	$protection = 'Unprotected';
} else if (getOption('protect_full_image') === '1') {
	if (getOption('full_image_download')) {
		$protection = 'Download';
	} else {
		$protection = 'Protected view';
	}
} else {
	$protection = false;
}
if ($protection) {
	setOption('protect_full_image', $protection);
} else {
	setOptionDefault('protect_full_image', 'Protected view');
}

setOptionDefault('locale', '');
setOptionDefault('date_format', '%x');

// plugins--default to enabled
setOptionDefault('zp_plugin_google_maps', 0);
setOptionDefault('zp_plugin_rating', 0);
setOptionDefault('zp_plugin_image_album_statistics', 0);
setOptionDefault('zp_plugin_flowplayer', 0);

setOptionDefault('zp_plugin_class-video', 1);
setOptionDefault('zp_plugin_filter-zenphoto_seo', 1);

setOptionDefault('use_lock_image', 1);
setOptionDefault('gallery_user', '');
setOptionDefault('search_user', '');
setOptionDefault('album_use_new_image_date', 0);
setOptionDefault('thumb_select_images', 0);
gettext($str = 'You can insert your Gallery description using on the Admin Options tab.');
setOptionDefault('Gallery_description', getAllTranslations($str));
setOptionDefault('multi_lingual', 0);
setOptionDefault('login_user_field', 1);
setOptionDefault('tagsort', 0);
setOptionDefault('albumimagesort', 'ID');
setOptionDefault('albumimagedirection', 'DESC');
setOptionDefault('cache_full_image', 0);
setOptionDefault('custom_index_page', '');
setOptionDefault('picture_of_the_day', serialize(array('day'=>NULL,'folder'=>NULL,'filename'=>NULL)));
setOptionDefault('exact_tag_match', 0);

setOptionDefault('EXIFMake', 1);
setOptionDefault('EXIFModel', 1);
setOptionDefault('EXIFExposureTime', 1);
setOptionDefault('EXIFFNumber', 1);
setOptionDefault('EXIFFocalLength', 1);
setOptionDefault('EXIFISOSpeedRatings', 1);
setOptionDefault('EXIFDateTimeOriginal', 1);
setOptionDefault('EXIFExposureBiasValue', 1);
setOptionDefault('EXIFMeteringMode', 1);
setOptionDefault('EXIFFlash', 1);
foreach ($_zp_exifvars as $key=>$item) {
	setOptionDefault($key, 0);
}
setOptionDefault('auto_rotate', 0);
setOptionDefault('IPTC_encoding', 'ISO-8859-1');

setOptionDefault('UTF8_image_URI', 0);
setOptionDefault('captcha', 'zenphoto');

setOptionDefault('sharpen_amount', 40);
setOptionDefault('sharpen_radius', 0.5);
setOptionDefault('sharpen_threshold', 3);

setOptionDefault('thumb_gray', 0);
setOptionDefault('image_gray', 0);
setOptionDefault('search_space_is_or', 0);
setOptionDefault('search_no_albums', 0);

// set filter load priorities
foreach (getEnabledPlugins() as $extension => $class) { // fix the options of 'assumed' backend plugins
	if ($class == 1 && (strpos($extension, 'class-') !== false)) {
		setOption('zp_plugin_'.$extension, 9);
	}
	if ($class == 1 && strpos($extension, 'filter-') === 0) {
		setOption('zp_plugin_'.$extension, 5);
	}
}
if (getOption('zp_plugin_rating') == 1) setOption('zp_plugin_rating', 5);

// default groups
define('administrators',1);
define('viewers',2);
define('bozos',4);
define('album_managers',8);
define('default_user',16);
define('newuser',32);

$groupsdefined = @unserialize(getOption('defined_groups'));
if (!is_array($groupsdefined)) $groupsdefined = array();
if (!in_array('administrators',$groupsdefined)) {
	$groupobj = $_zp_authority->newAdministrator('administrators',0);
	$groupobj->setName('group');
	$groupobj->setRights(ALL_RIGHTS);
	$groupobj->setCustomData(gettext('Users with full privileges'));
	$groupobj->setValid(0);
	$groupobj->save();
	$groupsdefined[] = 'administrators';
}
if (!in_array('viewers',$groupsdefined)) {
	$groupobj = $_zp_authority->newAdministrator('viewers',0);
	$groupobj->setName('group');
	$groupobj->setRights(NO_RIGHTS | POST_COMMENT_RIGHTS | VIEW_ALL_RIGHTS);
	$groupobj->setCustomData(gettext('Users allowed only to view zenphoto objects'));
	$groupobj->setValid(0);
	$groupobj->save();
	$groupsdefined[] = 'viewers';
}
if (!in_array('bozos',$groupsdefined)) {
	$groupobj = $_zp_authority->newAdministrator('bozos',0);
	$groupobj->setName('group');
	$groupobj->setRights(0);
	$groupobj->setCustomData(gettext('Banned users'));
	$groupobj->setValid(0);
	$groupobj->save();
	$groupsdefined[] = 'bozos';
}
if (!in_array('album managers',$groupsdefined)) {
	$groupobj = $_zp_authority->newAdministrator('album managers',0);
	$groupobj->setName('template');
	$groupobj->setRights(NO_RIGHTS | OVERVIEW_RIGHTS | POST_COMMENT_RIGHTS | VIEW_ALL_RIGHTS | UPLOAD_RIGHTS | COMMENT_RIGHTS | ALBUM_RIGHTS | THEMES_RIGHTS);
	$groupobj->setCustomData(gettext('Managers of one or more albums'));
	$groupobj->setValid(0);
	$groupobj->save();
	$groupsdefined[] = 'album managers';
}
if (!in_array('default',$groupsdefined)) {
	$groupobj = $_zp_authority->newAdministrator('default',0);
	$groupobj->setName('template');
	$groupobj->setRights(DEFAULT_RIGHTS);
	$groupobj->setCustomData(gettext('Default user settings'));
	$groupobj->setValid(0);
	$groupobj->save();
	$groupsdefined[] = 'default';
}
if (!in_array('newuser',$groupsdefined)) {
	$groupobj = $_zp_authority->newAdministrator('newuser',0);
	$groupobj->setName('template');
	$groupobj->setRights(NO_RIGHTS);
	$groupobj->setCustomData(gettext('Newly registered and verified users'));
	$groupobj->setValid(0);
	$groupobj->save();
	$groupsdefined[] = 'newuser';
}
setOption('defined_groups',serialize($groupsdefined)); // record that these have been set once (and never again)

if (getOption('Allow_comments') || getOption('zenpage_comments_allowed')) {
	setOptionDefault('zp_plugin_comment_form', 5);
	if (!is_null($default = getOption('Allow_comments'))) {
		setOptionDefault('comment_form_albums', $default);
		setOptionDefault('comment_form_images', $default);
	}
	if (!is_null($default = getOption('zenpage_comments_allowed'))) {
		setOptionDefault('comment_form_articles', $default);
		setOptionDefault('comment_form_pages', $default);
	}
}
setOptionDefault('comment_body_requiired', 1);

setOption('zp_plugin_rating', -abs(getOption('zp_plugin_rating')));

setOptionDefault('zp_plugin_zenphoto_sendmail', 5);
setOptionDefault('gallery_page_unprotected_register', 5);
setOptionDefault('gallery_page_unprotected_contact', 5);

setOptionDefault('RSS_album_image', 1);
setOptionDefault('RSS_comments', 1);
setOptionDefault('RSS_articles', 1);
setOptionDefault('RSS_article_comments', 1);

if (file_exists(dirname(__FILE__).'/js/editor_config.js.php') && file_exists(SERVERPATH . "/" . ZENFOLDER .'/'. PLUGIN_FOLDER. "/tiny_mce/tiny_mce.js")) {
	setOptionDefault('tinyMCEPresent',1);
} else {
	setOptionDefault('tinyMCEPresent',0);
}

setOptionDefault('AlbumThumbSelectField','ID');
setOptionDefault('AlbumThumbSelectDirection','DESC');
gettext($str = 'most recent');
setOptionDefault('AlbumThumbSelecorText',getAllTranslations($str));

setOptionDefault('site_email',"zenphoto@".$_SERVER['SERVER_NAME']);

setOptionDefault('zenpage_news_page', 'news');
setOptionDefault('zenpage_pages_page', 'pages');

if (isset($_GET['themelist'])) {
	$list = sanitize($_GET['themelist']);
	setOption('Zenphoto_theme_list',$list);
	$gallery = new Gallery();
	$themes = array_diff(array_keys($gallery->getThemes()), unserialize($list));
	if (count($themes)>0) setOption('zp_plugin_deprecated-functions',1);
}
setOptionDefault('zp_plugin_zenphoto_news', (version_compare(PHP_VERSION, '5.0.0') == 1)?5:0);
setOptionDefault('zp_plugin_hitcounter',1);
setOptionDefault('zp_plugin_tiny_mce', 5);
setOptionDefault('zp_plugin_security-logger', 9);
// migrate search space is opton
if (getOption('search_space_is_OR')) {
	setOption('search_space_is', '|');
}
query('DELETE FROM '.prefix('options').' WHERE `name`="search_space_is_OR"',true);

if (!file_exists(SERVERPATH.'/'.WEBPATH.'/'.ZENFOLDER.'/favicon.ico')) {
	@copy(SERVERPATH.'/'.ZENFOLDER.'/images/favicon.ico',SERVERPATH.'/favicon.ico');
}

?>
