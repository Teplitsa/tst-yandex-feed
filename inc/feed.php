<?php
/**
 * RSS2 Feed Template for Yandex.News translation.
 *
 */

header('Content-Type: ' . feed_content_type('rss') . '; charset=' . get_option('blog_charset'), true);
$layf_enable_turbo = get_option('layf_enable_turbo');

echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?>';
?>
<rss xmlns:yandex="http://news.yandex.ru" xmlns:media="http://search.yahoo.com/mrss/" <?php if($layf_enable_turbo):?> xmlns:turbo="http://turbo.yandex.ru"<?php endif?> version="2.0">
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
<?php if($layf_enable_turbo):?>
<turbo:cms_plugin><?php echo LAYF_YANDEX_CMS_PLUGIN_ID?></turbo:cms_plugin>
<?php endif?>

<?php while( have_posts()) : the_post(); ?>
<item<?php if($layf_enable_turbo):?> turbo="true"<?php endif;?>>
<title><?php the_title_rss();?></title>
<link><?php the_permalink_rss();?></link>

<?php if(!get_option('layf_remove_pdalink')): ?>
<pdalink><?php the_permalink_rss();?></pdalink>
<?php endif ?>
<description><?php La_Yandex_Feed_Core::custom_the_excerpt_rss();?></description>
<?php
    $layf_author = '';
    if(!get_option('layf_hide_author', '')) {
        $layf_author = apply_filters('layf_author', get_the_author(), get_the_ID());
    }
	if($layf_author):
?>
<author><?php echo $layf_author; ?></author>
<?php endif;?>
<?php
	$category = La_Yandex_Feed_Core::get_proper_category(get_the_ID());
	if($category) :?>
<category><?php echo $category;?></category>
<?php endif; ?>
<?php //enclosures
	$enclosure = La_Yandex_Feed_Core::item_enclosure();
	if(!empty($enclosure)): foreach($enclosure as $i => $img):
?>
<enclosure url="<?php echo esc_url($img['url']);?>" type="<?php echo esc_attr($img['mime']);?>"/>
<?php endforeach; endif;?>

<?php
	$media = La_Yandex_Feed_Core::item_media();
	if(!empty($media)):
	//media group 
?>
<?php foreach($media as $media_obj):?>
<?php if(!empty($media_obj['content']) || !empty($media_obj['player'])):?>
<media:group>
<?php if(!empty($media_obj['content'])):?>
<media:content url="<?php echo esc_url($media_obj['content']);?>" />
<?php endif?>
<?php if(!empty($media_obj['player'])):?>
<media:player url="<?php echo esc_url($media_obj['player']);?>" />
<?php endif?>
<?php if(!empty($media_obj['thumb'])) { ?>
<media:thumbnail url="<?php echo esc_url($media_obj['thumb']);?>"/>
<?php }?>
</media:group>
<?php endif?>
<?php endforeach; ?>
<?php endif;?>
<?php
	$related = La_Yandex_Feed_Core::item_related();
	if(!empty($related)):
?>
<yandex:related>
<?php foreach($related as $i => $link): ?>
<link url="<?php echo esc_url($link['url']);?>"><?php echo apply_filters('layf_related_link_text', $link['text']);?></link>
<?php endforeach;?>
</yandex:related>	
<?php	
	endif;
	
	$gmt_offset = get_option('gmt_offset');
	
	$gmt_offset_abs = floor(abs($gmt_offset));
	$gmt_offset_str = ($gmt_offset_abs > 9) ? $gmt_offset_abs.'00' : ('0'.$gmt_offset_abs.'00');
	$gmt_offset_str = $gmt_offset >= 0 ? '+' . $gmt_offset_str : '-' . $gmt_offset_str;
?>
<pubDate><?php echo mysql2date('D, d M Y H:i:s '.$gmt_offset_str, get_date_from_gmt(get_post_time('Y-m-d H:i:s', true)), false); ?></pubDate>
<?php if($layf_enable_turbo):?>
<turbo:content><?php echo La_Yandex_Feed_Core::get_the_turbo_content();?></turbo:content>
<?php endif?>
<yandex:full-text><?php echo La_Yandex_Feed_Core::get_the_content_feed(); ?></yandex:full-text>
</item>
<?php endwhile; ?>
</channel>
</rss>