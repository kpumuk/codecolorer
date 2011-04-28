<?php
/*
CodeColorer plugin core part
http://kpumuk.info/projects/wordpress-plugins/codecolorer
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

class CodeColorer {
  var $blocks = array();
  var $comments = array();

  var $geshiExternal = false;
  var $geshiVersion = '1.0.8.6';

  var $geshi;
  var $optionsPage;

  var $samplePhpCode = '
    [cc_php]
    /**
     * Comment
     */
    function hello() {
      echo "Hello!";
      return null;
    }
    exit();
    [/cc_php]
  ';

  /** Search content for code tags and replace it */
  function BeforeHighlightCodeBlock($content) {
    $content = preg_replace('#(\s*)\[cc([^\s\]_]*(?:_[^\s\]]*)?)([^\]]*)\](.*?)\[/cc\2\](\s*)#sie', '$this->PerformHighlightCodeBlock(\'\\4\', \'\\3\', $content, \'\\2\', \'\\1\', \'\\5\');', $content);
    $content = preg_replace('#(\s*)\<code(.*?)\>(.*?)\</code\>(\s*)#sie', '$this->PerformHighlightCodeBlock(\'\\3\', \'\\2\', $content, \'\', \'\\1\', \'\\4\');', $content);

    return $content;
  }

  function AfterHighlightCodeBlock($content) {
    $content = str_replace(array_keys($this->blocks), array_values($this->blocks), $content);

    return $content;
  }

  function BeforeProtectComment($content) {
    $content = preg_replace('#(\s*)(\[cc[^\s\]_]*(?:_[^\s\]]*)?[^\]]*\].*?\[/cc\1\])(\s*)#sie', '$this->PerformProtectComment(\'\\2\', $content, \'\\1\', \'\\3\');', $content);
    $content = preg_replace('#(\s*)(\<code.*?\>.*?\</code\>)(\s*)#sie', '$this->PerformProtectComment(\'\\2\', $content, \'\\1\', \'\\3\');', $content);

    return $content;
  }

  function AfterProtectComment($content) {
    $content = str_replace(array_keys($this->comments), array_values($this->comments), $content);
    $this->comments = array();

    return $content;
  }

  /**
   * Perform code highlightning
   */
  function PerformHighlightCodeBlock($text, $opts, $content, $suffix = '', $before = '', $after = '') {
    // Parse options
    $options = CodeColorerOptions::ParseOptions($opts, $suffix);

    // Load code from a file
    if (isset($options['file'])) {
      $uploadPath = wp_upload_dir();
      $baseDir = realpath($uploadPath['basedir']);
      $filePath = realpath(path_join($baseDir, $options['file']));
      # Security check: do not allow to display arbitrary files, only the ones from
      # uploads folder.
      if (false === $filePath || 0 !== strncmp($baseDir, $filePath, strlen($baseDir)) || !is_file($filePath)) {
        $text = 'Specified file is not in uploads folder, does not exists, or not a file.';
        $options['lang'] = 'text';
      } else {
        $text = file_get_contents($filePath);
      }
    }

    // Preprocess source text
    $text = str_replace(array("\\\"", "\\\'"), array ("\"", "\'"), $text);
    $text = preg_replace('/(< \?php)/i', '<?php', $text);
    $text = preg_replace('/(?:^(?:\s*[\r\n])+|\s+$)/', '', $text);

    if ($options['escaped']) {
      $text = html_entity_decode($text, ENT_QUOTES);
      $text = preg_replace('~&#x0*([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $text);
      $text = preg_replace('~&#0*([0-9]+);~e', 'chr(\\1)', $text);
    }

    $result = '';
    // Check if CodeColorer has been disabled for this particular block
    if (!$options['enabled']) {
      $result = '<code>' . $text . '</code>';
    } else {
      // See if we should force a height
      $num_lines = count(explode("\n", $text));

      $result = $this->PerformHighlightGeshi($text, $options);

      $result = $this->AddContainer($result, $options, $num_lines);
    }

    if ($options['inline']) {
      $blockID = $this->GetBlockID($content, false, '<span>', '</span>');
    } else {
      $blockID = $this->GetBlockID($content);
    }
    $this->blocks[$blockID] = $result;

    if ($options['inline']) {
      $result = $before . $blockID . $after;
    } else {
      $result = "\n\n$blockID\n\n";
    }

    return $result;
  }

  /**
   * Perform code protecting from mangling by Wordpress (used in Comments)
   */
  function PerformProtectComment($text, $content, $before, $after) {
    $text = str_replace(array("\\\"", "\\\'"), array ("\"", "\'"), $text);

    $blockID = $this->GetBlockID($content, true, '', '');
    $this->comments[$blockID] = $text;

    return $before . $blockID . $after;
  }

  /**
   * Perform code highlighting using GESHi engine
   */
  function PerformHighlightGeshi($content, $options) {
    /* Geshi configuration */
    if (!$this->geshi) {
      $this->geshi = new GeSHi();
      $this->geshi->enable_line_numbers(GESHI_NO_LINE_NUMBERS, 1);
      if (is_feed()) {
        $this->geshi->set_overall_style('padding:5px;font:normal 12px/1.4em Monaco, Lucida Console, monospace;white-space:nowrap');
      }
    }

    $geshi = $this->geshi;
    $geshi->set_source($content);
    $geshi->set_language($options['lang']);
    $geshi->set_overall_class('codecolorer');
    $geshi->set_tab_width($options['tab_size']);
    if (!is_feed()) {
      $geshi->enable_classes($options['theme'] != 'geshi');
      if ($options['nowrap']) {
        $geshi->set_overall_style('white-space:nowrap');
      } else {
        $geshi->set_overall_style('');
      }
    } else {
      $geshi->enable_classes(false);
    }
    if (!is_null($options['strict'])) $geshi->enable_strict_mode($options['strict']);
    if (isset($options['no_links']) && $options['no_links']) $geshi->enable_keyword_links(false);
    if (isset($options['highlight'])) {
      $hlines = explode(',', $options['highlight']);
      $highlight = array(); /* Empty array to store processed line numbers*/
      foreach($hlines as $v) {
        list($from, $to) = explode('-', $v);
        if (is_null($to)) $to = $from;
        for ($i = $from; $i <= $to; $i++) {
          array_push($highlight, $i);
        }
      }
      /* Sort the array in ascending numerical order */
      sort($highlight);
      $geshi->highlight_lines_extra($highlight);
      $geshi->set_highlight_lines_extra_style('background-color:#ffff66');
    }
    if ($options['inline']) {
      $geshi->set_header_type(GESHI_HEADER_NONE);
    } else {
      $geshi->set_header_type(GESHI_HEADER_DIV);
    }

    $result = $geshi->parse_code();

    if ($geshi->error()) {
      return $geshi->error();
    }

    if ($options['line_numbers'] && !$options['inline']) {
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

  function AddContainer($html, $options, $num_lines) {
    $custom_css_class = empty($options['class']) ? '' : ' ' . $options['class'];
    if ($options['inline']) {
      $theme = empty($options['inline_theme']) ? 'default' : $options['inline_theme'];
      $result  = '<code class="codecolorer ' . $options['lang'] . ' ' . $theme . $custom_css_class . '">';
      $result .= '<span class="' . $options['lang'] . '">' . $html . '</span>';
      $result .= '</code>';
    } else {
      $theme = empty($options['theme']) ? 'default' : $options['theme'];
      $style = 'style="';
      if ($options['nowrap']) $style .= 'overflow:auto;white-space:nowrap;';
      if (is_feed()) $style .= 'border:1px solid #9F9F9F;';
      $style .= $this->GetDimensionRule('width', is_feed() ? $options['rss_width'] : $options['width']);
      if($num_lines > $options['lines'] && $options['lines'] > 0) {
        $style .= $this->GetDimensionRule('height', $options['height']);
      }
      $style .= '"';

      $css_class = 'codecolorer-container ' . $options['lang'] . ' ' . $theme . $custom_css_class;
      if ($options['noborder']) $css_class .= ' codecolorer-noborder';
      $result = '<div class="' . $css_class . '" ' . $style . '>' . $html . '</div>';
    }
    return $result;
  }

  /**
   * Generate a block ID that will be replaced at the end (after all that
   * crazy WP text work!) with the right code
   */
  function GetBlockID($content, $comment = false, $before = '<div>', $after = '</div>') {
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

  function GetDimensionRule($dimension, $value) {
    $rule = '';
    if (!empty($value)) $rule = "$dimension:$value" . (is_numeric($value) ? 'px;' : ';');
    return $rule;
  }

  function ShowWarning($type, $title, $message) {
    $disable = ' <a href="options-general.php?page=codecolorer.php&amp;disable=' . $type . '">' . __('Close', 'codecolorer') . '</a>';
    echo '<div id="codecolorer-' . $type . '" class="updated fade"><p><strong>' . $title . "</strong> " . $message . $disable . "</p></div>\n";
  }

  function ShowGeshiWarning() {
    if ($this->geshiExternal) {
      $this->ShowWarning('concurrent', __('CodeColorer has detected a problem.', 'codecolorer'), sprintf(__('We found another plugin based on GeSHi library in your system. CodeColorer will work, but our version of GeSHi contain some patches, so we can\'t guarantee an ideal code highlighting now. Please review your <a href="%1$s">plugins</a>, maybe you don\'t need them all.', 'codecolorer'), "plugins.php"));
    }
  }

  function ShowOptionsPage() {
    $page = $this->GetOptionsPage();
    $page->Show();
  }

  function GetOptionsPage() {
    if (!$this->optionsPage) {
      if (!class_exists('CodeColorerAdmin')) {
        $path = dirname(__FILE__);
        if (!file_exists("$path/codecolorer-admin.php")) return false;
        require_once("$path/codecolorer-admin.php");
      }
      $this->optionsPage = new CodeColorerAdmin($this);
    }
    return $this->optionsPage;
  }

  function GetCodeHighlighted($code) {
    $content = $this->BeforeHighlightCodeBlock($code);
    return $this->AfterHighlightCodeBlock($content);
  }

  function GetSampleCodeHighlighted() {
    return $this->GetCodeHighlighted($this->samplePhpCode);
  }

  function &GetInstance() {
    static $instance = null;

    if (null === $instance) {
      $path = dirname(__FILE__);
      if (!class_exists('CodeColorerOptions')) {
        if (!file_exists("$path/codecolorer-options.php")) return null;
        require_once("$path/codecolorer-options.php");
      }

      $instance = new CodeColorer();

      # Maybe GeSHi has been loaded by some another plugin?
      if (!class_exists('GeSHi')) {
        if (!file_exists("$path/lib/geshi.php")) return null;
        require_once("$path/lib/geshi.php");
      } else {
        $instance->geshiExternal = true;
      }
    }

    return $instance;
  }
}