<?php
if(!defined('ABSPATH')) { die('You are not allowed to call this page directly.'); }

global $wpdb;

remove_filter( 'pre_set_site_transient_update_plugins', 'ImprUpdateController::queue_update' );

// Remove the wp-cron hooks for sure
if( $time = wp_next_scheduled( 'impr_process_tag_cache' ) )
  wp_unschedule_event( $time, 'impr_process_tag_cache' );
