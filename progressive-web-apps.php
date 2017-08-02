<?php

/**
 * Plugin Name:  Progressive Web Apps
 * Plugin URI:  http://wordpress.org/plugins/progressive-web-apps/
 * Description: Progressive Web Apps use modern web capabilities to deliver app-like user experiences. They're reliable, fast and engaging.
 * Author: PWAThemes.com
 * Author URI: https://pwathemes.com/
 * Version: 0.5.1
 * Copyright (c) 2017 PWAThemes.com
 * License: The Progressive Web Apps is Licensed under the Apache License, Version 2.0
 * Text Domain: progressive-web-apps
 */

require_once('core/config.php');
require_once('core/class-pwapp.php');

/**
 * Used to load the required files on the plugins_loaded hook, instead of immediately.
 */
function pwapp_frontend_init() {
     require_once('frontend/class-application.php');
     new PWAPP_Application();
}

function pwapp_admin_init() {
 	require_once('admin/class-admin-init.php');
	new PWAPP_Admin_Init();
}

if (class_exists( 'PWAPP_Core' ) && class_exists( 'PWAPP_Core' )) {

    global $progressive_web_apps;
    $progressive_web_apps = new PWAPP_Core();

    // Add hooks for activating & deactivating the plugin
    register_activation_hook( __FILE__, array( &$progressive_web_apps, 'activate' ) );
    register_deactivation_hook( __FILE__, array( &$progressive_web_apps, 'deactivate' ) );

    // Initialize the plugin's check logic and rendering
    if (is_admin()) {

        if (defined( 'DOING_AJAX' ) && DOING_AJAX) {

            require_once( PWAPP_PLUGIN_PATH . 'admin/class-admin-ajax.php' );

            $pwapp_admin_ajax = new PWAPP_Admin_Ajax();

            add_action('wp_ajax_pwapp_editimages', array( &$pwapp_admin_ajax, 'theme_editimages' ) );
            add_action('wp_ajax_pwapp_theme_settings', array( &$pwapp_admin_ajax, 'theme_settings' ) );
            add_action('wp_ajax_pwapp_send_feedback', array( &$pwapp_admin_ajax, 'send_feedback' ) );

        } else {
            add_action('plugins_loaded', 'pwapp_admin_init');
        }

    } else {
        add_action('plugins_loaded', 'pwapp_frontend_init');
    }
}
