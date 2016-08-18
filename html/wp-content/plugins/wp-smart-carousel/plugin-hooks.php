<?php
/*
Plugin Name: WP Smart Carousel
Plugin URI:  http://wppluginarea.com/wp-smart-carousel/
Description: In this smart carosuel you will get various style and feature with dynamic multiple shortcode. WP Smart Carousel it's supper easy to use.
Author: Wp Plugin Area
Author URI: http://wppluginarea.com
Version: 2.1
*/

/* Adding Latest jQuery from Wordpress */
function tp_smooth_carousel_latest_jquery() {
	wp_enqueue_script('jquery');
}
add_action('init', 'tp_smooth_carousel_latest_jquery');

/*Some Set-up*/
define('TP_SMOOTH_CAROUSEL', WP_PLUGIN_URL . '/' . plugin_basename( dirname(__FILE__) ) . '/' );

function tp_smooth_carousel_files(){
	/* Adding Plugin javascript file */
	wp_enqueue_script('tp-carousel-plugin-js', TP_SMOOTH_CAROUSEL.'js/owl.carousel.min.js', array('jquery'), '2.1', false);

	/* Adding Plugin owl carousel css file */
	wp_enqueue_style('tp-owl-carousel-css', TP_SMOOTH_CAROUSEL.'css/owl.carousel.css', array(), '2.1', false);
	
	/* Adding Plugin owl carousel Transition css file */
	wp_enqueue_style('tp-owl-transition-css', TP_SMOOTH_CAROUSEL.'css/owl.transitions.css', array(), '2.1', false);
	
	/* Adding Plugin tp carousel theme css file */
	wp_enqueue_style('tp-carousel-theme-css', TP_SMOOTH_CAROUSEL.'css/tp-carousel-style.css', array(), '2.1', false);
}
add_action( 'wp_enqueue_scripts', 'tp_smooth_carousel_files' );


// Hooks your functions into the correct filters
function tp_carosuel_mce_button() {
	// check user permissions
	if ( !current_user_can( 'edit_posts' ) && !current_user_can( 'edit_pages' ) ) {
		return;
	}
	// check if WYSIWYG is enabled
	if ( 'true' == get_user_option( 'rich_editing' ) ) {
		add_filter( 'mce_external_plugins', 'tp_carosuel_tinymce_plugin' );
		add_filter( 'mce_buttons', 'tp_carosuel_register_mce_button' );
	}
}
add_action('admin_head', 'tp_carosuel_mce_button');

// Declare script for new button
function tp_carosuel_tinymce_plugin( $plugin_array ) {
	$plugin_array['tp_carosuel_button'] = plugins_url('js/mce-button.js', __FILE__);
	return $plugin_array;
}

// Register new button in the editor
function tp_carosuel_register_mce_button( $buttons ) {
	array_push( $buttons, 'tp_carosuel_button' );
	return $buttons;
}

//Tinymc css load functions

function tp_carosuel_mce_css() {
	wp_enqueue_style('tp_caroseul_shortcode', plugins_url('/inc/tp-mcetwo-style.css', __FILE__) );
}
add_action( 'admin_enqueue_scripts', 'tp_carosuel_mce_css' );


//includes files
include_once('inc/tp_carosuel_custom.php');

//filter files
add_filter('widget_text', 'do_shortcode');


add_theme_support( 'post-thumbnails', array( 'post', 'tp-carousel-items' ) );
add_image_size( 'thumb', 400, 300, true );

function tp_custom_excerpt_length( $length ) {
	return 20;
}
add_filter( 'excerpt_length', 'tp_custom_excerpt_length', 999 );

/* Add Plugin Loop Code */
function tp_smooth_carousel_list_shortcode($atts){
	extract( shortcode_atts( array(
		'id' => 'demo',
		'count' => '10',
		'items' => '4',
		'navigation' => 'false',
		'pagination' => 'true',		
		'post_type' => 'tp-carousel-items',
		'margin' => '4px',
		'autoplay' => 'true',
		'content_style' => 'block',
		'custom_category' => '',
		'post_category' => '',
	), $atts, 'projects' ) );
	
    $q = new WP_Query(
        array('posts_per_page' => $count, 'post_type' => $post_type, 'tpcarousel_cat' => $custom_category, 'category_name' => $post_category, 'orderby' => 'meta_value','order' => 'DESC' )
        );		

	$list = '
	<script type="text/javascript">
		jQuery(document).ready(function() {
			jQuery("#owl'.$id.'").owlCarousel({
			items : '.$items.',
			autoPlay: '.$autoplay.',
			lazyLoad : true,
			stopOnHover : true,
			pagination : '.$pagination.',
			theme: "tp-carosuel-css",	
			navigation : '.$navigation.',
			navigationText: ["",""]
			});
		}); 
	</script>
<style type="text/css">
	div.owl-carousel  .item img{ display: block; width: 100%; height: auto; border-radius:none;	}
	.entry-content img, .comment-content img, .widget img, img.header-image, .author-avatar img, img.wp-post-image {
  border-radius: 0px;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.2);
}
</style>

	<div id="owl'.$id.'" class="owl-carousel tp_extra_cls"> '; 
		while($q->have_posts()) : $q->the_post();
			$idd = get_the_ID();
			$img= get_the_post_thumbnail( $post->ID, 'thumb', array ('class' => 'lazyOwl' ) );	
			$list .= '
				<div class="item" style="margin:'.$margin.'">'.$img.'
					<a href="'.get_the_permalink().'" target="_blank">				
						<div class="tp_carousel_content" style="display:'.$content_style.';">
							<h1>'.get_the_title().'</h1>
							<p>'.get_the_excerpt().'</p>
						<div class="tp_read_more">Read More</div>								
						</div>
					</a>
				</div>

				';        
		endwhile;
		$list.= '</div>';
		wp_reset_query();
		return $list;
}
add_shortcode('tp_carousel', 'tp_smooth_carousel_list_shortcode');	


?>