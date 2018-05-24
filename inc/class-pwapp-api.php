<?php
namespace PWAPP\Inc;

use \PWAPP\Inc\Options;
use \PWAPP\Inc\Uploads;
use \PWAPP\Inc\PWAPP_Export;

/**
 *  Class that sets up the rest routes for the app
 */
class PWAPP_API
{

	/**
	 * Returns an instance of the woocommerce api client with configurations in place
	 */
	protected function get_client() {
		return new PWAPP_Export();
	}

	/**
	 * Registers all the plugins rest routes
	 */
	public function register_pwapp_routes() {

		$client = $this->get_client();

		register_rest_route( 'pwapp', '/export-manifest', [
			'methods' => 'GET',
			'callback' => [ $client, 'export_manifest' ],
		]);

		register_rest_route( 'pwapp', '/categories', [
			'methods' => 'GET',
			'callback' => [ $client, 'export_categories' ]
		]);

		register_rest_route( 'pwapp', '/posts', [
			'methods' => 'GET',
			'callback' => [ $client, 'export_articles' ],
			'args' => [
				'categId' => [
					'required' => false,
					'type' => 'integer',
				],
			],
		]);

		register_rest_route( 'pwapp', '/pages', [
			'methods' => 'GET',
			'callback' => [ $client, 'export_pages' ]
		]);

		register_rest_route( 'pwapp', '/comments', [
			'methods' => 'GET',
			'callback' => [ $client, 'export_comments' ]
		]);

	}
}

