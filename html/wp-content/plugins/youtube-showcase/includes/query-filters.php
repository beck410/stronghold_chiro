<?php
/**
 * Query Filter Functions
 *
 * @package YT_SCASE_COM
 * @version 2.3.0
 * @since WPAS 4.0
 */
if (!defined('ABSPATH')) exit;
add_filter('posts_request', 'yt_scase_com_posts_request', 99, 2);
add_filter('post_limits', 'yt_scase_com_post_limits', 99, 2);
add_filter('posts_orderby', 'yt_scase_com_posts_orderby', 99, 2);
/**
 * Change limit for author archive before wp_query is processed
 *
 * @since WPAS 4.8
 * @param string $input
 *
 * @return string $input
 */
function yt_scase_com_post_limits($input, $query) {
	if (!is_admin() && $query->is_main_query() && (is_author() || is_search())) {
		global $yt_scase_com_limit;
		$yt_scase_com_limit = $input;
	}
	return $input;
}
/**
 * Change orderby for author archive before wp_query is processed
 *
 * @since WPAS 4.8
 * @param string $input
 *
 * @return string $input
 */
function yt_scase_com_posts_orderby($input, $query) {
	if (!is_admin() && $query->is_main_query() && (is_author() || is_search())) {
		global $wpdb;
		global $yt_scase_com_orderby;
		$input = str_replace($wpdb->posts . ".", "", $input);
		$yt_scase_com_orderby = $input;
		return '';
	}
	return $input;
}
/**
 * Change request for author archive before wp_query is processed
 *
 * @since WPAS 4.8
 * @param string $input
 *
 * @return string $input
 */
function yt_scase_com_posts_request($input, $query) {
	global $wpdb;
	if (!is_admin() && $query->is_main_query() && is_search()) {
		$input = emd_author_search_results('yt_scase_com', $input, $query, 'search');
	} elseif (!is_admin() && $query->is_main_query() && is_author()) {
		$input = emd_author_search_results('yt_scase_com', $input, $query, 'author');
	}
	return $input;
}
/**
 * Change query parameters before wp_query is processed
 *
 * @since WPAS 4.0
 * @param object $query
 *
 * @return object $query
 */
function yt_scase_com_query_filters($query) {
	$has_limitby = get_option("yt_scase_com_has_limitby_cap");
	if (!is_admin() && $query->is_main_query()) {
		if ($query->is_category && empty($query->query_vars['post_type'])) {
			$query->query_vars['post_type'] = Array(
				"post",
				"emd_video"
			);
		}
		if ($query->is_tag && empty($query->query_vars['post_type'])) {
			$query->query_vars['post_type'] = Array(
				"post",
				"emd_video"
			);
		}
		if ($query->is_author) {
			return $query;
		} elseif ($query->is_search) {
			$cap_post_types = get_post_types();
			$ent_list = get_option("yt_scase_com_ent_list");
			$query->set('post_type', array_diff($cap_post_types, array_keys($ent_list)));
			return $query;
		}
	}
	return $query;
}
add_action('pre_get_posts', 'yt_scase_com_query_filters');
