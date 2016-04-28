<?php
/*
Plugin Name: Yandex.News Feed by Teplitsa
Description: The plugin creates feed for Yandex.News service
Version: 1.8.6
Author: Teplitsa
Author URI: http://te-st.ru/
Text Domain: layf
Domain Path: /languages
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
Contributors:	
	Anna Ladoshkina aka foralien (webdev@foralien.com)
	Denis Cherniatev (denis.cherniatev@gmail.com)
	Lev Zvyagincev (ahaenor@gmail.com)

License: GPLv2 or later
	Copyright (C) 2012-2016 by Teplitsa of Social Technologies (http://te-st.ru).

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

if(!defined('ABSPATH')) die; // Die if accessed directly

// Plugin version:
if( !defined('LAYF_VERSION') )
    define('LAYF_VERSION', '1.8.5');
	
// Plugin DIR, with trailing slash:
if( !defined('LAYF_PLUGIN_DIR') )
    define('LAYF_PLUGIN_DIR', plugin_dir_path( __FILE__ ));

// Plugin URL:
if( !defined('LAYF_PLUGIN_BASE_URL') )
    define('LAYF_PLUGIN_BASE_URL', plugin_dir_url(__FILE__));
	
// Plugin ID:
if( !defined('LAYF_PLUGIN_BASE_NAME') )
    define('LAYF_PLUGIN_BASE_NAME', plugin_basename(__FILE__));

load_plugin_textdomain('layf', false, '/'.basename(LAYF_PLUGIN_DIR).'/languages');


/** Init **/
require_once(plugin_dir_path(__FILE__).'inc/tst-yandex-feed-core.php');
$layf = La_Yandex_Feed_Core::get_instance();


register_activation_hook( __FILE__, array( 'La_Yandex_Feed_Core', 'on_activation' ));
register_deactivation_hook(__FILE__, array( 'La_Yandex_Feed_Core', 'on_deactivation' ));


/** strings to be translated **/
$strings = array(
__('The plugin creates feed for Yandex.News service', 'layf'),
__('Teplitsa', 'layf'),
);