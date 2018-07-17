<?php

use \PWAPP\Inc\Themes_Config;
use \PWAPP\Inc\Options;
use \PWAPP\Inc\Uploads;

?>

<script type="text/javascript">
	if (window.PWAPPJSInterface && window.PWAPPJSInterface != null){
		jQuery(document).ready(function(){

			PWAPPJSInterface.localpath = "<?php echo plugins_url() . '/' . PWAPP_DOMAIN . '/'; ?>";
			PWAPPJSInterface.init();
		});
	}
</script>
<div id="pwapp-admin">
	<div class="spacer-60"></div>
	<!-- set title -->
	<h1><?php echo PWAPP_PLUGIN_NAME . ' ' . PWAPP_VERSION; ?></h1>
	<div class="spacer-20"></div>
	<div class="look-and-feel">
		<div class="left-side">
			<!-- add nav menu -->
			<?php include_once( PWAPP_PLUGIN_PATH . 'admin/sections/admin-menu.php' ); ?>

			<div class="spacer-0"></div>

			<!-- add content form -->
			<div class="details">
				<div class="spacer-10"></div>
				<p>Customize your progressive web application by choosing from the below color schemes & fonts, adding your logo and app icon.</p>
				<div class="spacer-20"></div>
			</div>
			<div class="spacer-10"></div>
			<?php

				$selected_theme = Options::get_setting( 'theme' );
				$has_old_theme = Themes_Config::has_old_theme();

				if ( $has_old_theme ) {
					Themes_Config::reset_theme_settings();
				}

				$theme_settings = Themes_Config::get_theme_config();

			if ( $theme_settings !== false ) :
				?>
					<div class="details">
						<h2 class="title">Customize Color Schemes and Fonts</h2>
						<div class="spacer-15"></div>
						<div class="grey-line"></div>
						<div class="spacer-10"></div>

						<?php if ($has_old_theme): ?>
							<div class="message-container warning">
								<div class="wrapper">
									<span>The app has been upgraded to a new version, please add your styling using the new colors and fonts settings.</span>
								</div>
								<div class="spacer-10"></div>
							</div>
						<?php endif; ?>
						<div class="spacer-20"></div>

						<form name="pwapp_edittheme_form" id="pwapp_edittheme_form" action="<?php echo admin_url( 'admin-ajax.php' ); ?>?action=pwapp_theme_settings" method="post">

							<div class="color-schemes">
								<p class="section-header">Select Color Scheme</p>
								<div class="spacer-20"></div>

								<!-- add labels -->
								<div class="colors description">
									<?php foreach ( $theme_settings['labels'] as $key => $description ) : ?>
										<div class="color-" title="<?php echo $description; ?>"><?php echo $key + 1; ?></div>
									<?php endforeach; ?>
								</div>
								<div class="spacer-15"></div>

								<!-- add presets radio buttons & colors -->
							<?php
							$selected_color_scheme = Options::get_setting( 'color_scheme' );
							if ( '' == $selected_color_scheme ) {
								$selected_color_scheme = 1;
							}

							foreach ( $theme_settings['presets'] as $color_scheme => $default_colors ) :
								?>
									<input type="radio" name="pwapp_edittheme_colorscheme" id="pwapp_edittheme_colorscheme" value="<?php echo $color_scheme; ?>"
										<?php
										if ( $color_scheme == $selected_color_scheme ) {
											echo 'checked="checked"';}
										?>
									 	autocomplete="off"
									/>
									<div class="colors">
										<?php foreach ( $theme_settings['labels'] as $key => $description ) : ?>
											<div class="color-<?php echo $color_scheme . '-' . $key; ?>" title="<?php echo $description; ?>" style="background: <?php echo $theme_settings['presets'][ $color_scheme ][ $key ]; ?>"></div>
										<?php endforeach; ?>
									</div>
									<div class="spacer-20"></div>
								<?php endforeach; ?>

								<!-- add custom scheme radio button -->
								<input type="radio" name="pwapp_edittheme_colorscheme" id="pwapp_edittheme_colorscheme" value="0" <?php echo  0 == $selected_color_scheme ? 'checked="checked"' : ''; ?> autocomplete="off" />
								<p>Edit custom colors</p>
							</div>

							<!-- start notice -->
							<div class="notice notice-left left" style="max-width: 40%;">
								<span>
									The color scheme will impact the following sections within the mobile web application:<br/><br/>
								<?php
								foreach ( $theme_settings['labels'] as $key => $description ) {
									echo ( $key + 1 ) . '.&nbsp;' . $description . '<br/>';
								}
								?>
								</span>
							</div>

							<div class="spacer-20"></div>

							<!-- start color pickers -->
							<div class="color-schemes-custom" style="display: <?php echo  0 == $selected_color_scheme ? 'block' : 'none'; ?>;">

								<p class="section-header">Your Custom Scheme</p>
								<div class="spacer-20"></div>

								<div class="set">
									<?php
									// display color pickers and divide them into two columns
									$half = ceil( count( $theme_settings['labels'] ) / 2 );

									// read the custom colors options array
									$selected_custom_colors = Options::get_setting( 'custom_colors' );

									foreach ( $theme_settings['labels'] as $key => $description ) :

										$color_value = '';
										if ( ! empty( $selected_custom_colors ) && array_key_exists( $key, $selected_custom_colors ) ) {
											$color_value = $selected_custom_colors[ $key ];
										}
										?>
										<label for="pwapp_edittheme_customcolor<?php echo $key; ?>"><?php echo ( $key + 1 ) . '. ' . $description; ?></label>
										<input type="text" name="pwapp_edittheme_customcolor<?php echo $key; ?>" id="pwapp_edittheme_customcolor<?php echo $key; ?>" value="<?php echo $color_value; ?>" autocomplete="off" />
										<div class="spacer-10"></div>

										<?php if ( $key + 1 == $half ) : ?>
											</div>
											<div class="set">
										<?php endif; ?>

									<?php endforeach; ?>
								</div>
							</div>
							<div class="spacer-20"></div>

							<!-- choose fonts -->
							<div class="font-chooser">
								<p class="section-header">Select Fonts</p>
								<div class="spacer-20"></div>

								<!-- add radio buttons -->
								<?php
									$selected_font_family = Options::get_setting( 'font_family' );
									if ( '' == $selected_font_family ) {
										$selected_font_family = 1;
									}
								?>

								<label for="pwapp_edittheme_fontfamily">Font family</label>

								<select name="pwapp_edittheme_fontfamily" id="pwapp_edittheme_fontfamily">
									<?php foreach ( Themes_Config::$allowed_fonts as $key => $font_family ) : ?>
										<option value="<?php echo $key + 1; ?>" data-text='<span style="font-family:<?php echo $font_family; ?>"><?php echo $font_family; ?></span>'
											<?php
											if ( $selected_font_family == $key + 1 ) {
												echo 'selected';}
											?>
										></option>
									<?php endforeach; ?>
								</select>

								<div class="spacer-10"></div>

							</div>

							<div class="spacer-20"></div>
							<!-- choose font size -->
							<div class="font-chooser left">
								<?php
								$font_size = Options::get_setting( 'font_size' );
								if ( '' == $font_size ) {
									$font_size = 1;
								}
								?>
								<label for="pwapp_edittheme_fontsize">Font size</label>
								<div class="toggle-container">
								<?php foreach ( Themes_Config::$allowed_fonts_sizes as $key => $allowed_font_size ) : ?>
										<div class="toggle-button">
											<input type="radio" name="pwapp_edittheme_fontsize" id="pwapp_edittheme_fontsize_option_<?php echo $key + 1; ?>" autocomplete="off" value="<?php echo $allowed_font_size['size']; ?>"
												<?php
												if ( $font_size == $allowed_font_size['size'] ) {
													echo 'checked';}
												?>
											>
											<div class="font-size font-size-<?php echo strtolower( $allowed_font_size['label'] ); ?>" id="pwapp_edittheme_fontsize_option_<?php echo $key + 1; ?>"></div>
											<label for="pwapp_edittheme_fontsize_option_<?php echo $key + 1; ?>"><?php echo $allowed_font_size['label']; ?></label>
										</div>
									<?php endforeach; ?>
								</div>
							</div>
							<div class="notice notice-left right" style="max-width: 40%;">
								<span>
									The headlines, subtitles and paragraphs font sizes will be calculated depending on the paragraph font size. The 3 sizes have been carefully chosen with readibility in mind, based on our own research into the display of text content from large publishers such as the NY Times and other high-profile publishers.
								</span>
							</div>

							<div class="spacer-20"></div>
							<a href="javascript:void(0);" id="pwapp_edittheme_send_btn" class="btn green smaller" >Save</a>
						</form>
					</div>
					<div class="spacer-15"></div>
			<?php endif; ?>

			<div class="details branding">

				<h2 class="title">Customize Your App's Logo and Icon</h2>
				<div class="spacer-15"></div>
				<div class="grey-line"></div>
				<div class="spacer-20"></div>
				<p>You can also personalize your app by adding <strong>your own logo and icon</strong>. The logo will be displayed on the home page of your mobile web app, while the icon will be used when readers add your app to their homescreen.</p>
				<div class="spacer-20"></div>
				<?php
					$warning_message = '';
					$icon_filename   = Options::get_setting( 'icon' );

				if ( '' == $icon_filename ) {
					$warning_message = 'Upload an App Icon to take advantage of the Add To Home Screen functionality!';

				} elseif ( '' != $icon_filename && file_exists( PWAPP_FILES_UPLOADS_DIR . $icon_filename ) ) {
					foreach ( Uploads::$manifest_sizes as $manifest_size ) {
						if ( ! file_exists( PWAPP_FILES_UPLOADS_DIR . $manifest_size . $icon_filename ) ) {
							$warning_message = 'Progressive Web Apps 1.0 comes with new icons sizes for Add To Home Screen which requires you to reupload your App Icon.';
							break;
						}
					}
				}
				?>
				<div id="pwapp_editimages_warning" class="message-container warning" style="display:<?php echo ( '' != $warning_message ) ? 'block' : 'none'; ?>">
					<div class="wrapper">
						<div class="relative"><a class="close-x"></a></div>
						<span><?php echo $warning_message; ?></span>
					</div>
					<div class="spacer-10"></div>
				</div>
				<div class="left">
					<form name="pwapp_editimages_form" id="pwapp_editimages_form" action="<?php echo admin_url( 'admin-ajax.php' ); ?>?action=pwapp_editimages&type=upload" method="post" enctype="multipart/form-data">

						<?php
							$logo_path = Options::get_setting( 'logo' );

						if ( '' != $logo_path ) {

							if ( ! file_exists( PWAPP_FILES_UPLOADS_DIR . $logo_path ) ) {
								$logo_path = '';
							} else {
								$logo_path = PWAPP_FILES_UPLOADS_URL . $logo_path;
							}
						}

						?>

						<!-- upload logo field -->
						<div class="pwapp_editimages_uploadlogo" style="display: <?php echo  '' == $logo_path ? 'block' : 'none'; ?>;">

							<label for="pwapp_editimages_logo">Upload your app logo</label>

							<div class="custom-upload">

								<input type="file" id="pwapp_editimages_logo" name="pwapp_editimages_logo" />
								<div class="fake-file">
									<input type="text" id="fakefilelogo" disabled="disabled" />
									<a href="#" class="btn grey smaller">Browse</a>
								</div>


								<a href="javascript:void(0)" id="pwapp_editimages_logo_removenew" class="remove" style="display: none;"></a>
							</div>

							<!-- cancel upload logo button -->
							<div class="pwapp_editimages_changelogo_cancel cancel-link" style="display: none;">
								<a href="javascript:void(0);" class="cancel">cancel</a>
							</div>
							<div class="field-message error" id="error_logo_container"></div>

						</div>

						<!-- logo image -->
						<div class="pwapp_editimages_logocontainer display-logo" style="display: <?php echo '' != $logo_path ? 'block' : 'none'; ?>;">

							<label for="branding_logo">App logo</label>
							<div class="img" id="pwapp_editimages_currentlogo" style="background:url(<?php echo $logo_path; ?>); background-size:contain; background-repeat: no-repeat; background-position: center"></div>

							<!-- edit/delete logo links -->
							<a href="javascript:void(0);" class="pwapp_editimages_changelogo btn grey smaller edit">Change</a>
							<a href="#" class="pwapp_editimages_deletelogo smaller remove">remove</a>

						</div>

						<div class="spacer-20"></div>

						<?php
							$icon_path = Options::get_setting( 'icon' );

						if ( '' != $icon_path ) {

							if ( ! file_exists( PWAPP_FILES_UPLOADS_DIR . $icon_path ) ) {
								$icon_path = '';
							} else {
								$icon_path = PWAPP_FILES_UPLOADS_URL . $icon_path;
							}
						}
						?>

						<!-- upload icon field -->
						<div class="pwapp_editimages_uploadicon" style="display: <?php echo  '' == $icon_path ? 'block' : 'none'; ?>;">

							<label for="pwapp_editimages_icon">Upload your app icon</label>

							<div class="custom-upload">

								<input type="file" id="pwapp_editimages_icon" name="pwapp_editimages_icon" />
								<div class="fake-file">
									<input type="text" id="fakefileicon" disabled="disabled" />
									<a href="#" class="btn grey smaller">Browse</a>
								</div>

								<a href="javascript:void(0)" id="pwapp_editimages_icon_removenew" class="remove" style="display: none;"></a>
							</div>
							<!-- cancel upload icon button -->
							<div class="pwapp_editimages_changeicon_cancel cancel-link" style="display: none;">
								<a href="javascript:void(0);" class="cancel">cancel</a>
							</div>
							<div class="field-message error" id="error_icon_container"></div>

						</div>

						<!-- icon image -->
						<div class="pwapp_editimages_iconcontainer display-icon" style="display: <?php echo  '' != $icon_path ? 'block' : 'none'; ?>;;">

							<label for="branding_icon">App icon</label>
							<img src="<?php echo $icon_path; ?>" id="pwapp_editimages_currenticon" />

							<!-- edit/delete icon links -->
							<a href="javascript:void(0);" class="pwapp_editimages_changeicon btn grey smaller edit">Change</a>
							<a href="#" class="pwapp_editimages_deleteicon smaller remove">remove</a>
						</div>

						<div class="spacer-20"></div>

						<a href="javascript:void(0);" id="pwapp_editimages_send_btn" class="btn green smaller">Save</a>

					</form>
				</div>

				<div class="notice notice-left right" style="max-width: 220px;">
					<span>
						Add your logo in a .png format with a transparent background. This will be displayed on the cover of your app.<br /><br />
						Your icon should be square with a recommended size of 512 x 512 px. This will be displayed when the app will be added to the homescreen.<br /><br />
						The file size for uploaded images should not exceed 1MB.
					</span>
				</div>
				<div class="spacer-0"></div>
			</div>
			<div class="spacer-15"></div>
			<div class="details branding">
				<h2 class="title">Add to Home Screen</h2>
				<div class="spacer-15"></div>
				<div class="grey-line"></div>
				<div class="spacer-20"></div>
				<p>In order for your users to be prompted to add the app to their home screen you must add a service worker to the root of your domain.</p>
				<div class="spacer-10"></div>
				<p>Move the 'sw.js' file which is located in the 'progressive-web-apps' plugin directory to the root of your domain '/' using FTP.</p>
				<div class="spacer-10"></div>
				<p>Once you have moved the file to your root, check the box bellow and click 'save'. For more details visit the <a href="https://docs.wpmobilepack.com/wp-mobile-pack-free/look-and-feel.html" target="_blank">support page.</a></p>
				<div class="spacer-30"></div>

				<form name="pwapp_service_worker_form" id="pwapp_service_worker_form" class="left" action="<?php echo admin_url( 'admin-ajax.php' ); ?>?action=pwapp_settings_save" method="post" style="min-width: 300px;">
					<?php $installed = Options::get_setting( 'service_worker_installed' ); ?>
					<div class="left">
						<input type="hidden" name="pwapp_option_service_worker_installed" id="pwapp_option_service_worker_installed" value="<?php echo $installed; ?>"/>
						<input type="checkbox" name="pwapp_service_worker_installed_check" id="pwapp_service_worker_installed_check" value="1"
						<?php
						if ( 1 == $installed ) {
							echo 'checked';}
						?>
						 />
						<label for="pwapp_service_worker_installed_check"> Service Worker Installed </label>
						<div class="spacer-40"></div>
						<a href="javascript:void(0);" id="pwapp_service_worker_send_btn" class="btn green smaller">Save</a>
					</div>
				</form>
				<div class="spacer-0"></div>
			</div>
			<div class="spacer-15"></div>

		</div>

		<div class="right-side">
			<!-- add feedback form -->
			<?php include_once( PWAPP_PLUGIN_PATH . 'admin/sections/feedback.php' ); ?>
		</div>
	</div>
</div>

<script type="text/javascript">
	if (window.PWAPPJSInterface && window.PWAPPJSInterface != null){
		jQuery(document).ready(function(){

			window.PWAPPJSInterface.add("UI_customizetheme","PWAPP_EDIT_THEME",{'DOMDoc':window.document}, window);
			window.PWAPPJSInterface.add("UI_editimages","PWAPP_EDIT_IMAGES",{'DOMDoc':window.document}, window);
			window.PWAPPJSInterface.add("UI_service_worker","PWAPP_SERVICE_WORKER",{'DOMDoc':window.document}, window);

		});
	}
</script>
