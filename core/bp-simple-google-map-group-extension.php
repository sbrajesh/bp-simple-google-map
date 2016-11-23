<?php

class BP_Simple_Google_Map_Group_Extension extends BP_Group_Extension {

	public function __construct() {

		$is_disabled = bpsgm_is_map_disabled();

		$show = $is_disabled ? 'noone' : 'anyone';

		$args = array(
			'name'              => __( 'Map', 'bp-simple-google-map' ),
			'slug'              => 'location',
			'nav_item_position' => 41,
			'show_tab'          =>  $show,
			'screens'           => array(
				'create'    => array(
					'enabled'   => ! $is_disabled,
					'position'  => 31,
				),
				'edit'    => array(
					'enabled'   => ! $is_disabled,
					'position'  => 31,
				),
			),
		);

		parent::init( $args );

	}

	//on group crate step
	public function create_screen( $group_id = null ) {

		if ( ! bp_is_group_creation_step( $this->slug ) ) {
			return false;
		}
		//map form
		$this->admin_form();
		//wp_nonce_field( 'groups_create_save_' . $this->slug );
	}

	//on group create save
	public function create_screen_save( $group_id = null ) {
		$bp = buddypress();

		//check_admin_referer( 'groups_create_save_' . $this->slug );
		$group_id = $bp->groups->new_group_id;

		if ( ! groups_update_groupmeta( $group_id, 'group_location', $_POST['group_location'] ) ) {
			bp_core_add_message( __( 'There was an error updating group location, please try again.', 'bp-simple-google-map' ), 'error' );
		} else {
			//bp_core_add_message( __( 'Map location saved successfully.', 'bp-simple-google-map' ) );
		}
	}

	//group admin edit page
	public function edit_screen( $group_id = null ) {
		if ( ! bp_is_group_admin_screen( $this->slug ) ) {
			return false;
		} ?>

		<h2><?php echo esc_attr( $this->name ) ?></h2>
		<?php
		$this->admin_form();

		//wp_nonce_field( 'groups_edit_save_' . $this->slug );
		?>
		<p><input type='submit' value="<?php _e( 'Save Changes', 'bp-simple-google-map' ) ?> &rarr;" id='map_save' name='save'/></p>
		<?php
	}

	//on save
	public function edit_screen_save( $group_id = null ) {
		global $bp;

		if ( ! isset( $_POST['group_location'] ) ) {
			return false;
		}

		//check_admin_referer( 'groups_edit_save_' . $this->slug );


		$group_id         = $bp->groups->current_group->id;
		$old_location     = groups_get_groupmeta( $group_id, 'group_location' );
		$current_location = $_POST['group_location'];
		if ( $old_location == $current_location || empty( $current_location ) ) {
			bp_core_add_message( __( 'Nothing Updated!', 'bp-simple-google-map' ), 'error' );
		} else {
			if ( ! groups_update_groupmeta( $group_id, 'group_location', $current_location ) ) {
				bp_core_add_message( __( 'There was an error updating group location, please try again.', 'bp-simple-google-map' ), 'error' );
			} else {
				bp_core_add_message( __( 'Location updated successfully.', 'bpsgmap ' ) );
			}
		}


		bp_core_redirect( bp_get_group_permalink( $bp->groups->current_group ) . '/admin/' . $this->slug );
	}

	//display the map using plugins template for group
	public function display( $group_id = null ) {

		$map_image = bpsgm_get_group_map_image();
		if ( ! $map_image ) {
			echo '<p>' . __( 'The location of this group is not specified yet.', 'bp-simple-google-map' ) . '</p>';
		} else {
			echo $map_image;
		}
	}

	public function admin_form( ) {
		$group_id = bpsgm_get_current_group_id();
		?>
		<div class="gmap-group-form">
			<label	for='group_location'><?php _e( "Please enter Group's Location(e.g. City Hall, New York, NY).", 'bp-simple-google-map' ); ?></label>

			<input id="group_location" type="text" name="group_location"  value="<?php echo groups_get_groupmeta( $group_id, 'group_location' ); ?>"/>
			<?php wp_nonce_field( 'groups_create_save_' . $this->slug ); ?>
			<p>  &nbsp;</p>
		</div>
		<?php
	}
}

bp_register_group_extension( 'BP_Simple_Google_Map_Group_Extension' );