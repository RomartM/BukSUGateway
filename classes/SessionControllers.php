<?php


class SessionControllers
{

    public function __construct()
    {
        add_filter( PLUGIN_PREFIX . 'session_validate', array($this, 'validate'));
        add_filter( PLUGIN_PREFIX . 'session_set', array($this, 'set'), 10, 3);
        add_filter( PLUGIN_PREFIX . 'session_reset', array($this, 'reset'));
    }

    private function set($data, $scope='/', $expiration=''){
        if(empty($expiration)){
            $expiration = mktime(24,0,0); // The next day
        }
        setcookie('gw',
            gw_encrypt_data(json_encode($data)),
            $expiration,
            $scope,
            _gw_remove_http(get_site_url()),
            false, // Only transmit on SSL connection
            true  // Only accessible by http no js
        );
    }

    private function validate(){
        if(isset($_COOKIE['gw'])){
            try {
                $raw = gw_decrypt_data(stripslashes($_COOKIE['gw']));
                if(!empty(json_decode($raw)->{'EXAMINEE_NO'}) && !empty(json_decode($raw)->{'EXAM_STATUS'})){
                    $value = apply_filters('gw_user_set', json_decode($raw));
                    return true;
                }
            } catch(Exception $e) {
                return false;
            }
        }
        return false;
    }

    private function reset(){
        unset($_COOKIE['gw']);
        return apply_filters('gw_session_set', null, '/', time());
    }
}