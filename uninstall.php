<?php

// If uninstall is not called from WordPress, exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit();
}

require_once('core/config.php');
require_once('core/class-pwapp.php');

// create uploads folder and define constants
if ( !defined( 'PWAPP_FILES_UPLOADS_DIR' ) && !defined( 'PWAPP_FILES_UPLOADS_URL' ) ) {
    $PWAPP_Uploads = new PWAPP_Uploads();
    $PWAPP_Uploads->define_uploads_dir();
}

// remove uploaded images and uploads folder
$PWAPP_Uploads = new PWAPP_Uploads();
$PWAPP_Uploads->remove_uploads_dir();

// delete plugin settings
PWAPP_Options::uninstall();