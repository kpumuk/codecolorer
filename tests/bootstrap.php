<?php
/*
CodeColorer WordPress integration test bootstrap
https://kpumuk.info/projects/wordpress-plugins/codecolorer
*/

$_tests_dir = getenv('WP_TESTS_DIR');

if (!$_tests_dir) {
    $_tests_dir = sys_get_temp_dir() . '/wordpress-tests-lib';
}

require dirname(__DIR__) . '/vendor/autoload.php';

if (!defined('WP_TESTS_PHPUNIT_POLYFILLS_PATH')) {
    define('WP_TESTS_PHPUNIT_POLYFILLS_PATH', dirname(__DIR__) . '/vendor/yoast/phpunit-polyfills');
}

require_once $_tests_dir . '/includes/functions.php';

function codecolorer_manually_load_plugin()
{
    require dirname(__DIR__) . '/codecolorer.php';
}

tests_add_filter('muplugins_loaded', 'codecolorer_manually_load_plugin');

require $_tests_dir . '/includes/bootstrap.php';
