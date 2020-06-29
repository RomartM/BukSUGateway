<?php

if (! defined( 'ABSPATH' ) ){
    exit;
}

/**
 * Class GWInit
 */
class GWInit {

    private $ajax_responder;

    /**
     *  Set things up.
     *  @since 1.0
     */
    public function __construct() {

        // include plugin classes
        $this->include_classes();

        //run on activation of plugin
        register_activation_hook( __FILE__, array( $this, 'wp_gw_activate' ) );

        //run on deactivation of plugin
        register_deactivation_hook( __FILE__, array( $this, 'wp_gw_deactivate' ) );

        //run on uninstall
        register_uninstall_hook( __FILE__, array( 'WP_GW_Init', 'wp_gw_uninstall' ) );

        // validate is caldera forms plugin exist
        add_action( 'admin_init', array( $this, 'wp_gw_validate_parent_plugin' ) );

        // register admin menu
        add_action( 'admin_menu', array( $this, 'register_wp_gw_menu_pages' ) );

        // load admin and wp asset files
        add_action( 'admin_enqueue_scripts', array( $this, 'register_gw_admin_plugin_scripts' ));
        add_action( 'admin_enqueue_scripts', array( $this, 'load_gw_admin_plugin_scripts' ));
        add_action( 'wp_enqueue_scripts', array( $this, 'register_gw_plugin_script' ));
        add_action( 'wp_enqueue_scripts', array( $this, 'load_gw_plugin_script' ));

        // Add form entries screen options
        add_filter('set-screen-option', array( $this, 'wp_gw_set_options' ), 10, 3);

        // Add custom link for our plugin
        add_filter( 'plugin_action_links_' . WP_GW_BASE_NAME , array( $this, 'wp_gw_plugin_action_links' ) );

        // load ajax responder
        //$this->ajax_responder = new GWAjaxResponder();
    }

    /**
     * Load plugin classes
     */
    public function include_classes(){
        GWUtility::instance()->load_classes(array(
            'classes/core/GWDataTable.php',
            'classes/core/GWShortCodes.php',
            'classes/core/GWPostResponder.php',
            //'classes/core/GWResponseFilter.php',
            //'classes/core/GWResultGenerator.php',
            //'classes/core/GWActivityCollector.php',
            'classes/core/GWEntriesManager.php',
            //'classes/core/GWAjaxResponder.php',
            'classes/core/GWFrontEnd.php',
            'classes/views/GWTabs.php',
            'classes/views/GWEntriesTable.php',
            'overrides/caldera-form-hooks.php',
            //'views/server.php',
            //'views/image-resource.php'
        ));
    }

    /**
     * Do things on plugin activation
     * @since 1.0
     */
    public function wp_gw_activate() {
        $db = new GWDataTable();
        $db->install();
    }

    /**
     *  Runs on plugin uninstall.
     *  a static class method or function can be used in an uninstall hook
     *
     *  @since 1.0
     */
    public static function wp_gw_uninstall() {
        // Drop Database excluding caldera form entries
        $db = new GWDataTable();
        $db->uninstall();
        $db->deleteOptions();
    }

    /**
     * Validate parent Plugin Caldera Forms exist and activated
     * @since 1.0
     */
    public function wp_gw_validate_parent_plugin() {
        if ( ( ! defined( CFCORE_VER  ) ) && ( ! defined( 'WP_GW_VERSION' ) ) ) {
            add_action( 'admin_notices', array( $this, 'wp_gw_caldera_forms_missing_notice' ) );
            deactivate_plugins( WP_GW_BASE_NAME );
            if ( isset( $_GET[ 'activate' ] ) ) {
                unset( $_GET[ 'activate' ] );
            }
        }
    }

    /**
     * If Caldera Forms plugin is not installed or activated then throw the error
     *
     * @return mixed error_message, an array containing the error message
     *
     * @since 1.0 initial version
     */
    public function wp_gw_caldera_forms_missing_notice() {
        $plugin_error = GWUtility::instance()->admin_notice( array(
            'type' => 'error',
            'message' => 'WP QR Pass requires Caldera Forms plugin to be installed and activated.'
        ) );
        echo $plugin_error;
    }

