<?php
define( 'ABSPATH', dirname(__FILE__) );
define( 'CONTROLLERS_PATH', ABSPATH . '/../app/controllers' );
define( 'HELPERS_PATH', ABSPATH . '/../app/helpers' );
define( 'MODELS_PATH', ABSPATH . '/../app/models' );

// Include the test framework
include_once(ABSPATH . '/EnhanceTestFramework.php');

// Load the wordpress mocked functions
include_once(ABSPATH . '/mockpress/mockpress.php');

// Load the application specific helpers
include_once(ABSPATH . '/test_helper.php');

\Enhance\Core::discoverTests(ABSPATH . '/tests/');
//require_once('./tests/ImprRemoteTests.php');

// Run the tests
\Enhance\Core::runTests();
