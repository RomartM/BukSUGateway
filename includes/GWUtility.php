<?php

if (! defined( 'ABSPATH' ) ){
    exit;
}

/**
 * Utilities class - singleton class
 * @since 1.0
 */
class GWUtility {

    public function __construct() {
        add_action('gw_admin_notice', array($this, 'admin_notice'));
        add_action('gw_admin_notice_no_student', array($this, 'no_student_admin_notice'));
    }

    /**
     * Get the singleton instance of the GWUtility class
     *
     * @return GWUtility instance of GWUtility
     */
    public static function instance() {

        static $instance = NULL;
        if ( is_null( $instance ) ) {
            $instance = new GWUtility();
        }
        return $instance;
    }

    /**
     * Format group name to underscores
     *
     * @param $group_name
     * @return string|string[]
     */
    public function format_group_name($group_name){
        return str_replace(' ', '_', strtolower($group_name));
    }

    /**
     * Load Plugin Classes
     *
     * @param array $classes_dir
     */
    public function load_classes($classes_dir = array()){
        forEach($classes_dir as $dir){
            $dir_path = WP_GW_ROOT . '/' . $dir;
            if(file_exists($dir_path)){
                $array = explode('/', $dir);
                if ( ! class_exists( explode('.',end($array))[0] ) ) {
                    include( WP_GW_ROOT . '/' . $dir );
                }
            }
        }
    }

    /**
     * Get current storage url
     */
    public function get_current_storage(){
        $is_remote = get_option( WP_GW_OPTION_PREFIX . "is_storage_remote'");
        if(!empty($is_remote)){
            return get_option( WP_GW_OPTION_PREFIX . "storage_remote_url'") . '/';
        }else{
            return WP_GW_STORAGE_PATH;
        }
    }

    /**
     * Get current datetime
     *
     * @return false|string
     */
    function get_date() {
        $date_format = get_option('date_format');
        $time_format = get_option('time_format');
        return date("{$date_format} {$time_format}", current_time('timestamp'));
    }

    /**
     * Prints message (string or array) in the debug.log file
     *
     * @param mixed $message
     */
    public function logger( $message ) {
        if ( WP_DEBUG === true ) {
            if ( is_array( $message ) || is_object( $message ) ) {
                error_log( print_r( $message, true ) );
            } else {
                error_log( $message );
            }
        }
    }

    /**
     * Display error or success message in the admin section
     *
     * @param array $data containing type and message
     * @return string with html containing the error message
     *
     * @since 1.0
     */
    public function admin_notice( $data = array() ) {
        // extract message and type from the $data array
        $message = isset( $data['message'] ) ? $data['message'] : "";
        $message_type = isset( $data['type'] ) ? $data['type'] : "";
        switch ( $message_type ) {
            case 'error':
                $admin_notice = '<div id="message" class="error notice is-dismissible">';
                break;
            case 'update':
                $admin_notice = '<div id="message" class="updated notice is-dismissible">';
                break;
            case 'update-nag':
                $admin_notice = '<div id="message" class="update-nag">';
                break;
            default:
                $message = __( 'There\'s something wrong with your code...', 'wp-gw' );
                $admin_notice = "<div id=\"message\" class=\"error\">\n";
                break;
        }

        $admin_notice .= "    <p>" . __( $message, 'wp-gw' ) . "</p>\n";
        $admin_notice .= "</div>\n";
        echo $admin_notice;
    }

    public function no_student_admin_notice(){
      $data = array('type' => 'error', 'message'=>'No student matched.' );
      $this->admin_notice( $data );
      echo sprintf('<a href="?page=%s" class="button button-secondary">Back to Student Lists</a>', $_REQUEST['page']);
      exit;
    }

    /**
     * Utility function to get the current user's role
     *
     * @since 1.0
     */
    public function get_current_user_role() {
        global $wp_roles;
        foreach ( $wp_roles->role_names as $role => $name ) :
            if ( current_user_can( $role ) )
                return $role;
        endforeach;
        return false;
    }

    public static function _gw_redirect($page, $query=null, $page_slug_name="", $url_to_redirect=null){
        $url = self::_gw_current_page_url($page_slug_name);
        if($url_to_redirect){
            $url = $url_to_redirect;
        }
        $url_to_redirect = add_query_arg( array(
            'page' => $page,
            'q' => $query,
        ),  $url);
        wp_redirect( $url_to_redirect );
        exit();
    }

    public static function _gw_remove_http($url) {
        $disallowed = array('http://', 'https://');
        foreach($disallowed as $d) {
            if(strpos($url, $d) === 0) {
                return explode("/", str_replace($d, '', $url))[0];
            }
        }
        return $url;
    }

    public static function _gw_parse_url($url){
        $parts = parse_url($url);
        parse_str($parts['query'], $query);
        return $query;
    }

    // Get Current Page URL
    public static function _gw_current_page_url($page_slug_name){
        global $wp;
        return home_url( $wp->request . $page_slug_name );
    }

    // Render Shortcode
    public static function _gw_render_shortcode($string){
        return do_shortcode(stripslashes($string));
    }

    // Render Shortcode
    public static function _gw_get_user_display_name($user_id){
      $user = get_user_by( 'ID',  $user_id );

      if(empty($user)){
        return '';
      }

      return $user->display_name;
    }

    // Data encryptions
    // https://gist.github.com/tott/7544453

    public static function gw_enc_meta(){
        $enc_meta['ciphering'] = "AES-128-CTR";
        $enc_meta['options'] = 0;
        $enc_meta['encryption_iv'] = '1234567891011121';
        $enc_meta['encryption_key'] = "GeeksforGeeks";
        return $enc_meta;
    }

    public static function gw_encrypt_data( $decrypted ) {
        list($ciphering, $options, $encryption_iv, $encryption_key) = array_values(self::gw_enc_meta());
        return openssl_encrypt($decrypted, $ciphering,
            $encryption_key, $options, $encryption_iv);
    }

    public static function gw_decrypt_data( $encrypted ) {
        list($ciphering, $options, $decryption_iv, $decryption_key) = array_values(self::gw_enc_meta());
        return openssl_decrypt ($encrypted, $ciphering,
            $decryption_key, $options, $decryption_iv);
    }

    /**
     * Utility function to get the current user's role
     *
     * @param $error
     * @since 1.0
     */
    public static function debug_log($error){
        try {
            if( ! is_dir( WP_GW_PATH.'logs' ) ) {
                mkdir( WP_GW_PATH . 'logs', 0755, true );
            }
        } catch (Exception $e) {

        }
        try {
            $log = fopen( WP_GW_PATH . "logs/log.txt", 'a');
            if ( is_array( $error ) ) {
                fwrite($log, print_r(date_i18n( 'j F Y H:i:s', current_time( 'timestamp' ) )." \t PHP ".phpversion(), TRUE));
                fwrite( $log, print_r($error, TRUE));
            } else {
                $result = fwrite($log, print_r(date_i18n( 'j F Y H:i:s', current_time( 'timestamp' ) )." \t PHP ".phpversion()." \t $error \r\n", TRUE));
            }
            fclose( $log );
        } catch (Exception $e) {

        }
    }
}

//new GWUtility();
