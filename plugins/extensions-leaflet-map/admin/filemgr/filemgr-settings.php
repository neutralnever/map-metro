<?php
/**
 * Admin page for filemgr settings functions
 *
 * @package Extensions for Leaflet Map
 */

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

// Parameter and Values
function leafext_filemgr_params() {
	$params = array(
		array(
			'param'     => 'types',
			'shortdesc' => __( 'Types', 'extensions-leaflet-map' ),
			'desc'      => esc_html__( 'Allow upload to media library', 'extensions-leaflet-map' ),
			'default'   => array(),
			'values'    => array( 'gpx', 'kml', 'geojson', 'json', 'tcx' ),
		),
		array(
			'param'     => 'gpxupload',
			'shortdesc' => __( 'Upload gpx files into the directory', 'extensions-leaflet-map' ) . ' /upload_dir()/gpx/',
			'desc'      => esc_html__( 'This may be of interest if you have used wp-gpx-maps.', 'extensions-leaflet-map' ),
			'default'   => '0',
			'values'    => 1,
		),
		array(
			'param'     => 'nonadmin',
			'shortdesc' => __( 'Allow non admin', 'extensions-leaflet-map' ),
			'desc'      => sprintf(
				/* translators: %s is code */
				esc_html__(
					'Allow all users who have access to the backend to see the files. A permission check %s only done if the files are registered in the media library.',
					'extensions-leaflet-map'
				),
				'(<code>current_user_can("edit_post / read", this_post)</code>)'
			),
			'default'   => '0',
			'values'    => 1,
		),
	);
	return $params;
}

// init settings
function leafext_filemgr_init() {
	register_setting( 'leafext_settings_filemgr', 'leafext_filemgr', 'leafext_validate_filemgr_options' );
	// register_setting( 'leafext_settings_filemgr', 'leafext_filemgr' );
	add_settings_section( 'filemgr_settings', __( 'Settings', 'extensions-leaflet-map' ), 'leafext_managefiles_help', 'leafext_settings_filemgr' );
	$fields = leafext_filemgr_params();
	foreach ( $fields as $field ) {
		add_settings_field( 'leafext_filemgr[' . $field['param'] . ']', $field['shortdesc'], 'leafext_form_filemgr', 'leafext_settings_filemgr', 'filemgr_settings', $field['param'] );
	}
}
add_action( 'admin_init', 'leafext_filemgr_init' );

function leafext_validate_filemgr_options( $options ) {
	if ( ! empty( $_POST ) && check_admin_referer( 'leafext_file', 'leafext_file_nonce' ) ) {
		if ( isset( $_POST['submit'] ) ) {
			$defaults = array();
			$params   = leafext_filemgr_params();
			foreach ( $params as $param ) {
				$defaults[ $param['param'] ] = $param['default'];
			}
			$params = get_option( 'leafext_filemgr', $defaults );
			foreach ( $options as $key => $value ) {
				$params[ $key ] = $value;
			}
			return $params;
		}
		if ( isset( $_POST['delete'] ) ) {
			delete_option( 'leafext_filemgr' );
		}
		return false;
	}
}

function leafext_form_filemgr( $field ) {
	$options = leafext_filemgr_params();
	// var_dump($options); wp_die();
	$option   = leafext_array_find2( $field, $options );
	$settings = leafext_filemgr_settings();
	$setting  = $settings[ $field ];
	if ( $option['desc'] !== '' ) {
		//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- esc_html__ used
		echo '<p>' . $option['desc'] . '</p>';
	}

	if ( $field === 'types' ) {
		foreach ( $option['values'] as $typ ) {
			$checked = in_array( $typ, $setting, true ) ? ' checked ' : '';
			echo ' <input type="checkbox" name="' . esc_attr( 'leafext_filemgr[' . $option['param'] . '][]' ) . '" value="' . esc_attr( $typ ) . '" id="' . esc_attr( $typ ) . '" ' . esc_attr( $checked ) . '>' . "\n";
			echo ' <label for="' . esc_attr( $typ ) . '" >' . esc_attr( $typ ) . '</label> ' . "\n";
		}
	} else {
		if ( $setting !== $option['default'] ) {
			// var_dump($setting,$option['default']);
			echo esc_html__( 'Plugins Default', 'extensions-leaflet-map' ) . ': ';
			echo $option['default'] ? 'true' : 'false';
			echo '<br>' . "\n";
		}
		echo '<input type="radio" name="' . esc_attr( 'leafext_filemgr[' . $option['param'] . ']' ) . '" value="1" ';
		echo $setting ? 'checked' : '';
		echo '> true &nbsp;&nbsp; ' . "\n";
		echo '<input type="radio" name="' . esc_attr( 'leafext_filemgr[' . $option['param'] . ']' ) . '" value="0" ';
		echo ( ! $setting ) ? 'checked' : '';
		echo '> false ' . "\n";
	}
}

function leafext_filemgr_settings() {
	$defaults = array();
	$params   = leafext_filemgr_params();
	foreach ( $params as $param ) {
		$defaults[ $param['param'] ] = $param['default'];
	}
	$options = shortcode_atts( $defaults, get_option( 'leafext_filemgr' ) );
	// var_dump($options); wp_die();
	return $options;
}

function leafext_managefiles_help() {
	if ( ! current_user_can( 'manage_options' ) ) {
		$text = __( 'You can display all gpx, kml, geojson, json and tcx files in subdirectories of uploads directory.', 'extensions-leaflet-map' ) . ' ';
	} else {
		$text = __( 'Here you can display all gpx, kml, geojson, json and tcx files in subdirectories of uploads directory.', 'extensions-leaflet-map' ) . ' ';
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		$text = $text . ' ' . __( 'You can manage these according to your permissions', 'extensions-leaflet-map' );
	} else {
		$text = $text . __( 'You can manage these', 'extensions-leaflet-map' );
	}
	$text = $text . '<ul>' . "\n";
	$text = $text . '<li>';
	$text = $text . __( 'direct in the Media Library.', 'extensions-leaflet-map' );
	$text = $text . '</li>' . "\n";
	$text = $text . '<li>';
	$text = $text . __( 'with any (S)FTP-Client,', 'extensions-leaflet-map' );
	$text = $text . '</li>' . "\n";
	$text = $text . '<li>';
	$text = $text . __( 'with any File Manager plugin,', 'extensions-leaflet-map' );
	$text = $text . '</li>' . "\n";
	$text = $text . '<li>';
	$text = $text . __( 'with any plugin for importing uploaded files to the Media Library.', 'extensions-leaflet-map' );
	$text = $text . '</li>' . "\n";
	$text = $text . '<li>';
	$text = $text . __( 'or in your own way.', 'extensions-leaflet-map' );
	$text = $text . '</li>' . "\n";
	$text = $text . '</ul>';
	if ( is_singular() || is_archive() ) {
		return $text;
	} else {
		echo wp_kses_post( $text );
	}
}
