<?php

class BP_Simple_Google_Map_Admin {

	private $errors = null;
	private $message  = null;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
	}

	public function add_menu() {

		if ( is_multisite() && ! bp_is_root_blog() ) {
			return ;
		}

		add_options_page( __( 'BP Simple Google Map', 'bp-simple-google-map' ), __( 'BP Simple Google Map', 'bp-simple-google-map' ), 'remove_users', 'bpsgmap-admin', array(
			$this,
			'form'
		) );
	}

	/**
	 * Save
	 */
	public function update() {

		if ( ! empty( $_POST['bpsgmap_save'] ) ) {
			//validate nonce
			if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'bp-simple-google-map' ) ) {
				die( __( 'Security check failed', 'bp-simple-google-map' ) );
			}

			$settings = bpsgm_get_admin_settings();//returns an ary of key val
			//let us process it
			$map_type = $_POST['map_type'];

			if ( $map_type && in_array( $map_type, array( 'roadmap', 'satellite', 'terrain', 'hybrid' ) ) ) {
				$settings['map_type'] = $_POST['map_type'];
			}

			$settings['map_key'] = isset( $_POST['map_key'] ) ? trim( $_POST['map_key'] ) : '';

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

			update_option( 'bpgsmap_settings', $settings );
			$this->message = __( 'Updated', 'bp-simple-google-map' );
		}
	}

	public function form() {

		$this->update();
		$settings = bpsgm_get_admin_settings();

		extract( $settings );

		$map_key = isset( $settings['map_key'] ) ? $settings['map_key'] : '';//backward compat to avoid notices
		?>
		<div class='wrap'>
			<h1><?php _e( 'BP Simple Google Map', 'bp-simple-google-map' ); ?></h1>
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
								<option value='roadmap' <?php selected( $map_type, 'roadmap' ); ?>><?php _e( 'Roadmap', 'bp-simple-google-map' ); ?></option>
								<option value='satellite' <?php selected( $map_type, 'satellite' ); ?>><?php _e( 'Satellite', 'bp-simple-google-map' ); ?></option>
								<option value='terrain' <?php selected( $map_type, 'terrain' ); ?>><?php _e( 'Terrain', 'bp-simple-google-map' ); ?></option>
								<option value='hybrid' <?php selected ( $map_type, 'hybrid' ); ?>><?php _e( 'Hybrid', 'bp-simple-google-map' ); ?></option>

							</select>
						</td>
					</tr>
					<tr valign='top'>
						<th scope='row'><?php _e( 'Map Key', 'bpsgmap' ); ?></th>
						<td>
							<input type="text" value="<?php echo esc_attr( $map_key ); ?>" name="map_key" />
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
							<?php wp_nonce_field( "bp-simple-google-map" ); ?>
							<input type="submit" name="bpsgmap_save" value="<?php _e( 'Save', 'bpsgmap' ); ?>" class="button button-primary"/>

						</td>
					</tr>
					</tbody>
				</table>
		</div>

	<?php }
}

//anonymous object
new BP_Simple_Google_Map_Admin();