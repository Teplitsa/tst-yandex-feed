<?php

$is_turbo = true;
$layf_enable_turbo = true;
$is_show_turbo = true;
$tstyn_is_single = true;

echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?>';
?>
<rss version="2.0" xmlns:yandex="http://news.yandex.ru" xmlns:turbo="http://turbo.yandex.ru">
<channel>


<?php include("feed-item.php") ?>

<?php include("feed-footer.php") ?>