    /**
     * Create/Register menu items for the plugin.
     * @since 1.0
     */
    public function register_wp_gw_menu_pages() {
        $admin_page = new GWAdminPages();

        $hook = add_menu_page(
            __( 'Exam Results', 'wp-gw' ),
            __( 'Exam Results', 'wp-gw' ),
            'manage_options',
            'gw-exam-results-manager',
            array( $admin_page, 'gw_exam_result_manager_contents' ),
            'dashicons-schedule',
            3
        );

        add_submenu_page(
            'gw-exam-results-manager',
            __( 'Upload Exam Results', 'wp-gw' ),
            __( 'Upload Exam Results', 'wp-gw' ),
            'manage_options',
            'gw-upload-exam',
            array( $admin_page, 'gw_upload_exam' ));

        add_submenu_page(
            'gw-exam-results-manager',
            __( 'Upload Admission Info', 'wp-gw' ),
            __( 'Upload Admission Info', 'wp-gw' ),
            'manage_options',
            'gw-admission-info',
            array( $admin_page, 'gw_admission_info' ));

        add_action( "load-$hook", array( $this, 'wp_gw_add_options') );
    }

    /**
     * Form Entries table options
     */
    public function wp_gw_add_options() {
        global $gwEntriesTable;

        $option = 'per_page';
        $args = array(
            'label' => 'Exam Results',
            'default' => 500,
            'option' => 'gw_entries_per_page'
        );
        add_screen_option( $option, $args );

//        $link_forms_data = get_option( WP_GW_OPTION_PREFIX . "link_forms");
//        if(empty($link_forms_data)){
//            return;
//        }
//
//        $default_tab = GWUtility::instance()->format_group_name(array_values($link_forms_data)[0]['group']);
//        $tab = isset($_GET['tab']) ? $_GET['tab'] : $default_tab;
//
//        $form_id = "";
//        $link_forms_data = get_option( WP_GW_OPTION_PREFIX . "link_forms");
//        foreach ($link_forms_data as $entry){
//            if($tab=== GWUtility::instance()->format_group_name($entry["group"])){
//                $form_id = $entry["cf_id"];
//
//            }
//        }
//
//        $form = Caldera_Forms_Forms::get_form(  $form_id );
        $gwEntriesTable = new GWEntriesTable();
    }

    /**
     * Set form entry table options
     *
     * @param $status
     * @param $option
     * @param $value
     * @return mixed
     */
    public function wp_gw_set_options($status, $option, $value) {


        if ( 'gw_entries_per_page' == $option ) return $value;

        return $status;

    }

    /**
     * Register admin pages assets
     */
    public function register_gw_admin_plugin_scripts() {
        global $current_user;
        wp_get_current_user();


        // Entries Manager Assets
        wp_register_style( 'gw-admin', WP_GW_URL . 'assets/admin/css/gw-style.css', array( 'wp-jquery-ui-dialog' ) );
        wp_register_script( 'gw-admin', WP_GW_URL . 'assets/admin/js/gw-script.js', array('jquery', 'jquery-ui-dialog') );
        wp_localize_script( 'gw-admin', 'gwAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));

    }

    /**
     * Load admin pages assets only to the specific page
     *
     * @param $hook
     */
    public function load_gw_admin_plugin_scripts( $hook ) {

        // Load Settings Tab Assets
        if($hook == 'entries-manager_page_gw-settings' && isset($_REQUEST['tab'])){
            switch ($_REQUEST['tab']){
                case 'response':
                    wp_enqueue_style( 'gw-admin-form-response' );
                    wp_enqueue_script( 'gw-admin-form-response' );
                    break;
                default:
            }
            return;
        }

        // Load Default form entry manager
        if( $hook != 'toplevel_page_gw-exam-results-manager' && $hook != 'entries-manager_page_gw-email-settings' ) {

            return;

        }

        // Load style & scripts.

        wp_enqueue_style( 'gw-admin' );
        wp_enqueue_script( 'gw-admin' );

    }

    /**
     * Register wp frontend assets
     */
    public function register_gw_plugin_script(){
        wp_register_style( 'gw-wp-generic', WP_GW_URL . 'assets/css/gw-generic-style.css', array() );
        wp_register_script( 'gw-wp-generic', WP_GW_URL . 'assets/js/gw-generic-script.js', array('jquery') );
        wp_localize_script( 'gw-wp-generic', 'gwPublicAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ),  'action' => 'resend_email_public'));
    }

    /**
     * Load wp frontend assets
     */
    public function load_gw_plugin_script(){
        wp_enqueue_style( 'gw-wp-generic' );
        wp_enqueue_script( 'gw-wp-generic' );
    }

    /**
     * Add custom link for the plugin beside activate/deactivate links
     * @param array $links Array of links to display below our plugin listing.
     * @return array Amended array of links.    *
     * @since 1.0
     */
    public function wp_gw_plugin_action_links( $links ) {
        return array_merge( array(
            '<a href="' . admin_url( 'admin.php?page=gw-exam-results-manager' ) . '">' . __( 'Exam Results', 'wp-gw' ) . '</a>'
        ), $links );
    }
}
