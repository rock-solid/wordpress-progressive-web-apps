var pwappAppBanner = pwappAppBanner || {};
pwappAppBanner.WIDGET = pwappAppBanner.WIDGET || {};

pwappAppBanner.WIDGET.appUrl = pwappAppBanner.WIDGET.appUrl || '';
pwappAppBanner.WIDGET.appIcon = pwappAppBanner.WIDGET.appIcon || '';
pwappAppBanner.WIDGET.appName = pwappAppBanner.WIDGET.appName || '';
pwappAppBanner.WIDGET.ref = pwappAppBanner.WIDGET.ref || '';
pwappAppBanner.WIDGET.trustedDevice = pwappAppBanner.WIDGET.trustedDevice || 0;
pwappAppBanner.WIDGET.iframeUrl = pwapppwappAppBanner.WIDGET.iframeUrl || '';
pwappAppBanner.WIDGET.cssPath = pwappAppBanner.WIDGET.cssPath || '';
pwappAppBanner.WIDGET.openAppButton = pwappAppBanner.WIDGET.openAppButton || 'OPEN';

(function () {

        /**
         * @class Cookie
         * @constructor
         */
        var Cookie = function () {
            this.initialize();
        };

        var p = Cookie.prototype;

        /**
         *
         * Initialization method.
         * @method initialize
         */
        p.initialize = function () {

        };

        /**
         * Get cookie (public method)
         *
         * @param c_name
         * @returns {string}
         */
        p.get = function (c_name) {

            var i, x, y, ARRcookies = document.cookie.split(";");
            for (i = 0; i < ARRcookies.length; i++) {
                x = ARRcookies[i].substr(0, ARRcookies[i].indexOf("="));
                y = ARRcookies[i].substr(ARRcookies[i].indexOf("=") + 1);
                x = x.replace(/^\s+|\s+$/g, "");
                if (x == c_name) {
                    return decodeURIComponent(y);
                }
            }
        };

        /**
         * Set cookie (public method)
         *
         * @param c_name
         * @param value
         * @param expireDays
         */
        p.set = function (c_name, value, expireDays) {
            var expireDate = new Date();
            expireDate.setDate(expireDate.getDate() + expireDays);

            var c_value = encodeURIComponent(value) + ((expireDays == null) ? "" : "; expires=" + expireDate.toUTCString()) + "; path=/;";
            document.cookie = c_name + "=" + c_value;
        };

        pwappAppBanner.Cookie = Cookie;
    }()
);

