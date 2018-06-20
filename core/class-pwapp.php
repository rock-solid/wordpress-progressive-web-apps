<?php

namespace PWAPP\Core;

use PWAPP\Inc\Cookie;
use PWAPP\Inc\Options;
use PWAPP\Inc\Uploads;

/**
 *
 * Main class for the progressive web apps plugin. This class handles:
 *
 * - activation / deactivation of the plugin
 * - setting / getting the plugin's options
 * - loading the admin section, javascript and css files
 * - loading the app in the frontend
 *
 */
class PWAPP
{

	/* ----------------------------------*/
	/* Methods							 */
	/* ----------------------------------*/

	/**
	 *
	 * Construct method that initializes the plugin's options
	 *
	 */
	public function __construct()
	{

		// create uploads folder and define constants
		if ( !defined( 'PWAPP_FILES_UPLOADS_DIR' ) && !defined( 'PWAPP_FILES_UPLOADS_URL' ) ) {
			$pwapp_uploads = new Uploads();
			$pwapp_uploads->define_uploads_dir();
		}

		if ( is_admin() ) {
			$this->setup_hooks();
		}
	}


	/**
	 *
	 * The activate() method is called on the activation of the plugin.
	 *
	 * This method adds to the DB the default settings of the plugin and creates the upload folder.
	 *
	 */
	public function activate()
	{
		// add settings to database
		Options::save_settings(Options::$options);

		$Uploads = new Uploads();
		$Uploads->create_uploads_dir();
	}


	/**
	 *
	 * The deactivate() method is called when the plugin is deactivated.
	 * This method removes temporary data (transients and cookies).
	 *
	 */
	public function deactivate()
	{
		// delete plugin settings (transients)
		Options::deactivate();

		// remove the cookies
		$pwapp_cookie = new Cookie();

		$pwapp_cookie->set_cookie("theme_mode", false, -3600);
		$pwapp_cookie->set_cookie("load_app", false, -3600);
	}


	/**
	 * Init admin notices hook
	 */
	public function setup_hooks(){
		add_action( 'admin_notices', array( $this, 'display_admin_notices' ) );
	}

	/**
	 *
	 * Show admin notices if PHP version is too old
	 *
	 */
	public function display_admin_notices(){

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if (version_compare(PHP_VERSION, '5.4') < 0) {
			echo '<div class="error"><p><b>Warning!</b> The ' . PWAPP_PLUGIN_NAME . ' plugin requires at least PHP 5.4.0!</p></div>';
		}

		$this->display_icon_reupload_notice();
	}


	/**
	 *
	 * Display icon reupload notice if icon is uploaded and manifest icon sizes are missing.
	 *
	 */
	public function display_icon_reupload_notice(){

		$icon_filename = Options::get_setting('icon');

		if ($icon_filename != '' && file_exists(PWAPP_FILES_UPLOADS_DIR  . $icon_filename)) {
			foreach (Uploads::$manifest_sizes as $manifest_size) {
				if (!file_exists(PWAPP_FILES_UPLOADS_DIR  . $manifest_size . $icon_filename)) {
					echo '<div class="notice notice-warning is-dismissible"><p>Progressive Web Apps 0.7 comes with Add To Home Screen functionality which requires you to reupload your <a href="' . get_admin_url() . 'admin.php?page=pwapp-options-theme-settings"/>App Icon</a>!</p></div>';
					return;
				}
			}
		}
	}

	/**
	 *
	 * Static method used to request the content of different pages using curl or fopen
	 * This method returns false if both curl and fopen are dissabled and an empty string ig the json could not be read
	 *
	 */
	public static function read_data($json_url) {

		// check if curl is enabled
		if (extension_loaded('curl')) {

			$send_curl = curl_init($json_url);

			// set curl options
			curl_setopt($send_curl, CURLOPT_URL, $json_url);
			curl_setopt($send_curl, CURLOPT_HEADER, false);
			curl_setopt($send_curl, CURLOPT_CONNECTTIMEOUT, 2);
			curl_setopt($send_curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($send_curl, CURLOPT_HTTPHEADER,array('Accept: application/json', "Content-type: application/json"));
			curl_setopt($send_curl, CURLOPT_FAILONERROR, FALSE);
			curl_setopt($send_curl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($send_curl, CURLOPT_SSL_VERIFYHOST, FALSE);
			$json_response = curl_exec($send_curl);

			// get request status
			$status = curl_getinfo($send_curl, CURLINFO_HTTP_CODE);
			curl_close($send_curl);

			// return json if success
			if ($status == 200)
				return $json_response;

		} elseif (ini_get( 'allow_url_fopen' )) { // check if allow_url_fopen is enabled

			// open file
			$json_file = fopen( $json_url, 'rb' );

			if($json_file) {

				$json_response = '';

				// read contents of file
				while (!feof($json_file)) {
					$json_response .= fgets($json_file);
				}
			}

			// return json response
			if($json_response)
				return $json_response;

		} else
			// both curl and fopen are disabled
			return false;

		// by default return an empty string
		return '';

	}

}
