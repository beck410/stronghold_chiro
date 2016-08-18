<?php global $video_indicators_count, $video_indicators_filter;
$real_post = $post;
$ent_attrs = get_option('yt_scase_com_attr_list');
?>
<div class="col-lg-3 col-md-4 col-sm-6 col-xs-12 <?php echo (($video_indicators_count == 0) ? 'active' : ''); ?>" data-slide-to="<?php echo $video_indicators_count; ?>" data-target="#emdvideos">
    <div class="panel panel-info">
        <div class="panel-body emd-vid">
            <div class="thumbnail" style="height: 100px">
                <img src="https://img.youtube.com/vi/<?php echo esc_html(emd_mb_meta('emd_video_key')); ?>
/<?php echo esc_html(emd_mb_meta('emd_video_thumbnail_resolution')); ?>
default.jpg" alt="<?php echo get_the_title(); ?>">
            </div>
        </div>
        <div class="panel-footer textRegular"><?php echo get_the_title(); ?></div>
    </div>
</div>