<?php
/*
Plugin Name: CodeColorer
Plugin URI: http://kpumuk.info/projects/wordpress-plugins/codecolorer
Description: This plugin allows you to insert code snippets to your posts with nice syntax highlighting powered by <a href="http://qbnz.com/highlighter/">GeSHi</a> library. After enabling this plugin visit <a href="options-general.php?page=codecolorer.php">the options page</a> to configure code style.
Version: 0.9.0
Author: Dmytro Shteflyuk
Author URI: http://kpumuk.info/
*/
/*
    Copyright 2006 - 2009  Dmytro Shteflyuk <kpumuk@kpumuk.info>

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/**
 * Doesn't work if PHP version is not 4.0.6 or higher
 */
if (version_compare(phpversion(), '4.0.6', '<')) {
  return;
}

/**
 * Loader class for the CodeColorer plugin
 */
class CodeColorerLoader {
  /**
   * Enables the CodeColorer plugin with registering all required hooks.
   */
  function Enable() {
    add_action('admin_init', array('CodeColorerLoader', 'AdminInit'));

    // Add plugin options page
    add_action('admin_menu', array('CodeColorerLoader', 'AddPluginOptionsPage'));

    // Load CodeColorer styles on admin pages
    add_action('admin_head', array('CodeColorerLoader', 'LoadStyles'));

    // Load CodeColorer styles on regular pages
    add_action('wp_head', array('CodeColorerLoader', 'LoadStyles'));

    // Add action links
    add_action('plugin_action_links_' . plugin_basename(__FILE__), array('CodeColorerLoader', 'AddPluginActions'));

    // Code highlighting filters
    add_filter('the_content', array('CodeColorerLoader', 'CallBeforeHighlightCodeBlock'), -1000);
    add_filter('the_content', array('CodeColorerLoader', 'CallAfterHighlightCodeBlock'), 1000);
    add_filter('the_excerpt', array('CodeColorerLoader', 'CallBeforeHighlightCodeBlock'), -1000);
    add_filter('the_excerpt', array('CodeColorerLoader', 'CallAfterHighlightCodeBlock'), 1000);
    add_filter('comment_text', array('CodeColorerLoader', 'CallBeforeHighlightCodeBlock'), -1000);
    add_filter('comment_text', array('CodeColorerLoader', 'CallAfterHighlightCodeBlock'), 1000);

    // Code protection filters
    add_filter('pre_comment_content', array('CodeColorerLoader', 'CallBeforeProtectComment'), -1000);
    add_filter('pre_comment_content', array('CodeColorerLoader', 'CallAfterProtectComment'), 1000);

    /* Add some default options if they don't exist */
    add_option('codecolorer_css_style', '');
    add_option('codecolorer_lines_to_scroll', 20);
    add_option('codecolorer_width', 435);
    add_option('codecolorer_height', 300);
    add_option('codecolorer_rss_width', 435);
    add_option('codecolorer_line_numbers', false);
    add_option('codecolorer_disable_keyword_linking', false);
    add_option('codecolorer_tab_size', 4);
    add_option('codecolorer_theme', '');
    add_option('codecolorer_inline_theme', '');
  }

  function AdminInit() {
    $plugin_dir = basename(dirname(__FILE__));
    load_plugin_textdomain('codecolorer', false, "$plugin_dir/languages");

    // Register out options so WordPress knows about them
    if (function_exists('register_setting')) {
      if (!class_exists('CodeColorerOptions')) {
        $path = dirname(__FILE__);
        if (!file_exists("$path/codecolorer-options.php")) return false;
        require_once("$path/codecolorer-options.php");
      }

      register_setting('codecolorer', 'codecolorer_css_style', '');
      register_setting('codecolorer', 'codecolorer_lines_to_scroll', 'intval');
      register_setting('codecolorer', 'codecolorer_width', '');
      register_setting('codecolorer', 'codecolorer_height', '');
      register_setting('codecolorer', 'codecolorer_rss_width', '');
      register_setting('codecolorer', 'codecolorer_line_numbers', '');
      register_setting('codecolorer', 'codecolorer_disable_keyword_linking', array('CodeColorerOptions', 'SanitizeBoolean'));
      register_setting('codecolorer', 'codecolorer_tab_size', 'intval');
      register_setting('codecolorer', 'codecolorer_theme', '');
      register_setting('codecolorer', 'codecolorer_inline_theme', '');
    }
  }

  function LoadStyles() {
    $css_url = plugins_url(basename(dirname(__FILE__)) . '/codecolorer.css');
    echo "<link rel=\"stylesheet\" href=\"$css_url\" type=\"text/css\" />\n";
    $styles = trim(get_option('codecolorer_css_style'));
    if (!empty($styles)) {
      echo "<style type=\"text/css\">$styles</style>\n";
    }
  }

  function AddPluginOptionsPage() {
    if (function_exists('add_options_page')) {
      add_options_page('CodeColorer', 'CodeColorer', 8, 'codecolorer.php', array('CodeColorerLoader', 'CallShowOptionsPage'));
    }
  }

  function AddPluginActions($links) {
    $new_links = array();

    $new_links[] = '<a href="options-general.php?page=codecolorer.php">' . __('Settings') . '</a>';

    return array_merge($new_links, $links);
  }

  function CallShowOptionsPage() {
    if (CodeColorerLoader::LoadPlugin()) {
      $cc = CodeColorer::GetInstance();
      $cc->ShowOptionsPage();
    }
  }

  function CallBeforeHighlightCodeBlock($content) {
    if (CodeColorerLoader::LoadPlugin()) {
      $cc = CodeColorer::GetInstance();
      return $cc->BeforeHighlightCodeBlock($content);
    }
    return $content;
  }

  function CallAfterHighlightCodeBlock($content) {
    if (CodeColorerLoader::LoadPlugin()) {
      $cc = CodeColorer::GetInstance();
      return $cc->AfterHighlightCodeBlock($content);
    }
    return $content;
  }

  function CallBeforeProtectComment($content) {
    if (CodeColorerLoader::LoadPlugin()) {
      $cc = CodeColorer::GetInstance();
      return $cc->BeforeProtectComment($content);
    }
    return $content;
  }

  function CallAfterProtectComment($content) {
    if (CodeColorerLoader::LoadPlugin()) {
      $cc = CodeColorer::GetInstance();
      return $cc->AfterProtectComment($content);
    }
    return $content;
  }

  function LoadPlugin() {
    if (!class_exists('CodeColorer')) {
      $path = dirname(__FILE__);
      if (!file_exists("$path/codecolorer-core.php")) return false;
      require_once("$path/codecolorer-core.php");
    }

    if (!CodeColorer::Enable()) return false;
    return true;
  }
}

CodeColorerLoader::Enable();

?>
