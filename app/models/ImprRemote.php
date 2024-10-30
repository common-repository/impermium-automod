<?php
if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}

/***** Define Exceptions *****/
class ImprHttpException extends Exception { }
class ImprRemoteException extends Exception { }
class Impr404Exception extends Exception { }

class ImprRemote {
  public static function send( $endpoint, $args=array(), $method='get', $domain='http://api.impermium.com', $blocking=true ) {
	  $impr_options = ImprOptions::fetch();
    $uri = "{$domain}{$endpoint}";
    
    $arg_array = array( 'body' => $args, 'timeout' => 15, 'blocking' => $blocking );

    if(strtolower($method) == 'get')
      $resp = wp_remote_get( $uri, $arg_array );
    else
      $resp = wp_remote_post( $uri, array_merge($arg_array, array('headers' => array('content-type' => 'application/json'))) );

    // If we're not blocking then the response is irrelevant
    // So we'll just return true.
    if( $blocking==false )
      return true;

    if( is_wp_error( $resp ) ) {
      throw new ImprHttpException( sprintf( __( 'You had an HTTP error connecting to %s' ), IMPR_DISPLAY_NAME ) );
    }
    else {
      if( null !== ( $json_res = json_decode( $resp['body'], true ) ) ) {
        if( isset($json_res['status']) and $json_res['status'] == '404' )
          throw new Impr404Exception( $json_res['message'] );
        else
          return $json_res;
      }
      else
        throw new ImprRemoteException( sprintf( __( 'The %s API sent you an un-decipherable message'), IMPR_DISPLAY_NAME ) );
    }
    
    return false;
  }
}
