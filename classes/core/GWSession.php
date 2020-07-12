<?php

/**
 * GW Session Class
 */
class GWSessionClass
{

  private $name;
  private $expiration;
  private $scope;
  private $url;
  private $only_ssl;
  private $only_http;
  private $user_data;

  function __construct($session_name)
  {
    // Set default session
    $this->name = 'gw_'.$session_name;
    $this->expiration = mktime(24, 0, 0);
    $this->scope = '/';
    $this->url = GWUtility::_gw_remove_http(get_site_url());
    $this->only_ssl = false;
    $this->only_http = true;

    // Action Filters Filters
    add_action("gw_session_{$session_name}_set_expiration", array( $this, 'setExpiration' ));
    add_action("gw_session_{$session_name}_set_data", array( $this, 'setData' ), 10, 3);
    add_action("gw_session_{$session_name}_set_cookie", array( $this, 'setCookie' ));
    add_action("gw_session_{$session_name}_set_reset", array( $this, 'reset' ));

    add_filter("gw_session_{$session_name}_validate", array( $this, 'validate' ));
    add_filter("gw_session_{$session_name}_validate_query", array( $this, 'validate_query' ));
  }

  // Setter

  public function setExpiration($expiration)
  {
    $this->expiration = $expiration;
  }

  public function setData($user_id, $user_type, $user_obj)
  {
    $this->user_data = array(
      'uid'   => $user_id,
      'utyp'  => $user_type,
      'uobj'  => $user_obj
    );
  }

  public function setCookie()
  {
    setcookie(
      $this->name,
      GWUtility::gw_encrypt_data(json_encode($this->user_data)),
      $this->expiration,
      $this->scope,
      $this->url,
      $this->only_ssl, // Only transmit on SSL connection
      $this->only_http  // Only accessible by http no js
    );
  }

  // Reset

  public function reset()
  {
    unset($_COOKIE[$this->name]);

    $this->user_data = null;
    $this->setExpiration(time());

    $this->setCookie(); // Set null cookie
  }

  // Validation

  public function validate($success_callback=null)
  {
      if (isset($_COOKIE[$this->name])) {
        try {
          $raw = GWUtility::gw_object_to_array(
            json_decode(
                GWUtility::gw_decrypt_data(stripslashes($_COOKIE[$this->name]))
              )
          );
          if (
               !empty($raw['uid']) &&
               !empty($raw['utyp']) &&
               !empty($raw['uobj'])
             ) { // TODO: Create Database Validation
              if(!empty($success_callback)){
                  return $success_callback($raw);
              }
              return true;
            }
          } catch (Exception $e) {
            return false;
          }
      }
      return false;
  }


  public function validate_query($raw=null)
  {
    try {
      if(empty($raw)){
        $raw = GWUtility::gw_object_to_array(
          json_decode(
              GWUtility::gw_decrypt_data(stripslashes($_COOKIE[$this->name]))
            )
        );
      }

      $data_source = new GWDataTable();
      if($raw['utyp'] == 'new'){
        return $data_source->isNewStudentExists($raw);
      }elseif ($raw['utyp'] == 'old') {
        return $data_source->isOldStudentExists($raw);
      }else{
        return false;
      }

    } catch (\Exception $e) {
      return false;
    }
    return false;
  }


}

// Login Session

new GWSessionClass("login");
new GWSessionClass("user");

 ?>
