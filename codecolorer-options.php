<?php
/*
CodeColorer plugin options part
https://kpumuk.info/projects/wordpress-plugins/codecolorer
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

class CodeColorerOptions
{
    public static function getThemes()
    {
        return array(
            ''                => 'Slush & Poppies',
            'blackboard'      => 'Blackboard',
            'dawn'            => 'Dawn',
            'mac-classic'     => 'Mac Classic',
            'twitlight'       => 'Twitlight',
            'vibrant'         => 'Vibrant Ink',
            'geshi'           => 'GeSHi',
            'railscasts'      => 'Railscasts',
            'solarized-light' => 'Solarized Light',
            'solarized-dark'  => 'Solarized Dark',
        );
    }

    public static function getLanguages()
    {
        return array(
            '', 'en', 'en_US', 'en_GB', 'ar', 'be_BY', 'cs_CZ', 'da_DK', 'de_DE', 'es_AR',
            'es_CO', 'es_ES', 'fa_IR', 'fr_FR', 'he_IL', 'hu_HU', 'id_ID', 'it_IT',
            'ja', 'ka_GE', 'ms_MY', 'nl_NL', 'pl_PL', 'pt_BR', 'pt_PT', 'ro_RO',
            'ru_RU', 'sk_SK', 'sv_SE', 'tr_TR', 'ua_UA', 'zh_CN', 'zh_TW'
          );
    }

    protected static function getLanguageMappings()
    {
        return array(
            'c#'  => 'csharp',
            'cs'  => 'csharp',
            'c++' => 'cpp',
            'f#'  => 'fsharp',
            'fs'  => 'fsharp',
            'js'  => 'javascript'
        );
    }

    public static function parseOptions($opts, $suffix = '')
    {
        $opts = str_replace(array("\\\"", "\\\'"), array ("\"", "\'"), $opts);
        preg_match_all('#([a-z_-]*?)\s*=\s*(["\'])(.*?)\2#i', $opts, $matches, PREG_SET_ORDER);
        $options = array();
        for ($i = 0; $i < sizeof($matches); $i++) {
            $options[$matches[$i][1]] = $matches[$i][3];
        }

        $options = self::populateDefaultValues($options);

        @list($modes, $lang) = explode('_', $suffix, 2);
        if (null !== ($mode = self::parseMode($modes, 'i'))) {
            $options['inline'] = $mode;
        }
        if (null !== ($mode = self::parseMode($modes, 'e'))) {
            $options['escaped'] = $mode;
        }
        if (null !== ($mode = self::parseMode($modes, 's'))) {
            $options['strict'] = $mode;
        }
        if (null !== ($mode = self::parseMode($modes, 'n'))) {
            $options['line_numbers'] = $mode;
        }
        if (null !== ($mode = self::parseMode($modes, 'b'))) {
            $options['noborder'] = $mode;
        }
        if (null !== ($mode = self::parseMode($modes, 'w'))) {
            $options['nowrap'] = $mode;
        }
        if (null !== ($mode = self::parseMode($modes, 'l'))) {
            $options['no_links'] = $mode;
        }

        if (!empty($lang)) {
            $options['lang'] = self::filterLanguage($lang);
        }

        return $options;
    }

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    protected static function populateDefaultValues($options)
    {
        if (!$options) {
            $options = array();
        }

        if (!isset($options['lang'])) {
            $options['lang'] = 'text';
        }
        $options['lang'] = self::filterLanguage($options['lang']);

        // Whether CodeColorer should be enabled (bool)
        if (isset($options['enabled'])) {
            $options['enabled'] = self::parseBoolean($options['enabled']);
        } elseif (isset($options['no_cc'])) {
            $options['enabled'] = !self::parseBoolean($options['no_cc']);
        } else {
            $options['enabled'] = true;
        }

        // Whether code in block is already escaped (bool)
        if (!isset($options['escaped'])) {
            $options['escaped'] = false;
        } else {
            $options['escaped'] = self::parseBoolean($options['escaped']);
        }

        // Whether horizontal wrapping should be disabled (bool)
        if (!isset($options['nowrap'])) {
            $options['nowrap'] = true;
        } else {
            $options['nowrap'] = self::parseBoolean($options['nowrap']);
        }

        // Disable container border (bool)
        if (!isset($options['noborder'])) {
            $options['noborder'] = false;
        } else {
            $options['noborder'] = self::parseBoolean($options['noborder']);
        }

        // Whether strict mode should be enabled (bool)
        if (!isset($options['strict'])) {
            $options['strict'] = null;
        } else {
            $options['strict'] = self::parseBoolean($options['strict']);
        }

        // Whether code should be rendered inline
        if (!isset($options['inline'])) {
            $options['inline'] = false;
        } else {
            $options['inline'] = self::parseBoolean($options['inline']);
        }

        // Tab size (int)
        if (!isset($options['tab_size'])) {
            $options['tab_size'] = intval(self::wpOption('codecolorer_tab_size'));
        } else {
            $options['tab_size'] = intval($options['tab_size']);
        }

        // Line numbers (bool)
        if (!isset($options['line_numbers'])) {
            $options['line_numbers'] = self::parseBoolean(self::wpOption('codecolorer_line_numbers'));
        } else {
            $options['line_numbers'] = self::parseBoolean($options['line_numbers']);
        }
        // var_dump(self::wpOption('codecolorer_line_numbers'));

        // First line (int)
        if (!isset($options['first_line'])) {
            $options['first_line'] = 1;
        } else {
            $options['first_line'] = intval($options['first_line']);
        }
        if ($options['first_line'] < 1) {
            $options['first_line'] = 1;
        }

        // Disable keyword linking (bool)
        if (!isset($options['no_links'])) {
            $options['no_links'] = self::parseBoolean(self::wpOption('codecolorer_disable_keyword_linking'));
        } else {
            $options['no_links'] = self::parseBoolean($options['no_links']);
        }

        // Lines to scroll (int)
        if (!isset($options['lines'])) {
            $options['lines'] = intval(self::wpOption('codecolorer_lines_to_scroll'));
        } else {
            $options['lines'] = intval($options['lines']);
        }

        // Block width (int or string)
        if (!isset($options['width'])) {
            $options['width'] = self::wpOption('codecolorer_width');
        }

        // Block height (int or string)
        if (!isset($options['height'])) {
            $options['height'] = self::wpOption('codecolorer_height');
        }

        // Block width in RSS (int or string)
        if (!isset($options['rss_width'])) {
            $options['rss_width'] = self::wpOption('codecolorer_rss_width');
        }

        // Custom CSS classes (string)
        if (!isset($options['class'])) {
            $options['class'] = self::wpOption('codecolorer_css_class');
        }

        // Theme (string)
        if (!isset($options['theme'])) {
            $options['theme'] = self::wpOption('codecolorer_theme');
            $options['inline_theme'] = self::wpOption('codecolorer_inline_theme');
        } else {
            $options['inline_theme'] = $options['theme'];
        }
        if ($options['theme'] == 'default') {
            $options['theme'] = '';
            $options['inline_theme'] = '';
        }

        return $options;
    }

    protected static function parseBoolean($val)
    {
        return $val === true || $val === 'true' || $val === 'on' || $val === '1' || (is_int($val) && $val !== 0);
    }

    protected static function parseMode($modes, $mode)
    {
        if (strpos($modes, $mode) !== false) {
            return true;
        }
        if (strpos($modes, strtoupper($mode)) !== false) {
            return false;
        }
        return null;
    }

    public static function sanitizeBoolean($val)
    {
        return $val == '1';
    }

    /**
     * Process the language identifier attribute string
     */
    protected static function filterLanguage($lang)
    {
        $lang = strtolower($lang);
        if (strstr($lang, 'html')) {
            return 'html4strict';
        }

        $langs = self::getLanguageMappings();
        if (isset($langs[$lang]) && !empty($langs[$lang])) {
            return $langs[$lang];
        }

        return $lang;
    }

    /**
     * Returns WordPress option value
     */
    protected static function wpOption($name)
    {
        return get_option($name);
    }

    public static function feedOverallStyle()
    {
        return 'padding:5px;' .
               'font:normal 12px/1.4em Monaco, Lucida Console, monospace;' .
               'white-space:nowrap;';
    }

    public static function feedLineNumbersStyle()
    {
        return self::feedOverallStyle() .
               'text-align:center;' .
               'color:#888888;' .
               'background-color:#EEEEEE;' .
               'border-right: 1px solid #9F9F9F;';
    }
}
