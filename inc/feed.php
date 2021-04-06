<?php include("feed-header.php") ?>

<?php while( have_posts()) : the_post();?>

<?php include("feed-item.php") ?>

<?php endwhile; ?>

<?php include("feed-footer.php") ?>