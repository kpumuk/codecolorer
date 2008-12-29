<?php
/*
CodeColorer plugin options part
http://kpumuk.info/projects/wordpress-plugins/codecolorer
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

load_plugin_textdomain('codecolorer', 'wp-content/plugins/codecolorer'); // NLS
include_once('codecolorer.php');
$CodeColorer = new CodeColorer();

$location = get_option('siteurl') . '/wp-admin/admin.php?page=codecolorer/codecolorer-options.php'; // Form Action URI

/* Add some default options if they don't exist */
add_option('codecolorer_line_numbers', false);
add_option('codecolorer_css_style', $CodeColorer->getDefaultStyle());
add_option('codecolorer_lines_to_scroll', $CodeColorer->getDefaultLinesToScroll());
add_option('codecolorer_line_height', $CodeColorer->getDefaultLineHeight());
add_option('codecolorer_disable_keyword_linking', false);
add_option('codecolorer_tab_size', 4);

/* Check form submission and update options */
if ('process' == $_POST['stage']) {
  update_option('codecolorer_css_style', $_POST['codecolorer_css_style']);
  update_option('codecolorer_lines_to_scroll', intval($_POST['codecolorer_lines_to_scroll']));
  update_option('codecolorer_line_height', intval($_POST['codecolorer_line_height']));
  update_option('codecolorer_line_numbers', isset($_POST['codecolorer_line_numbers']));
  update_option('codecolorer_disable_keyword_linking', isset($_POST['codecolorer_disable_keyword_linking']));
  update_option('codecolorer_tab_site', intval($_POST['codecolorer_tab_size']));
}

/* Get options for form fields */
$codecolorer_line_numbers = stripslashes(get_option('codecolorer_line_numbers'));
$codecolorer_css_style = stripslashes(get_option('codecolorer_css_style'));
$codecolorer_lines_to_scroll = stripslashes(get_option('codecolorer_lines_to_scroll'));
$codecolorer_line_height = stripslashes(get_option('codecolorer_line_height'));
$codecolorer_disable_keyword_linking = stripslashes(get_option('codecolorer_disable_keyword_linking'));
$codecolorer_tab_size = stripslashes(get_option('codecolorer_tab_size'));
?>

<div class="wrap"> 
  <h2><?php _e('Code Highlighting Options', 'codecolorer') ?></h2> 
  <form name="form1" method="post" action="<?php echo $location ?>&amp;updated=true">
  	<input type="hidden" name="stage" value="process" />

    <table width="100%" cellpadding="5" class="form-table"> 
      <tr valign="top">
        <th scope="row"><label for="codecolorer_css_style"><?php _e('CSS Style', 'codecolorer') ?>:</label></th>
        <td>
          <input name="codecolorer_css_style" type="text"  size="60" id="codecolorer_css_style" value="<?php echo $codecolorer_css_style ?>"/>
        </td>
      </tr>

      <tr valign="top">
        <th scope="row"><label for="codecolorer_lines_to_scroll"><?php _e('Lines to scroll', 'codecolorer') ?>:</label></th>
        <td>
          <input name="codecolorer_lines_to_scroll" type="text" class="small-text" size="60" id="codecolorer_lines_to_scroll" value="<?php echo $codecolorer_lines_to_scroll ?>"/>
          <span class="setting-description"><?php _e('If your code lines number is less than this value, block height would not be fixed.', 'codecolorer') ?></span>
  	    </td>
      </tr>

      <tr valign="top">
        <th scope="row"><label for="codecolorer_line_height"><?php _e('Line height', 'codecolorer') ?> (px):</label></th>
        <td>
          <input name="codecolorer_line_height" type="text" class="small-text" size="60" id="codecolorer_line_height" value="<?php echo $codecolorer_line_height ?>"/>
          <span class="setting-description"><?php _e('Used to calculate block height when lines number is more than &quot;Lines to scroll&quot; value.', 'codecolorer') ?></span>
  	    </td>
      </tr>

      <tr valign="top">
        <th scope="row"><label for="codecolorer_tab_size"><?php _e('Tab size', 'codecolorer') ?>:</label></th>
        <td>
          <input name="codecolorer_tab_size" type="text" class="small-text" size="60" id="codecolorer_tab_size" value="<?php echo $codecolorer_tab_size ?>"/>
          <span class="setting-description"><?php _e('how many spaces would represent TAB symbol.', 'codecolorer') ?></span>
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
    </table>
    
    <h3><?php _e('Preview', 'codecolorer') ?></h3>
    
    <table width="100%" cellpadding="5" class="form-table"> 
      <tr valign="top">
        <th scope="row"><label for="codecolorer_css_style"><?php _e('Example', 'codecolorer') ?>:</label></th>
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