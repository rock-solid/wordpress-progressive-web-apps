<?php

if (!class_exists('PWAPP_Application')) {

    /**
     *
     * PWAPP_Application
     *
     * Main class for managing frontend apps
     *
     */
    class PWAPP_Application {


        /**
         * Class constructor
         */
        public function __construct()
        {
            $this->check_load();
        }

        /**
         *
         * Create a cookie management object and return it
         *
         * @return object
         *
         */
        protected function get_cookie_manager()
        {
            return new PWAPP_Cookie();
        }

        /**
         *
         * Method that checks if we can load the mobile web application theme.
         *
         * The theme is loaded if ALL of the following conditions are met:
         *
         * - the user comes from a supported mobile device and browser
         * - the user has not deactivated the view of the mobile theme by switching to desktop mode
         * - the display mode of the app is set to 'normal' or is set to 'preview' and an admin is logged in
         *
         */
        public function check_load()
        {

            // Set app as visible by default
            $visible_app = true;

            // Check if the display mode is set to 'normal' or 'preview' and the admin is logged in
            if (!$this->check_display_mode()) {
                $visible_app = false;
            }

            // Assume the app will not be loaded
            $load_app = false;

            if ($visible_app) {

                // Check if the load app cookie is 1 or the user came from a mobile device
                $cookie_manager = $this->get_cookie_manager();
                $load_app_cookie = $cookie_manager->get_cookie('load_app');

                // If the load_app cookie is not set, verify the device
                if ($load_app_cookie === null) {
                    $load_app = $this->check_device();

                } elseif ($load_app_cookie == 1) {

                    // The cookie was already set for the device, so we can load the app
                    $load_app = true;
                }
            }

            // If we need to add the rel=alternate links in the header
            $show_alternate = true;

            // We have a mobile device and the app is visible, so we can load the app
            if ($load_app) {

                // Check if the user deactivated the app display
                $desktop_mode = $this->check_desktop_mode();

                if ($desktop_mode == false) {

                    // We're loading the mobile web app, so we don't need the rel=alternate links
                    $show_alternate = false;
                    $this->load_app();

                } else {

                    // Add hook in header (for smart app banner)
                    add_action('wp_head', array(&$this, 'show_smart_app_banner'));

                    // Add hook in footer to show the switch to mobile link
                    add_action('wp_footer', array(&$this,'show_mobile_link'));
                }
            }

            // Add hook in header (for rel=alternate)
            if ($show_alternate){
                add_action('wp_head', array(&$this, 'show_rel'));
            }
        }

        /**
         *
         * Check if the app display is enabled
         *
         * Returns true if display mode is "normal" (enabled for all mobile users) or
         * if display mode is "preview" and an admin is logged in.
         *
         * @return bool
         *
         */
        protected function check_display_mode()
        {

            $display_mode = PWAPP_Options::get_setting('display_mode');

            if ($display_mode == 'normal')
                return true;

            elseif ($display_mode == 'preview') {

                if (is_user_logged_in() && current_user_can('create_users'))
                    return true;
            }

            return false;
        }


        /**
         *
         * Call the mobile detection method to verify if we have a supported device
         *
         * @return bool
         *
         */
        protected function check_device(){

            if ( ! class_exists( 'PWAPP_Detect' ) ) {
                require_once(PWAPP_PLUGIN_PATH.'frontend/class-detect.php');
            }

            $WMobileDetect = new PWAPP_Detect();
            return $WMobileDetect->detect_device();
        }


        /**
         *
         * Check if the user selected to view the desktop mode or we can display the app.
         *
         * The GET/COOKIE "theme_mode" can have two values: 'desktop' or 'mobile'.
         *
         * - Desktop mode can be activated from the app by selecting to return to desktop view.
         * - Mobile mode can be reactivated from the footer of the website or smart app banner.
         *
         * @return bool
         *
         */
        protected function check_desktop_mode()
        {

            $desktop_mode = false;

            $cookie_manager = $this->get_cookie_manager();
            $param_name = PWAPP_Options::$prefix.'theme_mode';

            if (isset($_GET[$param_name]) && is_string($_GET[$param_name])){

                $theme_mode = $_GET[$param_name];

                if ($theme_mode == "desktop" || $theme_mode == "mobile"){
                    $cookie_manager->set_cookie('theme_mode', $theme_mode, 3600*30*24);
                }

                if ($theme_mode == "desktop")
                    $desktop_mode = true;

            } else {

                $theme_mode_cookie = $cookie_manager->get_cookie('theme_mode');

                if ($theme_mode_cookie){
                    if ($theme_mode_cookie == "desktop")
                        $desktop_mode = true;
                }
            }

            return $desktop_mode;
        }


        /**
         *
         * Method that loads the mobile web application theme.
         *
         * The theme url and theme name from the WP installation are overwritten by the settings below.
         */
        public function load_app()
        {
            add_filter("stylesheet", array(&$this, "app_theme"), 11);
            add_filter("template", array(&$this, "app_theme"), 11);

            add_filter('theme_root', array( &$this, 'app_theme_root' ), 11);
            add_filter('theme_root_uri', array( &$this, 'app_theme_root' ), 11);
        }


        /**
         * Return the theme name
         */
        public function app_theme()
        {
            return 'app2';
        }


        /**
         * Return path to the mobile themes folder
         */
        public function app_theme_root()
        {
            return PWAPP_PLUGIN_PATH . 'frontend/themes';
        }



        /**
         *
         * Method used to display a rel=alternate link in the header of the desktop theme
         *
         * This method is called from check_load()
         *
         */
        public function show_rel()
        {
            include(PWAPP_PLUGIN_PATH.'frontend/sections/show-rel.php');
        }

        /**
         *
         * Method used to include a smart app banner in the header of the desktop theme,
         * when the mobile theme is disabled.
         *
         * This method is called from check_load()
         *
         */
        public function show_smart_app_banner()
        {
            include(PWAPP_PLUGIN_PATH.'frontend/sections/smart-app-banner.php');
        }

        /**
         *
         * Method used to display a box on the footer of the theme
         *
         * This method is called from check_load()
         * The box contains a link that sets the cookie and loads the app
         *
         */
        public function show_mobile_link()
        {
            include(PWAPP_PLUGIN_PATH.'frontend/sections/show-mobile-link.php');
        }


        /**
         * Returns an array with all the application's frontend settings
         *
         * @return array
         */
        public static function load_app_settings()
        {

            // load basic settings
            $frontend_options = array(
                'color_scheme',
                'theme_timestamp',
                'font_headlines',
                'font_subtitles',
                'font_paragraphs',
				'display_mode' ,
				'display_website_link',
				'posts_per_page' ,
				'enable_facebook',
				'enable_twitter' ,
				'enable_google'
            );

            $settings = array();

            foreach ($frontend_options as $option_name){
                $settings[$option_name] = PWAPP_Options::get_setting($option_name);
            }



            // check if custom theme exists and the file size is greater than zero
            if ($settings['theme_timestamp'] != ''){

                $custom_theme_path = PWAPP_FILES_UPLOADS_DIR.'theme-'.$settings['theme_timestamp'].'.css';

                if (!file_exists($custom_theme_path) || filesize($custom_theme_path) == 0){
                    $settings['theme_timestamp'] = '';
                }
            }

            // load images
            foreach (array('icon', 'logo', 'cover') as $file_type){

                $file_path = PWAPP_Options::get_setting($file_type);

                if ($file_path == '' || !file_exists(PWAPP_FILES_UPLOADS_DIR.$file_path))
                    $settings[$file_type] = '';
                else
                    $settings[$file_type] = PWAPP_FILES_UPLOADS_URL.$file_path;

            }

            // generate comments token
            if (!class_exists('PWAPP_Tokens')) {
                require_once(PWAPP_PLUGIN_PATH . 'inc/class-pwapp-tokens.php');
            }

            $settings['comments_token'] = PWAPP_Tokens::get_token();

            return $settings;
        }


        /**
         * Check if a language file exists in the locales folder
         *
         * @param $locale
         * @return bool|string
         */
        public static function check_language_file($locale)
        {
            $language_file_path = PWAPP_PLUGIN_PATH.'frontend/locales/'.strip_tags($locale).'.json';

            if (!file_exists($language_file_path)) {
                $language_file_path = PWAPP_PLUGIN_PATH."frontend/locales/default.json";
            }

            if (file_exists($language_file_path)){
                return $language_file_path;
            }

            return false;
        }
    }
}
