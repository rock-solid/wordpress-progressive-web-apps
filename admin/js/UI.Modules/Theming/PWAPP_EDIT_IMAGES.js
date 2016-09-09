/*****************************************************************************************************/
/*                                                                                                   */
/*                                    	'EDIT APP ICON AND LOGO'		                             */
/*                                                                                                   */
/*****************************************************************************************************/

function PWAPP_EDIT_IMAGES(){

  var JSObject = this;

  this.type = 'pwapp_editimages';

  this.form;
  this.DOMDoc;

  this.send_btn;
  this.deletingIcon = false;
  this.deletingLogo = false;


  /*****************************************************************************************************/
  /*                                                                                                   */
  /*                              FUNCTION INIT - called from PWAPPJSInterface                         */
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

    // custom validation for FORM's inputs
    this.initValidation();
  };


  /*****************************************************************************************************/
  /*                                                                                                   */
  /*                                  FUNCTION INIT VALIDATION                                         */
  /*                                                                                                   */
  /*****************************************************************************************************/
  this.initValidation = function(){

    /*******************************************************/
    /*                    VALIDATION RULES                 */
    /*******************************************************/

    // this is the object that handles the form validations
    this.validator = jQuery('#'+this.form.id, this.DOMDoc).validate({
      rules: {
        pwapp_editimages_icon : {
          accept		: 'png|jpg|jpeg|gif'
        },
        pwapp_editimages_logo : {
          accept		: 'png'
        },
      },
      messages: {
        pwapp_editimages_icon : {
          accept		: 'Please add a png, gif or jpeg image.'
        },
        pwapp_editimages_logo : {
          accept		: 'Please add a png image format with a transparent background.',
        },
      },

      // the errorPlacement has to take the table layout into account
      // all the errors must be handled by containers/divs with custom ids: Ex. 'error_fullname_container'
      errorPlacement: function(error, element) {
        var split_name = element[0].id.split('_');
        var id = (split_name.length > 1) ? split_name[ split_name.length - 1] : split_name[0];
        var errorContainer = jQuery('#error_'+id+'_container',JSObject.DOMDoc);
        error.appendTo( errorContainer );
      },

      errorElement: 'span'
    });

    /*******************************************************/
    /*                     INPUT 'ICON'                    */
    /*******************************************************/

    // this is a hack for chrome and safari
    var $Icon = jQuery('#'+this.type+'_icon',this.DOMDoc);
    var $RemoveIconLink = jQuery('#'+this.type+'_icon_removenew',this.DOMDoc);

    $Icon.bind('change',function(){
      $Icon.focus();
      $Icon.blur();
      if (this.files[0])
        jQuery('#fakefileicon').val( this.files[0].name );
      $RemoveIconLink.css('display', 'block');
    });

    $RemoveIconLink.bind('click',function(){
      jQuery('#fakefileicon').val('');

      $Icon.val('');
      jQuery(JSObject.form).validate().element( '#' + JSObject.type + '_icon' );

      $RemoveIconLink.css('display', 'none');
    });

    /*******************************************************/
    /*                     EDIT ICON LINK                  */
    /*******************************************************/

    // attach click functions for the edit icon link
    var $EditIconLink = jQuery('.'+this.type+'_changeicon',this.DOMDoc);
    if ($EditIconLink.length > 0){

      $EditIconLink.click(
        function(){

          // if the file field is hidden
          if (jQuery('.'+JSObject.type+'_uploadicon',JSObject.DOMDoc).css('display') == 'none') {

            // reset file field value
            $Icon.val('');
            jQuery(JSObject.form).validate().element( '#' + JSObject.type + '_icon' );
            jQuery('#fakefileicon').val('')

            // hide the 'remove new icon' link
            $RemoveIconLink.css('display', 'none');

            // show upload icon field
            jQuery('.'+JSObject.type+'_uploadicon',JSObject.DOMDoc).show();

            // show cancel button
            if (jQuery('#'+JSObject.type+'_currenticon',JSObject.DOMDoc).attr('src') != '')
              jQuery('.'+JSObject.type+'_changeicon_cancel',JSObject.DOMDoc).show();

            // hide current icon
            if (jQuery('.'+JSObject.type+'_iconcontainer',JSObject.DOMDoc).css('display') == 'block')
              jQuery('.'+JSObject.type+'_iconcontainer',JSObject.DOMDoc).hide();
          }
        }
      );
    }

    /*******************************************************/
    /*                 CANCEL EDIT ICON LINK               */
    /*******************************************************/

    // attach click functions for the cancel edit icon link
    var $CancelEditIconLink = jQuery('.'+this.type+'_changeicon_cancel a',this.DOMDoc);
    if ($CancelEditIconLink.length > 0){

      $CancelEditIconLink.click(
        function(){

          // if the file field is visible
          if (jQuery('.'+JSObject.type+'_uploadicon',JSObject.DOMDoc).css('display') == 'block') {

            // reset file field value
            $Icon.val('');
            jQuery(JSObject.form).validate().element( '#' + JSObject.type + '_icon' );
            jQuery('#fakefileicon').val('');

            // hide upload icon field
            jQuery('.'+JSObject.type+'_uploadicon',JSObject.DOMDoc).hide();

            // hide cancel button
            jQuery(this).parent().hide();

            // display current icon (if it exists)
            if (jQuery('.'+JSObject.type+'_iconcontainer',JSObject.DOMDoc).css('display') == 'none' &&
              jQuery('#'+JSObject.type+'_currenticon',JSObject.DOMDoc).attr('src') != '')

              jQuery('.'+JSObject.type+'_iconcontainer',JSObject.DOMDoc).show();
          }
        }
      );
    }

    /*******************************************************/
    /*               DELETE ICON LINK               	   */
    /*******************************************************/

    // attach click functions for the delete icon link
    var $DeleteIconLink = jQuery('.'+this.type+'_deleteicon',this.DOMDoc);

    if ($DeleteIconLink.length > 0){

      var href = $DeleteIconLink.get(0).href;
      $DeleteIconLink.get(0).href = 'javascript:void(0);'

      $DeleteIconLink.click(
        function(){

          var isConfirmed = confirm('This app icon is used when the app is added to the homescreen. Are you sure you want to remove it?');

          if (isConfirmed) {

            jQuery.get(
              ajaxurl,
              {
                'action': 'pwapp_editimages',
                'type':   'delete',
                'source': 'icon'
              },
              function(responseJSON){

                var JSON = eval ('('+responseJSON+')');
                var response = Boolean(Number(String(JSON.status)));

                JSObject.deletingIcon = false;

                if (response == true) {

                  // remove image url
                  jQuery('#'+JSObject.type+'_currenticon',JSObject.DOMDoc).attr('src', '');

                  // trigger the display of the upload field
                  $EditIconLink.trigger('click');

                  // success message
                  var message = 'The app icon has been removed.';
                  PWAPPJSInterface.Loader.display({message: message});

                } else {

                  // error message
                  var message = 'There was an error. Please try again in few seconds.';
                  PWAPPJSInterface.Loader.display({message: message});

                }
              }
            );
          }
        }
      );
    }

    /*******************************************************/
    /*                     INPUT 'LOGO'                    */
    /*******************************************************/

    // this is a hack for chrome and safari
    var $Logo = jQuery('#'+this.type+'_logo',this.DOMDoc);
    var $RemoveLogoLink = jQuery('#'+this.type+'_logo_removenew',this.DOMDoc);

    $Logo.bind('change',function(){
      $Logo.focus();
      $Logo.blur();
      if (this.files[0])
        jQuery('#fakefilelogo').val( this.files[0].name );
      $RemoveLogoLink.css('display', 'block');
    });

    $RemoveLogoLink.bind('click',function(){
      jQuery('#fakefilelogo').val('');

      $Logo.val('');
      jQuery(JSObject.form).validate().element( '#' + JSObject.type + '_logo' );

      $RemoveLogoLink.css('display', 'none');
    });


    /*******************************************************/
    /*                     EDIT LOGO LINK                  */
    /*******************************************************/

    // attach click functions for the edit logo link
    var $EditLogoLink = jQuery('.'+this.type+'_changelogo',this.DOMDoc);
    if ($EditLogoLink.length > 0){

      $EditLogoLink.click(
        function(){

          // if the file field is hidden
          if (jQuery('.'+JSObject.type+'_uploadlogo',JSObject.DOMDoc).css('display') == 'none') {

            // reset file field value
            $Logo.val('');
            jQuery(JSObject.form).validate().element( '#' + JSObject.type + '_logo' );
            jQuery('#fakefilelogo').val('')

            // hide the 'remove new logo' link
            $RemoveLogoLink.css('display', 'none');

            // show upload logo field
            jQuery('.'+JSObject.type+'_uploadlogo',JSObject.DOMDoc).show();

            // show cancel button
            if (jQuery('#'+JSObject.type+'_currentlogo',JSObject.DOMDoc).css('background-image') != 'none')
              jQuery('.'+JSObject.type+'_changelogo_cancel',JSObject.DOMDoc).show();

            // hide current logo
            if (jQuery('.'+JSObject.type+'_logocontainer',JSObject.DOMDoc).css('display') == 'block')
              jQuery('.'+JSObject.type+'_logocontainer',JSObject.DOMDoc).hide();
          }
        }
      );
    }

    /*******************************************************/
    /*                 CANCEL EDIT LOGO LINK               */
    /*******************************************************/

    // attach click functions for the cancel edit logo link
    var $CancelEditLogoLink = jQuery('.'+this.type+'_changelogo_cancel a',this.DOMDoc);
    if ($CancelEditLogoLink.length > 0){

      $CancelEditLogoLink.click(
        function(){

          // if the file field is visible
          if (jQuery('.'+JSObject.type+'_uploadlogo',JSObject.DOMDoc).css('display') == 'block') {

            // reset file field value
            $Logo.val('');
            jQuery(JSObject.form).validate().element( '#' + JSObject.type + '_logo' );
            jQuery('#fakefilelogo').val('');

            // hide upload logo field
            jQuery('.'+JSObject.type+'_uploadlogo',JSObject.DOMDoc).hide();

            // hide cancel button
            jQuery(this).parent().hide();

            // display current logo (if it exists)
            if (jQuery('.'+JSObject.type+'_logocontainer',JSObject.DOMDoc).css('display') == 'none' &&
              jQuery('#'+JSObject.type+'_currentlogo',JSObject.DOMDoc).css('background-image') != 'none')

              jQuery('.'+JSObject.type+'_logocontainer',JSObject.DOMDoc).show();
          }
        }
      );
    }

    /*******************************************************/
    /*               DELETE LOGO LINK               	   */
    /*******************************************************/

    // attach click functions for the delete logo link
    var $DeleteLogoLink = jQuery('.'+this.type+'_deletelogo',this.DOMDoc);

    if ($DeleteLogoLink.length > 0){

      var href = $DeleteLogoLink.get(0).href;
      $DeleteLogoLink.get(0).href = 'javascript:void(0);';

      $DeleteLogoLink.click(
        function(){

          var isConfirmed = confirm('This app logo is used on the cover of your app. Are you sure you want to remove it?');

          if (isConfirmed) {

            jQuery.get(
              ajaxurl,
              {
                'action': 'pwapp_editimages',
                'type':   'delete',
                'source': 'logo'
              },
              function(responseJSON){

                var JSON = eval ('('+responseJSON+')');
                var response = Boolean(Number(String(JSON.status)));

                JSObject.deletingIcon = false;

                if (response == true) {

                  // remove image url
                  jQuery('#'+JSObject.type+'_currentlogo',JSObject.DOMDoc).css('background-image', 'none');

                  // trigger the display of the upload field
                  $EditLogoLink.trigger('click');

                  // success message
                  var message = 'The app logo has been removed.';
                  PWAPPJSInterface.Loader.display({message: message});

                } else {

                  // error message
                  var message = 'There was an error. Please try again in few seconds.';
                  PWAPPJSInterface.Loader.display({message: message});

                }
              }
            );
          }
        }
      );
    }
  };

  /*****************************************************************************************************/
  /*                                                                                                   */
  /*                                  FUNCTION DISPLAY NEW IMAGE                                       */
  /*                                                                                                   */
  /*****************************************************************************************************/
  this.displayImage = function(type, path){

    if (type == 'icon') {

      // reset file field value
      jQuery('#'+JSObject.type+'_icon',JSObject.DOMDoc).val('');
      jQuery('#fakefileicon').val('');

      // hide upload icon field
      jQuery('.'+JSObject.type+'_uploadicon',JSObject.DOMDoc).hide();

      // hide cancel button
      jQuery('.'+JSObject.type+'_changeicon_cancel',JSObject.DOMDoc).hide();

            // add new path in the src attribute
      jQuery('#'+JSObject.type+'_currenticon',JSObject.DOMDoc).attr('src', path);

      // display image container
      jQuery('.'+JSObject.type+'_iconcontainer',JSObject.DOMDoc).css('display', 'block');

    } else if (type == 'logo') {

      // reset file field value
      jQuery('#'+JSObject.type+'_logo',JSObject.DOMDoc).val('');
      jQuery('#fakefilelogo').val('');

      // hide upload logo field
      jQuery('.'+JSObject.type+'_uploadlogo',JSObject.DOMDoc).hide();

      // hide cancel button
      jQuery('.'+JSObject.type+'_changelogo_cancel',JSObject.DOMDoc).hide();

            // add new path in the src attribute
      jQuery('#'+JSObject.type+'_currentlogo',JSObject.DOMDoc).css('background-image', 'url('+path+')');

      // display image container
      jQuery('.'+JSObject.type+'_logocontainer',JSObject.DOMDoc).css('display', 'block');
    }
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
  }


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
      var $input = jQuery(this.form[name]);
      arr_errorsYCoord.push($input.offset().top);
    }

    // if there are no errors from syntax point of view, then send data
    if (arr_errorsYCoord.length == 0){
      this.sendData();
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

    if (JSON.uploaded_icon != undefined)
      JSObject.displayImage('icon', JSON.uploaded_icon)

    if (JSON.uploaded_logo != undefined)
      JSObject.displayImage('logo', JSON.uploaded_logo)

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
