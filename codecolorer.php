<?php
/*
Plugin Name: CodeColorer
Plugin URI: http://kpumuk.info/projects/wordpress-plugins/codecolorer
Description: This plugin allows you to insert code snippets to your posts with nice syntax highlighting powered by <a href="http://qbnz.com/highlighter/">GeSHi</a> library. After enabling this plugin visit <a href="options-general.php?page=codecolorer/codecolorer-options.php">the options page</a> to configure code style.
Version: 0.8.2
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

/** Instance of plugin */
$CodeColorer = new CodeColorer();

/** Register plugin actions */

/** Regular pages */
add_action('wp_head', array (&$CodeColorer, 'addCSS'), 1);

/** Admin pages */
if (is_admin()) {
    add_action('admin_head', array(&$CodeColorer, 'init'), 1);
    add_action('admin_head', array(&$CodeColorer, 'addCssStyle'), 1);
    add_action('admin_menu', array(&$CodeColorer, 'addPluginOptionsPage'), 1);
}

/** Filters */
add_filter('the_content', array(&$CodeColorer, 'highlightCode1'), -1000);
add_filter('the_content', array(&$CodeColorer, 'highlightCode2'), 1000);
add_filter('the_excerpt', array(&$CodeColorer, 'highlightCode1'), -1000);
add_filter('the_excerpt', array(&$CodeColorer, 'highlightCode2'), 1000);
add_filter('comment_text', array(&$CodeColorer, 'highlightCode1'), -1000);
add_filter('comment_text', array(&$CodeColorer, 'highlightCode2'), 1000);
add_filter('pre_comment_content', array(&$CodeColorer, 'protectCommentContent1'), -1000);
add_filter('pre_comment_content', array(&$CodeColorer, 'protectCommentContent2'), 1000);

unset ($CodeColorer);

/** CodeColorer plugin class */
class CodeColorer {
  var $pluginLocation = '/wp-content/plugins/codecolorer/';
  var $pluginPath;
  var $libPath;

  var $DEFAULT_LINES_TO_SCROLL = 20;
  var $DEFAULT_WIDTH = 435;
  var $DEFAULT_HEIGHT = 300;

  var $blocks = array();
  var $comments = array();

  var $samplePhpCode = '  /**
   * Comment
   */
  function hello() {
    echo "Hello!";
    return null;
  }
  exit();';

  /** Initialization of environment */
  function init() {
    $this->pluginPath = dirname(__FILE__) . DIRECTORY_SEPARATOR;
    $this->libPath = $this->pluginPath . 'lib' . DIRECTORY_SEPARATOR;
    require_once($this->libPath . 'geshi.php');
  }

  /** Add css references to page head */
  function addCSS() {
    $this->init();
    $this->addCssStyle();
  }

  function addCssStyle() {
    $styles = stripslashes(get_option('codecolorer_css_style'));
    echo '<link rel="stylesheet" href="' . get_option('siteurl') . $this->pluginLocation . 'codecolorer.css" type="text/css" />', "\n";
    if (!empty($styles)) {
      echo '<style type="text/css">' . $styles . "</style>\n";
    }
  }

  function sampleCodeFactory() {
    $this->init();
    $options = $this->parseOptions('lang="php"');
    $html = $this->highlightGeshi($this->samplePhpCode, $options);
    $num_lines = count(explode("\n", $this->samplePhpCode));
    return $this->addContainer($html, $options, $num_lines);
  }

  function addPluginOptionsPage() {
    add_options_page('CodeColorer', 'CodeColorer', 8, 'codecolorer/codecolorer-options.php');
  }

