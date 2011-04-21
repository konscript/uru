<?php
/**
 * basic functions used by zenphoto
 * Headers not sent yet!
 * @package functions
 *
 */

// force UTF-8 Ø

global $_zp_setupCurrentLocale_result, $_zp_current_context_stack, $_zp_HTML_cache;

if(!function_exists("json_encode")) {
	// load the drop-in replacement library
	require_once(dirname(__FILE__).'/lib-json.php');
}

require_once(dirname(__FILE__).'/functions-basic.php');
require_once(dirname(__FILE__).'/functions-filter.php');


$_zp_captcha = getOption('captcha');
if (empty($_zp_captcha)) 	$_zp_captcha = 'zenphoto';
require_once(getPlugin('captcha/'.$_zp_captcha.'.php'));
$_zp_captcha = new Captcha();

//setup session before checking for logon cookie
require_once(dirname(__FILE__).'/functions-i18n.php');

if (getOption('album_session') && session_id() == '') {
	// force session cookie to be secure when in https
	if(secureServer()) {
		$CookieInfo=session_get_cookie_params();
		session_set_cookie_params($CookieInfo['lifetime'],$CookieInfo['path'], $CookieInfo['domain'],TRUE);
	}
	session_start();
}

$_zp_setupCurrentLocale_result = setMainDomain();

require_once(dirname(__FILE__).'/class-load.php');
require_once(dirname(__FILE__).'/auth_zp.php');

// make sure these are defined
define('ZENPAGE_NEWS',getOption("zenpage_news_page"));
define('ZENPAGE_PAGES',getOption("zenpage_pages_page"));

$_zp_current_context_stack = array();


/**
 * initializes the $_zp_exifvars array display state
 *
 */
function setexifvars() {
	global $_zp_exifvars;
	/*
	 * Note: If fields are added or deleted, setup.php should be run or the new data won't be stored
	 * (but existing fields will still work; nothing breaks).
	 *
	 * This array should be ordered by logical associations as it will be the order that EXIF information
	 * is displayed
	 */
	$_zp_exifvars = array(
		// Database Field       		=> array('IFDX', 	 'Metadata Key',     			'ZP Display Text',        				 	 									Display?	size)
		'EXIFMake'              		=> array('IFD0',   'Make',              		gettext('Camera Maker'),           										true,				52),
		'EXIFModel'             		=> array('IFD0',   'Model',             		gettext('Camera Model'),           										true,				52),
		'EXIFDescription'       		=> array('IFD0',   'ImageDescription',  		gettext('Image Title'), 	         										false,			52),
		'IPTCObjectName'						=> array('IPTC',	 'ObjectName',						gettext('Object name'),																false,			256),
		'IPTCImageHeadline'  				=> array('IPTC',	 'ImageHeadline',					gettext('Image headline'),														false,			256),
		'IPTCImageCaption' 					=> array('IPTC',	 'ImageCaption',					gettext('Image caption'),															false,			2000),
		'IPTCImageCaptionWriter' 		=> array('IPTC',	 'ImageCaptionWriter',		gettext('Image caption writer'),											false,			32),
		'EXIFDateTime'  						=> array('SubIFD', 'DateTime', 				 			gettext('Time Taken'),            										true,				52),
		'EXIFDateTimeOriginal'  		=> array('SubIFD', 'DateTimeOriginal',  		gettext('Original Time Taken'),        								true,				52),
		'EXIFDateTimeDigitized'  		=> array('SubIFD', 'DateTimeDigitized',  		gettext('Time Digitized'),            								true,				52),
		'IPTCDateCreated'					  => array('IPTC',	 'DateCreated',						gettext('Date created'),															false,			8),
		'IPTCTimeCreated' 					=> array('IPTC',	 'TimeCreated',						gettext('Time created'),															false,			11),
		'IPTCDigitizeDate' 					=> array('IPTC',	 'DigitizeDate',					gettext('Digital Creation Date'),											false,			8),
		'IPTCDigitizeTime' 					=> array('IPTC',	 'DigitizeTime',					gettext('Digital Creation Time'),											false,			11),
		'EXIFArtist'			      		=> array('IFD0',   'Artist', 								gettext('Artist'), 	     					 										false,			52),
		'IPTCImageCredit'						=> array('IPTC',	 'ImageCredit',						gettext('Image Credit'),															false,			32),
		'IPTCByLine' 								=> array('IPTC',	 'ByLine',								gettext('ByLine'),																		false,			32),
		'IPTCByLineTitle' 					=> array('IPTC',	 'ByLineTitle',						gettext('ByLine Title'),															false,			32),
		'IPTCSource'								=> array('IPTC',	 'Source',								gettext('Image source'),															false,			32),
		'IPTCContact' 							=> array('IPTC',	 'Contact',								gettext('Contact'),																		false,			128),
		'EXIFCopyright'			    		=> array('IFD0',   'Copyright', 						gettext('Copyright Holder'), 			 										false,			128),
		'IPTCCopyright'							=> array('IPTC',	 'Copyright',							gettext('Copyright Notice'),													false,			128),
		'EXIFExposureTime'      		=> array('SubIFD', 'ExposureTime',      		gettext('Shutter Speed'),          										true,				52),
		'EXIFFNumber'           		=> array('SubIFD', 'FNumber',           		gettext('Aperture'),               										true,				52),
		'EXIFISOSpeedRatings'   		=> array('SubIFD', 'ISOSpeedRatings',   		gettext('ISO Sensitivity'),        										true,				52),
		'EXIFExposureBiasValue' 		=> array('SubIFD', 'ExposureBiasValue', 		gettext('Exposure Compensation'), 										true,				52),
		'EXIFMeteringMode'      		=> array('SubIFD', 'MeteringMode',      		gettext('Metering Mode'),         										true,				52),
		'EXIFFlash'             		=> array('SubIFD', 'Flash',             		gettext('Flash Fired'),         											true,				52),
		'EXIFImageWidth'        		=> array('SubIFD', 'ExifImageWidth',    		gettext('Original Width'),														false,			52),
		'EXIFImageHeight'       		=> array('SubIFD', 'ExifImageHeight',   		gettext('Original Height'),      											false,			52),
		'EXIFOrientation'       		=> array('IFD0',   'Orientation',       		gettext('Orientation'),            										false,			52),
		'EXIFContrast'          		=> array('SubIFD', 'Contrast',          		gettext('Contrast Setting'),      										false,			52),
		'EXIFSharpness'         		=> array('SubIFD', 'Sharpness',         		gettext('Sharpness Setting'),     										false,			52),
		'EXIFSaturation'        		=> array('SubIFD', 'Saturation',        		gettext('Saturation Setting'),    										false,			52),
		'EXIFWhiteBalance'					=> array('SubIFD', 'WhiteBalance',					gettext('White Balance'),															false,			52),
		'EXIFSubjectDistance'				=> array('SubIFD', 'SubjectDistance',				gettext('Subject Distance'),													false,			52),
		'EXIFFocalLength'       		=> array('SubIFD', 'FocalLength',       		gettext('Focal Length'),           										true,				52),
		'EXIFLensType'       				=> array('SubIFD', 'LensType',       				gettext('Lens Type'),   	        										false,			52),
		'EXIFLensInfo'       				=> array('SubIFD', 'LensInfo',      		 		gettext('Lens Info'),           											false,			52),
		'EXIFFocalLengthIn35mmFilm'	=> array('SubIFD', 'FocalLengthIn35mmFilm',	gettext('Focal Length Equivalent in 35mm Film'),			false,			52),
		'IPTCCity' 									=> array('IPTC',	 'City',									gettext('City'),																			false,			32),
		'IPTCSubLocation' 					=> array('IPTC',	 'SubLocation',						gettext('Sublocation'),																false,			32),
		'IPTCState' 								=> array('IPTC',	 'State',									gettext('Province/State'),														false,			32),
		'IPTCLocationCode' 					=> array('IPTC',	 'LocationCode',					gettext('Country/Primary Location Code'),							false,			3),
		'IPTCLocationName' 					=> array('IPTC',	 'LocationName',					gettext('Country/Primary Location Name'),							false,			64),
		'EXIFGPSLatitude'       		=> array('GPS',    'Latitude',          		gettext('Latitude'),              										false,			52),
		'EXIFGPSLatitudeRef'    		=> array('GPS',    'Latitude Reference',		gettext('Latitude Reference'),    										false,			52),
		'EXIFGPSLongitude'      		=> array('GPS',    'Longitude',         		gettext('Longitude'),             										false,			52),
		'EXIFGPSLongitudeRef'   		=> array('GPS',    'Longitude Reference',		gettext('Longitude Reference'),  											false,			52),
		'EXIFGPSAltitude'       		=> array('GPS',    'Altitude',          		gettext('Altitude'),              										false,			52),
		'EXIFGPSAltitudeRef'    		=> array('GPS',    'Altitude Reference',		gettext('Altitude Reference'),  											false,			52),
		'IPTCOriginatingProgram'		=> array('IPTC',	 'OriginatingProgram',		gettext('Originating Program '),											false,			32),
		'IPTCProgramVersion'			 	=> array('IPTC',	 'ProgramVersion',				gettext('Program version'),														false,			10)
		);
	foreach ($_zp_exifvars as $key=>$item) {
		$_zp_exifvars[$key][3] = getOption($key);
	}
}

/**
 * parses the allowed HTML tags for use by htmLawed
 *
 *@param string &$source by name, contains the string with the tag options
 *@return array the allowed_tags array.
 *@since 1.1.3
 **/
function parseAllowedTags(&$source) {
	$source = trim($source);
	if (substr($source, 0, 1) != "(") { return false; }
	$source = substr($source, 1); //strip off the open paren
	$a = array();
	while ((strlen($source) > 1) && (substr($source, 0, 1) != ")")) {
		$i = strpos($source, '=>');
		if ($i === false) { return false; }
		$tag = trim(substr($source, 0, $i));
		$source = trim(substr($source, $i+2));
		if (substr($source, 0, 1) != "(") { return false; }
		$x = parseAllowedTags($source);
		if ($x === false) { return false; }
		$a[$tag] = $x;
	}
	if (substr($source, 0, 1) != ')') { return false; }
	$source = trim(substr($source, 1)); //strip the close paren
	return $a;
}

