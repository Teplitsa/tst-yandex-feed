<?php
$is_turbo = get_query_var('yandex_feed') == 'turbo';
$layf_enable_turbo = get_option('layf_enable_turbo');
$is_show_turbo = $layf_enable_turbo || $is_turbo;
$tstyn_is_single = false;

include("feed-header.php");
?>

<?php while( have_posts()) : the_post();?>

<?php include("feed-item.php") ?>

<?php endwhile; ?>

<?php include("feed-footer.php") ?>