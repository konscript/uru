<?php /* PUT NOTHING BEFORE THIS LINE, not even a line break! */
global $_zp_conf_vars;
$conf = array();

/** Do not edit above this line. **/
/**********************************/

/*//////////   zenPHOTO Configuration Variables   ///////////////////////////////
Note: zp-config.php.source is the source file for zp-config.php

For most installations Setup.php will copy zp-config.php.source to zp-config.php and 
make the necessary changes to it. Unless this fails you most likely have no need even 
to read this file.

If for some reason setup.php is not able to edit the zp-config.php file and you cannot
fix the file/folder permissions so that it can then you may have copy the 
zp-config.php.source file to zp-config.php and make changes here manually.

Advanced installations may wish to modify the album folder definitions below. 

Also on some servers zenphoto has problems correctly determining the WEB path and
SERVER path. For these cases you will need to implement the path overrides below.
///////////////////////////////////////////////////////////////////////////////*/

////////////////////////////////////////////////////////////////////////////////
// Database Information 
////////////////////////////////////////////////////////////////////////////////

$conf['mysql_user'] = '';
$conf['mysql_pass'] = '';
$conf['mysql_host'] = 'localhost';
$conf['mysql_database'] = '';

// If you are having problems with displaying some characters you may have to change
// the following:
$conf['UTF-8'] = true;

// If you're sharing the database with other tables, use a prefix to be safe.
$conf['mysql_prefix'] = 'ud_';

////////////////////////////////////////////////////////////////////////////////
// zp-config.php required options
////////////////////////////////////////////////////////////////////////////////

// location of album folder. 
// 'album_folder' is the name of the folder for the zenphoto albums.
// 'album_folder_class' determines how to interpret 'album_folder':
//    'std'         --	the folder must be a simple name. It resides in the root 
//                      of the zenphoto installation.
//    'in_webpath'  --	the folder must be the full path of the album folder from 
//                      the WEB root of the zenphoto installation.
//    'external'    --	the folder must be a full server path to the album folder.
//                      Images within an external folder are not visible to web
//                      browsers, so certain features such as flash players cannot
//                      display them
$conf['album_folder'] = '/albums/';
$conf['album_folder_class'] = 'std';

// Server Protocol
// Change this to "https" if you use an HTTPS server (a "https://..." url)
// Otherwise you should leave it at "http"
// NOTE: If you change this on an already installed configuration you will also have
// to change the gallery configuration server protocal option.
$conf['server_protocol'] = "http";

// File system character set
// Change this if your server filesystem uses a character set other than ISO-8859-1
$conf['FILESYSTEM_CHARSET'] = 'ISO-8859-1';

////////////////////////////////////////////////////////////////////////////////
// Path Overrides
////////////////////////////////////////////////////////////////////////////////
// Uncomment the following two lines ONLY IF YOU'RE HAVING PROBLEMS,
// like "file not found" or "not readable" errors.
// These allow you to override Zenphoto's detection of the correct paths
// on your server, which might work better on some setups.
////////////////////////////////////////////////////////////////////////////////

// define('WEBPATH', '/zenphoto');
// define('SERVERPATH', '/full/server/path/to/zenphoto');

////////////////////////////////////////////////////////////////////////////////
if (!defined('CHMOD_VALUE')) { define('CHMOD_VALUE',0755); }
if (!defined('FILESYSTEM_CHARSET')) { define('FILESYSTEM_CHARSET','ISO-8859-1'); }
/** Do not edit below this line. **/
/**********************************/

$_zp_conf_vars = $conf;
unset($conf);

?>
