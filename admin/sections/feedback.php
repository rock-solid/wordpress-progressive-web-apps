<div class="form-box feedback">
    <h2>Give Us Your Feedback</h2>
    <div class="spacer-10"></div>
    <p>Help us improve this plugin. We're eager to hear your feedback and be sure that we ALWAYS answer it.</p>
    <div class="spacer-10"></div>
    <form id="pwapp_feedback_form" name="pwapp_feedback_form" action="<?php echo admin_url('admin-ajax.php'); ?>?action=pwapp_send_feedback" method="post">
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
        <a class="btn green smaller" href="javascript:void(0)" id="pwapp_feedback_send_btn">Send</a>
    </form>
</div>

<script type="text/javascript">
    if (window.PWAPPJSInterface && window.PWAPPJSInterface != null){
        jQuery(document).ready(function(){
            window.PWAPPJSInterface.add("UI_feedback","PWAPP_SEND_FEEDBACK",{'DOMDoc':window.document, 'feedbackEmail': '<?php echo PWAPP_FEEDBACK_EMAIL;?>'}, window);
        });
    }
</script>
