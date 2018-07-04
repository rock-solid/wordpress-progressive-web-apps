<?php

namespace PWAPP\Admin;

use PWAPP\Inc\Themes_Config;
use PWAPP\Inc\Themes_Compiler;
use PWAPP\Inc\Options;
use PWAPP\Inc\Uploads;

/**
 *
 * Admin_Ajax class for managing Ajax requests from the admin area of the plugin
 *
 * @todo Test separately the methods of this class
 */
class Admin_Ajax {


	/**
	 * Save new font settings into the database. Returns true if we need to compile the css file.
	 *
	 * @param $data = array with POST data
	 *
	 * @return array with the following properties:
	 * - scss - If we need to compile the theme
	 * - updated - If any of the font settings have changed
	 */
	protected function update_theme_fonts( $data ) {

		// check if we have to compile the scss file
		$response = array(
			'scss'    => false,
			'updated' => false,
		);

		if ( isset( $data['pwapp_edittheme_fontfamily'] ) ) {

			// check if the font settings have changed
			if ( Options::get_setting( 'font_family' ) != $data['pwapp_edittheme_fontfamily'] ) {

				Options::update_settings( 'font_family', $data['pwapp_edittheme_fontfamily'] );
				$response['updated'] = true;
			}

			// if a font different from the default one was selected, we need to compile the css file
			if ( 1 != $data['pwapp_edittheme_fontfamily'] ) {
				$response['scss'] = true;
			}
		}

		if ( isset( $data['pwapp_edittheme_fontsize'] ) ) {

			// check if the font size setting has changed
			if ( Options::get_setting( 'font_size' ) != $data['pwapp_edittheme_fontsize'] ) {

				Options::update_settings( 'font_size', $data['pwapp_edittheme_fontsize'] );
				$response['updated'] = true;
			}

			// if a font size different from the default one was selected, we need to compile the css file
			if ( 1 != $data['pwapp_edittheme_fontsize'] ) {
				$response['scss'] = true;
			}
		}

		return $response;
	}


	/**
	 *
	 * Save new color scheme setting into the database. Returns true if we need to compile the css file.
	 *
	 * @param $data = array with POST data
	 *
	 * @return array with the following properties:
	 * - scss - If we need to compile the theme
	 * - updated - If the color scheme setting has changed
	 */
	protected function update_theme_color_scheme( $data ) {

		// check if we have to compile the scss file
		$response = array(
			'scss'    => false,
			'updated' => false,
		);

		if ( isset( $data['pwapp_edittheme_colorscheme'] ) ) {

			if ( Options::get_setting( 'color_scheme' ) != $data['pwapp_edittheme_colorscheme'] ) {

				Options::update_settings( 'color_scheme', $data['pwapp_edittheme_colorscheme'] );
				$response['updated'] = true;
			}

			// enable compiling for the second & third color schemes
			if ( 1 != $data['pwapp_edittheme_colorscheme'] ) {
				$response['scss'] = true;
			}
		}

		return $response;
	}


	/**
	 * Save new colors settings into the database. Returns true if we need to compile the css file.
	 *
	 * @param $data = array with POST data
	 *
	 * @return array with the following properties:
	 * - scss - If we need to compile the theme
	 * - error = If set to true if we have invalid color codes or the number of colors is not the same
	 * with the one from the theme.
	 */
	protected function update_theme_colors( $data ) {

		$response = array(
			'scss'  => false,
			'error' => false,
		);

		$arr_custom_colors = array();

		// read theme and custom colors options
		$selected_theme         = Options::get_setting( 'theme' );
		$selected_custom_colors = Options::get_setting( 'custom_colors' );

		// how many colors does the theme have
		$theme_config = Themes_Config::get_theme_config();

		if ( false !== $theme_config ) {

			$no_theme_colors = count( $theme_config['vars'] );

			for ( $i = 0; $i < $no_theme_colors; $i++ ) {

				// validate color code format
				if ( isset( $data[ 'pwapp_edittheme_customcolor' . $i ] ) &&
					trim( $data[ 'pwapp_edittheme_customcolor' . $i ] ) != '' &&
					preg_match( '/^#[a-f0-9]{6}$/i', trim( $data[ 'pwapp_edittheme_customcolor' . $i ] ) ) ) {

					$arr_custom_colors[] = strtolower( $data[ 'pwapp_edittheme_customcolor' . $i ] );

					// if the color settings have changed, we need to recompile the css file
					if ( empty( $selected_custom_colors ) ||
						( isset( $selected_custom_colors[ $i ] ) && strtolower( $data[ 'pwapp_edittheme_customcolor' . $i ] ) != $selected_custom_colors[ $i ] ) ) {

						$response['scss'] = true;
					}
				} else {
					$response['error'] = true;
					break;
				}
			}

			// save colors only if all the colors from the theme have been set
			if ( count( $arr_custom_colors ) == $no_theme_colors ) {

				Options::update_settings( 'custom_colors', $arr_custom_colors );

			} else {

				$response['error'] = true;
				$response['scss']  = false;
			}
		}

		return $response;
	}


