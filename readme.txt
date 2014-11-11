=== Yandex.News Feed by Teplitsa ===
Contributors: foralien, denis.cherniatev, ahaenor
Tags: yandex,news,xml,rss,seo
Requires at least: 3.9
Tested up to: 4.0
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.txt

Трансляция материалов сайта для сервиса Яндекс.Новости.

== Description ==
Yandex.News Feed by Teplita - плагин для WordPress, позволяющий организовать трансляцию материалов сайта для сервиса Яндекс.Новости.

Задача плагина - облегчить интеграцию любого сайта на WordPress с Яндекс.Новостями, позволив авторам сайта избежать кастомных технических решений.

* Плагин элементарно устанавливается и требует минимум настроек.
* Функции трансляции записей в Яндекс.Новости доступны сразу после установки.
* Плагин строго и точно поддерживает [формат трансляции для Яндекс.Новостей](http://help.yandex.ru/news/info-for-mass-media.xml).
* Фильтрация содержание фида по видам записей (post_type), категориям и пользовательским таксономиям, индивидуально

После установки настройки плагина доступны через меню _Настройки -> Яндекс.Новости_.

Трансляция (фид) доступен для просмотра по ссылке _domain.ru/yandex/news/_.

Подробнее об использовании плагина читайте на сайте [Теплицы социальных технологий](http://te-st.ru/2014/04/08/screencast-yandex-news-plugin/)

== Installation ==
Процесс инсталляции плагина стандартен для WordPress.

Если у вас установлен GIT, то вы можете клонировать репозиторий: https://github.com/Teplitsa/tst-yandex-feed.git
или скачать его в виде ZIP архива: https://github.com/Teplitsa/tst-yandex-feed/archive/master.zip

== Changelog ==

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

