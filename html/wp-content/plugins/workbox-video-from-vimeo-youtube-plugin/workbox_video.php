<?php
/*
 * Plugin Name: Workbox Video from Vimeo & Youtube Plugin.
 * Author: Workbox Inc.
 * Author URI: http://www.workbox.com/
 * Plugin URI: http://blog.workbox.com/wordpress-video-gallery-plugin/
 * Version: 3.1.2
 * Description: The plugin allows to create a video gallery on any wordpress-generated page.
 * You can add videos from Youtube, Vimeo and Wistia by simply pasting the video URL.
 * Allows to control sort order of videos on the gallery page. Video galleries can be called on a page by using shortcodes now.
 * This plugin is for advanced users. If you run into problems, please send us detailed notes about your set up and the errors and we'll do our best to get back to you.
 * Spanish translation by Andrew Kurtis <a href="http://www.webhostinghub.com/">@WebHostingHub</a>
 * == Copyright ==
 * Copyright 2008-2016 Workbox Inc (email: support@workbox.com)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>
 */

/**
 * Main plugin class
 */
class workbox_YV_video {
	const SLUG = 'wbvideo';
	/**
	 * filters & actions initialization
	 */
	public function __construct() {
		// Activation hook
		register_activation_hook ( __FILE__, array (
				$this,
				'activate' 
		) );
		
		// deactivation hook
		register_deactivation_hook ( __FILE__, array (
				$this,
				'deactivate' 
		) );
		
		// uninstall hook
		register_uninstall_hook ( __FILE__, array (
				$this,
				'uninstall' 
		) );
		
		// init custom post types & taxonomies
		add_action ( 'init', array (
				$this,
				'init' 
		) );
		
		add_action ( 'admin_menu', array (
				$this,
				'setOptionsMenu' 
		) );
		
		// save additional fields & detect video if needed
		add_action ( 'save_post_' . self::SLUG, array (
				$this,
				'savePost' 
		), 10, 3 );
		
		// add additional columns
		add_filter ( 'manage_edit-' . self::SLUG . '_columns', array (
				$this,
				'setCustomColumns' 
		) );
		add_action ( 'manage_' . self::SLUG . '_posts_custom_column', array (
				$this,
				'customColumn' 
		), 10, 2 );
		
		// disable quick edit
		add_filter ( 'post_row_actions', array (
				$this,
				'removeQuickEdit' 
		), 10, 1 );
		
		// taxonomy add new form action
		add_action ( self::SLUG . '_category_add_form_fields', array (
				$this,
				'addTaxonomyForm' 
		) );
		
		// taxonomy edit form action
		add_action ( self::SLUG . '_category_edit_form_fields', array (
				$this,
				'editTaxonomyForm' 
		) );
		
		// save additional taxonomy data
		add_action ( 'edited_' . self::SLUG . '_category', array (
				$this,
				'saveTaxonomyMeta' 
		), 10, 2 );
		add_action ( 'create_' . self::SLUG . '_category', array (
				$this,
				'saveTaxonomyMeta' 
		), 10, 2 );
		
		// add sort links
		add_filter ( 'views_edit-' . self::SLUG, array (
				$this,
				'addSortableLinks' 
		) );
		
		// add sort JS scripts
		add_action ( 'admin_enqueue_scripts', array (
				$this,
				'registerSortableScripts' 
		) );
		add_action ( 'admin_footer', array (
				$this,
				'addSortableScripts' 
		) );
		add_action ( 'wp_ajax_wbsortdata', array (
				$this,
				'ajaxSortPosts' 
		) );
		
		// do sort by sort ID
		add_action ( 'pre_get_posts', array (
				$this,
				'setFrontDisplayParams' 
		) );
		
		// shows additional message
		add_action ( 'manage_posts_extra_tablenav', array (
				$this,
				'doShowMessage' 
		) );
		
		// add shortcode
		add_shortcode('workbox_video_YV_list', array($this, 'doShortCode'));
		
		// add js & css for the front end pages
		add_action ( 'wp_enqueue_scripts', array (
				$this,
				'add_js'
		) );
		add_action ( 'init', array (
				$this,
				'add_style'
		) );
		add_action ( 'wp_head', array (
				$this,
				'add_custom_style'
		) );
		
		add_filter('the_content', array($this, 'theContent'));
		
		add_action('admin_notices', array($this, 'upgrade23notice'));
	}
	
