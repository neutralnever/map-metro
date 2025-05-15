<?php
/**
 * Admin for layerswitch
 *
 * @package Extensions for Leaflet Map
 */

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

// init settings fuer tile switching
function leafext_maps_init() {
	add_settings_section( 'maps_settings', __( 'Extra Tile Server', 'extensions-leaflet-map' ), 'leafext_maps_help_text', 'leafext_settings_maps' );
	add_settings_field( 'leafext_form_maps_id', 'mapid:', 'leafext_form_maps', 'leafext_settings_maps', 'maps_settings', 'mapid' );
	register_setting( 'leafext_settings_maps', 'leafext_maps', 'leafext_validate_mapswitch' );
}
add_action( 'admin_init', 'leafext_maps_init' );

// Baue Abfrage der Tiles // Wie geht das besser?
function leafext_form_maps() {
	if ( ! current_user_can( 'manage_options' ) ) {
		$disabled = ' disabled ';
	} else {
		$disabled = '';
	}
	$options   = get_option( 'leafext_maps', array() );
	$map       = array(
		'mapid'   => '',
		'attr'    => '',
		'tile'    => '',
		'overlay' => '',
		'opacity' => '',
	);
	$options[] = $map;
	$i         = 0;
	$count     = count( $options );
	foreach ( $options as $option ) {
		if ( $i > 0 ) {
			echo '<tr><th colspan=2 style="border-top: 3px solid #646970"> </th></tr>';
			echo '<tr><th scope="row-title">mapid:</th>';
			echo '<td>';
			// } else {
			// echo '<td>';
		}
		echo '<input ' . esc_attr( $disabled ) . ' class="full-width" type="text" placeholder="name" name="' . esc_attr( 'leafext_maps[' . $i . '][mapid]' ) . '" value="' . esc_attr( $option['mapid'] ) . '" /></td>';
		echo '</tr>';

		echo '<tr><th scope="row-title">Attribution:</th>';
		echo '<td><input ' . esc_attr( $disabled ) . ' type="text" size="80" placeholder="Copyright" name="' . esc_attr( 'leafext_maps[' . $i . '][attr]' ) . '" value="' . esc_attr( $option['attr'] ) . '" /></td>';
		echo '</tr>';

		echo '<tr><th scope="row-title">Tile Server:</th>';
		echo '<td><input ' . esc_attr( $disabled ) . ' type="url" size="80" placeholder="https://{s}.tile.server.tld/{z}/{x}/{y}.png" name="' . esc_attr( 'leafext_maps[' . $i . '][tile]' ) . '" value="' . esc_attr( $option['tile'] ) . '" /></td>';
		echo '</tr>';

		echo '<tr><th scope="row-title">Extra Options: (optional)</th>';
		if ( ! isset( $option['options'] ) ) {
			$option['options'] = '';
		}
		echo '<td>';
		if ( $option['options'] === '' ) {
			echo esc_html__( 'The syntax is not checked!', 'extensions-leaflet-map' ) . '<br>';
		}
		echo '<input ' . esc_attr( $disabled ) . ' type="text" size="80"
			placeholder="' .
			esc_attr( 'minZoom: 1, maxZoom: 16, subdomains: "abcd", opacity: 0.5, bounds: [[22, -132], [51, -56]]' ) . '"
			name="leafext_maps[' . esc_html( $i ) . '][options]"
			pattern=' . "'" . '[a-zA-Z0-9_: ",\[\]\-.\{\}]*' . "'" . ' value="' . esc_attr( $option['options'] ) . '" /></td>';
		echo '</tr>';

		echo '<tr><th scope="row-title">Overlay Layer:</th>';
		if ( ! isset( $option['overlay'] ) ) {
			$option['overlay'] = '0';
		}
		$checked = $option['overlay'] === '1' ? 'checked' : '';
		echo '<td><input ' . esc_attr( $disabled ) . ' type="checkbox" name="' . esc_attr( 'leafext_maps[' . $i . '][overlay]' ) . '" value="1" ' . esc_attr( $checked ) . '/>';
		echo '</td></tr>';

		echo '<tr><th scope="row-title">Leaflet.Control.Opacity:</th>';
		if ( ! isset( $option['opacity'] ) ) {
			$option['opacity'] = '0';
		}
		$checked = $option['opacity'] === '1' ? 'checked' : '';
		echo '<td><input ' . esc_attr( $disabled ) . ' type="checkbox" name="' . esc_attr( 'leafext_maps[' . $i . '][opacity]' ) . '" value="1" ' . esc_attr( $checked ) . '/>';

		++$i;
		if ( $i < $count ) {
			echo '</td></tr>';
		}
	}
}

