<?php
/*
CodeColorer plugin unit tests
https://kpumuk.info/projects/wordpress-plugins/codecolorer
*/

use PHPUnit\Framework\TestCase;

/* WordPress method stubs */
$GLOBALS['wp_options'] = array();
$GLOBALS['wp_actions'] = array();
$GLOBALS['wp_filters'] = array();
$GLOBALS['wp_is_feed'] = false;

function add_option($name, $value) { $GLOBALS['wp_options'][$name] = $value; }
function add_action($name, $func) { array_push($GLOBALS['wp_actions'], $name); }
function add_filter($name, $func, $priority = 0, $arity = 0) { array_push($GLOBALS['wp_filters'], $name); }
function get_option($name) { return $GLOBALS['wp_options'][$name]; }
function plugin_basename($file) { return 'codecolorer'; }
function is_feed() { return $GLOBALS['wp_is_feed']; }
function wp_strip_all_tags($text) {
    $text = preg_replace('@<(script|style)[^>]*?>.*?</\\1>@si', '', $text);
	$text = strip_tags($text);
    return $text;
}

require_once 'codecolorer.php';