	/**
	 * initializing all required objects
	 */
	public function init() {
		$args = array (
				'label' => 'Videos',
				'labels' => array (
						'name' => 'Videos',
						'singular_name' => 'Video',
						'menu_name' => 'Videos',
						'name_admin_bar' => 'Videos',
						'all_items' => 'Videos',
						'add_new' => 'Add Video',
						'add_new_item' => 'Add New Video',
						'edit_item' => 'Edit Video',
						'new_item' => 'New Video',
						'view_item' => 'View Video',
						'search_items' => 'Search Videos',
						'not_found' => 'No Videos found',
						'not_found_in_trash' => 'No Videos found in trash',
						'parent_item_colon' => '' 
				),
				'description' => 'Videos posts',
				'public' => false,
				'show_ui' => true,
				'menu_icon' => 'dashicons-media-text',
				'supports' => array (
						'title',
						'editor',
						'custom-fields' 
				),
				'has_archive' => false,
				'register_meta_box_cb' => array (
						$this,
						'addMetaBox' 
				) 
		);
		register_post_type ( self::SLUG, $args );
		
		$args = array (
				'public' => false,
				'show_ui' => true,
				'show_in_nav_menus' => true,
				'show_tagcloud' => false,
				'show_admin_column' => true,
				'hierarchical' => true 
		);
		
		register_taxonomy ( self::SLUG . '_category', self::SLUG, $args );
		
		// clear version (to check upgrade process)
		if (isset($_GET['wb_clear_version_video'])) {
			delete_option(self::SLUG.'_version');
			die("Cleared");
		}
		
		// check if we need to upgrade data
		if (get_option(self::SLUG.'_version') == false || isset($_GET['wb_update_db_from2to3'])) { 
			$this->upgradeFrom2();
			
			if (isset($_GET['wb_update_db_from2to3'])) {
				wp_redirect(admin_url('edit.php?post_type='.self::SLUG));
				die();
			}
		}
		
		
		$nonce = isset ( $_POST ['_wpnonce'] ) ? $_POST ['_wpnonce'] : '';
		if (wp_verify_nonce ( $nonce, 'wb_video_options_edit' )) {
			if (isset ( $_POST ['wb_video_VY_page_len'] )) {
				update_option ( 'wb_video_VY_page_len', intval ( $_POST ['wb_video_VY_page_len'] ) );
			}
			
			if (isset ( $_POST ['class_wb_video_pager'] )) {
				update_option ( 'class_wb_video_pager', ($_POST ['class_wb_video_pager']) );
			}
			
			if (isset ( $_POST ['class_wb_video_pager_a'] )) {
				update_option ( 'class_wb_video_pager_a', ($_POST ['class_wb_video_pager_a']) );
			}
			
			if (isset ( $_POST ['class_wb_video_container'] )) {
				update_option ( 'class_wb_video_container', ($_POST ['class_wb_video_container']) );
			}
			
			if (isset ( $_POST ['class_wb_video_item'] )) {
				update_option ( 'class_wb_video_item', ($_POST ['class_wb_video_item']) );
			}
			
			if (isset ( $_POST ['class_wb_video_image_link'] )) {
				update_option ( 'class_wb_video_image_link', ($_POST ['class_wb_video_image_link']) );
			}
			
			if (isset ( $_POST ['class_wb_video_image_img'] )) {
				update_option ( 'class_wb_video_image_img', ($_POST ['class_wb_video_image_img']) );
			}
			
			if (isset ( $_POST ['class_wb_video_title'] )) {
				update_option ( 'class_wb_video_title', ($_POST ['class_wb_video_title']) );
			}
			
			if (isset ( $_POST ['class_wb_video_description'] )) {
				update_option ( 'class_wb_video_description', ($_POST ['class_wb_video_description']) );
			}
			
			if (isset ( $_POST ['class_wb_video_count_in_line'] )) {
				update_option ( 'class_wb_video_count_in_line', ($_POST ['class_wb_video_count_in_line']) );
			}
			
			wp_redirect ( 'edit.php?post_type=' . self::SLUG . '&page=' . self::SLUG . '_options&updated=yes' );
			die ();
		}
	}
	
	private function upgradeFrom2() {
		set_time_limit(0);
		global $wpdb;
		$videos = $wpdb->get_results('select * from wb_video_VY');
		$categories = $wpdb->get_results('select * from wb_video_galleries');
		
		// load categories into db
		$aCats = array();
		foreach ($categories as $cat) {
			$termData = wp_insert_term($cat->title, self::SLUG.'_category', array('description'=>$cat->description));
			if (!is_wp_error($termData) && isset($termData['term_id'])) {
				$aCats[$cat->id] = $termData['term_id'];
				$array = array (
						'wb_page_id' => isset($cat->post_id)?$cat->post_id:0,
						'wb_post_id' => isset($cat->post_blog_id)?$cat->post_blog_id:0,
						'wb_is_vertically' => isset($cat->is_vertical)?$cat->is_vertical:0
				);
		
				update_option ( self::SLUG . '_wb_taxonomy_' . $termData['term_id'], $array );
			} else if (is_wp_error($termData) && isset($termData->error_data['term_exists'])) {
				$aCats[$cat->id] = $termData->error_data['term_exists'];
			}
		}
		
		// load videos into db
		foreach ($videos as $video) {
			$postarr = array(
					'post_title'=>isset($video->title)?$video->title:0,
					'post_status'=>'publish',
					'post_type'=>self::SLUG,
					'post_content'=>isset($video->description)?$video->description:''
			);
			$post_id = wp_insert_post($postarr);
			if (is_int($post_id) && $post_id > 0) {
				// new video url posted - need to check
				if (isset($video->url)) {
					$this->parseVideoByURL($video->url, $post_id);
					update_post_meta ( $post_id, 'video_url', $video->url );
				}
		
				$aCatsToAdd = array();
				if (isset($aCats[$video->gallery_id]) && $video->gallery_id != 0) {
					$aCatsToAdd[] = $aCats[$video->gallery_id];
					wp_set_object_terms($post_id, $aCatsToAdd, self::SLUG.'_category');
					update_post_meta ( $post_id, 'sortorder' . $aCats[$video->gallery_id], $video->order_no );
				}
			}
		}
		
		add_option(self::SLUG.'_version', '3.1');
		
		set_transient(self::SLUG.'_db_upgraded', 1);
	}
	
	
	/**
	 * adds additional options pages
	 */
	public function setOptionsMenu() {
		add_submenu_page ( 'edit.php?post_type=' . self::SLUG, 'Options', 'Options', 'administrator', self::SLUG . '_options', array (
				$this,
				'showOptionsPage' 
		) );
	}
	
