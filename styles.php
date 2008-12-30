<?php
/*
CodeColorer plugin styles merger part
http://kpumuk.info/projects/wordpress-plugins/codecolorer
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

require dirname(__FILE__) . '/../../../wp-load.php';

header('Content-Type: text/css');
echo file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'codecolorer.css');
echo "\n/* Custom styles */\n";
echo stripslashes(get_option('codecolorer_css_style'));

?>