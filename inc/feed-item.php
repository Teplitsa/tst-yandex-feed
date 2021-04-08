<?php
    $turbo_true_false = $layf_enable_turbo && !get_post_meta(get_the_ID(), 'layf_exclude_from_feed', true) ? 'true' : 'false';
?>

<item<?php if($is_show_turbo):?> turbo="<?php echo $turbo_true_false;?>"<?php endif;?>>
<title><?php the_title_rss();?></title>
<link><?php the_permalink_rss();?></link>

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
<?php if($is_show_turbo):?>
<turbo:content><?php echo La_Yandex_Feed_Core::get_the_turbo_content();?></turbo:content>
<?php endif?>
<?php if(!$tstyn_is_single):?>
<yandex:full-text><?php echo La_Yandex_Feed_Core::get_the_content_feed(); ?></yandex:full-text>
<?php endif?>
</item>
