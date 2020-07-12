<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Class GWInit
 */
class GWInit
{
    // class instance
    public static $instance;
    /**
     *  Set things up.
     *  @since 1.0
     */
    public function __construct()
    {

        // include plugin classes
        $this->include_classes();

        //run on activation of plugin
        register_activation_hook(__FILE__, array( $this, 'wp_gw_activate' ));

        //run on deactivation of plugin
        register_deactivation_hook(__FILE__, array( $this, 'wp_gw_deactivate' ));

        //run on uninstall
        register_uninstall_hook(__FILE__, array( 'WP_GW_Init', 'wp_gw_uninstall' ));

        // validate is caldera forms plugin exist
        add_action('admin_init', array( $this, 'wp_gw_validate_parent_plugin' ));

        // register admin menu
        add_action('admin_menu', array( $this, 'register_wp_gw_menu_pages' ));

        // Add form entries screen options
        add_filter('set-screen-option', array( $this, 'wp_gw_set_options' ), 10, 3);

        // load admin and wp asset files
        add_action('admin_enqueue_scripts', array( $this, 'register_gw_admin_plugin_scripts' ));
        add_action('admin_enqueue_scripts', array( $this, 'load_gw_admin_plugin_scripts' ));
        add_action('wp_enqueue_scripts', array( $this, 'register_gw_plugin_script' ));
        add_action('wp_enqueue_scripts', array( $this, 'load_gw_plugin_script' ));

        // Add custom link for our plugin
        add_filter('plugin_action_links_' . WP_GW_BASE_NAME, array( $this, 'wp_gw_plugin_action_links' ));

        //$this->wp_gw_activate(); //Force Upgrade Database
        //
        //$db = new GWDataTable();
    	//$db->truncateExamResults();
    	//$db->genIDNumberNewStudents();

    }

