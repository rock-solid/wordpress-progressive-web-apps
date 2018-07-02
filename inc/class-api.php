<?php
namespace PWAPP\Inc;

use PWAPP\Inc\Options;
use PWAPP\Inc\Themes_Config;
use PWAPP\Inc\Uploads;
use PWAPP\Inc\PWAPP_Export;
use PWAPP\Frontend\Application;

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

		register_rest_route(
			'pwapp', '/language', array(
				'methods'  => 'GET',
				'callback' => array( $this, 'export_language' ),
			)
		);

	}

	public function export_categories( \WP_REST_Request $request ) {

		global $wp_rest_server;

		if ( isset( $request['per_page'] ) && isset( $request['page'] ) ) {
			$internal_request = new \WP_REST_Request( 'GET', '/wp/v2/categories' );

			$internal_request->set_param( 'per_page', $request['per_page'] );
			$internal_request->set_param( 'page', $request['page'] );

			$response   = rest_do_request( $internal_request );
			$categories = $wp_rest_server->response_to_data( $response, true );

			$refined_categories = [];

			foreach ( $categories as $category ) {

				$refined_categories[] = [
					'id'    => $category['id'],
					'slug'  => $category['id'],
					'name'  => $category['id'],
					'image' => $this->get_category_image( $category->id ),
				];
			}

			return   new \WP_REST_Response( $refined_categories, 200 );
			exit();
		}

		if ( isset( $request['id'] ) ) {
			$internal_request = new \WP_REST_Request( 'GET', '/wp/v2/categories/' . $request['id'] );

			$response   = rest_do_request( $internal_request );
			$categories = $wp_rest_server->response_to_data( $response, true );

			return new \WP_REST_Response(
				[
					'id'    => $categories['id'],
					'slug'  => $categories['id'],
					'name'  => $categories['id'],
					'image' => $this->get_category_image( $categories['id'] ),
				], 200
			);

		}

		exit();

	}


	/**
	 * Get the first featured image of a post that is not password protected from a category
	 */
	public function get_category_image( $category_id ) {
		$query = new \WP_Query(
			array(
				'posts_per_page' => 1,
				'meta_key'       => '_thumbnail_id',
				'post_type'      => 'post',
				'cat'            => $category_id,
				'has_password'   => false,
			)
		);

		if ( $query->have_posts() ) {
			return get_the_post_thumbnail_url( $query->post->ID );
		}

		return '';
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

		return new \WP_REST_Response( $arr_manifest, 200 );

	}


	/**
	 *
	 * Load app texts for the current locale.
	 *
	 * The JSON files with translations for each language are located in frontend/locales.
	 *
	 * @param $locale
	 * @param $response_type = javascript | list
	 * @return bool|mixed
	 *
	 */
	public function export_language() {

		$locale        = get_locale();
		$language_file = Application::check_language_file( $locale );

		if ( false !== $language_file ) {

			$app_texts      = file_get_contents( $language_file );
			$app_texts_json = json_decode( $app_texts, true );

			if ( $app_texts_json && ! empty( $app_texts_json ) && array_key_exists( 'APP_TEXTS', $app_texts_json ) ) {
				return new \WP_REST_Response( $app_texts_json['APP_TEXTS'], 200 );

			}
		}

		return false;
	}

}

