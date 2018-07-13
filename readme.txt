=== Progressive Web Apps ===
Contributors: cborodescu, anghelalexandra
Tags: progressive web apps, pwa, mobile, mobile web, mobile internet, smartphone, iphone, android, windows, webkit, chrome, safari, mobile web app, html5, responsive ui
Requires at least: 4.5
Requires PHP: 5.4
Tested up to: 4.8.1
Stable tag: 1.0
License: GPLv2 or later

Progressive Web Apps use modern web capabilities to deliver app-like user experiences. They're reliable, fast and engaging.

== Description ==

Progressive Web Apps are user experiences that have the reach of the web, and are:

* **Reliable** - Load instantly even in uncertain network conditions.
* **Fast** - Respond quickly to user interactions with silky smooth animations and no janky scrolling.
* **Engaging** - Feel like a natural app on the device, with an immersive user experience.

This new level of quality allows Progressive Web Apps to earn a place on the user's home screen. More details about PWAs here: [https://developers.google.com/web/progressive-web-apps/](https://developers.google.com/web/progressive-web-apps/)

The WordPress Progressive Web Apps plugin helps bloggers, publishers and other content creators to go beyond responsive web design and 'appify' their existing mobile presence. Progressive Web Apps is supported on: iOS & Android. Compatible browsers: Safari, Google Chrome, Android – Native Browser. The plugin has been tested on WordPress 4.8 and later, we recommend using the latest WordPress version.

The WordPress Progressive Web Apps plugin includes one FREE mobile PWA (MOSAIC) which is customizable (colors, fonts, appearance) via the WordPress admin area. The tech stack we used in building this Progressive Web App includes:

* React JS
* Semantic UI for UI components
* Redux for app state management
* SASS
* Webpack (Create React App boilerplate)
* Babel
* Jest & Sinon for unit tests