// Sanitize and validate input. Accepts an array, return a sanitized array.
function leafext_validate_mapswitch( $options ) {
		$maps = array();
	foreach ( $options as $option ) {
		if ( $option['mapid'] !== '' ) {
			$map            = array();
			$map['mapid']   = sanitize_text_field( $option['mapid'] );
			$map['attr']    = wp_kses_normalize_entities( $option['attr'] );
			$map['tile']    = sanitize_text_field( $option['tile'] );
			$map['options'] = wp_kses_normalize_entities( $option['options'] );
			$map['overlay'] = isset( $option['overlay'] ) ? $option['overlay'] : '';
			$map['opacity'] = isset( $option['opacity'] ) ? $option['opacity'] : '';
			$maps[]         = $map;
		}
	}
	return $maps;
}

// Erklaerung / Hilfe
function leafext_maps_help_text() {
	if ( is_singular() || is_archive() ) {
		$codestyle = '';
	} else {
		leafext_enqueue_admin();
		$codestyle = ' class="language-coffeescript"';
	}
	if ( ! ( is_singular() || is_archive() ) ) { // backend
		$tilesproviders = '?page=' . LEAFEXT_PLUGIN_SETTINGS . '&tab=tilesproviders';
		$tileswitch     = '?page=' . LEAFEXT_PLUGIN_SETTINGS . '&tab=tileswitch';
	} else { // for my frontend leafext.de
		$server = map_deep( wp_unslash( $_SERVER ), 'sanitize_text_field' );
		if ( strpos( $server['REQUEST_URI'], '/en/' ) !== false ) {
			$lang = '/en';
		} else {
			$lang = '';
		}
		$tilesproviders = $lang . '/doku/tilesproviders/';
		$tileswitch     = $lang . '/doku/tileswitch/';
	}
	$text = '';
	if ( ! ( is_singular() || is_archive() ) ) {
		$text = $text . '<img src="' . LEAFEXT_PLUGIN_PICTS . 'layerswitch.png"><p>';
		$text = $text . __( 'Here you can define your Tile servers. Additionally you can specify if opacity should be regulated.', 'extensions-leaflet-map' );
	}
	$text = $text . '<h2>Shortcode</h2><p>';
	$text = $text . __(
		'Per default all defined tile servers appear.',
		'extensions-leaflet-map'
	);
	$text = $text . '</p>
	<pre' . $codestyle . '><code' . $codestyle . '>&#091;leaflet-map mapid="..." ...]
&#091;layerswitch]
</code></pre>';
	$text = $text . '<p>' . sprintf(
		/* translators: %s is an option. */
		__(
			'You can select your defined Tile Server with parameter %s as comma separated list in the shortcode:',
			'extensions-leaflet-map'
		),
		'<code>tiles</code>'
	) . '</p>';
	$text = $text . '<pre' . $codestyle . '><code' . $codestyle . '>&#091;leaflet-map mapid="..." ...]
&#091;layerswitch tiles="mapid1,mapid2,..."]
</code></pre>';
	$text = $text . '<p>' . sprintf(
		/* translators: %s is an option. */
		__( 'You can use the parameter %s also.', 'extensions-leaflet-map' ),
		'<a href="' . $tilesproviders . '"><code>providers</code></a>'
	) . '</p>';
	if ( ! ( is_singular() || is_archive() ) ) {
		$text     = $text . '<h2>' . __( 'Settings', 'extensions-leaflet-map' ) . '</h2>';
		$text     = $text . '<p>' .
			__( 'Configure a mapid, attribution and a tile url for each tile server.', 'extensions-leaflet-map' ) .
			' mapid ' . __( 'appears in the switching control. To delete a server simply clear the field mapid.', 'extensions-leaflet-map' )
			. '</p>';
			$text = $text . '<div colspan=2 style="border-top: 3px solid #646970"> </div>';
	}
	if ( is_singular() || is_archive() ) {
		return $text;
	} else {
		echo wp_kses_post( $text );
	}
}
