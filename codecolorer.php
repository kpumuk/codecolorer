<?php
/*
Plugin Name: CodeColorer
Plugin URI: http://kpumuk.info/projects/wordpress-plugins/codecolorer/
Description: This plugin allows you to insert code snippets to your posts with nice syntax highlighting powered by <a href="http://qbnz.com/highlighter/">GeSHi</a> library. After enabling this plugin visit <a href="options-general.php?page=codecolorer.php">the options page</a> to configure code style.
Version: 0.9.9
Author: Dmytro Shteflyuk
Author URI: http://kpumuk.info/
Text Domain: codecolorer
Domain Path: /languages/
*/
/*
    Copyright 2006 - 2011  Dmytro Shteflyuk <kpumuk@kpumuk.info>

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

define('CODECOLORER_VERSION', '0.9.9');

/**
 * Loader class for the CodeColorer plugin
 */
class CodeColorerLoader {
  /**
   * Enables the CodeColorer plugin with registering all required hooks.
   */
  function Enable() {
    $path = dirname(__FILE__);
    if (!file_exists("$path/codecolorer-core.php")) return false;
    require_once("$path/codecolorer-core.php");

    // Add some default options if they don't exist
    add_option('codecolorer_css_style', '');
    add_option('codecolorer_css_class', '');
    add_option('codecolorer_lines_to_scroll', 20);
    add_option('codecolorer_width', 435);
    add_option('codecolorer_height', 300);
    add_option('codecolorer_rss_width', 435);
    add_option('codecolorer_line_numbers', false);
    add_option('codecolorer_disable_keyword_linking', false);
    add_option('codecolorer_tab_size', 4);
    add_option('codecolorer_theme', '');
    add_option('codecolorer_inline_theme', '');

    // Two more options to disable notifications
    add_option('codecolorer_language_notification', true);
    add_option('codecolorer_concurrent_notification', true);

    // Admin panel initialization
    add_action('admin_init', array('CodeColorerLoader', 'AdminInit'));

    // Add plugin options page
    add_action('admin_menu', array('CodeColorerLoader', 'AddPluginOptionsPage'));

    // Load CodeColorer styles on admin pages
    add_action('admin_print_styles', array('CodeColorerLoader', 'LoadStyles'));

    // Show notice when another GeSHi library found
    if (get_option('codecolorer_concurrent_notification')) {
      add_action('admin_notices', array('CodeColorerLoader', 'CallShowGeshiWarning'));
    }

    // Load CodeColorer styles on regular pages
    add_action('wp_print_styles', array('CodeColorerLoader', 'LoadStyles'));

    // Add action links
    add_action('plugin_action_links_' . plugin_basename(__FILE__), array('CodeColorerLoader', 'AddPluginActions'));

    // Add meta links
    add_filter('plugin_row_meta', array('CodeColorerLoader', 'AddPluginLinks'), 10, 2);

    // Code highlighting filters
    add_filter('the_content',  array('CodeColorerLoader', 'CallBeforeHighlightCodeBlock'), -1000);
    add_filter('the_content',  array('CodeColorerLoader', 'CallAfterHighlightCodeBlock'),   1000);
    add_filter('the_excerpt',  array('CodeColorerLoader', 'CallBeforeHighlightCodeBlock'), -1000);
    add_filter('the_excerpt',  array('CodeColorerLoader', 'CallAfterHighlightCodeBlock'),   1000);
    add_filter('comment_text', array('CodeColorerLoader', 'CallBeforeHighlightCodeBlock'), -1000);
    add_filter('comment_text', array('CodeColorerLoader', 'CallAfterHighlightCodeBlock'),   1000);
    add_filter('book_review',  array('CodeColorerLoader', 'CallBeforeHighlightCodeBlock'), -1000);
    add_filter('book_review',  array('CodeColorerLoader', 'CallAfterHighlightCodeBlock'),   1000);

    // Code protection filters
    add_filter('pre_comment_content', array('CodeColorerLoader', 'CallBeforeProtectComment'), -1000);
    add_filter('pre_comment_content', array('CodeColorerLoader', 'CallAfterProtectComment'), 1000);

    return true;
  }

