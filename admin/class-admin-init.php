<?php

if ( ! class_exists( 'PWAPP_Admin' ) ) {
    require_once(PWAPP_PLUGIN_PATH.'admin/class-admin.php');
}

if ( ! class_exists( 'PWAPP_Themes_Config' ) ) {
    require_once(PWAPP_PLUGIN_PATH.'inc/class-pwapp-themes-config.php');
}

if ( ! class_exists( 'PWAPP_Admin_Init' ) ) {

    /**
     * PWAPP_Admin_Init class for initializing the admin area for the plugin
     *
     * Displays menu & loads static files for each admin page.
     */
    class PWAPP_Admin_Init
    {

        /**
         * Class constructor
         *
         * Init admin menu and enqueue general Javascript & CSS files
         */
        public function __construct()
        {
            // enqueue css and javascript for the admin area
            add_action('admin_enqueue_scripts', array(&$this, 'pwapp_admin_enqueue_scripts'));

            // add admin menu hook
            add_action('admin_menu', array(&$this, 'pwapp_admin_menu'));
        }


        /**
         *
         * Build the admin menu and add all admin pages of the plugin
         *
         */
        public function pwapp_admin_menu()
        {

            // init admin object
            $PWAPPAdmin = new PWAPP_Admin();

            // add menu and submenu hooks
            add_menu_page(
				PWAPP_PLUGIN_NAME,
				PWAPP_PLUGIN_NAME,
				'manage_options',
				'pwapp-theme',
				array( &$PWAPPAdmin, 'theme' ),
				WP_PLUGIN_URL . '/'.PWAPP_DOMAIN.'/admin/images/appticles-logo.png'
			);
        }


        /**
         *
         * The pwapp_admin_enqueue_scripts is used to enqueue scripts and styles for the admin area.
         * The scripts and styles loaded by this method are used on all admin pages.
         *
         */
        public function pwapp_admin_enqueue_scripts()
        {
            // enqueue styles
            wp_enqueue_style(PWAPP_Options::$prefix.'css_general', plugins_url(PWAPP_DOMAIN.'/admin/css/general.css'), array(), PWAPP_VERSION);

            // enqueue scripts
            $blog_version = floatval(get_bloginfo('version'));
            $dependencies = array('jquery-core', 'jquery-migrate');

            // enqueue scripts
            wp_enqueue_script(PWAPP_Options::$prefix.'js_validate', plugins_url(PWAPP_DOMAIN.'/admin/js/UI.Interface/Lib/jquery.validate.min.js'), $dependencies, '1.11.1');
            wp_enqueue_script(PWAPP_Options::$prefix.'js_validate_additional', plugins_url(PWAPP_DOMAIN.'/admin/js/UI.Interface/Lib/validate-additional-methods.min.js'), $dependencies, '1.11.1');
            wp_enqueue_script(PWAPP_Options::$prefix.'js_loader', plugins_url(PWAPP_DOMAIN.'/admin/js/UI.Interface/Loader.min.js'), $dependencies, PWAPP_VERSION);
            wp_enqueue_script(PWAPP_Options::$prefix.'js_ajax_upload', plugins_url(PWAPP_DOMAIN.'/admin/js/UI.Interface/AjaxUpload.min.js'), $dependencies, PWAPP_VERSION);
            wp_enqueue_script(PWAPP_Options::$prefix.'js_interface', plugins_url(PWAPP_DOMAIN.'/admin/js/UI.Interface/JSInterface.min.js'), $dependencies, PWAPP_VERSION);

            wp_enqueue_script(PWAPP_Options::$prefix.'js_feedback', plugins_url(PWAPP_DOMAIN.'/admin/js/UI.Modules/Feedback/PWAPP_SEND_FEEDBACK.min.js'), array(), PWAPP_VERSION);

            // activate custom select
			wp_enqueue_style(PWAPP_Options::$prefix.'css_select_box_it', plugins_url(PWAPP_DOMAIN.'/admin/css/jquery.selectBoxIt.css'), array(), '3.8.1');
			wp_enqueue_script(PWAPP_Options::$prefix.'js_select_box_it', plugins_url(PWAPP_DOMAIN.'/admin/js/UI.Interface/Lib/jquery.selectBoxIt.min.js'), array('jquery','jquery-ui-core', 'jquery-ui-widget'), '3.8.1');

			$allowed_fonts = PWAPP_Themes_Config::$allowed_fonts;
			foreach ($allowed_fonts as $key => $font_family)
				wp_enqueue_style(PWAPP_Options::$prefix.'css_font'.($key+1), plugins_url(PWAPP_DOMAIN.'/frontend/fonts/font-'.($key+1).'.css'), array(), PWAPP_VERSION);

            wp_enqueue_style(PWAPP_Options::$prefix.'css_magnific_popup', plugins_url(PWAPP_DOMAIN.'/admin/css/magnific-popup.css'), array(), '0.9.9');
            wp_enqueue_script(PWAPP_Options::$prefix.'js_magnific_popup', plugins_url(PWAPP_DOMAIN.'/admin/js/UI.Interface/Lib/jquery.magnific-popup.min.js'), array(), '0.9.9');

            wp_enqueue_style('wp-color-picker');

            wp_enqueue_script(PWAPP_Options::$prefix.'js_theming_edittheme', plugins_url(PWAPP_DOMAIN.'/admin/js/UI.Modules/Theming/PWAPP_EDIT_THEME.min.js'), array('wp-color-picker'), PWAPP_VERSION);
            wp_enqueue_script(PWAPP_Options::$prefix.'js_theming_editimages', plugins_url(PWAPP_DOMAIN.'/admin/js/UI.Modules/Theming/PWAPP_EDIT_IMAGES.min.js'), array(), PWAPP_VERSION);
            wp_enqueue_script(PWAPP_Options::$prefix.'js_theming_editcover', plugins_url(PWAPP_DOMAIN.'/admin/js/UI.Modules/Theming/PWAPP_EDIT_COVER.min.js'), array(), PWAPP_VERSION);
        }
    }
}
