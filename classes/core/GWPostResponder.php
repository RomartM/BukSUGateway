<?php


class GWPostResponder
{
    public function __construct()
    {
        // New Student login
        add_action( 'admin_post_nopriv_gw_new_login', array( $this, 'new_student_login') );
        add_action( 'admin_post_gw_new_login', array( $this, 'new_student_login') );

        // Old student login
        add_action( 'admin_post_nopriv_gw_old_login', array( $this, 'old_student_login') );
        add_action( 'admin_post_gw_old_login', array( $this, 'old_student_login') );

        // Filters
        add_filter('gw_form_meta', array($this, 'form_metadata'));
        add_filter('gw_format_date', array($this, 'format_date'));
        add_filter('gw_get_user', array($this, 'get_user'));

        // Session Filters
        add_filter('gw_session_validate', array( $this, 'session_validate' ));
        add_filter('gw_session_set', array( $this, 'session_set' ), 10, 3);
        add_filter('gw_session_reset', array( $this, 'session_reset' ));

        // User Validation
        add_action('gw_validate_login', array($this, 'validate_login',), 10, 2);
        add_action('gw_validate_exam_status', array($this, 'validate_exam_status'));
    }

    public function sanitizer($success_callback, $error_callback){
        if ( 'POST' === $_SERVER['REQUEST_METHOD'] ){
            if( isset($_POST['gw_login_nonce']) ){
                $get_referer_url = 'gw_' .  wp_get_referer();

                if( wp_verify_nonce($_POST['gw_login_nonce'], $get_referer_url ) ){
                    return $success_callback();
                }
                return $error_callback('nonce_invalid');
            }
            return $error_callback('nonce_null');
        }
        return $error_callback('post_null');
    }

    public function new_student_login(){
        $this->sanitizer(
            function (){
                $referer_page = GWUtility::_gw_parse_url(wp_get_referer());
                if( isset($_POST['gw_examinee_number']) &&
                    isset($_POST['gw_date_of_exam']) &&
                    isset($_POST['gw_date_of_birth']) &&
                    isset($_POST['gw_time_of_exam']) ){

                    $data_entry = array(
                        "EXAMINEE_NO" => sanitize_text_field( $_POST['gw_examinee_number'] ),
                        "EXAMINATION_DATE" => apply_filters('gw_format_date', sanitize_text_field( $_POST['gw_date_of_exam'] )),
                        "BIRTHDATE" => apply_filters('gw_format_date', sanitize_text_field( $_POST['gw_date_of_birth'] )),
                        "EXAMINATION_TIME" => str_replace(' ', '', sanitize_text_field( $_POST['gw_time_of_exam'] ))
                    );

                    $initial_user_data = apply_filters('gw_get_user', $data_entry);

                    if(count($initial_user_data)!=0){
                        $user_data = array(
                            'FIRST_NAME' => $initial_user_data[0]->{'FIRST_NAME'},
                            'MIDDLE_NAME' => $initial_user_data[0]->{'MIDDLE_NAME'},
                            'LAST_NAME' => $initial_user_data[0]->{'LAST_NAME'},
                            'NAME_SUFFIX' => $initial_user_data[0]->{'NAME_SUFFIX'},
                            'FULL_NAME' => sprintf("%s %s %s %s",
                                $initial_user_data[0]->{'FIRST_NAME'},
                                $initial_user_data[0]->{'MIDDLE_NAME'},
                                $initial_user_data[0]->{'LAST_NAME'},
                                $initial_user_data[0]->{'NAME_SUFFIX'}),
                            'SEX' => $initial_user_data[0]->{'SEX'},
                            'EXAMINATION_DATE' => $initial_user_data[0]->{'EXAMINATION_DATE'},
                            'EXAMINATION_TIME' => $initial_user_data[0]->{'EXAMINATION_TIME'},
                            'EXAMINEE_NO' => $initial_user_data[0]->{'EXAMINEE_NO'},
                            'EXAM_STATUS' => $initial_user_data[0]->{'EXAM_STATUS'},
                            'PERCENT' => str_replace("%", "", $initial_user_data[0]->{'PERCENT'}),
                        );
                        $user_data['STUDENT_TYPE'] = 'new';
                        apply_filters('gw_session_set', $user_data, '/', ''); // Set session
                        GWUtility::_gw_redirect( 'pass_process', null, '/my/' );
                    }else{
                        GWUtility::_gw_redirect( $referer_page['page'], 404, "", wp_get_referer() ); // User does not exists
                    }
                }else{
                    GWUtility::_gw_redirect( $referer_page['page'], 417, "", wp_get_referer() ); // All fields are reuired
                }
            },
            function (){
                $referer_page = GWUtility::_gw_parse_url(wp_get_referer());
                GWUtility::_gw_redirect( $referer_page['page'], 400, "", wp_get_referer() ); // Bad Request
            });
    }