	/**
	 *
	 * Delete custom theme file and reset option
	 */
	protected function remove_custom_theme() {

		// remove compiled css file (if it exists)
		$theme_timestamp = Options::get_setting( 'theme_timestamp' );

		if ( '' != $theme_timestamp ) {

			$pwapp_themes_compiler = new Themes_Compiler();

			if ( false !== $pwapp_themes_compiler ) {

				$pwapp_themes_compiler->remove_css_file( $theme_timestamp );
				Options::update_settings( 'theme_timestamp', '' );
			}
		}
	}

	/**
	 *
	 * Method used to save the custom settings for a theme.
	 *
	 * Displays a JSON response with the following fields:
	 *
	 * - status = 0 if an error has occurred, 1 otherwise
	 * - messages = array with error messages, possible values are:
	 *
	 * - invalid custom colors format
	 * - settings were not changed
	 * - other error messages resulted from compiling the theme
	 */
	public function theme_settings() {
		if ( current_user_can( 'manage_options' ) ) {

			$arr_response = array(
				'status'   => 0,
				'messages' => array(),
			);

			// handle color schemes and fonts (look & feel page)
			if ( isset( $_POST['pwapp_edittheme_colorscheme'] ) && is_numeric( $_POST['pwapp_edittheme_colorscheme'] ) &&
			isset( $_POST['pwapp_edittheme_fontfamily'] ) && is_numeric( $_POST['pwapp_edittheme_fontfamily'] ) &&
			isset( $_POST['pwapp_edittheme_fontsize'] ) && is_numeric( $_POST['pwapp_edittheme_fontsize'] ) ) {

				// build array with the allowed fonts sizes
				$allowed_fonts_sizes = array();
				foreach ( Themes_Config::$allowed_fonts_sizes as $allowed_font_size ) {
					$allowed_fonts_sizes[] = $allowed_font_size['size'];
				}

				if ( in_array( $_POST['pwapp_edittheme_colorscheme'], array( 0, 1, 2, 3 ) ) &&
				in_array( $_POST['pwapp_edittheme_fontfamily'] - 1, array_keys( Themes_Config::$allowed_fonts ) ) &&
				in_array( $_POST['pwapp_edittheme_fontsize'], $allowed_fonts_sizes ) ) {

					// check if the theme compiler can be successfully loaded
					$pwapp_themes_compiler = new Themes_Compiler();

					if ( false === $pwapp_themes_compiler ) {

						$arr_response['messages'][] = 'Unable to load theme compiler. Please check your PHP version, should be at least 5.3.';

					} else {

						// save custom colors first
						$updated_colors = array(
							'scss'  => false,
							'error' => false,
						);

						if ( 0 == $_POST['pwapp_edittheme_colorscheme'] ) {

							$updated_colors = $this->update_theme_colors( $_POST );

							// if the colors were not successfully processed, display error message and exit
							if ( $updated_colors['error'] ) {

								$arr_response['messages'][] = 'Please select all colors before saving the custom color scheme!';
								echo json_encode( $arr_response );

								wp_die();
							}
						}

						// update fonts and check if we need to compile the scss file
						$updated_fonts = $this->update_theme_fonts( $_POST );

						// update color scheme
						$updated_color_scheme = $this->update_theme_color_scheme( $_POST );

						// the settings haven't changed, so return error status
						if ( ! $updated_colors['scss'] && ! $updated_fonts['updated'] && ! $updated_color_scheme['updated'] ) {

							$arr_response['messages'][] = 'Your application\'s settings have not changed!';

						} else {

							if ( $updated_colors['scss'] || $updated_fonts['scss'] || $updated_color_scheme['scss'] ) {

								$theme_timestamp = time();

								// create new css theme file
								$theme_compiled = $pwapp_themes_compiler->compile_css_file( $theme_timestamp );

								if ( ! $theme_compiled['compiled'] ) {
									$arr_response['messages'][] = $theme_compiled['error'];
								} else {

									// delete old css file (if it exists)
									$old_theme_timestamp = Options::get_setting( 'theme_timestamp' );

									// update theme timestamp
									Options::update_settings( 'theme_timestamp', $theme_timestamp );

									if ( '' != $old_theme_timestamp ) {
										$pwapp_themes_compiler->remove_css_file( $old_theme_timestamp );
									}

									// the theme was successfully compiled and saved
									$arr_response['status'] = 1;
								}
							} else {

								// we have reverted to the default theme settings, remove custom theme file
								$this->remove_custom_theme();
								$arr_response['status'] = 1;
							}
						}
					}
				}
			}

			echo json_encode( $arr_response );
		}

		wp_die();
	}



