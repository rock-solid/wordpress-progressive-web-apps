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
         * The menu item's title
         * @var string
         */
        private static $submenu_title = PWAPP_PLUGIN_NAME;

		/**
         * Submenu pages arrays. Each item has the following properties:
         *
         * - page_title = The page's and menu's title
         * - capability = GET parameter to be sent to the admin.php page
         * - function = The admin function that display the page (from class-admin.php)
         * - enqueue_hook = (optional) The method that adds the Javascript & CSS files required by each page
         *
         * @var array
         */
        private static $submenu_pages = array(
			array(
                'page_title' => "App Themes",
                'capability' => 'pwapp-options',
                'function' => 'themes',
                'enqueue_hook' => ''
            ),
            array(
                'page_title' => "Look & Feel",
                'capability' => 'pwapp-options-theme-settings',
                'function' => 'theme_settings',
                'enqueue_hook' => 'pwapp_admin_load_theme_settings_js'
            )
        );

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

			$menu_name = 'pwapp-options';

			add_menu_page(
				self::$submenu_title,
			 	self::$submenu_title,
			  	'manage_options',
			   	$menu_name,
			    '',
				WP_PLUGIN_URL . '/'.PWAPP_DOMAIN.'/admin/images/appticles-logo.png'
			);

            foreach (self::$submenu_pages as $submenu_item) {

                // add page in the submenu
                $submenu_page = add_submenu_page($menu_name, $submenu_item['page_title'], $submenu_item['page_title'], 'manage_options', $submenu_item['capability'], array(&$PWAPPAdmin, $submenu_item['function']));

                // enqueue js files for each subpage
                if (isset($submenu_item['enqueue_hook']) && $submenu_item['enqueue_hook'] != '') {
                    add_action('load-' . $submenu_page, array(&$this, $submenu_item['enqueue_hook']));
                }
            }
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

        }

		/**
         *
         * Load specific javascript files for the admin Look & Feel submenu page
         *
         */
        public function pwapp_admin_load_theme_settings_js()
        {

            // activate custom select
			wp_enqueue_style(PWAPP_Options::$prefix.'css_select_box_it', plugins_url(PWAPP_DOMAIN.'/admin/css/jquery.selectBoxIt.css'), array(), '3.8.1');
			wp_enqueue_script(PWAPP_Options::$prefix.'js_select_box_it', plugins_url(PWAPP_DOMAIN.'/admin/js/UI.Interface/Lib/jquery.selectBoxIt.min.js'), array('jquery','jquery-ui-core', 'jquery-ui-widget'), '3.8.1');

			$allowed_fonts = PWAPP_Themes_Config::$allowed_fonts;
			foreach ($allowed_fonts as $key => $font_family)
				wp_enqueue_style(PWAPP_Options::$prefix.'css_font'.($key+1), plugins_url(PWAPP_DOMAIN.'/frontend/fonts/font-'.($key+1).'.css'), array(), PWAPP_VERSION);

            wp_enqueue_style('wp-color-picker');

            wp_enqueue_script(PWAPP_Options::$prefix.'js_theming_edittheme', plugins_url(PWAPP_DOMAIN.'/admin/js/UI.Modules/Theming/PWAPP_EDIT_THEME.min.js'), array('wp-color-picker'), PWAPP_VERSION);
            wp_enqueue_script(PWAPP_Options::$prefix.'js_theming_editimages', plugins_url(PWAPP_DOMAIN.'/admin/js/UI.Modules/Theming/PWAPP_EDIT_IMAGES.min.js'), array(), PWAPP_VERSION);
            wp_enqueue_script(PWAPP_Options::$prefix.'js_theming_editcover', plugins_url(PWAPP_DOMAIN.'/admin/js/UI.Modules/Theming/PWAPP_EDIT_COVER.min.js'), array(), PWAPP_VERSION);
        }
    }
}