// Image utility functions
/**
 * Returns true if the file is an image
 *
 * @param string $filename the name of the target
 * @return bool
 */
function is_valid_image($filename) {
	global $_zp_supported_images;
	$ext = strtolower(substr(strrchr($filename, "."), 1));
	return in_array($ext, $_zp_supported_images);
}

/**
 * Search for a thumbnail for the image
 *
 * @param string $album folder path of the album
 * @param string $image name of the target
 * @return string
 */
function checkObjectsThumb($album, $image){
	global $_zp_supported_images;
	$image = substr($image, 0, strrpos($image,'.'));
	$candidates = safe_glob($album.$image.'.*');
	foreach ($candidates as $file) {
		$ext = substr($file,strrpos($file,'.')+1);
		if (in_array(strtolower($ext),$_zp_supported_images)) {
			return $image.'.'.$ext;
		}
	}
/* save incase the above becomes a performance issue.
	foreach($_zp_supported_images as $ext) {
		if(file_exists(internalToFilesystem($album."/".$image.'.'.$ext))) {
			return $image.'.'.$ext;
		}
	}
*/
	return NULL;
}

/**
 * Returns a truncated string
 *
 * @param string $string souirce string
 * @param int $length how long it should be
 * @param string $elipsis the text to tack on indicating shortening
 * @return string
 */
function truncate_string($string, $length, $elipsis='...') {
	if (strlen($string) > $length) {
		$pos = strpos($string, ' ', $length);
		if ($pos === FALSE) return substr($string, 0, $length) . $elipsis;
		return substr($string, 0, $pos) . $elipsis;
	}
	return $string;
}

/**
 * Returns the oldest ancestor of an alubm;
 *
 * @param string $album an album object
 * @return object
 */
function getUrAlbum($album) {
	if (!is_object($album)) return NULL;
	while (true) {
		$parent = $album->getParent();
		if (is_null($parent)) { return $album; }
		$album = $parent;
	}
}

/**
 * Returns a sort field part for querying
 * Note: $sorttype may be a comma separated list of field names. If so,
 *       these are peckmarked and returned otherwise unchanged.
 *
 * @param string $sorttype the 'Display" name of the sort
 * @param string $default the default if $sorttype is empty
 * @param string $filename the value to be used if $sorttype is 'Filename' since
 * 												 the field is different between the album table and the image table.
 * @return string
 */
function lookupSortKey($sorttype, $default, $filename) {
	$sorttype = strtolower($sorttype);
	switch ($sorttype) {
		case 'random':
			return 'RAND()';
		case "manual":
			return '`sort_order`';
		case "filename":
			return '`'.$filename.'`';
		default:
			if (empty($sorttype)) return $default;
			$list = explode(',', $sorttype);
			foreach ($list as $key=>$field) {
				$list[$key] = '`'.trim($field).'`';
			}
			return implode(',', $list);
	}
}

/**
 * Returns a formated date for output
 *
 * @param string $format the "strftime" format string
 * @param date $dt the date to be output
 * @return string
 */
function zpFormattedDate($format, $dt) {
	global $_zp_UTF8;
	$fdate = strftime($format, $dt);
	$charset = 'ISO-8859-1';
	$outputset = getOption('charset');
	if (function_exists('mb_internal_encoding')) {
		if (($charset = mb_internal_encoding()) == $outputset) {
			return $fdate;
		}
	}
	return $_zp_UTF8->convert($fdate, $charset, $outputset);
}

/**
 * Simple SQL timestamp formatting function.
 *
 * @param string $format formatting template
 * @param int $mytimestamp timestamp
 * @return string
 */
function myts_date($format,$mytimestamp) {
	// If your server is in a different time zone than you, set this.
	$timezoneadjust = getOption('time_offset');

	$month  = substr($mytimestamp,4,2);
	$day    = substr($mytimestamp,6,2);
	$year   = substr($mytimestamp,0,4);

	$hour   = substr($mytimestamp,8,2);
	$min    = substr($mytimestamp,10,2);
	$sec    = substr($mytimestamp,12,2);

	$epoch  = mktime($hour+$timezoneadjust,$min,$sec,$month,$day,$year);
	$date   = zpFormattedDate($format, $epoch);
	return $date;
}

/**
 * Get the size of a directory.
 * From: http://aidan.dotgeek.org/lib/
 *
 * @author      Aidan Lister <aidan@php.net>
 * @version     1.0.0
 * @param       string $directory   Path to directory
 */
function dirsize($directory) {
	$size = 0;
	if (substr($directory, -1, 1) !== DIRECTORY_SEPARATOR) {
		$directory .= DIRECTORY_SEPARATOR;
	}
	$stack = array($directory);
	for ($i = 0, $j = count($stack); $i < $j; ++$i) {
		if (is_file($stack[$i])) {
			$size += filesize($stack[$i]);
		} else if (is_dir($stack[$i])) {
			$dir = dir($stack[$i]);
			while (false !== ($entry = $dir->read())) {
				if ($entry == '.' || $entry == '..') continue;
				$add = $stack[$i] . $entry;
				if (is_dir($stack[$i] . $entry)) $add .= DIRECTORY_SEPARATOR;
				$stack[] = $add;
			}
			$dir->close();
		}
		$j = count($stack);
	}
	return $size;
}

// Text formatting and checking functions

/**
 * Determines if the input is an e-mail address. Adapted from WordPress.
 * Name changed to avoid conflicts in WP integrations.
 *
 * @param string $input_email email address?
 * @return bool
 */
function is_valid_email_zp($input_email) {
	$chars = "/^([a-z0-9+_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,6}\$/i";
	if(strstr($input_email, '@') && strstr($input_email, '.')) {
		if (preg_match($chars, $input_email)) {
			return true;
		}
	}
	return false;
}

/**
 * Send an mail to the mailing list. We also attempt to intercept any form injection
 * attacks by slime ball spammers. Returns error message if send failure.
 *
 * @param string $subject  The subject of the email.
 * @param string $message  The message contents of the email.
 * @param string $from_mail Optional sender for the email.
 * @param string $from_name Optional sender for the name.
 * @param array $email_list a list of email addresses
 * @param array $cc_addresses a list of addresses to send copies to.
 * @param array $bcc_addresses a list of addresses to send blind copies to.
 *
 * @return string
 *
 * @author Todd Papaioannou (lucky@luckyspin.org)
 * @since  1.0.0
 */
function zp_mail($subject, $message, $email_list=NULL, $cc_addresses=NULL, $bcc_addresses=NULL) {
	global $_zp_authority;
	$result = '';
	if (is_null($email_list)) {
		$email_list = $_zp_authority->getAdminEmail();
	}
	if (is_null($cc_addresses)) {
		$cc_addresses = array();
	}
	if (count($email_list) + count($bcc_addresses) > 0) {
		if (zp_has_filter('sendmail')) {
			// Make sure no one is trying to use our forms to send Spam
			// Stolen from Hosting Place:
			//   http://support.hostingplace.co.uk/knowledgebase.php?action=displayarticle&cat=0000000039&id=0000000040
			$badStrings = array("Content-Type:", "MIME-Version:",	"Content-Transfer-Encoding:",	"bcc:",	"cc:");
			foreach($_POST as $k => $v) {
				foreach($badStrings as $v2) {
					if (strpos($v, $v2) !== false) {
						header("HTTP/1.0 403 Forbidden");
						header("Status: 403 Forbidden");
						die("Forbidden");
						exit();
					}
				}
			}

			foreach($_GET as $k => $v){
				foreach($badStrings as $v2){
					if (strpos($v, $v2) !== false){
						header("HTTP/1.0 403 Forbidden");
						header("Status: 403 Forbidden");
						die("Forbidden");
						exit();
					}
				}
			}

			$from_mail = getOption('site_email');
			$from_name = get_language_string(getOption('gallery_title'), getOption('locale'));

			// Convert to UTF-8
			if (getOption('charset') != 'UTF-8') {
				$subject = $_zp_UTF8->convert($subject, getOption('charset'));
				$message = $_zp_UTF8->convert($message, getOption('charset'));
			}

			// Send the mail
			if (count($email_list) > 0) {
				$result = zp_apply_filter('sendmail', '', $email_list, $subject, $message, $from_mail, $from_name, $cc_addresses); // will be true if all mailers succeeded
			}
			if (count($bcc_addresses) > 0) {
				foreach ($bcc_addresses as $bcc) {
					$result = zp_apply_filter('sendmail', '', array($bcc), $subject, $message, $from_mail, $from_name, array()); // will be true if all mailers succeeded
				}
			}
		} else {
			$result = gettext('Mail send failed. There is no mail handler configured.');
		}
	} else {
		$result = gettext('Mail send failed. The "to" list is empty.');
	}
	return $result;
}

/**
 * Sorts the results of a DB search by the current locale string for $field
 *
 * @param array $dbresult the result of the DB query
 * @param string $field the field name to sort on
 * @param bool $descending the direction of the sort
 * @return array the sorted result
 */
function sortByMultilingual($dbresult, $field, $descending) {
	$temp = array();
	foreach ($dbresult as $key=>$row) {
		$temp[$key] = get_language_string($row[$field]);
	}
	natcasesort($temp);
	$result = array();
	foreach ($temp as $key=>$title) {
		if ($descending) {
			array_unshift($result, $dbresult[$key]);
		} else {
			$result[] = $dbresult[$key];
		}
	}
	return $result;
}

/**
 * Checks to see access is allowed to an album
 * Returns true if access is allowed.
 * There is no password dialog--you must have already had authorization via a cookie.
 *
 * @param string $album album object or name of the album
 * @param string &$hint becomes populated with the password hint.
 * @return bool
 */
