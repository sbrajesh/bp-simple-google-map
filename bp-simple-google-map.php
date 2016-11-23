<?php
/**
 * Plugin Name: BuddyPress Simple Google Map
 * Plugin URI: http://buddydev.com/plugins/bp-simple-google-map/
 * Author: Brajesh Singh
 * Author URI: http://buddydev.com/members/sbrajesh/
 * Version:1.0.1
 * Description: The current version allows adding static google map to buddypress Groups. Based on the community feedback,In future version, we may extend it to user maps too.
 */
//singleton class,
class BPSimpleGoogleMap {

	private static $instance;

	private function __construct() {

		//add hooks
		add_action( 'bp_init', array( $this, 'load_core' ) );
		add_action( 'bp_loaded', array( $this, 'load_textdomain' ) );

	}

	public static function get_instance() {

		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	function load_core() {
		$path = plugin_dir_path( __FILE__ );
		include_once( $path . "group-extension.php" );
		if ( is_admin() || is_network_admin() ) {
			require_once( $path . "admin.php" );
		}

	}

//load the textdomain for this plugin
	function load_textdomain() {
		$locale = apply_filters( 'bpsgmap_load_textdomain_get_locale', get_locale() );

		// if load .mo file
		if ( ! empty( $locale ) ) {
			$mofile_default = sprintf( '%slanguages/%s.mo', plugin_dir_path( __FILE__ ), $locale );

			$mofile = apply_filters( 'bpsgmap_load_textdomain_mofile', $mofile_default );

			if ( file_exists( $mofile ) ) {
				// make sure file exists, and load it
				load_textdomain( "bpsgmap", $mofile );
			}
		}
	}

	function get_group_location() {
		global $bp;
		$group_id = bp_get_group_id( $bp->groups->current_group );
		$loc      = groups_get_groupmeta( $group_id, "group_location" );

		return $loc;
	}

	function build_map_url( $settings, $loc = null ) {
		if ( ! $loc ) {
			$loc = self::get_group_location();
		}
		$size   = $settings['map_width'] . "x" . $settings['map_height'];
		$url    = 'http://maps.googleapis.com/maps/api/staticmap?';
		$params = array(
			"center"  => $loc,
			"sensor"  => 'false',
			'zoom'    => $settings['map_zoom_level'],
			'maptype' => $settings['map_type'],
			'size'    => $size,
			'markers' => 'color:blue|size=mid|label:S|' . $loc
		);
		$q      = http_build_query( $params );
		$url    = $url . $q;

		return $url;
	}

//get the image for group
	function get_group_map_image() {
		$settings = self::get_admin_settings();


		$loc = $this->get_group_location();
		if ( empty( $loc ) ) {
			return false;
		}

		$url = self::build_map_url( $settings, $loc );

		return "<img src='" . $url . "'alt='" . esc_attr( $loc ) . "' />";
	}

	function get_admin_settings() {
		$default  = array( 'map_type' => 'roadmap', 'map_zoom_level' => 6, 'map_height' => 640, 'map_width' => 640 );
		$settings = get_site_option( "bpgsmap_settings", $default );

		return maybe_unserialize( $settings );
	}


}

//just initialize
$_bpsgmap_instance = BPSimpleGoogleMap::get_instance();

//widget will show in group sidebar the map of the group
class BpSimpleGoogleMapWidget extends WP_Widget {

	function __construct() {
		parent::__construct( false, __( 'BP Simple Google Map', 'bpsgmap' ) );
	}

//currently we only support group, so hide if it is not group
	function widget( $args, $instance ) {
		if ( ! ( bp_is_group() && ! BPSimpleGoogleMapGroupHelper::is_map_disabled() ) ) {
			return;
		}//currently we are only supporting the group maps
		//display map
		$location = BPSimpleGoogleMap::get_group_location();

		if ( ! $location ) {
			return;
		}
		extract( $args );
		$url = BPSimpleGoogleMap::build_map_url( $instance, $location );
		echo $before_widget;
		echo $before_title;
		echo $instance['title'];
		echo $after_title;
		echo "<img src='" . $url . "' alt='" . esc_attr( $location ) . "' />";
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;


		if ( $new_instance['title'] ) {
			$instance['title'] = $new_instance['title'];
		}


		if ( $new_instance['map_type'] ) {
			$instance['map_type'] = $new_instance['map_type'];
		}


		if ( $new_instance['map_zoom_level'] ) {
			$instance['map_zoom_level'] = $new_instance['map_zoom_level'];
		}

		if ( $new_instance['map_width'] ) {
			$instance['map_width'] = $new_instance['map_width'];
		}

		if ( $new_instance['map_height'] ) {
			$instance['map_height'] = $new_instance['map_height'];
		}

		return $instance;
	}