The MOSAIC PWA (available for FREE) is built with React JS. Most of the premium mobile progressive web apps available at [PWAThemes.com/progressive-web-app-themes.html](https://pwathemes.com/progressive-web-app-themes.html) are built using Angular/Ionic 1 or React. Each PWA comes with a "production" version, which in essence is the bundled/packaged/minified collection of all the necessary JS and CSS files for the PWA to run correctly.

Each of our progressive web apps are tested with Karma, Jasmine and Protractor (Angular) or Jest (React). We have an average of 70-80% code coverage and we're working on improving this rate. While we do our best to catch any bugs out there, we are aware that some of them might escape us. Please reach out if you happen to come across a nasty one 😊.

The FREE PWA MOSAIC available in the WordPress Progressive Web Apps plugin is the window-display of themes. You can instantly see several categories and choose which ones are of interest and focus on those. Depending on the number of displayed categories, the boxes will resize to fit all available space. It's great for publishers that have posts spanning several categories and need a way to visually structure their content.

It comes with support for:

* Multi-image mosaic on cover page
* Pages & sub-pages menu
* Side-to-side navigation with lateral swiping through categories
* Maximum 2 articles per card

There are dozens of mobile progressive web apps available in the PRO version: [BASE](https://pwathemes.com/progressive-web-app-themes/base.html), [OBLIQ](https://pwathemes.com/progressive-web-app-themes/obliq.html), [ELEVATE](https://pwathemes.com/progressive-web-app-themes/elevate.html), [FOLIO](https://pwathemes.com/progressive-web-app-themes/folio.html), [INVISION](https://pwathemes.com/progressive-web-app-themes/invision.html), [POPSICLE](https://pwathemes.com/progressive-web-app-themes/popsicle.html), [PULSE](https://pwathemes.com/progressive-web-app-themes/pulse.html), [GHOST](https://pwathemes.com/progressive-web-app-themes/ghost.html), [PHANTOM](https://pwathemes.com/progressive-web-app-themes/phantom.html), [LUCID](https://pwathemes.com/progressive-web-app-themes/lucid.html), [EXTRUDE](https://pwathemes.com/progressive-web-app-themes/extrude.html), [VEDI](https://pwathemes.com/progressive-web-app-themes/vedi.html), [BLEND](https://pwathemes.com/progressive-web-app-themes/blend.html), [PURE](https://pwathemes.com/progressive-web-app-themes/pure.html),  [FUTURE](https://pwathemes.com/progressive-web-app-themes/future.html) & [PALM](https://pwathemes.com/progressive-web-app-themes/palm.html).

Additional key features available in PRO:

- **Rich UI/UX**
Your users can have a cozy browsing experience on their favorite mobile device without having to go to an App Store and install anything.

- **Monetization**
Take full control of your income by easily connecting the plugin with your Google DoubleClick for Publishers account.

- **Translations**
The plugin will automatically translate your mobile web app in one of the supported languages: Chinese (zh_CN), Dutch, English, French, German, Hungarian, Italian, Polish, Portuguese (Brazil), Romanian, Spanish or Swedish.

- **Premium Support**
We take pride in offering fantastic maintenance and hands-on support. Our team of friendly mobile experts makes sure technology doesn't stand in your way.

- **Analytics**
Get to know your mobile users and analyze your impact with our powerful yet simple reader-centric analytics via Google Analytics integration.

- **Add to Homescreen**
Users can add your mobile web application to their homescreens making it just a tap away.

- **Offline Mode**
The application's files are cached for offline usage together with the content. All of the categories, posts and pages that your users navigate to will be cached for offline usage.

- **Web Push Notifications**
We have integrated with the OneSignal WordPress plugin, allowing you to engage users through push notifications.

Advanced PWA features like offline mode and web push notifications are implemented using service workers and are currently available on Chrome.

We enjoy writing and maintaining this plugin. If you like it too, please rate us. But if you don't, let us know how we can improve it.

Have fun on your mobile adventures!


== Installation ==

= Simple installation for WordPress v4.5 and later =

1.  Go to the 'Plugins' / 'Add new' menu
1.	Upload progressive-web-apps.zip then press 'Install now'.
1.	Enjoy.

= Comprehensive setup =

A more comprehensive setup process and guide to configuration is as follows.

1. Locate your WordPress install on the file system
1. Extract the contents of `progressive-web-apps.zip` into `wp-content/plugins`
1. In `wp-content/plugins` you should now see a directory named `progressive-web-apps`
1. Login to the WordPress admin panel at `http://yoursite.com/wp-admin`
1. Go to the 'Plugins' menu.
1. Click 'Activate' for the plugin.
1. Go to the ‘Progressive Web Apps' admin panel.
1. Choose color schemes, fonts and add your own logo and app icon.
1. Access your site in a mobile browser and check if the application is displayed. If the app is not loading properly, make sure that the [WordPress REST API](https://developer.wordpress.org/rest-api/) can be accessed in the browser and doesn't return an error.
1. You're all done!

= Testing your installation =

Ideally, use a real mobile device to access your (public) site address and check that the switching and mobile web app work correctly.

You can also download a number of mobile emulators that can run on a desktop PC and simulate mobile devices.

Please note that the progressive web app will be enabled only on supported devices: iOS & Android. Only the following browsers are compatible: Safari, Google Chrome, Android - Native Browser.


== Changelog ==

= 1.0 =
* Completely rebuild Progressive Web App theme (Mosaic) using React, Redux and Semantic UI
* Connected app with the WordPress REST API
* Added PHP namespaces

= 0.7 =
* Security fix, replaced Smart App Banner script with jQuery Noty plugin

= 0.6 =
* Add  Web App Install Banner( Add to Home Screen ) functionality
* Translate app to Bosnian
* Add resize image method for logo, icon and cover

= 0.5.1 =
* Add links to PWAThemes.com

= 0.5 =
* Various bug fixes for the Mosaic mobile app theme
* Add separate tab for App Themes
* Add manifest background color, to improve PWA score

= 0.1 =
* Initial release

== Upgrade Notice ==

= 1.0 =
* The latest version comes with a brand Progressive Web App built on React & Semantic UI.


== Screenshots ==

1. The Mosaic progressive web app
2. "App Themes" page from the admin panel.
3. "App Themes" page from the admin panel with other Premium app themes
4. "Look & Feel" page from the admin panel. Customize theme by choosing colors and fonts.
5. "Look & Feel" page from the admin panel. Customize theme by adding your own app icon & logo.

== Repositories ==

Here are our Github development repositories:

* [https://github.com/appticles/wordpress-progressive-web-apps](https://github.com/appticles/wordpress-progressive-web-apps) - The plugin files, same as you will find for download on WordPress.org.
* [https://github.com/appticles/pwa-theme-mosaic](https://github.com/appticles/pwa-theme-mosaic) - The Progressive Web App source, built with React, Redux and Semantic UI.
