<?php

	$_path = "/srv/www/konscript/uru/";
    $_webpath = 'http://localhost/konscript/uru/';

	// DB
	define('DB_DRIVER', 'mysql');
	define('DB_HOSTNAME', 'localhost');
	define('DB_USERNAME', 'root');
	define('DB_PASSWORD', 'root');
	define('DB_DATABASE', '');
	define('DB_PREFIX', '');



// HTTP
define('HTTP_SERVER', $_webpath.'admin/');
define('HTTP_CATALOG', $_webpath);
define('HTTP_IMAGE', $_webpath.'image/');

// HTTPS
define('HTTPS_SERVER', $_webpath.'admin/');
define('HTTPS_IMAGE', $_webpath.'image/');

// DIR
define('DIR_APPLICATION', $_path.'admin/');
define('DIR_SYSTEM', $_path.'system/');
define('DIR_DATABASE', $_path.'system/database/');
define('DIR_LANGUAGE', $_path.'admin/language/');
define('DIR_TEMPLATE', $_path.'admin/view/template/');
define('DIR_CONFIG', $_path.'system/config/');
define('DIR_IMAGE', $_path.'image/');
define('DIR_CACHE', $_path.'system/cache/');
define('DIR_DOWNLOAD', $_path.'download/');
define('DIR_LOGS', $_path.'system/logs/');
define('DIR_CATALOG', $_path.'catalog/');
?>
