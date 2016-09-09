<?php

require_once PWAPP_PLUGIN_PATH . "libs/scssphp-0.3.0/scss.inc.php";
use Leafo\ScssPhp\Compiler;

if ( ! class_exists( 'PWAPP_Themes_Config' ) ) {
    require_once(PWAPP_PLUGIN_PATH.'inc/class-pwapp-themes-config.php');
}

if ( ! class_exists( 'PWAPP_Themes_Compiler' ) ) {

    /**
     * Overall Themes Management class
     *
     * This class uses the SCSS compiler and it should not be included outside the admin area
     *
     * @todo Test methods from this class separately.
     */
    class PWAPP_Themes_Compiler
    {

        /* ----------------------------------*/
        /* Methods							 */
        /* ----------------------------------*/


        /**
         * Compile new css theme file.
         *
         * The method will return false (error) if:
         *
         * - it can't compile the theme because the PHP version is too old
         * - the uploads folder is not writable
         * - the variables SCSS file can't be created
         * - the CSS file can't be compiled
         *
         * @param $theme_timestamp
         *
         * @return array with the following properties:
         * - compiled = (bool) If the theme was successfully compiled
         * - error = An error message
         *
         */
        public function compile_css_file($theme_timestamp)
        {

            $response = array(
                'compiled' => false,
                'error' => false
            );

            if (!is_writable(PWAPP_FILES_UPLOADS_DIR)){

                $response['error'] = "Error uploading theme files, the upload folder ".PWAPP_FILES_UPLOADS_DIR." is not writable.";

            } else {

                $wp_uploads_dir = wp_upload_dir();
                $pwapp_uploads_dir = $wp_uploads_dir['basedir'].'/';

                if (!is_writable($pwapp_uploads_dir)){

                    $response['error'] = "Error uploading theme files, your uploads folder ".$pwapp_uploads_dir." is not writable.";

                } else {

                    // write scss file with the colors and fonts variables
                    $generated_vars_scss = $this->generate_variables_file($error);

                    if ($generated_vars_scss) {

                        // compile css
                        $response['compiled'] = $this->generate_css_file($theme_timestamp, $error);

                        // cleanup variables file
                        $this->remove_variables_file();
                        return $response;
                    }
                }
            }

            return $response;
        }


        /**
         * Delete css file
         *
         * @param $theme_timestamp
         */
        public function remove_css_file($theme_timestamp)
        {
            $file_path = PWAPP_FILES_UPLOADS_DIR.'theme-'.$theme_timestamp.'.css';

            if (file_exists($file_path))
                unlink($file_path);
        }


        /**
         *
         * Write a scss file with the theme's settings (colors and fonts)
         *
         * @param bool $error
         * @return bool
         *
         */
        protected function generate_variables_file(&$error = false)
        {

            // attempt to open or create the scss file
            $file_path = PWAPP_FILES_UPLOADS_DIR.'_variables.scss';

            $fp = @fopen($file_path, "w");

            if ($fp !== false) {

                // read theme settings
                $theme = 2;
                $color_scheme = PWAPP_Options::get_setting('color_scheme');

                if ($color_scheme == 0){
                    $colors = PWAPP_Options::get_setting('custom_colors');
                } else {
                    $colors = PWAPP_Themes_Config::$color_schemes[$theme]['presets'][$color_scheme];
                }

                // write fonts
                foreach (array('headlines', 'subtitles', 'paragraphs') as $font_type){

                    $font_setting = PWAPP_Options::get_setting('font_'.$font_type);
                    $font_family = PWAPP_Themes_Config::$allowed_fonts[$font_setting-1];

                    fwrite($fp, '$'.$font_type."-font:'".str_replace(" ","",$font_family)."';\r\n");
                }

                // write font size
                fwrite($fp, '$base-font-size:'.PWAPP_Options::get_setting('font_size')."rem;\r\n");

                // write colors
                foreach (PWAPP_Themes_Config::$color_schemes[$theme]['vars'] as $key => $var_name){
                    fwrite($fp, '$'.$var_name.":".$colors[$key].";\r\n");
                }

                fclose($fp);
                return true;

            } else {

                $error = "Unable to compile theme, the file ".$file_path." is not writable.";
            }

            return false;
        }


        /**
         *
         * Delete variables scss file
         *
         */
        protected function remove_variables_file()
        {
            $file_path = PWAPP_FILES_UPLOADS_DIR.'_variables.scss';

            if (file_exists($file_path))
                unlink($file_path);
        }


        /**
         *
         * Generate a CSS file using the variables and theme SCSS files
         *
         * The CSS file is created as 'theme-{$theme_timestamp}.css' in the plugin's uploads folder.
         *
         * @param $theme_timestamp
         * @param bool|string $error
         * @return bool
         *
         */
        protected function generate_css_file($theme_timestamp, &$error = false)
        {

            // attempt to open or create the scss file
            $file_path = PWAPP_FILES_UPLOADS_DIR.'theme-'.$theme_timestamp.'.css';

            $fp = @fopen($file_path, "w");

            if ($fp !== false) {

                $scss_compiler = new Compiler();

                $scss_compiler->setImportPaths(array(
                    PWAPP_FILES_UPLOADS_DIR,
                    PWAPP_PLUGIN_PATH.'frontend/themes/app2'.'/scss/'
                ));

                $scss_compiler->setFormatter('scss_formatter_compressed');

                try {

                    // write compiler output directly in the css file
                    $compiled_file = $scss_compiler->compile('@import "_variables.scss"; @import "phone.scss";');
                    fwrite($fp, $compiled_file);

                    fclose($fp);
                    return true;

                } catch (Exception $e){

                    $error = "Unable to compile theme, the theme's scss file contains errors.";
                    fclose($fp);
                }

            } else {
                $error = "Unable to compile theme, the file ".$file_path." is not writable.";
            }

            return false;
        }

    }
}
