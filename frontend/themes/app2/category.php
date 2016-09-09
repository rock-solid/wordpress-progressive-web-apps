<?php

$category_name = single_cat_title("", false);
$mobile_url = home_url();

if ($category_name){

    $category_obj = get_term_by('name', $category_name, 'category');

    if ($category_obj && isset($category_obj->slug) && isset($category_obj->term_id) && is_numeric($category_obj->term_id)){
        $mobile_url .= "/#category/".$category_obj->slug.'/'.$category_obj->term_id;
    }
}

header("Location: ".$mobile_url);
