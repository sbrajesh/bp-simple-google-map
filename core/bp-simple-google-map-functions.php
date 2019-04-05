<?php

function bpsgm_is_map_disabled() {
	$current_group_id = bpsgm_get_current_group_id();//get current group id
	//check preference
	$is_disabled = groups_get_groupmeta( $current_group_id, 'bpsgmap_is_disabled' );
	if ( ! empty( $is_disabled ) && $is_disabled == 'yes' ) {
		return true;
	}

	return false;
}

function bpsgm_get_group_location () {
	$group_id = bp_get_group_id( groups_get_current_group() );
	$loc      = groups_get_groupmeta( $group_id, 'group_location' );

	return $loc;
}

function bpsgm_build_map_url( $settings, $loc = null  ) {
	if ( ! $loc ) {
		$loc = bpsgm_get_group_location();
	}
	$size   = $settings['map_width'] . "x" . $settings['map_height'];
	$url    = 'https://maps.googleapis.com/maps/api/staticmap?';
	$params = array(
		"center"  => $loc,
		"sensor"  => 'false',
		'zoom'    => $settings['map_zoom_level'],
		'maptype' => $settings['map_type'],
		'size'    => $size,
		'markers' => 'color:red|label:S|' . $loc,//size:mid|
		'key'     => isset( $settings['map_key'] ) ? trim( $settings['map_key'] ) : ''
	);

	$q      = http_build_query( $params );
	$url    = $url . $q;

	return $url;
}
//get the image for group
function bpsgm_get_group_map_image() {
	$settings = bpsgm_get_admin_settings();

	$loc = bpsgm_get_group_location();
	if ( empty( $loc ) ) {
		return false;
	}

	$url = bpsgm_build_map_url( $settings, $loc );

	return "<img src='" . $url . "'alt='" . esc_attr( $loc ) . "' />";
}

/**
 * Get simple google map settings
 *
 * @return mixed
 */
function bpsgm_get_admin_settings() {
	$default  = array( 'map_key' => '', 'map_type' => 'roadmap', 'map_zoom_level' => 6, 'map_height' => 640, 'map_width' => 640 );
	$settings = get_option( 'bpgsmap_settings', $default );

	return maybe_unserialize( $settings );
}

/**
 * get current group id
 *
 * @return int|null
 */
function bpsgm_get_current_group_id() {
	$group_id = null;
	if ( bp_is_group_create() ) {
		$group_id = isset( $_COOKIE['bp_new_group_id'] ) ? $_COOKIE['bp_new_group_id']: 0 ;
	} else if ( bp_is_group() ) {
		$group_id = groups_get_current_group()->id;
	}

	return $group_id;
}