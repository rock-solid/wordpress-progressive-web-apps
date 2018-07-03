<?php

if ( is_numeric( get_the_ID() ) ) {


	$post_obj    = get_post();
	$mobile_url .= '/#post/' . $post_obj->post_name . '/' . $post_obj->ID;

	header( 'Location: ' . home_url() . '/#post/' . $post_obj->post_name . '/' . $post_obj->ID );
} else {
	header( 'Location: ' . home_url() );
}
