<?php
/*
Plugin Name: CodeColorer
Plugin URI: http://kpumuk.info/projects/wordpress-plugins/codecolorer
Description: Syntax highlighting plugin, based on <a href="http://www.chroder.com/archives/2005/04/16/wordpress-codehighlight-plugin/">CodeHighlight</a>, <a href="http://blog.enargi.com/codesnippet/">Code Snippet</a> and <a href="http://qbnz.com/highlighter/">GeSHi</a> library. Use plugin options (In menu Options>CodeColorer) to configure code style.
Version: 0.7.0
Author: Dmytro Shteflyuk
Author URI: http://kpumuk.info/
*/
/*
    Copyright 2006 - 2008  Dmytro Shteflyuk <kpumuk@kpumuk.info>

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
add_action('admin_head', array(&$CodeColorer, 'init'), 1);
add_action('admin_head', array(&$CodeColorer, 'addCssStyle'), 1);
add_action('admin_head', array(&$CodeColorer, 'addPluginOptionsPage'), 1);

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
  
  var $DEFAULT_STYLE = '';#border: 1px solid #ccc; background: #eee;';
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
    echo '<link rel="stylesheet" href="' . get_option('siteurl') . $this->pluginLocation . 'codecolorer.css" type="text/css" />', "\n";
    echo '<style type="text/css">', "\n";
    echo '.codecolorer-container  {' . $this->getStyle() . '}', "\n";
    echo '</style>', "\n";
  }

  function getStyle() {
    $style = stripslashes(get_option('codecolorer_css_style'));

    /** Workaround for preview */
    if ('process' == $_POST['stage']) {
      if ($_POST['codecolorer_css_style'])
        $style = $_POST['codecolorer_css_style'];
    }
    if (empty($style)) $style = CodeColorer::getDefaultStyle();

    return $style;
  }

  function sampleCodeFactory() {
    $this->init();
    $options = $this->parseOptions('lang="php"');
    $html = $this->highlightGeshi($this->samplePhpCode, $options);
    $num_lines = count(explode("\n", $this->samplePhpCode));
    return $this->addContainer($html, $options, $num_lines);
  }

  function addPluginOptionsPage() {
    if (function_exists('add_options_page')) {
      add_options_page('CodeColorer', 'CodeColorer', 9, 'codecolorer/codecolorer-options.php');
    }
  }

  /** Perform code highlighting using GESHi engine */
  function highlightGeshi($content, $options) {
    $lang = $this->filterLang($options['lang']);
    if (!class_exists('geshi')) $this->init();
    
    /* Geshi configuration */
    $geshi = new GeSHi($content, $lang, $this->geshi_path);
    $geshi->enable_classes();
    $geshi->set_overall_class('codecolorer');
    $geshi->set_overall_style('font-family:Monaco,Lucida Console,monospace');
    $geshi->set_tab_width($options['tab_size']);
    $geshi->enable_line_numbers(GESHI_NO_LINE_NUMBERS, 1);
    if ($options['no_links']) {
      $geshi->enable_keyword_links(false); 
    }
    $geshi->set_header_type(GESHI_HEADER_DIV);

    if ($geshi->error()) {
      return $geshi->error();
    }
    
    $result = $geshi->parse_code();
    if ($options['line_numbers']) {
      $table = '<table cellspacing="0" cellpadding="0"><tr><td class="line-numbers"><div>';
      for ($i = 1, $count = substr_count($result, '<br />') + 1; $i <= $count; $i++) {
        $table .= $i . '<br />';
      }
      $result = $table . '</div></td><td>' . $result . '</td></table>';
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

    // See if we should force a height
    $num_lines = count(explode("\n", $text));

    $result = $this->highlightGeshi($text, $options);

    $result = $this->addContainer($result, $options, $num_lines);
    $blockID = $this->getBlockID($content);
    $this->blocks[$blockID] = $result;

    return "\n\n" . $blockID . "\n\n";
  }
  
  /** Perform code protecting from mangling by Wordpress (used in Comments) */
  function performProtect($text, $content) {
    $text = str_replace(array("\\\"", "\\\'"), array ("\"", "\'"), $text);

    $blockID = $this->getBlockID($content, true, '', '');
    $this->comments[$blockID] = $text;

    return "\n\n" . $blockID . "\n\n";
  }

  function addContainer($html, $options, $num_lines) {
    $style = 'style="overflow:auto;width:' . $options['width'] . 'px';
    if($num_lines > $options['lines'] && $options['lines'] > 0) {
      $style .= ';height:' . $options['height'] . 'px';
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
  
  function populateDefaultValues($options) {
    if (!$options['lang']) $options['lang'] = 'text';
    if (!$options['tab_size']) {
      $options['tab_size'] = intval(get_option('codecolorer_tab_size'));
    } else {
      $options['tab_size'] = intval($options['tab_size']);
    }
    if (!$options['line_numbers']) {
      $options['line_numbers'] = $this->parseBoolean(get_option('codecolorer_line_numbers'));
    } else {
        $options['line_numbers'] = $this->parseBoolean($options['line_numbers']);
    }
    if (!$options['no_links']) {
        $options['no_links'] = $this->parseBoolean(get_option('codecolorer_disable_keyword_linking'));
    } else {
        $options['no_links'] = $this->parseBoolean($options['no_links']);
    }
    if (!$options['lines']) {
        $options['lines'] = intval(get_option('codecolorer_lines_to_scroll'));
    } else {
        $options['lines'] = intval($options['lines']);
    }
    if (!$options['width']) {
        $options['width'] = intval(get_option('codecolorer_width'));
    } else {
        $options['width'] = intval($options['width']);
    }
    if (!$options['height']) {
        $options['height'] = intval(get_option('codecolorer_height'));
    } else {
        $options['height'] = intval($options['height']);
    }
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
  
  function getDefaultStyle() {
    return $this->DEFAULT_STYLE;
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
