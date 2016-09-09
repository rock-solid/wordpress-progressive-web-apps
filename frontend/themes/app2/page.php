<?php

if (is_numeric(get_the_ID())){

    if (get_option('show_on_front') == 'page' && get_option('page_on_front') == get_the_ID()) {
        require_once(PWAPP_PLUGIN_PATH.'frontend/sections/template.php');
    } else {
        header("Location: ".home_url()."/#page/".get_the_ID()); // redirect to page
    }
} else {
    header("Location: ".home_url());
}

?>
