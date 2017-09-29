<?php

if (class_exists('PWAPP_Core')):

    // The mobile web app paths will be set relative to the home url and will deactivate the desktop theme
    $mobile_url = home_url();
    $mobile_url .= parse_url(home_url(), PHP_URL_QUERY) ? '&' : '?';
    $mobile_url .= PWAPP_Options::$prefix.'theme_mode=mobile';

    if (is_single() || is_page() || is_category()){

        if (is_single()){

            // Read inactive categories
            $inactive_categories = PWAPP_Options::get_setting('inactive_categories');

            // Read post categories
            $post_categories = get_the_category();

            // Check if the post belongs to a visible category
            $visible_category = null;

            foreach ($post_categories as $post_category){

                if (!in_array($post_category->cat_ID, $inactive_categories)) {
                    $mobile_url .= "/#article/".get_the_ID();
                    break;
                }
            }

        } elseif (is_page()) {

            $page_id = get_the_ID();
            $inactive_pages = PWAPP_Options::get_setting('inactive_pages');

            if (!in_array($page_id, $inactive_pages)){
                $mobile_url .= "/#page/".$page_id;
            }

        } elseif (is_category()) {

            $category_name = single_cat_title("", false);

            if ($category_name){

                $category_obj = get_term_by('name', $category_name, 'category');

                if ($category_obj && isset($category_obj->slug) && isset($category_obj->term_id) && is_numeric($category_obj->term_id)){

                    $category_id = $category_obj->term_id;

                    // check if the category is active / inactive before displaying it
                    $inactive_categories = PWAPP_Options::get_setting('inactive_categories');

                    if (!in_array($category_id, $inactive_categories)){
                        $mobile_url .= "/#category/".$category_obj->slug.'/'.$category_id;
                    }
                }
            }
        }
    }

    // Load icon from the local settings and folder
    $app_icon_path = '';

    if (class_exists('PWAPP_Uploads')) {

        $app_icon_path = PWAPP_Options::get_setting('icon');

        if ($app_icon_path != '') {

            $PWAPP_Uploads = new PWAPP_Uploads();
            $app_icon_path = $PWAPP_Uploads->get_file_url($app_icon_path);
        }
    }

    // Load 'Open' app button translation
    if ( ! class_exists( 'PWAPP_Export' ) ) {
        require_once(PWAPP_PLUGIN_PATH.'frontend/export/class-export.php');
    }

    $pwapp_export = new PWAPP_Export();
    $pwapp_texts_json = $pwapp_export->load_language(get_locale(), 'list');

    $open_btn_text = 'Open';
    if ($pwapp_texts_json !== false && isset($pwapp_texts_json['APP_TEXTS']['LINKS']) && isset($pwapp_texts_json['APP_TEXTS']['LINKS']['OPEN_APP'])){
        $open_btn_text = $pwapp_texts_json['APP_TEXTS']['LINKS']['OPEN_APP'];
	}

	$app_name = get_bloginfo("name");
	if (strlen($app_name) > 20) {
		$app_name = substr($app_name, 0, 20).' ... ';
	}

	$app_url = home_url();
	if (strlen($app_url) > 20) {
		$app_url = substr($app_url, 0, 20).' ... ';
	}

	$is_secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;

?>

	<link href="<?php echo plugins_url()."/".PWAPP_DOMAIN;?>/frontend/sections/notification-banner/lib/noty.css" rel="stylesheet">
	<script src="<?php echo plugins_url()."/".PWAPP_DOMAIN;?>/frontend/sections/notification-banner/lib/noty.min.js" type="text/javascript" pagespeed_no_defer=""></script>
	<script src="<?php echo plugins_url()."/".PWAPP_DOMAIN;?>/frontend/sections/notification-banner/notification-banner.js" type="text/javascript" pagespeed_no_defer=""></script>

    <script type="text/javascript" pagespeed_no_defer="">
		jQuery(document).ready(function(){

			const pwappIconPath = "<?php echo esc_attr($app_icon_path);?>";

			PWAPPAppBanner.message =
				(pwappIconPath !== '' ? '<img src="<?php echo esc_attr($app_icon_path);?>" />' : '') +
				'<p><?php echo $app_name;?><br/> ' +
				'<span><?php echo $app_url;?></span></p>' +
				'<a href="<?php echo $mobile_url;?>"><span><?php echo $open_btn_text;?></span></a>';

			PWAPPAppBanner.cookiePrefix = "<?php echo PWAPP_Cookie::$prefix;?>";
			PWAPPAppBanner.isSecure = <?php echo $is_secure ? "true" : "false";?>;
		});
	</script>

<?php endif; ?>