  /** Perform code highlighting using GESHi engine */
  function highlightGeshi($content, $options) {
    if (!class_exists('geshi')) $this->init();

    /* Geshi configuration */
    $geshi = new GeSHi($content, $options['lang'], $this->geshi_path);
    $geshi->set_overall_class('codecolorer');
    if (is_feed()) {
      $geshi->set_overall_style('padding:5px;font:normal 12px/1.4em Monaco, Lucida Console, monospace;white-space:nowrap');
    } else {
      $geshi->enable_classes();
      if ($options['nowrap']) {
        $geshi->set_overall_style('white-space:nowrap');
      } else {
        $geshi->set_overall_style('');
      }
    }
    $geshi->set_tab_width($options['tab_size']);
    $geshi->enable_line_numbers(GESHI_NO_LINE_NUMBERS, 1);
    if ($options['no_links']) $geshi->enable_keyword_links(false);
    $geshi->set_header_type(GESHI_HEADER_DIV);

    if ($geshi->error()) {
      return $geshi->error();
    }

    $result = $geshi->parse_code();
    if ($options['line_numbers']) {
      $table = '<table cellspacing="0" cellpadding="0"><tbody><tr><td ';
      if (is_feed()) {
        $table .= 'style="padding:5px;text-align:center;color:#888888;background-color:#EEEEEE;border-right: 1px solid #9F9F9F;font: normal 12px/1.4em Monaco, Lucida Console, monospace;"';
      } else {
        $table .= 'class="line-numbers"';
      }
      $table .= '><div>';
      for ($i = 0, $count = substr_count($result, '<br />') + 1; $i < $count; $i++) {
        $table .= ($i + $options['first_line']) . '<br />';
      }
      $result = $table . '</div></td><td>' . $result . '</td></tr></tbody></table>';
    }

    return $result;
  }

  /** Search content for code tags and replace it */
  function highlightCode1($content) {
    $content = preg_replace('#\s*\[cc(.*?)\](.*?)\[/cc\]\s*#sie', '$this->performHighlight(\'\\2\', \'\\1\', $content);', $content);
    $content = preg_replace('#\s*\<code(.*?)\>(.*?)\</code\>\s*#sie', '$this->performHighlight(\'\\2\', \'\\1\', $content);', $content);

    return $content;
  }

  function highlightCode2($content) {
    $content = str_replace(array_keys($this->blocks), array_values($this->blocks), $content);
    $this->blocks = array();

    return $content;
  }

  function protectCommentContent1($content) {
    $content = preg_replace('#\s*(\[cc.*?\].*?\[/cc\])\s*#sie', '$this->performProtect(\'\\1\', $content);', $content);
    $content = preg_replace('#\s*(\<code.*?\>.*?\</code\>)\s*#sie', '$this->performProtect(\'\\1\', $content);', $content);

    return $content;
  }

  function protectCommentContent2($content) {
    $content = str_replace(array_keys($this->comments), array_values($this->comments), $content);
    $this->comments = array();

    return $content;
  }

  /**
   * Generate a block ID that will be replaced at the end (after all that
   * crazy WP text work!) with the right code
   */
  function getBlockID($content, $comment = false, $before = '<div>', $after = '</div>') {
    static $num = 0;

    $block = $comment ? 'COMMENT' : 'BLOCK';
    $before = $before . '::CODECOLORER_' . $block . '_';
    $after = '::' . $after;

    // Just do a check to make sure the user
    // hasn't (however unlikely) input block replacements
    // as legit text
    do {
      ++$num;
      $blockID = $before . $num . $after;
    } while (strpos($content, $blockID) !== false);

    return $blockID;
  }

  /** Perform code highlightning */
  function performHighlight($text, $opts, $content) {
    $text = str_replace(array("\\\"", "\\\'"), array ("\"", "\'"), $text);
    $text = preg_replace('/(< \?php)/i', '<?php', $text);
    $text = trim($text);

    $options = $this->parseOptions($opts);

    if ($options['no_cc']) {
      $result = '<code>' . $text . '</code>';
    } else {
      // See if we should force a height
      $num_lines = count(explode("\n", $text));

      $result = $this->highlightGeshi($text, $options);

      $result = $this->addContainer($result, $options, $num_lines);
      $blockID = $this->getBlockID($content);
      $this->blocks[$blockID] = $result;

      $result = "\n\n" . $blockID . "\n\n";
    }

    return $result;
  }

  /** Perform code protecting from mangling by Wordpress (used in Comments) */
  function performProtect($text, $content) {
    $text = str_replace(array("\\\"", "\\\'"), array ("\"", "\'"), $text);

    $blockID = $this->getBlockID($content, true, '', '');
    $this->comments[$blockID] = $text;

    return "\n\n" . $blockID . "\n\n";
  }

