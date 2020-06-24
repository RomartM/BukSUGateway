<?php


class FormHandlers
{
    public function __construct()
    {
    }

    private function meta($page){
        global $wp;
        $get_login_url =  add_query_arg( array(
            'page' => $page
        ), home_url( $wp->request ) . '/');

        $login_nonce = wp_create_nonce( 'gw_' . $get_login_url );
        $action_url = esc_url( admin_url('admin-post.php') );

        return array($get_login_url, $login_nonce, $action_url);
    }

    private function post_sanitize($success_callback, $error_callback){
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

    private function new_login(){
        $this->post_sanitize(
            function(){ // Success
                $referer_page = _gw_parse_url(wp_get_referer());
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
                        _gw_redirect( 'pass_process', null, '/my/' );
                    }else{
                        _gw_redirect( $referer_page['page'], 404, "", wp_get_referer() ); // User does not exists
                    }
                }else{
                    _gw_redirect( $referer_page['page'], 417, "", wp_get_referer() ); // All fields are reuired
                }
            },
            function($error){
                $referer_page = _gw_parse_url(wp_get_referer());
                _gw_redirect( $referer_page['page'], 400, "", wp_get_referer() ); // Bad Request
            }
        );
    }

    private function old_login(){
        $this->post_sanitize(
            function(){ // Success
                print_r('Success');
            },
            function($error){ // Error
                print_r($error);
            }
        );
    }

    private function get_user($data_entry){
        global $wpdb;
        $tablename = $wpdb->prefix."exam_results";
        $query = "SELECT * FROM {$tablename} where
		EXAMINEE_NO='{$data_entry["EXAMINEE_NO"]}' AND
		EXAMINATION_DATE='{$data_entry["EXAMINATION_DATE"]}' AND
		EXAMINATION_TIME='{$data_entry["EXAMINATION_TIME"]}' AND
		BIRTHDATE='{$data_entry["BIRTHDATE"]}'";
        return $wpdb->get_results($query, OBJECT);
    }
}