function checkAlbumPassword($album, &$hint) {
	global $_zp_pre_authorization, $_zp_gallery;
	if (is_object($album)) {
		$albumname = $album->name;
	} else {
		if (!is_object($_zp_gallery)) $_zp_gallery = new Gallery();
		$album = new Album($_zp_gallery, $albumname=$album);
	}
	if (zp_loggedin(ADMIN_RIGHTS | LIST_ALBUM_RIGHTS | MANAGE_ALL_ALBUM_RIGHTS)) return 'zp_master_admin';
	if (isMyAlbum($albumname, LIST_ALBUM_RIGHTS)) return 'zp_album_admin';  // he is allowed to see it.
	if (isset($_zp_pre_authorization[$albumname])) {
		return $_zp_pre_authorization[$albumname];
	}
	$hash = $album->getPassword();
	if (empty($hash)) {
		$album = $album->getParent();
		while (!is_null($album)) {
			$hash = $album->getPassword();
			$authType = "zp_album_auth_" . $album->get('id');
			$saved_auth = zp_getCookie($authType);

			if (!empty($hash)) {
				if ($saved_auth == $hash) {
					$_zp_pre_authorization[$albumname] = $authType;
					return $authType;
				} else {
					$hint = $album->getPasswordHint();
					return false;
				}
			}
			$album = $album->getParent();
		}
		// revert all tlhe way to the gallery
		$hash = getOption('gallery_password');
		$authType = 'zp_gallery_auth';
		$saved_auth = zp_getCookie($authType);
		if (empty($hash)) {
			$authType = 'zp_public_access';
		} else {
			if ($saved_auth != $hash) {
				$hint = get_language_string(getOption('gallery_hint'));
				return false;
			}
		}
	} else {
		$authType = "zp_album_auth_" . $album->get('id');
		$saved_auth = zp_getCookie($authType);
		if ($saved_auth != $hash) {
			$hint = $album->getPasswordHint();
			return false;
		}
	}
	$_zp_pre_authorization[$albumname] = $authType;
	return $authType;
}

/**
 * Processes a file for createAlbumZip
 *
 * @param string $dest the source [sic] to process
 */
function printLargeFileContents($dest) {
	$total = filesize($dest);
	$blocksize = (2 << 20); //2M chunks
	$sent = 0;
	$handle	= fopen($dest, "r");
	while ($sent < $total) {
		echo fread($handle, $blocksize);
		$sent += $blocksize;
	}
}

/**
 * Returns a consolidated list of plugins
 * The array structure is key=plugin name, value=plugin path
 *
 * @param string $pattern File system wildcard matching pattern to limit the search
 * @param string $folder subfolder within the plugin folders to search
 * @param bool $stripsuffix set to true to remove the suffix from the key name in the array
 * @return array
 */
function getPluginFiles($pattern, $folder='', $stripsuffix=true) {
	if (!empty($folder) && substr($folder, -1) != '/') $folder .= '/';
	$list = array();

	$curdir = getcwd();
	$basepath = SERVERPATH."/".USER_PLUGIN_FOLDER.'/'.$folder;
	if (is_dir($basepath)) {
		chdir($basepath);
		$filelist = safe_glob($pattern);
		foreach ($filelist as $file) {
			$key = filesystemToInternal($file);
			if ($stripsuffix) {
				$key = substr($key,0,strrpos($key,'.'));
			}
			$list[$key] = $basepath.$file;
		}
	}
	$basepath = SERVERPATH."/".ZENFOLDER.'/'.PLUGIN_FOLDER.'/'.$folder;
	if (file_exists($basepath)) {
		chdir($basepath);
		$filelist = safe_glob($pattern);
		foreach ($filelist as $file) {
			$key = filesystemToInternal($file);
			if ($stripsuffix) {
				$key = substr($key,0,strrpos($key,'.'));
			}
			$list[$key] = $basepath.$file;
		}
		chdir($curdir);
	}
	return $list;
}

/**
 * Returns the fully qualified "require" file name of the plugin file.
 *
 * @param  string $plugin is the name of the plugin file, typically something.php
 * @param  bool $inTheme tells where to find the plugin.
 *   true means look in the current theme
 *   false means look in the zp-core/plugins folder.
 *
 * @return string
 */
function getPlugin($plugin, $inTheme=false, $path=SERVERPATH) {
	global $_zp_themeroot;
	if ($inTheme) {
		$_zp_themeroot = WEBPATH.'/'. THEMEFOLDER.'/'.$inTheme;
		$pluginFile = '/'.THEMEFOLDER.'/'.internalToFilesystem($inTheme.'/'.$plugin);
		if (file_exists(SERVERPATH.$pluginFile)) {
			return $path.$pluginFile;
		} else {
			return false;
		}
	} else {
		$pluginFile = '/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/'.internalToFilesystem($plugin);
		if (file_exists(SERVERPATH.$pluginFile)) {
			return $path.$pluginFile;
		} else {
			$pluginFile = '/'.USER_PLUGIN_FOLDER.'/'.internalToFilesystem($plugin);
			if (file_exists(SERVERPATH.$pluginFile)) {
				return $path.$pluginFile;
			} else {
				return false;
			}
		}
	}
}

/**
 * Returns an array of the currently enabled plugins
 *
 * @return array
 */
function getEnabledPlugins() {
	$pluginlist = array();
	$sortlist = getPluginFiles('*.php');
	foreach ($sortlist as $extension=>$path) {
		$opt = 'zp_plugin_'.$extension;
		if ($option = getOption($opt)) {
			$pluginlist[$extension] = abs($option);
			$sortlist[$extension] = $option;
		}
	}
	arsort($pluginlist);
	foreach($pluginlist as $key=>$value) {
		$pluginlist[$key] = $sortlist[$key];
	}
	return $pluginlist;
}

/**
 * Gets an array of comments for the current admin
 *
 * @param int $number how many comments desired
 * @return array
 */
function fetchComments($number) {
	if ($number) {
		$limit = " LIMIT $number";
	} else {
		$limit = '';
	}

	$comments = array();
	if (zp_loggedin(ADMIN_RIGHTS | COMMENT_RIGHTS)) {
		if (zp_loggedin(ADMIN_RIGHTS | MANAGE_ALL_ALBUM_RIGHTS)) {
			$sql = "SELECT *, (date + 0) AS date FROM ".prefix('comments') . " ORDER BY id DESC$limit";
			$comments = query_full_array($sql);
		} else {
			$albumlist = getManagedAlbumList();
			$albumIDs = array();
			foreach ($albumlist as $albumname) {
				$subalbums = getAllSubAlbumIDs($albumname);
				foreach($subalbums as $ID) {
					$albumIDs[] = $ID['id'];
				}
			}
			if (count($albumIDs) > 0) {
				$sql = "SELECT  *, (`date` + 0) AS date FROM ".prefix('comments')." WHERE ";

				$sql .= " (`type`='albums' AND (";
				$i = 0;
				foreach ($albumIDs as $ID) {
					if ($i>0) { $sql .= " OR "; }
					$sql .= "(".prefix('comments').".ownerid=$ID)";
					$i++;
				}
				$sql .= ")) ";
				$sql .= " ORDER BY id DESC$limit";
				$albumcomments = query_full_array($sql);
				foreach ($albumcomments as $comment) {
					$comments[$comment['id']] = $comment;
				}
				$sql = "SELECT *, ".prefix('comments').".id as id, ".
				prefix('comments').".name as name, (".prefix('comments').".date + 0) AS date, ".
				prefix('images').".`albumid` as albumid,".
				prefix('images').".`id` as imageid".
							" FROM ".prefix('comments').",".prefix('images')." WHERE ";

				$sql .= "(`type` IN (".zp_image_types("'").") AND (";
				$i = 0;
				foreach ($albumIDs as $ID) {
					if ($i>0) { $sql .= " OR "; }
					$sql .= "(".prefix('comments').".ownerid=".prefix('images').".id AND ".prefix('images')
					.".albumid=$ID)";
					$i++;
				}
				$sql .= "))";
				$sql .= " ORDER BY ".prefix('images').".`id` DESC$limit";
				$imagecomments = query_full_array($sql);
				foreach ($imagecomments as $comment) {
					$comments[$comment['id']] = $comment;
				}
				krsort($comments);
				if ($number) {
					if ($number < count($comments)) {
						$comments = array_slice($comments, 0, $number);
					}
				}
			}
		}
	}
	return $comments;
}

define ('COMMENT_EMAIL_REQUIRED', 1);
define ('COMMENT_NAME_REQUIRED', 2);
define ('COMMENT_WEB_REQUIRED', 4);
define ('USE_CAPTCHA', 8);
define ('COMMENT_BODY_REQUIRED', 16);
define ('COMMENT_SEND_EMAIL', 32);
/**
 * Generic comment adding routine. Called by album objects or image objects
 * to add comments.
 *
 * Returns a comment object
 *
 * @param string $name Comment author name
 * @param string $email Comment author email
 * @param string $website Comment author website
 * @param string $comment body of the comment
 * @param string $code CAPTCHA code entered
 * @param string $code_ok CAPTCHA md5 expected
 * @param string $type 'albums' if it is an album or 'images' if it is an image comment
 * @param object $receiver the object (image or album) to which to post the comment
 * @param string $ip the IP address of the comment poster
 * @param bool $private set to true if the comment is for the admin only
 * @param bool $anon set to true if the poster wishes to remain anonymous
 * @param bit $check bitmask of which fields must be checked. If set overrides the options
 * @return object
 */
