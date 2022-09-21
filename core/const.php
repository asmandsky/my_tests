<?php

//HTTP consts
if (!empty($_SERVER['HTTP_HOST'])) {
	define('HOST', $_SERVER['HTTP_HOST']);
} else {
    define('HOST', 'www.site.ru');
}

define("HTTP_REL_PATH", '');

if (!defined('HTTPS')) {
    define('HTTPS', !empty($_SERVER['HTTP_X_HTTPS']) && $_SERVER['HTTP_X_HTTPS'] === 'true');
}
if (!defined('PROTOCOL')) {
    define('PROTOCOL', 'https');
}
if (!defined('SERVER_PROTOCOL')) {
    define('SERVER_PROTOCOL', PROTOCOL . '://');
}

define("DB_LOG", 0);

if (defined('HOST')) {
    define("SITE", SERVER_PROTOCOL . HOST . HTTP_REL_PATH);
} else {
    define("SITE", '/' . HTTP_REL_PATH);
}

if (empty($_SERVER['DOCUMENT_ROOT'])) {
	$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__);
}

// SERVER consts
define("SERVER_REL_PATH", '');
define("ROOT_DIR", $_SERVER['DOCUMENT_ROOT'] . SERVER_REL_PATH . "/");
define("CLASS_DIR", ROOT_DIR . "classes/");
define("CFG_DIR", ROOT_DIR . "core/");
define("LOGS_DIR", ROOT_DIR . "logs/");
define('MEMCACHED_HOST', 'memcached');
define('MEMCACHED_PORT', 11211);
define('USE_CACHE_TYPE', 2); //0 - use file system, 1 - use memcache, 2 - use soft memcache + protected this (use file system, if need)

