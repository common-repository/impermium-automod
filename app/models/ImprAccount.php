<?php
if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}
class ImprAccount {
  public static function api_key_is_valid( $api_key ) {
	  $endpoint = "/1.0/{$api_key}/key";
	  
	  try {
	    $resp = ImprRemote::send( $endpoint, array(), 'get', 'http://manage-api.impermium.com' );
	    if( isset( $resp['status'] ) and preg_match( '#4\d*#', $resp['status'] ) )
	      return false;
	  }
	  catch(Exception $e) {
	    return false;
	  }
	  
	  return true;
  }
}