function postComment($name, $email, $website, $comment, $code, $code_ok, $receiver, $ip, $private, $anon, $check=false) {
	global $_zp_captcha, $_zp_gallery, $_zp_authority;
	if ($check === false) {
		$whattocheck = 0;
		if (getOption('comment_email_required')) $whattocheck = $whattocheck | COMMENT_EMAIL_REQUIRED;
		if (getOption('comment_name_required')) $whattocheck = $whattocheck | COMMENT_NAME_REQUIRED;
		if (getOption('comment_web_required')) $whattocheck = $whattocheck | COMMENT_WEB_REQUIRED;
		if (getOption('Use_Captcha')) $whattocheck = $whattocheck | USE_CAPTCHA;
		if (getOption('comment_body_requiired')) $whattocheck = $whattocheck | COMMENT_BODY_REQUIRED;
		IF (getOption('email_new_comments')) $whattocheck = $whattocheck | COMMENT_SEND_EMAIL;
	} else {
		$whattocheck = $check;
	}
	$type = str_replace('zenpage_', '', $receiver->table); // remove the string 'zenpage_' if it is there.
	$class = get_class($receiver);
	$receiver->getComments();
	$name = trim($name);
	$email = trim($email);
	$website = trim($website);
	$admins = $_zp_authority->getAdministrators();
	$admin = array_shift($admins);
	$key = $admin['pass'];
	if (!empty($website) && substr($website, 0, 7) != "http://") {
		$website = "http://" . $website;
	}
	// Let the comment have trailing line breaks and space? Nah...
	// Also (in)validate HTML here, and in $name.
	$comment = trim($comment);
	$receiverid = $receiver->id;
	$goodMessage = 2;
	if ($private) $private = 1; else $private = 0;
	if ($anon) $anon = 1; else $anon = 0;
	$commentobj = new Comment();
	$commentobj->transient = false; // otherwise we won't be able to save it....
	$commentobj->setOwnerID($receiverid);
	$commentobj->setName($name);
	$commentobj->setEmail($email);
	$commentobj->setWebsite($website);
	$commentobj->setComment($comment);
	$commentobj->setType($type);
	$commentobj->setIP($ip);
	$commentobj->setPrivate($private);
	$commentobj->setAnon($anon);
	$commentobj->setInModeration(2); //mark as not posted
	if (($whattocheck & COMMENT_EMAIL_REQUIRED) && (empty($email) || !is_valid_email_zp($email))) {
		$commentobj->setInModeration(-2);
		$goodMessage = false;
	}
	if (($whattocheck & COMMENT_NAME_REQUIRED) && empty($name)) {
		$commentobj->setInModeration(-3);
		$goodMessage = false;
	}
	if (($whattocheck & COMMENT_WEB_REQUIRED) && (empty($website) || !isValidURL($website))) {
		$commentobj->setInModeration(-4);
		$goodMessage = false;
	}
	if (($whattocheck & USE_CAPTCHA)) {
		if (!$_zp_captcha->checkCaptcha($code, $code_ok)) {
			$commentobj->setInModeration(-5);
			$goodMessage = false;
		}
	}
	if (($whattocheck & COMMENT_BODY_REQUIRED) && empty($comment)) {
		$commentobj->setInModeration(-6);
		$goodMessage = false;
	}
	if ($goodMessage && !(false === ($requirePath = getPlugin('spamfilters/'.internalToFilesystem(getOption('spam_filter')).".php")))) {
		require_once($requirePath);
		$spamfilter = new SpamFilter();
		$goodMessage = $spamfilter->filterMessage($name, $email, $website, $comment, isImageClass($receiver)?$receiver->getFullImage():NULL, $ip);
	}
	if ($goodMessage) {
		if ($goodMessage == 1) {
			$moderate = 1;
		} else {
			$moderate = 0;
		}
		$commentobj->setInModeration($moderate);
	}
	$localerrors = $commentobj->getInModeration();
	zp_apply_filter('comment_post', $commentobj, $receiver);
	if ($check === false)	{ // ignore filter provided errors if caller is supplying the fields to check
		$localerrors = $commentobj->getInModeration();
	}
	if ($goodMessage && $localerrors >= 0)	{
		// Update the database entry with the new comment
		$commentobj->save();
		//  add to comments array and notify the admin user
		if (!$moderate) {
			$receiver->comments[] = array('name' => $commentobj->getname(),
																		'email' => $commentobj->getEmail(),
																		'website' => $commentobj->getWebsite(),
																		'comment' => $commentobj->getComment(),
																		'date' => $commentobj->getDateTime(),
																		'custom_data' => $commentobj->getCustomData());
		}
		$class = strtolower(get_class($receiver));
		switch ($class) {
			case "album":
				$url = "album=" . urlencode($receiver->name);
				$ur_album = getUrAlbum($receiver);
				if ($moderate) {
					$action = sprintf(gettext('A comment has been placed in moderation on your album "%1$s".'), $receiver->name);
				} else {
					$action = sprintf(gettext('A comment has been posted on your album "%1$s".'), $receiver->name);
				}
				break;
			case "zenpagenews":
				$url = "p=".ZENPAGE_NEWS."&title=" . urlencode($receiver->getTitlelink());
				if ($moderate) {
					$action = sprintf(gettext('A comment has been placed in moderation on your article "%1$s".'), $receiver->getTitlelink());
				} else {
					$action = sprintf(gettext('A comment has been posted on your article "%1$s".'), $receiver->getTitlelink());
				}
				break;
			case "zenpagepage":
				$url = "p=".ZENPAGE_PAGES."&title=" . urlencode($receiver->getTitlelink());
				if ($moderate) {
					$action = sprintf(gettext('A comment has been placed in moderation on your page "%1$s".'), $receiver->getTitlelink());
				} else {
					$action = sprintf(gettext('A comment has been posted on your page "%1$s".'), $receiver->getTitlelink());
				}
				break;
			default: // all image types
				$url = "album=" . urlencode($receiver->album->name) . "&image=" . urlencode($receiver->filename);
				$album = $receiver->getAlbum();
				$ur_album = getUrAlbum($album);
				if ($moderate) {
					$action = sprintf(gettext('A comment has been placed in moderation on your image "%1$s" in the album "%2$s".'), $receiver->getTitle(), $receiver->getAlbumName());
				} else {
					$action = sprintf(gettext('A comment has been posted on your image "%1$s" in the album "%2$s".'), $receiver->getTitle(), $receiver->getAlbumName());
				}
				break;
		}
		if (($whattocheck & COMMENT_SEND_EMAIL)) {
			$message = $action . "\n\n" .
					sprintf(gettext('Author: %1$s'."\n".'Email: %2$s'."\n".'Website: %3$s'."\n".'Comment:'."\n\n".'%4$s'),$commentobj->getname(), $commentobj->getEmail(), $commentobj->getWebsite(), $commentobj->getComment()) . "\n\n" .
					sprintf(gettext('You can view all comments about this item here:'."\n".'%1$s'), 'http://' . $_SERVER['SERVER_NAME'] . WEBPATH . '/index.php?'.$url) . "\n\n" .
					sprintf(gettext('You can edit the comment here:'."\n".'%1$s'), 'http://' . $_SERVER['SERVER_NAME'] . WEBPATH . '/' . ZENFOLDER . '/admin-comments.php?page=editcomment&id='.$commentobj->id);
			$emails = array();
			$admin_users = $_zp_authority->getAdministrators();
			foreach ($admin_users as $admin) {  // mail anyone with full rights
				if (!empty($admin['email']) && (($admin['rights'] & ADMIN_RIGHTS) ||
								(($admin['rights'] & (MANAGE_ALL_ALBUM_RIGHTS | COMMENT_RIGHTS)) == (MANAGE_ALL_ALBUM_RIGHTS | COMMENT_RIGHTS)))) {
					$emails[] = $admin['email'];
					unset($admin_users[$admin['id']]);
				}
			}
			if($type === "images" OR $type === "albums") { // mail to album admins
				$id = $ur_album->getAlbumID();
				$sql = 'SELECT `adminid` FROM '.prefix('admin_to_object').' WHERE `objectid`='.$id.' AND `type`="album"';
				$result = query_full_array($sql);
				foreach ($result as $anadmin) {
					$id = $anadmin['adminid'];
					if (array_key_exists($id,$admin_users)) {
						$admin = $admin_users[$id];
						if (($admin['rights'] & COMMENT_RIGHTS) && !empty($admin['email'])) {
							$emails[] = $admin['email'];
						}
					}
				}
			}
			$on = gettext('Comment posted');
			$gallery = new Gallery();
			zp_mail("[" . $gallery->getTitle() . "] $on", $message, $emails);
		}
	}
	return $commentobj;
}

/**
 * Populates and returns the $_zp_admin_album_list array
 * @return array
 */
function getManagedAlbumList() {
	global $_zp_admin_album_list, $_zp_current_admin_obj;
	$_zp_admin_album_list = array();
	if (zp_loggedin(MANAGE_ALL_ALBUM_RIGHTS)) {
		$sql = "SELECT `folder` FROM ".prefix('albums').' WHERE `parentid` IS NULL';
		$albums = query_full_array($sql);
		foreach($albums as $album) {
			$_zp_admin_album_list[$album['folder']] = 32767;
		}
	} else {
		$sql = 'SELECT '.prefix('albums').'.`folder`,'.prefix('admin_to_object').'.`edit` FROM '.prefix('albums').', '.
						prefix('admin_to_object').' WHERE '.prefix('admin_to_object').'.adminid='.
						$_zp_current_admin_obj->getID().' AND '.prefix('albums').'.id='.prefix('admin_to_object').'.objectid AND `type`="album"';
		$albums = query_full_array($sql);
		foreach($albums as $album) {
			$_zp_admin_album_list[$album['folder']] = $album['edit'];
		}
	}
	return array_keys($_zp_admin_album_list);
}

/**
 * Returns  an array of album ids whose parent is the folder
 * @param string $albumfolder folder name if you want a album different >>from the current album
 * @return array
 */
function getAllSubAlbumIDs($albumfolder='') {
	global $_zp_current_album;
	if (empty($albumfolder)) {
		if (isset($_zp_current_album)) {
			$albumfolder = $_zp_current_album->getFolder();
		} else {
			return null;
		}
	}
	$query = "SELECT `id`,`folder`, `show` FROM " . prefix('albums') . " WHERE `folder` LIKE '" . zp_escape_string($albumfolder) . "%'";
	$subIDs = query_full_array($query);
	return $subIDs;
}

/**
 * recovers search parameters from stored cookie, clears the cookie
 *
 * @param string $what the page type
 * @param string $album Name of the album
 * @param string $image Name of the image
 */
