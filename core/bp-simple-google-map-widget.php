<?php
//widget will show in group sidebar the map of the group
class BP_Simple_Google_Map_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct( false, __( 'BP Simple Google Map', 'bpsgmap' ) );
	}

		//currently we only support group, so hide if it is not group
	public function widget( $args, $instance ) {

		if ( ! ( bp_is_group() && ! bpsgm_is_map_disabled() ) ) {
			return;
		}//currently we are only supporting the group maps
		//display map
		$location = bpsgm_get_group_location();

		if ( ! $location ) {
			return;
		}

		extract( $args );
		$url = bpsgm_build_map_url( $instance, $location );
		echo $before_widget;
		echo $before_title;
		echo $instance['title'];
		echo $after_title;
		echo "<img src='" . $url . "' alt='" . esc_attr( $location ) . "' />";
		echo $after_widget;
	}

	public function update( $new_instance, $old_instance ) {
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

	public function form( $instance ) {
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
			<label for="bp-simple-google-map-title"><strong><?php _e( 'Title:', 'bp-simple-google-map' ); ?> </strong>
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
				       name="<?php echo $this->get_field_name( 'title' ); ?>" type="text"
				       value="<?php echo esc_attr( $title ); ?>" style="width: 100%"/>
			</label>
		</p>
		<p>
			<label for="bp-simple-google-map-type"><?php _e( 'Map Type', 'bpsgmap' ); ?>
				<select class="widefat" id="<?php echo $this->get_field_id( 'map_type' ); ?>" name="<?php echo $this->get_field_name( 'map_type' ); ?>" style="width: 30%">
					<option value='roadmap' <?php selected( $map_type, 'roadmap' ); ?>><?php _e( 'Roadmap', 'bp-simple-google-map' ); ?></option>
					<option value='satellite' <?php selected( $map_type, 'satellite' ); ?>><?php _e( 'Satellite', 'bp-simple-google-map' ); ?></option>
					<option value='terrain' <?php selected( $map_type, 'terrain' ); ?>><?php _e( 'Terrain', 'bp-simple-google-map' ); ?></option>
					<option value='hybrid' <?php selected ( $map_type, 'hybrid' ); ?>><?php _e( 'Hybrid', 'bp-simple-google-map' ); ?></option>
				</select>
			</label>
		</p>
		<p>
			<label for="bp-simple-google-map-zoom-level"><?php _e( 'Zoom Level', 'bp-simple-google-map' ); ?>
				<select class="widefat" id="<?php echo $this->get_field_id( 'map_zoom_level' ); ?>"
				        name="<?php echo $this->get_field_name( 'map_zoom_level' ); ?>" style="width: 30%">
					<?php for ( $i = 0; $i <= 21; $i ++ ): ?>
						<option value="<?php echo $i; ?>" <?php selected( $map_zoom_level, $i ); ?>><?php echo $i; ?></option>
					<?php endfor; ?>
				</select>
			</label>
		</p>
		<p>
			<label for="bp-simple-google-map-width"><?php _e( 'Map Width', 'bp-simple-google-map' ); ?>
				<input class="widefat" id="<?php echo $this->get_field_id( 'map_width' ); ?>"
				       name="<?php echo $this->get_field_name( 'map_width' ); ?>" type="text" size="3"
				       value="<?php echo $map_width; ?>"/> px
			</label>
		</p>
		<p>
			<label for="bp-simple-google-map-height"><?php _e( 'Map Height(max 640)', 'bp-simple-google-map' ); ?>
				<input class="widefat" id="<?php echo $this->get_field_id( 'map_height' ); ?>"
				       name="<?php echo $this->get_field_name( 'map_height' ); ?>" type="input" size="3"
				       value="<?php echo $map_height; ?>"/>px
			</label>
		</p>
		<?php
	}

}//end of widget

//register widget
function bpsgm_register_widgets() {
	register_widget( 'BP_Simple_Google_Map_Widget' );
}
//register the widget
add_action( 'bp_widgets_init', 'bpsgm_register_widgets' );