    /** Singleton instance */
    public static function get_instance()
    {
        if (! isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Load plugin classes
     */
    public function include_classes()
    {
        GWUtility::instance()->load_classes(array(
            'classes/core/GWDataTable.php',
            'classes/core/GWMailerService.php',
            'classes/core/GWSession.php',
            'classes/core/GWValidation.php',
            'classes/core/GWCourses.php',
            'classes/core/GWShortCodes.php',
            'classes/core/GWPostResponder.php',
            'classes/core/GWEntriesManager.php',
            'classes/core/GWFrontEnd.php',
            'classes/views/GWTabs.php',
            'classes/views/GWEntriesNewStudentTable.php',
            'classes/views/GWEntriesOldStudentTable.php',
            'overrides/caldera-form-hooks.php'
        ));
    }

    /**
     * Do things on plugin activation
     * @since 1.0
     */
    public function wp_gw_activate()
    {
        $db = new GWDataTable();
        $db->install();
    	//$db->truncateExamResults();
        //$db->truncateOldStudent();
    }

    /**
     *  Runs on plugin uninstall.
     *  a static class method or function can be used in an uninstall hook
     *
     *  @since 1.0
     */
    public static function wp_gw_uninstall()
    {
        // Drop Database excluding caldera form entries
        $db = new GWDataTable();
        $db->uninstall();
        $db->deleteOptions();
    }

    /**
     * Validate parent Plugin Caldera Forms exist and activated
     * @since 1.0
     */
    public function wp_gw_validate_parent_plugin()
    {
        if ((! defined(CFCORE_VER)) && (! defined('WP_GW_VERSION'))) {
            add_action('admin_notices', array( $this, 'wp_gw_caldera_forms_missing_notice' ));
            deactivate_plugins(WP_GW_BASE_NAME);
            if (isset($_GET[ 'activate' ])) {
                unset($_GET[ 'activate' ]);
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
    public function wp_gw_caldera_forms_missing_notice()
    {
        $plugin_error = GWUtility::instance()->admin_notice(array(
            'type' => 'error',
            'message' => 'Gateway BukSU requires Caldera Forms plugin to be installed and activated.'
        ));
        echo $plugin_error;
    }

    /**
     * Create/Register menu items for the plugin.
     * @since 1.0
     */
    public function register_wp_gw_menu_pages()
    {
        $admin_page = new GWAdminPages();

        if (! empty($_GET['_wp_http_referer'])) {
            wp_redirect(remove_query_arg(array( '_wp_http_referer', '_wpnonce' ), stripslashes($_SERVER['REQUEST_URI'])));
            exit;
        }

        add_menu_page(
            __('My Dashboard', 'wp-gw'),
            __('My Dashboard', 'wp-gw'),
            'manage_exam',
            'gw-pre-listing',
            array( $admin_page, 'gw_pre_listing' ),
            'dashicons-schedule',
            3
        );

        $hook_new_student = add_submenu_page(
            'gw-pre-listing',
            __('Pre Listing (New Student)', 'wp-gw'),
            __('New Student', 'wp-gw'),
            'manage_exam',
            'gw-pre-listing-new',
            array( $admin_page, 'gw_pre_listing_new' )
        );

        $hook_old_student = add_submenu_page(
            'gw-pre-listing',
            __('Pre Listing (Old Student)', 'wp-gw'),
            __('Old Student', 'wp-gw'),
            'manage_exam',
            'gw-pre-listing-old',
            array( $admin_page, 'gw_pre_listing_old' )
        );

        add_submenu_page(
            'gw-pre-listing',
            __('Upload Old Student', 'wp-gw'),
            __('Upload Old Student', 'wp-gw'),
            'manage_options',
            'gw-upload-old-student',
            array( $admin_page, 'gw_upload_old_student' )
        );

        add_submenu_page(
            'gw-pre-listing',
            __('Upload Exam Results', 'wp-gw'),
            __('Upload Exam Results', 'wp-gw'),
            'manage_options',
            'gw-upload-exam',
            array( $admin_page, 'gw_upload_exam' )
        );

        add_submenu_page(
            'gw-pre-listing',
            __('Upload Admission Info', 'wp-gw'),
            __('Upload Admission Info', 'wp-gw'),
            'manage_options',
            'gw-admission-info',
            array( $admin_page, 'gw_admission_info' )
        );

        add_submenu_page(
            'gw-pre-listing',
            __('Semester Settings', 'wp-gw'),
            __('Semester Settings', 'wp-gw'),
            'manage_options',
            'gw-settings-semester',
            array( $admin_page, 'gw_settings_semester' )
        );

      //  add_action("load-$hook", array( $this, 'wp_gw_add_options')); // Depracate

        // New Hooks for Student Variations
        add_action("load-$hook_new_student", array( $this, 'gw_new_student_options'));
        add_action("load-$hook_old_student", array( $this, 'gw_old_student_options'));

    }

    public function gw_new_student_options()
    {
      global $gwEntriesNewStudentTable;

      $option = 'per_page';
      $args = array(
          'label' => 'Pre Listing - New Students',
          'default' => 30,
          'option' => 'new_entries_per_page'
      );
      add_screen_option($option, $args);
      $gwEntriesNewStudentTable = new GWEntriesNewStudentTable();
    }

    public function gw_old_student_options()
    {
      global $gwEntriesOldStudentTable;

      $option = 'per_page';
      $args = array(
          'label' => 'Pre Listing - Old Student',
          'default' => 30,
          'option' => 'old_entries_per_page'
      );
      add_screen_option($option, $args);
      $gwEntriesOldStudentTable = new GWEntriesOldStudentTable();
    }

    // /**
    //  * Form Entries table options
    //  */
    // public function wp_gw_add_options()
    // {
    //     global $gwEntriesTable; // Deprecate
    //
    //     $option = 'per_page';
    //     $args = array(
    //         'label' => 'Pre Listing',
    //         'default' => 30,
    //         'option' => 'entries_per_page'
    //     );
    //     add_screen_option($option, $args);
    //     $gwEntriesTable = new GWEntriesTable();
    // }

    /**
     * Set form entry table options
     *
     * @param $status
     * @param $option
     * @param $value
     * @return mixed
     */
    public function wp_gw_set_options($status, $option, $value)
    {
        return $value;
    }

    /**
     * Register admin pages assets
     */
    public function register_gw_admin_plugin_scripts()
    {
        global $current_user;
        wp_get_current_user();


        // Entries Manager Assets
        wp_register_style('gw-admin', WP_GW_URL . 'assets/admin/css/gw-style.css', array( 'wp-jquery-ui-dialog' ));
        wp_register_script('gw-admin', WP_GW_URL . 'assets/admin/js/gw-script.js', array('jquery', 'jquery-ui-dialog'));
        wp_localize_script('gw-admin', 'gwAjax', array( 'ajaxurl' => admin_url('admin-ajax.php')));
    }

    /**
     * Load admin pages assets only to the specific page
     *
     * @param $hook
     */
    public function load_gw_admin_plugin_scripts($hook)
    {

        // Load Default form entry manager
        if (!($hook == 'my-dashboard_page_gw-pre-listing-new' || $hook == 'my-dashboard_page_gw-pre-listing-old')) {
            return;
        }

        // Load style & scripts.

        wp_enqueue_style('gw-admin');
        wp_enqueue_script('gw-admin');

        // Get file resources
        $this->setFileResources();
    }

    /**
     * Register wp frontend assets
     */
    public function register_gw_plugin_script()
    {
        wp_register_style('gw-wp-generic', WP_GW_URL . 'assets/css/gw-generic-style.css', array());
        wp_register_script('gw-wp-generic', WP_GW_URL . 'assets/js/gw-generic-script.js', array('jquery'));
        wp_localize_script('gw-wp-generic', 'gwPublicAjax', array( 'ajaxurl' => admin_url('admin-ajax.php'),  'action' => 'resend_email_public'));

        // Get file resources
        $this->setFileResources();
    }

    public function setFileResources()
    {
        if (isset($_REQUEST['action']) && isset($_REQUEST['filename']) && isset($_REQUEST['token']) && isset($_REQUEST['id'])) {
            ob_clean();

            $action = sanitize_text_field($_REQUEST['action']);
            $user_id = sanitize_text_field($_REQUEST['id']);
            $filename = sanitize_text_field($_REQUEST['filename']);
            $token = sanitize_text_field($_REQUEST['token']);

            if ($action == 'file') {
                if (! wp_verify_nonce($token, $user_id)) {
                    echo "404 Not Found";
                    die();
                }

                $user_type = "new";

                $wp_upload_dir = wp_get_upload_dir()['basedir'];
                $upload_directory = "{$wp_upload_dir}/user-requirements/{$user_type}/{$user_id}";

                if(!file_exists($upload_directory)){
                  $user_type = "old";
                  $upload_directory = "{$wp_upload_dir}/user-requirements/{$user_type}/{$user_id}";
                }

                $file_link = $upload_directory . "/" . $filename;
                if (is_file($file_link)) {
                    $this->getFile($file_link);
                } else {
                    echo "404 Not Found";
                }
            }
            die();
        }
    }

    public function getMIME($fg_content)
    {
        $file_info = new finfo(FILEINFO_MIME_TYPE);
        $mime_type = $file_info->buffer($fg_content);
        return $mime_type;
    }

    public function getFile($file_link)
    {
        if (false !== ($data = file_get_contents($file_link))) {
            header('Content-type: '. $this->getMIME($data));
            echo $data;
        } else {
            echo "404 Not Found";
        }
    }

    /**
     * Load wp frontend assets
     */
    public function load_gw_plugin_script()
    {
        wp_enqueue_style('gw-wp-generic');
        wp_enqueue_script('gw-wp-generic');
    }

    /**
     * Add custom link for the plugin beside activate/deactivate links
     * @param array $links Array of links to display below our plugin listing.
     * @return array Amended array of links.    *
     * @since 1.0
     */
    public function wp_gw_plugin_action_links($links)
    {
        return array_merge(array(
            '<a href="' . admin_url('admin.php?page=gw-exam-results-manager') . '">' . __('Exam Results', 'wp-gw') . '</a>'
        ), $links);
    }
}
