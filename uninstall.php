<?php
if(!defined('ABSPATH')) { die('You are not allowed to call this page directly.'); }

// This is called out of the context of the plugin
// we're going to load it up before beheading it :)
include_once plugin_dir_path( __FILE__ ).'wp-impermium.php';

if( !defined( 'WP_UNINSTALL_PLUGIN' ) ) { wp_die( __( "You're not doing it right", 'wp-impermium' ) ); }

if( IMPR_PLUGIN_SLUG == WP_UNINSTALL_PLUGIN ) {
  global $wpdb;

  remove_filter( 'pre_set_site_transient_update_plugins', 'ImprUpdateController::queue_update' );

  // Clear The WP-Impermium options
  delete_option('impr_options');

  // Clear all of the WP-Impermium tags
  $query = $wpdb->prepare( "DELETE FROM {$wpdb->commentmeta} WHERE meta_key LIKE 'impr_%'" );
  $wpdb->query($query);
  
  // Remove the wp-cron hooks for sure
  if( $time = wp_next_scheduled( 'impr_process_tag_cache' ) )
    wp_unschedule_event( $time, 'impr_process_tag_cache' );
}