  function addContainer($html, $options, $num_lines) {
    $style = 'style="';
    if ($options['nowrap']) $style .= 'overflow:auto;white-space:nowrap;';
    if (is_feed()) $style .= 'border: 1px solid #9F9F9F;';
    $style .= $this->getDimensionRule('width', is_feed() ? $options['rss_width'] : $options['width']);
    if($num_lines > $options['lines'] && $options['lines'] > 0) {
      $style .= $this->getDimensionRule('height', $options['height']);
    }
    $style .= '"';

    $result = '<div class="codecolorer-container ' . $options['lang'] . ' ' . $options['theme'] . '" ' . $style . '>' . $html . '</div>';
    return $result;
  }

  /** Process the lang identifier sttribute string */
  function filterLang($lang) {
    $lang = strtolower($lang);
    if (strstr($lang, 'html')) {
      $lang = 'html4strict';
    } elseif ($lang == 'c#') {
      $lang = 'csharp';
    } elseif ($lang == 'c++') {
      $lang = 'cpp';
    }
    return $lang;
  }

  function parseOptions($opts) {
    $opts = str_replace(array("\\\"", "\\\'"), array ("\"", "\'"), $opts);
    preg_match_all('#([a-z_-]*?)\s*=\s*(["\'])(.*?)\2#i', $opts, $matches, PREG_SET_ORDER);
    $options = array();
    for ($i = 0; $i < sizeof($matches); $i++) {
      $options[$matches[$i][1]] = $matches[$i][3];
    }
    return $this->populateDefaultValues($options);
  }

  function getDimensionRule($dimension, $value) {
    $rule = '';
    if (!empty($value)) $rule = "$dimension:$value" . (is_int($value) ? ';' : 'px;');
    return $rule;
  }

  function populateDefaultValues($options) {
    if (!$options['lang']) $options['lang'] = 'text';
    $options['lang'] = $this->filterLang($options['lang']);

    if (!$options['no_cc']) {
      $options['no_cc'] = false;
    } else {
      $options['no_cc'] = $this->parseBoolean($options['no_cc']);
    }

    if (!$options['nowrap']) {
      $options['nowrap'] = true;
    } else {
      $options['nowrap'] = $this->parseBoolean($options['nowrap']);
    }

    // Tab size (int)
    if (!$options['tab_size']) {
      $options['tab_size'] = intval(get_option('codecolorer_tab_size'));
    } else {
      $options['tab_size'] = intval($options['tab_size']);
    }

    // Line numbers (bool)
    if (!$options['line_numbers']) {
      $options['line_numbers'] = $this->parseBoolean(get_option('codecolorer_line_numbers'));
    } else {
      $options['line_numbers'] = $this->parseBoolean($options['line_numbers']);
    }

    // First line (int)
    if (!$options['first_line'] && $options['first_line'] !== '0') {
      $options['first_line'] = 1;
    } else {
      $options['first_line'] = intval($options['first_line']);
    }

    // Disable keyword linking (bool)
    if (!$options['no_links']) {
        $options['no_links'] = $this->parseBoolean(get_option('codecolorer_disable_keyword_linking'));
    } else {
        $options['no_links'] = $this->parseBoolean($options['no_links']);
    }

    // Lines to scroll (int)
    if (!$options['lines']) {
        $options['lines'] = intval(get_option('codecolorer_lines_to_scroll'));
    } else {
        $options['lines'] = intval($options['lines']);
    }

    // Block width (int or string)
    if (!$options['width']) {
        $options['width'] = get_option('codecolorer_width');
    }

    // Block height (int or string)
    if (!$options['height']) {
        $options['height'] = get_option('codecolorer_height');
    }

    // Block width in RSS (int or string)
    if (!$options['rss_width']) {
        $options['rss_width'] = get_option('codecolorer_rss_width');
    }

    // Theme (string)
    if (!$options['theme']) {
      $options['theme'] = get_option('codecolorer_theme');
    }
    if ($options['theme'] == 'default') {
      $options['theme'] = '';
    }

    return $options;
  }

  function parseBoolean($val) {
    return $val === true || $val === 'true' || $val === '1' || (is_int($val) && $val !== 0);
  }

  function getDefaultLinesToScroll() {
    return $this->DEFAULT_LINES_TO_SCROLL;
  }

  function getDefaultWidth() {
    return $this->DEFAULT_WIDTH;
  }

  function getDefaultHeight() {
    return $this->DEFAULT_HEIGHT;
  }
}

?>