	function form( $instance ) {
		$instance       = wp_parse_args( (array) $instance, array(
			'title'          => __( 'Google Map', 'bpsgmap' ),
			'map_height'     => 200,
			'map_width'      => 190,
			'map_zoom_level' => 6,
			'map_type'       => 'roadmap'
		) );
		$title          = strip_tags( $instance['title'] );
		$map_width      = absint( $instance['map_width'] );//map_width
		$map_height     = absint( $instance['map_height'] );//map_height
		$map_type       = $instance['map_type'];//map type?
		$map_zoom_level = $instance['map_zoom_level'];//zoom level
		?>
		<p>
			<label for="bp-simple-google-map-title"><strong><?php _e( 'Title:', 'bpsgmap' ); ?> </strong>
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
				       name="<?php echo $this->get_field_name( 'title' ); ?>" type="text"
				       value="<?php echo attribute_escape( $title ); ?>" style="width: 100%"/>
			</label>
		</p>
		<p>
			<label for="bp-simple-google-map-type"><?php _e( 'Map Type', 'bpsgmap' ); ?>
				<select class="widefat" id="<?php echo $this->get_field_id( 'map_type' ); ?>"
				        name="<?php echo $this->get_field_name( 'map_type' ); ?>" style="width: 30%">
					<option value='roadmap' <?php if ( $map_type == 'roadmap' ) {
						echo 'selected="selected"';
					} ?>><?php _e( 'Roadmap', 'bpsgmap' ); ?></option>
					<option value='satellite' <?php if ( $map_type == 'satellite' ) {
						echo 'selected="selected"';
					} ?>><?php _e( 'Satellite', 'bpsgmap' ); ?></option>
					<option value='terrain' <?php if ( $map_type == 'terrain' ) {
						echo 'selected="selected"';
					} ?>><?php _e( 'Terrain', 'bpsgmap' ); ?></option>
					<option value='hybrid' <?php if ( $map_type == 'hybrid' ) {
						echo 'selected="selected"';
					} ?>><?php _e( 'Hybrid', 'bpsgmap' ); ?></option>
				</select>
			</label>
		</p>
		<p>
			<label for="bp-simple-google-map-zoom-level"><?php _e( 'Zoom Level', 'bpsgmap' ); ?>
				<select class="widefat" id="<?php echo $this->get_field_id( 'map_zoom_level' ); ?>"
				        name="<?php echo $this->get_field_name( 'map_zoom_level' ); ?>" style="width: 30%">
					<?php for ( $i = 0; $i <= 21; $i ++ ): ?>
						<option value="<?php echo $i; ?>" <?php if ( $map_zoom_level == $i ) {
							echo 'selected="selected"';
						} ?>><?php echo $i; ?></option>
					<?php endfor; ?>
				</select>
			</label>
		</p>
		<p>
			<label for="bp-simple-google-map-width"><?php _e( 'Map Width', 'bpsgmap' ); ?>
				<input class="widefat" id="<?php echo $this->get_field_id( 'map_width' ); ?>"
				       name="<?php echo $this->get_field_name( 'map_width' ); ?>" type="text" size="3"
				       value="<?php echo $map_width; ?>"/> px
			</label>
		</p>
		<p>
			<label for="bp-simple-google-map-height"><?php _e( 'Map Height(max 640)', 'bpsgmap' ); ?>
				<input class="widefat" id="<?php echo $this->get_field_id( 'map_height' ); ?>"
				       name="<?php echo $this->get_field_name( 'map_height' ); ?>" type="input" size="3"
				       value="<?php echo $map_height; ?>"/>px
			</label>
		</p>
		<?php
	}

}//end of widget

//register widget
function bpsimplegoolemap_register_widgets() {
	add_action( 'widgets_init', create_function( '', 'return register_widget("BpSimpleGoogleMapWidget");' ) );
}

//register the widget
add_action( 'bp_loaded', 'bpsimplegoolemap_register_widgets' );

//always implement as singleton if you add some actions in constructor
class BPSimpleGoogleMapGroupHelper {
	static $instance;

	private function __construct() {


		add_action( 'bp_before_group_settings_admin', array( &$this, 'group_disable_form' ) );
		add_action( 'bp_before_group_settings_creation_step', array( &$this, 'group_disable_form' ) );
		add_action( 'groups_group_settings_edited', array( &$this, 'save_group_prefs' ) );
		add_action( 'groups_create_group', array( &$this, 'save_group_prefs' ) );
		add_action( 'groups_update_group', array( &$this, 'save_group_prefs' ) );


	}

	function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new BPSimpleGoogleMapGroupHelper();
		}

		return self::$instance;
	}

//check if the group yt is enabled
	function group_disable_form() {
		?>

		<div class="checkbox" id="bpsgmap_disable">
			<label><input type="checkbox" name="group-disable-bpsgmap" id="group-disable-bpsgmap"
			              value="yes" <?php if ( self::is_map_disabled() ): ?> checked="checked"<?php endif; ?>/> <?php _e( 'Disable Map', 'bpsgmap' ) ?>
			</label>
		</div>
		<?php

	}


	function save_group_prefs( $group_id ) {
		$disable = $_POST["group-disable-bpsgmap"];
		groups_update_groupmeta( $group_id, "bpsgmap_is_disabled", $disable );//save preference
	}

	function is_map_disabled() {

		$current_group_id = self::get_group_id();//get current group id
		//check preference
		$is_disabled = groups_get_groupmeta( $current_group_id, 'bpsgmap_is_disabled' );
		if ( ! empty( $is_disabled ) && $is_disabled == 'yes' ) {
			return true;
		}

		return false;


	}

//helper function, get current group id
	function get_group_id() {
		global $bp;
		if ( bp_is_group_create() ) {
			$group_id = $_COOKIE['bp_new_group_id'];
		} else if ( bp_is_group() ) {
			$group_id = $bp->groups->current_group->id;
		}

		return $group_id;
	}

}

BPSimpleGoogleMapGroupHelper::get_instance();//instantiate
?>
