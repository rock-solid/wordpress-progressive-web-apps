<?php
	// get current screen
	$screen = get_current_screen();
    
	// set current page
	if ($screen->id !== '')
		if($screen->id == 'toplevel_page_wpmp-pro')
			$current_page = "What's new";
		else
			$current_page = str_replace('wp-mobile-pack-pro_page_wpmp-pro-','',$screen->id);
	else
		$current_page = '';
?>

<div class="form-box feedback">
    <h2>Give Us Your Feedback</h2>
    <div class="spacer-10"></div>
    <p>Help us improve WP Mobile Pack. We're eager to hear your feedback and be sure that we ALWAYS answer it.</p>
    <div class="spacer-10"></div>
    <form id="wpmp_feedback_form" name="wpmp_feedback_form" action="<?php echo admin_url('admin-ajax.php'); ?>?action=wpmp_pro_send_feedback" method="post">
        <input type="hidden" name="wpmp_feedback_page" id="wpmp_feedback_page" value="<?php echo ucfirst($current_page);?>" />
        
        <input type="text" name="wpmp_feedback_email" id="wpmp_feedback_email" placeholder="Your e-mail address" class="small" />
        <div id="error_email_container" class="field-message error"></div> 
        <div class="spacer-10"></div>
        
        <input type="text" name="wpmp_feedback_name" id="wpmp_feedback_name" placeholder="Your name" class="small" />
        <div id="error_name_container" class="field-message error"></div> 
        <div class="spacer-10"></div>
        
        <textarea name="wpmp_feedback_message" id="wpmp_feedback_message" placeholder="You're awesome, did you know that?" class="small"></textarea>
        <div id="error_message_container" class="field-message error"></div>
        <div class="spacer-10"></div>
        <a class="btn green smaller" href="javascript:void(0)" id="wpmp_feedback_send_btn">Send</a>
    </form>
</div>

<div class="ask-review">
    <div class="spacer-10"></div>
    <p>If you like Wordpress Mobile Pack, <a href="https://wordpress.org/support/view/plugin-reviews/wordpress-mobile-pack?filter=5#postform" target="_blank">please leave us a &#9733;&#9733;&#9733;&#9733;&#9733; rating</a>. A huge thank you in advance!</p>
</div>

<script type="text/javascript">
    if (window.WPMPJSInterface && window.WPMPJSInterface != null){
        jQuery(document).ready(function(){
            window.WPMPJSInterface.add("UI_feedback","WPMP_SEND_FEEDBACK",{'DOMDoc':window.document, 'feedbackEmail': '<?php echo WPMP_PRO_FEEDBACK_EMAIL;?>'}, window);
        });
    }
</script>