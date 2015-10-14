<?php
/**
 * RSS2 Feed Template for Yandex.News translation.
 *
 */

header('Content-Type: ' . feed_content_type('rss-http') . '; charset=' . get_option('blog_charset'), true);

echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?>';
?>
<rss xmlns:yandex="http://news.yandex.ru" xmlns:media="http://search.yahoo.com/mrss/" version="2.0">
<channel>
<title><?php bloginfo_rss('name');?></title>
<link><?php bloginfo_rss('url') ?></link>
<description><?php bloginfo_rss("description") ?></description>
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

<?php while( have_posts()) : the_post(); ?>
<item>
<title><?php the_title_rss();?></title>
<link><?php the_permalink_rss();?></link>
<pdalink><?php the_permalink_rss();?></pdalink>
<description><?php the_excerpt_rss();?></description>
<?php
	$layf_author = apply_filters('layf_author', get_the_author(), get_the_ID()); 
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
<media:group>
<?php foreach($media as $media_obj):?>
<media:content url="<?php echo esc_url($media_obj['url']);?>">
<media:player url="<?php echo esc_url($media_obj['url']);?>" />
<?php if(!empty($media_obj['thumb'])) { ?>
<media:thumbnail url="<?php echo esc_url($media_obj['thumb']);?>"/>
<?php }?>
</media:content>
<?php endforeach; ?>
</media:group>
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
	$gmt_offset = ($gmt_offset > 9) ? $gmt_offset.'00' : ('0'.$gmt_offset.'00');
?>
<pubDate><?php echo mysql2date('D, d M Y H:i:s +'.$gmt_offset, get_date_from_gmt(get_post_time('Y-m-d H:i:s', true)), false); ?></pubDate>
<yandex:full-text><?php echo La_Yandex_Feed_Core::get_the_content_feed(); ?></yandex:full-text>
</item>
<?php endwhile; ?>
</channel>
</rss>