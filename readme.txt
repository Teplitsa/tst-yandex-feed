=== Yandex.News Feed by Teplitsa ===
Contributors: foralien, denis.cherniatev, ahaenor
Tags: yandex,news,xml,rss,seo,Yandex.News
Requires at least: 3.9
Tested up to: 4.2.3
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.txt

Трансляция материалов сайта для сервиса Яндекс.Новости.

== Description ==
Yandex.News Feed by Teplitsa - плагин для WordPress, позволяющий организовать трансляцию материалов сайта для сервиса Яндекс.Новости.

_For English description scroll down, please._

Задача плагина - облегчить интеграцию любого сайта на WordPress с Яндекс.Новостями, позволив авторам сайта избежать кастомных технических решений.

* Плагин элементарно устанавливается и требует минимум настроек.
* Функции трансляции записей в Яндекс.Новости доступны сразу после установки.

Плагин разработан и поддерживается [Теплицей социальных технологий](http://te-st.ru/).


**Основные функции**

* Обеспечение строгой и точной поддержки [формата трансляции Яндекс.Новостей](http://help.yandex.ru/news/info-for-mass-media.xml).
* Настройка пользовательских (кастомных) типов записей, попадающих в трансляцию.
* Фильтрация по категории (или кастомной таксономии), попадающих в трансляцию.
* При редактировании записей доступен метабокс для перечня ссылок на источники, упомянутые в статье, а также для индивидуального исключения записи из трансляции.

После установки настройки плагина доступны через меню _Настройки -> Яндекс.Новости_.

Трансляция (фид) доступна для просмотра по ссылке _domain.ru/yandex/news/_. В настройках может быть указан собственный адрес, которые работает при активных "красивых пермалинках".

Плагин имеет минимум необходимых настроек. Подробнее о его использовании можно узнать на сайте разработчиков:

* [руководство](http://te-st.ru/2014/12/02/wordpress-and-yandex-news/) по использованию плагина;
* [скринкаст](http://te-st.ru/2014/04/08/screencast-yandex-news-plugin/) по использованию плагина; 


**Помощь проекту**

Мы будем очень благодарны за вашу помощь проекту. Вы можете помочь следующими способами:

* Добавить сообщение об ошибке или предложение по улучшению на [GitHub](https://github.com/Teplitsa/tst-yandex-feed).
* Поделиться улучшениями кода, послав нам Pull Request.
* Сделать перевод плагина или оптимизировать его для вашей страны.

Если у вас есть вопросы по работе плагина, то обратитесь за поддержкой с помощью [GitHub](https://github.com/Teplitsa/tst-yandex-feed).


**IN ENGLISH**

Yandex.News Feed by Teplitsa - is the plugin for WordPress that allows you to convert your site materials  into Yandex.News format.

The goal of the plugin is to simplify the integration of any WordPress-powered website with Yandex.News.

* The installation process is smooth and requires minimum settings.
* Feed compatible with Yandex.News format is available immediately after installation.

The plugin is developed and maintained by [Teplitsa of social technologies](http://te-st.ru/).


**Features**

* Compatibility with Yandex.News [guidelines](http://help.yandex.ru/news/info-for-mass-media.xml).
* Custom post types support in feed.
* Filtering by category or custom taxonomy term.
* Individual settings for posts in feed.

After installing the plugin settings are available under menu _Settings -> Yandex.Novosti_.

Feed is accessible at the link _domain.ru/yandex/news/_. A custom URL could be specify through Settings page in case of active "pretty permalinks".

The plugin has the minimum of settings. Read more about it's usage at the developers' website:

* [detailed guide](http://te-st.EN/2014/12/02/wordpress-and-yandex-news/) about plugin usage;
* [screencast](http://te-st.EN/2014/04/08/screencast-yandex-news-plugin/) about plugin usage;


**Help us**

We will be very grateful for your help us to make the plugin better. You can do it in the following ways:

* Report bugs or suggest improvements at [GitHub](https://github.com/Teplitsa/tst-yandex-feed).
* Send us a Pull Request with your fixes or improvements.
* Translate the plugin or optimize it for your country.

If you have questions about the plugin, then ask for support through [GitHub](https://github.com/Teplitsa/tst-yandex-feed).


== Installation ==
Процесс инсталляции плагина стандартен для WordPress.

Если у вас установлен GIT, то вы можете клонировать репозиторий: https://github.com/Teplitsa/tst-yandex-feed.git
или скачать его в виде ZIP архива: https://github.com/Teplitsa/tst-yandex-feed/archive/master.zip

== Screenshots ==

1. Пример формата выдачи
2. Пример страницы настроек плагина


== Changelog ==

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