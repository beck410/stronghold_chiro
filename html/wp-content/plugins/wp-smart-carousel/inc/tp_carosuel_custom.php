<?php

// register custom post
add_action( 'init', 'tp_carousel_post_type' );
function tp_carousel_post_type() {
	register_post_type( 'tp-carousel-items',
		array(
			'labels' => array(
				'name' => __( 'Smart Carousels' ),
				'singular_name' => __( 'Smart Carousel' ),
				'add_new_item' => __( 'Add New Carousel' )
			),
			'public' => true,
			'supports' => array('thumbnail', 'title', 'editor'),
			'has_archive' => true,
			'rewrite' => array('slug' => 'carousel-items'),
		)
	);	
}

//Register Taxonomy for custom post.
function tp_carousel_taxonomy() {
	register_taxonomy(
		'tpcarousel_cat',  //The name of the taxonomy. Name should be in slug form (must not contain capital letters or spaces).
		'tp-carousel-items',                  // ur custom post type name
		array(
			'hierarchical'          => true,
			'label'                 => 'Carousel Category',  //Display name
			'query_var'             => true,
			'hierarchical'     	 	=> true,
			'show_ui'          	 	=> true,
			'show_admin_column' 	=> true,
			'rewrite'               => array(
				'slug'              => 'carousel-category', // This controls the base slug that will display before each term
				'with_front'    => true // Don't display the category base before
				)
			)
	);
}
add_action( 'init', 'tp_carousel_taxonomy');  