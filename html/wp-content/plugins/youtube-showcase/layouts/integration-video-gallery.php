<?php global $video_gallery_filter; ?><div class="emd-container">
<?php echo do_shortcode("[video_items filter=\"" . $video_gallery_filter . "\" int_from=\"video_gallery\"]"); ?>
 <?php echo do_shortcode("[video_indicators filter=\"" . $video_gallery_filter . "\" int_from=\"video_gallery\"]"); ?>
</div>