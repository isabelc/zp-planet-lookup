<?php
/**
Plugin Name: ZodiacPress Planet Lookup
Plugin URI:	https://cosmicplugins.com/downloads/zodiacpress-planet-lookup/
Description: Extension for ZodiacPress to lookup the sign of one planet or point.
Version: 1.0
Author:	Isabel Castillo
Author URI:	http://isabelcastillo.com
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: zp-planet-lookup
Domain Path: /languages

Copyright 2106 Isabel Castillo

ZodiacPress Planet Lookup is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

ZodiacPress Planet Lookup is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with ZodiacPress Planet Lookup. If not, see <http://www.gnu.org/licenses/>.
*/

// License for maintenance and security updates
if ( class_exists( 'ZP_License' ) && is_admin() ) {
	$zppl_license = new ZP_License( __FILE__, 'ZodiacPress Planet Lookup', '1.0', 'Isabel Castillo' );// @todo update v
}

// do nothing if ZP is not activated
if ( ! class_exists( 'ZodiacPress', false ) ) {
		return;
}

/**
 * Add settings to the planet_lookup tab to customize the output.
 */
function zppl_add_settings( $settings ) {
		
	$settings['planet_lookup']['main'] = array(
		'planet_lookup_output' => array(
					'id'	=> 'planet_lookup_output',
					'name'	=> __( 'Output', 'zp-planet-lookup' ),
					'type'	=> 'textarea',
					'desc'	=> __( 'What should be displayed as the output when someone looks up a planet? Available template tags are <br />{planet} - The planet/point that is looked up<br />{sign} - The zodiac sign of this planet/point<br />{glyph} - An icon for the zodiac sign', 'zp-planet-lookup' )

		),
		'planet_lookup_allow_unknown_bt' => array(
					'id'	=> 'planet_lookup_allow_unknown_bt',
					'name'	=> __( 'Allow Unknown Birth Time', 'zp-planet-lookup' ),
					'type'	=> 'checkbox',
					'desc'	=> __( 'Allow people with unknown birth times to look up planets. This does not apply to Moon, Part of Fortune, Ascendant, Midheaven, or Vertex since these will always require a birth time.', 'zp-planet-lookup' )
		)
	);

	return $settings;
}

/**
 * Add a Planet Lookup tab to ZP settings
 */
function zppl_add_settings_tab( $tabs ) {

	$new_tab = array( 'planet_lookup' => __( 'Planet Lookup', 'zp-planet-lookup' ) );

	// Insert the tab at the appropriate place.
	$offset = 1;
	$tabs = array_slice( $tabs, 0, $offset, true ) +
			$new_tab +
			array_slice( $tabs, $offset, NULL, true );

	return $tabs;
}

/**
 * Show no form title for Planet Lookup
 */
function zppl_remove_form_title( $title, $atts ) {
	if ( isset( $atts['report'] ) && false !== strpos( $atts['report'], 'planet_lookup' ) ) {
		$title = '';
	}
	return $title;
}

/**
 * Remove name field from Planet Lookup forms
 */
function zppl_remove_name_field( $show, $report_var ) {
	if ( false !== strpos( $report_var, 'planet_lookup' ) ) {
		return false;
	} else {
		return $show;
	}
}

/**
 * Require birth time for some planet lookups.
 */
function zppl_require_birth_time( $array ) {

	$array[] = 'planet_lookup_moon';
	$array[] = 'planet_lookup_pof';
	$array[] = 'planet_lookup_vertex';
	$array[] = 'planet_lookup_asc';
	$array[] = 'planet_lookup_mc';

	return $array;
}

/**
 * Remove the report header from the planet lookups.
 */
function zppl_remove_report_header( $header, $var ) {

	if ( false !== strpos( $var, 'planet_lookup' ) ) {
		return false;
	} else {
		return $header;
	}
}

/**
 * Remove the Start Over link from the planet lookups.
 */
function zppl_remove_start_over_link( $link, $report_var ) {

	if ( false !== strpos( $report_var, 'planet_lookup' ) ) {
		return false;
	} else {
		return $link;
	}
}

/**
 * Remove the unknown birth time NOTE for planet lookups.
 */
function zppl_remove_unknown_time_note( $note, $report_args ) {

	if ( false !== strpos( $report_args['report'], 'planet_lookup' ) ) {
		return false;
	} else {
		return $note;
	}
}

/**
 * Remove the 'Birth time is required' text for planet lookups.
 */
function zppl_remove_birth_time_required( $note, $report_args ) {

	if ( false !== strpos( $report_args['report'], 'planet_lookup' ) ) {
		return false;
	} else {
		return $note;
	}
}

/**
 * Get the output for the Planet Lookup and process the template tags.
 * @param int $sign_id The id (slug) of the zodiac sign
 * @param string $sign_label The zodiac sign label
 * @param string $planet_label The label of the planet in question
 */
function zppl_get_output( $sign_id, $sign_label, $planet_label ) {

	global $zodiacpress_options;

	$out = '';

	if ( empty( $zodiacpress_options['planet_lookup_output'] ) ) {

		$out .= $sign_label;

	} else {

		$glyph = '<span class="zp-icon-' . $sign_id . '"> </span>';

		$data = $zodiacpress_options['planet_lookup_output'];

		// replace the template tags: {sign}, {planet}, {glyph}
		$data = str_replace( '{sign}', $sign_label, $data );
		$data = str_replace( '{planet}', $planet_label, $data );
		$data = str_replace( '{glyph}', $glyph, $data );

		$out .= $data;

	}

	return $out;
		
}

/**
 * Return each planet upon lookup.
 */