	/**
	 * Shows options page
	 */
	public function showOptionsPage() {
		echo '<div class="wrap">
				<h1>Plugin Options</h1>';
		
		
		if (isset ( $_GET ['updated'] )) {
			echo '
			<div class="updated notice notice-success" id="message">
				<p>Settings updated</p>
			</div>
					';
		}
		
		echo '
				<form method="post">';
		wp_nonce_field ( 'wb_video_options_edit' );
		echo '
					<table border="0" width="90%">
		                <tr>
		                    <td width="200" align="right"><b>' . __ ( 'Show videos on page', 'workbox_video' ) . '</b><br><i>' . __ ( '0 - no pagination', 'workbox_video' ) . '</i>:</td>
		                    <td>
		                        <input type="text" name="wb_video_VY_page_len" value="' . intval ( get_option ( 'wb_video_VY_page_len' ) ) . '" size="10">
		                    </td>
		                </tr>
		
						<tr>
		                    <td colspan="2"><br><b>' . __ ( 'CSS Options (leave the field empty for default value)', 'workbox_video' ) . '</b></td>
		                </tr>
		                    		
						<tr>
                    		<td width="200" align="right"><b>' . __ ( 'Pages counter DIV container', 'workbox_video' ) . ':</b><br>(.wb_video_pager)<br>&nbsp;</td>
                    		<td>
                        		<input type="text" name="class_wb_video_pager" value="' . esc_attr ( get_option ( 'class_wb_video_pager' ) ) . '" style="width: 100%;">
								<br>
								<i>' . __ ( 'Default value', 'workbox_video' ) . ': width: 100%; clear: both;</i>
								<br><br>
		                    </td>
		                </tr>
						<tr>
                    		<td width="200" align="right"><b>' . __ ( 'Pages counter DIV container link', 'workbox_video' ) . ':</b><br>(.wb_video_pager a)<br>&nbsp;</td>
                    		<td>
                        		<input type="text" name="class_wb_video_pager_a" value="' . esc_attr ( get_option ( 'class_wb_video_pager_a' ) ) . '" style="width: 100%;">
								<br>
								<i>' . __ ( 'Default value', 'workbox_video' ) . ': none</i>
								<br><br>
                    		</td>
                		</tr>
						<tr>
	                    	<td width="200" align="right"><b>' . __ ( 'Main container DIV', 'workbox_video' ) . ':</b><br>(.wb_video_container)<br>&nbsp;</td>
		                    <td>
		                        <input type="text" name="class_wb_video_container" value="' . esc_attr ( get_option ( 'class_wb_video_container' ) ) . '" style="width: 100%;">
								<br>
								<i>' . __ ( 'Default value', 'workbox_video' ) . ': width: 100%; padding: 20px 0;</i>
								<br><br>
		                    </td>
		                </tr>
						<tr>
		                    <td width="200" align="right"><b>' . __ ( 'Specific item container DIV', 'workbox_video' ) . ':</b><br>(.wb_video_item)<br>&nbsp;</td>
		                    <td>
                        		<input type="text" name="class_wb_video_item" value="' . esc_attr ( get_option ( 'class_wb_video_item' ) ) . '" style="width: 100%;">
								<br>
								<i>' . __ ( 'Default value', 'workbox_video' ) . ': clear: both;</i>
								<br><br>
		                    </td>
		                </tr>
						<tr>
		                    <td width="200" align="right"><b>' . __ ( 'Image link A', 'workbox_video' ) . ':</b><br>(.wb_video_image_link)<br>&nbsp;</td>
		                    <td>
		                        <input type="text" name="class_wb_video_image_link" value="' . esc_attr ( get_option ( 'class_wb_video_image_link' ) ) . '" style="width: 100%;">
								<br>
								<i>' . __ ( 'Default value', 'workbox_video' ) . ': float: left; padding: 0 20px 20px 0;</i>
								<br><br>
		                    </td>
		                </tr>
						<tr>
                    		<td width="200" align="right"><b>' . __ ( 'Image', 'workbox_video' ) . ':</b><br>(.wb_video_image_img)<br>&nbsp;</td>
                    		<td>
                        		<input type="text" name="class_wb_video_image_img" value="' . esc_attr ( get_option ( 'class_wb_video_image_img' ) ) . '" style="width: 100%;">
								<br>
								<i>' . __ ( 'Default value', 'workbox_video' ) . ': none</i>
								<br><br>
		                    </td>
		                </tr>
						<tr>
		                    <td width="200" align="right"><b>' . __ ( 'Video title link A', 'workbox_video' ) . ':</b><br>(.wb_video_title)<br>&nbsp;</td>
		                    <td>
		                        <input type="text" name="class_wb_video_title" value="' . esc_attr ( get_option ( 'class_wb_video_title' ) ) . '" style="width: 100%;">
								<br>
								<i>Default value: none</i>
								<br><br>
		                    </td>
		                </tr>
						<tr>
			                    <td width="200" align="right"><b>' . __ ( 'Video description container DIV', 'workbox_video' ) . ':</b><br>(.wb_video_description)<br>&nbsp;</td>
			                    <td>
			                        <input type="text" name="class_wb_video_description" value="' . esc_attr ( get_option ( 'class_wb_video_description' ) ) . '" style="width: 100%;">
									<br>
									<i>Default value: none</i>
									<br><br>
			                    </td>
           				 </tr>
						<tr>
							<td width="200" align="right"><b>' . __ ( 'Count of video in line', 'workbox_video' ) . ':</b></td>
							<td><input type="text" name="class_wb_video_count_in_line" value="' . esc_attr ( get_option ( 'class_wb_video_count_in_line' ) ) . '" style="width: 100%;"></td>
						</tr>
			            <tr>
			                <td width="200" align="right">&nbsp;</td>
			                <td>
								<input type="submit" class="button" value="' . __ ( 'Update Options', 'workbox_video' ) . '">
			                </td>
			            </tr>
            		</table>
				</form>
				<br><br>
				';
		
		
			if ($this->canUpgradefrom2()) {
				echo '
				Version 2.* users! If your videos disappeared after upgrade - you can manually start DB upgrade 
				process. Just click the button below.<br>
				<br><a href="'.admin_url('edit.php?post_type='.self::SLUG.'&wb_update_db_from2to3').'" class="button">Upgrade DB Manually</a>
						';
			}
		
			echo '
			</div>';
		
		
			
	}
	
