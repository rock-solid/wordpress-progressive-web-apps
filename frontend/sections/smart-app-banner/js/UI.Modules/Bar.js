pwappAppBanner = pwappAppBanner || {};

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
