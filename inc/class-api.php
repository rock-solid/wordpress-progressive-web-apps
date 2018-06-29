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

		register_rest_route(
			'pwapp', '/posts', array(
				'methods'  => 'GET',
				'callback' => array( $this, 'export_posts' ),
				'args'     => array(
					'per_page'   => array(
						'required' => false,
						'type'     => 'integer',
					),
					'page'       => array(
						'required' => false,
						'type'     => 'integer',
					),
					'id'         => array(
						'required' => false,
						'type'     => 'integer',
					),
					'status'     => array(
						'required' => false,
						'type'     => 'string',
						'enum'     => array(
							'publish',
						),
					),
					'categories' => array(
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
					'id'    => $category->term_id,
					'slug'  => $category->slug,
					'name'  => $category->name,
					'image' => $this->get_category_image( $category->term_id ),
				];
			}

			return   new \WP_REST_Response( $refined_categories, 200 );
			exit();
		}

		if ( isset( $request['id'] ) ) {

			$category = get_category( $request['id'] );

			return new \WP_REST_Response(
				[
					'id'    => $category->term_id,
					'slug'  => $category->slug,
					'name'  => $category->name,
					'image' => $this->get_category_image( $category->term_id ),
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


	public function export_posts( \WP_REST_Request $incoming_request ) {
		global $wp_rest_server;
		$internal_request = new \WP_REST_Request( 'GET', '/wp/v2/posts' );

		if ( isset( $incoming_request['per_page'] ) && isset( $incoming_request['page'] ) && isset( $incoming_request['categories'] ) && isset( $incoming_request['status'] ) ) {
			// Set one or more request query parameters
			$internal_request->set_param( 'per_page', $incoming_request['per_page'] );
			$internal_request->set_param( 'page', $incoming_request['page'] );
			$internal_request->set_param( 'categories', $incoming_request['categories'] );
			$internal_request->set_param( 'status', $incoming_request['status'] );

			$response = rest_do_request( $internal_request );

			$response_data = $wp_rest_server->response_to_data( $response, true );

			$filtered_posts = $this->filter_no_password_posts( $response_data );
			$page           = $incoming_request['page'];

			if ( count( $response_data ) < $incoming_request['per_page'] ) {
				return new \WP_REST_Response(
					array(
						'posts'    => $filtered_posts,
						'page'     => $page,
						'loadMore' => false,
					), 200
				);
			}
			while ( count( $filtered_posts ) < $incoming_request['per_page'] && count( $response_data >= $incoming_request['per_page'] ) ) {

				$page++;
				$missing_posts = $incoming_request['per_page'] - count( $filtered_posts );

				$internal_request->set_param( 'page', $page );
				$response      = rest_do_request( $internal_request );
				$response_data = $wp_rest_server->response_to_data( $response, true );

				$no_password_posts = $this->filter_no_password_posts( $response_data );

				if ( count( $no_password_posts ) >= $missing_posts ) {
					$filtered_posts = array_merge( $filtered_posts, array_slice( $no_password_posts, 0, 1 === $missing_posts ? 1 : $missing_posts - 1 ) );
				}
			}

			return   new \WP_REST_Response(
				array(
					'posts'    => $filtered_posts,
					'page'     => $page !== $incoming_request['page'] ? $page : $page + 1,
					'loadMore' => true,
				), 200
			);
			exit();
		}

		if ( isset( $incoming_request['id'] ) ) {

			// Set one or more request query parameters
			$internal_request->set_param( 'id', $incoming_request['id'] );
			$response      = rest_do_request( $internal_request );
			$response_data = $wp_rest_server->response_to_data( $response, true );

			return new \WP_REST_Response( $response_data, 200 );
			exit();
		}

		exit();
	}

	public function filter_no_password_posts( $posts ) {
		$no_password_posts = [];

		foreach ( $posts as $post ) {
			if ( false === $post['content']['protected'] ) {
				array_push( $no_password_posts, $post );
			}
		}

		return $no_password_posts;
	}

}

