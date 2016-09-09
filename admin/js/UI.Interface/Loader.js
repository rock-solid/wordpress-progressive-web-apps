/**
 *
 * @langversion JAVASCRIPT
 *
 * http://www.appticles.com
 * alexandra@appticles.com
 *
 */


/*****************************************************************************************************/
/*                                                                                                   */
/*                                         PRELOADER CLASS                                           */
/*                                                                                                   */
/*****************************************************************************************************/
function PWAPPPreloader(){

  var JSObject = this;

  this.defaultParams = {width: 320,
            height: 80,
            message: 'Please wait...'};


  /*****************************************************************************************/
  /*                                      START PRELOADER                                  */
  /*****************************************************************************************/
  /**
   * start the loader
   * method type: LOCAL
   * params: @params : a JSON with new params like defaultParams
   */
  this.start = function(params){

    this.defaultParams = jQuery.extend({}, this.defaultParams, params);

    jQuery('#preloader_container').remove();
    jQuery('body *:first',document).before('<div id="preloader_container" style="position:fixed; z-index:999998; display:none;"><div class="preloader"></div><div style="position:fixed; background: #000;"><div id="preloader_table" align="center" style="font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#FFF; padding:10px;">'+this.defaultParams.message+'<br><br><img src="'+PWAPPJSInterface.localpath+'admin/images/loading_animation.gif" /></div></div></div>');

    var preloader_container = jQuery('#preloader_container');
    var table = jQuery('#preloader_table',preloader_container);
    var preloading_bg = jQuery('.preloader',preloader_container);

    var w = this.defaultParams.width;
    var h = this.defaultParams.height;

    table.width(w-20);
    table.height(h-20);
    table.parent().width(w);
    table.parent().height(h);
    preloading_bg.width(w);
    preloading_bg.height(h);
    preloading_bg.parent().width(w);
    preloading_bg.parent().height(h);

    var newW = (-w/2)+'px';
    var newH = (-h/2)+'px';

    preloader_container.css({'top':'50%' , 'left':'50%' , 'margin-left': newW, 'margin-top':newH}).fadeIn(500);
    preloader_container.css({'top':'50%' , 'left':'50%' , 'margin-left': newW, 'margin-top':newH}).fadeIn(500);
  };


  /*****************************************************************************************/
  /*                                      UPDATE PRELOADER                                 */
  /*****************************************************************************************/
  /**
   * update the preloader with a new message
   * method type: LOCAL
   * params: @msg : the new message to display
   */
  this.update = function(msg){

    var preloader_container = jQuery('#preloader_container');
    var table = jQuery('#preloader_table',preloader_container);
    table.get(0).rows[0].cells[0].innerHTML = msg;

  };

  /*****************************************************************************************/
  /*                                      REMOVE PRELOADER                                 */
  /*****************************************************************************************/
  /**
   * remove the loader from the stage
   * method type: LOCAL
   * params: @time : the time for the loader to disappear
   */
  this.remove = function(time){

    if (jQuery('#loading_container') != null){
      jQuery('#loading_container').remove();
    }

    if (time == null){
      time = 100;
    }

    var preloader_container = jQuery('#preloader_container');
    preloader_container.stop();

    preloader_container.fadeOut({duration: time},function(){ preloader_container.remove(); });
  };
}

