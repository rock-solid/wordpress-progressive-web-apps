pwappAppBanner = pwappAppBanner || {};

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
