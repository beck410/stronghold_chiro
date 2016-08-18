<?php
function my_scripts_method() {
    wp_enqueue_script(
        'interactiveSpine',
        get_stylesheet_directory_uri() . '/js/interactiveSpine.js',
        array( 'jquery' )
    );
}

add_action( 'wp_enqueue_scripts', 'my_scripts_method' );
?>
