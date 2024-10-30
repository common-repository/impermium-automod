<?php
if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}
class ImprOptionsController {
  public static function load_hooks() {
    //add_action('impr_sync_ui', 'ImprOptionsController::request_api_key');
  }
  
  public static function route() {
    if(!current_user_can('manage_options'))
      wp_die( __('You do not have sufficient permissions to access this page.', 'wp-impermium') );
    
    if( isset($_REQUEST['action']) and $_REQUEST['action']=='save' and isset($_REQUEST['_wpnonce']) and
        wp_verify_nonce($_REQUEST['_wpnonce'],'ImprOptionsController::save' ) )
      self::save();
    else if( isset($_REQUEST['action']) and $_REQUEST['action']=='reset' and isset($_REQUEST['_wpnonce']) and
             wp_verify_nonce($_REQUEST['_wpnonce'],'ImprOptionsController::reset') )
      self::reset();
    else if( isset($_REQUEST['action']) and $_REQUEST['action']=='queue' and isset($_REQUEST['_wpnonce']) and
             wp_verify_nonce($_REQUEST['_wpnonce'],'ImprUpdateController::manually_queue_update') )
      ImprUpdateController::manually_queue_update();
    else if( isset($_REQUEST['action']) and
             $_REQUEST['action']=='api_request_success' and
             isset($_REQUEST['_wpnonce']) and
             wp_verify_nonce($_REQUEST['_wpnonce'],'api_request_success') )
      self::api_request_success();
    else
      self::display();
  }
  
  public static function display() {
    $impr_options = ImprOptions::fetch();
    require IMPR_VIEWS_PATH . '/options/ui.php';
  }

  public static function save() {
    $impr_options = ImprOptions::fetch();

    // Set values from post
    $impr_options->set_from_array($_REQUEST, true);
    $errors = $impr_options->store();
    
    if( empty($errors) ) {
      $impr_options = ImprOptions::fetch(); // re-fetch
      $message = __('Your options were saved successfully', 'wp-impermium'); 
      require IMPR_VIEWS_PATH . '/options/ui.php';
    }
    else {
	    $_REQUEST = $_POST = array();
      require IMPR_VIEWS_PATH . '/options/ui.php';
    }
  }

  public static function reset() {
    ImprOptions::reset();
    $message = __('Your options have successfully been reset', 'wp-impermium');
    require IMPR_VIEWS_PATH . '/options/ui.php';
  }
  
  public static function request_api_key() {
    global $current_user;
    $impr_options = ImprOptions::fetch();
    
    if(( is_null($impr_options->api_key) or empty($impr_options->api_key) ) and
       ( !isset($_POST['api_key']) or empty($_POST['api_key']) )) {
	
	  wp_get_current_user();
	
	  $first_name = ( isset($_POST['first_name']) ? $_POST['first_name'] : $current_user->first_name );
      $last_name = ( isset($_POST['last_name']) ? $_POST['last_name'] : $current_user->last_name );
      $title = ( isset($_POST['title']) ? $_POST['title'] : '' );
      $company = ( isset($_POST['company']) ? $_POST['company'] : '' );
      $email = ( isset($_POST['email']) ? $_POST['email'] : $current_user->user_email );
      $phone = ( isset($_POST['phone']) ? $_POST['phone'] : '' );
      $website = ( isset($_POST['website']) ? $_POST['website'] : site_url() );
      $description = ( isset($_POST['description']) ? $_POST['description'] : '' );

      $nonce = wp_create_nonce('api_request_success');
      $success_url = admin_url("options-general.php?page=impr-options&action=api_request_success&_wpnonce={$nonce}");
      
      require( IMPR_VIEWS_PATH . "/shared/request_api_key.php" );
    }
  }

  public static function api_request_success() {
    $message = __('Your request for an API key was successfully sent.', 'wp-impermium'); 
    require IMPR_VIEWS_PATH . '/options/ui.php';
  }
}