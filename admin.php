<?php

class BPSimpleGoogleMapAdmin {
	function __construct() {
		$this->title      = __( 'BP Simple Google Map', 'bpsgmap' );
		$this->menu_title = __( 'BP Simple Google Map', 'bpsgmap' );
		$this->capability = "remove_users";
		$this->menu_slug  = "bpsgmap-admin";

		//add_action(is_multisite()?"network_admin_menu":"admin_menu",array(&$this,"add_menu"));
		if ( is_multisite() ) {
			add_action( "network_admin_menu", array( &$this, "add_menu" ) );
		} else {
			add_action( "admin_menu", array( &$this, "add_menu" ) );
		}

	}

	function BPSimpleGoogleMapAdmin() {
		$this->__construct();
	}

	function add_menu() {

		add_submenu_page( 'bp-general-settings', $this->title, $this->menu_title, $this->capability, $this->menu_slug, array(
			&$this,
			"form"
		) );


	}

	function update() {


		if ( ! empty( $_POST['bpsgmap_save'] ) ) {
			//validate nonce
			if ( ! wp_verify_nonce( $_POST['_wpnonce'], "bpsgmap" ) ) {
				die( __( 'Security check failed', 'bpsgmap' ) );
			}

			$settings = BPSimpleGoogleMap::get_admin_settings();//returns an ary of key val
			//let us process it
			$map_type = $_POST['map_type'];
			if ( $map_type && in_array( $map_type, array( 'roadmap', 'satellite', 'terrain', 'hybrid' ) ) ) {
				$settings['map_type'] = $_POST['map_type'];
			}

			$map_zoom_level = $_POST['map_zoom_level'];
			if ( $map_zoom_level && ( $map_zoom_level >= 0 && $map_zoom_level <= 21 ) ) {
				$settings['map_zoom_level'] = $map_zoom_level;
			}

			$map_height = $_POST['map_height'];
			if ( $map_height && ( $map_height >= 50 && $map_height <= 640 ) ) {
				$settings['map_height'] = $map_height;
			}


			$map_width = $_POST['map_width'];
			if ( $map_width && ( $map_width >= 50 && $map_width <= 640 ) ) {
				$settings['map_width'] = $map_width;
			}

			update_site_option( "bpgsmap_settings", $settings );
			$this->message = __( "Updated", 'bpsgmap' );
		}


	}


	function form() {
		$this->update();
		$settings = BPSimpleGoogleMap::get_admin_settings();
		extract( $settings );
		?>
		<div class='wrap'>
			<h2><?php echo $this->title; ?></h2>
			<?php if ( $this->errors || $this->message ): ?>
				<div class="updated fade" id="message"><p>
						<?php echo $this->errors;
						echo $this->message; ?>
					</p>
				</div>
			<?php endif; ?>
			<form action="" method='post' name='bpsgmap_settings_form'>

				<table class='form-table'>
					<tbody>
					<tr valign='top'>
						<th scope='row'><?php _e( 'Default Map Type', 'bpsgmap' ); ?></th>
						<td>
							<select name='map_type'>
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
						</td>
					</tr>
					<tr valign='top'>
						<th scope='row'><?php _e( 'Zoom Level', 'bpsgmap' ); ?></th>
						<td>
							<select name='map_zoom_level'>
								<?php for ( $i = 0; $i <= 21; $i ++ ): ?>
									<option value="<?php echo $i; ?>" <?php if ( $map_zoom_level == $i ) {
										echo 'selected="selected"';
									} ?>><?php echo $i; ?></option>
								<?php endfor; ?>


							</select>
						</td>
					</tr>

					<tr valign='top'>
						<th scope='row'><?php _e( 'Map Width', 'bpsgmap' ); ?></th>

						<td>

							<input type='text' size='3' name='map_width' value="<?php echo $map_width; ?>"/>px
							<p> <?php _e( 'For static map, the width should not be greater than 640px', 'bpsgmap' ); ?>
						</td>
					</tr>
					<tr valign='top'>
						<th scope="row"><?php _e( 'Map Height', 'bpsgmap' ); ?></th>

						<td>

							<input type='text' size='3' name='map_height' value="<?php echo $map_height; ?>"/>px
							<p> <?php _e( 'For static map, the height should not be greater than 640px', 'bpsgmap' ); ?>
						</td>
					</tr>

					</tr>
					<tr valign='top'>
						<td colspan='2'>
							<?php wp_nonce_field( "bpsgmap" ); ?>
							<input type="submit" name="bpsgmap_save" value="<?php _e( 'Save', 'bpsgmap' ); ?>"/>

						</td>
					</tr>
					</tbody>
				</table>
		</div>

	<?php }
}

//anonymous object
new BPSimpleGoogleMapAdmin();
?>