function handleSearchParms($what, $album=NULL, $image=NULL) {
	global $_zp_current_search, $zp_request, $_zp_last_album, $_zp_search_album_list, $_zp_current_album,
					$_zp_current_zenpage_news, $_zp_current_zenpage_page, $_zp_gallery;
	$_zp_last_album = zp_getCookie('zenphoto_last_album');
	if (is_null($album)) {
		if (is_object($zp_request)) {
			$reset = get_class($zp_request) != 'SearchEngine';
		} else {
			$reset = $zp_request;
		}
		if ($reset) { // clear the cookie if no album and not a search
			if (!isset($_REQUEST['preserve_serch_params'])) {
				zp_setcookie("zenphoto_search_params", "", time()-368000);
			}
			return;
		}
	}
	$context = get_context();
	$params = zp_getCookie('zenphoto_search_params');
	if (!empty($params)) {
		$_zp_current_search = new SearchEngine();
		$_zp_current_search->setSearchParams($params);
		// check to see if we are still "in the search context"
		if (!is_null($image)) {
			if ($_zp_current_search->getImageIndex($album->name, $image->filename) !== false) {
				$dynamic_album = $_zp_current_search->dynalbumname;
				if (!empty($dynamic_album)) {
					$_zp_current_album = new Album($_zp_gallery, $dynamic_album);
				}
				$context = $context | ZP_SEARCH_LINKED | ZP_IMAGE_LINKED;
			}
		}
		if (!is_null($album)) {
			$albumname = $album->name;
			zp_setCookie('zenphoto_last_album', $albumname);
			if (hasDynamicAlbumSuffix($albumname)) $albumname = substr($albumname, 0, -4); // strip off the .alb as it will not be reflected in the search path
			$_zp_search_album_list = $_zp_current_search->getAlbums(0);
			foreach ($_zp_search_album_list as $searchalbum) {
				if (strpos($albumname, $searchalbum) !== false) {
					$context = $context | ZP_SEARCH_LINKED | ZP_ALBUM_LINKED;
					break;
				}
			}
		} else {
			zp_setCookie('zenphoto_last_album', '', time()-368000);
		}
		/*
		while all this should work, currently there is no "memory" of zenpage search strings.
		maybe that should change, but not just now as it is pretty complex to figure out when
		to clear the cookie.
		*/
		if (!is_null($_zp_current_zenpage_page)) {
			$pages = $_zp_current_search->getSearchPages();
			if (!empty($pages)) {
				$tltlelink = $_zp_current_zenpage_page->getTitlelink();
				foreach ($pages as $apage) {
					if ($apage['titlelink']==$tltlelink) {
						$context = $context | ZP_SEARCH_LINKED;
						break;
					}
				}
			}
		}
		if (!is_null($_zp_current_zenpage_news)) {
			$news = $_zp_current_search->getSearchNews();
			if (!empty($news)) {
				$tltlelink = $_zp_current_zenpage_news->getTitlelink();
				foreach ($news as $anews) {
					if ($anews['titlelink']==$tltlelink) {
						$context = $context | ZP_SEARCH_LINKED;
						break;
					}
				}
			}
		}
		if (($context & ZP_SEARCH_LINKED)) {
			set_context($context);
		} else { // not an object in the current search path
			$_zp_current_search = null;
			zp_setcookie("zenphoto_search_params", "", time()-368000);
		}
	}
}

/**
 * Returns the number of album thumbs that go on a gallery page
 *
 * @return int
 */
function galleryAlbumsPerPage() {
	return max(1, getOption('albums_per_page'));
}

/**
 * Returns the theme folder
 * If there is an album theme, loads the theme options.
 *
 * @return string
 */
function setupTheme() {
	global $_zp_gallery, $_zp_current_album, $_zp_current_search, $_zp_options, $_zp_themeroot;
	if (!is_object($_zp_gallery)) $_zp_gallery = new Gallery();
	$albumtheme = '';
	if (in_context(ZP_SEARCH_LINKED)) {
		$name = $_zp_current_search->dynalbumname;
		if (!empty($name)) {
			$album = new Album($_zp_gallery, $name);
		} else {
			$album = NULL;
		}
	} else {
		$album = $_zp_current_album;
	}
	$theme = $_zp_gallery->getCurrentTheme();
	$id = 0;
	if (!is_null($album)) {
		$parent = getUrAlbum($album);
		$albumtheme = $parent->getAlbumTheme();
		if (!empty($albumtheme)) {
			$theme = $albumtheme;
			$id = $parent->id;
		}
	}
	$theme = zp_apply_filter('setupTheme', $theme);
	$themeindex = getPlugin('index.php', $theme);
	if (empty($theme) || empty($themeindex)) {
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s').' GMT');
		header('Content-Type: text/html; charset=' . getOption('charset'));
		?>
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
		</head>
		<body>
			<strong><?php printf(gettext('Zenphoto found no theme scripts. Please check the <em>%s</em> folder of your installation.'),THEMEFOLDER); ?></strong>
		</body>
		</html>
		<?php
		exit();
	} else {
		$requirePath = getPlugin('themeoptions.php', $theme);
		if (!empty($requirePath)) {
			require_once($requirePath);
			$optionHandler = new ThemeOptions(); /* prime the default theme options */
		}
		loadLocalOptions($id,$theme);
		$_zp_themeroot = WEBPATH . "/".THEMEFOLDER."/$theme";
	}
	return $theme;
}

/**
 * Allows plugins to add to the scripts output by zenJavascript()
 *
 * @param string $script the text to be added.
 */
function addPluginScript($script) {
	global $_zp_plugin_scripts;
	$_zp_plugin_scripts[] = $script;
}


/**
 * Registers a plugin as handler for a file extension
 *
 * @param string $suffix the file extension
 * @param string $objectName the name of the object that handles this extension
 */
function addPluginType($suffix, $objectName) {
	global $_zp_extra_filetypes;
	$_zp_extra_filetypes[strtolower($suffix)] = $objectName;
}

/**
 * Returns true if the file is handled by a plugin object
 *
 * @param string $filename
 * @return bool
 */
function is_valid_other_type($filename) {
	global $_zp_extra_filetypes;
	$ext = strtolower(substr(strrchr($filename, "."), 1));
	if (array_key_exists($ext, $_zp_extra_filetypes)) {
		return $ext;
	} else {
		return false;
	}
}

/**
 * Returns an array of unique tag names
 *
 * @return unknown
 */
function getAllTagsUnique() {
	global $_zp_unique_tags;
	if (!is_null($_zp_unique_tags)) return $_zp_unique_tags;  // cache them.
	$sql = "SELECT `name` FROM ".prefix('tags').' ORDER BY `name`';
	$result = query_full_array($sql);
	if (is_array($result)) {
		$_zp_unique_tags = array();
		foreach ($result as $row) {
			$_zp_unique_tags[] = $row['name'];
		}
		return $_zp_unique_tags;
	} else {
		return array();
	}
}

/**
 * Returns an array indexed by 'tag' with the element value the count of the tag
 *
 * @return array
 */
function getAllTagsCount() {
	global $_zp_count_tags;
	if (!is_null($_zp_count_tags)) return $_zp_count_tags;
	$_zp_count_tags = array();
	$sql = "SELECT `name`, `id` from ".prefix('tags').' ORDER BY `name`';
	$tagresult = query_full_array($sql);
	if (is_array($tagresult)) {
		$sql = 'SELECT `tagid`, `objectid` FROM '.prefix('obj_to_tag').' ORDER BY `tagid`';
		$countresult = query_full_array($sql);
		if (is_array($countresult)) {
			$id = 0;
			$tagcounts = array();
			foreach ($countresult as $row) {
				if ($id != $row['tagid']) {
					$tagcounts[$id = $row['tagid']] = 0;
				}
				$tagcounts[$id] ++;
			}
			foreach ($tagresult as $row) {
				if (isset($tagcounts[$row['id']])) {
					$_zp_count_tags[$row['name']] = $tagcounts[$row['id']];
				} else {
					$_zp_count_tags[$row['name']] = 0;
				}
			}
		} else {
			foreach ($tagresult as $tag) {
				$_zp_count_tags[$tag] = 0;
			}
		}
	}
	return $_zp_count_tags;
}

/**
 * Stores tags for an album/image
 *
 * @param array $tags the tag values
 * @param int $id the record id of the album/image
 * @param string $tbl 'albums' or 'images'
 */
function storeTags($tags, $id, $tbl) {
	global $_zp_UTF8;
	$tagsLC = array();
	foreach ($tags as $key=>$tag) {
		$tag = trim($tag);
		if (!empty($tag)) {
			$lc_tag = $_zp_UTF8->strtolower($tag);
			if (!in_array($lc_tag, $tagsLC)) {
				$tagsLC[] = $lc_tag;
			}
		}
	}
	$sql = "SELECT `id`, `tagid` from ".prefix('obj_to_tag')." WHERE `objectid`='".$id."' AND `type`='".$tbl."'";
	$result = query_full_array($sql);
	$existing = array();
	if (is_array($result)) {
		foreach ($result as $row) {
			$dbtag = query_single_row("SELECT `name` FROM ".prefix('tags')." WHERE `id`='".$row['tagid']."'");
			$existingLC = $_zp_UTF8->strtolower($dbtag['name']);
			if (in_array($existingLC, $tagsLC)) { // tag already set no action needed
				$existing[] = $existingLC;
			} else { // tag no longer set, remove it
				query("DELETE FROM ".prefix('obj_to_tag')." WHERE `id`='".$row['id']."'");
			}
		}
	}
	$tags = array_diff($tagsLC, $existing); // new tags for the object
	foreach ($tags as $tag) {
		$dbtag = query_single_row("SELECT `id` FROM ".prefix('tags')." WHERE `name`='".zp_escape_string($tag)."'");
		if (!is_array($dbtag)) { // tag does not exist
			query("INSERT INTO " . prefix('tags') . " (name) VALUES ('" . zp_escape_string($tag) . "')",true);
			$dbtag = array('id' => db_insert_id());
		}
		query("INSERT INTO ".prefix('obj_to_tag'). "(`objectid`, `tagid`, `type`) VALUES (".$id.",".$dbtag['id'].",'".$tbl."')");
	}
}

