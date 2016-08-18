<?php
/**
 * Entity Related Shortcode Functions
 *
 * @package YT_SCASE_COM
 * @version 2.3.0
 * @since WPAS 4.0
 */
if (!defined('ABSPATH')) exit;
/**
 * Shortcode function
 *
 * @since WPAS 4.0
 * @param array $atts
 * @param array $args
 * @param string $form_name
 * @param int $pageno
 *
 * @return html
 */
function yt_scase_com_video_grid_set_shc($atts, $args = Array() , $form_name = '', $pageno = 1, $shc_page_count = 0) {
	global $shc_count;
	if ($shc_page_count != 0) {
		$shc_count = $shc_page_count;
	} else {
		if (empty($shc_count)) {
			$shc_count = 1;
		} else {
			$shc_count++;
		}
	}
	$fields = Array(
		'app' => 'yt_scase_com',
		'class' => 'emd_video',
		'shc' => 'video_grid',
		'shc_count' => $shc_count,
		'form' => $form_name,
		'has_pages' => true,
		'pageno' => $pageno,
		'pgn_class' => '',
		'theme' => 'bs',
		'hier' => 0,
		'hier_type' => 'ul',
		'hier_depth' => - 1,
		'hier_class' => '',
		'has_json' => 0,
	);
	$args_default = array(
		'posts_per_page' => '16',
		'post_status' => 'publish',
		'orderby' => 'date',
		'order' => 'DESC',
		'filter' => ''
	);
	return emd_shc_get_layout_list($atts, $args, $args_default, $fields);
}
add_shortcode('video_grid', 'video_grid_list');
function video_grid_list($atts) {
	$show_shc = 1;
	if ($show_shc == 1) {
		wp_enqueue_style('wpas-boot');
		wp_enqueue_script('wpas-boot-js');
		wp_enqueue_script('view-video-grid');
		wp_enqueue_script('video-grid-js');
		add_action('wp_footer', 'yt_scase_com_enq_allview');
		$list = "<div class='emd-container'>";
		$list.= yt_scase_com_video_grid_set_shc($atts);
		$list.= "</div>";
	} else {
		$list = '<div class="alert alert-info not-authorized">You are not authorized to access this content.</div>';
	}
	return $list;
}
/**
 * Shortcode function
 *
 * @since WPAS 4.0
 * @param array $atts
 * @param array $args
 * @param string $form_name
 * @param int $pageno
 *
 * @return html
 */
function yt_scase_com_video_indicators_set_shc($atts, $args = Array() , $form_name = '', $pageno = 1, $shc_page_count = 0) {
	global $shc_count;
	if ($shc_page_count != 0) {
		$shc_count = $shc_page_count;
	} else {
		if (empty($shc_count)) {
			$shc_count = 1;
		} else {
			$shc_count++;
		}
	}
	$fields = Array(
		'app' => 'yt_scase_com',
		'class' => 'emd_video',
		'shc' => 'video_indicators',
		'shc_count' => $shc_count,
		'form' => $form_name,
		'has_pages' => true,
		'pageno' => $pageno,
		'pgn_class' => 'visible-lg visible-md',
		'theme' => 'bs',
		'hier' => 0,
		'hier_type' => 'ul',
		'hier_depth' => - 1,
		'hier_class' => '',
		'has_json' => 0,
	);
	$args_default = array(
		'posts_per_page' => '16',
		'post_status' => 'publish',
		'orderby' => 'date',
		'order' => 'DESC',
		'filter' => ''
	);
	return emd_shc_get_layout_list($atts, $args, $args_default, $fields);
}
add_shortcode('video_indicators', 'video_indicators_list');
function video_indicators_list($atts) {
	$show_shc = 1;
	if ($show_shc == 1) {
		wp_enqueue_style('wpas-boot');
		wp_enqueue_script('wpas-boot-js');
		wp_enqueue_script('view-video-indicators');
		wp_enqueue_script('video-indicators-js');
		add_action('wp_footer', 'yt_scase_com_enq_allview');
		$list = "<div class='emd-container'>";
		$list.= yt_scase_com_video_indicators_set_shc($atts);
		$list.= "</div>";
	} else {
		$list = '<div class="alert alert-info not-authorized">You are not authorized to access this content.</div>';
	}
	return $list;
}
/**
 * Shortcode function
 *
 * @since WPAS 4.0
 * @param array $atts
 * @param array $args
 * @param string $form_name
 * @param int $pageno
 *
 * @return html
 */
function yt_scase_com_video_items_set_shc($atts, $args = Array() , $form_name = '', $pageno = 1, $shc_page_count = 0) {
	global $shc_count;
	if ($shc_page_count != 0) {
		$shc_count = $shc_page_count;
	} else {
		if (empty($shc_count)) {
			$shc_count = 1;
		} else {
			$shc_count++;
		}
	}
	$fields = Array(
		'app' => 'yt_scase_com',
		'class' => 'emd_video',
		'shc' => 'video_items',
		'shc_count' => $shc_count,
		'form' => $form_name,
		'has_pages' => true,
		'pageno' => $pageno,
		'pgn_class' => 'hidden',
		'theme' => 'bs',
		'hier' => 0,
		'hier_type' => 'ul',
		'hier_depth' => - 1,
		'hier_class' => '',
		'has_json' => 0,
	);
	$args_default = array(
		'posts_per_page' => '16',
		'post_status' => 'publish',
		'orderby' => 'date',
		'order' => 'DESC',
		'filter' => ''
	);
	return emd_shc_get_layout_list($atts, $args, $args_default, $fields);
}
add_shortcode('video_items', 'video_items_list');
function video_items_list($atts) {
	$show_shc = 1;
	if ($show_shc == 1) {
		wp_enqueue_style('wpas-boot');
		wp_enqueue_script('wpas-boot-js');
		add_action('wp_footer', 'yt_scase_com_enq_allview');
		$list = "<div class='emd-container'>";
		$list.= yt_scase_com_video_items_set_shc($atts);
		$list.= "</div>";
	} else {
		$list = '<div class="alert alert-info not-authorized">You are not authorized to access this content.</div>';
	}
	return $list;
}
add_filter('widget_text', 'shortcode_unautop');
add_filter('widget_text', 'do_shortcode', 11);
