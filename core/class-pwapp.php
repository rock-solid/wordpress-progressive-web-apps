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


		/**
         *
         * Static method used to request the content of different pages using curl or fopen
         * This method returns false if both curl and fopen are dissabled and an empty string ig the json could not be read
         *
         */
        public static function read_data($json_url) {

            // check if curl is enabled
            if (extension_loaded('curl')) {

                $send_curl = curl_init($json_url);

                // set curl options
                curl_setopt($send_curl, CURLOPT_URL, $json_url);
                curl_setopt($send_curl, CURLOPT_HEADER, false);
                curl_setopt($send_curl, CURLOPT_CONNECTTIMEOUT, 2);
                curl_setopt($send_curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($send_curl, CURLOPT_HTTPHEADER,array('Accept: application/json', "Content-type: application/json"));
                curl_setopt($send_curl, CURLOPT_FAILONERROR, FALSE);
                curl_setopt($send_curl, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($send_curl, CURLOPT_SSL_VERIFYHOST, FALSE);
                $json_response = curl_exec($send_curl);

                // get request status
                $status = curl_getinfo($send_curl, CURLINFO_HTTP_CODE);
                curl_close($send_curl);

                // return json if success
                if ($status == 200)
                    return $json_response;

            } elseif (ini_get( 'allow_url_fopen' )) { // check if allow_url_fopen is enabled

                // open file
                $json_file = fopen( $json_url, 'rb' );

                if($json_file) {

                    $json_response = '';

                    // read contents of file
                    while (!feof($json_file)) {
                        $json_response .= fgets($json_file);
                    }
                }

                // return json response
                if($json_response)
                    return $json_response;

            } else
                // both curl and fopen are disabled
                return false;

            // by default return an empty string
            return '';

        }

    }
}
