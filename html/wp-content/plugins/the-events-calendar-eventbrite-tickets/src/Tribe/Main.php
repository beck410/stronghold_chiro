<?php

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}


if ( ! class_exists( 'Tribe__Events__Tickets__Eventbrite__Main' ) ) {

	/**
	 * Tribe__Events__Tickets__Eventbrite__Main main class
	 *
	 * @package Tribe__Events__Tickets__Eventbrite__Main
	 * @since  1.0
	 * @author Modern Tribe Inc.
	 */
	class Tribe__Events__Tickets__Eventbrite__Main {

		/**************************************************************
		 * EventBrite Configuration
		 **************************************************************/
		const REQUIRED_TEC_VERSION = '4.2';

		protected static $instance;
		public static $errors;
		public static $eventBritePrivacy = 0;
		public static $eventBriteTimezone;
		public static $eventBriteTransport; // https if supported, otherwise http
		public static $pluginVersion = '4.2';
		protected $cache_expiration = HOUR_IN_SECONDS; // defaults to 1 hour, use $this->get_cache_expiration() to apply filters
		public $pluginDir;
		public $pluginPath;
		public $pluginUrl;
		public $pluginSlug;

		/**
		 * Constant holding the meta name for the Sticky fields after errors on saving
		 * @var string
		 */
		const EB_SAVED_META_DATA = 'tribe-eventbrite-saved-data';

		public static $metaTags = array(
			'_EventBriteId',			// ID in Eventbrite of this event
			'_EventBriteTicketName',
			'_EventBriteTicketDescription',
			'_EventBriteTicketStartDate',
			'_EventBriteTicketStartHours',
			'_EventBriteTicketStartMinutes',
			'_EventBriteTicketStartMeridian',
			'_EventBriteTicketEndDate',
			'_EventBriteTicketEndHours',
			'_EventBriteTicketEndMinutes',
			'_EventBriteTicketEndMeridian',
			'_EventBriteIsDonation',
			'_EventBriteTicketQuantity',
			'_EventBriteIncludeFee',
			'_EventBriteStatus',
			'_EventBriteEventCost',
			'_EventRegister',
			'_EventShowTickets',
		);


		/**
		 * Enforce singleton factory method
		 *
		 * @return Tribe__Events__Tickets__Eventbrite__Main
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		/**
		 * checks whether the The Events Calendar 2.0 or higher is active
		 *
		 * @since  1.0
		 * @author jgabois & Justin Endler
		 * @return bool
		 */
		public static function is_core_active() {
			return defined( 'Tribe__Events__Main::VERSION' ) && version_compare( Tribe__Events__Main::VERSION, '2.0', '>=' );
		}

		/**
		 * A 32bit absolute integer method, returns as String
		 *
		 * @param  string $number A numeric Integer
		 * @since  3.9.6
		 *
		 * @return string         Sanitized version of the Absolute Integer
		 */
		public static function sanitize_absint( $number = null ) {
			// If it's not numeric we forget about it
			if ( ! is_numeric( $number ) ) {
				return false;
			}

			$number = preg_replace( '/[^0-9]/', '', $number );

			// After the Replace return false if Empty
			if ( empty( $number ) ) {
				return false;
			}

			// After that it should be good to ship!
			return $number;
		}

		/**
		 * class constructer
		 * init necessary functions
		 *
		 */
		public function __construct() {

			// set internal variables
			$this->pluginPath = apply_filters( 'tribe_eb_pluginpath', trailingslashit( dirname( dirname( dirname( __FILE__ ) ) ) ) );
			$this->pluginDir = apply_filters( 'tribe_eb_plugindir', trailingslashit( basename( $this->pluginPath ) ) );
			$this->pluginFile = apply_filters( 'tribe_eb_pluginfile', $this->pluginDir . 'tribe-eventbrite.php' );
			$this->pluginUrl = apply_filters( 'tribe_eb_pluginurl', plugins_url() . '/' . $this->pluginDir );
			$this->pluginSlug = 'tribe-eventbrite';

			// bootstrap plugin
			self::load_domain();
			add_action( 'plugins_loaded', array( $this, 'check_oauth' ) );
			add_action( 'plugins_loaded', array( $this, 'add_actions' ) );
			add_action( 'plugins_loaded', array( $this, 'add_filters' ) );
		}

		/**
		 * Checks if the OAuth token is valid, this method has a Caching system in place
		 * to avoid doing an external request on every pageload
		 *
		 * @return void
		 */
		public function check_oauth() {
			if ( ! is_admin() ) {
				return;
			}

			$timer = get_transient( 'tribe_oauth_verification_failed' );
			if ( ! $timer ) {
				$api = Tribe__Events__Tickets__Eventbrite__API::instance();
				$response = $api->request( $api->get_base_url( 'users/me' ), 'get' );
			}

			if ( $timer || ( ! empty( $response->error ) && 'INVALID_AUTH' === $response->error ) ) {
				$api->valid_oauth = false;

				add_action( 'admin_notices', array( $this, 'action_display_auth_notice' ) );
			}
		}

		/**
		 * Prints a Authorization Notice when Token becomes invalid
		 * @return void
		 */
		public function action_display_auth_notice() {
			$url = Tribe__Settings::instance()->get_url( array( 'tab' => 'addons' ) );
			echo
				'<div class="error"><p>' .
					sprintf( __( 'Your Eventbrite token is not valid, check %s page to see how to create a new one.', 'events-eventbrite' ), '<a href="' . $url . '">' . esc_html__( 'Add-ons API', 'events-eventbrite' ) . '</a>' ) .
				'</p></div>';
		}

		/**
		 * echo admin error if/when TEC is not active
		 *
		 * @return void
		 */
		public function notice_missing_core() {
			$url = 'plugin-install.php?tab=plugin-information&plugin=the-events-calendar&TB_iframe=true';
			$title = __( 'The Events Calendar', 'tribe-events-community' );
			echo
				'<div class="error"><p>' .
					sprintf( __( 'To begin using The Events Calendar: Eventbrite Tickets, please install the latest version of <a href="%s" class="thickbox" title="%s">The Events Calendar</a>.', 'tribe-events-community' ), esc_url( $url ), esc_attr( $title ) ) .
				'</p></div>';
		}

		/**
		 * Add Eventbrite Tickets to the list of add-ons to check required version.
		 *
		 * @return array $plugins the existing plugins
		 * @return array the plugins
		 */
		public function init_addon( $plugins ) {
			$plugins['TribeEB'] = array(
				'plugin_name' => 'The Events Calendar: Eventbrite Tickets',
				'required_version' => self::REQUIRED_TEC_VERSION,
				'current_version' => self::$pluginVersion,
				'plugin_dir_file' => basename( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/tribe-eventbrite.php',
			);

			return $plugins;
		}

		/**
		 * run all WordPress action hooks
		 *
		 * @return void
		 */
		public function add_actions() {
			add_filter( 'tribe_tec_addons', array( $this, 'init_addon' ) );

			if ( ! class_exists( 'Tribe__Events__Main' ) ) {
				add_action( 'admin_notices', array( $this, 'notice_missing_core' ) );
			} elseif ( $this->is_core_active() ) {
				add_action( 'admin_notices', array( $this, 'notice_missing_token' ) );
				add_action( 'admin_notices', array( $this, 'notice_edit_event' ) );
				add_action( 'admin_notices', array( $this, 'action_display_notices' ) );

				add_action( 'admin_init', array( $this, 'prepopulate' ) );
				add_action( 'admin_enqueue_scripts', array( $this, 'load_assets' ) );
				add_action( 'plugin_action_links_' . trailingslashit( $this->pluginDir ) . 'tribe-eventbrite.php', array( $this, 'addLinksToPluginActions' ) );
				add_action( 'template_redirect', array( $this, 'authorize_redirect' ) );
				add_action( 'parse_request', array( $this, 'maybe_regenerate_rewrite_rules' ) );
				add_action( 'tribe_settings_validate_before_checks', array( $this, 'authorize_get_permission_redirect' ) );
				add_filter( 'tribe_addons_tab_fields', array( $this, 'add_addon_fields' ) );

				if ( Tribe__Events__Tickets__Eventbrite__API::instance()->is_ready() ) {

					add_action( 'wp_before_admin_bar_render', array( $this, 'addEventbriteToolbarItems' ), 20 );

					add_action( 'tribe_events_update_meta', array( $this, 'action_sync_event' ), 20 );
					add_action( 'tribe_events_event_clear', array( $this, 'clear_details' ) );
					add_action( 'tribe_events_cost_table', array( $this, 'add_metabox' ), 1 );
					add_action( 'tribe_eventbrite_before_integration_header', array( $this, 'addEventbriteLogo' ) );
					add_action( 'tribe_events_single_event_after_the_meta', array( $this, 'print_ticket_form' ), 9 );

					add_action( 'tribe_import_render_tab_eventbrite', array( $this, 'include_import_page' ) );
					add_action( 'wp_ajax_tribe_eb_search_existing', array( $this, 'search_api_events' ) );

				}
			}
		}

		/**
		 * WP connection to the EB API class to search using Select2
		 *
		 * @return void
		 */
		public function search_api_events() {
			$response = (object) array(
				'status' => false,
				'message' => '',
			);

			if ( ! isset( $_GET['q'] ) ) {
				return;
			}

			$api = Tribe__Events__Tickets__Eventbrite__API::instance();

			$term = esc_attr( $_GET['q'] );

			$eb_response = $api->user_events( $term );

			foreach ( $eb_response->events as $key => $event ) {
				$event->text = $event->name->text;
				$response->items[] = $event;
			}

			return wp_send_json( $response );
		}

		/**
		 * run all WordPress filter hooks
		 *
		 * @return void
		 */
		public function add_filters() {
			add_filter( 'tribe_help_tab_forums_url', array( $this, '_link_support_forum' ), 100 );

			// get all pricing items for tickets on the cost field
			add_filter( 'tribe_get_cost', array( $this, 'filter_get_cost' ), 20, 3 );
			add_filter( 'tribe_events_admin_show_cost_field', '__return_false' );
			add_filter( 'tribe_events_template_paths', array( $this, 'add_eventbrite_template_paths' ) );

			if ( ! class_exists( 'Tribe__Events__Main' ) ) {
				return;
			} elseif ( $this->is_core_active() ) {
				add_filter( 'rewrite_rules_array', array( $this, 'rewrite_rules_array' ) );
				add_filter( 'query_vars', array( $this, 'query_vars' ) );
				add_filter( 'tribe_support_registered_template_systems', array( $this, 'add_template_updates_check' ) );

				// Only Apply if Core and API keys are configured
				if ( Tribe__Events__Tickets__Eventbrite__API::instance()->is_ready() ) {
					add_filter( 'tribe_import_tabs', array( $this, 'add_import_tab' ) );
					add_filter( 'tribe_import_general_settings', array( $this, 'add_settings_fields' ), 15 );
				}
			}
		}


		public function add_import_tab( $tabs ) {
			$tabs[ __( 'Eventbrite', 'tribe-eventbrite' ) ] = 'eventbrite';

			return $tabs;
		}

		public function add_settings_fields( $fields = array() ) {
			$newfields = array(
				'eventbrite-title' => array(
					'type' => 'html',
					'html' => '<h3>' . esc_html__( 'Eventbrite Import Settings', 'tribe-eventbrite' ) . '</h3>',
				),
				'eventbrite-form-content-start' => array(
					'type' => 'html',
					'html' => '<div class="tribe-settings-form-wrap">',
				),
				'imported_post_status[eventbrite]' => array(
					'type' => 'dropdown',
					'label' => __( 'Default status to use for imported events', 'tribe-eventbrite' ),
					'options' => Tribe__Events__Importer__Options::get_possible_stati(),
					'validation_type' => 'options',
					'parent_option' => Tribe__Events__Main::OPTIONNAME,
				),
				'eventbrite-form-content-end' => array(
					'type' => 'html',
					'html' => '</div>',
				),
			);
			return array_merge( $fields, $newfields );
		}

		/**
		 * Register Eventbrite Tickets with the template update checker.
		 *
		 * @param array $plugins
		 *
		 * @return array
		 */
		public function add_template_updates_check( $plugins ) {
			$plugins[ __( 'Eventbrite Tickets', 'tribe-eventbrite' ) ] = array(
				self::$pluginVersion,
				$this->pluginPath . 'src/views/eventbrite',
				trailingslashit( get_stylesheet_directory() ) . 'tribe-events/eventbrite',
			);

			return $plugins;
		}

		/**
		 * Pre-populates an event with Eventbrite info, shows an error on failure.
		 */
		public function prepopulate() {
			// Sanity checks
			if ( ! current_user_can( 'publish_tribe_events' ) || empty( $_GET['import_eventbrite'] ) || ! wp_verify_nonce( $_GET['import_eventbrite'], 'import_eventbrite' ) ) {
				return;
			}

			// Attempt to import the event then take the user to the event editor
			try {
				$event_id = $this->import_existing_events();
				wp_safe_redirect( admin_url( 'post.php?post=' . $event_id . '&action=edit' ) );
				die;
			}
			// Or, on failure, keep them on the importer screen and trigger an appropriate error message
			catch ( Tribe__Events__Post_Exception $e ) {
				wp_safe_redirect( admin_url( 'edit.php?post_type=tribe_events&page=events-importer&tab=eventbrite&error=' . urlencode( $e->getMessage() ) ) );
				die;
			}
		}

		public function maybe_regenerate_rewrite_rules() {
			// if they don't have any rewrite rules, do nothing
			if ( ! is_array( $GLOBALS['wp_rewrite']->rules ) ) {
				return;
			}

			$rules = $this->rewrite_rules_array();

			$diff = array_diff( $rules, $GLOBALS['wp_rewrite']->rules );
			$key_diff = array_diff_assoc( $rules, $GLOBALS['wp_rewrite']->rules );

			if ( empty( $diff ) && empty( $key_diff ) ) {
				return;
			}

			flush_rewrite_rules();
		}

		public function query_vars( $vars ) {
			array_push( $vars, 'tribe_oauth' );
			return $vars;
		}

		public function rewrite_rules_array( $rules = array() ) {
			$rule = array(
				'tribe-oauth/eventbrite/?' => '?index.php?tribe_oauth=eventbrite',
			);
			return $rule + $rules;
		}

		public function authorize_redirect() {
			// Only move forward if we got a clear oauth for EB
			if ( 'eventbrite' !== get_query_var( 'tribe_oauth' ) ) {
				return;
			}

			// By default we redirect to the home_url
			$url = apply_filters( 'tribe_eb_authorize_redirect_fail', home_url( '/' ) );

			// This prevents caching plugins to cache if we get to this point
			if ( ! defined( 'DONOTCACHEPAGE' ) ) {
				define( 'DONOTCACHEPAGE', true );
			}

			if ( ! empty( $_GET['code'] ) && current_user_can( 'manage_options' ) ) {
				// After API Tab we need to change this to the right redirect
				$url = apply_filters( 'tribe_eb_authorize_redirect_success', add_query_arg( array(
					'post_type' => Tribe__Events__Main::POSTTYPE,
					'page' => Tribe__Settings::instance()->adminSlug,
					'tab' => 'addons',
					'code' => wp_kses( $_GET['code'], array() ),
					'oauth' => get_query_var( 'tribe_oauth' ),
				), admin_url( '/edit.php' ) ) );
			}

			// Since we are dealing with an internal URL safe redirect
			wp_safe_redirect( apply_filters( 'tribe_eb_authorize_redirect', $url ) );
			die;
		}

		public function authorize_get_permission_redirect() {
			$code = null;
			$instance = (object) array(
				'errors' => array(),
				'major_errors' => array(),
			);
			$instance->url = add_query_arg( array(
				'post_type' => Tribe__Events__Main::POSTTYPE,
				'page' => Tribe__Settings::instance()->adminSlug,
				'tab' => 'addons',
			), admin_url( 'edit.php' ) );

			// check permissions
			if ( ! current_user_can( 'manage_options' ) ) {
				$instance->errors[]    = __( "You don't have permission to do that.", 'tribe-eventbrite' );
				$instance->major_error = true;
			}

			if ( isset( $_POST['tribe-eventbrite-authorize'] ) && isset( $_POST['current-settings-tab'] ) ) {
				// check the nonce
				if ( ! wp_verify_nonce( $_POST['tribe-save-settings'], 'saving' ) ) {
					$instance->errors[]    = __( 'The request was sent insecurely.', 'tribe-eventbrite' );
					$instance->major_error = true;
				}

				// check that the request originated from the current tab
				if ( 'addons' !== $_POST['current-settings-tab'] ) {
					$instance->errors[]    = __( "The request wasn't sent from this tab.", 'tribe-eventbrite' );
					$instance->major_error = true;
				}

				// bail if we have errors
				if ( count( $instance->errors ) ) {
					remove_action( 'shutdown', array( $instance, 'deleteOptions' ) );
					add_option( 'tribe_settings_errors', $instance->errors );
					add_option( 'tribe_settings_major_error', $instance->major_error );
					wp_redirect( $instance->url );
					die;
				}
			} elseif ( ! empty( $_GET['oauth'] ) && 'eventbrite' === $_GET['oauth'] && ! empty( $_GET['code'] ) ) {
				$code = $_GET['code'];
			} else {
				return;
			}

			$api = Tribe__Events__Tickets__Eventbrite__API::instance();

			$result = $api->authorize( $code );

			if ( is_wp_error( $result ) ) {
				/* add_option() would be more efficient (than update_option()) however it won't
				 * work if the option already exists, hence the usage of update_option().
				 *
				 * @see https://central.tri.be/issues/46741
				 */
				update_option( 'tribe_settings_errors', sprintf( __( 'Unable to get authorization from the Eventbrite API: %s', 'tribe-eventbrite' ),
					esc_html( $result->get_error_message() )
				) );
				update_option( 'tribe_settings_major_error', $instance->major_error );
				wp_redirect( $instance->url );
				die;
			}

			$instance->url = add_query_arg( array(
				'success' => 1,
			), $instance->url );

			wp_redirect( esc_url_raw( $instance->url ) );
			die;
		}

		/**
		 * Apply filters and return the eventbrite cache expiration
		 *
		 * @return int number of seconds until the cache should expire
		 *
		 */
		public function get_cache_expiration() {
			return apply_filters( 'tribe_events_eb_cache_expiration', $this->cache_expiration );
		}

		/**
		 * load plugin text domain
		 *
		 * @since  1.0
		 * @author jgabois & Justin Endler
		 * @return void
		 */
		public function load_domain() {
			$mopath = trailingslashit( basename( dirname( EVENTBRITE_PLUGIN_FILE ) ) ) . 'lang/';
			$domain = 'tribe-eventbrite';

			// If we don't have Common classes load the old fashioned way
			if ( ! class_exists( 'Tribe__Main' ) ) {
				load_plugin_textdomain( $domain, false, $mopath );
			} else {
				// This will load `wp-content/languages/plugins` files first
				Tribe__Main::instance()->load_text_domain( $domain, $mopath );
			}
		}

		/**
		 * enqueue scripts & styles in the admin
		 *
		 * @since  1.0
		 * @author jgabois & Justin Endler
		 * @return void
		 */
		public function load_assets() {
			wp_enqueue_style(
				'tribe-eventbrite-admin',
				$this->pluginUrl . 'src/resources/css/eb-tec-admin.css',
				array(),
				apply_filters( 'tribe_eventbrite_css_version', self::$pluginVersion )
			);

			wp_register_script(
				'tribe-eventbrite-select-existing',
				$this->pluginUrl . 'src/resources/js/select-existing.js',
				array( 'tribe-events-select2' ),
				apply_filters( 'tribe_eventbrite_js_version', self::$pluginVersion )
			);

			if ( Tribe__Admin__Helpers::instance()->is_screen( 'tribe_events_page_events-importer' ) ) {
				wp_enqueue_script( 'tribe-eventbrite-select-existing' );
			}
		}

		/**
		 * Get the Notices from Eventbrite
		 *
		 * @param  WP_Post|int $event The Event Object
		 * @return array        An array of strings with the Notices
		 */
		public function get_notices( $event ) {
			if ( is_numeric( $event ) ){
				$event = get_post( $event );
			}

			if ( ! $event instanceof WP_Post ) {
				return false;
			}

			if ( ! tribe_is_event( $event->ID ) ) {
				return false;
			}

			/**
			 * Allow users to filter the Metakey based on Event Object
			 * @param string $metakey The Meta name
			 * @param WP_Post $event The Event Object
			 */
			$error_metakey = apply_filters( 'tribe_eventbrite_notices_key', '_tribe-eventbrite-notices', $event );

			// Get the Errors
			$notices = array_filter( (array) get_post_meta( $event->ID, $error_metakey ) );

			$tags = array(
				'a' => array(
					'href' => array(),
					'title' => array(),
					'target' => array(),
					'rel' => array(),
				),
				'ul' => array(),
				'ol' => array(),
				'li' => array(),
				'br' => array(),
				'em' => array(),
				'strong' => array(),
				'b' => array(),
				'p' => array(),
			);

			// Apply the Security
			foreach ( $notices as $key => $message ) {
				$notices[ $key ] = wp_kses( $message, $tags );
			}

			/**
			 * Allow users to filter the Notices based on the Event
			 *
			 * @param array $notices The array of strings that will be printed as notices
			 * @param WP_Post $event The Post object for the Event
			 */
			return apply_filters( 'tribe_eventbrite_notices', $notices, $event );
		}

		/**
		 * Action to create the Admin Notices
		 *
		 * @return void
		 */
		public function action_display_notices() {
			if ( empty( $_GET['post'] ) ) {
				return;
			}

			$event = get_post( absint( $_GET['post'] ) );

			if ( ! $event instanceof WP_Post ) {
				return false;
			}

			if ( ! tribe_is_event( $event->ID ) ) {
				return false;
			}

			$notices = $this->get_notices( $event );
			if ( empty( $notices ) ) {
				return;
			}

			$tags = array(
				'a' => array(
					'href' => array(),
					'title' => array(),
					'target' => array(),
					'rel' => array(),
				),
				'ul' => array(),
				'ol' => array(),
				'li' => array(),
				'br' => array(),
				'em' => array(),
				'strong' => array(),
				'b' => array(),
				'p' => array(),
			);

			// Include a template
			include_once $this->pluginPath . 'src/views/eventbrite/eb-admin-notices.php';

			/**
			 * Allow users to filter the Metakey based on Event Object
			 * @param string $metakey The Meta name
			 * @param WP_Post $event The Event Object
			 */
			$error_metakey = apply_filters( 'tribe_eventbrite_notices_key', '_tribe-eventbrite-notices', $event );
			delete_post_meta( $event->ID, $error_metakey );
		}

		/**
		 * Adding a new Notice inside of the Eventbrite
		 *
		 * @param  WP_Post|int  $event  The Event to add the Notice to
		 * @param  string  $message     The actual notice
		 * @param  array   $sent        The sent Data
		 *
		 * @return boolean
		 */
		public function throw_notice( $event, $message, $sent = array() ) {
			if ( is_numeric( $event ) ) {
				$event = get_post( $event );
			}

			if ( !$event instanceof WP_Post ) {
				return false;
			}

			if ( !tribe_is_event( $event->ID ) ) {
				return false;
			}

			if ( !empty( $sent ) ) {
				update_post_meta( $event->ID, self::EB_SAVED_META_DATA, $sent );
			}

			/**
			 * Allow users to filter the Metakey based on Event Object
			 * @param string $metakey The Meta name
			 * @param WP_Post $event The Event Object
			 */
			$error_metakey = apply_filters( 'tribe_eventbrite_notices_key', '_tribe-eventbrite-notices', $event );

			// The errors (flushed on page reload)
			return add_post_meta( $event->ID, $error_metakey, (string) $message );
		}

		/**
		 * Updates the Eventbrite information in WordPress and makes the
		 * API calls to EventBrite to update the listing on their side
		 *
		 * @link http://www.eventbrite.com/api/doc/
		 * @param int $postId the ID of the event being edited
		 * @uses $_POST
		 * @return void
		 */
		public function action_sync_event( $event ) {
			$event = get_post( $event );

			if ( ! is_object( $event ) || ! $event instanceof WP_Post ) {
				return;
			}

			if ( ! tribe_is_event( $event->ID ) ) {
				return;
			}

			// Clean if Register Event is not Yes
			if ( empty( $_POST['EventRegister'] ) || 'yes' !== $_POST['EventRegister'] ) {
				self::clear_details( $event );
				return;
			}

			$eventbrite_id = get_post_meta( $event->ID, '_EventBriteId', true );

			$args = array(
				'status' => ( ! empty( $_POST['EventBriteStatus'] ) ? esc_attr( wp_kses( $_POST['EventBriteStatus'], array() ) ) : 'draft' ),
				'show_tickets' => ( ! empty( $_POST['EventShowTickets'] ) ? esc_attr( wp_kses( $_POST['EventShowTickets'], array() ) ) : 'yes' ),
				'image_sync_mode' => ( isset( $_POST['EventBriteImageSyncMode'] ) ? (int) $_POST['EventBriteImageSyncMode'] : 1 ),
			);

			$api = Tribe__Events__Tickets__Eventbrite__API::instance();
			$error_triggered = false;

			if ( wp_is_post_revision( $event->ID ) ) {
				return $this->throw_notice( $event, __( 'This Event is a revision and cannot sync to Eventbrite.', 'tribe-eventbrite' ), $_POST );
			}

			if ( ! is_numeric( $eventbrite_id ) ) {
				$event_title = get_the_title( $event );
				if ( empty( $event_title ) ) {
					return $this->throw_notice( $event, __( 'An Event Title is required', 'tribe-eventbrite' ), $_POST );
				}

				$venue = get_post_meta( $event->ID, '_EventVenueID', true );
				if ( is_numeric( $venue ) ) {
					$venue = get_post( $venue );
				}

				if ( ! $venue instanceof WP_Post || ! tribe_is_venue( $venue->ID ) ) {
					return $this->throw_notice( $event, __( 'The venue is missing', 'tribe-eventbrite' ), $_POST );
				} else {
					$venue->metas = array(
						'title' => array(
							'value' => get_the_title( $venue->ID ),
							'message' => __( 'The Venue is missing the Title', 'tribe-eventbrite' ),
						),
						'address' => array(
							'value' => get_post_meta( $venue->ID, '_VenueAddress', true ),
							'message' => __( 'No Address for this Venue', 'tribe-eventbrite' ),
						),
						'city' => array(
							'value' => get_post_meta( $venue->ID, '_VenueCity', true ),
							'message' => __( 'This Venue is missing the City', 'tribe-eventbrite' ),
						),
					);

					$throw_notice = false;
					foreach ( $venue->metas as $name => $meta ) {
						if ( ! empty( $meta['value'] ) ) {
							continue;
						} else {
							$throw_notice = true;
						}
						$this->throw_notice( $event, $meta['message'], $_POST );
					}

					if ( $throw_notice ) {
						return false;
					}
				}

				$organizer = get_post_meta( $event->ID, '_EventOrganizerID', true );
				if ( is_numeric( $organizer ) ) {
					$organizer = get_post( $organizer );
				}

				if ( ! $organizer instanceof WP_Post || ! tribe_is_organizer( $organizer->ID ) ) {
					return $this->throw_notice( $event, __( 'An organizer is required', 'tribe-eventbrite' ), $_POST );
				}

				// make sure all required fields are present
				$required_fields = array(
					'EventBriteTicketName' => __( 'Ticket Name', 'tribe-eventbrite' ),
					'EventBriteTicketStartDate' => __( 'Date to Start Ticket Sales', 'tribe-eventbrite' ),
					'EventBriteTicketEndDate' => __( 'Date to End Ticket Sales', 'tribe-eventbrite' ),
					'EventBriteIsDonation' => __( 'Ticket Type', 'tribe-eventbrite' ),
					'EventBriteEventCost' => __( 'Ticket Cost', 'tribe-eventbrite' ),
					'EventBriteTicketQuantity' => __( 'Ticket Quantity', 'tribe-eventbrite' ),
					'EventBriteIncludeFee' => __( 'Ticket - Include Fee in Price', 'tribe-eventbrite' ),
				);

				$missing_fields = array();
				$sent_fields = array();
				$message = '';

				foreach ( $required_fields as $key => $label ) {
					if ( ! isset( $_POST[ $key ] ) || '' === $_POST[ $key ] || is_null( $_POST[ $key ] ) ) {
						$missing_fields[ $key ] = $label;
					}
				}

				// if all fields are missing, assume the fields weren't meant to be filled out
				if ( count( $missing_fields ) != count( $required_fields ) ) {
					// if ticket type is set to Donation or Free, allow cost to be set to null
					if ( isset( $_POST['EventBriteIsDonation'] ) && 0 != $_POST['EventBriteIsDonation'] ) {
						if ( isset( $missing_fields['EventBriteEventCost'] ) ) {
							unset( $missing_fields['EventBriteEventCost'] );
						}
					} elseif ( isset( $_POST['EventBriteEventCost'] ) && ! Tribe__Events__Cost_Utils::instance()->is_valid_cost( $_POST['EventBriteEventCost'], false ) ) {
						$missing_fields['EventBriteEventCost'] = __( 'Ticket Cost (must be numeric)', 'tribe-eventbrite' );
					}

					// if ticket type is set to free, fee inclusion to be set to null
					if ( isset( $_POST['EventBriteIsDonation'] ) && 2 === $_POST['EventBriteIsDonation'] ) {
						if ( isset( $missing_fields['EventBriteIncludeFee'] ) ) {
							unset( $missing_fields['EventBriteIncludeFee'] );
						}
					}

					if ( ! empty( $missing_fields ) ) {
						$html = '<ul>';
						foreach ( $missing_fields as $key => $message ) {
							$html .= '<li>' . esc_html( $message ) . '</li>';
						}
						$html .= '</ul>';
						$message = sprintf( __( 'Missing Fields: %s', 'tribe-eventbrite' ), $html );
						return $this->throw_notice( $event, $message, $_POST );
					}
				}
				// check the dates of the ticket
				if ( isset( $_POST['EventBriteTicketStartDate'] ) ) {
					$date_errors = array();

					// Get the event datetime data
					$event_timezone = class_exists( 'Tribe__Events__Timezones' )
						? Tribe__Events__Timezones::get_event_timezone_string( $event->ID )
						: '';

					// If we have an event-specific timezone we can also pull the UTC time directly
					$event_end_date = $event_timezone
						? strtotime( get_post_meta( $event->ID, '_EventEndDateUTC', true ) )
						: Tribe__Events__Tickets__Eventbrite__API::wp_strtotime( get_post_meta( $event->ID, '_EventEndDate', true ) );

					$datepicker_format = Tribe__Date_Utils::datepicker_formats( tribe_get_option( 'datepickerFormat' ) );

					// Build Start Date
					$ticket_start = Tribe__Date_Utils::datetime_from_format( $datepicker_format, $_POST['EventBriteTicketStartDate'] );
					$ticket_start .= ' ' . $_POST['EventBriteTicketStartHours'] . ':' . $_POST['EventBriteTicketStartMinutes'];
					$ticket_start .= ( isset( $_POST['EventBriteTicketStartMeridian'] ) ) ? $_POST['EventBriteTicketStartMeridian'] : null;

					// Escaping ticket_start
					$ticket_start = esc_attr( wp_kses( $ticket_start, array() ) );

					// Apply timezone
					if ( $event_timezone ) {
						$ticket_start = Tribe__Events__Timezones::to_utc( $ticket_start, $event_timezone );
						$ticket_start_timestamp = strtotime( $ticket_start );
					} else {
						$ticket_start_timestamp = Tribe__Events__Tickets__Eventbrite__API::wp_strtotime( $ticket_start );
					}

					// Build End Date
					$ticket_end = Tribe__Date_Utils::datetime_from_format( $datepicker_format, $_POST['EventBriteTicketEndDate'] );
					$ticket_end .= ' ' . $_POST['EventBriteTicketEndHours'] . ':' . $_POST['EventBriteTicketEndMinutes'];
					$ticket_end .= ( isset( $_POST['EventBriteTicketEndMeridian'] ) ) ? $_POST['EventBriteTicketEndMeridian'] : null;

					// Escaping ticket_end
					$ticket_end = esc_attr( wp_kses( $ticket_end, array() ) );

					// Apply timezone
					if ( class_exists( 'Tribe__Events__Timezones' ) ) {
						$ticket_end = Tribe__Events__Timezones::to_utc( $ticket_end, $event_timezone );
						$ticket_end_timestamp = strtotime( $ticket_end );
					} else {
						$ticket_end_timestamp = Tribe__Events__Tickets__Eventbrite__API::wp_strtotime( $ticket_end );
					}

					if ( $ticket_end_timestamp > $event_end_date ) {
						$date_errors[] = __( 'Ticket sales end date cannot be after the event ends', 'tribe-eventbrite' );
					}

					if ( $ticket_start_timestamp > $ticket_end_timestamp ) {
						$date_errors[] = __( 'Ticket sales start date cannot be after ticket sales end date', 'tribe-eventbrite' );
					}

					if ( $ticket_start_timestamp === $ticket_end_timestamp ) {
						$date_errors[] = __( 'Ticket sales start and end datetime must not be the same', 'tribe-eventbrite' );
					}

					if ( ! empty( $date_errors ) ) {
						$html = '<ul>';
						foreach ( $date_errors as $key => $message ) {
							$html .= '<li>' . esc_html( $message ) . '</li>';
						}
						$html .= '</ul>';
						$message = sprintf( __( 'The dates you have chosen for your ticket sales are inconsistent: %s', 'tribe-eventbrite' ), $html );
						return $this->throw_notice( $event, $message, $_POST );
					}
				}

				$cost = Tribe__Events__Cost_Utils::instance()->parse_cost_range( '00.00 ' . $_POST['EventBriteEventCost'], 2 );
				$cost = array_keys( $cost );
				$cost = end( $cost );

				$args['tickets'][] = array(
					'name' => $_POST['EventBriteTicketName'],
					'description' => $_POST['EventBriteTicketDescription'],
					'start' => $ticket_start_timestamp,
					'end' => $ticket_end_timestamp,
					'type' => $_POST['EventBriteIsDonation'],
					'cost' => $cost,
					'qty' => $_POST['EventBriteTicketQuantity'],
					'include_fee' => $_POST['EventBriteIncludeFee'],
				);
			}

			$api->sync_event( $event, $args );
		}

		/**
		 * Get the ticket costs from Eventbrite
		 *
		 * @param $cost the original cost of the event from tribe_get_cost()
		 * @param $post the TEC event to get the Eventbrite ticket costs from
		 * @param $withCurrencySymbol whether to add the currency symbol
		 *
		 * @return string $cost the cost of the Eventbrite tickets
		 * @see $this->get_cache_expiration()
		 */
		public function filter_get_cost( $cost, $post, $withCurrencySymbol ) {
			if ( is_null( $post ) ) {
				$post = get_the_ID();
			}

			$post = get_post( $post );

			if ( ! is_object( $post ) || ! $post instanceof WP_Post ) {
				return $cost;
			}

			// if the cache isn't expired we'll use the value stored there
			$cache_expiration = $this->get_cache_expiration();

			// Check if we already have the cost
			$cached_cost_key = 'tribe_eventbrite_cost_' . ( $withCurrencySymbol ? 'formatted_' : '' ) . $post->ID;
			$eb_cost         = $eb_cached_cost = get_transient( $cached_cost_key );

			if ( ! $eb_cached_cost ) {
				// the transient doesn't exist, check the postmeta (that was the pre 3.10 way of storing it)
				$postmeta_eb_cached_cost = get_post_meta( $post->ID, '_EventbriteCost', true );
				if ( ! empty( $postmeta_eb_cached_cost ) ) {
					if ( time() < $postmeta_eb_cached_cost['timestamp'] + $cache_expiration ) {
						// the cost is not expired, let's use it
						$eb_cost = $postmeta_eb_cached_cost['cost'];
					}
					// either we found a valid cached cost or we didn't, but delete the postmeta, we're not using it anymore
					delete_post_meta( $post->ID, '_EventbriteCost' );
				}

				// at this point, if we didn't get the cost from the postmeta or the transient, let's get it from Eventbrite
				$api     = Tribe__Events__Tickets__Eventbrite__API::instance();
				$eb_cost = $api->get_cost( $post );

				if ( $eb_cost ) {
					// Update the transient
					set_transient( $cached_cost_key, $eb_cost, $cache_expiration );
				} else {
					// we didn't find a value from Eventbrite, just return the original value
					return $cost;
				}
			}
			// If there cost is empty return the EB one
			if ( empty( $cost ) ) {
				$cost = $eb_cost;
			} else {
				$eb_free_ticket_label     = esc_attr__( 'Free', 'events-eventbrite' );
				$eb_donation_ticket_label = esc_attr__( 'Donation', 'events-eventbrite' );

				$eb_sorted_mins = array( $eb_free_ticket_label, $eb_donation_ticket_label );
				$cost_utils     = Tribe__Events__Cost_Utils::instance();
				$cost           = $cost_utils->merge_cost_ranges(
					$cost,
					$eb_cost,
					$withCurrencySymbol,
					$eb_sorted_mins
				);
			}

			// If there's more than one price, this will make them into a range
			if ( is_array( $cost ) ) {
				$cost = implode( apply_filters( 'tribe_eb_event_cost_separator', ' - ' ), $cost );
			}

			return apply_filters( 'tribe_eb_event_cost', $cost );
		}

		/**
		 * Clears/deletes all Eventbrite meta from an event
		 *
		 * @since 1.0
		 * @author jgabois & Justin Endler
		 * @param int $postId the ID of the event being edited
		 * @uses self::metaTags
		 * @return void
		 */
		public function clear_details( $event ) {
			$event = get_post( $event );

			if ( ! is_object( $event ) || ! $event instanceof WP_Post ) {
				return false;
			}

			foreach ( self::$metaTags as $meta ) {
				delete_post_meta( $event->ID, $meta );
			}
			return true;
		}

		/**
		 * retrieves data from an existing Eventbrite event
		 *
		 * @throws Exception
		 * @return mixed error on failure / json string of the event on success
		 */
		public function import_existing_events() {
			add_filter( 'tribe-post-origin', array( $this, 'filter_imported_origin' ) );

			$api = Tribe__Events__Tickets__Eventbrite__API::instance();

			$eventbrite_raw = ! empty( $_GET['eventbrite_selected_id'] ) ? $_GET['eventbrite_selected_id'] : null;
			$eventbrite_raw = ! empty( $_GET['eventbrite_id'] ) ? $_GET['eventbrite_id'] : $eventbrite_raw;
			$eventbrite_id = null;

			if ( empty( $eventbrite_raw ) ) {
				throw new Tribe__Events__Post_Exception( __( 'We were unable to import your Eventbrite event. Please verify the event id and try again.', 'tribe-eventbrite' ) );
			}

			if ( is_numeric( $eventbrite_raw ) ) {
				$eventbrite_id = self::sanitize_absint( $eventbrite_raw );
			} else {
				// The @ is required to prevent bad URL from throwing a Warning (5.2 compat)
				$url = @parse_url( $eventbrite_raw );
				if ( ! $url ) {
					throw new Tribe__Events__Post_Exception( __( 'Invalid URL for the event', 'tribe-eventbrite' ) );
				}

				if ( preg_match( '/-?([0-9]+)\/?$/', $url['path'], $eventbrite_match ) ) {
					$eventbrite_id = self::sanitize_absint( $eventbrite_match[1] );
				}
			}

			if ( ! $eventbrite_id ) {
				throw new Tribe__Events__Post_Exception( __( 'We were unable to import your Eventbrite event. Please verify the event id and try again.', 'tribe-eventbrite' ) );
			}

			$event = $api->get_event( $eventbrite_id, true );

			if ( ! $event ) {
				throw new Tribe__Events__Post_Exception( __( 'We were unable to import your Eventbrite event. Please verify the event id and try again.', 'tribe-eventbrite' ) );
			}

			if ( $api->is_event_imported( $event->id ) ) {
				throw new Tribe__Events__Post_Exception( __( 'Event already imported.', 'tribe-eventbrite' ) );
			}

			// insert new ECP event
			$postdata = array(
				'filter' => true,

				'post_title' => wp_kses_post( $event->name->text ),
				'post_type' => Tribe__Events__Main::POSTTYPE,
				'post_status' => Tribe__Events__Importer__Options::get_default_post_status( 'eventbrite' ),
				'post_content' => ! empty( $event->description ) ? wp_kses_post( (string) $event->description->html ) : '',
				'_EventBriteId' => $event->id,
				'_EventRegister' => 'yes',
			);

			// save a new organizer
			if ( ! empty( $event->organizer ) ) {
				$postdata['_OrganizerEventBriteID'] = $event->organizer->id;

				// don't create a new organizer if this one is already imported
				$organizer = $api->is_organizer_imported( $event->organizer->id );
				$organizerData = array();

				if ( ! $organizer ) {
					$organizerData['Organizer'] = $event->organizer->name;
				} else {
					$organizerData['OrganizerID'] = $organizer->ID;
				}

				$postdata['Organizer'] = $organizerData;
				$_POST['Organizer'] = $organizerData;
			}

			if ( ! empty( $event->venue ) ) {
				$postdata['_VenueEventBriteID'] = $event->venue->id;

				// Don't create a new venue if this one is already imported
				$venue = $api->is_venue_imported( $event->venue->id, $event->venue );

				$venueData = array();

				if ( ! $venue ) {
					$venueData['Address']  = ( ! empty( $event->venue->address->address_1 ) ) ? $event->venue->address->address_1 : null;
					$venueData['Address'] .= ( ! empty( $event->venue->address->address_2 ) ) ? $event->venue->address->address_2 : null;
					$venueData['Venue']    = ( ! empty( $event->venue->name ) ) ? $event->venue->name : null;
					$venueData['Country']  = ( ! empty( $event->venue->address->country ) ) ? $api->get_country_name( $event->venue->address->country ) : null;
					$venueData['Zip']      = ( ! empty( $event->venue->address->postal_code ) ) ? $event->venue->address->postal_code : null;
					$venueData['State']    = ( ! empty( $event->venue->address->region ) ) ? $event->venue->address->region : null;
					$venueData['Province'] = ( ! empty( $event->venue->address->region ) ) ? $event->venue->address->region : null;
					$venueData['City']     = ( ! empty( $event->venue->address->city ) ) ? $event->venue->address->city : null;
				} else {
					$venueData['VenueID'] = $venue->ID;
				}

				$postdata['Venue'] = $venueData;
				$_POST['Venue'] = $venueData;
			}

			// Setup the Correct action
			remove_action( 'tribe_events_update_meta', array( $this, 'action_sync_event' ), 20 );
			add_action( 'tribe_events_update_meta', array( $this, 'link_imported_event_data' ), 10, 2 );

			$start = strtotime( $event->start->local );
			$end   = strtotime( $event->end->local );

			$postdata['EventStartDate'] = date( Tribe__Date_Utils::DBDATEFORMAT, $start );
			$postdata['EventEndDate'] = date( Tribe__Date_Utils::DBDATEFORMAT, $end );
			$postdata['EventTimezone'] = $event->start->timezone;

			if ( 86400 !== ( $end - $start ) ) {
				$postdata['EventStartHour'] = date( Tribe__Date_Utils::HOURFORMAT, $start );
				$postdata['EventStartMinute'] = date( Tribe__Date_Utils::MINUTEFORMAT, $start );
				$postdata['EventStartMeridian'] = date( Tribe__Date_Utils::MERIDIANFORMAT, $start );

				$postdata['EventEndHour'] = date( Tribe__Date_Utils::HOURFORMAT, $end );
				$postdata['EventEndMinute'] = date( Tribe__Date_Utils::MINUTEFORMAT, $end );
				$postdata['EventEndMeridian'] = date( Tribe__Date_Utils::MERIDIANFORMAT, $end );
			} else {
				$postdata['EventAllDay'] = true;

				$postdata['EventStartHour'] = false;
				$postdata['EventStartMinute'] = false;

				$postdata['EventEndHour'] = false;
				$postdata['EventEndMinute'] = false;
			}

			$event_id = tribe_create_event( $postdata );

			if ( is_wp_error( $event_id ) ) {
				throw new Tribe__Events__Post_Exception( __( 'We were unable to import your Eventbrite event. Please try again.', 'tribe-eventbrite' ) );
			}

			// Update Eventbrite status and timezone information
			update_post_meta( $event_id, '_EventBriteStatus', $event->status );
			update_post_meta( $event_id, '_EventShowTickets', 'yes' );

			remove_filter( 'tribe-post-origin', array( $this, 'filter_imported_origin' ) );

			/**
			 * Whether to obtain the featured image set for the event on eventbrite.com and use it
			 * as the local featured image (ie, on WordPress).
			 *
			 * @var bool $synch     whether to synchronize
			 * @var int  $event_id  post ID of the event being imported
			 */
			if ( apply_filters( 'tribe_eb_pull_image', true, $event_id ) ) {
				$api->sync_image( $event_id );
			}

			return $event_id;
		}

		/**
		 * Given a valid datetime string, converts to the local WP timezone then returns the
		 * corresponding unix timestamp.
		 *
		 * Example, with a UTC datetime and assuming America/Vancouver as the local WP timezone:
		 *
		 *                 Input:                Output:
		 *       (actual)  2015-12-25T15:00:00Z  1451030400
		 *     (equal to)  1451055600            2015-12-25 08:00:00
		 *
		 * @param  string $datetime
		 * @return int    unix timestamp
		 */
		protected function convert_to_local_time( $datetime ) {
			return strtotime( $datetime ) + ( get_option( 'gmt_offset', 0 ) * HOUR_IN_SECONDS );
		}

		/**
		 * links existing data with an imported event from Eventbrite
		 *
		 * @since 1.0
		 * @author jgabois & Justin Endler
		 * @param  int $event_id the event ID
		 * @param  mixed $data the event's data
		 * @return void
		 */
		public function link_imported_event_data( $event_id, $data ) {

			$eb_event_id = $data['_EventBriteId'];
			$eb_organizer_id = $data['_OrganizerEventBriteID'];
			$eb_venue_id = isset( $data['_VenueEventBriteID'] ) ? $data['_VenueEventBriteID'] : false;

			$ecp_venue = get_post_meta( $event_id, '_EventVenueID', true );
			$ecp_organizer = get_post_meta( $event_id, '_EventOrganizerID', true );

			update_post_meta( $event_id, '_EventBriteId', $eb_event_id );
			update_post_meta( $event_id, '_EventRegister', 'yes' );

			if ( $ecp_organizer && $eb_organizer_id ) {
				update_post_meta( $ecp_organizer, '_OrganizerEventBriteID', $eb_organizer_id );

				if ( $ecp_venue && $eb_venue_id ) {
					update_post_meta( $ecp_venue, '_VenueEventBriteId' . $eb_organizer_id, $eb_venue_id );
				}
			}
		}

		/**
		 * returns filter value for tribe-post-origin.
		 * @since 1.0
		 * @author PaulHughes01
		 * @return string $origin
		 */
		public function filter_imported_origin() {
			$origin = 'eventbrite-tickets';
			return $origin;
		}

		/**
		 * add the options page for this plugin
		 *
		 * @return void
		 */
		public function add_option_page() {
			add_submenu_page(
				'/edit.php?post_type=' . Tribe__Events__Main::POSTTYPE,
				__( 'Import: Eventbrite ', 'tribe-eventbrite' ),
				__( 'Import: Eventbrite', 'tribe-eventbrite' ),
				'edit_posts',
				'import-eventbrite-events',
				array( $this, 'include_import_page' )
			);
		}

		/**
		 * include the import page view
		 *
		 * @return void
		 */
		public function include_import_page() {
			include_once $this->pluginPath . 'src/views/eventbrite/import-eventbrite-events.php';
		}

		/**
		 * the event brite meta box
		 *
		 * @global userdata - the current user data
		 * @param int $postId the ID of the current event
		 * @return void
		 */
		public function add_metabox( $post_id ) {
			// Fetch the Saved data
			$saved_raw_data = get_post_meta( $post_id, self::EB_SAVED_META_DATA, true );

			// Set the flag
			$has_valid_raw_data = false;

			// Make sure it only affects this request
			delete_post_meta( $post_id, self::EB_SAVED_META_DATA );

			foreach ( self::$metaTags as $tag ) {
				$name = ltrim( $tag, '_' );
				if ( ! empty( $saved_raw_data[ $name ] ) ) {
					$$tag = $saved_raw_data[ $name ];
					$has_valid_raw_data = true;
				} elseif ( $post_id ) {
					$val = get_post_meta( $post_id, $tag, true );
					$$tag = $val;
				} else {
					$$tag = '';
				}
			}

			$api = Tribe__Events__Tickets__Eventbrite__API::instance();
			$event = $api->get_event( $post_id );

			$_EventBriteId = ( isset( $event->id ) && is_numeric( $event->id ) ? $event->id : null );
			$isRegisterChecked = ( $has_valid_raw_data || ( isset( $event->id ) && is_numeric( $event->id ) ) ? true : false );
			$displayTickets = ( $_EventShowTickets == 'yes' ) ? true : false;
			$image_sync_mode = (int) get_post_meta( $post_id, '_eventbrite_image_sync_mode', true );

			$tribe_ecp = Tribe__Events__Main::instance();

			include_once( $this->pluginPath . 'src/views/eventbrite/eventbrite-meta-box-extension.php' );
		}

		/**
		 * displays the Eventbrite ticket form.
		 * Heavily modified by Paul Hughes with the release of TEC 3.0.
		 *
		 * @since 1.0
		 * @author jgabois & Justin Endler
		 * @param string $content the current html content
		 * @return string filtered $content
		 */
		public static function print_ticket_form() {
			tribe_get_template_part( 'eventbrite/hooks/ticket-form' );
			tribe_get_template_part( 'eventbrite/modules/ticket-form' );
		}

		public function notice_missing_token() {
			$api = Tribe__Events__Tickets__Eventbrite__API::instance();
			if ( $api->is_ready() ) {
				if ( ! empty( $_GET['success'] ) && ! empty( $_GET['page'] ) && ! empty( $_GET['post_type'] ) && ! empty( $_GET['tab'] ) && 'addons' === $_GET['tab'] ) {
					echo '<div class="updated tribe-notice">';
					echo '<p>' . esc_attr__( 'Success! You have authorized your Eventbrite Application Key.', 'tribe-eventbrite' ) . '</p>';
					echo '</div>';
				}

				return;
			}

			$dismissed = get_transient( 'tribe-eb-dimissed-not_ready' );

			if ( ! empty( $_GET['tribe-eb-dismiss-notice'] ) ) {
				$slug = sanitize_title( $_GET['tribe-eb-dismiss-notice'] );
				$dimissed = (int) set_transient( 'tribe-eb-dimissed-' . $slug, 1, WEEK_IN_SECONDS );

				return;
			}

			if ( $dismissed ) {
				return;
			}

			?>
			<script type="text/javascript">
			( function( $ ) {
				$( document ).ready( function() {
					$( '.tribe-eventbrite-notice' ).on( 'click', '.notice-dismiss', function() {
						var append = [ window.location.href ];
						if ( -1 === window.location.href.indexOf( '?' ) ) {
							append.push( '?' )
						} else {
							append.push( '&' )
						}
						append.push( 'tribe-eb-dismiss-notice=' );
						append.push( $( this ).parents( '.tribe-eventbrite-notice' ).data( 'ref' ) )
						window.location.href = append.join('');
					} );
				} );
			}( jQuery ) );
			</script>
			<div class="notice updated is-dismissible tribe-eventbrite-notice" data-ref="not_ready">
			<p>
			<?php
			echo sprintf( __( 'Welcome to The Events Calendar: Eventbrite Tickets! We appreciate your support and hope you enjoy the functionality this add-on has to offer. Before jumping into it, make sure you\'ve reviewed our %sEventbrite Tickets new user primer%s so you\'re familiar with the basics.', 'tribe-eventbrite' ),
				'<a href="' . Tribe__Events__Main::$tribeUrl . 'support/documentation/eventbrite-tickets-new-user-primer/?utm_source=helptab&utm_medium=promolink&utm_campaign=plugin" target="_blank">',
				'</a>' );
			?>
			</p>
			<p>
			<?php
			echo sprintf( __( 'Add your %sEventbrite Application Keys%s to your %sEventbrite settings page%s. Don\'t have an Application Key? %sSign up for Eventbrite now%s and follow our %snew user primer%s to create a new Eventbrite Application for your WordPress site. Then simply create a new event or modify an existing one and enable Eventbrite Tickets to add and sell tickets.', 'tribe-eventbrite' ),
				'<a href="https://www.eventbrite.com/myaccount/apps/?ref=etckt" target="_blank">',
				'</a>',

				'<a href="' . esc_url( admin_url( 'edit.php?post_type=tribe_events&page=' . Tribe__Settings::instance()->adminSlug . '&tab=addons' ) ) . '">',
				'</a>',

				'<a href="http://www.eventbrite.com/r/etp" target="_blank">',
				'</a>',

				'<a href="' . Tribe__Events__Main::$tribeUrl . 'support/documentation/eventbrite-tickets-new-user-primer/?utm_source=helptab&utm_medium=promolink&utm_campaign=plugin" target="_blank">',
				'</a>' );
			?>
			</p>
			</div>
			<?php
		}

		public function notice_edit_event() {
			global $post_id, $pagenow;

			$errors = array();

			// Bail if we are not within the post editor
			if ( 'post.php' !== $pagenow ) {
				return;
			}

			if ( ! tribe_is_event( $post_id ) ) {
				return;
			}

			$api = Tribe__Events__Tickets__Eventbrite__API::instance();

			// Bail unless the event is linked to Eventbrite
			$event = $api->get_event( $post_id );
			if ( $event && empty( $event->status ) && empty( $event->id ) ) {
				// Inform the user if the event is currently in "draft" mode (on Eventbrite)
				if ( 'draft' === $event->status && ! empty( $event->ticket_classes ) ) {
					$errors[] = __( "Eventbrite status is set to DRAFT. You can update this in the 'Eventbrite Information' section further down this page.", 'tribe-eventbrite' );
				}

				// Inform the user if tickets have not yet been added on Eventbrite
				if ( empty( $event->ticket_classes ) && 'draft' !== $event->status ) {
					$errors[] = __( 'You did not create any tickets for your event.  You will not be able to publish this event on Eventbrite unless you first add a ticket at Eventbrite.com.', 'tribe-eventbrite' );
				}
			}

			if ( empty( $errors ) ) {
				return;
			}

			// Display any appropriate error messages
			foreach ( $errors as $message ) {
				printf( '<div class="error"><p>%s</p></div>', $message );
			}
		}

		/**
		 * Add the eventbrite importer toolbar item.
		 *
		 * @since 1.0.1
		 * @author PaulHughes01
		 * @return void
		 */
		public function addEventbriteToolbarItems() {
			global $wp_admin_bar;

			if ( current_user_can( 'publish_tribe_events' ) ) {
				$import_node = $wp_admin_bar->get_node( 'tribe-events-import' );
				if ( ! is_object( $import_node ) ) {
					$wp_admin_bar->add_menu( array(
						'id' => 'tribe-events-import',
						'title' => __( 'Import', 'tribe-eventbrite' ),
						'parent' => 'tribe-events-import-group',
					) );
				}
			}

			if ( current_user_can( 'publish_tribe_events' ) ) {
				$url = add_query_arg( array(
					'post_type' => Tribe__Events__Main::POSTTYPE,
					'page' => 'events-importer',
					'tab' => 'eventbrite',
				), admin_url( 'edit.php' ) );

				$wp_admin_bar->add_menu( array(
					'id' => 'tribe-eventbrite-import',
					'title' => __( 'Eventbrite', 'tribe-eventbrite' ),
					'href' => esc_url( $url ),
					'parent' => 'tribe-events-import',
				) );
			}
		}

		/**
		 * Return additional action for the plugin on the plugins page.
		 *
		 * @param array $actions
		 * @since 2.0.8
		 * @return array
		 */
		public function addLinksToPluginActions( $actions ) {
			if ( class_exists( ' Tribe__Events__Main' ) ) {
				$actions['settings'] = '<a href="' . esc_url( add_query_arg( array( 'post_type' => Tribe__Events__Main::POSTTYPE, 'page' => 'import-eventbrite-events' ), esc_url( admin_url( 'edit.php' ) ) ) ) .'">' . __( 'Import Events', 'tribe-eventbrite' ) . '</a>';
			}
			return $actions;
		}

		/**
		 * Adds the Eventbrite logo to the editing events form.
		 *
		 * @since 1.0.3
		 * @author PaulHughes01
		 * @return void
		 */
		public function addEventbriteLogo() {
			$image_url = trailingslashit( $this->pluginUrl ) . 'src/resources/images/eventbritelogo.png';
			echo '<img class="tribe-eb-logo" src="' . esc_url( $image_url ) . '" />';
		}

		/**
		 * Return the forums link as it should appear in the help tab.
		 *
		 * @param $content
		 * @since 1.0.3
		 * @return string
		 */
		public function _link_support_forum( $content ) {
			$promo_suffix = '?utm_source=helptab&utm_medium=promolink&utm_campaign=plugin';
			return Tribe__Events__Main::$tribeUrl . 'support/forums/' . $promo_suffix;
		}

		/**
		 * Filter template paths to add the eventbrite plugin to the queue
		 *
		 * @param array $paths
		 * @return array $paths
		 * @author Jessica Yazbek
		 * @since 3.2.1
		 */
		public function add_eventbrite_template_paths( $paths ) {
			$paths['eventbrite'] = self::instance()->pluginPath;
			return $paths;
		}

		/**
		 * Add the Eventbrite Fields to the Add-ons page on the correct position
		 *
		 * @param array $fields The array of existing fields added to the addons page
		 * @return array
		 */
		public function add_addon_fields( $fields = array() ) {
			$new_fields = array(
				'eventbrite-title' => array(
					'type' => 'html',
					'html' => '<h3>' . esc_attr__( 'Eventbrite', 'tribe-eventbrite' ) . '</h3>',
				),

				'eventbrite-info-content' => array(
					'type' => 'html',
					'html' => '<p style="line-height: 2em;">' . sprintf( __( 'Eventbrite Tickets needs to be connected to your Eventbrite account via an App Key/Client Secret. If you haven\'t yet configured one, do so at %1$s. When configuring your application, make sure to set the OAuth Redirect URI set to %2$s. Once your App Key and Client Secret are configured plug them in below, "Save" the page, and hit the "Get Authorization" button that appears once the Key + Secret have saved. After you\'ve been authorized you\'ll be ready to start syncing Events!', 'tribe-eventbrite' ), '<a href="http://m.tri.be/vp" target="_blank">' . __( 'http://m.tri.be/vp', 'tribe_eventbrite' ) . '</a>', '"<a href="' . home_url( '/tribe-oauth/eventbrite' ) . '" target="_blank"><em>' . home_url( '/tribe-oauth/eventbrite' ) . '</em></a>"' ) . '</p>',
				),

				'eventbrite-form-content-start' => array(
					'type' => 'html',
					'html' => '<div class="tribe-settings-form-wrap">',
				),

				'eventbrite-api_auth_url' => array(
					'type' => 'text',
					'size' => 'large',
					'label' => __( 'Auth URL', 'tribe-eventbrite' ),
					'tooltip' => __( 'When configuring your application, make sure to set the <strong>OAuth Redirect URI</strong> on Eventbrite to the value above. <strong>We recommend you copy and paste this as it must be identical to what you see above.</strong>', 'tribe-eventbrite' ),
					'default' => home_url( '/tribe-oauth/eventbrite/' ),
					'value' => home_url( '/tribe-oauth/eventbrite/' ),
				),

				'eventbrite-app_key' => array(
					'type' => 'text',
					'label' => __( 'Application Key', 'tribe-eventbrite' ),
					'validation_type' => 'alpha_numeric',
				),
				'eventbrite-client_secret' => array(
					'type' => 'text',
					'label' => __( 'Client Secret', 'tribe-eventbrite' ),
					'validation_type' => 'alpha_numeric',
				),
				'eventbrite-authorize' => array(
					'type' => 'html',
					'html' => '',
				),

				'eventbrite-form-content-end'   => array(
					'type' => 'html',
					'html' => '</div>',
				),
			);

			$api = Tribe__Events__Tickets__Eventbrite__API::instance();
			if ( ! empty( $api->key ) && ! empty( $api->secret ) ) {
				$new_fields['eventbrite-authorize']['html'] = get_submit_button( esc_attr__( 'Get Authorization', 'tribe-eventbrite' ), 'secondary', 'tribe-eventbrite-authorize', true );
			} else {
				$new_fields['eventbrite-info-content']['html'] = '<div style="float:right; margin-left: 20px; margin-bottom: 5px;"><iframe src="https://player.vimeo.com/video/126437922?title=0&byline=0&portrait=0" width="350" height="196" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe><p style="text-align: right;margin-top: 5px;">' . sprintf( __( 'See our %s.' ), '<a href="http://m.tri.be/vq" target="_blank">' . esc_attr__( 'detailed walkthrough' ) . '</a>' ) . '</p></div>' . $new_fields['eventbrite-info-content']['html'];
			}

			return array_merge( (array) $fields, $new_fields );
		}
	} // end Tribe__Events__Tickets__Eventbrite__Main class

} // end if !class_exists Tribe__Events__Tickets__Eventbrite__Main