  function AdminInit() {
    $plugin_dir = basename(dirname(__FILE__));
    load_plugin_textdomain('codecolorer', false, "$plugin_dir/languages");

    if (!class_exists('CodeColorerOptions')) {
      $path = dirname(__FILE__);
      if (!file_exists("$path/codecolorer-options.php")) return false;
      require_once("$path/codecolorer-options.php");
    }

    // Register out options so WordPress knows about them
    register_setting('codecolorer', 'codecolorer_css_style', '');
    register_setting('codecolorer', 'codecolorer_css_class', '');
    register_setting('codecolorer', 'codecolorer_lines_to_scroll', 'intval');
    register_setting('codecolorer', 'codecolorer_width', '');
    register_setting('codecolorer', 'codecolorer_height', '');
    register_setting('codecolorer', 'codecolorer_rss_width', '');
    register_setting('codecolorer', 'codecolorer_line_numbers', '');
    register_setting('codecolorer', 'codecolorer_disable_keyword_linking', array('CodeColorerOptions', 'SanitizeBoolean'));
    register_setting('codecolorer', 'codecolorer_tab_size', 'intval');
    register_setting('codecolorer', 'codecolorer_theme', '');
    register_setting('codecolorer', 'codecolorer_inline_theme', '');

    // Scripts
    if (current_user_can('edit_posts') || current_user_can('edit_pages')) {
      // Quick tags
      add_action('wp_print_scripts', array('CodeColorerLoader', 'RegisterQuicktag'));

      // TinyMCE
      // temporarily disabled
      // if (get_user_option('rich_editing') == 'true') {
      //   add_filter('mce_external_plugins', array('CodeColorerLoader', 'AddTinyMCEPlugin'));
      //   add_filter('mce_buttons', array('CodeColorerLoader', 'RegisterTinyMCEButton'));
      // }
    }
  }

  function LoadStyles() {
    $css_url = plugins_url(basename(dirname(__FILE__)) . '/codecolorer.css');
    wp_register_style('codecolorer', $css_url, array(), CODECOLORER_VERSION, 'screen');
    wp_enqueue_style('codecolorer');
    $styles = trim(get_option('codecolorer_css_style'));
    if (!empty($styles)) {
      echo "<style type=\"text/css\">$styles</style>\n";
    }
  }

  function AddPluginOptionsPage() {
    if (function_exists('add_options_page')) {
      add_options_page('CodeColorer', 'CodeColorer', 'manage_options', 'codecolorer.php', array('CodeColorerLoader', 'CallShowOptionsPage'));
    }
  }

  function AddPluginActions($links) {
    $new_links = array();

    $new_links[] = '<a href="options-general.php?page=codecolorer.php">' . __('Settings', 'codecolorer') . '</a>';

    return array_merge($new_links, $links);
  }

  function AddPluginLinks($links, $file) {
    if ($file == basename(dirname(__FILE__)) . '/' . basename(__FILE__)) {
      $links[] = '<a href="http://kpumuk.info/projects/wordpress-plugins/codecolorer/#faq">' . __('FAQ', 'codecolorer') . '</a>';
      $links[] = '<a href="http://kpumuk.info/projects/wordpress-plugins/codecolorer/#support">' . __('Support', 'codecolorer') . '</a>';
    }
    return $links;
  }

  function RegisterQuicktag() {
    if (is_admin()) {
      wp_enqueue_script('jquery');
      $url = plugins_url(basename(dirname(__FILE__)) . '/js/quicktags.js');
      wp_enqueue_script('codecolorer', $url, array('jquery'), CODECOLORER_VERSION, true);
      wp_localize_script('codecolorer', 'codeColorerL10n', array(
        'enterLanguage' => __('Enter Language')
      ));
    }
  }

  function RegisterTinyMCEButton($buttons) {
    array_push($buttons, 'separator', 'codecolorer');
    return $buttons;
  }

  function AddTinyMCEPlugin($plugins) {
    $url = plugins_url(basename(dirname(__FILE__)) . '/js/tinymce_plugin.js');
    $plugins['codecolorer'] = $url;
    return $plugins;
  }

  function CallShowOptionsPage() {
    $cc = &CodeColorer::GetInstance();
    if (null !== $cc) {
      $cc->ShowOptionsPage();
    }
  }

  function CallShowGeshiWarning() {
    $cc = &CodeColorer::GetInstance();
    if (null !== $cc) {
      $cc->ShowGeshiWarning();
    }
  }

  function CallBeforeHighlightCodeBlock($content) {
    $cc = &CodeColorer::GetInstance();
    if (null !== $cc) {
      return $cc->BeforeHighlightCodeBlock($content);
    }
    return $content;
  }

  function CallAfterHighlightCodeBlock($content) {
    $cc = &CodeColorer::GetInstance();
    if (null !== $cc) {
      return $cc->AfterHighlightCodeBlock($content);
    }
    return $content;
  }

  function CallBeforeProtectComment($content) {
    $cc = &CodeColorer::GetInstance();
    if (null !== $cc) {
      return $cc->BeforeProtectComment($content);
    }
    return $content;
  }

  function CallAfterProtectComment($content) {
    $cc = &CodeColorer::GetInstance();
    if (null !== $cc) {
      return $cc->AfterProtectComment($content);
    }
    return $content;
  }

  function Highlight($code) {
    $cc = &CodeColorer::GetInstance();
    if (null !== $cc) {
      return $cc->GetCodeHighlighted($code);
    }
    return $code;
  }
}

CodeColorerLoader::Enable();

function codecolorer_highlight($code) {
  return CodeColorerLoader::Highlight($code);
}