/**
 * Retrieves the tags for an album/image
 * Returns them in an array
 *
 * @param int $id the record id of the album/image
 * @param string $tbl 'albums' or 'images'
 * @return unknown
 */
function readTags($id, $tbl) {
	$tags = array();
	$result = query_full_array("SELECT `tagid` FROM ".prefix('obj_to_tag')." WHERE `type`='".$tbl."' AND `objectid`='".$id."'");
	if (is_array($result)) {
		foreach ($result as $row) {
			$dbtag = query_single_row("SELECT `name` FROM".prefix('tags')." WHERE `id`='".$row['tagid']."'");
			$tags[] = $dbtag['name'];
		}
	}
	natcasesort($tags);
	return $tags;
}

/**
 * Creates the body of a select list
 *
 * @param array $currentValue list of items to be flagged as checked
 * @param array $list the elements of the select list
 * @param bool $descending set true for a reverse order sort
 */
function generateListFromArray($currentValue, $list, $descending, $localize) {
	if ($localize) {
		$list = array_flip($list);
		if ($descending) {
			arsort($list);
		} else {
			natcasesort($list);
		}
		$list = array_flip($list);
	} else {
		if ($descending) {
			rsort($list);
		} else {
			natcasesort($list);
		}
	}
	foreach($list as $key=>$item) {
		echo '<option value="' . htmlentities($item,ENT_QUOTES,getOption("charset")) . '"';
		if (in_array($item, $currentValue)) {
			echo ' selected="selected"';
		}
		if ($localize) $display = $key; else $display = $item;
		echo '>' . $display . "</option>"."\n";
	}
}

/**
 * Generates a selection list from files found on disk
 *
 * @param strig $currentValue the current value of the selector
 * @param string $root directory path to search
 * @param string $suffix suffix to select for
 * @param bool $descending set true to get a reverse order sort
 */
function generateListFromFiles($currentValue, $root, $suffix, $descending=false) {
	$curdir = getcwd();
	chdir($root);
	$filelist = safe_glob('*'.$suffix);
	$list = array();
	foreach($filelist as $file) {
		$file = str_replace($suffix, '', $file);
		$list[] = filesystemToInternal($file);
	}
	generateListFromArray(array($currentValue), $list, $descending, false);
	chdir($curdir);
}

/**
 * General link printing function
 * @param string $url The link URL
 * @param string $text The text to go with the link
 * @param string $title Text for the title tag
 * @param string $class optional class
 * @param string $id optional id
 */
function printLink($url, $text, $title=NULL, $class=NULL, $id=NULL) {
	echo "<a href=\"" . htmlspecialchars($url,ENT_QUOTES) . "\"" .
	(($title) ? " title=\"" . html_encode($title) . "\"" : "") .
	(($class) ? " class=\"$class\"" : "") .
	(($id) ? " id=\"$id\"" : "") . ">" .
	$text . "</a>";
}

/**
 * multidimensional array column sort
 *
 * @param array $array The multidimensional array to be sorted
 * @param string $index Which key should be sorted by
 * @param string $order true for descending sorts
 * @param bool $natsort If natural order should be used
 * @param bool $case_sensitive If the sort should be case sensitive
 * @return array
 *
 * @author redoc (http://codingforums.com/showthread.php?t=71904)
 */
function sortMultiArray($array, $index, $descending=false, $natsort=true, $case_sensitive=false) {
	if(is_array($array) && count($array)>0) {
		foreach ($array as $key=>$row) {
			if (is_array($row) && array_key_exists($index, $row)) {
				$temp[$key]=$row[$index];
			} else {
				$temp[$key] = '';
			}
		}
		if($natsort) {
			if ($case_sensitive) {
				natsort($temp);
			} else {
				natcasesort($temp);
			}
			if($descending)  {
				$temp=array_reverse($temp,TRUE);
			}
		} else {
			if ($descending) {
				arsort($temp);
			} else {
				asort($temp);
			}
		}
		foreach(array_keys($temp) as $key) {
			if(is_numeric($key)) {
				$sorted[]=$array[$key];
			} else {
				$sorted[$key]=$array[$key];
			}
		}
		return $sorted;
	}
	return $array;
}

/**
 * Returns a list of album IDs that the current viewer is allowed to see
 *
 * @return array
 */
function getNotViewableAlbums() {
	if (zp_loggedin(ADMIN_RIGHTS | MANAGE_ALL_ALBUM_RIGHTS)) return array(); //admins can see all
	$hint = '';
	global $_zp_not_viewable_album_list;
	if (is_null($_zp_not_viewable_album_list)) {
		$sql = 'SELECT `folder`, `id`, `password`, `show` FROM '.prefix('albums').' WHERE `show`=0 OR `password`!=""';
		$result = query_full_array($sql);
		if (is_array($result)) {
			$_zp_not_viewable_album_list = array();
			foreach ($result as $row) {
				if (checkAlbumPassword($row['folder'], $hint)) {
					if (!($row['show'] || isMyAlbum($row['folder'], LIST_ALBUM_RIGHTS))) {
						$_zp_not_viewable_album_list[] = $row['id'];
					}
				} else {
					$_zp_not_viewable_album_list[] = $row['id'];
				}
			}
		}
	}
	return $_zp_not_viewable_album_list;
}

/**
 * Parses and sanitizes Theme definition text
 *
 * @param file $file theme file
 * @return string
 */
function parseThemeDef($file) {
	$file = internalToFilesystem($file);
	$themeinfo = array('name'=>gettext('Unknown name'), 'author'=>gettext('Unknown author'), 'version'=>gettext('Unknown'), 'desc'=>gettext('<strong>Theme description error!</strong>'), 'date'=>gettext('Unknown date'));
	if (is_readable($file) && $fp = @fopen($file, "r")) {
		while($line = trim(fgets($fp))) {
			if (!empty($line) && substr($line, 0, 1) != "#") {
				$item = explode("::", $line);
				if (count($item)>1) {
					$v = sanitize(trim($item[1]), 1);
				} else {
					$v = gettext('<strong>Theme description error!</strong>');
				}
				$i = sanitize(trim($item[0]), 1);
				$themeinfo[$i] = $v;
			}
		}
		return $themeinfo;
	} else {
		return false;
	}
}

/**
 * Emits a page error. Used for attempts to bypass password protection
 *
 * @param string $err error code
 * @param string $text error message
 *
 */
function pageError($err,$text) {
	header("HTTP/1.0 ".$err.' '.$text);
	header("Status: ".$err.' '.$text);
	echo "<html xmlns=\"http://www.w3.org/1999/xhtml\"><head>	<title>".$err." - ".$text."</TITLE>	<META NAME=\"ROBOTS\" CONTENT=\"NOINDEX, FOLLOW\"></head>";
	echo "<BODY bgcolor=\"#ffffff\" text=\"#000000\" link=\"#0000ff\" vlink=\"#0000ff\" alink=\"#0000ff\">";
	echo "<FONT face=\"Helvitica,Arial,Sans-serif\" size=\"2\">";
	echo "<b>".sprintf(gettext('Page error: %2$s (%1$s)'),$err, $text)."</b><br /><br />";
	echo "</body></html>";
}

/**
 * Checks to see if a URL is valid
 *
 * @param string $url the URL being checked
 * @return bool
 */
function isValidURL($url) {
	return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
}

/**
 * Provide an alternative to glob which does not return filenames with accented charactes in them
 *
 * @param string $pattern the 'pattern' for matching files
 * @param bit $flags glob 'flags'
 */
function safe_glob($pattern, $flags=0) {
	$split=explode('/',$pattern);
	$match=array_pop($split);
	$path_return = $path = implode('/',$split);
	if (empty($path)) {
		$path = '.';
	} else {
		$path_return = $path_return . '/';
	}
	if (!is_dir($path)) return array();
	if (($dir=opendir($path))!==false) {
		$glob=array();
		while(($file=readdir($dir))!==false) {
			if (safe_fnmatch($match,$file)) {
				if ((is_dir("$path/$file"))||(!($flags&GLOB_ONLYDIR))) {
					if ($flags&GLOB_MARK) $file.='/';
					$glob[]=$path_return.$file;
				}
			}
		}
		closedir($dir);
		if (!($flags&GLOB_NOSORT)) sort($glob);
		return $glob;
	} else {
		return array();
	}
}

/**
 * pattern match function Works with accented characters where the PHP one does not.
 *
 * @param string $pattern pattern
 * @param string $string haystack
 * @return bool
 */
function safe_fnmatch($pattern, $string) {
	return @preg_match('/^' . strtr(addcslashes($pattern, '\\.+^$(){}=!<>|'), array('*' => '.*', '?' => '.?')) . '$/i', $string);
}

/**
 * Returns the value of a cookie from either the cookies or from $_SESSION[]
 *
 * @param string $name the name of the cookie
 */
function zp_getCookie($name) {
	if (DEBUG_LOGIN) {
		if (isset($_SESSION[$name])) {
			$sessionv = $_SESSION[$name];
		} else {
			$sessionv = '';
		}
		if (isset($_COOKIE[$name])) {
			$cookiev = $_COOKIE[$name];
		} else {
			$cookiev = '';
		}
		debugLog("zp_getCookie($name)::".'album_session='.getOption('album_session')."; SESSION[".session_id()."]=".$sessionv.", COOKIE=".$cookiev);
	}
	if (isset($_COOKIE[$name]) && !empty($_COOKIE[$name]) && !getOption('album_session')) {
		return $_COOKIE[$name];
	}
	if (isset($_SESSION[$name])) {
		return $_SESSION[$name];
	}
	return false;
}

/**
 * Sets a cookie both in the browser cookies and in $_SESSION[]
 *
 * @param string $name The 'cookie' name
 * @param string $value The value to be stored
 * @param timestamp $time The time the cookie expires
 * @param string $path The path on the server in which the cookie will be available on
 */
