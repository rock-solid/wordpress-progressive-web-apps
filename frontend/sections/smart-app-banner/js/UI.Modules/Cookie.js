pwappAppBanner = pwappAppBanner || {};

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
