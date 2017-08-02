=== Progressive Web Apps ===
Contributors: cborodescu, anghelalexandra
Tags: progressive web apps, pwa, mobile, mobile web, mobile internet, smartphone, iphone, android, windows, webkit, chrome, safari, mobile web app, html5, responsive ui
Requires at least: 4.0
Tested up to: 4.7.5
Stable tag: 0.5.1
License: GPLv2 or later

Progressive Web Apps use modern web capabilities to deliver app-like user experiences. They're reliable, fast and engaging.

== Description ==

Progressive Web Apps are user experiences that have the reach of the web, and are:

* **Reliable** - Load instantly even in uncertain network conditions.
* **Fast** - Respond quickly to user interactions with silky smooth animations and no janky scrolling.
* **Engaging** - Feel like a natural app on the device, with an immersive user experience.

This new level of quality allows Progressive Web Apps to earn a place on the user's home screen. More details about PWAs here: [https://developers.google.com/web/progressive-web-apps/](https://developers.google.com/web/progressive-web-apps/)

The WordPress Progressive Web Apps plugin helps bloggers, publishers and other content creators to go beyond responsive web design and ‘appify' their existing mobile presence. Progressive Web Apps is supported on: iOS & Android. Compatible browsers: Safari, Google Chrome, Android – Native Browser. The plugin has been tested on WordPress 4.0 and later.

The WordPress Progressive Web Apps plugin includes one FREE mobile app theme (MOSAIC) which is customizable (colors, fonts, appearance) via the WordPress admin area. The tech stack we use in building Progressive Web Apps includes:

* AngularJS/Ionic
* ReactJS
* Sencha Touch
* SASS
* Gulp
* Bower
* Karma
* Jasmine
* Protractor


The MOSAIC mobile app theme (available for FREE) is built with Sencha Touch, but we're currently working on migrating it to AngularJS/Ionic 1. Most of the premium mobile app themes available at [PWAThemes.com/progressive-web-app-themes.html](https://pwathemes.com/progressive-web-app-themes.html) are built using Angular/Ionic 1 & SASS. Each mobile app theme comes with a "production" version, which in essence is the bundled/packaged/minified collection of all the necessary JS/CSS files for the PWA to run correctly.

Each of our mobile app themes are tested with Karma, Jasmine and Protractor. We have an average of 50-60% code coverage and we're working on improving this rate. While we do our best to catch any bugs out there, we are aware that some of them might escape us. Please reach out if you happen to come across a nasty one 😊.

The FREE mobile app theme MOSAIC available in the WordPress Progressive Web Apps plugin is the window-display of themes. You can instantly see several categories and choose which ones are of interest and focus on those. Depending on the number of displayed categories, the boxes will resize to fit all available space. It's great for publishers that have posts spanning several categories and need a way to visually structure their content.

It comes with support for:

* Multi-image mosaic on cover page
* Pages & sub-pages menu
* Side-to-side navigation with lateral swiping through categories
* Maximum 2 articles per page

There are dozens of mobile app themes available in the PRO version: [BASE](https://pwathemes.com/progressive-web-app-themes/base.html), [OBLIQ](https://pwathemes.com/progressive-web-app-themes/obliq.html), [ELEVATE](https://pwathemes.com/progressive-web-app-themes/elevate.html), [FOLIO](https://pwathemes.com/progressive-web-app-themes/folio.html), [INVISION](https://pwathemes.com/progressive-web-app-themes/invision.html), [POPSICLE](https://pwathemes.com/progressive-web-app-themes/popsicle.html), [PULSE](https://pwathemes.com/progressive-web-app-themes/pulse.html), [GHOST](https://pwathemes.com/progressive-web-app-themes/ghost.html), [PHANTOM](https://pwathemes.com/progressive-web-app-themes/phantom.html), [LUCID](https://pwathemes.com/progressive-web-app-themes/lucid.html), [EXTRUDE](https://pwathemes.com/progressive-web-app-themes/extrude.html), [VEDI](https://pwathemes.com/progressive-web-app-themes/vedi.html), [BLEND](https://pwathemes.com/progressive-web-app-themes/blend.html), [PURE](https://pwathemes.com/progressive-web-app-themes/pure.html), [GOTHAM](https://pwathemes.com/progressive-web-app-themes/gotham.html), [FUTURE](https://pwathemes.com/progressive-web-app-themes/future.html) & [PALM](https://pwathemes.com/progressive-web-app-themes/palm.html).

Additional key features available in PRO:

- **Rich UI/UX**
Your users can have a cozy browsing experience on their favorite mobile device without having to go to an App Store and install anything.

- **Monetization**
Take full control of your income by easily connecting the plugin with your Google DoubleClick for Publishers account.

- **Translations**
Wordpress Mobile Pack will automatically translate your mobile web app in one of the supported languages: Chinese (zh_CN), Dutch, English, French, German, Hungarian, Italian, Polish, Portuguese (Brazil), Romanian, Spanish or Swedish.

- **Google AMP Integration**
Integrate with the official Google Accelerated Mobile Pages plugin. The Accelerated Mobile Pages (AMP) Project is an open source initiative that embodies the vision that publishers can create mobile optimized content once and have it load instantly everywhere.

- **Premium Support**
We take pride in offering fantastic maintenance and hands-on support. Our team of friendly mobile experts makes sure technology doesn't stand in your way.

- **Analytics**
Get to know your mobile users and analyze your impact with our powerful yet simple reader-centric analytics via Google Analytics integration.

- **Add to Homescreen**
Users can add your mobile web application to their homescreens making it just a tap away.

We enjoy writing and maintaining this plugin. If you like it too, please rate us. But if you don't, let us know how we can improve it. 

Have fun on your mobile adventures!


== Installation ==

= Simple installation for WordPress v4.0 and later =

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
1. Access your site in a mobile browser and check if the application is displayed. If the app is not loading properly, make sure that the file exporting the content - http://yoursite.com/{your plugins folder}/progressive-web-apps/frontend/export/content.php - can be accessed in the browser and doesn't return a '404 Not Found' or '403 Forbidden' error.
1. You're all done!

= Testing your installation =

Ideally, use a real mobile device to access your (public) site address and check that the switching and mobile web app work correctly.

You can also download a number of mobile emulators that can run on a desktop PC and simulate mobile devices.

Please note that the progressive web app will be enabled only on supported devices: iOS & Android. Only the following browsers are compatible: Safari, Google Chrome, Android - Native Browser and Firefox (as of 2.0.2).


== Changelog ==

= 0.5.1 =
* Add links to PWAThemes.com

= 0.5 =
* Various bug fixes for the Mosaic mobile app theme
* Add separate tab for App Themes
* Add manifest background color, to improve PWA score

= 0.1 =
* Initial release


== Screenshots ==

1. The Mosaic mobile app theme
2. "App Themes" page from the admin panel. 
3. "App Themes" page from the admin panel with other Premium app themes
4. "Look & Feel" page from the admin panel. Customize theme by choosing colors and fonts.
5. "Look & Feel" page from the admin panel. Customize theme by adding your own app icon, logo & cover.

== Repositories ==

Here's our Github development repository:

* [https://github.com/appticles/wordpress-progressive-web-apps](https://github.com/appticles/wordpress-progressive-web-apps) - The plugin files, same as you will find for download on Wordpress.org.
