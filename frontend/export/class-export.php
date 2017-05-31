<?php

if ( ! class_exists( 'PWAPP_Formatter' ) ) {
    require_once(PWAPP_PLUGIN_PATH.'inc/class-pwapp-formatter.php');
}

if ( ! class_exists( 'PWAPP_Export' ) ) {

    /**
     * Class PWAPP_Export
     *
     * Contains different methods for exporting categories, articles and comments
     *
     */
    class PWAPP_Export
    {

        /* ----------------------------------*/
        /* Attributes						 */
        /* ----------------------------------*/

        public $purifier;
        private $inactive_categories = array();
        private $inactive_pages = array();

        /* ----------------------------------*/
        /* Methods							 */
        /* ----------------------------------*/


        /**
         *
         * Init purifier, inactive categories and pages properties
         *
         */
        public function __construct()
        {
            $this->purifier = PWAPP_Formatter::init_purifier();
            $this->inactive_categories = PWAPP_Options::get_setting('inactive_categories');
            $this->inactive_pages = PWAPP_Options::get_setting('inactive_pages');
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
         *
         * Verify if a post has a featured image and return it
         *
         * @param $post_id
         * @return array
         */
        protected function get_post_image($post_id)
        {

            $image_details = array();

            if (has_post_thumbnail($post_id)) {

                $post_thumbnail_id = get_post_thumbnail_id($post_id);
                $image_metadata = wp_get_attachment_metadata($post_thumbnail_id, true);

                if (is_array($image_metadata) && !empty($image_metadata)) {

                    if (isset($image_metadata['width']) && isset($image_metadata['height'])) {

                        $image_details = array(
                            "src" => wp_get_attachment_url($post_thumbnail_id),
                            "width" => $image_metadata['width'],
                            "height" => $image_metadata['height']
                        );
                    }
                }
            }

            return $image_details;
        }


        /**
         *
         * Compose array with a post's details for a posts list
         *
         * @param $post
         * @return array
         *
         */
        protected function format_post_short($post)
        {

            // check if the post has a post thumbnail assigned to it and save it in an array
            $image_details = $this->get_post_image($post->ID);

            // Build post array - get_the_title(), get_permalink() methods can be used inside or outside of The Loop.
            // If used outside the loop an ID must be specified.

            $arr_article = array(
                'id' => $post->ID,
                "title" => get_the_title(),
                "author" => get_the_author_meta('display_name'),
                "link" => get_permalink(),
                "image" => !empty($image_details) ? $image_details : "",
                "date" => PWAPP_Formatter::format_date(strtotime($post->post_date)),
                "timestamp" => strtotime($post->post_date),
                "description" => apply_filters('the_excerpt', get_the_excerpt()),
                "content" => '',
                "categories" => $this->get_visible_categories_ids($post)
            );

            return $arr_article;
        }


        /**
         *
         * Compose array with a post's details and full content for the post details page
         *
         * @param $post
         * @return array
         *
         * @todo Generated description is different from the format_post_short() method, unify them or remove description field.
         *
         */
        protected function format_post_full($post)
        {

            // check if the post has a post thumbnail assigned to it and save it in an array
            $image_details = $this->get_post_image($post->ID);

            // Build post array - get_the_title(), get_permalink() methods can be used inside or outside of The Loop.
            // If used outside the loop an ID must be specified.

            // get & filter content
            $content = apply_filters("the_content", $post->post_content);

            // remove script tags
            $content = PWAPP_Formatter::remove_script_tags($content);
            $content = $this->purifier->purify($content);

            // remove all urls from attachment images
            $content = preg_replace(array('{<a(.*?)(wp-att|wp-content\/uploads|attachment)[^>]*><img}', '{ wp-image-[0-9]*" /></a>}'), array('<img', '" />'), $content);

            // check if the post has a manually edited excerpt, otherwise create an excerpt from the content
            if (has_excerpt($post->ID)) {

                $description = $this->purifier->purify($post->post_excerpt);

            } else {

                $description = PWAPP_Formatter::truncate_html(strip_tags($content), 100, '...', false, false);
                $description = apply_filters('the_excerpt', $description);
            }

            $avatar = "";
            $get_avatar = get_avatar($post->post_author, 50);
            preg_match("/src='(.*?)'/i", $get_avatar, $matches);
            if (isset($matches[1])) {
                $avatar = $matches[1];
            }

            $arr_article = array(
                'id' => $post->ID,
                "title" => get_the_title($post->ID),
                "author" => get_the_author_meta('display_name', $post->post_author),
                "author_description" => get_the_author_meta( 'description', $post->post_author ),
                "author_avatar" => $avatar,
                "link" => get_permalink($post->ID),
                "image" => !empty($image_details) ? $image_details : "",
                "date" => PWAPP_Formatter::format_date(strtotime($post->post_date)),
                "timestamp" => strtotime($post->post_date),
                "description" => $description,
                "content" => $content,
                "categories" => $this->get_visible_categories_ids($post)
            );

            return $arr_article;
        }


        /**
         *
         * If 'inactive_categories' has been set, return an array with only the active categories ids.
         * Otherwise, return false.
         *
         * @return array|bool
         *
         */
        protected function get_active_categories(){

            // build array with the active categories ids
            $active_categories_ids = false;

            // check if we must limit search to some categories ids
            if (count($this->inactive_categories) > 0) {

                // read all categories
                $categories = get_categories(array('hierarchical' => 0));

                $active_categories_ids = array();

                foreach ($categories as $category) {
                    if (!in_array($category->cat_ID, $this->inactive_categories))
                        $active_categories_ids[] = $category->cat_ID;
                }
            }

            return $active_categories_ids;
        }

        /**
         *
         * Order response categories array
         *
         * @param $arr_categories
         * @return array
         *
         */
        protected function order_categories($arr_categories)
        {

            // build array with the ordered categories
            $arr_ordered_categories = array();

            if (!empty($arr_categories)) {

                // check if the categories were ordered from the admin panel
                $order_categories = PWAPP_Options::get_setting('ordered_categories');

                // check if we have a latest category (should be the first one to appear)
                $has_latest = 0;
                if (isset($arr_categories[0])) {

                    // set order for the latest category and add it in the list
                    $arr_categories[0]['order'] = 1;
                    $has_latest = 1;

                    $arr_ordered_categories[] = $arr_categories[0];
                }

                // if the categories have been ordered
                if (!empty($order_categories)) {

                    // last ordered used for a category
                    $last_order = 1;

                    foreach ($order_categories as $category_id) {

                        // inactive categories & latest will be skipped
                        if (array_key_exists($category_id, $arr_categories)) {

                            // set the order for the category and add it in the list
                            $arr_categories[$category_id]['order'] = $last_order + $has_latest;

                            $arr_ordered_categories[] = $arr_categories[$category_id];
                            $last_order++;
                        }
                    }

                    foreach ($arr_categories as $key => $category) {
                        if ($category['order'] === false) {

                            $arr_categories[$key]['order'] = $last_order + $has_latest;

                            $arr_ordered_categories[] = $arr_categories[$key];
                            $last_order++;
                        }
                    }

                } else {

                    // the categories were not ordered from the admin panel, so just init the order field for each
                    // last order used for a category
                    $last_order = 1;

                    // set order for all the categories besides latest
                    foreach ($arr_categories as $key => $category) {

                        if ($category['id'] != 0) {

                            // set the order for the category and add it in the list
                            $arr_categories[$key]['order'] = $last_order + $has_latest;

                            $arr_ordered_categories[] = $arr_categories[$key];
                            $last_order++;
                        }
                    }
                }
            }

            return $arr_ordered_categories;
        }


        /**
         * Returns a post's visible category.
         * If the post doesn't belong to any visible categories, returns false.
         *
         * @param $post
         * @return null or category object
         */
        protected function get_visible_category($post)
        {
            // get post categories
            $categories = get_the_category($post->ID);

            // check if at least one of the categories is visible
            $visible_category = null;

            foreach ($categories as $category) {

                if (!in_array($category->cat_ID, $this->inactive_categories)) {
                    $visible_category = clone $category;
                }
            }

            return $visible_category;
        }


        /**
         * Parse the 'categories_details' array and return an array with icon paths, indexed by category id.
         * The method checks if an icon exists before adding it in the array.
         *
         * @return array
         */
        protected function get_categories_images(){

            $categories_images = array();

            $categories_details = PWAPP_Options::get_setting('categories_details');

            // create an uploads manager object
            $PWAPP_Uploads = $this->get_uploads_manager();

            if (is_array($categories_details) && !empty($categories_details)) {

                foreach ($categories_details as $category_id => $category_details){

                    if (is_array($category_details) && array_key_exists('icon', $category_details)) {

                        $icon_path = $category_details['icon'];

                        if ($icon_path != ''){
                            $icon_path = $PWAPP_Uploads->get_file_url($icon_path);
                        }

                        if ($icon_path != ''){

                            // categories icons are used as backgrounds,
                            // so we can use the default width / height in the exports
                            $categories_images[$category_id] = array(
                                'src' => $icon_path,
                                'width' => PWAPP_Uploads::$allowed_files['category_icon']['max_width'],
                                'height' => PWAPP_Uploads::$allowed_files['category_icon']['max_height']
                            );
                        }
                    }
                }
            }

            return $categories_images;
        }


        /**
         * Returns a post's visible categories ids.
         *
         * @param $post
         * @return array
         */
        protected function get_visible_categories_ids($post)
        {
            // get post categories
            $categories = get_the_category($post->ID);

            // check if at least one of the categories is visible
            $arr_categories_ids = array();

            foreach ($categories as $category) {

                if (!in_array($category->cat_ID, $this->inactive_categories)) {
                    $arr_categories_ids[] = $category->cat_ID;
                }
            }

            return $arr_categories_ids;
        }

        /**
         *
         * The comment_closed method is used to determine the comment status for an article.
         * The method returns 'open' if the users can comment and 'closed' otherwise.
         *
         * @param $post
         * @return string
         *
         */
        protected function comment_closed($post)
        {

            // set initial status for comments
            if ($post->comment_status == 'open' && get_option('comment_registration') == 0)
                $comment_status = 'open';
            else
                $comment_status = 'closed';

            // if the option close_comments_for_old_posts is not set, return comment status
            if (!get_option('close_comments_for_old_posts'))
                return $comment_status;

            // if the number of old days is not set, return comment_status
            $days_old = (int)get_option('close_comments_days_old');
            if (!$days_old)
                return $comment_status;

            /** This filter is documented in wp-includes/comment.php */
            $post_types = apply_filters('close_comments_for_post_types', array('post'));
            if (!in_array($post->post_type, $post_types))
                $comment_status = 'open';

            // if the post is older than the number of days set, change comment_status to false
            if (time() - strtotime($post->post_date_gmt) > ($days_old * DAY_IN_SECONDS))
                $comment_status = 'closed';

            // return comment status
            return $comment_status;
        }

		/**
		* Filter for export_categories.
		* It only retrieves categories with published, not password protected posts.
		*
		* @param $terms
		* @param $taxonomies
		* @param $args
		*/
        public function get_terms_filter($terms, $taxonomies, $args)
        {
			global $wpdb;

			$taxonomy = $taxonomies[0];
			if (!is_array($terms) && count($terms) < 1)
				return $terms;

			$filtered_terms = array();
			foreach ($terms as $term){
				$result = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts p JOIN $wpdb->term_relationships rl ON p.ID = rl.object_id WHERE rl.term_taxonomy_id = $term->term_id AND p.post_type = 'post' AND p.post_status = 'publish' AND p.post_password = '' LIMIT 1");
				if (intval($result) > 0)
					$filtered_terms[] = $term;
			}

			return $filtered_terms;
        }

        /**
         *
         * The export_categories method is used for exporting every category with a fixed number of articles.
         *
         *  This method returns a JSON with the following format:
         *
         *  - ex :
         *    {
         *        "categories": [
         *            {
         *                "id": 0,
         *                "order": 1,
         *                "name": "Latest",
         *                "link": "",
         *                "image": {
         *                    "src": "{image_path}",
         *                    "width": 480,
         *                    "height": 270
         *                },
         *                "parent_id":0,
         *                "articles": [
         *                    {
         *                        "id": "123456",
         *                        "title": "Post title",
         *                        "timestamp": 1398969000,
         *                        "author": "Author's name",
         *                        "date": "Thu, May 01, 2014 06:30",
         *                        "link": "{post_link}",
         *                        "image": "",
         *                        "description" : "<p>Lorem ipsum sit dolor amet..</p>",
         *                        "content": '',
         *                        "category_id": 3,
         *                        "category_name": "Post category"
         *                    }
         *                ]
         *            }
         *        ]
         *    }
         *
         * - The "Latest" category will be composed from all the visible categories and articles.
         *
         * Receives the following GET params:
         *
         * - callback = The JavaScript callback method
         * - content = 'exportcategories'
		 * - page = (optional) Number of the page to be displayed
         * - rows = (optional) Number of rows per page
         * - limit = (optional) The number of articles to be added for each category. Default value is 7.
         * - withArticles = (optional) Whether the categories will be returned with articles or not.
		 * Default value is 1 (read articles), any other value will skip over reading the articles.
         */
         public function export_categories()
		 {

            $page = false;
            if (isset($_GET["page"]) && is_numeric($_GET["page"]))
                $page = $_GET["page"];

            $rows = false;
            if (isset($_GET["rows"]) && is_numeric($_GET["rows"]))
                $rows = $_GET["rows"];

            if ($page && $rows == false) {
                $rows = 5;
            } elseif ($rows && $page == false) {
                $page = 1;
            }

            // set default limit
            $limit = 7;
            if (isset($_GET["limit"]) && is_numeric($_GET["limit"]))
                $limit = $_GET["limit"];

            $with_articles = 1;
            if (isset($_GET["withArticles"]) && is_numeric($_GET["withArticles"]))
                $with_articles = $_GET["withArticles"];

            // add the filter for exporting only categories with published posts
            add_filter('get_terms', array($this, 'get_terms_filter'), 10, 3);

            // get categories that have posts
            $categories = get_terms('category', 'hide_empty=1');

            // build array with the active categories ids
            $active_categories_ids = array();

            foreach ($categories as $category) {
                if (!in_array($category->term_id, $this->inactive_categories))
                    $active_categories_ids[] = $category->term_id;
            }

            // init categories array
            $arr_categories = array();

            // remove inline style for the photos types of posts
            add_filter('use_default_gallery_style', '__return_false');

            if (count($active_categories_ids) > 0) {

                $categories_images = $this->get_categories_images();
                foreach ($categories as $key => $category) {

                    if (in_array($category->term_id, $active_categories_ids)) {

						$arr_categories[$category->term_id] = array(
							'id' => $category->term_id,
							'order' => false,
							'name' => $category->name,
							'name_slug' => $category->slug,
							'parent_id' => isset($category->parent) ? $category->parent : 0,
							'link' => get_category_link($category->term_id),
							'image' => array_key_exists($category->term_id, $categories_images) ? $categories_images[$category->term_id] : ''
						);
                    }
                }
            }

            // remove the filter for exporting only categories with published posts
			remove_filter('get_terms', array($this, 'get_terms_filter'), 10);

            // activate latest category only if we have at least 2 visible categories
            if (count($arr_categories) > 1) {

				$arr_categories[0] = array(
					'id' => 0,
					'order' => false,
					'name' => 'Latest',
					'name_slug' => 'Latest',
					'image' => '',
					'parent_id' => 0
				);
            }

            $arr_categories = $this->order_categories($arr_categories);

            if ($page && $rows) {

                $nr_categories = count($arr_categories);

                if ($page > ceil($nr_categories/$rows)) {
                    return '{"categories":' . json_encode(array()) . ',"page":"' .$page . '","rows":"' .$rows  .'"' .',"pwapp":"'.PWAPP_VERSION.'"}';
                }

                $start = $rows * ($page-1);
                $arr_categories = array_slice($arr_categories, $start, $rows );
            }

            if ($with_articles == 1) {

                foreach ($arr_categories as $key => $arr_category) {

                     // Reset query & search posts from this category
                     $posts_query = array(
                         'numberposts' => $limit,
                         'posts_per_page' => $limit,
                         'post_type' => 'post',
                         'post_status' => 'publish',
                         'post_password' => ''
                     );

                     if ($arr_category['id'] == 0){
                         // read posts for the latest category (use all active categories)
                         $posts_query['cat'] = implode(', ', $active_categories_ids);
                     } else {
                         $posts_query['category__in'] = $arr_category['id'];
                     }

                     $cat_posts_query = new WP_Query($posts_query);

                     while ($cat_posts_query->have_posts()) {

                         $cat_posts_query->the_post();
                         $post = $cat_posts_query->post;

                         if ($post->post_type == 'post' && $post->post_password == '' && $post->post_status == 'publish') {

							// retrieve array with the post's details
							$post_details = $this->format_post_short($post);

							// if the category doesn't have a featured image yet, use the one from the current post
							if (!is_array($arr_categories[$key]["image"]) && !empty($post_details['image'])) {
								$arr_categories[$key]["image"] = $post_details['image'];
							}

							// if this is the first article from the category, create the 'articles' array
							if (!isset($arr_categories[$key]["articles"]))
								$arr_categories[$key]["articles"] = array();

							if ($arr_category['id'] == 0){

								// get post category
								$visible_category = $this->get_visible_category($post);

								if ($visible_category !== null) {
									$post_details['category_id'] = $visible_category->term_id;
									$post_details['category_name'] = $visible_category->name;
								}

							} else {
								$post_details['category_id'] = $arr_category['id'];
								$post_details['category_name'] = $arr_category['name'];
							}

							// add article in the array
							$arr_categories[$key]["articles"][] = $post_details;
                         }
                     }
                 }
            }

            if ($page && $rows) {
                return '{"categories":' . json_encode($arr_categories) . ',"page":"' .$page . '","rows":"' .$rows  .'"' .',"pwapp":"'.PWAPP_VERSION.'"}';
            } else {
                return '{"categories":' . json_encode($arr_categories) . ',"pwapp":"'.PWAPP_VERSION.'"}';
            }
        }


		/**
		*
		* The export_category method is used for exporting a category's details without it's articles
		*
		*  This method returns a JSON with the following format:
		*
		*  - ex :
		*    {
		*        "category":
		*            {
		*              "id":"",
		*               "name":"",
		*               "name_slug":"",
		*               "parent_id":"",
		*               "link": "",
		*               "image": {
		*                  "src": "{image_path}",
		*                  "width": 480,
		*                  "height":270
		*                }
		*              }
		*     }
		*
		*
		* Receives the following GET params:
		*
		* - callback = The JavaScript callback method
		* - content = 'exportcategory'
		* - categoryId = The id of the category we want
		*
		*/
        public function export_category()
		{
            if (isset($_GET["categoryId"]) && is_numeric($_GET["categoryId"])) {

                if ($_GET["categoryId"] == 0){

                    $arr_category = array(
                        'id' => 0,
                        'name' => 'Latest',
                        'name_slug' => 'Latest',
                        'image' => ""
                    );

                    return '{"category":' . json_encode($arr_category) . '}' ;
                }

                $the_category = get_term($_GET["categoryId"], 'category');

                if ($the_category && !in_array($the_category->term_id, $this->inactive_categories)) {

                    $category_details = PWAPP_Options::get_setting('categories_details');

                    if (is_array($category_details) && !empty($category_details)) {

                        if (isset($category_details[$the_category->term_id]) &&
                            is_array($category_details[$the_category->term_id]) &&
                            array_key_exists('icon', $category_details[$the_category->term_id])) {

                            $icon_path = $category_details[$the_category->term_id]['icon'];

                            if ($icon_path != ''){

                                $PWAPP_Uploads = $this->get_uploads_manager();
                                $icon_path = $PWAPP_Uploads->get_file_url($icon_path);
                            }

                            if ($icon_path != ''){

                                $category_image = array(
                                    'src' => $icon_path,
                                    'width' => PWAPP_Uploads::$allowed_files['category_icon']['max_width'],
                                    'height' => PWAPP_Uploads::$allowed_files['category_icon']['max_height']
                                );
                            }
                        }
                    }

                    $arr_category = array (
                        'id' => $the_category->term_id,
                        'name' => $the_category->name,
                        'name_slug' => $the_category->slug,
                        'parent_id' => $the_category->parent,
                        'link' => get_category_link($the_category->term_id),
                        'image' => isset($category_image) ? $category_image : ''
                    );

                    return '{"category":' . json_encode($arr_category) . '}' ;
                }

                return '{"error":"Category does not exist"}' ;
            }

            return '{"error":"Invalid category id"}' ;
        }




        /**
         *
         *  The export_articles method is used for exporting a number of articles for each category.
         *
         *  The method returns a JSON with the following format:
         *
         *  - ex :
         *    {
         *        "articles": [
         *            {
         *              "id": "123456",
         *              "title": "Post title",
         *              "timestamp": 1398950385,
         *              "author": "",
         *              "date": "Thu, May 01, 2014 01:19",
         *              "link": "{post_link}",
         *              "image": "",
         *              "description":"<p>Post content goes here...</p>",
         *              "content": '',
         *              "category_id": 5,
         *              "category_name": "Post category"
         *            },
         *           ...
         *        ]
         *    }
         *
         * Receives the following GET params:
         *
         * - callback = The JavaScript callback method
         * - content = 'exportarticles'
         * - lastTimestamp = (optional) Read articles that were published before this date
         * - categoryId = (optional) The category id. Default value is 0 (for the 'Latest' category).
         * - limit = (optional) The number of articles to be read from the category. Default value is 7.
         *
         */
        public function export_articles()
        {

            // init articles array
            $arr_articles = array();

            // set last timestamp
            $last_timestamp = date("Y-m-d H:i:s");
            if (isset($_GET["lastTimestamp"]) && is_numeric($_GET["lastTimestamp"]))
                $last_timestamp = date("Y-m-d H:i:s", $_GET["lastTimestamp"]);

            // set category id
            $category_id = 0;
            if (isset($_GET["categoryId"]) && is_numeric($_GET["categoryId"]))
                $category_id = $_GET["categoryId"];

            // set limit
            $limit = 7;
            if (isset($_GET["limit"]) && is_numeric($_GET["limit"]))
                $limit = $_GET["limit"];

            // set args for posts
            $args = array(
                'date_query' => array('before' => $last_timestamp),
                'numberposts' => $limit,
                'posts_per_page' => $limit,
                'post_status' => 'publish',
                'post_password' => ''
            );

            // if the selected category is active
            $is_active_category = false;

            // remove inline style for the photos types of posts
            add_filter('use_default_gallery_style', '__return_false');

            if ($category_id != 0) {

                $args["cat"] = $category_id;

                // check if this category was not deactivated
                if (!in_array($category_id, $this->inactive_categories))
                    $is_active_category = true;

            } else {

                // latest category will always be active
                $is_active_category = true;

                // build array with the active categories ids
                $active_categories_ids = $this->get_active_categories();

                // if we have to limit the categories, search posts that belong to the active categories
                if ($active_categories_ids !== false)
                    $args["category__in"] = $active_categories_ids;
            }

            if ($is_active_category) {

                $posts_query = new WP_Query($args);

                if ($posts_query->have_posts()) {

                    while ($posts_query->have_posts()) {

                        $posts_query->the_post();
                        $post = $posts_query->post;

                        if ($post->post_type == 'post' && $post->post_password == '' && $post->post_status == 'publish') {

                            // retrieve array with the post's details
                            $post_details = $this->format_post_short($post);

                            // get post category
                            $category = null;

                            if ($category_id > 0) {
                                $category = get_category($category_id);
                            } else {

                                // since a post can have many categories and we have set inactive categories,
                                // search for a category that is active
                                if ($active_categories_ids !== false) {

                                    $post_categories = wp_get_post_categories($post->ID);

                                    foreach ($post_categories as $post_category_id) {

                                        if (in_array($post_category_id, $active_categories_ids)) {
                                            $category = get_category($post_category_id);
                                            break;
                                        }
                                    }

                                } else {

                                    // get a random post category
                                    $cat = get_the_category();
                                    $category = $cat[0];
                                }
                            }

                            if ($category !== null) {

                                $post_details['category_id'] = $category->term_id;
                                $post_details['category_name'] = $category->name;

                                $arr_articles[] = $post_details;
                            }
                        }
                    }
                }
            }

            return '{"articles":' . json_encode($arr_articles) . "}";
        }


        /**
         *
         *  The exportArticle method is used for exporting a single post.
         *
         *  The method returns a JSON with the following format:
         *
         *  - ex :
         *    {
         *      "article": {
         *        "id": "123456",
         *        "title": "Post title",
         *        "author": "",
         *        "author_description":"",
         *        "author_avatar":"",
         *        "link": "{post_link}",
         *        "image": "",
         *        "date": "Thu, May 01, 2014 04:07",
         *        "timestamp": 1398960437,
         *        "description":"<p>The first of the content goes here</p>",
         *        "content": "<p>The full content goes here</p>",
         *        "categories":"",
         *        "category_id": 5,
         *        "category_name": "Post category"
         *        "comment_status": "open", (the values can be 'opened' or 'closed')
         *        "no_comments": 2,
         *        "show_avatars" : true,
         *        "require_name_email" : true,
         *        }
         *    }
         *
         *
         * Receives the following GET params:
         *
         * - callback = The JavaScript callback method
         * - content = 'exportarticle'
         * - articleId = The article's id.
         *
         */
        public function export_article()
        {

            global $post;

            if (isset($_GET["articleId"]) && is_numeric($_GET["articleId"])) {

                $post_details = array();

                // get post by id
                $post = get_post($_GET["articleId"]);

                if ($post != null && $post->post_type == 'post' && $post->post_password == '' && $post->post_status == 'publish') {

                    // check if at least one of the post's categories is visible
                    $visible_category = $this->get_visible_category($post);

                    if ($visible_category !== null) {

                        $post_details = $this->format_post_full($post);

                        // add category data
                        $post_details['category_id'] = $visible_category->term_id;
                        $post_details['category_name'] = $visible_category->name;

                        // get comments status
                        $comment_status = $this->comment_closed($post);

                        // check we have at least one approved comment that needs to be displayed
                        $comment_count = wp_count_comments($post->ID);
                        $no_comments = $comment_count->approved;

                        if ($comment_status == 'closed') {

                            if ($comment_count)
                                if ($comment_count->approved == 0)
                                    $comment_status = 'disabled';
                        }

                        // add comments data
                        $post_details['comment_status'] = $comment_status;
                        $post_details['no_comments'] = $no_comments;
                        $post_details['show_avatars'] = intval(get_option("show_avatars"));
                        $post_details['require_name_email'] = intval(get_option("require_name_email"));
                    }
                }

                return '{"article":' . json_encode($post_details) . "}";
            }

            return '{"error":"Invalid post id"}';
        }



        /**
         *
         * The export_comments method is used for exporting the comments for an article.
         *
         * The method returns a JSON with the specific content:
         *
         *  - ex :
         *    {
         *      "comments": [
         *           {
         *                "id": "1234",
         *                "author": "Comment author",
         *                "author_url": "{author_url}",
         *                "date": "Thu, May 01, 2014 04:07",
         *                "content": "<p>The comment's text goes here.</p>",
         *                "article_id": "123456",
         *                "article_title": "Post title",
         *                "avatar": "{avatar}",
         *            },
         *           ...
         *       ]
         *    }
         *
         * Receives the following GET params:
         *
         * - callback = The JavaScript callback method
         * - content = 'exportcomments'
         * - articleId = The article's id
         *
         */
        public function export_comments()
        {

            // check if the export call is correct
            if (isset($_GET["articleId"]) && is_numeric($_GET["articleId"])) {

                $arr_comments = array();

                // get post by id
                $post = get_post($_GET["articleId"]);

                if ($post != null && $post->post_type == 'post' && $post->post_password == '' && $post->post_status == 'publish') {

                    // check if at least one of the post's categories is visible
                    $visible_category = $this->get_visible_category($post);

                    if ($visible_category !== null) {

                        $args = array(
                            'parent' => '',
                            'post_id' => $post->ID,
                            'post_type' => 'post',
                            'status' => 'approve',
                        );

                        // order comments
                        if (get_bloginfo('version') >= 3.6) {

                            $comments_order = strtoupper(get_option('comment_order'));

                            if (!in_array($comments_order, array('ASC', 'DESC'))){
                                $comments_order = 'ASC';
                            }

                            $args['orderby'] = 'comment_date_gmt';
                            $args['order'] = $comments_order;
                        }

                        // read comments
                        $comments = get_comments($args);

                        if (is_array($comments) && !empty($comments)) {

                            foreach ($comments as $comment) {

                                $avatar = '';

                                // get avatar only if the author wants it displayed
                                if (get_option("show_avatars")) {

                                    $get_avatar = get_avatar($comment, 50);
                                    preg_match("/src='(.*?)'/i", $get_avatar, $matches);
                                    if (isset($matches[1]))
                                        $avatar = $matches[1];
                                }

                                $arr_comments[] = array(
                                    'id' => $comment->comment_ID,
                                    'author' => $comment->comment_author != '' ? ucfirst($comment->comment_author) : 'Anonymous',
                                    'author_url' => $comment->comment_author_url,
                                    'date' => PWAPP_Formatter::format_date(strtotime($comment->comment_date)),
                                    'content' => $this->purifier->purify($comment->comment_content),
                                    'article_id' => $post->ID,
                                    'article_title' => strip_tags(trim($post->post_title)),
                                    'avatar' => $avatar
                                );
                            }
                        }
                    }
                }

                // return comments json
                return '{"comments":' . json_encode($arr_comments) . "}";
            }

            return '{"error":"Invalid post id"}';

        }



        /**
         *  The save_comment method is used for adding a comment to an article.
         *
         *  The method returns a JSON with the success/error message.
         *
         * Receives the following GET params:
         *
         * - callback = The JavaScript callback method
         * - content = 'savecomment'
         * - articleId = The article's id
         * - author
         * - email
         * - url
         * - comment
         * - comment_parent
         * - code = Access token for saving comments
         *
         * @todo Translate error messages
         */
        public function save_comment()
        {

            if (!isset($_SERVER['HTTP_REFERER']) || strpos($_SERVER['HTTP_REFERER'], $_SERVER["HTTP_HOST"]) !== false) {

                if (isset($_GET["articleId"]) && is_numeric($_GET["articleId"])) {

                    // check token
                    if (isset($_GET['code']) && $_GET["code"] !== '') {

                        if (!class_exists('PWAPP_Tokens')) {
                            require_once(PWAPP_PLUGIN_PATH . 'inc/class-pwapp-tokens.php');
                        }

                        // if the token is valid, go ahead and save comment to the DB
                        if (PWAPP_Tokens::check_token($_GET['code'])) {

                            $arr_response = array(
                                'status' => 0,
                                'message' => ''
                            );

                            // get post by id
                            $post = get_post($_GET["articleId"]);

                            if ($post != null && $post->post_type == 'post' && $post->post_password == '' && $post->post_status == 'publish') {

                                // check if at least one of the post's categories is visible
                                $visible_category = $this->get_visible_category($post);

                                if ($visible_category !== null) {

                                    // check if the post accepts comments
                                    if (comments_open($post->ID)) {

                                        // get post variables
                                        $comment_post_ID = $post->ID;
                                        $comment_author = (isset($_GET['author'])) ? trim(strip_tags($_GET['author'])) : '';
                                        $comment_author_email = (isset($_GET['email'])) ? trim($_GET['email']) : '';
                                        $comment_author_url = (isset($_GET['url'])) ? trim($this->purifier->purify($_GET['url'])) : '';
                                        $comment_content = (isset($_GET['comment'])) ? trim($this->purifier->purify($_GET['comment'])) : '';
                                        $comment_type = 'comment';
                                        $comment_parent = isset($_GET['comment_parent']) ? absint($_GET['comment_parent']) : 0;

                                        // return errors for empty fields
                                        if (get_option('require_name_email')) {

                                            if ($comment_author_email == '' || $comment_author == '') {

                                                $arr_response['message'] = "Missing name or email"; //Please fill the required fields (name, email).
                                                return json_encode($arr_response);

                                            } elseif (!is_email($comment_author_email)) {

                                                $arr_response['message'] = "Invalid email address";
                                                return json_encode($arr_response);
                                            }
                                        }

                                        if ($comment_content == '') {
                                            $arr_response['message'] = "Missing comment"; // Please type a comment
                                            return json_encode($arr_response);
                                        }

                                        // set comment data
                                        $comment_data = compact('comment_post_ID', 'comment_author', 'comment_author_email', 'comment_author_url', 'comment_content', 'comment_type', 'comment_parent', 'user_ID');

                                        // add a hook for duplicate comments
                                        add_action("comment_duplicate_trigger", array(&$this, 'duplicate_comment'));

                                        // get comment id
                                        $comment_id = wp_new_comment($comment_data);

                                        // get status
                                        if (is_numeric($comment_id)) {

                                            // get comment
                                            $comment = get_comment($comment_id);

                                            // set status by comment status
                                            if ($comment->comment_approved == 1) {

                                                $arr_response['status'] = 1;
                                                $arr_response['message'] = "Your comment was successfully added";

                                            } else {

                                                $arr_response['status'] = 2;
                                                $arr_response['message'] = "Your comment is awaiting moderation";
                                            }

                                            return json_encode($arr_response);
                                        }

                                    } else {
                                        // Sorry, comments are closed for this item
                                        $arr_response['message'] = "Comments are closed";
                                    }

                                } else {
                                    // Sorry, the post belongs to a hidden category and is not available
                                    $arr_response['message'] = "Invalid post id";
                                }

                            } else {
                                // Sorry, the post is not available
                                $arr_response['message'] = "Invalid post id";
                            }

                            return json_encode($arr_response);
                        }
                    }
                }
            }
        }


        /**
         *
         * Action hook that is called when a duplicate comment is detected.
         *
         * The method is used to echo a JSON with an error and applies an exit to prevent wp_die().
         *
         * @improvement
         * If possible, improve this method by registering it as an ajax request and using wp_die() instead of exit()
         * to allow unit testing.
         */
        public function duplicate_comment()
        {
            // display the json
            $arr_response = array(
                'status' => 0,
                'message' => 'Duplicate comment'
            );

            echo $this->purifier->purify($_GET['callback']). '(' . json_encode($arr_response) . ')';

            // end
            exit();
        }


        /**
         *
         * Return array with the pages keys, ordered by hierarchy.
         * Child pages will be excluded if their parents are hidden.
         *
         * @param int $limit
         * @return array
         *
         */
        protected function get_pages_order($limit = 100){

            $all_pages = get_pages(
                array(
                    'sort_column' => 'menu_order,post_title',
                    'exclude' => implode(',', $this->inactive_pages),
                    'post_status' => 'publish',
                    'post_type' => 'page',
                    'number' => $limit
                )
            );

            $order_pages = array_keys(get_page_hierarchy($all_pages));
            return $order_pages;
        }


        /**
         *
         *  The exportPages method is used for exporting all the visible pages.
         *
         *  This method returns a JSON with the following format:
         *
         *  - ex :
         *    {
         *        "pages": [
         *            {
         *              "id": "123456",
         *              "order": 3,
         *              "title": "Page title",
         *              "image": "",
         *              "content": "<p>The page's content goes here.</p>",
         *            },
         *           ...
         *        ]
         *    }
         *
         * The method receives the following GET params:
         *
         * - callback = The JavaScript callback method
         * - content = 'exportpages'
         *
         */
        public function export_pages()
        {

            // init pages arrays
            $arr_pages = array();

            // set args for pages
            $limit = 100;

            $args = array(
                'post__not_in' => $this->inactive_pages,
                'numberposts' => $limit,
                'posts_per_page' => $limit,
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_password' => ''
            );

            if (get_bloginfo('version') >= 3.6) {
                $args['orderby'] = 'menu_order title';
                $args['order'] = 'ASC';
            }

            // get array with the ordered pages by hierarchy
            $order_pages = $this->get_pages_order($limit);

            // remove inline style for the photos types of posts
            add_filter('use_default_gallery_style', '__return_false');

            $pages_query = new WP_Query($args);

            if ($pages_query->have_posts()) {

                while ($pages_query->have_posts()) {

                    $pages_query->the_post();
                    $page = $pages_query->post;

                    if ($page->post_type == 'page' && $page->post_password == '' && $page->post_status == 'publish') {

                        // if the page has a title that is not empty
                        if (strip_tags(trim(get_the_title())) != '') {

                            // read featured image
                            $image_details = $this->get_post_image($page->ID);

                            if (get_option(PWAPP_Options::$prefix.'page_' . $page->ID) === false)
                                $content = apply_filters("the_content", $page->post_content);
                            else
                                $content = apply_filters("the_content", get_option(PWAPP_Options::$prefix.'page_' . $page->ID));

                            // if we have a pages hierarchy, use the order from that array
                            if (!empty($order_pages)) {

                                // if the page and its parent are visible, they should exist in the order array
                                $index_order = array_search($page->ID, $order_pages);

                                if (is_numeric($index_order)) {

                                    $current_key = $index_order + 1;

                                    $arr_pages[] = array(
                                        'id' => $page->ID,
                                        'parent_id' => intval($page->post_parent),
                                        'order' => $current_key,
                                        'title' => strip_tags(trim(get_the_title())),
                                        'link' => get_permalink(),
                                        'image' => !empty($image_details) ? $image_details : "",
                                        'content' => '',
                                        'has_content' => $content != '' ? 1 : 0
                                    );
                                }
                            }
                        }
                    }
                }
            }

            return '{"pages":' . json_encode($arr_pages) . "}";
        }


        /**
         *
         * The export_page method is used for exporting a single page.
         *
         * The method returns a JSON with the following format:
         *
         *  - ex :
         *    {
         *      "page": {
         *        "id": "123456",
         *        "title": "Page title",
         *        "link": "{page_link}",
         *        "image": "",
         *        "content": "<p>Page content goes here</p>"
         *     }
         *    }
         *
         * The method receives the following GET params:
         *
         * - callback = The JavaScript callback method
         * - content = 'exportpage'
         * - pageId = The page's id
         *
         * @todo (Improvement) Don't export page if its parent is hidden
         *
         */
        public function export_page()
        {

            global $post;

            if (isset($_GET["pageId"]) && is_numeric($_GET["pageId"])) {

                // init page array
                $arr_page = array();

                // get page by id
                $post = get_post($_GET["pageId"]);

                if ($post != null && $post->post_type == 'page' && $post->post_password == '' && $post->post_status == 'publish' && strip_tags(trim($post->post_title)) != '') {

                    // check if page is visible
                    $is_visible = false;

                    if (!in_array($post->ID, $this->inactive_pages))
                        $is_visible = true;

                    if ($is_visible) {

                        // featured image details
                        $image_details = $this->get_post_image($post->ID);

                        // for the content, first check if the admin edited the content for this page
                        if (get_option(PWAPP_Options::$prefix.'page_' . $post->ID) === false)
                            $content = apply_filters("the_content", $post->post_content);
                        else
                            $content = apply_filters("the_content", get_option(PWAPP_Options::$prefix.'page_' . $post->ID));

                        // remove script tags
                        $content = PWAPP_Formatter::remove_script_tags($content);
                        $content = $this->purifier->purify($content);

                        // remove all urls from attachment images
                        $content = preg_replace(array('{<a(.*?)(wp-att|wp-content\/uploads|attachment)[^>]*><img}', '{ wp-image-[0-9]*" /></a>}'), array('<img', '" />'), $content);

                        $arr_page = array(
                            "id" => $post->ID,
                            "parent_id" => wp_get_post_parent_id($post->ID),
                            "title" => get_the_title($post->ID),
                            "link" => get_permalink($post->ID),
                            "image" => !empty($image_details) ? $image_details : "",
                            "content" => $content,
                            "has_content" => $content != '' ? 1 : 0
                        );
                    }
                }

                // return page json
                return '{"page":' . json_encode($arr_page) . "}";
            }

            return '{"error":"Invalid post id"}';
        }


        /**
         *
         * Export manifest files for Android or Mozilla.
         *
         * The method receives a single GET param:
         *
         * - content = 'androidmanifest' or 'mozillamanifest'
         */
        public function export_manifest()
        {

            // set blog name
            $blog_name = get_bloginfo("name");

            // init response depending on the manifest type
            if (isset($_GET['content']) && $_GET['content'] == 'androidmanifest') {

                $arr_manifest = array(
                    'name' => $blog_name,
                    'start_url' => home_url(),
                    'display' => 'standalone',
					'orientation' => 'any',
                );

				if (!class_exists('PWAPP_Themes_Config')) {
					require_once(PWAPP_PLUGIN_PATH . 'inc/class-pwapp-themes-config.php');
				}

				$background_color = PWAPP_Themes_Config::get_manifest_background();

				if ($background_color !== false){
					$arr_manifest['theme_color'] = $background_color;
					$arr_manifest['background_color'] = $background_color;
				}


            } else {

                // remove domain name from the launch path
                $launch_path = home_url();
                $launch_path = str_replace('http://' . $_SERVER['HTTP_HOST'], '', $launch_path);
                $launch_path = str_replace('https://' . $_SERVER['HTTP_HOST'], '', $launch_path);

                $arr_manifest = array(
                    'name' => $blog_name,
                    'launch_path' => $launch_path,
                    'developer' => array(
                        "name" => $blog_name
                    )
                );
            }

            // load icon from the local settings and folder
            $icon_path = PWAPP_Options::get_setting('icon');

            if ($icon_path != '') {

                $PWAPP_Uploads = $this->get_uploads_manager();
                $icon_path = $PWAPP_Uploads->get_file_url($icon_path);
            }

            // set icon depending on the manifest file type
            if ($icon_path != '') {

                if ($_GET['content'] == 'androidmanifest') {

                    $arr_manifest['icons'] = array(
                        array(
                            "src" => $icon_path,
                            "sizes" => "192x192"
                        )
                    );

                } else {
                    $arr_manifest['icons'] = array(
                        '152' => $icon_path,
                    );
                }
            }

            return json_encode($arr_manifest);

        }


        /**
         *
         * Load app texts for the current locale.
         *
         * The JSON files with translations for each language are located in frontend/locales.
         *
         * @param $locale
         * @param $response_type = javascript | list
         * @return bool|mixed
         *
         */
        public function load_language($locale, $response_type = 'javascript')
        {

            if (!class_exists('PWAPP_Application'))
                require_once(PWAPP_PLUGIN_PATH.'frontend/class-application.php');

            $language_file = PWAPP_Application::check_language_file($locale);

            if ($language_file !== false) {

                $appTexts = file_get_contents($language_file);
                $appTextsJson = json_decode($appTexts, true);

                if ($appTextsJson && !empty($appTextsJson) && array_key_exists('APP_TEXTS', $appTextsJson)) {

                    if ($response_type == 'javascript')
                        return 'var APP_TEXTS = ' . json_encode($appTextsJson['APP_TEXTS']);
                    else
                        return $appTextsJson;
                }
            }

            return false;
        }
	}
}
