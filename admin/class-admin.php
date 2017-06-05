<?php

if ( ! class_exists( 'PWAPP_Admin' ) ) {

    /**
     *
     * PWAPP_Admin class for managing the admin area for the plugin
     *
     */
    class PWAPP_Admin
    {

		/**
         *
         * Method used to render the themes selection page from the admin area
         *
         */
        public function themes() {

            include(PWAPP_PLUGIN_PATH.'admin/pages/themes.php');
        }

		/**
         *
         * Method used to render the themes selection page from the admin area
         *
         */
        public function theme_settings() {

            include(PWAPP_PLUGIN_PATH.'admin/pages/theme-settings.php');
        }

        /**
         * Static method used to request the more from an endpoint on a different domain.
         *
         * The method returns an array containing the upgrade information or an empty array by default.
         *
         */
        public static function more_updates() {

            $json_data =  get_transient(PWAPP_Options::$transient_prefix.'more_updates');

			if ($json_data){

				if ($json_data == 'warning') {
                    return $json_data;
				}

                // get response
                $response = json_decode($json_data, true);

                if (isset($response["content"]) && is_array($response["content"]) && !empty($response["content"])) {

					if (isset($response['content']['version']) && $response['content']['version'] == PWAPP_MORE_UPDATES_VERSION) {
                    	return $response["content"];
					}
				}
			}

			// check if we have a https connection
			$is_secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;

			// JSON URL that should be requested
			$json_url = ($is_secure ? PWAPP_MORE_UPDATES_HTTPS : PWAPP_MORE_UPDATES);

			// get response
			$json_response = PWAPP_Core::read_data($json_url);

			if ($json_response !== false && $json_response != '') {

				// Store this data in a transient
				set_transient(PWAPP_Options::$transient_prefix.'more_updates', $json_response, 3600*24*2);

				// get response
				$response = json_decode($json_response, true);

				if (isset($response["content"]) && is_array($response["content"]) && !empty($response["content"])){

					// return response
					return $response["content"];
				}

			} elseif ($json_response == false) {

				// Store this data in a transient
				set_transient(PWAPP_Options::$transient_prefix.'more_updates', 'warning', 3600*24*2 );

				// return message
				return 'warning';
			}

            // by default return empty array
            return array();
        }

		/**
         * Get array with the PRO themes.
         *
         * @return string
         */
		public static function upgrade_pro_themes($upgrade_content = false){

			$themes = array();

			if ($upgrade_content === false)
				$upgrade_content = self::more_updates();

			if  (is_array($upgrade_content) && !empty($upgrade_content)){

				if (array_key_exists('premium', $upgrade_content) && array_key_exists('themes', $upgrade_content['premium'])) {

					if (array_key_exists('list', $upgrade_content['premium']['themes']) && is_array($upgrade_content['premium']['themes']['list'])) {

						foreach ($upgrade_content['premium']['themes']['list'] as $theme){

							if (isset($theme['title']) &&
								isset($theme['icon']) && filter_var($theme['icon'], FILTER_VALIDATE_URL) &&
								(!isset($theme['demo']['link']) || filter_var($theme['demo']['link'], FILTER_VALIDATE_URL)) &&
								(!isset($theme['details']['link']) || filter_var($theme['details']['link'], FILTER_VALIDATE_URL))
							){
								$themes[] = $theme;
							}
						}
					}
				}
			}

			return $themes;
		}


        /**
         * Build tree hierarchy for the pages array
         *
         * @param $all_pages
         * @return array
         */
        /*protected function build_pages_tree($all_pages){

            $nodes_pages = array();

            foreach ($all_pages as $p) {

                $nodes_pages[$p->ID] = array(
                    'id' => $p->ID,
                    'parent_id' => intval($p->post_parent),
                    'obj' => clone $p
                );
            }

            $pages_tree = array(0 => array());

            foreach ($nodes_pages as $n) {

                $pid = $n['parent_id'];
                $id = $n['id'];

                if (!isset($pages_tree[$pid]))
                    $pages_tree[$pid] = array('child' => array());

                if (isset($pages_tree[$id]))
                    $child = &$pages_tree[$id]['child'];
                else
                    $child = array();

                $pages_tree[$id] = $n;
                $pages_tree[$id]['child'] = &$child;
                unset($pages_tree[$id]['parent_id']);
                unset($child);

                $pages_tree[$pid]['child'][] = &$pages_tree[$id];
            }

            if (!empty($pages_tree) && !empty($pages_tree[0])) {
                $nodes_pages = $pages_tree[0]['child'];
                unset($pages_tree);
                return $nodes_pages;
            }

            return array();

        }*/

    }
}
