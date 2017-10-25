<?php
/*
 * Plugin Name: CodeColorer
 * Plugin URI: https://kpumuk.info/projects/wordpress-plugins/codecolorer/
 * Description: This plugin allows you to insert code snippets to your posts with nice syntax highlighting powered by <a href="http://qbnz.com/highlighter/">GeSHi</a> library. After enabling this plugin visit <a href="options-general.php?page=codecolorer.php">the options page</a> to configure code style.
 * Version: 0.9.14
 * Author: Dmytro Shteflyuk
 * Author URI: https://kpumuk.info/
 * Text Domain: codecolorer
 */
/*
    Copyright 2006 - 2017  Dmytro Shteflyuk <kpumuk@kpumuk.info>

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

define('CODECOLORER_VERSION', '0.9.14');

/**
 * Loader class for the CodeColorer plugin
 */
class CodeColorerLoader
{
    /**
     * Enables the CodeColorer plugin with registering all required hooks.
     */
    public static function enable()
    {
        $path = dirname(__FILE__);
        if (!file_exists("$path/codecolorer-core.php")) {
            return false;
        }
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
        add_action('admin_init', array('CodeColorerLoader', 'adminInit'));

        // Add plugin options page
        add_action('admin_menu', array('CodeColorerLoader', 'addPluginOptionsPage'));

        // Load CodeColorer styles on admin pages
        add_action('admin_print_styles', array('CodeColorerLoader', 'loadStyles'));

        // Show notice when another GeSHi library found
        if (get_option('codecolorer_concurrent_notification')) {
            add_action('admin_notices', array('CodeColorerLoader', 'callShowGeshiWarning'));
        }

        // Load CodeColorer styles on regular pages
        add_action('wp_print_styles', array('CodeColorerLoader', 'loadStyles'));

        // Add action links
        add_action('plugin_action_links_' . plugin_basename(__FILE__), array('CodeColorerLoader', 'addPluginActions'));

        // Add meta links
        add_filter('plugin_row_meta', array('CodeColorerLoader', 'addPluginLinks'), 10, 2);

        // Code highlighting filters
        add_filter('the_content', array('CodeColorerLoader', 'callBeforeHighlightCodeBlock'), -1000);
        add_filter('the_content', array('CodeColorerLoader', 'callAfterHighlightCodeBlock'), 1000);
        add_filter('the_excerpt', array('CodeColorerLoader', 'callBeforeHighlightCodeBlock'), -1000);
        add_filter('the_excerpt', array('CodeColorerLoader', 'callAfterHighlightCodeBlock'), 1000);
        add_filter('comment_text', array('CodeColorerLoader', 'callBeforeHighlightCodeBlock'), -1000);
        add_filter('comment_text', array('CodeColorerLoader', 'callAfterHighlightCodeBlock'), 1000);
        add_filter('book_review', array('CodeColorerLoader', 'callBeforeHighlightCodeBlock'), -1000);
        add_filter('book_review', array('CodeColorerLoader', 'callAfterHighlightCodeBlock'), 1000);

        // Code protection filters
        add_filter('pre_comment_content', array('CodeColorerLoader', 'callBeforeProtectComment'), -1000);
        add_filter('pre_comment_content', array('CodeColorerLoader', 'callAfterProtectComment'), 1000);

        // TablePress support
        add_filter('tablepress_cell_content', array('CodeColorerLoader', 'callBeforeHighlightCodeBlock'), -1000, 1);
        add_filter('tablepress_cell_content', array('CodeColorerLoader', 'callAfterHighlightCodeBlock'), 1000, 1);

        return true;
    }

