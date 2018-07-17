<div class="form-box feedback">
	<h2>Give Us Your Feedback</h2>
	<div class="spacer-10"></div>
	<p>Help us improve this plugin. We're eager to hear your feedback and be sure that we ALWAYS answer it.</p>
	<div class="spacer-10"></div>
	<form id="pwapp_feedback_form" name="pwapp_feedback_form" action="<?php echo admin_url( 'admin-ajax.php' ); ?>?action=pwapp_send_feedback" method="post">
		<input type="hidden" name="pwapp_feedback_page" id="pwapp_feedback_page" value="look_feel" />

		<input type="text" name="pwapp_feedback_email" id="pwapp_feedback_email" placeholder="Your e-mail address" class="small" />
		<div id="error_email_container" class="field-message error"></div>
		<div class="spacer-10"></div>

		<input type="text" name="pwapp_feedback_name" id="pwapp_feedback_name" placeholder="Your name" class="small" />
		<div id="error_name_container" class="field-message error"></div>
		<div class="spacer-10"></div>

		<textarea name="pwapp_feedback_message" id="pwapp_feedback_message" placeholder="You're awesome, did you know that?" class="small"></textarea>
		<div id="error_message_container" class="field-message error"></div>
		<div class="spacer-10"></div>

		<p>Webcrumbz will use the information you provide on this form to be in touch with you and to provide updates and marketing. Please let us know all the ways you would like to hear from us:</p>
		<div class="spacer-10"></div>
		<input type="checkbox" name="pwapp_feedback_permissions_email" id="pwapp_feedback_permissions_email" value="1" /> Email
		<div class="spacer-10"></div>
		<input type="checkbox" name="pwapp_feedback_permissions_directemail" id="pwapp_feedback_permissions_directemail" value="1" /> Direct Email
		<div class="spacer-10"></div>
		<a class="btn green smaller" href="javascript:void(0)" id="pwapp_feedback_send_btn">Send</a>
	</form>
</div>
<div class="spacer-10"></div>
<div class="ask-review">
	<p>You can change your mind at any time by contacting us at <?php echo PWAPP_CONTACT_EMAIL;?>. For more information about our privacy practices please visit <a href="https://appticles.com" target="_blank">our website</a>. By clicking the Send button, you agree that we may process your information in accordance with these terms.
</div>

<script type="text/javascript">
	if (window.PWAPPJSInterface && window.PWAPPJSInterface != null){
		jQuery(document).ready(function(){
			window.PWAPPJSInterface.add("UI_feedback","PWAPP_SEND_FEEDBACK",{'DOMDoc':window.document, 'feedbackEmail': '<?php echo PWAPP_FEEDBACK_EMAIL; ?>'}, window);
		});
	}
</script>