	/**
	 *
	 * Save service worker setting
	 */
	public function settings_save() {

		if ( current_user_can( 'manage_options' ) ) {
			$status = 0;

			if ( isset( $_POST ) && is_array( $_POST ) && ! empty( $_POST ) ) {

				if ( isset( $_POST['pwapp_option_service_worker_installed'] ) && '' != $_POST['pwapp_option_service_worker_installed'] && is_numeric( $_POST['pwapp_option_service_worker_installed'] ) ) {

					$enabled_option = intval( $_POST['pwapp_option_service_worker_installed'] );

					if ( 0 == $enabled_option || 1 == $enabled_option ) {

						$status = 1;
						// save option
						Options::update_settings( 'service_worker_installed', $enabled_option );
					}
				}
			}

			echo $status;
		}

		exit();
	}

	/**
	 * Resize & copy image using WordPress methods
	 *
	 * @param $file_type = icon or logo
	 * @param $file_path
	 * @param $file_name
	 * @param string                          $error_message
	 * @return bool
	 */
	protected function resize_image( $file_type, $file_path, $file_name, &$error_message = '' ) {

		$copied_and_resized = false;

		if ( array_key_exists( $file_type, Uploads::$allowed_files ) ) {

			$arr_maximum_size = Uploads::$allowed_files[ $file_type ];

			$image = wp_get_image_editor( $file_path );

			if ( ! is_wp_error( $image ) ) {

				$image_size = $image->get_size();

				if ( 'icon' == $file_type ) {

					foreach ( Uploads::$manifest_sizes as $manifest_size ) {

						$manifest_image = wp_get_image_editor( $file_path );
						$manifest_image->resize( $manifest_size, $manifest_size, true );
						$manifest_image->save( PWAPP_FILES_UPLOADS_DIR . $manifest_size . $file_name );
					}
				}

				// if the image exceeds the size limits
				if ( $image_size['width'] > $arr_maximum_size['max_width'] || $image_size['height'] > $arr_maximum_size['max_height'] ) {

					// resize and copy to the plugin uploads folder
					$image->resize( $arr_maximum_size['max_width'], $arr_maximum_size['max_height'] );
					$image->save( PWAPP_FILES_UPLOADS_DIR . $file_name );

					$copied_and_resized = true;

				} else {

					// copy file without resizing to the plugin uploads folder
					$copied_and_resized = copy( $file_path, PWAPP_FILES_UPLOADS_DIR . $file_name );
				}
			} else {

				$error_message = 'We encountered a problem resizing your ' . $file_type . '. Please choose another image!';
			}
		}

		return $copied_and_resized;
	}