	/**
	 * Register meta-box for addotional video params
	 */
	public function addMetaBox() {
		add_meta_box ( self::SLUG . '_meta', 'Additional Parameters', array (
				$this,
				'showMetaBox' 
		), self::SLUG, 'normal', 'high' );
	}
	
	/**
	 * Shows meta-box
	 *
	 * @param unknown $post        	
	 */
	public function showMetaBox($post) {
		echo '
			<label>Video URL:</label>
			<input type="text" name="video_url" value="' . esc_attr ( get_post_meta ( $post->ID, 'video_url', true ) ) . '" size="50">
				';
	}
	
	/**
	 * parse video and saves required meta tags
	 * @param unknown $current_video_url
	 * @param unknown $post_id
	 */
	public function parseVideoByURL($current_video_url, $post_id) {
		$pos = strpos ( $current_video_url, 'http://' );
		$posS = strpos ( $current_video_url, 'https://' );
		if ($pos === false && $posS === false) {
			$current_video_url = 'http://' . $current_video_url;
		}
		
		// parse url and detect type
		$media_source = explode ( '/', $current_video_url );
		$media_source = explode ( '.', $media_source [2] );
		
		if ((($media_source [0] == 'www') && ($media_source [1] == 'vimeo')) || ($media_source [0] == 'vimeo')) {
			// vimeo
			update_post_meta ( $post_id, 'video_type', 'vimeo' ); // set type
				
			$vimeo_key = explode ( '.com/', $current_video_url );
			$vimeo_key = @explode ( '?', $vimeo_key [1] );
			// get info
			$data = @json_decode ( file_get_contents ( 'http://vimeo.com/api/v2/video/' . $vimeo_key [0] . '.json' ) );
				
			$thumb = '';
			$width = 0;
			$height = 0;
			if (isset ( $data [0]->thumbnail_small )) {
				$thumb = $data [0]->thumbnail_small;
				$width = intval ( $data [0]->width );
				$height = intval ( $data [0]->height );
		
				update_post_meta ( $post_id, 'video_thumb', $thumb );
				update_post_meta ( $post_id, 'video_source', '<iframe src="//player.vimeo.com/video/' . $vimeo_key [0] . '?title=0&amp;byline=0&amp;portrait=0&amp;color=6fde9f" width="' . $width . '" height="' . $height . '" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>' );
			}
		} else if ((($media_source [0] == 'www') && ($media_source [1] == 'youtube')) || ($media_source [0] == 'youtu')) {
			update_post_meta ( $post_id, 'video_type', 'youtube' ); // set type
				
			if (strpos ( $current_video_url, "&v" ) || strpos ( $current_video_url, "?v" )) {
				$youtube_key = explode ( '/', $current_video_url );
				$youtube_key = @explode ( 'v=', $youtube_key [3] );
				$youtube_key = @explode ( '&', $youtube_key [1] );
			} else {
				$youtube_key = @explode ( '?', $current_video_url );
				$youtube_key [0] = @substr ( $youtube_key [0], - 11 );
			}
				
			$thumb = '//i.ytimg.com/vi/' . $youtube_key [0] . '/default.jpg';
			$width = 560;
			$height = 349;
				
			update_post_meta ( $post_id, 'video_thumb', $thumb );
			update_post_meta ( $post_id, 'video_source', '<iframe width="' . $width . '" height="' . $height . '" src="//www.youtube.com/embed/' . $youtube_key [0] . '?rel=0" frameborder="0" allowfullscreen></iframe>' );
		} else if ((($media_source [1] == 'wistia')) || ($media_source [0] == 'wistia')) {
			update_post_meta ( $post_id, 'video_type', 'wistia' ); // set type
				
			$url = 'http://fast.wistia.com/oembed?url=' . $current_video_url . '&width=640&height=480';
			$ch = curl_init ();
			curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt ( $ch, CURLOPT_URL, $url );
			$result = curl_exec ( $ch );
			curl_close ( $ch );
			$wistia = json_decode ( $result );
				
			update_post_meta ( $post_id, 'video_thumb', isset ( $wistia->{'thumbnail_url'} ) ? $wistia->{'thumbnail_url'} : '' );
			update_post_meta ( $post_id, 'video_source', isset ( $wistia->{'html'} ) ? $wistia->{'html'} : '' );
		}
	}
	
	
	/**
	 * save additional data from controls
	 *
	 * @param unknown $post_id        	
	 * @param unknown $post        	
	 * @param unknown $update        	
	 */
	public function savePost($post_id, $post, $update) {
		if (isset ( $_POST ['action'] ) && $_POST ['action'] == 'editpost') {
			$prev_video_url = get_post_meta ( $post_id, 'video_url', true );
			$current_video_url = stripslashes ( isset ( $_POST ['video_url'] ) ? $_POST ['video_url'] : '' );
			
			// new video url posted - need to check
			if ($current_video_url != $prev_video_url) {
				// add http if needed
				$this->parseVideoByURL($current_video_url, $post_id);
			}
			
			update_post_meta ( $post_id, 'video_url', $current_video_url );
			
			// set sort fields
			$terms = wp_get_post_terms ( $post_id, self::SLUG . '_category' );
			foreach ( $terms as $t ) {
				$order_no = get_post_meta ( $post_id, 'sortorder' . $t->term_id, true );
				if (! $order_no)
					update_post_meta ( $post_id, 'sortorder' . $t->term_id, 999999 );
			}
		}
	}
	
