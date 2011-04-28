<?php
/*
CodeColorer plugin admin part
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

class CodeColorerAdmin {
  function CodeColorerAdmin($codeColorer) {
    $this->cc = $codeColorer;
    $this->DisableNotifications();
  }

  function ShowThemeSelectOptions($current_theme) {
    foreach(CodeColorerOptions::GetThemes() as $theme => $description) {
      echo '<option value="' . $theme . '"';
      if ($theme == $current_theme) echo ' selected="selected"';
      echo '>' . htmlspecialchars($description) . '</option>';
    }
  }

  function ShowLanguageWarning() {
    $locale = get_locale();
    if (!in_array($locale, CodeColorerOptions::GetLanguages())) {
      $msgFormat = __('Your current locale is %1$s, and CodeColorer has incomplete or does not have a translation into your language. It would be great, if you have a time to <a href="%2$s">help us to translate</a> it.', 'codecolorer');
      $this->cc->ShowWarning('language', __('CodeColorer translation is incomplete.', 'codecolorer'), sprintf($msgFormat, $locale, "http://kpumuk.info/projects/wordpress-plugins/codecolorer/#translation"));
    }
  }

  function DisableNotifications() {
    if (isset($_GET['disable']) && in_array($_GET['disable'], array('concurrent', 'language'))) {
      update_option('codecolorer_' . $_GET['disable'] . '_notification', false);
    }
  }

  function Show() {
    ?>

    <div class="wrap">
      <?php screen_icon(); ?>
      <h2>CodeColorer: <?php _e('Code Highlighting Options', 'codecolorer') ?></h2>

      <?php if (get_option('codecolorer_language_notification')) $this->ShowLanguageWarning(); ?>

      <form name="form1" method="post" action="options.php">
        <?php settings_fields('codecolorer') ?>

        <table width="100%" cellpadding="5" class="form-table">
          <tr valign="top">
            <th scope="row"><label for="codecolorer_lines_to_scroll"><?php _e('Lines to scroll', 'codecolorer') ?>:</label></th>
            <td>
              <input name="codecolorer_lines_to_scroll" type="text" class="small-text" size="60" id="codecolorer_lines_to_scroll" value="<?php echo get_option('codecolorer_lines_to_scroll') ?>"/>
              <span class="description"><?php _e('If your code lines number is less than this value, block height would not be fixed. Set to <b>-1</b> to remove vertical scroll.', 'codecolorer') ?></span>
            </td>
          </tr>

          <tr valign="top">
            <th scope="row"><label for="codecolorer_width"><?php _e('Width', 'codecolorer') ?>:</label></th>
            <td>
              <input name="codecolorer_width" type="text" class="small-text" size="60" id="codecolorer_width" value="<?php echo get_option('codecolorer_width') ?>"/>
              <span class="description"><?php _e('Default code block width. Integer means pixels, also you can specify <tt>em</tt> or <tt>%</tt> suffix. Could be omitted to use whole width.', 'codecolorer') ?></span>
            </td>
          </tr>

          <tr valign="top">
            <th scope="row"><label for="codecolorer_height"><?php _e('Height', 'codecolorer') ?>:</label></th>
            <td>
              <input name="codecolorer_height" type="text" class="small-text" size="60" id="codecolorer_height" value="<?php echo get_option('codecolorer_height') ?>"/>
              <span class="description"><?php _e('When code has more than &quot;Lines to Scroll&quot; lines, block height will be set to this value.', 'codecolorer') ?></span>
            </td>
          </tr>

          <tr valign="top">
            <th scope="row"><label for="codecolorer_rss_width"><?php _e('Width in RSS', 'codecolorer') ?>:</label></th>
            <td>
              <input name="codecolorer_rss_width" type="text" class="small-text" size="60" id="codecolorer_rss_width" value="<?php echo get_option('codecolorer_rss_width') ?>"/>
              <span class="description"><?php _e('Default code block width in RSS. See Width option description.', 'codecolorer') ?></span>
            </td>
          </tr>

          <tr valign="top">
            <th scope="row"><label for="codecolorer_tab_size"><?php _e('Tab size', 'codecolorer') ?>:</label></th>
            <td>
              <input name="codecolorer_tab_size" type="text" class="small-text" size="60" id="codecolorer_tab_size" value="<?php echo get_option('codecolorer_tab_size') ?>"/>
              <span class="description"><?php _e('Indicating how many spaces would represent TAB symbol.', 'codecolorer') ?></span>
            </td>
          </tr>

          <tr valign="top">
            <th scope="row"><label for="codecolorer_theme"><?php _e('Theme', 'codecolorer') ?>:</label></th>
            <td>
              <select name="codecolorer_theme" id="codecolorer_theme">
                <?php $this->ShowThemeSelectOptions(get_option('codecolorer_theme')) ?>
              </select>
              <span class="description"><?php _e('Default color scheme.', 'codecolorer') ?></span>
            </td>
          </tr>

          <tr valign="top">
            <th scope="row"><label for="codecolorer_inline_theme"><?php _e('Inline Theme', 'codecolorer') ?>:</label></th>
            <td>
              <select name="codecolorer_inline_theme" id="codecolorer_inline_theme">
                <?php $this->ShowThemeSelectOptions(get_option('codecolorer_inline_theme')) ?>
              </select>
              <span class="description"><?php _e('Default color scheme for inline code blocks.', 'codecolorer') ?></span>
            </td>
          </tr>

          <tr valign="top">
            <th scope="row"><?php _e('Formatting', 'codecolorer') ?>:</th>
            <td>
              <label for="codecolorer_line_numbers">
                <input name="codecolorer_line_numbers" type="checkbox" id="codecolorer_line_numbers" value="1" <?php checked(TRUE, get_option('codecolorer_line_numbers')); ?> />
                <?php _e('Show line numbers', 'codecolorer') ?>
              </label><br />

              <label for="codecolorer_disable_keyword_linking">
                <input name="codecolorer_disable_keyword_linking" type="checkbox" id="codecolorer_disable_keyword_linking" value="1" <?php checked(TRUE, get_option('codecolorer_disable_keyword_linking')); ?> />
                <?php _e('Disable keyword linking', 'codecolorer') ?>
              </label>
            </td>
          </tr>

          <tr valign="top">
            <th scope="row"><label for="codecolorer_css_class"><?php _e('Custom CSS Classes', 'codecolorer') ?>:</label></th>
            <td>
              <input name="codecolorer_css_class" type="text" class="regular-text" size="60" id="codecolorer_css_class" value="<?php echo get_option('codecolorer_css_class') ?>"/>
              <span class="description"><?php _e('These custom CSS classes will be appended to the wrapper HTML element.', 'codecolorer') ?></span>
            </td>
          </tr>

          <tr valign="top">
            <th scope="row"><label for="codecolorer_css_style"><?php _e('Custom CSS Styles', 'codecolorer') ?>:</label></th>
            <td>
              <textarea name="codecolorer_css_style" id="codecolorer_css_style" rows="5" cols="60"><?php echo htmlspecialchars(get_option('codecolorer_css_style')) ?></textarea><br />
              <span class="description"><?php _e('These custom CSS rules will be appended to the CodeColorer default CSS file.', 'codecolorer') ?></span>
            </td>
          </tr>
        </table>

        <h3><?php _e('Preview', 'codecolorer') ?></h3>

        <table width="100%" cellpadding="5" class="form-table">
          <tr valign="top">
            <th scope="row"><label for="codecolorer_css_style"><?php _e('Code Example', 'codecolorer') ?>:</label></th>
            <td>
              <?php echo $this->cc->GetSampleCodeHighlighted(); ?>
            </td>
          </tr>
        </table>

        <p class="submit">
          <input type="submit" class="button-primary" name="Submit" value="<?php _e('Save Options', 'codecolorer') ?> &raquo;" />
        </p>
      </form>
    </div>

    <?php
  }
}
