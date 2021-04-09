<?php
header('Content-Type: ' . feed_content_type('rss') . '; charset=' . get_option('blog_charset'), true);

echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?>';
?>
<rss xmlns:yandex="http://news.yandex.ru" xmlns:media="http://search.yahoo.com/mrss/" <?php if($is_show_turbo):?> xmlns:turbo="http://turbo.yandex.ru"<?php endif?> version="2.0">
<channel>
<title><?php bloginfo_rss('name');?></title>
<link><?php bloginfo_rss('url') ?></link>
<description><?php bloginfo_rss("description") ?></description>
<?php
$layf_analytics_id = trim(get_option('layf_analytics_id', ''));
if(!empty($layf_analytics_id)):
?>
<yandex:analytics type="<?php echo get_option('layf_analytics_type', 'Yandex')?>" id="<?php echo $layf_analytics_id?>"></yandex:analytics>
<?php endif?>
<?php
$layf_adnetwork_id_header = trim(get_option('layf_adnetwork_id_header', ''));
if(!empty($layf_adnetwork_id_header)):
?>
<yandex:adNetwork type="Yandex" id="<?php echo $layf_adnetwork_id_header?>" turbo-ad-id="header_ad_place"></yandex:adNetwork>
<?php endif?>
<?php
$layf_adnetwork_id_footer = trim(get_option('layf_adnetwork_id_footer', ''));
if(!empty($layf_adnetwork_id_footer)):
?>
<yandex:adNetwork type="Yandex" id="<?php echo $layf_adnetwork_id_footer?>" turbo-ad-id="footer_ad_place"></yandex:adNetwork>
<?php endif?>
<?php
$logo = get_option('layf_feed_logo', '');	
if(!empty($logo)):
?>
<yandex:logo><?php echo esc_url($logo);?></yandex:logo>
<?php endif;?>
<?php
	$logo_square = get_option('layf_feed_logo_square', '');
	if(!empty($logo_square)):
?>
<yandex:logo  type="square"><?php echo esc_url($logo_square);?></yandex:logo>
<?php endif;?>
<?php if($is_show_turbo):?>
<turbo:cms_plugin><?php echo LAYF_YANDEX_CMS_PLUGIN_ID?></turbo:cms_plugin>
<?php endif?>