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
$init = new GWInit();

//$init->wp_gw_activate(); //Force Upgrade Database

////////////////////////////// OLD


//// Global Prefixes
//
//define('PLUGIN_PREFIX', 'gw_');
//// Create a new table
//function plugin_table(){
//
//    global $wpdb;
//    $charset_collate = $wpdb->get_charset_collate();
//
//    $tablename = $wpdb->prefix."exam_results";
//
//    $sql = "CREATE TABLE $tablename (
//     id mediumint(11) NOT NULL AUTO_INCREMENT,
//     EXAMINEE_NO varchar(80) NOT NULL,
//     EXAMINATION_DATE varchar(80) NOT NULL,
//     EXAMINATION_TIME varchar(80) NOT NULL,
//     EMAIL_ADDRESS varchar(80) NULL,
//     LAST_NAME varchar(80) NULL,
//     FIRST_NAME varchar(80) NULL,
//     MIDDLE_NAME varchar(80) NULL,
//     NAME_SUFFIX varchar(80) NULL,
//     SEX varchar(80) NULL,
//     BIRTHDATE varchar(80) NULL,
//     CONTACT_NUMBER varchar(80) NULL,
//     TOTAL varchar(80) NOT NULL,
//     PERCENT varchar(80) NOT NULL,
//     EXAM_STATUS varchar(80) NOT NULL,
//     STUDENT_LEVEL varchar(80) NOT NULL,
//     PRIMARY KEY (id)
//   ) $charset_collate;";
//
//    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
//    dbDelta( $sql );
//
//}
//register_activation_hook( __FILE__, 'plugin_table' );
//
// Add menu
function plugin_menu() {

    add_menu_page("BukSU Gateway", "BukSU Gateway","manage_options", "gateway", "displayList",plugins_url('/myplugin/img/icon.png'));

}
add_action("admin_menu", "plugin_menu");

function displayList(){
    include "displaylist.php";
}
