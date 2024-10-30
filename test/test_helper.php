<?php
define('IMPR_DISPLAY_NAME', 'WP Impermium');
define('IMPR_OPTIONS_SLUG', 'impr_options');

$_SERVER['REMOTE_ADDR'] = '10.0.0.1';

_reset_wp();

global $wp_test_expectations;
$impr_options_array = array( "api_key" => '1234abcd',
                             "tags" => array( "spam"       => "block",
                                              "bulk"       => "block",
                                              "profanity"  => "allow",
                                              "violence"   => "moderate",
                                              "derogatory" => "allow",
                                              "insult"     => "allow",
                                              "error"      => "block"
                                            )
                           );

$wp_test_expectations['options']['impr_options'] = $impr_options_array;