function zp_setCookie($name, $value, $time=NULL, $path=NULL, $secure=false) {
	if (DEBUG_LOGIN) debugLog("zp_setCookie($name, $value, $time, $path)::album_session=".getOption('album_session'));
	if (is_null($time)) $time = time()+COOKIE_PESISTENCE;
	if (is_null($path)) if (($path = WEBPATH) == '') $path = '/';
	if ($time < time() || !getOption('album_session')) {
		setcookie($name, $value, $time, $path, "", $secure);
	}
	if ($time < time()) {
		if (isset($_SESSION))	unset($_SESSION[$name]);
		if (isset($_COOKIE)) unset($_COOKIE[$name]);
	} else {
		$_SESSION[$name] = $value;
		$_COOKIE[$name] = $value;
	}
}

/**
 * returns a list of comment record 'types' for "images"
 * @param string $quote quotation mark to use
 *
 * @return string
 */
function zp_image_types($quote) {
	global $_zp_extra_filetypes;
	$typelist = $quote.'images'.$quote.','.$quote.'_images'.$quote.',';
	$types = array_unique($_zp_extra_filetypes);
	foreach ($types as $type) {
		$typelist .= $quote.strtolower($type).'s'.$quote.',';
	}
	return substr($typelist, 0, -1);
}
/**

 * Returns video argument of the current Image.
 *
 * @param object $image optional image object
 * @return bool
 */
function isImageVideo($image=NULL) {
	if (is_null($image)) {
		if (!in_context(ZP_IMAGE)) return false;
		global $_zp_current_image;
		$image = $_zp_current_image;
	}
	return strtolower(get_class($image)) == 'video';
}

/**
 * Returns true if the image is a standard photo type
 *
 * @param object $image optional image object
 * @return bool
 */
function isImagePhoto($image=NULL) {
	if (is_null($image)) {
		if (!in_context(ZP_IMAGE)) return false;
		global $_zp_current_image;
		$image = $_zp_current_image;
	}
	$class = strtolower(get_class($image));
	return $class == '_image' || $class == 'transientimage';
}

/**
 * Copies a directory recursively
 * @param string $srcdir the source directory.
 * @param string $dstdir the destination directory.
 * @return the total number of files copied.
 */
function dircopy($srcdir, $dstdir) {
	$num = 0;
	if(!is_dir($dstdir)) mkdir($dstdir);
	if($curdir = opendir($srcdir)) {
		while($file = readdir($curdir)) {
			if($file != '.' && $file != '..') {
				$srcfile = $srcdir . '/' . $file;
				$dstfile = $dstdir . '/' . $file;
				if(is_file($srcfile)) {
					if(is_file($dstfile)) $ow = filemtime($srcfile) - filemtime($dstfile); else $ow = 1;
					if($ow > 0) {
						if(copy($srcfile, $dstfile)) {
							touch($dstfile, filemtime($srcfile)); $num++;
						}
					}
				}
				else if(is_dir($srcfile)) {
					$num += dircopy($srcfile, $dstfile);
				}
			}
		}
		closedir($curdir);
	}
	return $num;
}

/**
 * Returns a byte size from a size value (eg: 100M).
 *
 * @param int $bytes
 * @return string
 */
function byteConvert( $bytes ) {

	if ($bytes<=0)
	return '0 Byte';

	$convention=1024; //[1000->10^x|1024->2^x]
	$s=array('Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB');
	$e=floor(log($bytes,$convention));
	return round($bytes/pow($convention,$e),2).' '.$s[$e];
}

/**
 * Returns an i.php "image name" for an image not within the albums structure
 *
 * @param string $image Path to the image
 * @return string
 */
function makeSpecialImageName($image) {
	$filename = basename($image);
	$i = strpos($image, ZENFOLDER);
	if ($i === false) {
		$folder = '_{'.basename(dirname(dirname($image))).'}_{'.basename(dirname($image)).'}_';
	} else {
		$folder = '_{'.ZENFOLDER.'}_{'.substr($image, $i + strlen(ZENFOLDER) + 1 , - strlen($filename) - 1).'}_';
	}
	return $folder.$filename;
}

/**
 * Converts a datetime to connoical form
 *
 * @param string $datetime input date/time string
 * @param bool $raw set to true to return the timestamp otherwise you get a string
 * @return mixed
 */
function dateTimeConvert($datetime, $raw=false) {
	// Convert 'yyyy:mm:dd hh:mm:ss' to 'yyyy-mm-dd hh:mm:ss' for Windows' strtotime compatibility
	$datetime = preg_replace('/(\d{4}):(\d{2}):(\d{2})/', ' \1-\2-\3', $datetime);
	$time = strtotime($datetime);
	if ($time == -1 || $time === false) return false;
	if ($raw) return $time;
	return date('Y-m-d H:i:s', $time);
}

/*** Context Manipulation Functions *******/
/******************************************/

/* Contexts are simply constants that tell us what variables are available to us
 * at any given time. They should be set and unset with those variables.
 */

if(getOption('zp_plugin_zenpage')) {
	require_once(dirname(__FILE__).'/'.PLUGIN_FOLDER.'/zenpage/zenpage-template-functions.php');
}

function get_context() {
	global $_zp_current_context;
	return $_zp_current_context;
}
function set_context($context) {
	global $_zp_current_context;
	$_zp_current_context = $context;
}
function in_context($context) {
	return get_context() & $context;
}
function add_context($context) {
	set_context(get_context() | $context);
}
function rem_context($context) {
	global $_zp_current_context;
	set_context(get_context() & ~$context);
}
// Use save and restore rather than add/remove when modifying contexts.
function save_context() {
	global $_zp_current_context, $_zp_current_context_stack;
	array_push($_zp_current_context_stack,$_zp_current_context);
}
function restore_context() {
	global $_zp_current_context, $_zp_current_context_stack;
	$_zp_current_context = array_pop($_zp_current_context_stack);
}

/**
 * Logs a time into the debug log
 *
 * @param string $tag log annotation
 */
function logTime($tag) {
	 $mtime = microtime();
	 $mtime = explode(" ",$mtime);
	 $mtime = $mtime[1] + $mtime[0];
	 $time = $mtime;
	 debugLog($tag.' '.$time);
}

/**
 * checks password posting
 *
 * @param string $authType override of athorization type
 */
function zp_handle_password($authType=NULL, $check_auth=NULL, $check_user=NULL) {
	global $_zp_loggedin, $_zp_login_error, $_zp_current_album, $_zp_authority, $_zp_current_zenpage_page;
	if (empty($authType)) { // not supplied by caller
		$check_auth = '';
		if (isset($_GET['z']) && $_GET['p'] == 'full-image' || isset($_GET['p']) && $_GET['p'] == '*full-image') {
			$authType = 'zp_image_auth';
			$check_auth = getOption('protected_image_password');
			$check_user = getOption('protected_image_user');
		} else if (in_context(ZP_SEARCH)) {  // search page
			$authType = 'zp_search_auth';
			$check_auth = getOption('search_password');
			$check_user = getOption('search_user');
		} else if (in_context(ZP_ALBUM)) { // album page
			$authType = "zp_album_auth_" . $_zp_current_album->get('id');
			$check_auth = $_zp_current_album->getPassword();
			$check_user = $_zp_current_album->getUser();
			if (empty($check_auth)) {
				$parent = $_zp_current_album->getParent();
				while (!is_null($parent)) {
					$check_auth = $parent->getPassword();
					$check_user = $parent->getUser();
					$authType = "zp_album_auth_" . $parent->get('id');
					if (!empty($check_auth)) { break; }
					$parent = $parent->getParent();
				}
			}
		} else if (in_context(ZP_ZENPAGE_PAGE)) {
			$authType = "zp_page_auth_" . $_zp_current_zenpage_page->get('id');
			$check_auth = $_zp_current_zenpage_page->getPassword();
			$check_user = $_zp_current_zenpage_page->getUser();
			if (empty($check_auth)) {
				$pageobj = $_zp_current_zenpage_page;
				while(empty($check_auth)) {
					$parentID = $pageobj->getParentID();
					if ($parentID == 0) break;
					$sql = 'SELECT `titlelink` FROM '.prefix('zenpage_pages').' WHERE `id`='.$parentID;
					$result = query_single_row($sql);
					$pageobj = new ZenpagePage($result['titlelink']);
					$authType = "zp_page_auth_" . $pageobj->get('id');
					$check_auth = $pageobj->getPassword();
					$check_user = $pageobj->getUser();
				}
			}
		}
		if (empty($check_auth)) { // anything else is controlled by the gallery credentials
			$authType = 'zp_gallery_auth';
			$check_auth = getOption('gallery_password');
			$check_user = getOption('gallery_user');
		}
	}
	// Handle the login form.
	if (DEBUG_LOGIN) debugLog("zp_handle_password: \$authType=$authType; \$check_auth=$check_auth; \$check_user=$check_user; ");
	if (isset($_POST['password']) && isset($_POST['pass'])) {	// process login form
		if (isset($_POST['user'])) {
			$post_user = $_POST['user'];
		} else {
			$post_user = '';
		}
		$post_pass = $_POST['pass'];
		$auth = $_zp_authority->passwordHash($post_user, $post_pass);
		if (DEBUG_LOGIN) debugLog("zp_handle_password: \$post_user=$post_user; \$post_pass=$post_pass; \$auth=$auth; ");
		$_zp_loggedin = $_zp_authority->checkLogon($post_user, $post_pass, false);

		$redirect_to = $_POST['redirect'];
		if (substr($redirect_to,0,1)=='/') {
			$initial = '/';
		} else {
			$initial = '';
		}
		$redirect_to = $initial.sanitize_path($_POST['redirect']);
		if (strpos($redirect_to, WEBPATH.'/')===0) {
			$redirect_to = substr($redirect_to,strlen(WEBPATH)+1);
		}
		if ($_zp_loggedin) $_zp_loggedin = zp_apply_filter('guest_login_attempt', $_zp_loggedin, $post_user, $post_pass, 'zp_admin_auth');
		if ($_zp_loggedin) {	// allow Admin user login
			// https: set the 'zenphoto_ssl' marker for redirection
			if(secureServer()) {
				zp_setcookie("zenphoto_ssl", "needed");
			}
			// set cookie as secure when in https
			zp_setcookie("zenphoto_auth", $auth, NULL, NULL, secureServer());
			if (isset($_POST['redirect']) && !empty($_POST['redirect'])) {
				header("Location: " . FULLWEBPATH . "/" . $redirect_to);
				exit();
			}
		} else {
			$success = ($auth == $check_auth) && $post_user == $check_user;
			$success = zp_apply_filter('guest_login_attempt', $success, $post_user, $post_pass, $authType);;
			if ($success) {
				// Correct auth info. Set the cookie.
				if (DEBUG_LOGIN) debugLog("zp_handle_password: valid credentials");
				zp_setcookie($authType, $auth);
				if (isset($_POST['redirect']) && !empty($_POST['redirect'])) {
					header("Location: " . FULLWEBPATH . "/" . $redirect_to);
					exit();
				}
			} else {
				// Clear the cookie, just in case
				if (DEBUG_LOGIN) debugLog("zp_handle_password: invalid credentials");
				zp_setcookie($authType, "", time()-368000);
				$_zp_login_error = true;
			}
		}
		return;
	}
	if (empty($check_auth) || zp_loggedin()) { //no password on record or admin logged in
		return;
	}
	if (($saved_auth = zp_getCookie($authType)) != '') {
		if ($saved_auth == $check_auth) {
			if (DEBUG_LOGIN) debugLog("zp_handle_password: valid cookie");
			return;
		} else {
			// Clear the cookie
			if (DEBUG_LOGIN) debugLog("zp_handle_password: invalid cookie");
			zp_setcookie($authType, "", time()-368000);
		}
	}
}

