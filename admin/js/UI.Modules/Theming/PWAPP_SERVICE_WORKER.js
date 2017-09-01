/*****************************************************************************************************/
/*                                                                                                   */
/*                                'EDIT SERVICE WORKER SETTING'		                                 */
/*                                                                                                   */
/*****************************************************************************************************/

function PWAPP_SERVICE_WORKER(){

  var JSObject = this;

  this.type = 'pwapp_service_worker';

  this.form;
  this.DOMDoc;

  this.send_btn;


  /*****************************************************************************************************/
  /*                                                                                                   */
  /*                              FUNCTION INIT - called from PWAPPJSInterface                          */
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
  };


  /*****************************************************************************************************/
  /*                                                                                                   */
  /*                                  FUNCTION ADD BUTTONS ACTIONS                                     */
  /*                                                                                                   */
  /*****************************************************************************************************/
  this.addButtonsActions = function(){

    /*******************************************************/
    /*                     CHECKBOXES EVENT                */
    /*******************************************************/

    jQuery('#'+JSObject.type+'_installed_check',JSObject.DOMDoc).change(function() {

      // set the value on the dummy text field that will always be visible in the post data
      if (this.checked) {
        jQuery('#pwapp_option_service_worker_installed',JSObject.DOMDoc).val('1');
      } else {
        jQuery('#pwapp_option_service_worker_installed',JSObject.DOMDoc).val('0');
      }
    });

    /*******************************************************/
    /*                     SEND "BUTTON"                   */
    /*******************************************************/
    jQuery(this.send_btn).unbind('click');
    jQuery(this.send_btn).bind('click',function(){
      JSObject.disableButton(this);
      JSObject.sendData();
    });
    JSObject.enableButton(this.send_btn);

    jQuery('#'+JSObject.form.id,JSObject.DOMDoc).bind('keypress', function (e) {
      if (e.keyCode == 13) return false;
    });

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

    PWAPPJSInterface.Preloader.start();

    //disable form elements
    setTimeout(function(){
      var aElems = JSObject.form.elements;
      nElems = aElems.length;

      for (j=0; j<nElems; j++) {
        aElems[j].disabled = true;
      }
    },300);

    return true;
  };



  /*****************************************************************************************************/
  /*                                                                                                   */
  /*                              FUNCTION COMPLETE UPLOADING DATA                                     */
  /*                                                                                                   */
  /*****************************************************************************************************/
  this.completeUploadingData = function(response){

    jQuery('#'+JSObject.form.id,JSObject.DOMDoc).unbind('submit');
    jQuery('#'+JSObject.form.id,JSObject.DOMDoc).bind('submit',function(){return false;});

    // remove preloader
    PWAPPJSInterface.Preloader.remove(100);

    response = Boolean(Number(String(response)));

    if (response == true){

      // show message
      var message = 'Your app has been successfully modified!';
      PWAPPJSInterface.Loader.display({message: message});

    } else {

      var message = 'There was an error. Please reload the page and try again.';
      PWAPPJSInterface.Loader.display({message: message});
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

  };

}
