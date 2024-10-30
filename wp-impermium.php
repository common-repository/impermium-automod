<?php
/*
Plugin Name: Impermium AutoModerator
Plugin URI: http://impermium.com
Description: Impermium AutoModerator defends your website against social spam, racist and inappropriate language, and other forms of abuse. 
Version: 3.1.4
Author: Impermium
Author URI: http://impermium.com
Copyright: 2012, Impermium

GNU General Public License, Free Software Foundation <http://creativecommons.org/licenses/GPL/2.0/>
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}

define('IMPR_PLUGIN_SLUG',plugin_basename(__FILE__));
define('IMPR_PLUGIN_NAME',dirname(IMPR_PLUGIN_SLUG));
$impr_script_url = get_site_url() . '/index.php?plugin=impr';
define('IMPR_PATH',WP_PLUGIN_DIR.'/'.IMPR_PLUGIN_NAME);
define('IMPR_IMAGES_PATH',IMPR_PATH.'/images');
define('IMPR_CSS_PATH',IMPR_PATH.'/css');
define('IMPR_JS_PATH',IMPR_PATH.'/js');
define('IMPR_I18N_PATH',IMPR_PATH.'/i18n');
define('IMPR_APIS_PATH',IMPR_PATH.'/app/apis');
define('IMPR_MODELS_PATH',IMPR_PATH.'/app/models');
define('IMPR_CONTROLLERS_PATH',IMPR_PATH.'/app/controllers');
define('IMPR_VIEWS_PATH',IMPR_PATH.'/app/views');
define('IMPR_WIDGETS_PATH',IMPR_PATH.'/app/widgets');
define('IMPR_HELPERS_PATH',IMPR_PATH.'/app/helpers');
define('IMPR_TESTS_PATH',IMPR_PATH.'/tests');
define('IMPR_URL',plugins_url($path = '/'.IMPR_PLUGIN_NAME));
define('IMPR_IMAGES_URL',IMPR_URL.'/images');
define('IMPR_VIEWS_URL',IMPR_URL.'/app/views');
define('IMPR_CSS_URL',IMPR_URL.'/css');
define('IMPR_JS_URL',IMPR_URL.'/js');
define('IMPR_SCRIPT_URL',$impr_script_url);
define('IMPR_OPTIONS_SLUG','impr_options');

/**
 * Returns current plugin name.
 *
 * @return string Plugin name
 */
function impr_plugin_get_name() {
  if( ! function_exists( 'get_plugins' ) )
    require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
  
  $plugin_folder = get_plugins( '/' . plugin_basename( dirname( __FILE__ ) ) );
  $plugin_file = basename( ( __FILE__ ) );

  return $plugin_folder[$plugin_file]['Name'];
}

// Constant for defining the name of the plugin
define('IMPR_DISPLAY_NAME', impr_plugin_get_name());

/**
 * Returns current plugin version.
 *
 * @return string Plugin version
 */
function impr_plugin_get_version() {
  if( ! function_exists( 'get_plugins' ) )
    require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
  
  $plugin_folder = get_plugins( '/' . plugin_basename( dirname( __FILE__ ) ) );
  $plugin_file = basename( ( __FILE__ ) );

  return $plugin_folder[$plugin_file]['Version'];
}

// Constant for defining the version of the plugin
define('IMPR_VERSION', impr_plugin_get_version());


/**
 * Returns current plugin author.
 *
 * @return string Plugin author
 */
function impr_plugin_get_author() {
  if( ! function_exists( 'get_plugins' ) )
    require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
  
  $plugin_folder = get_plugins( '/' . plugin_basename( dirname( __FILE__ ) ) );
  $plugin_file = basename( ( __FILE__ ) );

  return $plugin_folder[$plugin_file]['Author'];
}

// Constant for defining the version of the plugin
define('IMPR_AUTHOR', impr_plugin_get_author());

/**
 * Returns current plugin author uri.
 *
 * @return string Plugin author uri
 */
function impr_plugin_get_author_uri() {
  if( ! function_exists( 'get_plugins' ) )
    require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
  
  $plugin_folder = get_plugins( '/' . plugin_basename( dirname( __FILE__ ) ) );
  $plugin_file = basename( ( __FILE__ ) );

  return $plugin_folder[$plugin_file]['AuthorURI'];
}

// Constant for defining the version of the plugin
define('IMPR_AUTHOR_URI', impr_plugin_get_author_uri());


/**
 * Returns current plugin description.
 *
 * @return string Plugin description
 */
function impr_plugin_get_description() {
  if( ! function_exists( 'get_plugins' ) )
    require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
  
  $plugin_folder = get_plugins( '/' . plugin_basename( dirname( __FILE__ ) ) );
  $plugin_file = basename( ( __FILE__ ) );

  return $plugin_folder[$plugin_file]['Description'];
}

// Constant for defining the version of the plugin
define('IMPR_DESCRIPTION', impr_plugin_get_description());

// Autoload all the requisite classes
function impr_autoloader($class_name) {
  if(preg_match('/^Impr.+$/', $class_name))
  {
    if(preg_match('/^.+Controller$/', $class_name))
      $filepath = IMPR_CONTROLLERS_PATH . "/{$class_name}.php";
    else if(preg_match('/^.+Helper$/', $class_name))
      $filepath = IMPR_HELPERS_PATH . "/{$class_name}.php";
    else
      $filepath = IMPR_MODELS_PATH . "/{$class_name}.php";
    
    if(file_exists($filepath))
      require_once($filepath);
  }
}

// if __autoload is active, put it on the spl_autoload stack
if( is_array(spl_autoload_functions()) and 
    in_array('__autoload', spl_autoload_functions()) ) {
   spl_autoload_register('__autoload');
}

// Add the autoloader
spl_autoload_register('impr_autoloader');

// Gotta load the language before everything else
ImprAppController::load_language();

ImprAppController::load_hooks();
ImprOptionsController::load_hooks();
ImprCommentsController::load_hooks();
ImprUpdateController::load_hooks();

register_deactivation_hook( IMPR_PLUGIN_SLUG, create_function( '', 'require_once( IMPR_PATH . "/deactivation.php");' ) );
register_uninstall_hook( IMPR_PLUGIN_SLUG, create_function( '', 'require_once( IMPR_PATH . "/uninstall.php");' ) );
