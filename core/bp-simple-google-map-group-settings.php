<?php

/**
 * Helps save group preference
 *
 * Class BP_Simple_Google_Map_Group_Settings_Helper
 */

class BP_Simple_Google_Map_Group_Settings_Helper {
	private static $instance;

	private function __construct() {
		add_action( 'bp_before_group_settings_admin', array( $this, 'group_disable_form' ) );
		add_action( 'bp_before_group_settings_creation_step', array( $this, 'group_disable_form' ) );
		add_action( 'groups_group_settings_edited', array( $this, 'save_group_prefs' ) );
		add_action( 'groups_create_group', array( $this, 'save_group_prefs' ) );
		add_action( 'groups_update_group', array( $this, 'save_group_prefs' ) );
	}

	public static function get_instance() {

		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function group_disable_form() {
		?>

		<div class="checkbox" id="bpsgmap_disable">
			<label>
				<input type="checkbox" name="group-disable-bpsgmap" id="group-disable-bpsgmap" value="yes" <?php if ( bpsgm_is_map_disabled() ): ?> checked="checked"<?php endif; ?>/> <?php _e( 'Disable Map', 'bpsgmap' ) ?>
			</label>
		</div>
		<?php
	}

	public function save_group_prefs( $group_id ) {
		$disable = isset( $_POST['group-disable-bpsgmap'] ) ? 'yes' : 0;
		groups_update_groupmeta( $group_id, 'bpsgmap_is_disabled', $disable );//save preference
	}
}

BP_Simple_Google_Map_Group_Settings_Helper::get_instance();//instantiate