	/**
	 * Creationg custom columns in the admin list
	 *
	 * @param unknown $columns        	
	 * @return string
	 */
	public function setCustomColumns($columns) {
		unset ( $columns ['date'] );
		$columns ['video_type'] = 'Video Type';
		$columns ['video_thumb'] = 'Thumbnail';
		
		return $columns;
	}
	
	/**
	 * show custom columns content the the admin list
	 *
	 * @param unknown $column        	
	 * @param unknown $post_id        	
	 */
	public function customColumn($column, $post_id) {
		switch ($column) {
			case 'video_type' :
				echo get_post_meta ( $post_id, 'video_type', true );
				break;
			case 'video_thumb' :
				$thumb = get_post_meta ( $post_id, 'video_thumb', true );
				echo '<img src="' . $thumb . '" width="100">';
				break;
		}
	}
	
	/**
	 * disabling quick edit links
	 * 
	 * @param unknown $actions        	
	 */
	public function removeQuickEdit($actions) {
		global $post;
		
		if (isset ( $post->post_type ) && $post->post_type == self::SLUG) {
			unset ( $actions ['inline hide-if-no-js'] );
		}
		
		return $actions;
	}
	
	/**
	 * Show taxonomy add form
	 */
	public function addTaxonomyForm() {
		$pages = get_pages ();
		$posts = get_posts ();
		
		?>
<div class="form-field">
	<label for="">Attach to page</label> <select name="wb_page_id">
		<option value="0">-= not attached =-</option>
					<?php
		foreach ( $pages as $p ) {
			?>
						<option value="<?php echo $p->ID; ?>"><?php echo $p->post_title; ?></option>
						<?php
		}
		?>
				</select>
</div>
<div class="form-field">
	<label for="">Attach to post</label> <select name="wb_post_id">
		<option value="0">-= not attached =-</option>
					<?php
		foreach ( $posts as $p ) {
			?>
						<option value="<?php echo $p->ID; ?>"><?php echo $p->post_title; ?></option>
						<?php
		}
		?>
				</select>
</div>

<div class="form-field">
	<label for="">Stack videos vertically</label> <input type="checkbox"
		name="wb_is_vertically" value="1" checked>
</div>
<?php
	}
	
	/**
	 * Show taxonomy edit form
	 *
	 * @param unknown $term        	
	 */
	public function editTaxonomyForm($term) {
		$pages = get_pages ();
		$posts = get_posts ();
		
		$array = get_option ( self::SLUG . '_wb_taxonomy_' . $term->term_id );
		
		if (! is_array ( $array ))
			$array = array ();
		
		$array_initial = array (
				'wb_page_id' => '',
				'wb_post_id' => '',
				'wb_is_vertically' => '' 
		);
		
		$array = array_merge ( $array_initial, $array );
		?>
<tr class="form-field">
	<th scope="row" valign="top"><label>Attach to page</label></th>
	<td><select name="wb_page_id">
			<option value="0">-= not attached =-</option>
				<?php
		foreach ( $pages as $p ) {
			?>
					<option value="<?php echo $p->ID; ?>"
				<?php echo $p->ID == $array['wb_page_id']?'selected':''?>><?php echo $p->post_title; ?></option>
					<?php
		}
		?>
			</select></td>
</tr>

<tr class="form-field">
	<th scope="row" valign="top"><label>Attach to post</label></th>
	<td><select name="wb_post_id">
			<option value="0">-= not attached =-</option>
				<?php
		foreach ( $posts as $p ) {
			?>
					<option value="<?php echo $p->ID; ?>"
				<?php echo $p->ID == $array['wb_post_id']?'selected':''?>><?php echo $p->post_title; ?></option>
					<?php
		}
		?>
			</select></td>
</tr>
<tr class="form-field">
	<th scope="row" valign="top"><label>Stack videos vertically</label></th>
	<td><input type="checkbox" name="wb_is_vertically" value="1"
		<?php echo $array['wb_is_vertically']?'checked':''?>></td>
</tr>
<?php
	}
	
	/**
	 * save Taxonomy
	 *
	 * @param unknown $term_id        	
	 */
	public function saveTaxonomyMeta($term_id) {
		$action = isset ( $_POST ['action'] ) ? $_POST ['action'] : '';
		$taxonomy = isset ( $_POST ['taxonomy'] ) ? $_POST ['taxonomy'] : '';
		
		if (($action == 'editedtag' || $action == 'add-tag') && $taxonomy == self::SLUG . '_category') {
			$array = array (
					'wb_page_id' => intval ( isset ( $_POST ['wb_page_id'] ) ? $_POST ['wb_page_id'] : 0 ),
					'wb_post_id' => intval ( isset ( $_POST ['wb_post_id'] ) ? $_POST ['wb_post_id'] : 0 ),
					'wb_is_vertically' => intval ( isset ( $_POST ['wb_is_vertically'] ) ? $_POST ['wb_is_vertically'] : 0 ) 
			);
			
			update_option ( self::SLUG . '_wb_taxonomy_' . $term_id, $array );
		}
	}
	
