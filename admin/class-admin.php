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
        public function theme() {

            include(PWAPP_PLUGIN_PATH.'admin/pages/theme.php');
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