	/**
	 *
	 * Remove image using the corresponding option's value for the filename
	 *
	 * @param $file_type = icon or logo
	 * @return bool
	 */
	protected function remove_image( $file_type ) {
		// get previous image filename
		$previous_file_path = Options::get_setting( $file_type );

		// check the file exists and remove it
		if ( '' != $previous_file_path ) {
			$uploads = new Uploads();

			if ( 'icon' == $file_type ) {
				foreach ( Uploads::$manifest_sizes as $manifest_size ) {
					$uploads->remove_uploaded_file( $manifest_size . $previous_file_path );
				}
			}

			return $uploads->remove_uploaded_file( $previous_file_path );
		}

		return false;
	}


	/**
	 *
	 * Method used to save the icon, logo
	 */
	public function theme_editimages() {

		if ( current_user_can( 'manage_options' ) ) {

			$action = null;

			if ( ! empty( $_GET ) && isset( $_GET['type'] ) ) {
				if ( 'upload' == $_GET['type'] || 'delete' == $_GET['type'] ) {
					$action = $_GET['type'];
				}
			}

			$arr_response = array(
				'status'   => 0,
				'messages' => array(),
			);

			if ( 'upload' == $action ) {

				if ( ! empty( $_FILES ) && sizeof( $_FILES ) > 0 ) {

					require_once ABSPATH . 'wp-admin/includes/image.php';

					if ( ! function_exists( 'wp_handle_upload' ) ) {
						require_once ABSPATH . 'wp-admin/includes/file.php';
					}

					$default_uploads_dir = wp_upload_dir();

					// check if the upload folder is writable
					if ( ! is_writable( PWAPP_FILES_UPLOADS_DIR ) ) {

						$arr_response['messages'][] = 'Error uploading image, the upload folder ' . PWAPP_FILES_UPLOADS_DIR . ' is not writable.';

					} elseif ( ! is_writable( $default_uploads_dir['path'] ) ) {

						$arr_response['messages'][] = 'Error uploading image, the upload folder ' . $default_uploads_dir['path'] . ' is not writable.';

					} else {

						$has_uploaded_files = false;

						foreach ( $_FILES as $file => $info ) {

							if ( ! empty( $info['name'] ) ) {

								$has_uploaded_files = true;

								$file_type = null;

								if ( 'pwapp_editimages_icon' == $file ) {
									$file_type = 'icon';
								} elseif ( 'pwapp_editimages_logo' == $file ) {
									$file_type = 'logo';
								}

								if ( $info['error'] >= 1 || $info['size'] <= 0 && array_key_exists( $file_type, Uploads::$allowed_files ) ) {

									$arr_response['status']     = 0;
									$arr_response['messages'][] = 'We encountered a problem processing your ' . $file_type . '. Please choose another image!';

								} elseif ( $info['size'] > 1048576 ) {

									$arr_response['status']     = 0;
									$arr_response['messages'][] = 'Do not exceed the 1MB file size limit when uploading your custom ' . $file_type . '.';

								} else {

									// make unique file name for the image
									$arr_file_name  = explode( '.', $info['name'] );
									$file_extension = end( $arr_file_name );

									$arr_allowed_extensions = Uploads::$allowed_files[ $file_type ]['extensions'];

									// check file extension
									if ( ! in_array( strtolower( $file_extension ), $arr_allowed_extensions ) ) {

										$arr_response['messages'][] = 'Error saving image, please add a ' . implode( ' or ', $arr_allowed_extensions ) . ' image for your ' . $file_type . '!';

									} else {

										// upload image
										$unique_file_name = $file_type . '_' . time() . '.' . $file_extension;

										// upload to the default uploads folder
										$upload_overrides = array( 'test_form' => false );
										$movefile         = wp_handle_upload( $info, $upload_overrides );

										if ( is_array( $movefile ) ) {

											if ( isset( $movefile['error'] ) ) {

												$arr_response['messages'][] = $movefile['error'];

											} else {

												// resize and copy image

												$copied_and_resized = $this->resize_image( $file_type, $movefile['file'], $unique_file_name, $error_message );

												if ( '' != $error_message ) {
													$arr_response['messages'][] = $error_message;
												}

												// delete previous image and set option

												if ( $copied_and_resized ) {

													// delete previous image
													$this->remove_image( $file_type );

													// save option
													Options::update_settings( $file_type, $unique_file_name );

													// add path in the response
													$arr_response['status']                   = 1;
													$arr_response[ 'uploaded_' . $file_type ] = PWAPP_FILES_UPLOADS_URL . $unique_file_name;
												}

												// remove file from the default uploads folder
												if ( file_exists( $movefile['file'] ) ) {
													unlink( $movefile['file'] );
												}
											}
										}
									}
								}
							}
						}

						if ( false == $has_uploaded_files ) {
							$arr_response['messages'][] = 'Please upload an image!';
						}
					}
				}
			} elseif ( 'delete' == $action ) {

				// delete icon, logo, depending on the 'source' param
				if ( isset( $_GET['source'] ) ) {

					if ( array_key_exists( $_GET['source'], Uploads::$allowed_files ) ) {

						$file_type = $_GET['source'];

						if ( in_array( $file_type, array( 'icon', 'logo', 'cover' ) ) ) {

							// get the previous file name from the options table
							$this->remove_image( $file_type );

							// save option with an empty value
							Options::update_settings( $file_type, '' );

							$arr_response['status'] = 1;
						}
					}
				}
			}

			// echo json with response
			echo json_encode( $arr_response );
		}

		exit();
	}