    public static function adminInit()
    {
        $pluginDir = basename(dirname(__FILE__));
        load_plugin_textdomain('codecolorer', false, "$pluginDir/languages");

        if (!class_exists('CodeColorerOptions')) {
            $path = dirname(__FILE__);
            if (!file_exists("$path/codecolorer-options.php")) {
                return false;
            }
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
        register_setting('codecolorer', 'codecolorer_disable_keyword_linking', array('CodeColorerOptions', 'sanitizeBoolean'));
        register_setting('codecolorer', 'codecolorer_tab_size', 'intval');
        register_setting('codecolorer', 'codecolorer_theme', '');
        register_setting('codecolorer', 'codecolorer_inline_theme', '');

        // Scripts
        if (current_user_can('edit_posts') || current_user_can('edit_pages')) {
            // Quick tags
            add_action('wp_print_scripts', array('CodeColorerLoader', 'registerQuicktag'));

            // TinyMCE
            // temporarily disabled
            // if (get_user_option('rich_editing') == 'true') {
            //   add_filter('mce_external_plugins', array('CodeColorerLoader', 'addTinyMCEPlugin'));
            //   add_filter('mce_buttons', array('CodeColorerLoader', 'registerTinyMCEButton'));
            // }
            add_filter('tiny_mce_before_init', array('CodeColorerLoader', 'addTinyMCEValidElements'));
            add_filter('teeny_mce_before_init', array('CodeColorerLoader', 'addTinyMCEValidElements'));
        }
    }

    public static function loadStyles()
    {
        $cssUrl = plugins_url(basename(dirname(__FILE__)) . '/codecolorer.css');
        wp_register_style('codecolorer', $cssUrl, array(), CODECOLORER_VERSION, 'screen');
        wp_enqueue_style('codecolorer');
        $styles = trim(get_option('codecolorer_css_style'));
        if (!empty($styles)) {
            echo "<style type=\"text/css\">$styles</style>\n";
        }
    }

    public static function addPluginOptionsPage()
    {
        if (function_exists('add_options_page')) {
            add_options_page('CodeColorer', 'CodeColorer', 'manage_options', 'codecolorer.php', array('CodeColorerLoader', 'callShowOptionsPage'));
        }
    }

    public static function addPluginActions($links)
    {
        $newLinks = array();

        $newLinks[] = '<a href="options-general.php?page=codecolorer.php">' . __('Settings', 'codecolorer') . '</a>';

        return array_merge($newLinks, $links);
    }

    public static function addPluginLinks($links, $file)
    {
        if ($file == basename(dirname(__FILE__)) . '/' . basename(__FILE__)) {
            $links[] = '<a href="https://kpumuk.info/projects/wordpress-plugins/codecolorer/#faq">' . __('FAQ', 'codecolorer') . '</a>';
            $links[] = '<a href="https://kpumuk.info/projects/wordpress-plugins/codecolorer/#support">' . __('Support', 'codecolorer') . '</a>';
        }
        return $links;
    }

    public static function registerQuicktag()
    {
        if (!is_admin()) {
            return;
        }

        wp_enqueue_script('jquery');
        $url = plugins_url(basename(dirname(__FILE__)) . '/js/quicktags.js');
        wp_enqueue_script('codecolorer', $url, array('jquery'), CODECOLORER_VERSION, true);
        wp_localize_script(
            'codecolorer',
            'codeColorerL10n',
            array(
                'enterLanguage' => __('Enter Language')
            )
        );
    }

    public static function registerTinyMCEButton($buttons)
    {
        array_push($buttons, 'separator', 'codecolorer');
        return $buttons;
    }

    public static function addTinyMCEPlugin($plugins)
    {
        $url = plugins_url(basename(dirname(__FILE__)) . '/js/tinymce_plugin.js');
        $plugins['codecolorer'] = $url;
        return $plugins;
    }

    public static function addTinyMCEValidElements($init) {
        $knownOptions = CodeColorerOptions::parseOptions('');
        $ext = 'code[' . implode('|', array_keys($knownOptions)) . ']';

        if (isset($init['extended_valid_elements'])) {
            $init['extended_valid_elements'] .= ',' . $ext;
        } else {
            $init['extended_valid_elements'] = $ext;
        }

        return $init;
    }

    public static function callShowOptionsPage()
    {
        $codecolorer = &CodeColorer::getInstance();
        if (null !== $codecolorer) {
            $codecolorer->showOptionsPage();
        }
    }

    public static function callShowGeshiWarning()
    {
        $codecolorer = &CodeColorer::getInstance();
        if (null !== $codecolorer) {
            $codecolorer->showGeshiWarning();
        }
    }

    public static function callBeforeHighlightCodeBlock($content)
    {
        $codecolorer = &CodeColorer::getInstance();
        if (null !== $codecolorer) {
            return $codecolorer->beforeHighlightCodeBlock($content);
        }
        return $content;
    }

    public static function callAfterHighlightCodeBlock($content)
    {
        $codecolorer = &CodeColorer::getInstance();
        if (null !== $codecolorer) {
            return $codecolorer->afterHighlightCodeBlock($content);
        }
        return $content;
    }

    public static function callBeforeProtectComment($content)
    {
        $codecolorer = &CodeColorer::getInstance();
        if (null !== $codecolorer) {
            return $codecolorer->beforeProtectComment($content);
        }
        return $content;
    }

    public static function callAfterProtectComment($content)
    {
        $codecolorer = &CodeColorer::getInstance();
        if (null !== $codecolorer) {
            return $codecolorer->afterProtectComment($content);
        }
        return $content;
    }

    public static function highlight($code)
    {
        $codecolorer = &CodeColorer::getInstance();
        if (null !== $codecolorer) {
            return $codecolorer->getCodeHighlighted($code);
        }
        return $code;
    }
}

CodeColorerLoader::enable();

function codecolorer_highlight($code)
{
    return CodeColorerLoader::highlight($code);
}
