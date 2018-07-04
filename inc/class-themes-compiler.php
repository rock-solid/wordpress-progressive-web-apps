<?php

namespace PWAPP\Inc;

use Leafo\ScssPhp\Compiler;
use PWAPP\Inc\Options;
use PWAPP\Inc\Themes_Config;

/**
 * Overall Themes Management class
 *
 * This class uses the SCSS compiler and it should not be included outside the admin area
 *
 * @todo Test methods from this class separately.
 */
class Themes_Compiler {

	//@todo look into how compiler was fixed by andrei
	/* ----------------------------------*/
	/* Methods							 */
	/* ----------------------------------*/


	/**
	 * Compile new css theme file.
	 *
	 * The method will return false (error) if:
	 *
	 * - it can't compile the theme because the PHP version is too old
	 * - the uploads folder is not writable
	 * - the variables SCSS file can't be created
	 * - the CSS file can't be compiled
	 *
	 * @param $theme_timestamp
	 *
	 * @return array with the following properties:
	 * - compiled = (bool) If the theme was successfully compiled
	 * - error = An error message
	 *
	 */
	public function compile_css_file( $theme_timestamp ) {

		$response = array(
			'compiled' => false,
			'error'    => false,
		);

		if ( ! is_writable( PWAPP_FILES_UPLOADS_DIR ) ) {

			$response['error'] = 'Error uploading theme files, the upload folder ' . PWAPP_FILES_UPLOADS_DIR . ' is not writable.';

		} else {

			$wp_uploads_dir    = wp_upload_dir();
			$pwapp_uploads_dir = $wp_uploads_dir['basedir'] . '/';

			if ( ! is_writable( $pwapp_uploads_dir ) ) {

				$response['error'] = 'Error uploading theme files, your uploads folder ' . $pwapp_uploads_dir . ' is not writable.';

			} else {

				// write scss file with the colors and fonts variables
				$generated_vars_scss = $this->generate_variables_file( $error );

				if ( $generated_vars_scss ) {

					// compile css
					$response['compiled'] = $this->generate_css_file( $theme_timestamp, $error );

					// cleanup variables file
					$this->remove_variables_file();
					return $response;
				}
			}
		}

		return $response;
	}


	/**
	 * Delete css file
	 *
	 * @param $theme_timestamp
	 */
	public function remove_css_file( $theme_timestamp ) {
		$file_path = PWAPP_FILES_UPLOADS_DIR . 'theme-' . $theme_timestamp . '.css';

		if ( file_exists( $file_path ) ) {
			unlink( $file_path );
		}
	}


	/**
	 *
	 * Write a scss file with the theme's settings (colors and fonts)
	 *
	 * @param bool $error
	 * @return bool
	 *
	 */
	protected function generate_variables_file( &$error = false ) {

		// attempt to open or create the scss file
		$file_path = PWAPP_FILES_UPLOADS_DIR . '_variables.scss';

		$fp = @fopen( $file_path, 'w' );

		if ( false !== $fp ) {

			// read theme settings
			$theme        = 2;
			$color_scheme = Options::get_setting( 'color_scheme' );

			$theme_config = Themes_Config::get_theme_config();

			if ( false !== $theme_config ) {

				if ( 0 == $color_scheme ) {
					$colors = Options::get_setting( 'custom_colors' );
				} else {
					$colors = $theme_config['presets'][ $color_scheme ];
				}

				// write colors
				foreach ( $theme_config['vars'] as $key => $var_name ) {
					fwrite( $fp, '$' . $var_name . ':' . $colors[ $key ] . ";\r\n" );
				}

				// write font family
				$font_family = Themes_Config::$allowed_fonts[ Options::get_setting( 'font_family' ) - 1 ];
				fwrite( $fp, '$base-font-family: "' . $font_family . '";' . "\r\n" );

				// write font size
				fwrite( $fp, '$base-font-size:' . Options::get_setting( 'font_size' ) . "rem;\r\n" );

				fclose( $fp );
				return true;
			}
		} else {

			$error = 'Unable to compile theme, the file ' . $file_path . ' is not writable.';
		}

		return false;
	}


	/**
	 *
	 * Delete variables scss file
	 *
	 */
	protected function remove_variables_file() {
		$file_path = PWAPP_FILES_UPLOADS_DIR . '_variables.scss';

		if ( file_exists( $file_path ) ) {
			unlink( $file_path );
		}
	}


	/**
	 *
	 * Generate a CSS file using the variables and theme SCSS files
	 *
	 * The CSS file is created as 'theme-{$theme_timestamp}.css' in the plugin's uploads folder.
	 *
	 * @param $theme_timestamp
	 * @param bool|string $error
	 * @return bool
	 *
	 */
	protected function generate_css_file( $theme_timestamp, &$error = false ) {

		// attempt to open or create the scss file
		$file_path = PWAPP_FILES_UPLOADS_DIR . 'theme-' . $theme_timestamp . '.css';

		$fp = @fopen( $file_path, 'w' );

		if ( false !== $fp ) {

			$scss_compiler = new Compiler();

			$scss_compiler->setImportPaths(
				array(
					PWAPP_FILES_UPLOADS_DIR,
					PWAPP_PLUGIN_PATH . 'frontend/themes/app2' . '/scss/',
				)
			);

			$scss_compiler->setFormatter( 'Leafo\ScssPhp\Formatter\Compressed' );

			try {

				// write compiler output directly in the css file
				$compiled_file = $scss_compiler->compile( '@import "_variables.scss"; @import "phone.scss";' );
				fwrite( $fp, $compiled_file );

				fclose( $fp );
				return true;

			} catch ( Exception $e ) {

				$error = "Unable to compile theme, the theme's scss file contains errors.";
				fclose( $fp );
			}
		} else {
			$error = 'Unable to compile theme, the file ' . $file_path . ' is not writable.';
		}

		return false;
	}
}