(function () {

    /**
     * @class Bar
     * @constructor
     */
    var Bar = function (options) {
        this.initialize(options);
    };

    var p = Bar.prototype;

    /**
     * Public properties
     */

    /**
     * The DOM object to manage.
     * @property htmlElement
     * @type HTMLElement
     */
    p.htmlElement = null;
    p.iframe = null;
    p.iframeUrl = null;

    /**
     * Initialization method.
     * @method initialize
     */
    p.initialize = function (options) {

        var me = this;
        this.createWrapper(options);

        // add orientation change event
        if ('onorientationchange' in window) {

            // orientation change event functions
            this.orientationchangeFn = function () {
                setTimeout(function () {
                    me.resize();
                }, 250);
            };
            window.addEventListener("orientationchange", this.orientationchangeFn, false);
        }
        else {
            var mqOrientation = window.matchMedia("(orientation: portrait)");

            // The Listener will fire whenever this either matches or ceases to match
            mqOrientation.addListener(function (media) {
                // portrait
                if (media.matches) {
                    me.resize("portrait");
                }
                else {
                    me.resize("landscape");
                }
            }, false);
        }

        // add zoom event
        this.touchendFn = function () {
            clearTimeout(window.resizeEvt);
            window.resizeEvt = setTimeout(function () {
                //alert("resize");
                me.resize();
            }, 300);
        };

        window.resizeEvt;
        window.addEventListener("touchleave", this.touchendFn, false);
        window.addEventListener("touchcancel", this.touchendFn, false);
        window.addEventListener("touchmove", this.touchendFn, false);
        window.addEventListener("touchend", this.touchendFn, false);
    };


    /**
     * Create wrapper (public method)
     * @param options
     */
    p.createWrapper = function (options) {
        var wrapper = document.createElement("appticles-wrapper");
        this.htmlElement = wrapper;
        var height = Math.round(this.getHeight());
        var shadow = Math.round(0.1 * height);

        wrapper.style.position = "fixed";
        wrapper.style.width = "100%";
        wrapper.style.height = "0px";
        wrapper.style.top = "0px";
        wrapper.style.left = "0px";
        wrapper.style.boxSizing = "border-box";
        wrapper.style.zIndex = 1000000;
        wrapper.style.display = "block";
        wrapper.style.height = (height + shadow) + "px";

        document.body.appendChild(wrapper);

        var appName = options.appName || "";
        var appUrl = options.appUrl || "";
        var appIcon = options.appIcon || "";
        var cssPath = options.cssPath || "";
        var iframeUrl = this.iframeUrl = options.iframeUrl || "";
        var originUrl = window.location.href;
        var openAppButton = options.openAppButton || "";

        var _dc = (new Date()).getTime();
        var params = "cssPath=" + cssPath + "?_dc" + _dc;
        params += "&amp;height=" + height;
        params += "&amp;appName=" + encodeURIComponent(appName);
        params += "&amp;appUrl=" + encodeURIComponent(appUrl);
        params += "&amp;appIcon=" + appIcon;
        params += "&amp;originUrl=" + encodeURIComponent(originUrl);
        params += "&amp;openText=" + encodeURIComponent(openAppButton);

        var html = [
            '<iframe id="appticles-iframe-bar" width="100%" height="' + (height + shadow) + 'px" src="' + iframeUrl + '#' + params + '" frameborder="0" allowtransparency="true" scrolling="no" style="position:relative;"></iframe>'
        ].join("");

        wrapper.innerHTML = html;

        this.iframe = document.getElementById("appticles-iframe-bar");
    };


    /**
     * Resize banner (public method)
     *
     * @param orientation
     */
    p.resize = function (orientation) {

        var wrapper = this.htmlElement;
        if (wrapper == null) return;

        // compute new wrapper height
        var newH = Math.round(this.getHeight());
        var shadow = Math.round(0.1 * newH);
        wrapper.style.height = (newH + shadow) + "px";

        // change iframe params
        var params = "height=" + newH;
        this.iframe.style.height = (newH + shadow) + "px";
        this.iframe.src = this.iframeUrl + '#' + params;
    };


    /**
     * Get height (public method)
     *
     * @param orientation
     * @returns {number}
     */
    p.getHeight = function (orientation) {

        orientation = orientation || this.getOrientation();

        var screenWidth, screenHeight, windowWidth, newH;

        // resize wrapper
        if (orientation == "portrait") {
            screenWidth = Math.min(screen.width, screen.height);
            screenHeight = Math.max(screen.width, screen.height);
            windowWidth = window.innerWidth;
            newH = 90 * (windowWidth / screenWidth);

            return newH;
        }
        else if (orientation == "landscape") {
            screenWidth = Math.max(screen.width, screen.height);
            screenHeight = Math.min(screen.width, screen.height);
            windowWidth = window.innerWidth;
            newH = (90 * (windowWidth / screenHeight)) * (screenHeight / screenWidth);

            return newH;
        }
    };

    /**
     * Destroy bar (public method)
     */
    p.destroy = function () {

        // remove the bar from HTML
        var wrapper = this.htmlElement;
        wrapper.parentNode.removeChild(wrapper);
        this.htmlElement = null;

        // remove orientation change event
        if ('onorientationchange' in window) {
            window.removeEventListener("orientationchange", this.orientationchangeFn);
        }

        // remove touch end event
        window.removeEventListener("touchleave", this.touchendFn);
        window.removeEventListener("touchcancel", this.touchendFn);
        window.removeEventListener("touchmove", this.touchendFn);
        window.removeEventListener("touchend", this.touchendFn);

        window[this] = null;
        delete this;
    };


    /**
     * Get device orientation
     *
     * @return string (portrait | landscape)
     */
    p.getOrientation = function () {
        if (window.matchMedia("(orientation: portrait)").matches) {
            return "portrait";
        }
        else if (window.matchMedia("(orientation: landscape)").matches) {
            return "landscape";
        }
    };

    pwappAppBanner.Bar = Bar;
}());

