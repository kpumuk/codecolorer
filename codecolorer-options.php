<?php
/*
CodeColorer plugin options part
http://kpumuk.info/projects/wordpress-plugins/codecolorer
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

load_plugin_textdomain('codecolorer', 'wp-content/plugins/codecolorer'); // NLS
include_once('codecolorer.php');
$CodeColorer = new CodeColorer();

$location = get_option('siteurl') . '/wp-admin/admin.php?page=codecolorer/codecolorer-options.php'; // Form Action URI

/* Add some default options if they don't exist */
add_option('codecolorer_css_style', '');
add_option('codecolorer_lines_to_scroll', $CodeColorer->getDefaultLinesToScroll());
add_option('codecolorer_width', $CodeColorer->getDefaultWidth());
add_option('codecolorer_height', $CodeColorer->getDefaultHeight());
add_option('codecolorer_rss_width', $CodeColorer->getDefaultWidth());
add_option('codecolorer_line_numbers', false);
add_option('codecolorer_disable_keyword_linking', false);
add_option('codecolorer_tab_size', 4);
add_option('codecolorer_theme', '');
add_option('codecolorer_inline_theme', '');
/* Obsolete options */
// add_option('codecolorer_line_height', $CodeColorer->getDefaultLineHeight());

/* Check form submission and update options */
if ('process' == $_POST['stage']) {
  update_option('codecolorer_css_style', $_POST['codecolorer_css_style']);
  update_option('codecolorer_lines_to_scroll', intval($_POST['codecolorer_lines_to_scroll']));
  update_option('codecolorer_width', $_POST['codecolorer_width']);
  update_option('codecolorer_height', $_POST['codecolorer_height']);
  update_option('codecolorer_rss_width', $_POST['codecolorer_rss_width']);
  update_option('codecolorer_line_numbers', isset($_POST['codecolorer_line_numbers']));
  update_option('codecolorer_disable_keyword_linking', isset($_POST['codecolorer_disable_keyword_linking']));
  update_option('codecolorer_tab_site', intval($_POST['codecolorer_tab_size']));
  update_option('codecolorer_theme', $_POST['codecolorer_theme']);
  update_option('codecolorer_inline_theme', $_POST['codecolorer_inline_theme']);
}

/* Get options for form fields */
$codecolorer_css_style = stripslashes(get_option('codecolorer_css_style'));
$codecolorer_lines_to_scroll = stripslashes(get_option('codecolorer_lines_to_scroll'));
$codecolorer_width = stripslashes(get_option('codecolorer_width'));
$codecolorer_height = stripslashes(get_option('codecolorer_height'));
$codecolorer_rss_width = stripslashes(get_option('codecolorer_rss_width'));
$codecolorer_line_numbers = stripslashes(get_option('codecolorer_line_numbers'));
$codecolorer_disable_keyword_linking = stripslashes(get_option('codecolorer_disable_keyword_linking'));
$codecolorer_tab_size = stripslashes(get_option('codecolorer_tab_size'));
$codecolorer_theme = stripslashes(get_option('codecolorer_theme'));
$codecolorer_inline_theme = stripslashes(get_option('codecolorer_inline_theme'));
?>

