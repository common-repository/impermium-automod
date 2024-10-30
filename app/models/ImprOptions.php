<?php
if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}
class ImprOptions {
  public static function fetch() {
    static $impr_options;
    
    if(!isset($impr_options)) {
      $impr_options_array = get_option(IMPR_OPTIONS_SLUG);
      
      // If option didn't exist or unserializing didn't work
      if(!$impr_options_array or !is_array($impr_options_array))
        $impr_options = new ImprOptions(); // Just grab the defaults
      else
        $impr_options = new ImprOptions($impr_options_array); // Sets defaults for unset options
    }

    return $impr_options;
  }

  public static function reset() {
    delete_option(IMPR_OPTIONS_SLUG);
  }
  
  public function __construct($options=array()) {
    $this->set_strings();
    $this->set_from_array($options);
    $this->set_defaults();
  }

  public function set_defaults() {
    if(!isset($this->api_key))
      $this->api_key = '';
      
    if(!isset($this->tags))
      $this->tags = array();
    
    // Initialize any missing tags
    foreach(ImprComment::supported_tags(true) as $tag => $args) {
      if(!isset($this->tags[$tag]))
        $this->tags[$tag] = $args['default'];
    }
  }
  
  /** Returns a list of tags grouped by severity and then alphabetically. */
  public function tag_lookup() {
    $lu = array( 'allow' => array(),
                 'moderate' => array(),
                 'block' => array() );
    
    foreach( $this->tags as $tag => $action )
      $lu[$action][] = $tag;
    
    sort( $lu['allow'] );
    sort( $lu['moderate'] );
    sort( $lu['block'] );
    
    return $lu;
  }
  
  /** Returns a list of tags ordered by how severe the action is for each. */
  public function ordered_tags() {
    $lookup = $this->tag_lookup();
    return array_merge( $lookup['block'], $lookup['moderate'], $lookup['allow'] );
  }

  private function set_strings() {
    $this->api_key_str = 'api_key';
    $this->tags_str    = 'tags';
  }
  
  public function set_from_array($options=array(), $post_array=false) {
    if($post_array) {
      $this->api_key = $options[$this->api_key_str];
      $this->tags = $options[$this->tags_str];
    }
    else {
      // Set values from array
      foreach($options as $key => $value)
        $this->{$key} = $value;
    }
  }

  public function store($validate=true) {
    if($validate) {
      $errors = $this->validate();

      if(empty($errors))
        update_option(IMPR_OPTIONS_SLUG, (array)$this);

      return $errors;
    }

    update_option(IMPR_OPTIONS_SLUG, (array)$this);
  }
  
  public function validate($errors=array()) {
    if( isset($_POST[$this->api_key_str]) and
        !empty($_POST[$this->api_key_str]) and
        !ImprAccount::api_key_is_valid($_POST[$this->api_key_str]) ) {
	    $errors[] = sprintf(__("Your %s API key is invalid"), IMPR_DISPLAY_NAME);
    }
    
    return $errors;
  }
}
