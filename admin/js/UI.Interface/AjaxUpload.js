/**
 *
 * @langversion JAVASCRIPT
 *
 * http://www.appticles.com
 *
 */
function PWAPPAjaxUpload(){

  var JSObject = this;

  /*****************************************************************************************/
  /*                                      CREATE IFRAME                                    */
  /*****************************************************************************************/
  /**
   * create the iframe which will do the submit, and attach it on the 'onStart' and 'onComplete' functions
   * method type: LOCAL
   * params: @c : JSON with 'onStart' and 'onComplete' functions
   */
  this.frame = function(c) {

    var n = 'f' + Math.floor(Math.random() * 99999);

    jQuery('body *:first',window.document).before('<div><iframe style="display:none" src="about:blank" id="'+n+'" name="'+n+'"></iframe></div>');
    jQuery('#'+n,window.document).bind("load",function(){
      JSObject.loaded(n);
    });

    if (c && typeof(c.onComplete) == 'function') {
      jQuery('#'+n,window.document).get(0).onComplete = c.onComplete;
    }
    return n;
  };


   /*****************************************************************************************/
  /*                               ATTACH THE FORM TARGET - IFRAME                         */
  /*****************************************************************************************/
  /**
   * attach to the current form the submit target: the new created iframe
   * method type: LOCAL
   * params: @f    : form object
   *		   @name : new created iframe's id
   */
  this.form = function(f, name) {
    jQuery(f).attr('target', name);
  };


   /*****************************************************************************************/
  /*                           DO THE FORM SUBMIT IN NEW TARGET WINDOW                     */
  /*****************************************************************************************/
  /**
   * do the submit event in the new target iframe window
   * method type: AJAX
   * params: @f : form object
   *		   @c : JSON with 'onStart' and 'onComplete' functions
   */
  this.dosubmit = function(f, c) {

    JSObject.form(f, JSObject.frame(c));

    if (c && typeof(c.onStart) == 'function') {
      return c.onStart();
    } else {
      return true;
    }
  };


   /*****************************************************************************************/
  /*                            ONLOAD NEW IFRAME (TARGET) CONTENT                         */
  /*****************************************************************************************/
  /**
   * run when the new target (iframe) is completely loaded
   * method type: LOCAL
   * params: @id : iframe id
   */
  this.loaded = function(id) {

    if (jQuery('#'+id,window.document).get(0).contentWindow) {
      var d = jQuery('#'+id,window.document).get(0).contentWindow.document;
    } else if (jQuery('#'+id,window.document).get(0).contentDocument) {
      var d = jQuery('#'+id,window.document).get(0).contentDocument;
    } else {
      var d = window.frames[id].document;
    }

    if (d.location.href == 'about:blank') {
      return;
    }

    if (typeof(jQuery('#'+id,window.document).get(0).onComplete) == 'function') {
      jQuery('#'+id,window.document).get(0).onComplete(d.body.innerHTML);
    }
  };

}
