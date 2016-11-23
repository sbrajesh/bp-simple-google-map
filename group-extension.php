<?php

//handle everything except the front end display

class BPSimpleGoogleMap_Group_Extension extends BP_Group_Extension {
	var $visibility = 'public'; // 'public' will show your extension to non-group members, 'private' means you have to be a member of the group to view your extension.

	var $enable_create_step = false; // enable create step
	var $enable_nav_item = false; //show on front end
	var $enable_edit_item = false; //

	function __construct() {

		$this->name = __( 'Map', 'bpsgmap' );
		$this->slug = 'location';
		if ( ! BPSimpleGoogleMapGroupHelper::is_map_disabled() ) {
			$this->create_step_position = 31;
			$this->nav_item_position    = 41;
			$this->enable_create_step   = true;
			$this->enable_edit_item     = true;
			$this->enable_nav_item      = true;
		}


	}

	function BPSimpleGoogleMap_Group_Extension() {

		$this->__construct();
	}

	//on group crate step
	function create_screen() {
		if ( ! bp_is_group_creation_step( $this->slug ) ) {
			return false;
		}
		//map form
		$this->admin_form();
		wp_nonce_field( 'groups_create_save_' . $this->slug );
	}

	//on group create save
	function create_screen_save() {
		global $bp;

		check_admin_referer( 'groups_create_save_' . $this->slug );
		$group_id = $bp->groups->new_group_id;

		//print_r($cats);
		if ( ! groups_update_groupmeta( $group_id, 'group_location', $_POST['group_location'] ) ) {
			bp_core_add_message( __( 'There was an error updating group location, please try again.', 'bpsgmap' ), 'error' );
		} else {
			//bp_core_add_message( __( 'Map location saved successfully.', 'bpsgmap' ) );
		}
	}

	//group admin edit page
	function edit_screen() {
		if ( ! bp_is_group_admin_screen( $this->slug ) ) {
			return false;
		} ?>

		<h2><?php echo esc_attr( $this->name ) ?></h2>
		<?php
		$this->admin_form();


		wp_nonce_field( 'groups_edit_save_' . $this->slug );
		?>
		<p><input type='submit' value="<?php _e( 'Save Changes', 'bpsgmap' ) ?> &rarr;" id='map_save' name='save'/></p>
		<?php
	}

	//on save
	function edit_screen_save() {
		global $bp;

		if ( ! isset( $_POST['group_location'] ) ) {
			return false;
		}

		check_admin_referer( 'groups_edit_save_' . $this->slug );


		$group_id         = $bp->groups->current_group->id;
		$old_location     = groups_get_groupmeta( $group_id, 'group_location' );
		$current_location = $_POST['group_location'];
		if ( $old_location == $current_location || empty( $current_location ) ) {
			bp_core_add_message( __( 'Nothing Updated!', 'bpsgmap' ), 'error' );
		} else {
			if ( ! groups_update_groupmeta( $group_id, 'group_location', $current_location ) ) {
				bp_core_add_message( __( 'There was an error updating group location, please try again.', 'bpsgmap' ), 'error' );
			} else {
				bp_core_add_message( __( 'Location updated successfully.', 'bpsgmap ' ) );
			}
		}


		bp_core_redirect( bp_get_group_permalink( $bp->groups->current_group ) . '/admin/' . $this->slug );
	}

	//display the map using plugins template for group
	function display() {
		$gmap      = BPSimpleGoogleMap::get_instance();
		$map_image = $gmap->get_group_map_image();
		if ( ! $map_image ) {
			echo '<p>' . __( 'The location of this group is not specified yet.', 'bpsgmap' ) . '</p>';
		} else {
			echo $map_image;
		}
	}

	function admin_form() {
		$group_id = bp_get_group_id();
		?>
		<div class="gmap-group-form">
			<label
				for='group_location'><?php _e( 'Please enter Group\'s Location(e.g. City Hall, New York, NY).', "bpsgmap" ); ?></label>
			<input id="group_location" type="text" name="group_location"
			       value="<?php echo groups_get_groupmeta( $group_id, 'group_location' ); ?>"/>
			<?php wp_nonce_field( 'groups_create_save_' . $this->slug ); ?>
			<p>  &nbsp;</p>
		</div>
		<?php

	}
}

bp_register_group_extension( 'BPSimpleGoogleMap_Group_Extension' );
?>