	/**
	 *
	 * Method used to send a feedback e-mail from the admin
	 *
	 * Handle request, then display 1 for success and 0 for error.
	 */
	public function send_feedback() {

		if ( current_user_can( 'manage_options' ) ) {

			$status = 0;

			if ( isset( $_POST ) && is_array( $_POST ) && ! empty( $_POST ) ) {

				if ( isset( $_POST['pwapp_feedback_page'] ) &&
					isset( $_POST['pwapp_feedback_name'] ) &&
					isset( $_POST['pwapp_feedback_email'] ) &&
					isset( $_POST['pwapp_feedback_message'] ) ) {

					if ( '' != $_POST['pwapp_feedback_page'] &&
						'' != $_POST['pwapp_feedback_name'] &&
						'' != $_POST['pwapp_feedback_email'] &&
						'' != $_POST['pwapp_feedback_message'] ) {

						$admin_email = $_POST['pwapp_feedback_email'];

						// filter e-mail
						if ( filter_var( $admin_email, FILTER_VALIDATE_EMAIL ) !== false ) {

							// set e-mail variables
							$message  = 'Name: ' . strip_tags( $_POST['pwapp_feedback_name'] ) . "\r\n \r\n";
							$message .= 'E-mail: ' . $admin_email . "\r\n \r\n";
							$message .= 'Message: ' . strip_tags( $_POST['pwapp_feedback_message'] ) . "\r\n \r\n";
							$message .= 'Page: ' . stripslashes( strip_tags( $_POST['pwapp_feedback_page'] ) ) . "\r\n \r\n";

							if ( isset( $_SERVER['HTTP_HOST'] ) ) {
								$message .= 'Host: ' . $_SERVER['HTTP_HOST'] . "\r\n \r\n";
							}

							// add license fields
							$message .= 'License key: ' . Options::get_setting( 'license_key' ) . "\r\n \r\n";
							$message .= 'License status: ' . Options::get_setting( 'license_status' ) . "\r\n \r\n";

							$license_expiry_date = Options::get_setting( 'license_expiry_date' );
							if ( '' != $license_expiry_date ) {
								$message .= 'License expires: ' . date( 'Y-m-d H:i:s', $license_expiry_date ) . "\r\n \r\n";
							}

							$subject = PWAPP_PLUGIN_NAME . ' Feedback';
							$to      = PWAPP_FEEDBACK_EMAIL;

							// set headers
							$headers = 'From:' . $admin_email . "\r\nReply-To:" . $admin_email;

							// send e-mail
							if ( mail( $to, $subject, $message, $headers ) ) {
								$status = 1;
							}
						}
					}
				}
			}

			echo $status;
		}

		exit();
	}
}