/*****************************************************************************************************/
/*                                                                                                   */
/*                                            MESSAGE CLASS                                          */
/*                                                                                                   */
/*****************************************************************************************************/
function PWAPPMessage(){
  var JSObject = this;

  this.defaultParams = {
              width: 				450,
              time: 				8000,
              speed:				500,
              delay:				500,
              message:			"",
              closeFunction: 		null};	// for redirect purpose...., after the message is closed

  this.parentContainer;				// the message container (div element)
  this.container;



  /*****************************************************************************************/
  /*                                      VIEW MESSAGE                                     */
  /*****************************************************************************************/
  /**
   * view message
   * method type: LOCAL
   * params: @params : a JSON with new params like defaultParams
   */
  this.view = function(params){

    this.defaultParams = jQuery.extend({}, this.defaultParams, params);

    // number of messages current displayed
    var no_messages = jQuery('div[id="pwappMessageBox"]', this.parentContainer).get().length;

    // add message box to the parent container
    var html = '<div id="pwappMessageBox" class="loader" style="display:none;">';
      html += 	'<div id="pwappBoxTable" align="left" style="background: #fefcd4;">';
      html += 		'<div style="float:right; height:15px; padding-right:5px; padding-top:5px"><img id="pwappCloseMsg" src="'+PWAPPJSInterface.localpath+'admin/images/btn_close_msg.png" border="0" style="cursor:pointer" /></div>';
      html += 		'<div align="center" style="font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#000; padding:15px;">' + this.defaultParams.message +'</div>';
      html += 	'</div>';
      html += '</div>';
      html += '<div style="height:5px;"></div>';

    jQuery(this.parentContainer).append(html);

    this.container = jQuery('div[id="pwappMessageBox"]:eq('+no_messages+')', this.parentContainer);
    var table = jQuery('#pwappBoxTable',this.container);
    var close_btn = jQuery('#pwappCloseMsg',table);

    // set message table width
    var w = this.defaultParams.width;
    table.width(w);
    table.parent().width(w);

    // open message box
    this.container.delay(this.defaultParams.delay).slideDown(this.defaultParams.speed);


    // start message timer
    this.container.unbind("countTimer");
    this.container.bind("countTimer",function(){

      var seconds = JSObject.defaultParams.time;

      //wait until elapsed time reaches 0 seconds
      if (seconds/1000 - 1 >= 1){
        JSObject.defaultParams.time -= 1000;
      }
      else{
        jQuery(this).unbind("countTimer");
        clearInterval(jQuery(this).data("timerInterval"));
        jQuery(this).removeData("timerInterval");

        //remove the message from the stage
        JSObject.remove();

        if (typeof JSObject.defaultParams.closeFunction == "function") JSObject.defaultParams.closeFunction();
      }

    })

    this.container.data("timerInterval", setInterval(function(){ JSObject.container.trigger("countTimer")},1000));


    // close button 'click' action
    close_btn.unbind("click");
    close_btn.bind("click",function(){

      jQuery(this).unbind("click");

      //remove the message from the stage
      JSObject.remove();
    })
  }


  /*****************************************************************************************/
  /*                                      REMOVE MESSAGE                                   */
  /*****************************************************************************************/
  /**
   * remove message
   * method type: LOCAL
   * params: no params
   */
  this.remove = function(){

    // close message box
    JSObject.container.slideUp(JSObject.defaultParams.speed, function(){
                                      var spacerDiv = jQuery(this).next();

                                      jQuery(this).remove();

                                      // close the bottom spacer
                                      spacerDiv.slideUp(200, function(){ jQuery(this).remove(); })
                                      });
  }

}


/*****************************************************************************************************/
/*                                                                                                   */
/*                                            LOADER CLASS                                           */
/*                                                                                                   */
/*****************************************************************************************************/
function PWAPPLoader(){

  var JSObject = this;
  this.arr_messages = [];		// an array with messages objects


  /*****************************************************************************************/
  /*                                      INIT LOADER                                      */
  /*****************************************************************************************/
  /**
   * initialize the Loader, and create the messages box
   * method type: LOCAL
   * params: no params
   */
  this.init = function(){

    jQuery('#pwapp_messages_container').remove();
    jQuery(jQuery('body').get(0)).append('<div align="center" id="pwapp_messages_container" style="position:fixed; z-index:999999; width: 100%; display:block; top: 5px; margin: 0 auto;"></div>');

    var messages_container = jQuery('#pwapp_messages_container');
  };

  /*****************************************************************************************/
  /*                                      DISPLAY LOADER                                   */
  /*****************************************************************************************/
  /**
   * display a new message
   * method type: LOCAL
   * params: @params : a JSON with params {time, width, message, speed, closeFunction}
   */
  this.display = function(params){

    var messages_container = jQuery('#pwapp_messages_container');

    var newMessage = new PWAPPMessage();
    newMessage.parentContainer = messages_container;
    this.arr_messages.push(newMessage);

    newMessage.view(params);
  };

}
