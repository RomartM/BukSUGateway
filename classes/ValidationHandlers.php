<?php


class ValidationHandlers
{

    public function __construct()
    {
        add_action( PLUGIN_PREFIX . 'validate_login', array($this, 'login'), 10, 2);
        add_action( PLUGIN_PREFIX . 'validate_exam_status', array($this, 'exam_status'));
    }

    private function login($redirect_if_success=true, $redirect_if_fail=true){
        $login_status = _gw_session_validate();
        if($login_status){
            if($redirect_if_success){
                _gw_redirect( 'pass_process', null, null );
            }
        }else{
            if($redirect_if_fail){
                _gw_redirect( 'login', null, null );
            }
        }
    }

    private function exam_status($redirect_if_success=true){
        $user_meta = apply_filters('gw_user_set', null);
        if($user_meta->{'EXAM_STATUS'} == 'PASSED'){
            if($redirect_if_success){
                _gw_redirect( 'pass_success', null, null );
            }
        }else{
            _gw_redirect( 'pass_fail', null, null );
        }
    }

}