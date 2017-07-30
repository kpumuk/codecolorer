<?php
/*
CodeColorer plugin core part
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

class CodeColorer
{
    private $blocks = array();
    private $comments = array();

    private $geshiExternal = false;

    private $geshi;
    private $optionsPage;

    private $samplePhpCode = '
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
    public function beforeHighlightCodeBlock($content)
    {
        $helper = $this;

        $content = preg_replace_callback(
            '#(\s*)\[cc([^\s\]_]*(?:_[^\s\]]*)?)([^\]]*)\](.*?)\[/cc\2\](\s*)#si',
            function ($matches) use ($helper, $content) {
                return $helper->performHighlightCodeBlock(
                    $matches[4],
                    $matches[3],
                    $content,
                    $matches[2],
                    $matches[1],
                    $matches[5]
                );
            },
            $content
        );

        $content = preg_replace_callback(
            '#(\s*)\<code(.*?)\>(.*?)\</code\>(\s*)#si',
            function ($matches) use ($helper, $content) {
                return $helper->performHighlightCodeBlock(
                    $matches[3],
                    $matches[2],
                    $content,
                    '',
                    $matches[1],
                    $matches[4]
                );
            },
            $content
        );

        return $content;
    }

    public function afterHighlightCodeBlock($content)
    {
        $content = str_replace(array_keys($this->blocks), array_values($this->blocks), $content);

        return $content;
    }

    public function beforeProtectComment($content)
    {
        $helper = $this;
        $content = preg_replace_callback(
            '#(\s*)(\[cc[^\s\]_]*(?:_[^\s\]]*)?[^\]]*\].*?\[/cc\1\])(\s*)#si',
            function ($matches) use ($helper, $content) {
                return $helper->performProtectComment($matches[2], $content, $matches[1], $matches[3]);
            },
            $content
        );
        $content = preg_replace_callback(
            '#(\s*)(\<code.*?\>.*?\</code\>)(\s*)#si',
            function ($matches) use ($helper, $content) {
                return $helper->performProtectComment($matches[2], $content, $matches[1], $matches[3]);
            },
            $content
        );

        return $content;
    }

    public function afterProtectComment($content)
    {
        $content = str_replace(array_keys($this->comments), array_values($this->comments), $content);
        $this->comments = array();

        return $content;
    }

    /**
     * Perform code highlighting.
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    private function performHighlightCodeBlock($text, $opts, $content, $suffix = '', $before = '', $after = '')
    {
        // Parse options
        $options = CodeColorerOptions::parseOptions($opts, $suffix);

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
            $text = preg_replace_callback(
                '~&#x0*([0-9a-f]+);~i',
                function ($matches) {
                    return chr(hexdec($matches[1]));
                },
                $text
            );
            $text = preg_replace_callback(
                '~&#0*([0-9]+);~',
                function ($matches) {
                    return chr($matches[1]);
                },
                $text
            );
        }

        $result = '';
        // Check if CodeColorer has been disabled for this particular block
        if (!$options['enabled']) {
            $result = '<code>' . $text . '</code>';
        } else {
          // See if we should force a height
            $numLines = count(explode("\n", $text));

            $result = $this->performHighlightGeshi($text, $options);

            $result = $this->addContainer($result, $options, $numLines);
        }

        if ($options['inline']) {
            $blockID = $this->getBlockID($content, false, '<span>', '</span>');
        } else {
            $blockID = $this->getBlockID($content);
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
    private function performProtectComment($text, $content, $before, $after)
    {
        $text = str_replace(array("\\\"", "\\\'"), array ("\"", "\'"), $text);

        $blockID = $this->getBlockID($content, true, '', '');
        $this->comments[$blockID] = $text;

        return $before . $blockID . $after;
    }

    /**
     * Perform code highlighting using GESHi engine
     */
    private function performHighlightGeshi($content, $options)
    {
        /* Geshi configuration */
        if (!$this->geshi) {
            $this->geshi = new GeSHi();
            $this->geshi->enable_line_numbers(GESHI_NO_LINE_NUMBERS, 1);
            if (is_feed()) {
                $this->geshi->set_overall_style(CodeColorerOptions::feedOverallStyle());
            }
        }

        $geshi = $this->geshi;
        $geshi->set_source($content);
        $geshi->set_language($options['lang']);
        $geshi->set_overall_class('codecolorer');
        $geshi->set_tab_width($options['tab_size']);
        if (!is_feed()) {
            $geshi->enable_classes($options['theme'] != 'geshi');
            $geshi->set_overall_style('');
            if ($options['nowrap']) {
                $geshi->set_overall_style('white-space:nowrap');
            }
        } else {
            $geshi->enable_classes(false);
        }
        if (!is_null($options['strict'])) {
            $geshi->enable_strict_mode($options['strict']);
        }
        if (isset($options['no_links']) && $options['no_links']) {
            $geshi->enable_keyword_links(false);
        }
        if (isset($options['highlight'])) {
            $hlines = explode(',', $options['highlight']);
            $highlight = array(); /* Empty array to store processed line numbers*/
            foreach ($hlines as $v) {
                list($from, $to) = array_pad(explode('-', $v, 2), 2, null);
                if (is_null($to)) {
                    $to = $from;
                }
                for ($i = $from; $i <= $to; $i++) {
                    array_push($highlight, $i);
                }
            }
            /* Sort the array in ascending numerical order */
            sort($highlight);
            $geshi->highlight_lines_extra($highlight);
            $geshi->set_highlight_lines_extra_style('background-color:#ffff66');
        }
        $geshi->set_header_type($options['inline'] ? GESHI_HEADER_NONE : GESHI_HEADER_DIV);

        $result = $geshi->parse_code();

        if ($geshi->error()) {
            return $geshi->error();
        }

        if ($options['line_numbers'] && !$options['inline']) {
            $table = '<table cellspacing="0" cellpadding="0"><tbody><tr><td ';
            if (is_feed()) {
                $table .= 'style="' . CodeColorerOptions::feedLineNumbersStyle() . '"';
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

    private function addContainer($html, $options, $numLines)
    {
        $customCSSClass = empty($options['class']) ? '' : ' ' . $options['class'];
        if ($options['inline']) {
            $theme = empty($options['inline_theme']) ? 'default' : $options['inline_theme'];
            $result  = '<code class="codecolorer ' . $options['lang'] . ' ' . $theme . $customCSSClass . '">';
            $result .= '<span class="' . $options['lang'] . '">' . $html . '</span>';
            $result .= '</code>';
        } else {
            $theme = empty($options['theme']) ? 'default' : $options['theme'];
            $style = 'style="';
            if ($options['nowrap']) {
                $style .= 'overflow:auto;white-space:nowrap;';
            }
            if (is_feed()) {
                $style .= 'border:1px solid #9F9F9F;';
            }
            $style .= $this->getDimensionRule('width', is_feed() ? $options['rss_width'] : $options['width']);
            if ($numLines > $options['lines'] && $options['lines'] > 0) {
                $style .= $this->getDimensionRule('height', $options['height']);
            }
            $style .= '"';

            $cssClass = 'codecolorer-container ' . $options['lang'] . ' ' . $theme . $customCSSClass;
            if ($options['noborder']) {
                $cssClass .= ' codecolorer-noborder';
            }
            $result = '<div class="' . $cssClass . '" ' . $style . '>' . $html . '</div>';
        }
        return $result;
    }

    /**
     * Generate a block ID that will be replaced at the end (after all that
     * crazy WP text work!) with the right code
     */
    private function getBlockID($content, $comment = false, $before = '<div>', $after = '</div>')
    {
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

    private function getDimensionRule($dimension, $value)
    {
        if (empty($value)) {
            return '';
        }
        return "$dimension:$value" . (is_numeric($value) ? 'px;' : ';');
    }

    public function showWarning($type, $title, $message)
    {
        $disable = ' <a href="options-general.php?page=codecolorer.php&amp;disable=' . $type . '">' . __('Close', 'codecolorer') . '</a>';
        echo '<div id="codecolorer-' . $type . '" class="updated fade"><p><strong>' . $title . "</strong> " . $message . $disable . "</p></div>\n";
    }

    public function showGeshiWarning()
    {
        if ($this->geshiExternal) {
            $this->showWarning('concurrent', __('CodeColorer has detected a problem.', 'codecolorer'), sprintf(__('We found another plugin based on GeSHi library in your system. CodeColorer will work, but our version of GeSHi contain some patches, so we can\'t guarantee an ideal code highlighting now. Please review your <a href="%1$s">plugins</a>, maybe you don\'t need them all.', 'codecolorer'), "plugins.php"));
        }
    }

    public function showOptionsPage()
    {
        $page = $this->getOptionsPage();
        $page->show();
    }

    private function getOptionsPage()
    {
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

    public function getCodeHighlighted($code)
    {
        $content = $this->beforeHighlightCodeBlock($code);
        return $this->afterHighlightCodeBlock($content);
    }

    public function getSampleCodeHighlighted()
    {
        return $this->getCodeHighlighted($this->samplePhpCode);
    }

    public static function &getInstance()
    {
        static $instance = null;

        if (null === $instance) {
            $path = dirname(__FILE__);
            if (!class_exists('CodeColorerOptions')) {
                if (!file_exists("$path/codecolorer-options.php")) {
                    return null;
                }
                require_once("$path/codecolorer-options.php");
            }

            $instance = new CodeColorer();

            # Maybe GeSHi has been loaded by some another plugin?
            if (!class_exists('GeSHi')) {
                if (!file_exists("$path/lib/geshi.php")) {
                    return null;
                }
                require_once("$path/lib/geshi.php");
            } else {
                $instance->geshiExternal = true;
            }
        }

        return $instance;
    }
}
