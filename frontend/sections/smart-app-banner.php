<?php
use PWAPP\Core\PWAPP;
use PWAPP\Inc\Options;
use PWAPP\Inc\Uploads;
use PWAPP\Inc\Api;
use PWAPP\Inc\Cookie;




	// The mobile web app paths will be set relative to the home url and will deactivate the desktop theme
	$mobile_url  = home_url();
	$mobile_url .= parse_url( home_url(), PHP_URL_QUERY ) ? '&' : '?';
	$mobile_url .= Options::$prefix . 'theme_mode=mobile';

if ( is_single() || is_page() || is_category() ) {

	if ( is_single() ) {
		$post_obj    = get_post();
		$mobile_url .= '/#post/' . $post_obj->post_name . '/' . $post_obj->ID;

	} elseif ( is_page() ) {

		$page_obj    = get_post();
		$mobile_url .= '/#page/' . $page_obj->post_name . '/' . $page_obj->ID;


	} elseif ( is_category() ) {

		$category_name = single_cat_title( '', false );

		if ( $category_name ) {

			$category_obj = get_term_by( 'name', $category_name, 'category' );

			if ( $category_obj && isset( $category_obj->slug ) && isset( $category_obj->term_id ) && is_numeric( $category_obj->term_id ) ) {

				$mobile_url .= '/#category/' . $category_obj->slug . '/' . $category_obj->term_id;
			}
		}
	}
}

$app_icon_path = Options::get_setting( 'icon' );

if ( '' != $app_icon_path ) {

	$pwapp_uploads = new Uploads();
	$app_icon_path = $pwapp_uploads->get_file_url( $app_icon_path );
}


$pwapp_api        = new Api();
$pwapp_texts_json = $pwapp_api->get_app_texts_json( get_locale(), 'list' );

$open_btn_text = 'Open';

if ( false !== $pwapp_texts_json && isset( $pwapp_texts_json['APP_TEXTS']['LINKS'] ) && isset( $pwapp_texts_json['APP_TEXTS']['LINKS']['OPEN_APP'] ) ) {
	$open_btn_text = $pwapp_texts_json['APP_TEXTS']['LINKS']['OPEN_APP'];
}

	$app_name = get_bloginfo( 'name' );
if ( strlen( $app_name ) > 20 ) {
	$app_name = substr( $app_name, 0, 20 ) . ' ... ';
}

	$app_url = home_url();
if ( strlen( $app_url ) > 20 ) {
	$app_url = substr( $app_url, 0, 20 ) . ' ... ';
}

	$is_secure = ( ! empty( $_SERVER['HTTPS'] ) && 'off' !== $_SERVER['HTTPS'] ) || 443 == $_SERVER['SERVER_PORT'];

?>

	<link href="<?php echo plugins_url() . '/' . PWAPP_DOMAIN; ?>/frontend/sections/notification-banner/lib/noty.css" rel="stylesheet">
	<script src="<?php echo plugins_url() . '/' . PWAPP_DOMAIN; ?>/frontend/sections/notification-banner/lib/noty.min.js" type="text/javascript" pagespeed_no_defer=""></script>
	<script src="<?php echo plugins_url() . '/' . PWAPP_DOMAIN; ?>/frontend/sections/notification-banner/notification-banner.js" type="text/javascript" pagespeed_no_defer=""></script>

	<script type="text/javascript" pagespeed_no_defer="">
		jQuery(document).ready(function(){

			const pwappIconPath = "<?php echo esc_attr( $app_icon_path ); ?>";

			PWAPPAppBanner.message =
				(pwappIconPath !== '' ? '<img src="<?php echo esc_attr( $app_icon_path ); ?>" />' : '') +
				'<p><?php echo $app_name; ?><br/> ' +
				'<span><?php echo $app_url; ?></span></p>' +
				'<a href="<?php echo $mobile_url; ?>"><span><?php echo $open_btn_text; ?></span></a>';

			PWAPPAppBanner.cookiePrefix = "<?php echo Cookie::$prefix; ?>";
			PWAPPAppBanner.isSecure = <?php echo $is_secure ? 'true' : 'false'; ?>;
		});
	</script>


