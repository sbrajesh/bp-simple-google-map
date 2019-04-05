<?php
/**
 * Plugin Name: BuddyPress Simple Google Map
 * Plugin URI: https://buddydev.com/plugins/bp-simple-google-map/
 * Author: BuddyDev
 * Author URI: https://buddydev.com/
 * Version: 1.0.4
 * Description: The current version allows adding static google map to BuddyPress Groups. Based on the community feedback, In future version, we may extend it to user maps too.
 */

//singleton class,
class BP_Simple_Google_Map {

	private static $instance;

	private function __construct() {

		//add hooks
		add_action( 'bp_loaded', array( $this, 'load' ) );
		add_action( 'bp_init', array( $this, 'load_textdomain' ) );
	}

	public static function get_instance() {

		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Load core
	 */
	public function load() {

		if ( ! bp_is_active( 'groups' ) ) {
			return ;
		}

		$path = plugin_dir_path( __FILE__ );
		require_once $path . 'core/bp-simple-google-map-functions.php' ;
		require_once $path . 'core/bp-simple-google-map-widget.php' ;
		require_once $path . 'core/bp-simple-google-map-group-settings.php' ;
		require_once $path . 'core/bp-simple-google-map-group-extension.php' ;

		if ( is_admin() || is_network_admin() ) {
			require_once( $path . 'bp-simple-google-map-admin.php' );
		}
	}

	/**
	 * Load translation files
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'bp-simple-google-map', false, dirname( plugin_basename( __FILE__ ) ) .'/languages' );
	}
}

BP_Simple_Google_Map::get_instance();