    public function old_student_login(){
        $this->sanitizer(
            function(){ // Success
                print_r('Success');
            },
            function($error){ // Error
                print_r($error);
            }
        );
    }

    public function form_metadata($page){
        global $wp;
        $get_login_url =  add_query_arg( array(
            'page' => $page
        ), home_url( $wp->request ) . '/');

        $login_nonce = wp_create_nonce( 'gw_' . $get_login_url );
        $action_url = esc_url( admin_url('admin-post.php') );

        return array($get_login_url, $login_nonce, $action_url);
    }

    public function get_user($data_entry){
    	global $wpdb;

    	$tablename = $wpdb->prefix."exam_results";
    	$query = "SELECT * FROM {$tablename} where
    		EXAMINEE_NO='{$data_entry["EXAMINEE_NO"]}' AND
    		EXAMINATION_DATE='{$data_entry["EXAMINATION_DATE"]}' AND
    		EXAMINATION_TIME='{$data_entry["EXAMINATION_TIME"]}' AND
    		BIRTHDATE='{$data_entry["BIRTHDATE"]}'";
    	return $wpdb->get_results($query, OBJECT);
    }

    public function session_validate(){
        if(isset($_COOKIE['gw'])){
            try {
                $raw = GWUtility::gw_decrypt_data(stripslashes($_COOKIE['gw']));
                if(!empty(json_decode($raw)->{'EXAMINEE_NO'}) && !empty(json_decode($raw)->{'EXAM_STATUS'})){
                    apply_filters('gw_user_set', json_decode($raw));
                    return true;
                }
            } catch(Exception $e) {
                return false;
            }
        }
        return false;
    }

    public function session_set($data, $scope='/', $expiration=''){
        if(empty($expiration)){
            $expiration = mktime(24,0,0); // The next day
        }
        setcookie('gw',
            GWUtility::gw_encrypt_data(json_encode($data)),
            $expiration,
            $scope,
            GWUtility::_gw_remove_http(get_site_url()),
            false, // Only transmit on SSL connection
            true  // Only accessible by http no js
        );
    }

    public function session_reset(){
        unset($_COOKIE['gw']);
        return apply_filters('gw_session_set', null, '/', time());
    }

    public function format_date($string_date){
        $date = date_create($string_date);
        return date_format($date, "n/j/Y");
    }

    public function validate_login($redirect_if_success=true, $redirect_if_fail=true){
        $login_status = apply_filters('gw_session_validate', null);
        if($login_status){
            if($redirect_if_success){
                GWUtility::_gw_redirect( 'pass_process', null, null );
            }
        }else{
            if($redirect_if_fail){
                GWUtility::_gw_redirect( 'login', null, null );
            }
        }
    }

    public function validate_exam_status($redirect_if_success=true){
        $user_meta = apply_filters('gw_user_set', null);
        if($user_meta->{'EXAM_STATUS'} == 'PASSED'){
            if($redirect_if_success){
                GWUtility::_gw_redirect( 'pass_success', null, null );
            }
        }else{
            GWUtility::_gw_redirect( 'pass_fail', null, null );
        }
    }

}

new GWPostResponder();