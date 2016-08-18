<?php
/*
 Plugin Name: The Events Calendar: Eventbrite Tickets
 Description: Looking to track attendees, sell tickets and more? Eventbrite is a free service that provides the full power of a conference ticketing system. This plugin extends Events Calendar with all the basic Eventbrite controls without ever leaving WordPress. In the absence of Events Calendar, this plugin provides interfaces to easily insert Eventbrite widgets into posts, the sidebar, or anywhere in your template files. Don't have an Eventbrite account? No problem, use the following link to set one up: <a href='http://www.eventbrite.com/r/etp'>http://www.eventbrite.com/r/etp</a>
 Version: 4.2
 Author: Modern Tribe, Inc.
 Author URI: http://m.tri.be/27
 Text Domain: tribe-eventbrite
 License: GPLv2 or later
*/

/*
Copyright 2009-2012 by Modern Tribe Inc and the contributors

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

define( 'EVENTBRITE_PLUGIN_FILE', __FILE__ );

/**
 * activation hook
 */
register_activation_hook( __FILE__, 'tribe_events_eventbrite_activate' );
function tribe_events_eventbrite_activate() {

	require_once dirname( __FILE__ ) . '/src/Tribe/Main.php';
	require_once dirname( __FILE__ ) . '/src/Tribe/PUE.php';

	tribe_events_eventbrite_start();
}

/**
 * Loads the plugin main files
 */
function tribe_events_eventbrite_start() {
	tribe_init_eventbrite_autoloading();

	$classes_exist = class_exists( 'Tribe__Events__Main' ) && class_exists( 'Tribe__Events__Tickets__Eventbrite__Main' );
	$version_ok = $classes_exist && version_compare( Tribe__Events__Main::VERSION, Tribe__Events__Tickets__Eventbrite__Main::REQUIRED_TEC_VERSION, '>=' );

	if ( ! $version_ok ) {
		add_action( 'admin_notices', 'tribe_eventbrite_show_fail_message' );
		return;
	}

	// In a test context we want to avoid attempting to define this twice (once during activation, once thereafter)
	if ( ! defined( 'EVENTBRITE_PLUGIN_FILE' ) ) {
		define( 'EVENTBRITE_PLUGIN_FILE', __FILE__ );
	}

	include_once( 'src/functions/xml-to-array.php' );
	include_once( 'src/functions/template-tags.php' );
	include_once( 'src/functions/deprecated-template-tags.php' );

	Tribe__Events__Tickets__Eventbrite__Main::instance();
	new Tribe__Events__Tickets__Eventbrite__PUE( __FILE__ );
}


/**
 * Sets up autoloading for the plugin
 */
function tribe_init_eventbrite_autoloading() {
	if ( ! class_exists( 'Tribe__Autoloader' ) ) {
		return;
	}

	$autoloader = Tribe__Autoloader::instance();

	$autoloader->register_prefix( 'Tribe__Events__Tickets__Eventbrite__', dirname( __FILE__ ) . '/src/Tribe' );

	// deprecated classes are registered in a class to path fashion
	foreach ( glob( dirname( __FILE__ ) . '/src/deprecated/*.php' ) as $file ) {
		$class_name = str_replace( '.php', '', basename( $file ) );
		$autoloader->register_class( $class_name, $file );
	}
	$autoloader->register_autoloader();
}

/**
 * Bootstrap the plugin
 */
function Tribe_Eventbrite_Load() {
	tribe_events_eventbrite_start();
}
add_action( 'plugins_loaded', 'Tribe_Eventbrite_Load', 5 );

/**
 * Shows message if the plugin can't load due to TEC not being installed.
 */
function tribe_eventbrite_show_fail_message() {
	if ( current_user_can( 'activate_plugins' ) ) {
		$url = 'plugin-install.php?tab=plugin-information&plugin=the-events-calendar&TB_iframe=true';
		$title = esc_html__( 'The Events Calendar', 'tribe-events-community' );
		echo '<div class="error"><p>' . sprintf( __( 'To begin using The Events Calendar: Eventbrite Tickets, please install the latest version of <a href="%s" class="thickbox" title="%s">The Events Calendar</a>.', 'tribe-eventbrite' ), esc_url( $url ), $title ) . '</p></div>';
	}
}

