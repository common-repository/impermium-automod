<?php
if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}

class ImprUpdateController {
  public static function load_hooks() {
    add_filter( 'pre_set_site_transient_update_plugins', 'ImprUpdateController::queue_update' );
    add_filter( 'plugins_api', 'ImprUpdateController::plugin_info', 11, 3 );
    //add_filter( 'impr_sync_ui', 'ImprUpdateController::queue_button' );
  }

  public static function queue_update( $transient, $force=false ) {
    if( empty( $transient->checked ) )
      return $transient;
    
    $impr_options = ImprOptions::fetch();
    
    if( isset($impr_options->api_key) and !empty($impr_options->api_key) ) {
      $plugin_name       = IMPR_PLUGIN_SLUG;
      $installed_version = $transient->checked[$plugin_name];
      $plugin_slug       = 'wp-impermium';
      $plugin_url        = 'http://impermium.com';
      $script            = IMPR_PATH . '/wp-impermium.php';
      $mothership        = "/1.0/{$impr_options->api_key}/wordpress/version_check/{$installed_version}";
      
      try {
        $plugin_info = ImprRemote::send( $mothership, array(), 'get', 'http://manage-api.impermium.com' );
        
        if( !isset($plugin_info['supported']) or !$plugin_info['supported'] )
          update_option('impr_version_not_supported', true);
        
        if(isset($plugin_info['upgrade_to_version']))
          $curr_version = $plugin_info['upgrade_to_version'];
	      
        if( ( $force or
              ( isset( $curr_version ) and
                version_compare( $curr_version, $installed_version, '>' ) ) ) and
            isset( $plugin_info['upgrade_to_url'] ) ) {
          
          $download_url = $plugin_info['upgrade_to_url'];
          
          if( !empty( $download_url ) and $download_url ) {
            $transient->response[$plugin_name] = (object)array(
              'id'          => $curr_version,
              'slug'        => $plugin_slug,
              'new_version' => $curr_version,
              'url'         => $plugin_url,
              'package'     => $download_url
            );
          }
        }
        else
          throw new Exception( __('Version installed is current') );
      }
      catch(Exception $e) {
        if(isset($transient->response[$plugin_name]))
          unset($transient->response[$plugin_name]);
      }
    }
    
    return $transient;
  }
  
  public static function manually_queue_update() {
    $transient = get_site_transient("update_plugins" );
    set_site_transient( "update_plugins", self::queue_update( $transient, true ) );
  }
  
  public static function queue_button() {
    ?>
    <a href="<?php echo admin_url('options-general.php?page=impr-options&action=queue&_wpnonce=' . wp_create_nonce('ImprUpdateController::manually_queue_update')); ?>"><?php _e('Check for Update')?></a>
    <?php
  }
	
  public static function plugin_info($false, $action, $args) {
    global $wp_version;
    
    if( !isset($action) or $action != 'plugin_information' )
      return false;
    
    if( isset( $args->slug) && !preg_match( "#.*" . $args->slug . ".*#", IMPR_PLUGIN_NAME ) )
			return false;
		
		$impr_options = ImprOptions::fetch();
		$installed_version = IMPR_VERSION;
		
    $mothership = "/1.0/{$impr_options->api_key}/wordpress/version_check/{$installed_version}";
    
    try {
      $plugin_info  = ImprRemote::send( $mothership, array(), 'get', 'http://manage-api.impermium.com' );
      $new_version  = $plugin_info['upgrade_to_version'];
      $download_url = $plugin_info['upgrade_to_url'];
    } catch(Exception $e) {
      $new_version  = $installed_version;
      $download_url = 'http://impermium.com';
    }
		
    return (object) array( "slug" => IMPR_PLUGIN_NAME,
                            "name" => IMPR_DISPLAY_NAME,
                            "author" => '<a href="http://impermium.com">' . IMPR_AUTHOR . '</a>',
                            "author_profile" => "http://impermium.com",
                            "contributors" => array( "Impermium" => "http://impermium.com" ),
                            "homepage" => "http://impermium.com",
                            "version" => $new_version,
                            "new_version" => $new_version,
                            "requires" => $wp_version,
                            "tested" => $wp_version,
                            "compatibility" => array( $wp_version => array ( $new_version => array( 100, 0, 0))),
                            "rating" => "100.00",
                            "num_ratings" => "1",
                            "downloaded" => "1000",
                            "added" => "2012-07-26",
                            "last_updated" => "2012-07-26",
                            "tags" => array( "moderation" => __("Moderation"),
                                             "spam" => __("Spam"),
                                             "block" => __("Block"),
                                             "comments" => __("Comments"),
                                             "moderate" => __("Moderate"),
                                             "profanity" => __("Profanity"),
                                             "hate" => __("Hate"),
                                             "bulk" => __("Bulk"),
                                             "insult" => __("Insult") ),
                            "sections" => array( "description" => "<p>" . IMPR_DESCRIPTION . "</p>" ),
                            "download_link" => $download_url );
  }
}