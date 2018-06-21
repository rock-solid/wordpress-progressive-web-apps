<?php

/**
 * Plugin Name:  Progressive Web Apps
 * Plugin URI:  http://wordpress.org/plugins/progressive-web-apps/
 * Description: Progressive Web Apps use modern web capabilities to deliver app-like user experiences. They're reliable, fast and engaging.
 * Author: PWAThemes.com
 * Author URI: https://pwathemes.com/
 * Version: 0.7
 * Copyright (c) 2017 PWAThemes.com
 * License: The Progressive Web Apps is Licensed under the Apache License, Version 2.0
 * Text Domain: progressive-web-apps
 */

namespace PWAPP;

use PWAPP\Admin\Admin_Init;
use PWAPP\Admin\Admin_Ajax;
use PWAPP\Frontend\Application;
use PWAPP\Inc\PWAPP_API;
use PWAPP\Core\PWAPP;

require_once 'vendor/autoload.php';
require_once 'core/config.php';
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

global $progressive_web_apps;
$progressive_web_apps = new PWAPP();

/**
 * Used to load the required files on the plugins_loaded hook, instead of immediately.
 */
function pwapp_admin_init() {
	new  Admin_Init();
}

function pwapp_frontend_init() {
	new Application();
}

$pwapp_api = new PWAPP_API();

// add_action( 'rest_api_init', [ $pwapp_api, 'register_pwapp_routes' ] );


// // Add hooks for activating & deactivating the plugin
register_activation_hook( __FILE__, [ $progressive_web_apps, 'activate' ] );
register_deactivation_hook( __FILE__, [ $progressive_web_apps, 'deactivate' ] );

// Initialize the plugin's check logic and rendering
if ( is_admin() ) {

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

		$pwapp_admin_ajax = new Admin_Ajax();

		add_action( 'wp_ajax_pwapp_editimages', [ $pwapp_admin_ajax, 'theme_editimages' ] );
		add_action( 'wp_ajax_pwapp_theme_settings', [ $pwapp_admin_ajax, 'theme_settings' ] );
		add_action( 'wp_ajax_pwapp_send_feedback', [ $pwapp_admin_ajax, 'send_feedback' ] );
		add_action( 'wp_ajax_pwapp_settings_save', [ $pwapp_admin_ajax, 'settings_save' ] );

	} else {
		add_action( 'plugins_loaded', 'PWAPP\pwapp_admin_init' );
	}
} else {
	add_action( 'plugins_loaded', 'PWAPP\pwapp_frontend_init' );
}