<div class="wrap">
  <h2>CodeColorer: <?php _e('Code Highlighting Options', 'codecolorer') ?></h2>
  <form name="form1" method="post" action="<?php echo $location ?>&amp;updated=true">
  	<input type="hidden" name="stage" value="process" />

    <table width="100%" cellpadding="5" class="form-table">
      <tr valign="top">
        <th scope="row"><label for="codecolorer_lines_to_scroll"><?php _e('Lines to scroll', 'codecolorer') ?>:</label></th>
        <td>
          <input name="codecolorer_lines_to_scroll" type="text" class="small-text" size="60" id="codecolorer_lines_to_scroll" value="<?php echo $codecolorer_lines_to_scroll ?>"/>
          <span class="setting-description"><?php _e('If your code lines number is less than this value, block height would not be fixed. Set to <b>-1</b> to remove vertical scroll.', 'codecolorer') ?></span>
  	    </td>
      </tr>

      <tr valign="top">
        <th scope="row"><label for="codecolorer_width"><?php _e('Width', 'codecolorer') ?>:</label></th>
        <td>
          <input name="codecolorer_width" type="text" class="small-text" size="60" id="codecolorer_width" value="<?php echo $codecolorer_width ?>"/>
          <span class="setting-description"><?php _e('Default code block width. Integer means pixels, also you can specify <tt>em</tt> or <tt>%</tt> suffix. Could be omitted to use whole width.', 'codecolorer') ?></span>
  	    </td>
      </tr>

      <tr valign="top">
        <th scope="row"><label for="codecolorer_height"><?php _e('Height', 'codecolorer') ?>:</label></th>
        <td>
          <input name="codecolorer_height" type="text" class="small-text" size="60" id="codecolorer_height" value="<?php echo $codecolorer_height ?>"/>
          <span class="setting-description"><?php _e('When code has more than &quot;Lines to Scroll&quot; lines, block height will be set to this value.', 'codecolorer') ?></span>
  	    </td>
      </tr>

      <tr valign="top">
        <th scope="row"><label for="codecolorer_rss_width"><?php _e('Width in RSS', 'codecolorer') ?>:</label></th>
        <td>
          <input name="codecolorer_rss_width" type="text" class="small-text" size="60" id="codecolorer_rss_width" value="<?php echo $codecolorer_rss_width ?>"/>
          <span class="setting-description"><?php _e('Default code block width in RSS. See Width option description.', 'codecolorer') ?></span>
  	    </td>
      </tr>

      <tr valign="top">
        <th scope="row"><label for="codecolorer_tab_size"><?php _e('Tab size', 'codecolorer') ?>:</label></th>
        <td>
          <input name="codecolorer_tab_size" type="text" class="small-text" size="60" id="codecolorer_tab_size" value="<?php echo $codecolorer_tab_size ?>"/>
          <span class="setting-description"><?php _e('Indicating how many spaces would represent TAB symbol.', 'codecolorer') ?></span>
  	    </td>
      </tr>

      <tr valign="top">
        <th scope="row"><label for="codecolorer_theme"><?php _e('Theme', 'codecolorer') ?>:</label></th>
        <td>
          <select name="codecolorer_theme" id="codecolorer_theme">
            <option value=""<?php if ($codecolorer_theme == '') echo ' selected="selected"' ?>>Slush &amp; Poppies</option>
            <option value="blackboard"<?php if ($codecolorer_theme == 'blackboard') echo ' selected="selected"' ?>>Blackboard</option>
            <option value="dawn"<?php if ($codecolorer_theme == 'dawn') echo ' selected="selected"' ?>>Dawn</option>
            <option value="mac-classic"<?php if ($codecolorer_theme == 'mac-classic') echo ' selected="selected"' ?>>Mac Classic</option>
            <option value="twitlight"<?php if ($codecolorer_theme == 'twitlight') echo ' selected="selected"' ?>>Twitlight</option>
            <option value="vibrant"<?php if ($codecolorer_theme == 'vibrant') echo ' selected="selected"' ?>>Vibrant Ink</option>
          </select>
          <span class="setting-description"><?php _e('Default color scheme.', 'codecolorer') ?></span>
        </td>
      </tr>

      <tr valign="top">
        <th scope="row"><label for="codecolorer_inline_theme"><?php _e('Inline Theme', 'codecolorer') ?>:</label></th>
        <td>
          <select name="codecolorer_inline_theme" id="codecolorer_inline_theme">
            <option value=""<?php if ($codecolorer_inline_theme == '') echo ' selected="selected"' ?>>Slush &amp; Poppies</option>
            <option value="blackboard"<?php if ($codecolorer_inline_theme == 'blackboard') echo ' selected="selected"' ?>>Blackboard</option>
            <option value="dawn"<?php if ($codecolorer_inline_theme == 'dawn') echo ' selected="selected"' ?>>Dawn</option>
            <option value="mac-classic"<?php if ($codecolorer_inline_theme == 'mac-classic') echo ' selected="selected"' ?>>Mac Classic</option>
            <option value="twitlight"<?php if ($codecolorer_inline_theme == 'twitlight') echo ' selected="selected"' ?>>Twitlight</option>
            <option value="vibrant"<?php if ($codecolorer_inline_theme == 'vibrant') echo ' selected="selected"' ?>>Vibrant Ink</option>
          </select>
          <span class="setting-description"><?php _e('Default color scheme for inline code blocks.', 'codecolorer') ?></span>
        </td>
      </tr>

      <tr valign="top">
        <th scope="row"><?php _e('Formatting', 'codecolorer') ?>:</th>
        <td>
          <label for="codecolorer_line_numbers">
            <input name="codecolorer_line_numbers" type="checkbox" id="codecolorer_line_numbers" value="codecolorer_line_numbers"
              <?php if($codecolorer_line_numbers == TRUE) {?> checked="checked" <?php } ?> />
              <?php _e('Show line numbers', 'codecolorer') ?>
          </label><br />

          <label for="codecolorer_disable_keyword_linking">
            <input name="codecolorer_disable_keyword_linking" type="checkbox" id="codecolorer_disable_keyword_linking" value="codecolorer_disable_keyword_linking"
              <?php if($codecolorer_disable_keyword_linking == TRUE) {?> checked="checked" <?php } ?> />
            <?php _e('Disable keyword linking', 'codecolorer') ?>
          </label>
  	    </td>
      </tr>

      <tr valign="top">
        <th scope="row"><label for="codecolorer_css_style"><?php _e('Custom CSS Styles', 'codecolorer') ?>:</label></th>
        <td>
          <textarea name="codecolorer_css_style" type="text" id="codecolorer_css_style" rows="5" cols=60><?php echo $codecolorer_css_style ?></textarea><br />
          <span class="setting-description"><?php _e('These custom CSS rules will be appended to the CodeColorer default CSS file.', 'codecolorer') ?></span>
        </td>
      </tr>
    </table>

    <h3><?php _e('Preview', 'codecolorer') ?></h3>

    <table width="100%" cellpadding="5" class="form-table">
      <tr valign="top">
        <th scope="row"><label for="codecolorer_css_style"><?php _e('Code Example', 'codecolorer') ?>:</label></th>
        <td>
          <?php echo $CodeColorer->sampleCodeFactory(); ?>
        </td>
      </tr>
    </table>

    <p class="submit">
      <input type="submit" class="button-primary" name="Submit" value="<?php _e('Save Options', 'codecolorer') ?> &raquo;" />
    </p>
  </form>
</div>