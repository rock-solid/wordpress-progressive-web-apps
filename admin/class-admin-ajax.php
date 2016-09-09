<?php

if ( ! class_exists( 'PWAPP_Themes_Config' ) ) {
    require_once(PWAPP_PLUGIN_PATH.'inc/class-pwapp-themes-config.php');
}

if ( ! class_exists( 'PWAPP_Admin_Ajax' ) ) {

    /**
     *
     * PWAPP_Admin_Ajax class for managing Ajax requests from the admin area of the plugin
     *
     * @todo Test separately the methods of this class
     *
     */
    class PWAPP_Admin_Ajax
    {

        /**
         *
         * Create a theme management object and return it
         *
         * @return object
         *
         */
        protected function get_theme_manager()
        {
            if ( ! class_exists( 'PWAPP_Themes_Compiler' ) && version_compare(PHP_VERSION, '5.3') >= 0 ) {
                require_once(PWAPP_PLUGIN_PATH.'inc/class-pwapp-themes-compiler.php');
            }

            if (class_exists('PWAPP_Themes_Compiler')) {
                return new PWAPP_Themes_Compiler();
            }

            return false;
        }

        /**
         *
         * Create an uploads management object and return it
         *
         * @return object
         *
         */
        protected function get_uploads_manager()
        {
            return new PWAPP_Uploads();
        }

        /**
         * Save new font settings into the database. Returns true if we need to compile the css file.
         *
         * @param $data = array with POST data
         *
         * @return array with the following properties:
         * - scss - If we need to compile the theme
         * - updated - If any of the font settings have changed
         *
         */
        protected function update_theme_fonts($data)
        {

            // check if we have to compile the scss file
            $response = array(
                'scss' => false,
                'updated' => false
            );

            foreach (array('headlines', 'subtitles', 'paragraphs') as $font_type) {

                if (isset($data['pwapp_edittheme_font'.$font_type])) {

                    // check if the font settings have changed
                    if ($data['pwapp_edittheme_font'.$font_type] != PWAPP_Options::get_setting('font_'.$font_type)) {

                        PWAPP_Options::update_settings('font_' . $font_type, $data['pwapp_edittheme_font' . $font_type]);
                        $response['updated'] = true;
                    }

                    // if a font different from the default one was selected, we need to compile the css file
                    if ($data['pwapp_edittheme_font'.$font_type] != 1) {
                        $response['scss'] = true;
                    }
                }
            }

            if (isset($data['pwapp_edittheme_fontsize'])) {

                // check if the font size setting has changed
                if ($data['pwapp_edittheme_fontsize'] != PWAPP_Options::get_setting('font_size')) {

                    PWAPP_Options::update_settings('font_size', $data['pwapp_edittheme_fontsize']);
                    $response['updated'] = true;
                }

                // if a font size different from the default one was selected, we need to compile the css file
                if ($data['pwapp_edittheme_fontsize'] != 1) {
                    $response['scss'] = true;
                }
            }

            return $response;
        }


        /**
         *
         * Save new color scheme setting into the database. Returns true if we need to compile the css file.
         *
         * @param $data = array with POST data
         *
         * @return array with the following properties:
         * - scss - If we need to compile the theme
         * - updated - If the color scheme setting has changed
         */
        protected function update_theme_color_scheme($data)
        {

            // check if we have to compile the scss file
            $response = array(
                'scss' => false,
                'updated' => false
            );

            if (isset($data['pwapp_edittheme_colorscheme'])) {

                if (PWAPP_Options::get_setting('color_scheme') != $data['pwapp_edittheme_colorscheme']) {

                    PWAPP_Options::update_settings('color_scheme', $data['pwapp_edittheme_colorscheme']);
                    $response['updated'] = true;
                }

                // enable compiling for the second & third color schemes
                if ($data['pwapp_edittheme_colorscheme'] != 1) {
                    $response['scss'] = true;
                }
            }

            return $response;
        }


        /**
         * Save new colors settings into the database. Returns true if we need to compile the css file.
         *
         * @param $data = array with POST data
         *
         * @return array with the following properties:
         * - scss - If we need to compile the theme
         * - error = If set to true if we have invalid color codes or the number of colors is not the same
         * with the one from the theme.
         */
        protected function update_theme_colors($data)
        {

            $response = array(
                'scss' => false,
                'error' => false
            );

            $arr_custom_colors = array();

            // read theme and custom colors options
            $selected_theme = PWAPP_Options::get_setting('theme');
            $selected_custom_colors = PWAPP_Options::get_setting('custom_colors');

            // how many colors does the theme have
            $no_theme_colors = count(PWAPP_Themes_Config::$color_schemes[$selected_theme]['vars']);

            for ($i = 0; $i < $no_theme_colors; $i++) {

                // validate color code format
                if (isset($data['pwapp_edittheme_customcolor' . $i]) &&
                    trim($data['pwapp_edittheme_customcolor' . $i]) != '' &&
                    preg_match('/^#[a-f0-9]{6}$/i', trim($data['pwapp_edittheme_customcolor' . $i]))) {

                    $arr_custom_colors[] = strtolower($data['pwapp_edittheme_customcolor' . $i]);

                    // if the color settings have changed, we need to recompile the css file
                    if (empty($selected_custom_colors) ||
                        (isset($selected_custom_colors[$i]) && strtolower($data['pwapp_edittheme_customcolor' . $i]) != $selected_custom_colors[$i])){

                        $response['scss'] = true;
                    }

                } else {
                    $response['error'] = true;
                    break;
                }
            }

            // save colors only if all the colors from the theme have been set
            if (count($arr_custom_colors) == $no_theme_colors){

                PWAPP_Options::update_settings('custom_colors', $arr_custom_colors);

            } else {

                $response['error'] = true;
                $response['scss'] = false;
            }

            return $response;
        }


        /**
         *
         * Delete custom theme file and reset option
         *
         */
        protected function remove_custom_theme(){

            // remove compiled css file (if it exists)
            $theme_timestamp = PWAPP_Options::get_setting('theme_timestamp');

            if ($theme_timestamp != ''){

                $pwapp_pro_themes_compiler = $this->get_theme_manager();

                if ($pwapp_pro_themes_compiler !== false) {

                    $pwapp_pro_themes_compiler->remove_css_file($theme_timestamp);
                    PWAPP_Options::update_settings('theme_timestamp', '');
                }
            }
        }

        /**
         *
         * Method used to save the custom settings for a theme.
         *
         * Displays a JSON response with the following fields:
         *
         * - status = 0 if an error has occurred, 1 otherwise
         * - messages = array with error messages, possible values are:
         *
         * - invalid custom colors format
         * - settings were not changed
         * - other error messages resulted from compiling the theme
         *
         */
        public function theme_settings()
        {
            if (current_user_can('manage_options')) {

                $arr_response = array(
                    'status' => 0,
                    'messages' => array()
                );

                // handle color schemes and fonts (look & feel page)
                if (isset($_POST['pwapp_edittheme_colorscheme']) && is_numeric($_POST['pwapp_edittheme_colorscheme']) &&
                    isset($_POST['pwapp_edittheme_fontheadlines']) && is_numeric($_POST['pwapp_edittheme_fontheadlines']) &&
                    isset($_POST['pwapp_edittheme_fontsubtitles']) && is_numeric($_POST['pwapp_edittheme_fontsubtitles']) &&
                    isset($_POST['pwapp_edittheme_fontparagraphs']) && is_numeric($_POST['pwapp_edittheme_fontparagraphs']) &&
                    isset($_POST['pwapp_edittheme_fontsize']) && is_numeric($_POST['pwapp_edittheme_fontsize'])){

                    // build array with the allowed fonts sizes
                    $allowed_fonts_sizes = array();
                    foreach (PWAPP_Themes_Config::$allowed_fonts_sizes as $allowed_font_size) {
                        $allowed_fonts_sizes[] = $allowed_font_size['size'];
                    }

                    if (in_array($_POST['pwapp_edittheme_colorscheme'], array(0,1,2,3)) &&
                        in_array($_POST['pwapp_edittheme_fontheadlines']-1, array_keys(PWAPP_Themes_Config::$allowed_fonts)) &&
                        in_array($_POST['pwapp_edittheme_fontsubtitles']-1, array_keys(PWAPP_Themes_Config::$allowed_fonts)) &&
                        in_array($_POST['pwapp_edittheme_fontparagraphs']-1, array_keys(PWAPP_Themes_Config::$allowed_fonts)) &&
                        in_array($_POST['pwapp_edittheme_fontsize'], $allowed_fonts_sizes)){

                        // check if the theme compiler can be successfully loaded
                        $pwapp_pro_themes_compiler = $this->get_theme_manager();

                        if ($pwapp_pro_themes_compiler === false) {

                            $arr_response['messages'][] = 'Unable to load theme compiler. Please check your PHP version, should be at least 5.3.';

                        } else {

                            // save custom colors first
                            $updated_colors = array('scss' => false, 'error' => false);

                            if ($_POST['pwapp_edittheme_colorscheme'] == 0) {

                                $updated_colors = $this->update_theme_colors($_POST);

                                // if the colors were not successfully processed, display error message and exit
                                if ($updated_colors['error']) {

                                    $arr_response['messages'][] = 'Please select all colors before saving the custom color scheme!';
                                    echo json_encode($arr_response);

                                    wp_die();
                                }
                            }

                            // update fonts and check if we need to compile the scss file
                            $updated_fonts = $this->update_theme_fonts($_POST);

                            // update color scheme
                            $updated_color_scheme = $this->update_theme_color_scheme($_POST);

                            // the settings haven't changed, so return error status
                            if (!$updated_colors['scss'] && !$updated_fonts['updated'] && !$updated_color_scheme['updated']) {

                                $arr_response['messages'][] = 'Your application\'s settings have not changed!';

                            } else {

                                if ($updated_colors['scss'] || $updated_fonts['scss'] || $updated_color_scheme['scss']) {

                                    $theme_timestamp = time();

                                    // create new css theme file
                                    $theme_compiled = $pwapp_pro_themes_compiler->compile_css_file($theme_timestamp);

                                    if (!$theme_compiled['compiled']) {
                                        $arr_response['messages'][] = $theme_compiled['error'];
                                    } else {

                                        // delete old css file (if it exists)
                                        $old_theme_timestamp = PWAPP_Options::get_setting('theme_timestamp');

                                        // update theme timestamp
                                        PWAPP_Options::update_settings('theme_timestamp', $theme_timestamp);

                                        if ($old_theme_timestamp != '') {
                                            $pwapp_pro_themes_compiler->remove_css_file($old_theme_timestamp);
                                        }

                                        // the theme was successfully compiled and saved
                                        $arr_response['status'] = 1;
                                    }

                                } else {

                                    // we have reverted to the default theme settings, remove custom theme file
                                    $this->remove_custom_theme();
                                    $arr_response['status'] = 1;
                                }
                            }
                        }
                    }
                }

                echo json_encode($arr_response);
            }

            wp_die();
        }



        /**
         *
         * Remove image using the corresponding option's value for the filename
         *
         * @param $file_type = icon, logo or cover
         * @return bool
         */
        protected function remove_image($file_type)
        {
            // get previous image filename
            $previous_file_path = PWAPP_Options::get_setting($file_type);

            // check the file exists and remove it
            if ($previous_file_path != '') {

                $PWAPP_Uploads = $this->get_uploads_manager();
                return $PWAPP_Uploads->remove_uploaded_file($previous_file_path);
            }

            return false;
        }


        /**
         *
         * Method used to save the icon, logo, cover or a category image
         *
         */
        public function theme_editimages()
        {

            if (current_user_can( 'manage_options' )){

                $action = null;

                if (!empty($_GET) && isset($_GET['type']))
                    if ($_GET['type'] == 'upload' || $_GET['type'] == 'delete')
                        $action = $_GET['type'];

                $arr_response = array(
                    'status' => 0,
                    'messages' => array()
                );

                if ($action == 'upload'){

                    if (!empty($_FILES) && sizeof($_FILES) > 0){

                        require_once(ABSPATH . 'wp-admin/includes/image.php');

                        if (!function_exists( 'wp_handle_upload' ))
                            require_once( ABSPATH . 'wp-admin/includes/file.php' );

                        $default_uploads_dir = wp_upload_dir();

                        // check if the upload folder is writable
                        if (!is_writable(PWAPP_FILES_UPLOADS_DIR)){

                            $arr_response['messages'][] = "Error uploading image, the upload folder ".PWAPP_FILES_UPLOADS_DIR." is not writable.";

                        } elseif (!is_writable($default_uploads_dir['path'])) {

                            $arr_response['messages'][] = "Error uploading image, the upload folder ".$default_uploads_dir['path']." is not writable.";

                        } else {

                            $has_uploaded_files = false;

                            foreach ($_FILES as $file => $info) {

                                if (!empty($info['name'])) {

                                    $has_uploaded_files = true;

                                    $file_type = null;

                                    if ($file == 'pwapp_editimages_icon') {
                                        $file_type = 'icon';
                                    } elseif ($file == 'pwapp_editimages_logo') {
                                        $file_type = 'logo';
                                    } elseif ($file == 'pwapp_editcover_cover') {
                                        $file_type = 'cover';
                                    } elseif ($file == 'pwapp_categoryedit_icon') {
                                        $file_type = 'category_icon';
                                    }

                                    if ($info['error'] >= 1 || $info['size'] <= 0 && array_key_exists($file_type, PWAPP_Uploads::$allowed_files)) {

                                        $arr_response['status'] = 0;
                                        $arr_response["messages"][] = "We encountered a problem processing your " . ($file_type == 'category_icon' ? 'image' : $file_type) . ". Please choose another image!";

                                    } elseif ($info['size'] > 1048576) {

                                        $arr_response['status'] = 0;
                                        $arr_response["messages"][] = "Do not exceed the 1MB file size limit when uploading your custom " . ($file_type == 'category_icon' ? 'image' : $file_type) . ".";

                                    } elseif ($file_type == 'category_icon' && (!isset($_POST['pwapp_categoryedit_id']) || !is_numeric($_POST['pwapp_categoryedit_id']))) {

                                        // If the category icon file is NOT accompanied by the category ID, default to the error message
                                        $arr_response['status'] = 0;

                                    } else {

                                        /****************************************/
                                        /*										*/
                                        /* SET FILENAME, ALLOWED FORMATS AND SIZE */
                                        /*										*/
                                        /****************************************/

                                        // make unique file name for the image
                                        $arrFilename = explode(".", $info['name']);
                                        $fileExtension = end($arrFilename);

                                        $arrAllowedExtensions = PWAPP_Uploads::$allowed_files[$file_type]['extensions'];

                                        // check file extension
                                        if (!in_array(strtolower($fileExtension), $arrAllowedExtensions)) {

                                            $arr_response['messages'][] = "Error saving image, please add a " . implode(' or ', $arrAllowedExtensions) . " image for your " . ($file_type == 'category_icon' ? 'category' : $file_type) . "!";

                                        } else {

                                            /****************************************/
                                            /*										*/
                                            /* UPLOAD IMAGE                         */
                                            /*										*/
                                            /****************************************/

                                            $uniqueFilename = $file_type . '_' . time() . '.' . $fileExtension;

                                            // upload to the default uploads folder
                                            $upload_overrides = array('test_form' => false);
                                            $movefile = wp_handle_upload($info, $upload_overrides);

                                            if (is_array($movefile)) {

                                                if (isset($movefile['error'])) {

                                                    $arr_response['messages'][] = $movefile['error'];

                                                } else {

                                                    /****************************************/
                                                    /*										*/
                                                    /* RESIZE AND COPY IMAGE                */
                                                    /*										*/
                                                    /****************************************/

													// @todo Add resize image
													$copied_and_resized = copy($movefile['file'], PWAPP_FILES_UPLOADS_DIR . $uniqueFilename);

                                                    /****************************************/
                                                    /*										*/
                                                    /* DELETE PREVIOUS IMAGE AND SET OPTION */
                                                    /*										*/
                                                    /****************************************/

                                                    if ($copied_and_resized) {

                                                        if ($file_type == 'category_icon') {

                                                            // delete previous image
                                                            $this->remove_image_category($_POST['pwapp_categoryedit_id']);

                                                            // update categories settings array
                                                            $categories_details = PWAPP_Options::get_setting('categories_details');
                                                            $categories_details[$_POST['pwapp_categoryedit_id']] = array('icon' => $uniqueFilename);

                                                            PWAPP_Options::update_settings('categories_details', $categories_details);

                                                        } else {

                                                            // delete previous image
                                                            $this->remove_image($file_type);

                                                            // save option
                                                            PWAPP_Options::update_settings($file_type, $uniqueFilename);
                                                        }

                                                        // add path in the response
                                                        $arr_response['status'] = 1;
                                                        $arr_response['uploaded_' . $file_type] = PWAPP_FILES_UPLOADS_URL . $uniqueFilename;
                                                    }

                                                    // remove file from the default uploads folder
                                                    if (file_exists($movefile['file']))
                                                        unlink($movefile['file']);
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            if ($has_uploaded_files == false && !isset($_POST['pwapp_editcover_text'])) {
                                $arr_response['messages'][] = "Please upload an image!";
                            }
                        }
                    }

                } elseif ($action == 'delete'){

                    /****************************************/
                    /*										*/
                    /* DELETE ICON / LOGO / COVER       	*/
                    /*										*/
                    /****************************************/

                    // delete icon, logo or cover, depending on the 'source' param
                    if (isset($_GET['source'])) {

                        if (array_key_exists($_GET['source'], PWAPP_Uploads::$allowed_files)){

                            $file_type = $_GET['source'];

                            if ($file_type == 'category_icon' && isset($_GET['category_id']) && is_numeric($_GET['category_id'])) {

                                // delete previous image
                                $this->remove_image_category($_GET['category_id']);

                                // update categories settings array
                                $categories_details = PWAPP_Options::get_setting('categories_details');
                                unset($categories_details[ $_GET['category_id'] ]);

                                PWAPP_Options::update_settings('categories_details', $categories_details);

                                $arr_response['status'] = 1;

                            } elseif (in_array($file_type, array('icon', 'logo', 'cover'))) {

                                // get the previous file name from the options table
                                $this->remove_image($file_type);

                                // save option with an empty value
                                PWAPP_Options::update_settings($file_type, '');

                                $arr_response['status'] = 1;
                            }
                        }
                    }
                }

                if (isset($_POST['pwapp_editcover_text'])) {

                    // load HTML purifier / formatter
                    if (!class_exists('PWAPP_Formatter')) {
                        require_once(PWAPP_PLUGIN_PATH . 'inc/class-pwapp-formatter.php');
                    }

                    $purifier = PWAPP_Formatter::init_purifier();

                    $cover_text = $purifier->purify(stripslashes($_POST['pwapp_editcover_text']));
                    PWAPP_Options::update_settings('cover_text', $cover_text);

                    $arr_response['status'] = 1;
                }

                // echo json with response
                echo json_encode($arr_response);
            }

            exit();
        }


        /**
         *
         * Method used to send a feedback e-mail from the admin
         *
         * Handle request, then display 1 for success and 0 for error.
         *
         */
        public function send_feedback()
        {

            if (current_user_can('manage_options')){

                $status = 0;

                if (isset($_POST) && is_array($_POST) && !empty($_POST)){

                    if (isset($_POST['pwapp_feedback_page']) &&
                        isset($_POST['pwapp_feedback_name']) &&
                        isset($_POST['pwapp_feedback_email']) &&
                        isset($_POST['pwapp_feedback_message'])){

                        if ($_POST['pwapp_feedback_page'] != '' &&
                            $_POST['pwapp_feedback_name'] != '' &&
                            $_POST['pwapp_feedback_email'] != '' &&
                            $_POST['pwapp_feedback_message'] != ''){

                            $admin_email = $_POST['pwapp_feedback_email'];

                            // filter e-mail
                            if (filter_var($admin_email, FILTER_VALIDATE_EMAIL) !== false ){

                                // set e-mail variables
                                $message = "Name: ".strip_tags($_POST["pwapp_feedback_name"])."\r\n \r\n";
                                $message .= "E-mail: ".$admin_email."\r\n \r\n";
                                $message .= "Message: ".strip_tags($_POST["pwapp_feedback_message"])."\r\n \r\n";
                                $message .= "Page: ".stripslashes(strip_tags($_POST['pwapp_feedback_page']))."\r\n \r\n";

                                if (isset($_SERVER['HTTP_HOST']))
                                    $message .= "Host: ".$_SERVER['HTTP_HOST']."\r\n \r\n";

                                // add license fields
                                $message .= "License key: ".PWAPP_Options::get_setting('license_key')."\r\n \r\n";
                                $message .= "License status: ".PWAPP_Options::get_setting('license_status')."\r\n \r\n";

                                $license_expiry_date = PWAPP_Options::get_setting('license_expiry_date');
                                if ($license_expiry_date != '')
                                    $message .= "License expires: ".date('Y-m-d H:i:s', $license_expiry_date)."\r\n \r\n";

                                $subject = PWAPP_PLUGIN_NAME.' Feedback';
                                $to = PWAPP_FEEDBACK_EMAIL;

                                // set headers
                                $headers = 'From:'.$admin_email."\r\nReply-To:".$admin_email;

                                // send e-mail
                                if (mail($to, $subject, $message, $headers))
                                    $status = 1;
                            }
                        }
                    }
                }

                echo $status;
            }

            exit();
        }
    }
}
