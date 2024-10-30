<?php
require_once( MODELS_PATH . '/ImprUtils.php' );
require_once( MODELS_PATH . '/ImprRemote.php' );
require_once( MODELS_PATH . '/ImprAccount.php' );

// Naming: By using "extends \Enhance\TestFixture" you signal that the public methods in
// your class are tests.
class ImprAccountTests extends \Enhance\TestFixture {
  public function setUp() {
	if(!isset($_SERVER['REMOTE_ADDR']))
	  $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
  }

  public function tearDown() { }
  
  public function account_api_key_is_valid() {
    global $wp_test_expectations;
	
	  $api_key = 'abcd1234';
	  $uri = "/1.0/{$api_key}/key";
	  
	  wp_insert_user(array('ID' => 1));
	  wp_set_current_user(1);
    
    $wp_test_expectations['http_response'] = array( 'uri' => $uri,
                                                    'type' => 'get',
                                                    'body' => json_encode( array( 'blah' => 'blah') ) );
	  $target = \Enhance\Core::getCodeCoverageWrapper('ImprAccount');
	  $result = $target->api_key_is_valid($api_key);
    \Enhance\Assert::areIdentical(true, $result);
    
    $wp_test_expectations['http_response'] = array( 'uri' => $uri,
                                                    'type' => 'get',
                                                    'body' => json_encode( array( 'status' => 400 ) ) );
	  $result = $target->api_key_is_valid($api_key);
    \Enhance\Assert::areIdentical(false, $result);

    $wp_test_expectations['http_response'] = new WP_Error();
	  $result = $target->api_key_is_valid($api_key);
    \Enhance\Assert::areIdentical(false, $result);
  }
}