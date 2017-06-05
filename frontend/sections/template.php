<?php
	$app_settings = PWAPP_Application::load_app_settings();

	$frontend_path = plugins_url()."/".PWAPP_DOMAIN."/frontend/";
	$theme_path = $frontend_path."themes/app2/";

	// check fonts
	$loaded_fonts = array(
		$app_settings['font_headlines'],
		$app_settings['font_subtitles'],
		$app_settings['font_paragraphs'],
	);

	$loaded_fonts = array_unique($loaded_fonts);

	// check if locale file exists
	$texts_json_exists = PWAPP_Application::check_language_file(get_locale());

	if ($texts_json_exists === false) {
		echo "ERROR, unable to load language file. Please check the '".PWAPP_DOMAIN."/frontend/locales/' folder.";
	}

	if (!class_exists('PWAPP_Themes_Config')) {
		require_once(PWAPP_PLUGIN_PATH . 'inc/class-pwapp-themes-config.php');
	}

	$background_color = PWAPP_Themes_Config::get_manifest_background($app_settings['color_scheme']);
?>
<!DOCTYPE HTML>
<html manifest="" <?php language_attributes(); ?>>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-touch-fullscreen" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black" />
    <link rel="apple-touch-icon-precomposed" href="" />
    <meta name="mobile-web-app-capable" content="yes" />
    <link rel="manifest" href="<?php echo $frontend_path."export/content.php?content=androidmanifest";?>" />

	<?php if ($background_color !== false) :?>
		<meta name="theme-color" content="<?php echo $background_color; ?>">
	<?php endif;?>

    <?php if ($app_settings['icon'] != ''): // icon path for Firefox ?>
        <link rel="shortcut icon" href="<?php echo $app_settings['icon'];?>"/>
    <?php endif;?>

    <title><?php echo get_bloginfo("name");?></title>
	<noscript>Your browser does not support JavaScript!</noscript>
    <style type="text/css">
        /**
        * Example of an initial loading indicator.
        * It is recommended to keep this as minimal as possible to provide instant feedback
        * while other resources are still being loaded for the first time
        */
        html, body {
            height: 100%;
            width: 100%;
            margin: 0;
            padding: 0;
            background-color: #e5e8e3;
        }

        #appLoadingIndicator {
            position: absolute;
            top: 50%;
            margin-top: -8px;
            text-align: center;
            width: 100%;
            height: 16px;
            -webkit-animation-name: appLoadingIndicator;
            -webkit-animation-duration: 0.5s;
            -webkit-animation-iteration-count: infinite;
            -webkit-animation-direction: linear;
            animation-name: appLoadingIndicator;
            animation-duration: 0.5s;
            animation-iteration-count: infinite;
            animation-direction: linear;
        }

        #appLoadingIndicator > * {
            background-color: #c6cdbe;
            display: inline-block;
            height: 16px;
            width: 16px;
            -webkit-border-radius: 8px;
            -moz-border-radius: 8px;
            border-radius: 8px;
            margin: 0 2px;
            opacity: 0.8;
        }

        @-webkit-keyframes appLoadingIndicator{
            0% {
                opacity: 0.8
            }
            50% {
                opacity: 0
            }
            100% {
                opacity: 0.8
            }
        }

        @keyframes appLoadingIndicator{
            0% {
                opacity: 0.8
            }
            50% {
                opacity: 0
            }
            100% {
                opacity: 0.8
            }
        }
    </style>

    <script type="text/javascript" pagespeed_no_defer="">
        var appticles = {
            exportPath: "<?php echo $frontend_path."export/";?>",

            <?php if ($app_settings['display_website_link']):?>
                websiteUrl: '<?php echo home_url(); echo parse_url(home_url(), PHP_URL_QUERY) ? '&' : '?'; echo PWAPP_Options::$prefix; ?>theme_mode=desktop',
            <?php endif;?>

            logo: "<?php echo $app_settings['logo'];?>",
            icon: "<?php echo $app_settings['icon'];?>",
            defaultCover: "<?php echo $app_settings['cover'] != '' ? $app_settings['cover'] : $frontend_path."images/pattern-".rand(1, 6).".jpg";;?>",
            userCover: <?php echo intval($app_settings['cover'] != '');?>,
            hasFacebook: <?php echo $app_settings['enable_facebook'];?>,
            hasTwitter: <?php echo $app_settings['enable_twitter'];?>,
            hasGoogle: <?php echo $app_settings['enable_google'];?>,
            commentsToken: "<?php echo $app_settings['comments_token'];?>",
            articlesPerCard: <?php if ($app_settings['posts_per_page'] == 'single') echo 1; elseif ($app_settings['posts_per_page'] == 'double') echo 2; else echo '"auto"' ;?>
        }
    </script>


    <!-- core -->
    <?php if ($app_settings['theme_timestamp'] != ''):?>
        <link rel="stylesheet" href="<?php echo PWAPP_FILES_UPLOADS_URL.'theme-'.$app_settings['theme_timestamp'].'.css';?>" type="text/css" />
    <?php else: ?>
        <link rel="stylesheet" href="<?php echo $theme_path;?>css/phone.css?date=20160420" type="text/css" />
    <?php endif;?>

    <!-- custom fonts -->
    <?php foreach ($loaded_fonts as $font_no):?>
        <link rel="stylesheet" href="<?php echo $frontend_path."fonts/font-".$font_no.".css?date=20160106";?>" type="text/css">
    <?php endforeach;?>

    <script src="<?php echo $frontend_path.'export/content.php?content=apptexts&locale='.get_locale();?>" type="text/javascript"></script>
    <script src="<?php echo $theme_path;?>js/app.js?date=20160525" type="text/javascript"></script>
</head>
<body>
<div id="appLoadingIndicator">
    <div></div>
    <div></div>
    <div></div>
</div>
</body>
</html>
