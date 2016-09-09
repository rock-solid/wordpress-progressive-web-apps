/**
 *
 * @langversion JAVASCRIPT
 *
 * http://www.appticles.com
 *
 */
 /* Browsers tested: IE6+, Firefox 3+, Opera 8+, Chrome, Safari 4 for windows*/

// hack for IE
if (window.console === undefined) {
  var console = { log : function(param){ alert(param);} };
}

var PWAPPJSInterface =  function(){

  var objects_arr = new Array();

  return{

    localpath: '',				    		// domain path

    AjaxUpload: new PWAPPAjaxUpload(),   		// object that makes the upload of a form without refreshin via AJAX
    Preloader: new PWAPPPreloader(),  		// the preloader object used for sending data to server through AJAX
    Loader: new PWAPPLoader(),   				// the object used to display AJAX error messages


    /*****************************************************************************************/
    /*                                      INIT INTERFACE                                   */
    /*****************************************************************************************/
    /**
     * initialize the PWAPPJSInterface
     * method type: LOCAL
     * params: none
     */
    init: function(){

      //when document is finish loaded, initialize the interface objects (UI_register, UI_users, UI_comments, etc)
      jQuery(document).ready(function(){

        PWAPPJSInterface.Loader.init();
        PWAPPJSInterface.initObjects();

      });
    },



    /*****************************************************************************************/
    /*                                      INIT INTERFACE OBJECTS                           */
    /*****************************************************************************************/
    /**
     * initialize the PWAPPJSInterface objects
     * method type: LOCAL
     * params: none
     */
    initObjects: function(){
      for (var i=0; i<objects_arr.length; i++){
        objects_arr[i].init();
      }
    },


    /*****************************************************************************************/
    /*                                   ADD INTERFACE OBJECT                                */
    /*****************************************************************************************/
    /**
     * add an object to the PWAPPJSInterface
     * method type: LOCAL
     * params: @objName : the name of the object in the PWAPPJSInterface
     *         @objType : object type like: REGISTER, USERS, COMMENTS, etc
     *         @params  : a JSON with params to pass to the new created object. Ex: {'name':'Johnson','age':24}
     */
    add: function(objName, objType, params, iframeWindow){

      //find similar object and remove it
      for (var i=0; i<objects_arr.length; i++){
        var obj = objects_arr.shift();
        if (obj === this[objName]){
          this[objName] = null;
        }
        else{
          objects_arr.push(obj);
        }
      }

      iframeWindow = (iframeWindow == null) ? window : iframeWindow;

      //create object
      this[objName] = new iframeWindow[objType]();
      if (params != null){
        for (var property in params){
          this[objName][property] = params[property];

        }
      }
      objects_arr.push(this[objName]);

    },


    /*****************************************************************************************/
    /*                                   SCROLL TO FIT SIZE                                  */
    /*****************************************************************************************/
    /**
     * scroll the document body so that the object fits his entire height inside the body visible area
     * method type: LOCAL
     * params: @obj : jQuery object such as jQuery(div), jQuery(p) ...
     */
    scrollToFit: function(obj){

      var container = jQuery('html,body');
      var scrollTop = parseInt(container.scrollTop());
      var containerHeight = container.get(0).clientHeight;
      var objTop = parseInt(obj.offset().top);
      var objHeight = obj.height();
      var objBottom = objTop + objHeight;

      if (objTop < scrollTop){
        jQuery(container).animate({scrollTop: objTop }, 1000);
      }
      else if (objTop >= scrollTop && objTop < containerHeight+scrollTop){
        if (objBottom > containerHeight+scrollTop){
          jQuery(container).animate({scrollTop: objBottom-containerHeight }, 1000);
        }
      }
      else if (objTop >= containerHeight+scrollTop){
        jQuery(container).animate({scrollTop: objBottom-containerHeight }, 1000);
      }
    }
  };
}();
