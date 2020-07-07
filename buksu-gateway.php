<?php

/*
Plugin Name: BukSU Gateway
Plugin URI: https://buksu.edu.ph
Description: A brief description of the Plugin.
Version: 1.0
Author: rome
Author URI: http://URI_Of_The_Plugin_Author
License: A "Slug" license name e.g. GPL2
*/

if (! defined( 'ABSPATH' ) ){
    exit;
}

// Declare some global constants
define( 'WP_GW_VERSION', '1.0' );
define( 'WP_GW_TABLE_LOG_VERSION', '1.0' );
define( 'WP_GW_TABLE_EXAM_RESULT_VERSION', '1.0' );
define( 'WP_GW_TABLE_ADMISSION_INFO_VERSION', '1.0' );
define( 'WP_GW_ROOT', dirname( __FILE__ ) );
define( 'WP_GW_URL', plugins_url( '/', __FILE__ ) );
define( 'WP_GW_BASE_FILE', basename( dirname( __FILE__ ) ) . '/buksu-gateway.php' );
define( 'WP_GW_BASE_NAME', plugin_basename( __FILE__ ) );
define( 'WP_GW_PATH', plugin_dir_path( __FILE__ ) ); //use for include files to other files
define( 'WP_GW_PRODUCT_NAME', 'BukSU Gateway' );
define( 'WP_GW_OPTION_PREFIX', 'wp_gw_' );
define( 'WP_GW_STORAGE_PATH', WP_CONTENT_URL . '/uploads/' );
//define("GW_PAGE_SLUG_NAME", "/my/");

/*
 * include classes
 */

if ( ! class_exists( 'GWUtils' ) ) {
    include( WP_GW_ROOT . '/includes/GWUtility.php' );
}

if ( ! class_exists( 'GWAdminPages' ) ) {
    include( WP_GW_ROOT . '/classes/views/GWAdminPages.php' );
}

if ( ! class_exists( 'GWInit' ) ) {
    include( WP_GW_ROOT . '/classes/init/GWInit.php' );
}

// Initialize the QR Pass class
add_action( 'plugins_loaded', function () {
	GWInit::get_instance();
} );

// $d = new GWDataTable(); // Truncate Exam Results Table
// $d->truncateExamResults();

 //$d = new GWDataTable(); // Truncate Exam Results Table
 //$d->truncateAdmissionInfo();

// TODO: For new student -> After course request the system will generate a transaction id.
//  This id will be used to check the student course request and will
