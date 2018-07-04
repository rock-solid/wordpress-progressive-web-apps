<?php

namespace PWAPP\Inc;

use PWAPP\Inc\Options;

/**
 * Overall Themes Config class
 *
 */
class Themes_Config {


	/* ----------------------------------*/
	/* Properties						 */
	/* ----------------------------------*/

	public static $allowed_fonts = array(
		'Roboto Condensed Light',
		'Roboto Condensed Bold',
		'Roboto Condensed Regular',
		'OpenSans Condensed Light',
		'Crimson Roman',
		'Roboto Slab Light',
		'Helvetica Neue Light Condensed',
		'Helvetica Neue Bold Condensed',
		'Gotham Book',
	);

	/**
	 * Allowed font sizes are float numbers. Their unit measure is 'rem'.
	 * @var array
	 */
	public static $allowed_fonts_sizes = array(
		array(
			'label' => 'Small',
			'size'  => 0.875,
		),
		array(
			'label' => 'Normal',
			'size'  => 1,
		),
		array(
			'label' => 'Large',
			'size'  => 1.125,
		),
	);

	/**
	* Theme config json. Use this only for admin purposes.
	* If the theme param is missing, the method will return the settings of the current selected theme.
	*
	* @param int $theme
	*
	* @return array or false
	*/
	public static function get_theme_config( $theme = 2 ) {

		$theme_config_path = PWAPP_PLUGIN_PATH . 'frontend/themes/app2/presets.json';

		if ( file_exists( $theme_config_path ) ) {

			$theme_config      = file_get_contents( $theme_config_path );
			$theme_config_json = json_decode( $theme_config, true );

			if ( $theme_config_json && ! empty( $theme_config_json ) &&
				array_key_exists( 'vars', $theme_config_json ) && is_array( $theme_config_json['vars'] ) &&
				array_key_exists( 'labels', $theme_config_json ) && is_array( $theme_config_json['labels'] ) &&
				array_key_exists( 'presets', $theme_config_json ) && is_array( $theme_config_json['presets'] ) &&
				array_key_exists( 'fonts', $theme_config_json ) && is_array( $theme_config_json['fonts'] ) &&
				array_key_exists( 'posts_per_page', $theme_config_json ) && is_numeric( $theme_config_json['posts_per_page'] ) ) {

				return $theme_config_json;
			}
		}

		return false;
	}

	/**
	* Get the application's background color for the app manifest.
	*
	* @param int or null $color_scheme
	* @return string or false
	*/
	public static function get_manifest_background( $color_scheme = null ) {
		if ( null == $color_scheme ) {
			$color_scheme = Options::get_setting( 'color_scheme' );
		}

		$theme_presets = self::get_theme_config();

		switch ( $color_scheme ) {
			case 0:
				$custom_colors = Options::get_setting( 'custom_colors' );
				if ( is_array( $custom_colors ) ) {
					return end( $custom_colors );
				}
				break;
			case 1:
			case 2:
			case 3:
				return end( $theme_presets['presets'][ $color_scheme ] );
		}
		return false;
	}
}
