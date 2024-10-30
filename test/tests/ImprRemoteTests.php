<?php
require_once( MODELS_PATH . '/ImprOptions.php' );
require_once( MODELS_PATH . '/ImprUtils.php' );
require_once( MODELS_PATH . '/ImprRemote.php' );

// Naming: By using "extends \Enhance\TestFixture" you signal that the public methods in
// your class are tests.
class ImprRemoteTests extends \Enhance\TestFixture {
  public function setUp() { }

  public function tearDown() { }
  
  public function remote_send() {
	global $remote_body, $wp_test_expectations, $_SERVER;
	$_SERVER['REQUEST_URI'] = 'http://example.com/cool/article/comment';
	
	$target = \Enhance\Core::getCodeCoverageWrapper('ImprRemote');
	
	$endpoint = '/api/yo.json';
	$uri = "http://api.impermium.com{$endpoint}";
	
	$expects = array( 'what' => 'boom' );
	$wp_test_expectations['http_response'] = array( 'uri' => $uri, 'type' => 'get', 'body' => json_encode( $expects ) );
	
    // Successful Result
	$result = $target->send( $endpoint );
    \Enhance\Assert::areIdentical($expects, $result);

    // Throws an error when the http response is messed up
	$wp_test_expectations['http_response'] = new WP_Error();
    \Enhance\Assert::throws( $target, 'send', array( $endpoint ) );

    // Throws an error when the json response is improperly formatted
	$expects = 'dont mess with me';
	$wp_test_expectations['http_response'] = array( 'uri' => $uri, 'type' => 'get', 'body' => $expects );
    \Enhance\Assert::throws( $target, 'send', array( $endpoint ) );
  }
}