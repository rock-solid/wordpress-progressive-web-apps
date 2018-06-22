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
			'pwapp', '/manifest', array(
				'methods'  => 'GET',
				'callback' => array( $this, 'export_manifest' ),
			)
		);

		register_rest_route(
			'pwapp', '/categories', array(
				'methods'  => 'GET',
				'callback' => array( $this, 'export_categories' ),
				'args'     => array(
					'per_page' => array(
						'required' => false,
						'type'     => 'integer',
					),
					'page	'  => array(
						'required' => false,
						'type'     => 'integer',
					),
					'id'       => array(
						'required' => false,
						'type'     => 'integer',
					),
				),
			)
		);

	}

	public function export_categories( \WP_REST_Request $request ) {

		if ( isset( $request['per_page'] ) && isset( $request['page'] ) ) {

			$offset = 1 == $request['page'] ? '' : $request['per_page'] - 2;

			$categories = get_terms(
				array(
					'taxonomy'   => 'category',
					'number'     => $request['per_page'],
					'hide_empty' => false,
					'offset'     => $offset,
				)
			);

			$refined_categories = [];

			foreach ( $categories as $category ) {
				$refined_categories[] = [
					'id'   => $category->term_id,
					'slug' => $category->slug,
					'name' => $category->name,
				];
			}

			echo wp_json_encode( $refined_categories );
			exit();
		}

		if ( isset( $request['id'] ) ) {

			$category = get_the_category( $request['id'] );

			echo wp_json_encode(
				[
					'id'   => $category[0]->id,
					'slug' => $category[0]->slug,
					'name' => $category[0]->name,
				]
			);
			exit();
		}

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

