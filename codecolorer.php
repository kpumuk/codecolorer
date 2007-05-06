<?php
/*
Plugin Name: CodeColorer
Plugin URI: http://kpumuk.info/projects/wordpress-plugins/codecolorer
Description: Syntax highlighting plugin, based on <a href="http://www.chroder.com/archives/2005/04/16/wordpress-codehighlight-plugin/">CodeHighlight</a>, <a href="http://blog.enargi.com/codesnippet/">Code Snippet</a> and <a href="http://qbnz.com/highlighter/">GeSHi</a> library. Use plugin options (In menu Options>CodeColorer) to configure code style.
Version: 0.5.3
Author: Dmytro Shteflyuk
Author URI: http://kpumuk.info/
*/
/*
    Copyright 2006  Dmytro Shteflyuk <kpumuk@kpumuk.info>

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
  
  var $DEFAULT_STYLE = 'border: 1px solid #ccc; background: #eee; padding: 5px; margin: 10px;';
  var $DEFAULT_LINES_TO_SCROLL = 20;
  var $DEFAULT_LINE_HEIGHT = 14;

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
  function addCSS($id) {
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
    $html = $this->highlight_geshi($this->samplePhpCode, 'php-brief');
    $num_lines = count(explode("\n", $this->samplePhpCode));
    return $this->addContainer($html, 'php', $num_lines);
  }

  function addPluginOptionsPage() {
    if (function_exists('add_options_page')) {
      add_options_page('CodeColorer', 'CodeColorer', 9, 'codecolorer/codecolorer-options.php');
    }
  }

  /** Perform code highlighting using GESHi engine */
  function highlight_geshi($content, $lang) {
    $lang = $this->filterLang($lang);
    if (!class_exists('geshi')) $this->init();
    
    /* Geshi configuration */
    $geshi = new GeSHi($content, $lang, $this->geshi_path);
    $geshi->enable_classes();
    $geshi->set_overall_class('codecolorer');
    $geshi->set_overall_style('');
    if (stripslashes(get_option('codecolorer_line_numbers'))) {
      $geshi->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS, 1);
    } else {
      $geshi->enable_line_numbers(GESHI_NO_LINE_NUMBERS, 1);
    }
    if (stripslashes(get_option('codecolorer_disable_keyword_linking'))) {
      $geshi->enable_keyword_links(false); 
    }
    $geshi->set_header_type(GESHI_HEADER_DIV);

    if ($geshi->error()) {
      return $geshi->error();
    }
    
    $result = $geshi->parse_code();
    return $result;
  }

  /** Search content for code tags and replace it */
  function highlightCode1($content) {
    $content = preg_replace('#\[cc\](.*?)\[/cc\]#sie', '$this->performHighlight(\'\\1\', \'text\', $content);', $content);
    $content = preg_replace('#\[cc lang=["\'](.*?)["\']\](.*?)\[/cc\]#sie', '$this->performHighlight(\'\\2\', \'\\1\', $content);', $content);
    $content = preg_replace('#\<code\>(.*?)\</code\>#sie', '$this->performHighlight(\'\\1\', \'text\', $content);', $content);
    $content = preg_replace('#\<code lang=["\'](.*?)["\']\>(.*?)\</code\>#sie', '$this->performHighlight(\'\\2\', \'\\1\', $content);', $content);

    return $content;
  }

  function highlightCode2($content) {
    $content = str_replace(array_keys($this->blocks), array_values($this->blocks), $content);
    $this->blocks = array();

    return $content;
  }

  function protectCommentContent1($content) {
  	$content = stripslashes($content);
    $content = preg_replace('#\[cc\](.*?)\[/cc\]#sie', '$this->performProtect(\'\\1\', \'text\', $content);', $content);
    $content = preg_replace('#\[cc lang=["\'](.*?)["\']\](.*?)\[/cc\]#sie', '$this->performProtect(\'\\2\', \'\\1\', $content);', $content);
    $content = preg_replace('#\<code\>(.*?)\</code\>#sie', '$this->performProtect(\'\\1\', \'text\', $content);', $content);
    $content = preg_replace('#\<code lang=["\'](.*?)["\']\>(.*?)\</code\>#sie', '$this->performProtect(\'\\2\', \'\\1\', $content);', $content);

    return $content;
  }

  function protectCommentContent2($content) {
  	$content = stripslashes($content);
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
  function performHighlight($text, $lang, $content) {
    $text = str_replace(array("\\\"", "\\\'"), array ("\"", "\'"), $text);
    $text = preg_replace('/(< \?php)/i', '<?php', $text);
    $text = trim($text);

    // See if we should force a height
    $num_lines = count(explode("\n", $text));

    if ($lang) {
      $result = $this->highlight_geshi($text, $lang);
    } else {
      $result = $text;
    }

    $result = $this->addContainer($result, $lang, $num_lines);
    $blockID = $this->getBlockID($content);
    $this->blocks[$blockID] = $result;

    return $blockID;
  }
  
  /** Perform code protecting from mangling by Wordpress (used in Comments) */
  function performProtect($text, $lang, $content) {
  	$text = str_replace(array("\\\"", "\\\'"), array ("\"", "\'"), $text);
  	$before = '<div>[cc lang="' . $lang . '"]';
  	$after = '[/cc]</div>';
    $blockID = $this->getBlockID($content, true, $before, $after);
    var_dump($blockID);
    $this->comments[$blockID] = addslashes('[cc lang="' . $lang . '"]' . $text . '[/cc]');

    return $blockID;
  }

  function addContainer($html, $lang, $num_lines) {
    if($num_lines > $this->getLinesToScroll())
        $style = ' style="height:' . ($this->getLinesToScroll() * $this->getLineHeight()) . 'px;"';
    elseif($num_lines == 1)
        $style = ' style="height:' . intval(2.5 * $this->getLineHeight()) . 'px;"';
    else
        $style = '';

    $result = '<div class="codecolorer-container' . ($lang ? ' ' . $lang : '') . '"' . $style . '>' . $html . '</div>';
    return $result;
  }

  /** Process the lang identifier sttribute string */
  function filterLang($lang) {
    $lang = strtolower($lang);
    if (strstr($lang, 'html')) {
      $lang = 'html4strict';
    }
    return $lang;
  }
  
  function getLinesToScroll() {
    return intval(get_option('codecolorer_lines_to_scroll'));
  }
  
  function getLineHeight() {
    return intval(get_option('codecolorer_line_height'));
  }
  
  function getDefaultStyle() {
    return $this->DEFAULT_STYLE;
  }
  
  function getDefaultLinesToScroll() {
    return $this->DEFAULT_LINES_TO_SCROLL;
  }
  
  function getDefaultLineHeight() {
    return $this->DEFAULT_LINE_HEIGHT;
  }
}

?>