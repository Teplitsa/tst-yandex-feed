<?php
/*
Plugin Name: LA Yandex Feed
Description: The plugin creates feed for Yandex.News service
Version: 1.0
Author: Teplitsa
Author URI: http://te-st.ru/
Contributors:	
	Anna Ladoshkina aka foralien (webdev@foralien.com)	

License: GPLv2 or later

	Copyright (C) 2012-2013 by Teplitsa of Social Technologies (http://te-st.ru).

	GNU General Public License, Free Software Foundation <http://www.gnu.org/licenses/gpl-2.0.html>

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/

if( !defined('ABSPATH') ) exit; // Exit if accessed directly

define('LAYF_PLUGIN_DIR', plugin_dir_path( __FILE__ ));

load_plugin_textdomain('layf', false, '/la-yandex-feed/languages');


/** Init **/
require_once(plugin_dir_path(__FILE__).'inc/la-yandex-feed-core.php');
$layf = La_Yandex_Feed_Core::get_instance();


register_activation_hook( __FILE__, array( 'La_Yandex_Feed_Core', 'on_activation' ));
register_deactivation_hook(__FILE__, array( 'La_Yandex_Feed_Core', 'on_deactivation' ));


