<?php
/**
 * Admin page for hover functions
 *
 * @package Extensions for Leaflet Map
 */

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

function leafext_hover_init() {
	add_settings_section( 'hover_settings', '', '', 'leafext_settings_hover' );
	$fields = leafext_hover_params();
	foreach ( $fields as $field ) {
		if ( $field['changeable'] ) {
			add_settings_field(
				'leafext_hover[' . $field['param'] . ']',
				$field['desc'],
				'leafext_form_hover',
				'leafext_settings_hover',
				'hover_settings',
				$field['param']
			);
		}
	}
	register_setting( 'leafext_settings_hover', 'leafext_hover', 'leafext_validate_hover' );
}
add_action( 'admin_init', 'leafext_hover_init' );

function leafext_form_hover( $field ) {
	$params   = leafext_hover_params();
	$defaults = array();
	foreach ( $params as $param ) {
		if ( $param['changeable'] ) {
			$defaults[ $param['param'] ] = $param['default'];
		}
	}

	$options = leafext_hover_settings();
	// var_dump($options);
	if ( ! current_user_can( 'manage_options' ) ) {
		$disabled = ' disabled ';
	} else {
		$disabled = '';
	}

	foreach ( $options as $key => $value ) {
		if ( $key === $field ) {
			echo wp_kses_post( __( 'You can change it for each map with', 'extensions-leaflet-map' ) . ' <code>' . $key . '</code><br>' );
			if ( $value !== $defaults[ $key ] ) {
				echo wp_kses_post( __( 'Plugins Default', 'extensions-leaflet-map' ) . ': ' . $defaults[ $key ] . '<br>' );
			}
			if ( $key === 'class' ) {
				echo '<input ' . esc_attr( $disabled ) . ' type="text" size=15 name="' . esc_attr( 'leafext_hover[' . $key . ']' ) . '" value="' . esc_attr( $value ) . '" />';
			} else {
				echo '<input ' . esc_attr( $disabled ) . ' type="number" min="0" size=3 name="' . esc_attr( 'leafext_hover[' . $key . ']' ) . '" value="' . esc_attr( $value ) . '" />';
			}
		}
	}
}

// Sanitize and validate input. Accepts an array, return a sanitized array.
function leafext_validate_hover( $options ) {
	$post = map_deep( wp_unslash( $_POST ), 'sanitize_text_field' );
	if ( ! empty( $post ) && check_admin_referer( 'leafext_hover', 'leafext_hover_nonce' ) ) {
		if ( isset( $post['submit'] ) ) {
			$options['class']      = sanitize_text_field( $options['class'] );
			$options['tolerance']  = (int) $options['tolerance'];
			$options['popupclose'] = (int) $options['popupclose'];
			delete_option( 'leafext_canvas' ); // old option
			return $options;
		}
		if ( isset( $post['delete'] ) ) {
			delete_option( 'leafext_hover' );
			delete_option( 'leafext_canvas' );
		}
		return false;
	}
}

// Draw the menu page itself
function leafext_hover_admin_page() {
	if ( current_user_can( 'manage_options' ) ) {
		echo '<form method="post" action="options.php">';
	} else {
		echo '<form>';
	}
	settings_fields( 'leafext_settings_hover' );
	do_settings_sections( 'leafext_settings_hover' );
	if ( current_user_can( 'manage_options' ) ) {
		wp_nonce_field( 'leafext_hover', 'leafext_hover_nonce' );
		submit_button();
		submit_button( __( 'Reset', 'extensions-leaflet-map' ), 'delete', 'delete', false );
	}
	echo '</form>';
}
