<?php

if ( is_single() || is_page() || is_category() ) :

	// The mobile web app paths will be set relative to the home url
	$mobile_url = home_url();
	$is_visible = false;

	if ( is_single() ) {

		$post_obj    = get_post();
		$mobile_url .= '/#post/' . $post_obj->post_name . '/' . $post_obj->ID;

	} elseif ( is_page() ) {

		$page_obj    = get_post();
		$mobile_url .= '/#page/' . $page_obj->post_name . '/' . $page_obj->ID;

	} elseif ( is_category() ) {

		$category_name = single_cat_title( '', false );

		if ( $category_name ) {

			$category_obj = get_term_by( 'name', $category_name, 'category' );

			if ( $category_obj && isset( $category_obj->slug ) && isset( $category_obj->term_id ) && is_numeric( $category_obj->term_id ) ) {

				$mobile_url .= '/#category/' . $category_obj->slug . '/' . $category_obj->term_id;

			}
		}
	}


	?>
	<link rel="alternate" media="only screen and (max-width: 640px)" href="<?php echo $mobile_url; ?>" />
	<?php

endif;
?>
