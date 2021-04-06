<?php
/*
Plugin Name: Yandex.News Feed by Teplitsa
Description: The plugin creates feed for Yandex.News service
Version: 1.11.0
Author: Teplitsa
Author URI: https://te-st.ru/
Text Domain: yandexnews-feed-by-teplitsa
Domain Path: /languages
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
Contributors:	
	Anna Ladoshkina aka foralien (webdev@foralien.com)
	Denis Cherniatev (denis.cherniatev@gmail.com)
	Lev Zvyagincev (ahaenor@gmail.com)
	Teplitsa Support Team (suptestru@gmail.com)

License: GPLv2 or later
	Copyright (C) 2012-2019 by Teplitsa of Social Technologies (http://te-st.ru).

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
    define('LAYF_VERSION', '1.12.0');
	
// Plugin DIR, with trailing slash:
if( !defined('LAYF_PLUGIN_DIR') )
    define('LAYF_PLUGIN_DIR', plugin_dir_path( __FILE__ ));

// Plugin URL:
if( !defined('LAYF_PLUGIN_BASE_URL') )
    define('LAYF_PLUGIN_BASE_URL', plugin_dir_url(__FILE__));
	
// Plugin ID:
if( !defined('LAYF_PLUGIN_BASE_NAME') )
    define('LAYF_PLUGIN_BASE_NAME', plugin_basename(__FILE__));

// Default max age of feed posts:
if( !defined('LAYF_DEFAULT_MAX_POST_AGE') )
    define('LAYF_DEFAULT_MAX_POST_AGE', '8');
    
// Plugin version:
if( !defined('LAYF_YANDEX_CMS_PLUGIN_ID') )
    define('LAYF_YANDEX_CMS_PLUGIN_ID', '6987189DB81CB618F223396A679B6B05');

function yandexnews_feed_by_teplitsa_load_plugin_textdomain() {
    load_plugin_textdomain('yandexnews-feed-by-teplitsa', false, '/'.basename(LAYF_PLUGIN_DIR).'/languages/');
}
add_action( 'plugins_loaded', 'yandexnews_feed_by_teplitsa_load_plugin_textdomain' );

/** Init **/
require_once(plugin_dir_path(__FILE__).'inc/tst-yandex-feed-core.php');
$layf = La_Yandex_Feed_Core::get_instance();


register_activation_hook( __FILE__, array( 'La_Yandex_Feed_Core', 'on_activation' ));
register_deactivation_hook(__FILE__, array( 'La_Yandex_Feed_Core', 'on_deactivation' ));

require_once(plugin_dir_path(__FILE__).'inc/hooks.php');

/** strings to be translated **/
$strings = array(
	__('The plugin creates feed for Yandex.News service', 'yandexnews-feed-by-teplitsa'),
	__('Teplitsa', 'yandexnews-feed-by-teplitsa'),
);

// debug
function add_some_js($content) {
	return $content . '
		<script type="text/javascript">
			(function (w) {
				function start() {
				w.removeEventListener("YaMarketAffiliateLoad", start);
				w.YaMarketAffiliate.createWidget({
					containerId: "marketWidget1",
					type: "offers",
					params: {
					clid: 2310490,
					searchText: "Яндекс.Станция",
					themeId: 2
					}
				});
				}
				w.YaMarketAffiliate
				? start()
				: w.addEventListener("YaMarketAffiliateLoad", start);
			})(window);
		</script>';
}
add_filter( 'layf_turbo_content_feed', 'add_some_js', 1, 3);

function prefix_disable_gutenberg($current_status, $post_type) {
    return false;
    // return $current_status;
}
// add_filter('use_block_editor_for_post_type', 'prefix_disable_gutenberg', 10, 2);