function zppl_lookup_planet( $report, $form, $chart ) {

	// extract the planet name
	$report_var = $form['zp-report-variation'];
	$this_planet = substr( $report_var, strrpos( $report_var, '_' ) + 1 );

	$planets		= zp_get_planets();
	$planet_key		= '';
	$planet_label	= '';
		
	foreach ( $planets as $p_key => $p_value ) {
		if ( $this_planet == $p_value['id'] ) {
			$planet_key		= $p_key;
			$planet_label	= $p_value['label'];
			continue;
		}
	}

	$sign_pos	= floor( $chart->planets_longitude[ $planet_key ] / 30 );
	$signs		= zp_get_zodiac_signs();

	// If birthtime is unknown, check if planet ingress occurs this day
	if ( $form['unknown_time'] ) {

		$ingress = zp_is_planet_ingress_today( $planet_key, $chart->planets_longitude[ $planet_key ], $form );

		if ( ( isset( $ingress[0] ) && '' !== $ingress[0] ) &&
			( isset( $ingress[1] ) && '' !== $ingress[1] ) ) {

			$report = '<p>' .
				sprintf( __( 'NOTE: %1$s changed signs the day you were born. It moved from %2$s to %3$s. Therefore, you will need your exact time of birth to know which of these two signs your %1$s is in.', 'zp-planet-lookup' ),
				$planet_label,
				$signs[ $ingress[0] ]['label'],
				$signs[ $ingress[1] ]['label'] ) . 
				'</p>';
		} else {

			// no ingress so do regular output
			$report = zppl_get_output( $signs[ $sign_pos ]['id'], $signs[ $sign_pos ]['label'], $planet_label );

		}


	} else {
		$report = zppl_get_output( $signs[ $sign_pos ]['id'], $signs[ $sign_pos ]['label'], $planet_label );
	}

	// the HTML

	$open		= '<div class="zp-planet-lookup-output">';
	$report		= wp_kses_post( $report );
	$back_link	= '<a href="' .
				 esc_url( get_permalink() ) . '">' .
				 apply_filters( 'zppl_lookup_another_text', __('Look Up Another', 'zp-planet-lookup') ) .
				 '</a>';
	$close		= '</div>';

	return $open . $report . ' &nbsp; ' . $back_link . $close;

}

/**
 * Add all of the plugin's filters.
 */
function zppl_init() {

	add_filter( 'zp_registered_settings', 'zppl_add_settings' );
	add_filter( 'zp_settings_tabs', 'zppl_add_settings_tab' );
	add_filter( 'zp_shortcode_default_form_title', 'zppl_remove_form_title', 10, 2 );
	add_filter( 'zp_form_show_name_field', 'zppl_remove_name_field', 10, 2 );
	add_filter( 'zp_reports_require_birthtime', 'zppl_require_birth_time' );
	add_filter( 'zp_report_header', 'zppl_remove_report_header', 10, 2 );	
	add_filter( 'zp_show_start_over_link', 'zppl_remove_start_over_link', 10, 2 );
	add_filter( 'zp_allow_unknown_time_note', 'zppl_remove_unknown_time_note', 10, 2 );
	add_filter( 'zp_birth_time_required', 'zppl_remove_birth_time_required', 10, 2 );

	add_filter( 'zp_planet_lookup_sun_report', 'zppl_lookup_planet', 20, 3 );
	add_filter( 'zp_planet_lookup_moon_report', 'zppl_lookup_planet', 20, 3 );
	add_filter( 'zp_planet_lookup_mercury_report', 'zppl_lookup_planet', 20, 3 );
	add_filter( 'zp_planet_lookup_venus_report', 'zppl_lookup_planet', 20, 3 );
	add_filter( 'zp_planet_lookup_mars_report', 'zppl_lookup_planet', 20, 3 );
	add_filter( 'zp_planet_lookup_jupiter_report', 'zppl_lookup_planet', 20, 3 );
	add_filter( 'zp_planet_lookup_saturn_report', 'zppl_lookup_planet', 20, 3 );
	add_filter( 'zp_planet_lookup_uranus_report', 'zppl_lookup_planet', 20, 3 );
	add_filter( 'zp_planet_lookup_neptune_report', 'zppl_lookup_planet', 20, 3 );
	add_filter( 'zp_planet_lookup_pluto_report', 'zppl_lookup_planet', 20, 3 );
	add_filter( 'zp_planet_lookup_chiron_report', 'zppl_lookup_planet', 20, 3 );
	add_filter( 'zp_planet_lookup_lilith_report', 'zppl_lookup_planet', 20, 3 );
	add_filter( 'zp_planet_lookup_nn_report', 'zppl_lookup_planet', 20, 3 );
	add_filter( 'zp_planet_lookup_pof_report', 'zppl_lookup_planet', 20, 3 );
	add_filter( 'zp_planet_lookup_vertex_report', 'zppl_lookup_planet', 20, 3 );
	add_filter( 'zp_planet_lookup_asc_report', 'zppl_lookup_planet', 20, 3 );
	add_filter( 'zp_planet_lookup_mc_report', 'zppl_lookup_planet', 20, 3 );

}
add_action( 'plugins_loaded', 'zppl_init' );

/**
 * Upon activation, save a default setting
 */
function zppl_install() {

	global $zodiacpress_options;

	if ( empty( $zodiacpress_options['planet_lookup_output'] ) ) {

		$zodiacpress_options['planet_lookup_output'] = __( 'You have {planet} in {sign}.', 'zp-planet-lookup' );
		$update = update_option( 'zodiacpress_settings', $zodiacpress_options );
	}
}
register_activation_hook( __FILE__, 'zppl_install' );
