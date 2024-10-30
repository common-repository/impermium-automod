<?php
require_once( MODELS_PATH . '/ImprOptions.php' );
require_once( MODELS_PATH . '/ImprUtils.php' );
require_once( MODELS_PATH . '/ImprRemote.php' );
require_once( MODELS_PATH . '/ImprComment.php' );

// Naming: By using "extends \Enhance\TestFixture" you signal that the public methods in
// your class are tests.
class ImprCommentTests extends \Enhance\TestFixture {
  public function setUp() {
	  if(!isset($_SERVER['REMOTE_ADDR']))
      $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
  }

  public function tearDown() { }
  
  public function comment_check() {
    global $wp_test_expectations;
	
    $api_key = 'abcd1234';
    $uri = "/4.0/{$api_key}/post/";
    
    $_SERVER['REQUEST_URI'] = 'http://example.com/cool/article/comment';
    
    // Set default values
    $args = array( 'user_id' => 'ANONYMOUS',
                   'comment_id' => "0",
                   'content' => '',
                   'comment_permalink' => '',
                   'article_permalink' => '',
                   'publisher_id' => 0,
                   'in_response_to' => 0,
                   'enduser_ip' => 0,
                   'operation' => 'CREATE',
                   'first_name' => 'ANONYMOUS',
                   'last_name' => 'USER',
                   'user_url' => '',
                   'email_identity' => 'anonymous@example.com',
                   'timestamp' => date( time() ),
                   'entrypoint' => $_SERVER['REQUEST_URI'],
                   'http_headers' => '',
                   'smtp_headers' => '',
                   'sitekey' => '' );

		$expects = array( "post_type" => "comment",
		                  "response_id" => "79898989",
                      "timestamp" => "20110208123409Z",
                      "4.0" => array(
                        "tags" => array( "spam", "profanity", "violence", "malicious", "anomalous" ),
                        "tag_details" => array( "spam" => array( "score" => 0.92 ),
                                                "violence" => array( "anchor" => array( "kill" ) ),
                                                "anomolous" => array( "confidence" => "low" )
                                              )
                      )
                    );
    
    $wp_test_expectations['http_response'] = array( 'uri' => $uri, 'type' => 'get', 'body' => json_encode( $expects ) );
    $target = \Enhance\Core::getCodeCoverageWrapper('ImprComment');
    $result = $target->check( $args );
    \Enhance\Assert::areIdentical($expects, $result);
  }

  public function comment_approve() {
    global $wp_test_expectations;
	
    $api_key = 'abcd1234';
    $uri = "/4.0/{$api_key}/post/";
	  
		$response = array( "post_type" => "comment",
                       "response_id" => "79898989",
                       "timestamp" => "20110208123409Z",
                       "4.0" => array(
                         "tags" => array( "spam", "profanity", "violence", "malicious", "anomalous" ),
                         "tag_details" => array( "spam" => array( "score" => 0.92 ),
                                                 "violence" => array( "anchor" => array( "kill" ) ),
                                                 "anomolous" => array( "confidence" => "low" )
                                               )
                       )
                     );
    
    $wp_test_expectations['http_response'] = array( 'uri' => $uri, 'type' => 'get', 'body' => json_encode( $response ) );
    
    $comment_data = array( 'comment_date_gmt' => '2012-05-24 11:12:23',
                           'user_id' => 0, // Anonymous
                           'comment_content' => 'This is super cool',
                           'comment_post_ID' => 53,
                           'comment_author_IP' => '10.0.0.1',
                           'comment_author' => 'Bevis Malone',
                           'comment_author_url' => 'http://example.com',
                           'comment_author_email' => 'bevis@example.com'
                         );
    
    $target = \Enhance\Core::getCodeCoverageWrapper('ImprComment');
    $result = $target->approve( 1, $comment_data );
    \Enhance\Assert::areIdentical('spam', $result);

    $response = array( "post_type" => "comment",
                       "response_id" => "79898989",
                        "timestamp" => "20110208123409Z",
                        "4.0" => array(
                          "tags" => array( "profanity" ),
                          "tag_details" => array()
                        )
                      );
    
    $wp_test_expectations['http_response'] = array( 'uri' => $uri, 'type' => 'get', 'body' => json_encode( $response ) );
    $result = $target->approve( 1, $comment_data );
    \Enhance\Assert::areIdentical(1, $result);
  }
  
  public function comment_report_blocked() {
    global $wp_test_expectations;
    
    $comment = (object)array( 'comment_ID' => '53',
                              'comment_content' => 'Blah blah blah blah dingy ding.' );
    
    $impr_options = ImprOptions::fetch();
    
    $uri = "/4.0/{$impr_options->api_key}/post/user_feedback/";
    
    wp_insert_user(array('ID' => 25, 'user_login' => 'cooluser'));
    wp_set_current_user(25);
    
    $expected = array( "4.0"  => array("tag_details" => array(), "tags" => array()),
                       "timestamp" => "20120723210435Z",
                       "event_type" => "comment.user_feedback",
                       "status" => 200,
                       "client_id" => "1319",
                       "hostname" => "events3001.impermium.com",
                       "response_id" => "01BFA0F4-D50A-11E1-87C8-30CECDDE6AB9"
                     );
    
    $wp_test_expectations['http_response'] = array( 'uri' => $uri,
                                                    'type' => 'post',
                                                    'body' => json_encode( $expected ) );
    
    $target = \Enhance\Core::getCodeCoverageWrapper('ImprComment');
    $result = $target->report_blocked( $comment );
    
    \Enhance\Assert::isTrue($result);
  }
}