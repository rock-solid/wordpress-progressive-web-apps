/*****************************************************************************************************/
/*                                                                                                   */
/*                                    	'EDIT COVER'					                             */
/*                                                                                                   */
/*****************************************************************************************************/

function PWAPP_EDIT_COVER(){

  var JSObject = this;

  this.type = 'pwapp_editcover';

  this.form;
  this.DOMDoc;

  this.send_btn;
  this.deletingCover = false;

  /*****************************************************************************************************/
  /*                                                                                                   */
  /*                              FUNCTION INIT - called from PWAPPJSInterface                           */
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
        pwapp_editcover_cover : {
          accept		: 'png|jpg|jpeg|gif'
        }
      },
      messages: {
        pwapp_editcover_cover : {
          accept		: 'Please add a png, gif or jpeg image.'
        }
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
    /*                     INPUT 'COVER'                   */
    /*******************************************************/

    // this is a hack for chrome and safari
    var $Cover = jQuery('#'+this.type+'_cover',this.DOMDoc);
    var $RemoveCoverLink = jQuery('#'+this.type+'_cover_removenew',this.DOMDoc);

    $Cover.bind('change',function(){
      $Cover.focus();
      $Cover.blur();
      if (this.files[0]){
        jQuery('#fakefilecover').val( this.files[0].name );
      }
      $RemoveCoverLink.css('display', 'block');
    });

    $RemoveCoverLink.bind('click',function(){

      jQuery('#fakefilecover').val('');

      $Cover.val('');
      jQuery(JSObject.form).validate().element( '#' + JSObject.type + '_cover' );

      $RemoveCoverLink.css('display', 'none');
    });

    /*******************************************************/
    /*                     EDIT ICON LINK                  */
    /*******************************************************/

    // attach click functions for the edit cover link
    var $EditCoverLink = jQuery('.'+this.type+'_changecover',this.DOMDoc);
    if ($EditCoverLink.length > 0){

      $EditCoverLink.click(
        function(){

          // if the file field is hidden
          if (jQuery('.'+JSObject.type+'_uploadcover',JSObject.DOMDoc).css('display') == 'none') {

            // reset file field value
            $Cover.val('');
            jQuery(JSObject.form).validate().element( '#' + JSObject.type + '_cover' );
            jQuery('#fakefilecover').val('')

            // hide the 'remove new cover' link
            $RemoveCoverLink.css('display', 'none');

            // show upload cover field
            jQuery('.'+JSObject.type+'_uploadcover',JSObject.DOMDoc).show();

            // show cancel button
            if (jQuery('#'+JSObject.type+'_currentcover',JSObject.DOMDoc).css('background-image') != 'none')
              jQuery('.'+JSObject.type+'_changecover_cancel',JSObject.DOMDoc).show();

            // hide current cover
            if (jQuery('.'+JSObject.type+'_covercontainer',JSObject.DOMDoc).css('display') == 'block')
              jQuery('.'+JSObject.type+'_covercontainer',JSObject.DOMDoc).hide();
          }
        }
      );
    }

    /*******************************************************/
    /*                 CANCEL EDIT ICON LINK               */
    /*******************************************************/

    // attach click functions for the cancel edit cover link
    var $CancelEditCoverLink = jQuery('.'+this.type+'_changecover_cancel a',this.DOMDoc);
    if ($CancelEditCoverLink.length > 0){

      $CancelEditCoverLink.click(
        function(){

          // if the file field is visible
          if (jQuery('.'+JSObject.type+'_uploadcover',JSObject.DOMDoc).css('display') == 'block') {

            // reset file field value
            $Cover.val('');
            jQuery(JSObject.form).validate().element( '#' + JSObject.type + '_cover' );
            jQuery('#fakefilecover').val('');

            // hide upload cover field
            jQuery('.'+JSObject.type+'_uploadcover',JSObject.DOMDoc).hide();

            // hide cancel button
            jQuery(this).parent().hide();

            // display current logo (if it exists)
            if (jQuery('.'+JSObject.type+'_covercontainer',JSObject.DOMDoc).css('display') == 'none' &&
              jQuery('#'+JSObject.type+'_currentcover',JSObject.DOMDoc).css('background-image') != 'none')

              jQuery('.'+JSObject.type+'_covercontainer',JSObject.DOMDoc).show();
          }
        }
      );
    }

    /*******************************************************/
    /*               DELETE ICON LINK               	   */
    /*******************************************************/

    // attach click functions for the delete cover link
    var $DeleteCoverLink = jQuery('.'+this.type+'_deletecover',this.DOMDoc);

    if ($DeleteCoverLink.length > 0){

      var href = $DeleteCoverLink.get(0).href;
      $DeleteCoverLink.get(0).href = 'javascript:void(0);';

      $DeleteCoverLink.click(
        function(){

          var isConfirmed = confirm('Are you sure you want to remove the app cover?');

          if (isConfirmed) {

            jQuery.get(
              ajaxurl,
              {
                'action': 'pwapp_editimages',
                'type':   'delete',
                'source': 'cover'
              },
              function(responseJSON){

                var JSON = eval ('('+responseJSON+')');
                var response = Boolean(Number(String(JSON.status)));

                JSObject.deletingCover = false;

                if (response == true) {

                  // remove image url
                  jQuery('#'+JSObject.type+'_currentcover',JSObject.DOMDoc).css('background-image', 'none');

                  // trigger the display of the upload field
                  $EditCoverLink.trigger('click');

                  // success message
                  var message = 'The app cover has been removed.';
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

    // reset file field value
    jQuery('#'+JSObject.type+'_cover',JSObject.DOMDoc).val('');
    jQuery('#fakefilecover').val('')

    // hide upload cover field
    jQuery('.'+JSObject.type+'_uploadcover',JSObject.DOMDoc).hide();

    // hide cancel button
    jQuery('.'+JSObject.type+'_changecover_cancel',JSObject.DOMDoc).hide();

    // add new path in the background attribute
    jQuery('#'+JSObject.type+'_currentcover',JSObject.DOMDoc).css('background-image', 'url('+path+')');

    // display image container
    jQuery('.'+JSObject.type+'_covercontainer',JSObject.DOMDoc).css('display', 'block');
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
  }


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

    if (JSON.uploaded_cover != undefined)
      JSObject.displayImage('cover', JSON.uploaded_cover)

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
