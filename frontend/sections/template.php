<?php
use  PWAPP\Frontend\Application;
use PWAPP\Inc\Options;

global $pwapp;

$frontend = new Application();

$app_settings = $frontend->load_app_settings();

$site_name = get_bloginfo( 'name' );

$site_url = get_site_url();

$theme_path    = plugins_url() . '/' . PWAPP_DOMAIN . '/frontend/themes/app2/';
$frontend_path = plugins_url() . '/' . PWAPP_DOMAIN . '/frontend/';

$website_url_data = array(
	home_url(),
	parse_url( home_url(), PHP_URL_QUERY ) ? '&' : '?',
	Options::$prefix . 'theme_mode=desktop',
);

$config = array(
	'export'          => array(
		'categories' => $site_url . '/wp-json/pwapp/categories',
		'posts'      => $site_url . '/wp-json/wp/v2/posts',
		'pages'      => $site_url . '/wp-json/wp/v2/pages?_embed=media',
		'comments'   => $site_url . '/wp-json/wp/v2/comments',
		'media'      => $site_url . '/wp-json/wp/v2/media',
	),
	'translate'       => array(
		'path' => $site_url . '/wp-json/pwapp/language',
	),
	'socialMedia'     => array(
		'facebook' => $app_settings['enable_facebook'],
		'twitter'  => $app_settings['enable_twitter'],
		'google'   => $app_settings['enable_google'],
	),
	'websiteUrl'  => join( '', $website_url_data ),
	'logo'        => $app_settings['logo'],
	'commentsEnabled' => 0,
	'offlineMode' => 0,
);

$config_json = wp_json_encode( $config );

?>


<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,minimum-scale=1,user-scalable=no" />
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<meta name="apple-touch-fullscreen" content="yes" />
	<meta name="apple-mobile-web-app-status-bar-style" content="black" />
	<meta name="mobile-web-app-capable" content="yes" />
	<meta name="theme-color" content="#a333c8">
	<link rel="manifest" href="<?php echo  $site_url . '/wp-json/pwapp/manifest/'; ?>">
	<?php if ( '' != $app_settings['icon'] ) : ?>
		<link rel="apple-touch-icon" href="<?php echo $app_settings['icon']; ?>" />
	<?php endif; ?>
	<link rel="shortcut icon" href="/favicon.ico">

	<title><?php echo esc_html( $site_name ); ?></title>

	<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.2.12/semantic.min.css">
	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.6.0/slick.min.css" />
	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.6.0/slick-theme.min.css"/>

	<link rel="stylesheet" href="<?php echo $frontend_path . 'fonts/font-' . $app_settings['font_family'] . '.css?date=20160106'; ?>" type="text/css" />

	<?php if ($app_settings['theme_timestamp'] === ''): ?>
		<link href="<?php echo $theme_path; ?>css/phone-v2.css" rel="stylesheet" />
	<?php else: ?>
		<link href="<?php echo PWAPP_FILES_UPLOADS_URL.'theme-v2-'.$app_settings['theme_timestamp'].'.css';?>" rel="stylesheet" />
	<?php endif; ?>

	<script type="text/javascript" pagespeed_no_defer="">
		window.__INITIAL_CONFIG__ =  <?php echo $config_json; ?>
	</script>

	<?php if ( '1' === $app_settings['service_worker_installed'] ) : ?>
		<script>
			if ('serviceWorker' in navigator) {
				navigator.serviceWorker.register('/sw.js');
			}
		</script>
	<?php endif; ?>
</head>

<body>
	<noscript>You need to enable JavaScript to run this app.</noscript>
	<div id="root" style="height:100%"></div>
	<script type="text/javascript" src="<?php echo $theme_path; ?>js/main.35aa4542.js"></script>
</body>

</html>
