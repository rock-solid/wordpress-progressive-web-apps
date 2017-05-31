<?php

if ( ! class_exists( 'PWAPP_Themes_Config' ) ) {

    /**
     * Overall Themes Config class
     *
     */
    class PWAPP_Themes_Config
    {

        /* ----------------------------------*/
        /* Properties						 */
        /* ----------------------------------*/

        public static $allowed_fonts = array(
            'Roboto Condensed Light',
            'Roboto Condensed Bold',
            'Roboto Condensed Regular',
            'OpenSans Condensed Light',
            'Crimson Roman',
            'Roboto Slab Light',
            'Helvetica Neue Light Condensed',
            'Helvetica Neue Bold Condensed',
            'Gotham Book'
        );

        /**
         * Allowed font sizes are float numbers. Their unit measure is 'rem'.
         * @var array
         */
        public static $allowed_fonts_sizes = array(
            array(
                'label' => 'Small',
                'size' => 0.875
            ),
            array(
                'label' => 'Normal',
                'size' => 1
            ),
            array(
                'label' => 'Large',
                'size' => 1.125
            )
        );

        public static $color_schemes = array(

            2 => array(
                'labels' => array(
                    'Headlines and primary texts',
                    'Article background',
                    'Article border',
                    'Secondary texts - dates and other messages',
                    'Category label',
                    'Category text color',
                    'Buttons background',
                    'Buttons icon',
                    'Menu',
                    'Forms',
                    'Cover text color'
                ),
                'vars' => array(
                    'base-text-color',
                    'base-bg-color',
                    'article-border-color',
                    'extra-text-color',
                    'category-color',
                    'category-text-color',
                    'buttons-bg-color',
                    'buttons-color',
                    'actions-panel-color',
                    'form-color',
                    'cover-text-color'
                ),
                'presets' => array(
                    1 => array(
                        '#303030',
                        '#ffffff',
                        '#dddddd',
                        '#999999',
                        '#f90c9a',
                        '#ffffff',
                        '#fafafa',
                        '#747474',
                        '#32394a',
                        '#5c5c5c',
                        '#ffffff'
                    ),
                    2 => array(
                        '#445256',
                        '#ededed',
                        '#d9e0e3',
                        '#7c9197',
                        '#ffb505',
                        '#ffffff',
                        '#e7e7e7',
                        '#34799f',
                        '#616d6f',
                        '#7c9197',
                        '#ffffff'
                    ),
                    3 => array(
                        '#647279',
                        '#ebf3f6',
                        '#bcc7cb',
                        '#709cb1',
                        '#19bcbb',
                        '#ffffff',
                        '#cfe0e6',
                        '#ea6c55',
                        '#2d4d45',
                        '#709cb1',
                        '#ffffff'
                    )
                ),
                'cover' => 1,
                'cover_text' => 0,
                'posts_per_page' => 1
            )
        );


		/**
		* Get the application's background color for the app manifest.
		*
		* @param int or null $color_scheme
		* @return string or false
		*
		* @todo Update this method to use a separate color variable.
		*/
		public static function get_manifest_background($color_scheme = null)
		{
			if ($color_scheme == null){
                $color_scheme = PWAPP_Options::get_setting('color_scheme');
            }

			switch ($color_scheme) {

				case 0 :
					$custom_colors = PWAPP_Options::get_setting('custom_colors');

					if (is_array($custom_colors) && isset($custom_colors[1])) {
						return $custom_colors[1];
					}
					break;

				case 1 :
				case 2 :
				case 3 :
					return self::$color_schemes['2']['presets'][$color_scheme][1];
			}

			return false;
		}
    }
}
