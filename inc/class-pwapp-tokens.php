<?php

if ( ! class_exists( 'PWAPP_Tokens' ) ) {

    /**
     * Class PWAPP_Tokens
     *
     * Contains different methods for setting / getting tokens
     */
    class PWAPP_Tokens
    {

        /**
         *
         * Method used to create a token for the comments form.
         *
         * The method returns a string formed using the encoded domain and a timestamp.
         *
         * @return string
         *
         */
        public static function get_token()
        {

            $token = md5(md5(get_bloginfo("wpurl")).PWAPP_CODE_KEY);

            // encode token again
            $token = base64_encode($token.'_'.strtotime('+1 hour'));

            // generate token
            return $token;
        }


        /**
         *
         * Method used to check if a generated token is valid.
         *
         * The method returns true if the token is valid and false otherwise.
         *
         * @param $token - string
         * @return bool
         *
         */
        public static function check_token($token)
        {

            if (base64_decode($token,true)){

                // decode token to get timestamp and encoded url
                $decoded_token = base64_decode($token,true);

                if (strpos($decoded_token, "_") !== FALSE) {

                    // get params
                    $arrParams = explode('_',$decoded_token);

                    if (is_array($arrParams) && !empty($arrParams) && count($arrParams) == 2) {

                        // check timestamp
                        if (time() < $arrParams[1]) {

                            // get the generated encoded domain
                            $generated_url = md5(md5(get_bloginfo("wpurl")).PWAPP_CODE_KEY);

                            // check encoded domain
                            if ($arrParams[0] ==  $generated_url)
                                return true;
                        }
                    }
                }
            }

            // by default return false;
            return false;
        }

    }
}