	/**
	 * Add sortable links
	 * 
	 * @param unknown $views        	
	 */
	public function addSortableLinks($views) {
		$terms = get_terms ( self::SLUG . '_category' );
		
		foreach ( $terms as $t ) {
			$views [$t->slug . '_video_category'] = '<a href="edit.php?post_type=' . self::SLUG . '&' . self::SLUG . '_category=' . $t->slug . '&wbsort=yes&wbcat=' . $t->term_id . '" ' . ((isset ( $_GET ['wbcat'] ) && $_GET ['wbcat'] == $t->term_id) ? 'class="current"' : '') . '>Sort in ' . $t->name . '</a>';
		}
		
		return $views;
	}
	
	/**
	 * Register Sortable jUQery UI components
	 */
	public function registerSortableScripts() {
		wp_enqueue_script ( 'jquery' );
		wp_enqueue_script ( 'jquery-ui-core', false, 'jquery' );
		wp_enqueue_script ( 'jquery-ui-sortable', false, 'jquery' );
	}
	
	/**
	 * Add styles and scripts to sort items
	 */
	public function addSortableScripts() {
		if (isset ( $_GET ['wbsort'] )) {
			echo '
 				  <style>
				  		.wbsortplaceholder td { height: 10px; background-color: #cccccc; border-top: 3px solid #999; border-bottom: 3px solid #999; }
 						.ui-sortable-helper { border: 3px solid #333;}
				  </style>
				  <script>
					  jQuery(function() {
					    jQuery( "#the-list" ).sortable({
							placeholder: "wbsortplaceholder",
 							forcePlaceholderSize: true,
 							update: function( event, ui ) {
								var sorted = jQuery( "#the-list" ).sortable( "serialize");
 								var data = {
									"action": "wbsortdata",
									"sorted": sorted,
 									"wbcat": ' . (isset ( $_GET ['wbcat'] ) ? $_GET ['wbcat'] : 0) . '
								};
						
								jQuery.post(ajaxurl, data, function(response) {});
							}
					    });
					    jQuery( "#the-list" ).disableSelection();
					  });
				  </script>
 			';
		}
	}
	
	/**
	 * Sort posts.
	 * Called by ajax
	 */
	public function ajaxSortPosts() {
		$cat_id = isset ( $_POST ['wbcat'] ) ? $_POST ['wbcat'] : 0;
		$sSorted = isset ( $_POST ['sorted'] ) ? $_POST ['sorted'] : '';
		
		$aS = preg_split ( '/&/', $sSorted );
		$aIDs = array ();
		foreach ( $aS as $item ) {
			$aI = preg_split ( '/=/', $item );
			if (isset ( $aI [1] ))
				$aIDs [] = $aI [1];
		}
		
		// now resort posts as required
		foreach ( $aIDs as $k => $post_id ) {
			update_post_meta ( $post_id, 'sortorder' . $cat_id, $k + 1 );
		}
		
		wp_die ();
	}
	
	/**
	 * Enable sort by
	 */
	public function setFrontDisplayParams($query) {
		if (isset ( $_GET ['wbsort'] ) && is_admin () && $query->get ( 'post_type' ) == self::SLUG && isset ( $_GET ['wbcat'] )) {
			$query->set ( 'orderby', 'meta_value' );
			$query->set ( 'meta_key', 'sortorder' . $_GET ['wbcat'] );
			$query->set ( 'order', 'ASC' );
		}
	}
	
	/**
	 * generates gallery content
	 * 
	 * @param string $galleryID        	
	 */
	private function getContent($galleryID = false) {
		global $post, $count;
		if ($count == null)
			$count = 0;
		$count ++;
		
		
		$term = false;
		
		if (is_int ( $galleryID )) { // search gallery by
			$term = get_term ( $galleryID, self::SLUG . '_category' );
			if ($term) {
				$full_list = new WP_Query ( array (
						'post_type' => self::SLUG,
						'post_status' => 'publish',
						'orderby' => 'meta_value',
						'order' => 'ASC',
						'nopaging'=>true,
						'meta_key' => 'sortorder' . $term->term_id,
						'tax_query' => array(
								array(
										'taxonomy' => self::SLUG . '_category',
										'terms'    => $term->term_id,
								),
						),
				) );
			}
		} else if (is_string ( $galleryID )) {
			$term = get_term_by ( 'name',  ( $galleryID ), self::SLUG . '_category' );
			if ($term) {
				$videos_list = new WP_Query ( array (
						'post_type' => self::SLUG,
						'post_status' => 'publish',
						'orderby' => 'meta_value',
						'order' => 'ASC',
						'meta_key' => 'sortorder' . $term->term_id,
						'tax_query' => array(
								array(
										'taxonomy' => self::SLUG . '_category',
										'terms'    => $term->name,
										'field'    => 'name',
								),
						),
				) );
				
			}
		} else if (is_array($galleryID)) {
			$videos_list = new WP_Query ( array (
					'post_type' => self::SLUG,
					'post_status' => 'publish',
					'orderby' => 'post_title',
					'order' => 'ASC',
					'tax_query' => array(
							array(
									'taxonomy' => self::SLUG . '_category',
									'terms'    => $galleryID,
									'field'    => 'name',
							),
					),
			) );
		} else {
			$videos_list = new WP_Query ( array (
					'post_type' => self::SLUG,
					'post_status' => 'publish',
					'orderby' => 'post_title',
					'order' => 'ASC' 
			) );
		}
		
		
		
		$array_initial = array (
				'wb_page_id' => '',
				'wb_post_id' => '',
				'wb_is_vertically' => 0
		);
		
		if ($term) {
			$array = get_option ( self::SLUG . '_wb_taxonomy_' . $term->term_id );
			if (! is_array ( $array ))
				$array = array ();
			
			$array = array_merge ( $array_initial, $array );
		} else {
			$array = $array_initial;
		}
		
		
		$html = '';
		if (! is_wp_error( $videos_list ) && is_object ( $videos_list ) && $videos_list->have_posts ()) {
			$countInLine = intval ( get_option ( 'class_wb_video_count_in_line' ) );
			if ($countInLine == 0)
				$countInLine = 3;
			$index = 1;
			$flagOfBegin = false;
			
			$page_len = get_option('wb_video_VY_page_len', 0);
			$page_id = isset($_GET['wb_video_page_id'])?intval($_GET['wb_video_page_id']):0;
			$pages = 1;
			
			$paging_html = '';
			$list = array();
			if ($page_len <= 0) {
				$list = $videos_list->posts;
			} else {
				$records = sizeof($videos_list->posts);
				$pages = ceil($records/$page_len);
				$page_id = max(0, min($pages-1, $page_id));
				for ($i = $page_id*$page_len; $i<($page_id+1)*$page_len; $i++) {
					if (isset($videos_list->posts[$i]))
						$list[] = $videos_list->posts[$i];
				}
				
				if ($pages > 1) {
					$paging_html .= '<div class="wb_video_pager"> Page: ';
					
					$aPages = array();
					for($i = 0; $i<$pages; $i++) {
						$aPages[] = ($i == $page_id)?'<a href="'.get_permalink($post->ID).'?wb_video_page_id='.$i.'"><span>['.($i+1).']</span></a>':'<a href="'.get_permalink($post->ID).'?wb_video_page_id='.$i.'">'.($i+1).'</a>';	
					}
					
					$paging_html.= implode(' | ', $aPages);
					$paging_html.= '</div>';
				}
			}
			
			$html.= $paging_html;
			
			$html .= '<div class="wb_video_container">';
			foreach ( $list as $k => $item ) {
				$class = ' class="wb_vertical_container"';
				if ($array['wb_is_vertically'] == 0) {
					$class = ' class="wb_horizontal_container"';
				}
				
				
				if ($index == 1) {
					$html .= '<div' . $class . '>';
					$flagOfBegin = true;
				}
				
				$width = 630;
				$height = 440;
				
				$f1 = preg_match ( '/width="([^"]*)"/ims', get_post_meta($item->ID, 'video_source', true), $result1 );
				$f2 = preg_match ( '/height="([^"]*)"/ims', get_post_meta($item->ID, 'video_source', true), $result2 );
				
				if ($f1)
					$width = $result1 [1];
				if ($f2)
					$height = $result2 [1];
				
				$html .= '<div class="wb_video_item">';
				$html .= '<a href="#TB_inline?height=' . $height . '&width=' . $width . '&inlineId=movie' . $count . '_' . $k . '" class="wb_video_image_link thickbox" style="position: relative;"><img src="' . get_post_meta($item->ID, 'video_thumb', true) . '" width="120" class="wb_video_image_img"><b class="wb_video_icon"></b></a>';
				if ($item->post_title) {
					if ($array['wb_is_vertically'] == 0) {
						$html .= '<div class="wb_video_title"><a href="#TB_inline?height=' . $height . '&width=' . $width . '&inlineId=movie' . $count . '_' . $k . '" class="wb_video_title thickbox">' . $item->post_title . '</a></div>';
					} else {
						$html .= '<a href="#TB_inline?height=' . $height . '&width=' . $width . '&inlineId=movie' . $count . '_' . $k . '" class="wb_video_title thickbox">' . $item->post_title . '</a>';
					}
				}
				if ($item->post_content) {
					$html .= '<div class="wb_video_description">' . $item->post_content . '</div>';
				}
				$html .= '</div>';
				if ($flagOfBegin == true) {
					if (($index == $countInLine) && ($array['wb_is_vertically'] == 0)) {
						$html .= '</div>';
						$index = 0;
					}
					$index ++;
				}
			}
			if (($index <= $countInLine) && ($flagOfBegin == true) && ($index > 1) && ($array['wb_is_vertically'] == 0)) {
				$html .= '</div>';
			} else if ($array['wb_is_vertically'] == 1) {
				$html .= '</div>';
			}
			
			$html.= '</div><br style="clear: both;">';
			$html.= $paging_html;
			
			foreach ( $list as $k => $item ) {
				$html .= '
				<div style="display: none;" id="movie' . $count . '_' . $k . '">' . get_post_meta($item->ID, 'video_source', true) . '</div>
				';
			}
		}
		
		return $html;
	}
	
	public function doShortCode($atts = false) {
		$gallery_name = '';
		extract ( shortcode_atts ( array (
				'gallery_name' => ''
		), $atts ) );
		$html = $this->getContent($gallery_name);
		return $html;
	}
	
	/**
	 * for use outside of plugin
	 * 
	 * @param string $aGalleryID
	 *        	- 0 - shows all videos, number or string - shows category with this ID or name, array of ID or names - shows videos from all specified galleries
	 */
	static public function showList($aGalleryID = false) {
		$o = new self();
		return  $o->getContent($aGalleryID);
	}
	
	
	/**
	 * Content filter (to show gallery in the post or page)
	 * @param unknown $content
	 */
	public function theContent($content) {
		global $post;
		
		if (isset($post->post_type) && in_array($post->post_type, array('page', 'post'))) {
			if (!isset($GLOBALS['wb_video_posts_data'])) {
				$terms = get_terms(self::SLUG.'_category');
				$aData = array();
				foreach ($terms as $term) {
					$array = get_option ( self::SLUG . '_wb_taxonomy_' . $term->term_id );
					if (isset($array['wb_page_id'])) {
						if (isset($aData[$array['wb_page_id']])) {
							$aData[$array['wb_page_id']][] = $term->name;
						} else {
							$aData[$array['wb_page_id']] = array($term->name);
						}
					}
					
					if (isset($array['wb_post_id'])) {
						if (isset($aData[$array['wb_post_id']])) {
							$aData[$array['wb_post_id']][] = $term->name;
						} else {
							$aData[$array['wb_post_id']] = array($term->name);
						}
					}
				}
			
				foreach ($aData as &$item) {
					if (sizeof($item) == 1)
						$item = $item[0];
				}
				
				$GLOBALS['wb_video_posts_data'] = $aData;
			}
			
			
			if (isset($post->ID) && isset($GLOBALS['wb_video_posts_data'][$post->ID])) {
				$content = $content.'<br>'.$this->getContent($GLOBALS['wb_video_posts_data'][$post->ID]);
			}
		}
		
		return $content;
	}
	
	/**
	 * Adds custom styles for gallery
	 */
	public function add_custom_style() {
		echo '
		<style>
		    .wb_video_pager {' . (get_option ( 'class_wb_video_pager' ) != '' ? get_option ( 'class_wb_video_pager' ) : 'width: 100%; clear: both;') . '}
		    .wb_video_pager a {' . (get_option ( 'class_wb_video_pager_a' ) != '' ? get_option ( 'class_wb_video_pager_a' ) : '') . '}
		    .wb_video_container {' . (get_option ( 'class_wb_video_container' ) != '' ? get_option ( 'class_wb_video_container' ) : 'width: 100%; padding: 20px 0; display: inline-block;') . '}
		    .wb_video_item {' . (get_option ( 'class_wb_video_item' ) != '' ? get_option ( 'class_wb_video_item' ) : 'clear: both;') . '}
		    .wb_video_image_link {' . (get_option ( 'class_wb_video_image_link' ) != '' ? get_option ( 'class_wb_video_image_link' ) : 'float: left; padding: 0 20px 5px 0;') . '}
		    .wb_video_image_img  {' . (get_option ( 'class_wb_video_image_img' ) != '' ? get_option ( 'class_wb_video_image_img' ) : '') . '}
		    .wb_video_title {' . (get_option ( 'class_wb_video_title' ) != '' ? get_option ( 'class_wb_video_title' ) : '') . '}
		    .wb_video_description {' . (get_option ( 'class_wb_video_description' ) != '' ? get_option ( 'class_wb_video_description' ) : '') . '}
		    .wb_video_icon {position:absolute; left:46px; top:33px; display:block; width:31px; height:27px; background:url(' . WB_VID_URL . 'ico-play.png) 0 0 no-repeat;}
			.wb_horizontal_container { clear: both; }
			.wb_horizontal_container .wb_video_item { float: left; clear: none; }
			.tb-close-icon::before {content: "×" !important;}
			.wb_video_title { clear: both;}
		</style>
		';
	}
	public function add_js() {
		wp_enqueue_script ( 'jquery' );
		wp_enqueue_script ( 'thickbox', null, array (
				'jquery'
		) );
	}
	public function add_style() {
		wp_enqueue_style ( 'thickbox.css', '/' . WPINC . '/js/thickbox/thickbox.css', null, '1.0' );
	}
	
	/**
	 * Shows message about sort method
	 * @param unknown $which
	 */
	public function doShowMessage($which) {
		if ($which == 'top' && isset ( $_GET ['wbsort'] )) {
			echo '<br style="clear: both;"><br><b>NOTE! Drag & drop video items to sort them inside this category</b><br>';
		}
	}
	
	
	
	
	/**
	 * Activation function
	 */
	public function activate() {
		$this->init ();
		flush_rewrite_rules ();
		// add some settings
	}
	
	/**
	 * Deactivation function
	 */
	public function deactivate() {
	}
	
	/**
	 * Uninstall function
	 */
	public function uninstall() {
		// delete all video posts & taxonomies
	}
	
	/**
	 * Check if we need to upgrade from 2.* version to 3+
	 */
	public function needUpgrtadeFrom2() {
		global $wpdb;
		$data = $wpdb->get_results('SHOW TABLES LIKE "wb_video_VY";');
		if (is_array($data) && sizeof($data)>0 && get_option(self::SLUG.'_version') == '') 
			return true;
		else 
			return false;
	}
	
	/**
	 * Check if version 2 tables exists
	 */
	public function canUpgradefrom2() {
		global $wpdb;
		$data = $wpdb->get_results('SHOW TABLES LIKE "wb_video_VY";');
		if (is_array($data) && sizeof($data)>0)
			return true;
		else
			return false;
	}
	
	public function upgrade23notice() {
		if (get_transient(self::SLUG.'_db_upgraded') == 1) {
			echo '
			<div class="updated notice notice-success  is-dismissible" id="message">
				<p>Workbox Video Plugin. Database Upgraded!</p>
			</div>
					';
			
			delete_transient(self::SLUG.'_db_upgraded');
		}
		
		if ($this->needUpgrtadeFrom2()) {
			echo '
			<div class="updated notice error  is-dismissible" id="message">
				<p>Important! Workbox Videos plugin has been updated. You must update your database to ensure the correct plugin performance and avoid video data loss.
					<a style="float: right;" href="'.admin_url('edit.php?post_type='.self::SLUG.'&wb_update_db_from2to3').'" class="button">Update Database</a>
					<br>Your videos & categories from the old tables will be copied automatically
				</p>
			</div>
					';
		}
	}
	
	
}

new workbox_YV_video ();
