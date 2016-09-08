<?php

if ( ! class_exists( 'PWAPP_Options' ) ) {
    require_once(PWAPP_PLUGIN_PATH.'inc/class-pwapp-options.php');
}

if ( ! class_exists( 'PWAPP_Uploads' ) ) {
    require_once(PWAPP_PLUGIN_PATH.'inc/class-pwapp-uploads.php');
}

if ( ! class_exists( 'PWAPP_Cookie' ) ) {
    require_once(PWAPP_PLUGIN_PATH.'inc/class-pwapp-cookie.php');
}

if ( ! class_exists( 'PWAPP_Core' ) ) {

    /**
     * PWAPP_Core
     *
     * Main class for the progressive web apps plugin. This class handles:
     *
     * - activation / deactivation of the plugin
     * - setting / getting the plugin's options
     * - loading the admin section, javascript and css files
     * - loading the app in the frontend
     *
     */
    class PWAPP_Core
    {

        /* ----------------------------------*/
        /* Methods							 */
        /* ----------------------------------*/

        /**
         *
         * Construct method that initializes the plugin's options
         *
         */
        public function __construct()
        {

            // create uploads folder and define constants
            if ( !defined( 'PWAPP_FILES_UPLOADS_DIR' ) && !defined( 'PWAPP_FILES_UPLOADS_URL' ) ) {
                $PWAPP_Uploads = new PWAPP_Uploads();
                $PWAPP_Uploads->define_uploads_dir();
            }

            if ( is_admin() ) {
                $this->setup_hooks();
            }
        }


        /**
         *
         * The activate() method is called on the activation of the plugin.
         *
         * This method adds to the DB the default settings of the plugin and creates the upload folder.
         *
         */
        public function activate()
        {
            // add settings to database
            PWAPP_Options::save_settings(PWAPP_Options::$options);

            $PWAPP_Uploads = new PWAPP_Uploads();
            $PWAPP_Uploads->create_uploads_dir();
        }


        /**
         *
         * The deactivate() method is called when the plugin is deactivated.
         * This method removes temporary data (transients and cookies).
         *
         */
        public function deactivate()
        {
            // delete plugin settings (transients)
            PWAPP_Options::deactivate();

            // remove the cookies
            $PWAPP_Cookie = new PWAPP_Cookie();

            $PWAPP_Cookie->set_cookie("theme_mode", false, -3600);
            $PWAPP_Cookie->set_cookie("load_app", false, -3600);
        }


        /**
         * Init admin notices hook
         */
        public function setup_hooks(){
            add_action( 'admin_notices', array( $this, 'display_admin_notices' ) );
        }

        /**
         *
         * Show admin notices if PHP version is too old
         *
         */
        public function display_admin_notices(){

            if ( ! current_user_can( 'manage_options' ) ) {
                return;
            }

            if (version_compare(PHP_VERSION, '5.4') < 0) {
                echo '<div class="error"><p><b>Warning!</b> The ' . PWAPP_PLUGIN_NAME . ' plugin requires at least PHP 5.4.0!</p></div>';
            }
        }

        /**
         *
         * Method used to check if a specific plugin is installed and active,
         * returns true if the plugin is installed and false otherwise.
         *
         * @param $plugin_name - the name of the plugin
         *
         * @return bool
         */
        public static function is_active_plugin($plugin_name)
        {

            $active_plugin = false; // by default, the search plugin does not exist

            // if the plugin name is empty return false
            if ($plugin_name != '') {

                // if function doesn't exist, load plugin.php
                if (!function_exists('get_plugins')) {
                    require_once ABSPATH . 'wp-admin/includes/plugin.php';
                }

                // get active plugins from the DB
                $apl = get_option('active_plugins');

                // get list withh all the installed plugins
                $plugins = get_plugins();

                foreach ($apl as $p){
                    if (isset($plugins[$p])){
                        // check if the active plugin is the searched plugin
                        if ($plugins[$p]['Name'] == $plugin_name)
                            $active_plugin = true;
                    }
                }
            }

            return $active_plugin;
        }
    }
}
