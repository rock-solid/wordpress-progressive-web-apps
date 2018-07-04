<?php
use PWAPP\Core\PWAPP;
use PWAPP\Inc\Api;
use PWAPP\Inc\Options;

	$pwapp_api        = new Api();
	$pwapp_texts_json = $pwapp_api->get_app_texts_json( get_locale(), 'list' );

	$pwapp_footer_text = 'Switch to mobile version';
if ( false !== $pwapp_texts_json && isset( $pwapp_texts_json['APP_TEXTS']['LINKS'] ) && isset( $pwapp_texts_json['APP_TEXTS']['LINKS']['VISIT_APP'] ) ) {
	$pwapp_footer_text = $pwapp_texts_json['APP_TEXTS']['LINKS']['VISIT_APP'];
}
?>
	<div id="show-mobile" style="width:100%; text-align: center;">
		<a href="
		<?php
		echo home_url();
		echo parse_url( home_url(), PHP_URL_QUERY ) ? '&' : '?';
		echo Options::$prefix;
		?>
		theme_mode=mobile" title="<?php echo $pwapp_footer_text; ?>"><?php echo $pwapp_footer_text; ?></a>
	</div>
