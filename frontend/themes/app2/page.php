<?php

if ( is_numeric( get_the_ID() ) ) {

	if ( get_option( 'show_on_front' ) == 'page' && get_option( 'page_on_front' ) == get_the_ID() ) {
		require_once( PWAPP_PLUGIN_PATH . 'frontend/sections/template.php' );
	} else {
		$page_obj = get_post();

		header( 'Location: ' . home_url() . '/#page/' . $page_obj->post_name . '/' . $page_obj->ID ); // redirect to page
	}
} else {
	header( 'Location: ' . home_url() );
}