/**
 * Set options local to theme and/or album
 *
 * @param string $key
 * @param string $value
 * @param object $album
 * @param string $theme default theme
 * @param bool $default set to true for setting default theme options (does not set the option if it already exists)
 */
function setThemeOption($key, $value, $album=NULL, $theme, $default=false) {
	global $gallery;
	if (is_null($album)) {
		$id = 0;
	} else {
		$id = $album->id;
		$theme = $album->getAlbumTheme();
	}
	if (empty($theme)) {
		$theme = $gallery->getCurrentTheme();
	}
	$theme = "'".zp_escape_string($theme)."'";

	$exists = query_single_row("SELECT `name`, `value`, `id` FROM ".prefix('options')." WHERE `name`='".zp_escape_string($key)."' AND `ownerid`=".$id.' AND `theme`='.$theme, true);
	if ($exists) {
		if ($default) return; // don't update if setting the default
		if (is_null($value)) {
			$sql = "UPDATE " . prefix('options') . " SET `value`=NULL WHERE `id`=" . $exists['id'];
		} else {
			$sql = "UPDATE " . prefix('options') . " SET `value`='" . zp_escape_string($value) . "' WHERE `id`=" . $exists['id'];
		}
	} else {
		if (is_null($value)) {
			$sql = "INSERT INTO " . prefix('options') . " (name, value, ownerid, theme) VALUES ('" . zp_escape_string($key) . "',NULL,$id,$theme)";
		} else {
			$sql = "INSERT INTO " . prefix('options') . " (name, value, ownerid, theme) VALUES ('" . zp_escape_string($key) . "','" . zp_escape_string($value) . "',$id,$theme)";
		}
	}
	$result = query($sql);
}

/**
 * Used to set default values for theme specific options
 *
 * @param string $key
 * @param mixed $value
 */
function setThemeOptionDefault($key, $value) {
	$bt = @debug_backtrace();
	if (is_array($bt)) {
		$b = array_shift($bt);
		$theme = basename(dirname($b['file']));
		setThemeOption($key, $value, NULL, $theme, true);
	} else {
		setOptionDefault($key, $value); // can't determine the theme.
	}
}

/**
 * Sets value for a boolena theme option
 * insures that the value is either zero or one
 *
 * @param string $key Option key
 * @param bool $bool value to be set
 * @param object $album album object
 * @param string $theme default theme name
 */
function setBoolThemeOption($key, $bool, $album=NULL, $theme=false) {
	if ($bool) {
		$value = 1;
	} else {
		$value = 0;
	}
	setThemeOption($key, $value, $album, $theme);
}

/**
 * Returns the value of a theme option
 *
 * @param string $option option key
 * @param object $album
 * @param string $theme default theme name
 * @return mixed
 */
function getThemeOption($option, $album=NULL, $theme=false) {
	global $gallery;
	if (is_null($album)) {
		$id = 0;
	} else {
		$id = $album->id;
		$theme = $album->getAlbumTheme();
	}
	if (empty($theme)) {
		$theme = $gallery->getCurrentTheme();
	}
	$theme = "'".zp_escape_string($theme)."'";

	// album-theme
	$sql = "SELECT `value` FROM " . prefix('options') . " WHERE `name`='" . zp_escape_string($option) . "' AND `ownerid`=".$id." AND `theme`=".$theme;
	$db = query_single_row($sql);
	if (!$db) {
		// raw theme option
		$sql = "SELECT `value` FROM " . prefix('options') . " WHERE `name`='" . zp_escape_string($option) . "' AND `ownerid`=0 AND `theme`=".$theme;
		$db = query_single_row($sql);
		if (!$db) {
			// raw album option
			$sql = "SELECT `value` FROM " . prefix('options') . " WHERE `name`='" . zp_escape_string($option) . "' AND `ownerid`=".$id." AND `theme`=NULL";
			$db = query_single_row($sql);
			if (!$db) {
				return getOption($option);
			}
		}
	}
	return $db['value'];
}

/**
 * Returns true if all the right conditions are set to allow comments for the $type
 *
 * @param string $type Which comments
 * @return bool
 */
function commentsAllowed($type) {
	return getOption($type) && (!getOption('comment_form_members_only') || zp_loggedin(ADMIN_RIGHTS | POST_COMMENT_RIGHTS));
}

/**
 * Returns the viewer's IP address
 * Deals with transparent proxies
 *
 * @return string
 */
function getUserIP() {
	if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		return sanitize($_SERVER['HTTP_X_FORWARDED_FOR'], 0);
	} else {
		return sanitize($_SERVER['REMOTE_ADDR'], 0);
	}
}

/**
 * Strips out and/or replaces characters from the string that are not "soe" friendly
 *
 * @param string $source
 * @return string
 */
function seoFriendly($source) {
	if (zp_has_filter('seoFriendly')) {
		$string = zp_apply_filter('seoFriendly', $source);
	} else { // no filter, do basic cleanup
		$string = preg_replace("/&([a-zA-Z])(uml|acute|grave|circ|tilde|ring),/","",$source);
		$string = preg_replace("/[^a-zA-Z0-9_.-]/","",$string);
		$string = str_replace(array('---','--'),'-', $string);
	}
	return $string;
}

/**
 * Returns true if there is an internet connection
 *
 * @param string $host optional host name to test
 *
 * @return bool
 */
function is_connected($host = 'www.zenphoto.org') {
	$err_no = $err_str = false;
	$connected = @fsockopen($host, 80, $errno, $errstr, 0.5);
	if ($connected){
		fclose($connected);
		return true;
	}
	return false;
}

/**
 * produce debugging information on 404 errors
 * @param string $album
 * @param string $image
 * @param string $theme
 */
function debug404($album, $image, $theme) {
	if (DEBUG_404) {
		$ignore = array('/favicon.ico');
		$target = $_SERVER['REQUEST_URI'];
		foreach ($ignore as $uri) {
			if ($target == $uri) return;
		}
		trigger_error(sprintf(gettext('Zenphoto processed a 404 error on %s. See the debug log for details.'),$target), E_USER_NOTICE);
		debugLog("404 error: album=$album; image=$image; theme=$theme");
		debugLogArray('$_SERVER', $_SERVER, 0, '');
		debugLogArray('$_REQUEST', $_REQUEST, 0, '');
		debugLog('');
	}
}

/**
 * returns an XSRF token
 * @param striong $action
 */
function getXSRFToken($action) {
	global $_zp_current_admin_obj;
	return md5($action.prefix(getUserIP()).serialize($_zp_current_admin_obj).session_id());
}

/**
 * Emits a "hidden" input for the XSRF token
 * @param string $action
 */
function XSRFToken($action) {
	?>
	<input type="hidden" name="XSRFToken" id="XSRFToken" value="<?php echo getXSRFToken($action); ?>" />
	<?php
}

/**
 * Starts a sechedule script run
 * @param string $script The script file to load
 * @param array $params "POST" parameters
 * @param bool $inline set to true to run the task "in-line". Set false run asynchronously
 */
function cron_starter($script, $params, $inline=false) {
	global $_zp_authority, $_zp_loggedin, $_zp_current_admin_obj, $_zp_null_account;
	$admins = $_zp_authority->getAdministrators();
	$admin = array_shift($admins);
	while (!$admin['valid']) {
		$admin = array_shift($admins);
	}

	if ($inline) {
		$_zp_null_account = NULL;
		$_zp_loggedin = $_zp_authority->checkAuthorization($admin['pass']);
		$_zp_current_admin_obj = $_zp_authority->newAdministrator($admin['user']);
		foreach ($params as $key=>$value) {
			if ($key=='XSRFTag') {
				$key = 'XSRFToken';
				$value = getXSRFToken($value);
			}
			$_POST[$key] = $_GET[$key] = $_REQUEST[$key] = $value;
		}
		require_once($script);
	} else {
		$auth = md5($script.serialize($admin));
		$paramlist = 'link='.$script;
		foreach ($params as $key=>$value) {
			$paramlist .= '&'.$key.'='.$value;
		}
		$paramlist .= '&auth='.$auth;
		?>
		<script type="text/javascript">
		// <!-- <![CDATA[
		$.ajax({
			type: 'POST',
			data: '<?php echo $paramlist; ?>',
			url: '<?php echo WEBPATH.'/'.ZENFOLDER; ?>/cron_runner.php'
		});
		// ]]> -->
		</script>
		<?php
	}
}

//load PHP specific functions
require_once(PHPScript('5.0.0', '_functions.php'));

setexifvars();
?>
