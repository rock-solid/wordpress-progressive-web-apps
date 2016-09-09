<?php

if (class_exists('PWAPP_Core')):

    if (is_single() || is_page() || is_category()):

        // The mobile web app paths will be set relative to the home url
        $mobile_url = home_url();
        $is_visible = false;

        if (is_single()){

            // Read inactive categories
            $inactive_categories = PWAPP_Options::get_setting('inactive_categories');

            // Read post categories
            $post_categories = get_the_category();

            // Check if the post belongs to a visible category
            $is_visible = false;
            $visible_category = null;

            foreach ($post_categories as $post_category){

                if (!in_array($post_category->cat_ID, $inactive_categories)) {
                    $is_visible = true;
                    $mobile_url .= "/#article/".get_the_ID();
                    break;
                }
            }

        } elseif (is_page()) {

            $page_id = get_the_ID();
            $inactive_pages = PWAPP_Options::get_setting('inactive_pages');

            if (!in_array($page_id, $inactive_pages)){

                $is_visible = true;

                $mobile_url .= "/#page/".$page_id;
            }

        } elseif (is_category()) {

            $category_name = single_cat_title("", false);

            if ($category_name){

                $category_obj = get_term_by('name', $category_name, 'category');

                if ($category_obj && isset($category_obj->slug) && isset($category_obj->term_id) && is_numeric($category_obj->term_id)){

                    $category_id = $category_obj->term_id;

                    // check if the category is active / inactive before displaying it
                    $inactive_categories = PWAPP_Options::get_setting('inactive_categories');

                    if (!in_array($category_id, $inactive_categories)){

                        $is_visible = true;

                        $mobile_url .= "/#category/".$category_obj->slug.'/'.$category_id;
                    }
                }
            }
        }

        if ($is_visible):
            ?>
            <link rel="alternate" media="only screen and (max-width: 640px)" href="<?php echo $mobile_url;?>" />
        <?php
        endif;
    endif;
endif;
?>
