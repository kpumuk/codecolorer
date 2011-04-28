<?php
/*
CodeColorer plugin options part
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

class CodeColorerOptions {
  function GetThemes() {
    return array(
      ''            => 'Slush & Poppies',
      'blackboard'  => 'Blackboard',
      'dawn'        => 'Dawn',
      'mac-classic' => 'Mac Classic',
      'twitlight'   => 'Twitlight',
      'vibrant'     => 'Vibrant Ink',
      'geshi'       => 'GeSHi',
      'railscasts'  => 'Railscasts'
    );
  }

  function GetLanguages() {
    return array(
      '', 'en', 'en_US', 'ar', 'be_BY', 'cs_CZ', 'da_DK', 'de_DE', 'es_AR',
      'es_CO', 'es_ES', 'fa_IR', 'fr_FR', 'he_IL', 'hu_HU', 'it_IT', 'ja',
      'ka_GE', 'ms_MY', 'nl_NL', 'pl_PL', 'pt_BR', 'ro_RO', 'ru_RU', 'sk_SK',
      'sv_SE', 'tr_TR', 'ua_UA', 'zh_CN', 'zh_TW'
    );
  }

  function GetLanguageMappings() {
    return array(
      'c#'  => 'csharp',
      'cs'  => 'csharp',
      'c++' => 'cpp',
      'f#'  => 'fsharp',
      'fs'  => 'fsharp',
      'js'  => 'javascript'
    );
  }

  function ParseOptions($opts, $suffix = '') {
    $opts = str_replace(array("\\\"", "\\\'"), array ("\"", "\'"), $opts);
    preg_match_all('#([a-z_-]*?)\s*=\s*(["\'])(.*?)\2#i', $opts, $matches, PREG_SET_ORDER);
    $options = array();
    for ($i = 0; $i < sizeof($matches); $i++) {
      $options[$matches[$i][1]] = $matches[$i][3];
    }

    $options = CodeColorerOptions::PopulateDefaultValues($options);

    @list($modes, $lang) = explode('_', $suffix, 2);
    if (NULL !== ($mode = CodeColorerOptions::ParseMode($modes, 'i'))) {
      $options['inline'] = $mode;
    }
    if (NULL !== ($mode = CodeColorerOptions::ParseMode($modes, 'e'))) {
      $options['escaped'] = $mode;
    }
    if (NULL !== ($mode = CodeColorerOptions::ParseMode($modes, 's'))) {
      $options['strict'] = $mode;
    }
    if (NULL !== ($mode = CodeColorerOptions::ParseMode($modes, 'n'))) {
      $options['line_numbers'] = $mode;
    }
    if (NULL !== ($mode = CodeColorerOptions::ParseMode($modes, 'b'))) {
      $options['noborder'] = $mode;
    }
    if (NULL !== ($mode = CodeColorerOptions::ParseMode($modes, 'w'))) {
      $options['nowrap'] = $mode;
    }
    if (NULL !== ($mode = CodeColorerOptions::ParseMode($modes, 'l'))) {
      $options['no_links'] = $mode;
    }

    if (!empty($lang)) {
      $options['lang'] = CodeColorerOptions::FilterLanguage($lang);
    }

    return $options;
  }

  function PopulateDefaultValues($options) {
    if (!$options) $options = array();

    if (!isset($options['lang'])) $options['lang'] = 'text';
    $options['lang'] = CodeColorerOptions::FilterLanguage($options['lang']);

    // Whether CodeColorer should be enabled (bool)
    if (isset($options['enabled'])) {
      $options['enabled'] = CodeColorerOptions::ParseBoolean($options['enabled']);
    } elseif (isset($options['no_cc'])) {
      $options['enabled'] = !CodeColorerOptions::ParseBoolean($options['no_cc']);
    } else {
      $options['enabled'] = true;
    }

    // Whether code in block is already escaped (bool)
    if (!isset($options['escaped'])) {
      $options['escaped'] = false;
    } else {
      $options['escaped'] = CodeColorerOptions::ParseBoolean($options['escaped']);
    }

    // Whether horizontal wrapping should be disabled (bool)
    if (!isset($options['nowrap'])) {
      $options['nowrap'] = true;
    } else {
      $options['nowrap'] = CodeColorerOptions::ParseBoolean($options['nowrap']);
    }

    // Disable container border (bool)
    if (!isset($options['noborder'])) {
      $options['noborder'] = false;
    } else {
      $options['noborder'] = CodeColorerOptions::ParseBoolean($options['noborder']);
    }

    // Whether strict mode should be enabled (bool)
    if (!isset($options['strict'])) {
      $options['strict'] = NULL;
    } else {
      $options['strict'] = CodeColorerOptions::ParseBoolean($options['strict']);
    }

    // Whether code should be rendered inline
    if (!isset($options['inline'])) {
      $options['inline'] = false;
    } else {
      $option['inline'] = CodeColorerOptions::ParseBoolean($options['inline']);
    }

    // Tab size (int)
    if (!isset($options['tab_size'])) {
      $options['tab_size'] = intval(get_option('codecolorer_tab_size'));
    } else {
      $options['tab_size'] = intval($options['tab_size']);
    }

    // Line numbers (bool)
    if (!isset($options['line_numbers'])) {
      $options['line_numbers'] = CodeColorerOptions::ParseBoolean(get_option('codecolorer_line_numbers'));
    } else {
      $options['line_numbers'] = CodeColorerOptions::ParseBoolean($options['line_numbers']);
    }
    // var_dump(get_option('codecolorer_line_numbers'));

    // First line (int)
    if (!isset($options['first_line'])) {
      $options['first_line'] = 1;
    } else {
      $options['first_line'] = intval($options['first_line']);
    }
    if ($options['first_line'] < 1) $options['first_line'] = 1;

    // Disable keyword linking (bool)
    if (!isset($options['no_links'])) {
        $options['no_links'] = CodeColorerOptions::ParseBoolean(get_option('codecolorer_disable_keyword_linking'));
    } else {
        $options['no_links'] = CodeColorerOptions::ParseBoolean($options['no_links']);
    }

    // Lines to scroll (int)
    if (!isset($options['lines'])) {
        $options['lines'] = intval(get_option('codecolorer_lines_to_scroll'));
    } else {
        $options['lines'] = intval($options['lines']);
    }

    // Block width (int or string)
    if (!isset($options['width'])) {
        $options['width'] = get_option('codecolorer_width');
    }

    // Block height (int or string)
    if (!isset($options['height'])) {
        $options['height'] = get_option('codecolorer_height');
    }

    // Block width in RSS (int or string)
    if (!isset($options['rss_width'])) {
        $options['rss_width'] = get_option('codecolorer_rss_width');
    }

    // Custom CSS classes (string)
    if (!isset($options['class'])) {
        $options['class'] = get_option('codecolorer_css_class');
    }

    // Theme (string)
    if (!isset($options['theme'])) {
      $options['theme'] = get_option('codecolorer_theme');
      $options['inline_theme'] = get_option('codecolorer_inline_theme');
    } else {
      $options['inline_theme'] = $options['theme'];
    }
    if ($options['theme'] == 'default') {
      $options['theme'] = '';
      $options['inline_theme'] = '';
    }

    return $options;
  }

  function ParseBoolean($val) {
    return $val === true || $val === 'true' || $val === 'on' || $val === '1' || (is_int($val) && $val !== 0);
  }

  function ParseMode($modes, $mode) {
    if (strpos($modes, $mode) !== false) {
      return true;
    }
    if (strpos($modes, strtoupper($mode)) !== false) {
      return false;
    }
    return null;
  }

  function SanitizeBoolean($val) {
    return $val == '1';
  }

  /**
   * Process the language identifier attribute string
   */
  function FilterLanguage($lang) {
    $lang = strtolower($lang);
    if (strstr($lang, 'html')) {
      $lang = 'html4strict';
    } else {
      $langs = CodeColorerOptions::GetLanguageMappings();
      if (isset($langs[$lang]) && $langs[$lang]) $lang = $langs[$lang];
    }
    return $lang;
  }
}
