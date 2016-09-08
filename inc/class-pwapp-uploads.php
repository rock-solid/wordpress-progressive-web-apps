<?php

if (!class_exists('PWAPP_Uploads')) {

    /**
     * Overall Uploads Management class
     *
     * Instantiates all the uploads and offers a number of utility methods to work with the options
     */
    class PWAPP_Uploads
    {

        /* ----------------------------------*/
        /* Properties						 */
        /* ----------------------------------*/

        public static $allowed_files = array(
            'logo' => array(
                'max_width' => 120,
                'max_height' => 120,
                'extensions' => array('png')
            ),
            'icon' => array(
                'max_width' => 256,
                'max_height' => 256,
                'extensions' => array('jpg', 'jpeg', 'png','gif')
            ),
            'cover' => array(
                'max_width' => 1000,
                'max_height' => 1000,
                'extensions' => array('jpg', 'jpeg', 'png','gif')
            ),
            'category_icon' => array(
                'max_width' => 500,
                'max_height' => 500,
                'extensions' => array('jpg', 'jpeg', 'png','gif')
            ),
        );

        protected static $htaccess_template = 'frontend/sections/htaccess-template.txt';

        /* ----------------------------------*/
        /* Methods							 */
        /* ----------------------------------*/

        /**
         *
         * Define constants with the uploads dir paths
         *
         */
        public function define_uploads_dir()
        {
            $wp_uploads_dir = wp_upload_dir();

            $pwapp_uploads_dir = $wp_uploads_dir['basedir'] . '/' . PWAPP_DOMAIN . '/';

            define('PWAPP_FILES_UPLOADS_DIR', $pwapp_uploads_dir);
            define('PWAPP_FILES_UPLOADS_URL', $wp_uploads_dir['baseurl'] . '/' . PWAPP_DOMAIN . '/');

            add_action( 'admin_notices', array( $this, 'display_admin_notices' ) );
        }


        /**
         *
         * Display uploads folder specific admin notices.
         *
         */
        public function display_admin_notices()
        {
            if (!current_user_can('manage_options')) {
                return;
            }

            // if the directory doesn't exist, display notice
            if (!file_exists(PWAPP_FILES_UPLOADS_DIR)) {
                echo '<div class="error"><p><b>Warning!</b> The '.PWAPP_PLUGIN_NAME.' uploads folder does not exist: '.PWAPP_FILES_UPLOADS_DIR.'</p></div>';
            } elseif (!is_writable(PWAPP_FILES_UPLOADS_DIR)) {
                echo '<div class="error"><p><b>Warning!</b> The ' . PWAPP_PLUGIN_NAME . ' uploads folder is not writable: ' . PWAPP_FILES_UPLOADS_DIR . '</p></div>';
            }
        }

        /**
         *
         * Create uploads folder
         *
         */
        public function create_uploads_dir()
        {

            $wp_uploads_dir = wp_upload_dir();

            $pwapp_uploads_dir = $wp_uploads_dir['basedir'] . '/' . PWAPP_DOMAIN . '/';

            // check if the uploads folder exists and is writable
            if (file_exists($wp_uploads_dir['basedir']) && is_dir($wp_uploads_dir['basedir']) && is_writable($wp_uploads_dir['basedir'])) {

                // if the directory doesn't exist, create it
                if (!file_exists($pwapp_uploads_dir)) {

                    mkdir($pwapp_uploads_dir, 0777);

                    // add .htaccess file in the uploads folder
                    $this->set_htaccess_file();
                }
            }
        }


        /**
         *
         * Clean up the uploads dir when the plugin is uninstalled
         *
         */
        public function remove_uploads_dir()
        {

            foreach (array('icon','logo','cover') as $image_type) {

                $image_path = PWAPP_Options::get_setting($image_type);
                $this->remove_uploaded_file($image_path);
            }

            // remove categories images
            $categories_details = PWAPP_Options::get_setting('categories_details');

            if (is_array($categories_details) && !empty($categories_details)) {

                foreach ($categories_details as $category_id => $category_details) {

                    if (is_array($category_details) && array_key_exists('icon', $category_details)) {
                        $this->remove_uploaded_file($category_details['icon']);
                    }
                }
            }

            // remove compiled css file (if it exists)
            $theme_timestamp = PWAPP_Options::get_setting('theme_timestamp');

            if ($theme_timestamp != ''){

                if (!class_exists( 'PWAPP_Themes_Compiler' ) && version_compare(PHP_VERSION, '5.3') >= 0 ) {
                    require_once(PWAPP_PLUGIN_PATH.'inc/class-pwapp-themes-compiler.php');
                }

                if (class_exists('PWAPP_Themes_Compiler')) {

                    $pwapp_themes_compiler = new PWAPP_Themes_Compiler();
                    $pwapp_themes_compiler->remove_css_file($theme_timestamp);
                }
            }

            // remove htaccess file
            $this->remove_htaccess_file();

            // delete folder
            rmdir(PWAPP_FILES_UPLOADS_DIR);
        }




        /**
         * Check if a file path exists in the uploads folder and returns its url.
         *
         * @param $file_path
         * @return string
         */
        public function get_file_url($file_path){

            if (file_exists(PWAPP_FILES_UPLOADS_DIR.$file_path)){
                return PWAPP_FILES_UPLOADS_URL.$file_path;
            }

            return '';
        }

        /**
         * Delete an uploaded file
         *
         * @param $file_path
         * @return bool
         *
         */
        public function remove_uploaded_file($file_path){

            // check the file exists and remove it
            if ($file_path != ''){
                if (file_exists(PWAPP_FILES_UPLOADS_DIR.$file_path))
                    return unlink(PWAPP_FILES_UPLOADS_DIR.$file_path);
            }
        }

        /**
         *
         * Create a .htaccess file with rules for compressing and caching static files for the plugin's upload folder
         * (css, images)
         *
         * @return bool
         *
         */
        protected function set_htaccess_file()
        {
            $file_path = PWAPP_FILES_UPLOADS_DIR.'.htaccess';

            if (!file_exists($file_path)){

                if (is_writable(PWAPP_FILES_UPLOADS_DIR)){

                    $template_path = PWAPP_PLUGIN_PATH.self::$htaccess_template;

                    if (file_exists($template_path)){

                        $fp = @fopen($file_path, "w");
                        fwrite($fp, file_get_contents($template_path));
                        fclose($fp);

                        return true;
                    }
                }
            }

            return false;
        }

        /**
         *
         * Remote .htaccess file with rules for compressing and caching static files for the plugin's upload folder
         * (css, images)
         *
         * @return bool
         *
         */
        protected function remove_htaccess_file()
        {

            $file_path = PWAPP_FILES_UPLOADS_DIR.'.htaccess';

            if (file_exists($file_path)){
                unlink($file_path);
            }
        }
    }
}