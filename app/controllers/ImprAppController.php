<?php
if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}
class ImprAppController {
  public static function load_hooks() {
    //add_action('wp_enqueue_scripts', 'ImprAppController::load_scripts');
    //add_action('wp_print_styles', 'ImprAppController::load_styles');
    add_action('init', 'ImprAppController::routes');
    //add_action('admin_init', 'ImprAppController::process_tag_cache_queue');
    add_action('admin_menu', 'ImprAppController::menu');
    add_action('admin_notices', 'ImprAppController::add_api_key_headline');
    add_action('admin_notices', 'ImprAppController::akismet_conflict_headline');
    add_action('admin_notices', 'ImprAppController::version_not_supported_headline');
  }
  
  public static function menu() {
    $options_menu_hook = add_options_page(IMPR_DISPLAY_NAME, IMPR_DISPLAY_NAME, 'manage_options', 'impr-options', 'ImprOptionsController::route');
    
    add_action( 'admin_print_scripts-'.$options_menu_hook, 'ImprAppController::load_admin_options_scripts' );
    add_action( 'admin_print_styles-'.$options_menu_hook, 'ImprAppController::load_admin_options_styles' );
    
    add_action( 'admin_print_scripts-edit-comments.php', 'ImprAppController::load_admin_comments_scripts' );
    add_action( 'admin_print_styles-edit-comments.php', 'ImprAppController::load_admin_comments_styles' );
    
    add_action( 'admin_print_scripts-index.php', 'ImprAppController::load_admin_dashboard_scripts' );
    add_action( 'admin_print_styles-index.php', 'ImprAppController::load_admin_dashboard_styles' );

    do_action( 'impr_load_options_menu', $options_menu_hook );
  }
  
  public static function load_admin_options_styles() {
    wp_enqueue_style( 'wp-pointer' );
    wp_enqueue_style( 'impr-admin-options', IMPR_CSS_URL . '/admin-options.css', array('wp-pointer') );
  }

  public static function load_admin_options_scripts() {
    wp_enqueue_script('wp-pointer');
    wp_enqueue_script( 'jquery-validate', 'http://ajax.aspnetcdn.com/ajax/jquery.validate/1.8.1/jquery.validate.js', array('jquery') );
    wp_enqueue_script( 'impr-admin-options', IMPR_JS_URL . '/admin-options.js', array('jquery','jquery-validate','wp-pointer') );
  }
  
  public static function load_admin_comments_styles() {
    wp_enqueue_style( 'impr-admin-comments', IMPR_CSS_URL . '/admin-edit-comments.css', array() );
  }

  public static function load_admin_comments_scripts() {	
    wp_enqueue_script( 'impr-admin-comments', IMPR_JS_URL . '/admin-edit-comments.js', array('jquery') );
  }
  
  public static function load_admin_dashboard_styles() {
    wp_enqueue_style( 'impr-admin-dashboard', IMPR_CSS_URL . '/admin-dashboard.css', array() );
  }

  public static function load_admin_dashboard_scripts() {	
    wp_enqueue_script( 'impr-admin-dashboard', IMPR_JS_URL . '/admin-dashboard.js', array('jquery') );
  }
  
  public static function load_language() {
    $path_from_plugins_folder = str_replace( ABSPATH, '', IMPR_PATH ) . '/i18n/';
    load_plugin_textdomain( 'impermium', false, $path_from_plugins_folder );
  }

  public static function routes() {
    if(isset($_REQUEST['plugin']) and $_REQUEST['plugin']=='impr') {
      if(isset($_REQUEST['controller']) and $_REQUEST['controller']=='comments') {
        if(isset($_REQUEST['action']) and $_REQUEST['action']=='queue') {
          if(isset($_REQUEST['nonce']) and wp_verify_nonce($_REQUEST['nonce'],'queue_tag_cache'))
            ImprComment::set_tag_cache(stripslashes($_REQUEST['tk']));
        }
      }
      exit;
    }

    /*
    if( preg_match("#^/impr/([^/]*?)/([^/]*?)/?#", $_SERVER['REQUEST_URI'], $matches) ) {
      if($matches[1][0]=='users')
    }
    */
  }

  public static function load_admin_scripts() {
    $impr_options = ImprOptions::fetch();
    //wp_enqueue_script( 'impr-jquery', $impr_options->remote_url . '/javascripts/jquery.impermium.js', array('jquery') );
  }

  public static function load_admin_styles() {
    $impr_options = ImprOptions::fetch();
	  //wp_enqueue_style( 'impr-css', $impr_options->remote_url . '/stylesheets/impermium.css', array() );
  }

  public static function add_api_key_headline() {
    $impr_options = ImprOptions::fetch();
  
    if(( is_null($impr_options->api_key) or empty($impr_options->api_key) ) and
       ( !isset($_POST['api_key']) or empty($_POST['api_key']) )) {
      require( IMPR_VIEWS_PATH . "/shared/headline.php" );
    }
  }

  public static function akismet_conflict_headline() {
    $impr_options = ImprOptions::fetch();
    
    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    
    if(is_plugin_active('akismet/akismet.php'))
      require( IMPR_VIEWS_PATH . "/shared/akismet.php" );
  }
  
  public static function version_not_supported_headline() {
    if(get_option('impr_version_not_supported')) {
      // Until this is resolved we'll hit the api to see when to take the notice down
      $impr_options = ImprOptions::fetch();
      
      if( isset($impr_options->api_key) and !empty($impr_options->api_key) ) {
        $mothership  = "/1.0/{$impr_options->api_key}/wordpress/version_check/" . IMPR_VERSION;
        
        try {
          $plugin_info = ImprRemote::send( $mothership, array(), 'get', 'http://manage-api.impermium.com' );
          
          if( isset($plugin_info['supported']) and $plugin_info['supported']=='true' )
            delete_option( 'impr_version_not_supported' );
          else
            require( IMPR_VIEWS_PATH . "/shared/version_not_supported.php" );
        }
        catch(Exception $e) {
    	    $errors = array($e->getMessage());
    	    require( IMPR_VIEWS_PATH . "/shared/errors.php" );
    	  }
      }
    }
  }
}
