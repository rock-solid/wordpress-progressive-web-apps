/*****************************************************************************************************/
/*                                                                                                   */
/*                                    	'EDIT THEME COLORS AND FONTS'		                             */
/*                                                                                                   */
/*****************************************************************************************************/

function PWAPP_EDIT_THEME(){

  var JSObject = this;

  this.type = 'pwapp_edittheme';

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

    // save a reference to 'SEND' Button
    this.send_btn = jQuery('#'+this.type+'_send_btn',this.DOMDoc).get(0);

    // save a reference to the FORM and remove the default submit action
    this.form = this.DOMDoc.getElementById(this.type+'_form');

    // add actions to send, cancel, ... buttons
    this.addButtonsActions();

    if (this.form == null){
      return;
    }

    this.initCustomColors();
    this.initCustomSelects();
    this.initPreviewButton();
  };

  /*****************************************************************************************************/
  /*                                                                                                   */
  /*                                  INIT PREVIEW THEME BUTTONS                                       */
  /*                                                                                                   */
  /*****************************************************************************************************/
  this.initPreviewButton = function(){

    var snapshots = [
      {
        src: JSObject.baseThemesUrl + 'app2/snapshots/1-preview-cover.png',
        title: 'Home page cover'
      },
      {
        src: JSObject.baseThemesUrl + 'app2/snapshots/2-preview-categories.png',
        title: 'View posts'
      },
      {
        src: JSObject.baseThemesUrl + 'app2/snapshots/3-preview-menu.png',
        title: 'Categories menu'
      },
      {
        src: JSObject.baseThemesUrl + 'app2/snapshots/4-preview-article.png',
        title: 'Post details'
      }
    ];

    jQuery('#' + this.type + '_preview_2').magnificPopup({
      items: snapshots,
      gallery: {
        enabled: true
      },
      type: 'image' // this is default type
    });

    jQuery('#' + this.type + '_preview_2').hover(
      function(){
        jQuery(this).parent().siblings('div.text-preview').show();
      },
      function(){
        jQuery(this).parent().siblings('div.text-preview').hide();
      }
    );
  };


  /*****************************************************************************************************/
  /*                                                                                                   */
  /*                                  ENABLE COLOR PICKERS                                             */
  /*                                                                                                   */
  /*****************************************************************************************************/
  this.initCustomColors = function(){

    // enable color picker for the color inputs
    jQuery('input[name^="' + JSObject.type + '_customcolor"]').wpColorPicker();

    // toggle display for the custom colors section
    jQuery('input[name="' + JSObject.type + '_colorscheme"]').click(function(){
      if (jQuery(this).val() == 0){
        jQuery('.color-schemes-custom').show();
      } else {
        jQuery('.color-schemes-custom').hide();
      }
    });
  };


  /*****************************************************************************************************/
  /*                                                                                                   */
  /*                                  FUNCTION ENABLE CUSTOM SELECTS                                   */
  /*                                                                                                   */
  /*****************************************************************************************************/
  this.initCustomSelects = function(){
    jQuery('#' + JSObject.type + '_fontheadlines').selectBoxIt();
    jQuery('#' + JSObject.type + '_fontsubtitles').selectBoxIt();
    jQuery('#' + JSObject.type + '_fontparagraphs').selectBoxIt();
  };


  /*****************************************************************************************************/
  /*                                                                                                   */
  /*                                  FUNCTION ADD BUTTONS ACTIONS                                     */
  /*                                                                                                   */
  /*****************************************************************************************************/
  this.addButtonsActions = function(){

    /*******************************************************/
    /*                     SEND 'BUTTON'                   */
    /*******************************************************/
    jQuery(this.send_btn).unbind('click');
    jQuery(this.send_btn).bind('click',function(){
      JSObject.disableButton(this);
      JSObject.sendData();
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
  this.completeUploadingData = function(responseJSON){

    jQuery('#'+JSObject.form.id,JSObject.DOMDoc).unbind('submit');
    jQuery('#'+JSObject.form.id,JSObject.DOMDoc).bind('submit',function(){return false;});

    // remove preloader
    PWAPPJSInterface.Preloader.remove(100);

    var JSON = eval ('('+responseJSON+')');
    var response = Boolean(Number(String(JSON.status)));

    if (response == true && JSON.messages.length == 0){

      // show message
      var message = 'Your app has been successfully modified!';
      PWAPPJSInterface.Loader.display({message: message});

    } else {

      // show messages
      if (JSON.messages.length == 0) {

        var message = 'There was an error. Please reload the page and try again.';
        PWAPPJSInterface.Loader.display({message: message});

      } else {

        for (var i = 0; i < JSON.messages.length; i++ )
          PWAPPJSInterface.Loader.display({message: JSON.messages[i]});
      }
    }

    // enable form elements
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
