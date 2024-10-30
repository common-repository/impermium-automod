<?php
if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}
class ImprUtils {
  public static function is_logged_in_and_current_user($user_id) {
    global $current_user;
    self::get_currentuserinfo();

    return (self::is_user_logged_in() and ($current_user->ID == $user_id));
  }
  
  public static function is_logged_in_and_an_admin() {
    return (self::is_user_logged_in() and self::is_admin());
  }
  
  public static function is_logged_in_and_a_subscriber() {
    return (self::is_user_logged_in() and self::is_subscriber());
  }
  
  public static function is_admin() {
    return current_user_can('administrator');
  }

  public static function is_subscriber() {
    return (current_user_can('subscriber') and !current_user_can('contributor'));
  }
  
  public static function is_at_least_a_subscriber() {
    return current_user_can('read');
  }
  
  public static function get_slug($post_id) {
	return basename( get_permalink( $post_id ) );
  }

  public static function current_user_uuid() {
	global $current_user;
    self::get_currentuserinfo();

    return get_user_meta($current_user->ID, '_impr_uuid', true);
  }

  public static function object_to_string($object) {
    ob_start();
    print_r($object);
    $obj_string = ob_get_contents();
    ob_end_clean();
    return $obj_string;
  }
  
  public static function get_currentuserinfo() {
    ImprUtils::_include_pluggables('get_currentuserinfo');
    return get_currentuserinfo();
  }
  
  public static function get_userdata($id) {
    ImprUtils::_include_pluggables('get_userdata');
    $data = get_userdata($id);
    // Handle the returned object for wordpress > 3.2
    if (!empty($data->data))
    {
      return $data->data;
    }
    return $data;
  }

  public static function get_userdatabylogin($screenname) {
    ImprUtils::_include_pluggables('get_userdatabylogin');
    $data = get_userdatabylogin($screenname);
    // Handle the returned object for wordpress > 3.2
    if (!empty($data->data))
    {
      return $data->data;
    }
    return $data;
  }

  public static function wp_mail($recipient, $subject, $message, $header) {
    ImprUtils::_include_pluggables('wp_mail');
    return wp_mail($recipient, $subject, $message, $header);
  }

  public static function is_user_logged_in() {
    ImprUtils::_include_pluggables('is_user_logged_in');
    return is_user_logged_in();
  }

  public static function get_avatar( $id, $size ) {
    ImprUtils::_include_pluggables('get_avatar');
    return get_avatar( $id, $size );
  }

  public static function wp_hash_password( $password_str ) {
    ImprUtils::_include_pluggables('wp_hash_password');
    return wp_hash_password( $password_str );
  }

  public static function wp_generate_password( $length, $special_chars ) {
    ImprUtils::_include_pluggables('wp_generate_password');
    return wp_generate_password( $length, $special_chars );
  }

  public static function wp_redirect( $location, $status=302 ) {
    ImprUtils::_include_pluggables('wp_redirect');
    return wp_redirect( $location, $status );
  }

  public static function wp_salt( $scheme='auth' ) {
    ImprUtils::_include_pluggables('wp_salt');
    return wp_salt( $scheme );
  }

  public static function _include_pluggables($function_name) {
    if(!function_exists($function_name))
      require_once(ABSPATH . WPINC . '/pluggable.php');
  }
}