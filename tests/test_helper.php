<?php
/*
CodeColorer plugin unit tests
https://kpumuk.info/projects/wordpress-plugins/codecolorer
*/

use PHPUnit\Framework\TestCase;

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__) . '/');
}

/* WordPress method stubs */
$GLOBALS['wp_options'] = array();
$GLOBALS['wp_actions'] = array();
$GLOBALS['wp_filters'] = array();
$GLOBALS['wp_is_feed'] = false;
$GLOBALS['wp_upload_basedir'] = sys_get_temp_dir() . '/codecolorer-test-uploads';

function add_option($name, $value) { $GLOBALS['wp_options'][$name] = $value; }
function add_action($name, $func) { array_push($GLOBALS['wp_actions'], $name); }
function add_filter($name, $func, $priority = 0, $arity = 0) { array_push($GLOBALS['wp_filters'], $name); }
function get_option($name) { return $GLOBALS['wp_options'][$name]; }
function plugin_basename($file) { return 'codecolorer'; }
function is_feed() { return $GLOBALS['wp_is_feed']; }
function wp_upload_dir() { return array('basedir' => $GLOBALS['wp_upload_basedir']); }
function path_join($base, $path) { return rtrim((string) $base, '/\\') . '/' . ltrim((string) $path, '/\\'); }
function esc_html($text) { return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8'); }
function esc_attr($text) { return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8'); }
function sanitize_key($key) { return preg_replace('/[^a-z0-9_-]/', '', strtolower((string) $key)); }
function sanitize_html_class($class, $fallback = '') {
    $sanitized = preg_replace('|%[a-fA-F0-9][a-fA-F0-9]|', '', (string) $class);
    $sanitized = preg_replace('/[^A-Za-z0-9_-]/', '', $sanitized);
    if (empty($sanitized) && !empty($fallback)) {
        return sanitize_html_class($fallback);
    }
    return $sanitized;
}
function sanitize_textarea_field($text) {
    $text = wp_strip_all_tags((string) $text);
    $text = preg_replace('|%[a-fA-F0-9][a-fA-F0-9]|', '', $text);
    return trim($text);
}
function sanitize_text_field($text) {
    $text = sanitize_textarea_field($text);
    $text = preg_replace('/[\r\n\t ]+/', ' ', $text);
    return trim($text);
}
function wp_strip_all_tags($text) {
    $text = preg_replace('@<(script|style)[^>]*?>.*?</\\1>@si', '', $text);
    $text = strip_tags($text);
    return $text;
}

abstract class CodeColorerTestCase extends TestCase
{
    protected function assertMatchesRegexCompat($pattern, $subject, $message = '')
    {
        if (method_exists($this, 'assertMatchesRegularExpression')) {
            $this->assertMatchesRegularExpression($pattern, $subject, $message);
            return;
        }

        $this->assertRegExp($pattern, $subject, $message);
    }

    protected function assertStringContainsCompat($needle, $haystack, $message = '')
    {
        if (method_exists($this, 'assertStringContainsString')) {
            $this->assertStringContainsString($needle, $haystack, $message);
            return;
        }

        $this->assertContains($needle, $haystack, $message);
    }

    protected function assertStringNotContainsCompat($needle, $haystack, $message = '')
    {
        if (method_exists($this, 'assertStringNotContainsString')) {
            $this->assertStringNotContainsString($needle, $haystack, $message);
            return;
        }

        $this->assertNotContains($needle, $haystack, $message);
    }
}

require_once 'codecolorer.php';

function codecolorer_simulate_comment_submission($content)
{
    $protected = CodeColorerLoader::callBeforeProtectComment($content);
    $sanitized = wp_strip_all_tags($protected);

    return CodeColorerLoader::callAfterProtectComment($sanitized);
}

function codecolorer_render_comment($content)
{
    return CodeColorerLoader::callAfterHighlightCodeBlock(
        CodeColorerLoader::callBeforeHighlightCodeBlock($content)
    );
}

function codecolorer_simulate_comment_render($content)
{
    return codecolorer_render_comment(codecolorer_simulate_comment_submission($content));
}
