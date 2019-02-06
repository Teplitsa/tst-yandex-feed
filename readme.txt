=== Yandex.News Feed by Teplitsa ===
Contributors: foralien, denis.cherniatev, ahaenor, teplosup
Tags: yandex,Турбо,Яндекс,новости,news,Турбо-страницы,xml,rss,seo,turbo,turbo pages
Requires at least: 3.9
Tested up to: 4.9.8
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.txt

Yandex.News Feed by Teplitsa - allows you to convert your site materials into Yandex News format with turbo pages support.

The goal of the plugin is to simplify the integration of any WordPress-powered website with Yandex.News.

* The installation process is smooth and requires minimum settings.
* Feed compatible with Yandex.News format is available immediately after installation.

The plugin is developed and maintained by [Teplitsa of social technologies](http://te-st.ru/).


**Features**

* Compatibility with Yandex.News [guidelines](http://help.yandex.ru/news/info-for-mass-media.xml).
* Yandex turbo-pages support.
* Custom post types support in feed.
* Filtering by category or custom taxonomy term.
* Individual settings for posts in feed.
* If the feed generation process overloads your DB server, you can enable cache. Just set cache lifetime value.

After installing the plugin settings are available under menu _Settings -> Yandex.Novosti_.

Feed is accessible at the link _domain.ru/yandex/news/_. A custom URL could be specify through Settings page in case of active "pretty permalinks".

The plugin has the minimum of settings. Read more about it's usage at the developers' website:

* [detailed guide](https://te-st.ru/2014/12/02/wordpress-and-yandex-news/) about plugin usage;
* [screencast](https://te-st.ru/2014/04/08/screencast-yandex-news-plugin/) about plugin usage;


**Help us**

We will be very grateful for your help us to make the plugin better. You can do it in the following ways:

* Report bugs or suggest improvements at [GitHub](https://github.com/Teplitsa/tst-yandex-feed).
* Send us a Pull Request with your fixes or improvements.
* Translate the plugin or optimize it for your country.

If you have questions about the plugin, then ask for support through [GitHub](https://github.com/Teplitsa/tst-yandex-feed).


== Installation ==
Installation process is typical for WordPress.

You can also use GIT: https://github.com/Teplitsa/tst-yandex-feed.git
or download as ZIP: https://github.com/Teplitsa/tst-yandex-feed/archive/master.zip

== Screenshots ==

1. Feed sample
2. Settings page sample


== Changelog ==

= 1.10.8 =
* Update: Compatibility with PHP5.3 restored.
* Update: Caching optimized.

= 1.10.7 =
* Update: Default min feed items limit removed. Now less than 300 records allowed.
* Update: Figure video tags support added.
* Update: Caching improved.

= 1.10.6 =
* Update: Escape special chars in item description improved.

= 1.10.5 =
* Update: Embed shortcode support added.

= 1.10.4 =
* Update: Compatibility with WP Multilang plugin added.

= 1.10.3 =
* Update: Some Turbo settings moved to Yandex.Webmaster.
* Update: Turbo-pages limit settings updated.

= 1.10.2 =
* Update: New tags allowed.
* Update: Yandex Plugin ID added.

= 1.10.1 =
* Fix: Timezone negative UTC offset bug fixed.
* Update: Authors list extended.

= 1.10.0 =
* New: Yandex Ad Network blocks support added.
* New: Analytics support added.
* Update: mp4 video support added.
* Fix: Protocol issue in enclosure resolved.
* Fix: "More" tag removed from short description.

= 1.9.1 =
* New: Option to set max age of the feed posts added.
* Update: Yandex turbo-content header composition even for posts without thumbnails.
* Update: Links to useful articles fixed.

= 1.9 =
* Update: Yandex turbo-pages support added.

= 1.8.13 =
* Update: Readme updated.

= 1.8.12 =
* Update: External URLs removed from enclosures list.

= 1.8.11 =
* Fix: Feed limit fixed.

= 1.8.10 =
* New: Terms slug support added in tax filter.
* New: Terms slug support added in tax exclude filter.
* New: Clear cache occurs when save empty cache lifetime.

= 1.8.9 =
* New: Feed cache added. Generated feed cache is stored in WP options table. Try to turn it on if feed generation overloads you DB server.
* New: Cache lifetime option added.

= 1.8.8 =
* New: Exclude terms feature added from h8every1 pull request: https://github.com/Teplitsa/tst-yandex-feed/pull/11
* Fix: Text domain changed

= 1.8.7 =
* Fix: Feed optimized

= 1.8.6 =
* New: Remove unused shortcodes option added
* New: Remove pdalink tag option added
* New: Remove teaser from yandex:full-text option added
* New: Feed length optional limit added
* Fix: Feed Content-type fixed for WordPress 4.5

= 1.8.5 =
* New: thumbnails in feed replaced to original images
* New: Option to include or exclude featured image from feed added
* Fix: youtube links parsing improved
* Fix: duplicated enclosures removed

= 1.8.4.2 =
* Fix: media:group structure optimized

= 1.8.4.1 =
* Fix: Size of youtube video thumbnails changed

= 1.8.4 =
* New: Youtube video thumbnails added
* Fix: media:group structure optimized

= 1.8.3 =
* Fix: Minor fixes and updates for feed content

= 1.8.2 =
* Fix: Minor fixes and updated for admin settings

= 1.8.1 =
* Fix: Incorrect custom URL behaviour on existing installs

= 1.8 =
* New: Support for YouTube video embedded in the post content
* News: Custom URL for the feed (with pretty permalinks active)
* Fix: Image caption text stripped out from the translation context

= 1.7 =
* New: Added support for new Yandex square logo format update

= 1.6 =
* Fix: Minor fixes in plugin dashboard area

= 1.5 =
* New: Own options page for plugin settings
* New: Posts in feed could be filtering by category or custom taxonomy term
* New: Posts could be excluded from feed with individual setting

= 1.4 =
* Fix: Incorrect formatting filtering applyed for the full feed content

= 1.3 =
* Fix: Inline styles appear in feed content

= 1.2 =
* Fix: Category field should contains only one category label
* Fix: Some shortcodes appeared incorrectly in the feed content

= 1.1 =
* Fix: Some invalid characters appear in feed
* Fix: Security fix
* Fix: Translation files not loading
* Fix: Incorrect content behaviour due to conflicts with some themes

= 1.0 =
* First official release!