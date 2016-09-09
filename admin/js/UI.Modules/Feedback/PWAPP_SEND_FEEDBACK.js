/*****************************************************************************************************/
/*                                                                                                   */
/*                                    	'SEND FEEDBACK'				                             	         */
/*                                                                                                   */
/*****************************************************************************************************/

function PWAPP_SEND_FEEDBACK(){

  var JSObject = this;

  this.type = 'pwapp_feedback';

  this.form;
  this.DOMDoc;
  this.feedbackEmail;

  /*****************************************************************************************************/
  /*                                                                                                   */
  /*                              FUNCTION INIT - called from PWAPPJSInterface                         */
  /*                                                                                                   */
  /*****************************************************************************************************/
  this.init = function(){

    // save a reference to PWAPPJSInterface Object
    PWAPPJSInterface = window.parent.PWAPPJSInterface;

    // save a reference to "SEND" Button
    this.send_btn = jQuery('#'+this.type+'_send_btn',this.DOMDoc).get(0);

    // save a reference to the FORM and remove the default submit action
    this.form = this.DOMDoc.getElementById(this.type+'_form');

    // add actions to send, cancel, ... buttons
    this.addButtonsActions();

    if (this.form == null){
      return;
    }

    // custom validation for FORM's inputs
    this.initValidation();
  };

  /*****************************************************************************************************/
  /*                                                                                                   */
  /*                                  FUNCTION INIT VALIDATION                                         */
  /*                                                                                                   */
  /*****************************************************************************************************/
  this.initValidation = function(){

      // this is the object that handles the form validations
    this.validator = jQuery('#'+this.form.id, this.DOMDoc).validate({
      rules: {
        pwapp_feedback_email: {
          required    : true,
          email       : true
        },
        pwapp_feedback_name: {
          required    : true
        },
        pwapp_feedback_message: {
          required    : true
        }
      },

      // the errorPlacement has to take the table layout into account
      // all the errors must be handled by containers/divs with custom ids: Ex. "error_fullname_container"
      errorPlacement: function(error, element) {

        var split_name = element[0].id.split("_");
        var id = (split_name.length > 1) ? split_name[ split_name.length - 1] : split_name[0];
        var errorContainer = jQuery("#error_"+id+"_container",JSObject.DOMDoc);
        error.appendTo( errorContainer );
      },

      errorElement: 'span'
    });

    /*************  PLACEGOLDERS *************/

    var $NameInput = jQuery('#'+this.type+'_name',this.DOMDoc);
    $NameInput.data('holder', $NameInput.attr('placeholder'));

    $NameInput.focusin(function(){jQuery(this).attr('placeholder','');}).focusout(function(){jQuery(this).attr('placeholder',jQuery(this).data('holder'));});


    var $EmailInput = jQuery('#'+this.type+'_email',this.DOMDoc);
    $EmailInput.data('holder',$EmailInput.attr('placeholder'));

    $EmailInput.focusin(function(){jQuery(this).attr('placeholder','');}).focusout(function(){jQuery(this).attr('placeholder',jQuery(this).data('holder'));});


    var $MessageInput = jQuery('#'+this.type+'_message',this.DOMDoc);
    $MessageInput.data('holder',$MessageInput.attr('placeholder'));

    $MessageInput.focusin(function(){jQuery(this).attr('placeholder','');}).focusout(function(){jQuery(this).attr('placeholder',jQuery(this).data('holder'));});

    /*******************************************/
  };


  /*****************************************************************************************************/
  /*                                                                                                   */
  /*                                  FUNCTION ADD BUTTONS ACTIONS                                     */
  /*                                                                                                   */
  /*****************************************************************************************************/
  this.addButtonsActions = function(){

    /*******************************************************/
    /*                     SEND "BUTTON"                   */
    /*******************************************************/
    jQuery(this.send_btn).unbind('click');
    jQuery(this.send_btn).bind('click',function(){
      JSObject.disableButton(this);
      JSObject.validate();
    });
    JSObject.enableButton(this.send_btn);
  };

  /*****************************************************************************************************/
  /*                                                                                                   */
  /*                                 FUNCTION ENABLE BUTTON                                            */
  /*                                                                                                   */
  /*****************************************************************************************************/
  this.enableButton = function(btn){
    jQuery(btn).css('cursor','pointer');
    jQuery(btn).animate({opacity:1},100);
  };

  /*****************************************************************************************************/
  /*                                                                                                   */
  /*                                 FUNCTION DISABLE BUTTON                                           */
  /*                                                                                                   */
  /*****************************************************************************************************/
  this.disableButton = function(btn){
    jQuery(btn).unbind('click');
    jQuery(btn).animate({opacity:0.4},100);
    jQuery(btn).css('cursor','default');
  };


  /*****************************************************************************************************/
  /*                                                                                                   */
  /*                                 FUNCTION SCROLL TO FIRST ERROR                                    */
  /*                                                                                                   */
  /*****************************************************************************************************/
  this.scrollToError = function(yCoord){
    var container = jQuery('html,body', JSObject.DOMDoc);
    var scrollTop = parseInt(jQuery('html,body').scrollTop()) || parseInt(jQuery('body').scrollTop());
    var containerHeight = container.get(0).clientHeight;
    var top = parseInt(container.offset().top);

    if (yCoord < scrollTop){
      jQuery(container).animate({scrollTop: yCoord-20 }, 1000);
    }
    else if (yCoord > scrollTop + containerHeight){
      jQuery(container).animate({scrollTop: scrollTop + containerHeight }, 1000);
    }
  };


  /*****************************************************************************************************/
  /*                                                                                                   */
  /*                                 FUNCTION VALIDATE INFORMATION                                     */
  /*                                                                                                   */
  /*****************************************************************************************************/
  this.validate = function(){

    jQuery(this.form).validate().form();

    // y coordinates of error inputs
    var arr_errorsYCoord = [];

    // find the y coordinate for the errors
    for (var name in this.validator.invalid){
      var input = jQuery(this.form[name]);
      arr_errorsYCoord.push(input.offset().top);
    }

    // if there are no errors from syntax point of view, then send data
    if (arr_errorsYCoord.length == 0){
      JSObject.sendData();
    }
    //move container(div) scroll to the first error
    else{
      arr_errorsYCoord.sort(function(a, b){ return (a-b); });
      JSObject.scrollToError(arr_errorsYCoord[0]);

      // add actions to send, cancel, ... buttons. At this moment the buttons are disabled.
      JSObject.addButtonsActions();
    }
  };


  /*****************************************************************************************************/
  /*                                                                                                   */
  /*                       FUNCTION SUBMIT FORM  THROUGH an IFRAME as target                           */
  /*                                                                                                   */
  /*****************************************************************************************************/
  this.submitForm = function(){
    return PWAPPJSInterface.AjaxUpload.dosubmit(JSObject.form, {'onStart' : JSObject.startUploadingData, 'onComplete' : JSObject.completeUploadingData});
  };


  /*****************************************************************************************************/
  /*                                                                                                   */
  /*                                      FUNCTION SEND DATA                                           */
  /*                                                                                                   */
  /*****************************************************************************************************/
  this.sendData = function(){

    jQuery('#'+this.form.id,this.DOMDoc).unbind('submit');
    jQuery('#'+this.form.id,this.DOMDoc).bind('submit',function(){JSObject.submitForm();});
    jQuery('#'+this.form.id,this.DOMDoc).submit();

    JSObject.disableButton(JSObject.send_btn);
  };


  /*****************************************************************************************************/
  /*                                                                                                   */
  /*                                FUNCTION START UPLOADING DATA                                      */
  /*                                                                                                   */
  /*****************************************************************************************************/
  this.startUploadingData = function(){
    // make something useful before submit (onStart)

    //add preloader
    var msg = 'Please wait...';
    PWAPPJSInterface.Preloader.start({message: msg});

    //disable form elements
    setTimeout(function(){
      var aElems = JSObject.form.elements;
      nElems = aElems.length;

      for (j=0; j<nElems; j++) {
        aElems[j].disabled = true;
      }
    },300);

    jQuery('.feedback',JSObject.DOMDoc).animate({opacity:0.4},300);

    return true;
  };



  /*****************************************************************************************************/
  /*                                                                                                   */
  /*                              FUNCTION COMPLETE UPLOADING DATA                                     */
  /*                                                                                                   */
  /*****************************************************************************************************/
  this.completeUploadingData = function(response) {

    jQuery('#'+JSObject.form.id,JSObject.DOMDoc).unbind('submit');
    jQuery('#'+JSObject.form.id,JSObject.DOMDoc).bind('submit',function(){return false;});

    response = Boolean(Number(String(response)));

    // remove preloader
    PWAPPJSInterface.Preloader.remove(100);

    if (response){

      // success message
      var message = 'Thank you for your message, we\'ll be in touch with you soon!';
      PWAPPJSInterface.Loader.display({message: message});

      JSObject.form.reset();

    } else {

      // show message
      var message = 'We were unable to send the message because your mail() function is probably disabled. Please send your message directly to <a href="mailto:'+JSObject.feedbackEmail+'">'+JSObject.feedbackEmail+'</a>.';
      PWAPPJSInterface.Loader.display({message: message, time: 15000});
    }

    //enable form elements
    setTimeout(function(){
      var aElems = JSObject.form.elements;
      nElems = aElems.length;
      for (j=0; j<nElems; j++) {
        aElems[j].disabled = false;
      }
    },300);

    //enable buttons
    JSObject.addButtonsActions();

    jQuery('.feedback',JSObject.DOMDoc).animate({opacity:1},300);
  };
}
