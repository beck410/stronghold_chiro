<?php
/**
 * Enqueue Scripts Functions
 *
 * @package YT_SCASE_COM
 * @version 2.3.0
 * @since WPAS 4.0
 */
if (!defined('ABSPATH')) exit;
add_action('admin_enqueue_scripts', 'yt_scase_com_load_admin_enq');
/**
 * Enqueue style and js for each admin entity pages and settings
 *
 * @since WPAS 4.0
 * @param string $hook
 *
 */
function yt_scase_com_load_admin_enq($hook) {
	global $typenow;
	$dir_url = YT_SCASE_COM_PLUGIN_URL;
	do_action('emd_ext_admin_enq', 'yt_scase_com');
	if ($hook == 'edit-tags.php') {
		return;
	}
	if (isset($_GET['page']) && in_array($_GET['page'], Array(
		'yt_scase_com',
		'yt_scase_com_notify',
		'yt_scase_com_settings'
	))) {
		wp_enqueue_script('accordion');
		return;
	} else if (isset($_GET['page']) && in_array($_GET['page'], Array(
		'yt_scase_com_store',
		'yt_scase_com_designs',
		'yt_scase_com_support'
	))) {
		wp_enqueue_style('admin-tabs', $dir_url . 'assets/css/admin-store.css');
		return;
	}
	if (in_array($typenow, Array(
		'emd_video'
	))) {
		$theme_changer_enq = 1;
		$datetime_enq = 0;
		$date_enq = 0;
		$sing_enq = 0;
		$tab_enq = 0;
		if ($hook == 'post.php' || $hook == 'post-new.php') {
			$unique_vars['msg'] = __('Please enter a unique value.', 'yt-scase-com');
			$unique_vars['reqtxt'] = __('required', 'yt-scase-com');
			$unique_vars['app_name'] = 'yt_scase_com';
			$ent_list = get_option('yt_scase_com_ent_list');
			if (!empty($ent_list[$typenow])) {
				$unique_vars['keys'] = $ent_list[$typenow]['unique_keys'];
				if (!empty($ent_list[$typenow]['req_blt'])) {
					$unique_vars['req_blt_tax'] = $ent_list[$typenow]['req_blt'];
				}
			}
			$tax_list = get_option('yt_scase_com_tax_list');
			if (!empty($tax_list[$typenow])) {
				foreach ($tax_list[$typenow] as $txn_name => $txn_val) {
					if ($txn_val['required'] == 1) {
						$unique_vars['req_blt_tax'][$txn_name] = Array(
							'hier' => $txn_val['hier'],
							'type' => $txn_val['type'],
							'label' => $txn_val['label'] . ' ' . __('Taxonomy', 'yt-scase-com')
						);
					}
				}
			}
			wp_enqueue_script('unique_validate-js', $dir_url . 'assets/js/unique_validate.js', array(
				'jquery',
				'jquery-validate'
			) , YT_SCASE_COM_VERSION, true);
			wp_localize_script("unique_validate-js", 'unique_vars', $unique_vars);
		} elseif ($hook == 'edit.php') {
			wp_enqueue_style('yt-scase-com-allview-css', YT_SCASE_COM_PLUGIN_URL . '/assets/css/allview.css');
		}
		if ($datetime_enq == 1) {
			wp_enqueue_script("jquery-ui-timepicker", $dir_url . 'assets/ext/emd-meta-box/js/jqueryui/jquery-ui-timepicker-addon.js', array(
				'jquery-ui-datepicker',
				'jquery-ui-slider'
			) , YT_SCASE_COM_VERSION, true);
			$tab_enq = 1;
		} elseif ($date_enq == 1) {
			wp_enqueue_script("jquery-ui-datepicker");
			$tab_enq = 1;
		}
	}
}
add_action('wp_enqueue_scripts', 'yt_scase_com_frontend_scripts');
/**
 * Enqueue style and js for each frontend entity pages and components
 *
 * @since WPAS 4.0
 *
 */
function yt_scase_com_frontend_scripts() {
	$dir_url = YT_SCASE_COM_PLUGIN_URL;
	wp_register_style('yt-scase-com-allview-css', $dir_url . '/assets/css/allview.css');
	$grid_vars = Array();
	$local_vars['ajax_url'] = admin_url('admin-ajax.php');
	$wpas_shc_list = get_option('yt_scase_com_shc_list');
	wp_register_script('video-grid-js', $dir_url . 'assets/js/video-grid.js');
	wp_register_script('video-indicators-js', $dir_url . 'assets/js/video-indicators.js');
	wp_register_script('view-video-grid', $dir_url . 'assets/js/view-video-grid.js');
	wp_register_script('video-gallery-js', $dir_url . 'assets/js/video-gallery.js');
	wp_register_style('wpas-boot', $dir_url . 'assets/ext/wpas/wpas-bootstrap.min.css');
	wp_register_script('wpas-boot-js', $dir_url . 'assets/ext/wpas/bootstrap.min.js', array(
		'jquery'
	));
	wp_register_script('view-video-indicators', $dir_url . 'assets/js/view-video-indicators.js');
	if (is_single() && get_post_type() == 'emd_video') {
		wp_enqueue_style('wpas-boot');
		wp_enqueue_script('wpas-boot-js');
		wp_enqueue_style('yt-scase-com-allview-css');
		return;
	}
}
/**
 * Enqueue if allview css is not enqueued
 *
 * @since WPAS 4.5
 *
 */
function yt_scase_com_enq_allview() {
	if (!wp_style_is('yt-scase-com-allview-css', 'enqueued')) {
		wp_enqueue_style('yt-scase-com-allview-css');
	}
}