(function () {

    /**
     * @class Stage
     * @constructor
     */
    var Stage = function (WIDGET) {
        this.WIDGET = WIDGET;

        this.initialize(WIDGET);
    };

    var p = Stage.prototype;

    /**
     * Public properties
     */
    p.cookie = null;
    p.WIDGET = null;
    p.bar = null;


    /**
     * Initialization method.
     * @method initialize
     */
    p.initialize = function () {

        // create cookie obj
        this.cookie = new pwappAppBanner.Cookie();
    };


    /**
     * Detect device
     * @todo (Future releases) Add device detection using user agent
     */
    p.detectDevice = function () {

        var me = this;

        if (me.WIDGET.trustedDevice == 1){

            me.redirectFn(1);
            return;
        }

        me.redirectFn(0);
    };


    /**
     * Set mobile device cookie and call createBar() method
     *
     * @param isAllowedDevice
     *
     */
    p.redirectFn = function (isAllowedDevice) {

        var WIDGET = this.WIDGET;
        var cookie = this.cookie;
        cookie.set(WIDGET.cookiePrefix + "mobile_device", isAllowedDevice, 7);

        if (Boolean(Number(String(isAllowedDevice)))) {
            this.createBar();
        }
    };


    /**
     * Create bar
     */
    p.createBar = function () {

        // wait until the document body is created
        var me = this;
        var DOMLoadTimer = setInterval(function () {
            if (document.body && document.body.clientWidth != 0) {
                clearInterval(DOMLoadTimer);

                me.bar = new pwappAppBanner.Bar({
                    appIcon: me.WIDGET.appIcon,
                    appName: me.WIDGET.appName,
                    appUrl: me.WIDGET.appUrl,
                    cssPath: me.WIDGET.cssPath,
                    iframeUrl: me.WIDGET.iframeUrl,
                    openAppButton: me.WIDGET.openAppButton
                });
            }
        }, 10);
    };

    /**
     *
     * Redirect to app
     *
     */
    p.openApp = function () {

        var WIDGET = this.WIDGET;
        var mobileUrl = WIDGET.ref;

        document.cookie = WIDGET.cookiePrefix + "redirect=; expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/;";

        // redirect to the app
        if (mobileUrl && mobileUrl.length > 0) {
            window.location.href = mobileUrl;
        }
    };

    pwappAppBanner.Stage = Stage;
}());

(function() {

    var appticlesStage, stage;

    /**
     * Create timer that will check if the document is ready
     * @type {number}
     */
    var DOMLoadTimer = setInterval(function () {
        if (/loading|loaded|complete/i.test(document.readyState)) {
            clearInterval(DOMLoadTimer);
            documentLoaded();
        }
    }, 10);

    /**
     * Init method, called when the document is ready
     *
     * The 'redirect' GET param is used for hosted apps (on Appticles).
     * Setting redirect=false will deactivate the app banner.
     *
     */
    function documentLoaded() {

        // create stage
        appticlesStage = stage = new pwappAppBanner.Stage(pwappAppBanner.WIDGET);

        // get saved cookies
        var cookie = stage.cookie,
            mobileDevice = cookie.get(pwappAppBanner.WIDGET.cookiePrefix + "mobile_device"),
            redirect = cookie.get(pwappAppBanner.WIDGET.cookiePrefix + "redirect"),
            closed = cookie.get(pwappAppBanner.WIDGET.cookiePrefix + "closed"),
            appUrl = pwappAppBanner.WIDGET.appUrl;

        // if there was a previous detection and the device is mobile
        if (mobileDevice && Boolean(Number(String(mobileDevice))) == true && appUrl && appUrl.length > 1){

            // if there is a cookie already set, then convert it to a boolean value
            // redirect param is used for hosted apps (on Appticles)
            redirect = (redirect != null) ? Boolean(Number(String(redirect))) : true;

            var urlParams = window.location.href.split("?");

            // if the URL contains a redirect param, then set up a cookie with this value
            if (urlParams.length > 1){
                if (urlParams[urlParams.length-1].indexOf("redirect=false") != -1){
                    cookie.set(pwappAppBanner.WIDGET.cookiePrefix + "redirect", 0, 7);
                    redirect = false;
                }
                else if (urlParams[urlParams.length-1].indexOf("redirect=true") != -1){
                    cookie.set(pwappAppBanner.WIDGET.cookiePrefix + "redirect", 1, 7);
                    redirect = true;
                }
            }

            // create the wrapper bar
            if (redirect && !closed){

                // attach on hash change listener
                window.onhashchange = onHashChange;

                // create wrapper bar
                stage.createBar();
            }

            return;
        }
        // if there was a previous detection and the device is a desktop one
        else if (mobileDevice && Boolean(Number(String(mobileDevice))) == false){
            return;
        }
        else if (window.location.href.indexOf("redirect=false") != -1){
            return;
        }

        window.onhashchange = onHashChange;

        // detect device
        stage.detectDevice();
    }

    /**
     * The hashchange event fires when a window's hash changes (location.hash).
     * The hash is used for opening the app or hiding the banner (using close button from iframe).
     *
     */
    function onHashChange(){

        var params = window.location.hash.split("#")[1];

        if (params){
            params = params.split("=");

            if (params[0] == "app_action"){

                switch (params[1]){

                    case "closebar":
                        // set cookie
                        appticlesStage.cookie.set(pwappAppBanner.WIDGET.cookiePrefix + "closed", 1, 7);

                        // remove the bar
                        setTimeout(function(){
                            appticlesStage.bar.destroy();
                            appticlesStage = null;
                        }, 300);

                        window.onhashchange = null;
                        window.location.hash = "";
                        break;

                    case "openapp":
                        window.onhashchange = null;
                        window.location.hash = "";
                        appticlesStage.openApp();
                        break;

                    default: break;
                }
            }
        }
    }

}());
