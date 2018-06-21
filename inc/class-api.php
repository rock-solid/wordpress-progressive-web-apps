<?php
namespace PWAPP\Inc;

use PWAPP\Inc\Options;
use PWAPP\Inc\Themes_Config;
use PWAPP\Inc\Uploads;
use PWAPP\Inc\PWAPP_Export;

/**
 *  Class that sets up the rest routes for the app
 */
class Api {


	/**
	 * Registers all the plugins rest routes
	 */
	public function register_pwapp_routes() {

		register_rest_route(
			'pwapp', '/manifest', [
				'methods'  => 'GET',
				'callback' => [ $this, 'export_manifest' ],
			]
		);

	}

	/**
	 * Export the app manifest
	 */
	public function export_manifest() {

		// set blog name
		$blog_name = get_bloginfo( 'name' );

		$arr_manifest = array(
			'name'        => $blog_name,
			'short_name'  => $blog_name,
			'start_url'   => home_url(),
			'display'     => 'standalone',
			'orientation' => 'any',
		);

		$background_color = Themes_Config::get_manifest_background();

		if ( false !== $background_color ) {
			$arr_manifest['theme_color']      = $background_color;
			$arr_manifest['background_color'] = $background_color;
		}

		// load icon from the local settings and folder
		$icon_path = Options::get_setting( 'icon' );

		if ( '' != $icon_path ) {

			$base_path             = $icon_path;
			$arr_manifest['icons'] = array();
			$uploads               = new Uploads();

			foreach ( Uploads::$manifest_sizes as $manifest_size ) {

				$icon_path = $uploads->get_file_url( $manifest_size . $base_path );

				if ( '' !== $icon_path ) {

					$arr_manifest['icons'][] = array(
						'src'   => $icon_path,
						'sizes' => $manifest_size . 'x' . $manifest_size,
						'type'  => 'image/png',
					);
				}
			}
		}

		echo wp_json_encode( $arr_manifest );
		exit();

	}





}

