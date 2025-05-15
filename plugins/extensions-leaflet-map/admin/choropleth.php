<?php
/**
 * Admin functions for choropleth shortcode
 *
 * @package Extensions for Leaflet Map
 */

/*
 * Doku choropleth deutsch https://doc.arcgis.com/de/insights/latest/create/choropleth-maps.htm
 * https://gisgeography.com/choropleth-maps-data-classification/
 */

// mode: q for quantile, e for equidistant, k for k-means
// quantile maps try to arrange groups so they have the same quantity.
// equidistant: divide the classes into equal groups.
// k-means: each standard deviation becomes a class (?)

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

function leafext_choropleth_help() {
	if ( is_singular() || is_archive() ) {
		$codestyle = '';
		$text      = '';
	} else {
		leafext_enqueue_admin();
		$codestyle = ' class="language-coffeescript"';
		$text      = '<h2>Leaflet Choropleth</h2>';
	}

	$text = $text . '
  <h2>Shortcode</h2>';
	$text = $text . '<p><pre' . $codestyle . '><code' . $codestyle . '>&#091;leaflet-map fitbounds ....]' . "\n";
	$text = $text . '[leaflet-geojson src=https://domain.tld/path/to/file.geojson][/leaflet-geojson]
[choropleth valueProperty="property1" scale="white, red" steps=5 mode=e legend fillopacity=0.8]Property1 {property1}&lt;br>{property2} Property2[/choropleth]
[zoomhomemap]';
	$text = $text . '</code></pre></p>';

	$text = $text . '<h2>' . __( 'Popup Content', 'extensions-leaflet-map' ) . '</h2><p>';
	$text = $text . sprintf(
		/* translators: %s is an example code. */
		__( 'You can specify %s as you like.', 'extensions-leaflet-map' ),
		'<code>Property1 {property1}&lt;br>{property2} Property2</code>'
	);
	$text = $text . '</p><p>' .
	__(
		'Use it like the popup content for Geojsons in Leaflet Map: To add feature properties to the popups, use the inner content and curly brackets to substitute the values:',
		'extensions-leaflet-map'
	) .
	'<pre' . $codestyle . '><code' . $codestyle . '>&#91;choropleth ...]Field A = {field_a}[/choropleth]';
	$text = $text . '</code></pre>'
	. '</p>';

	$text    = $text . '<h2>' . __( 'Options', 'extensions-leaflet-map' ) . '</h2>';
	$options = leafext_choropleth_params();
	$new     = array();
	$new[]   = array(
		'param'   => '<strong>Option</strong>',
		'default' => '<strong>' . __( 'Default', 'extensions-leaflet-map' ) . '</strong>',
		'desc'    => '<strong>' . __( 'Description', 'extensions-leaflet-map' ) . '</strong>',
		'example' => '<strong>' . __( 'Example', 'extensions-leaflet-map' ) . '</strong>',
	);
	foreach ( $options as $option ) {
		$new[] = array(
			'param'   => $option[0],
			'default' => $option[2],
			'desc'    => $option[1],
			'example' => $option[3],
		);
	}
	$text = $text . leafext_html_table( $new );

	$text = $text . '<h3>mode</h3>
  <ul>
  <li> ' . __( 'quantile maps try to arrange groups so they have the same quantity.', 'extensions-leaflet-map' ) . '</li>
  <li> ' . __( 'equidistant: divide the classes into equal groups.', 'extensions-leaflet-map' ) . '</li>
  <li> ' . __( 'k-means: each standard deviation becomes a class.', 'extensions-leaflet-map' ) . '</li>
  </ul>';

	$text = $text . '<small>(' . __( "Help me, if I'm wrong.", 'extensions-leaflet-map' ) . ')</small>';
	return $text;
}
