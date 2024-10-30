<?php
if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}
class ImprComment {
  public static function check( $args=array() ) {
    $impr_options = ImprOptions::fetch();
    
    if( isset($impr_options->api_key) and !empty($impr_options->api_key) ) {
      $endpoint = "/4.0/{$impr_options->api_key}/post/";
      
      // Set default values
      $args = array_merge( array( 'post_type' => 'comment',
                                  'operation' => 'CREATE',
                                  'entrypoint' => $_SERVER['REQUEST_URI'] //,
                                ), $args );
      
      $json = json_encode( $args );
      
      try {
        return ImprRemote::send( $endpoint, $json, 'post' );
      }
      catch(Exception $e) {
        return false;
      }
    }
    
    return false;
  }

  public static function report_blocked( $comment, $action='block' ) {
    global $current_user;
    
    $impr_options = ImprOptions::fetch();
    
    if( isset($impr_options->api_key) and !empty($impr_options->api_key) ) {
      
      if( !function_exists( 'get_currentuserinfo' ) )
        require_once( ABSPATH . WPINC . '/pluggable.php' );
      
      get_currentuserinfo();
      
      $endpoint = "/4.0/{$impr_options->api_key}/post/user_feedback/";
      
      $args = array( "post_type" => "comment",
                     "post_id" => $comment->comment_ID,
                     "content" => $comment->comment_content,
                     "user_id" => (($comment->user_id <= 0) ? 'ANONYMOUS' : $comment->user_id),
                     "desired_result" => array(
                       "4.0" => array(
                          "action" => array( $action )
                        ),
                      ),
                     "reporter_ip" => $_SERVER['REMOTE_ADDR'],
                     "reporter_user_id" => $current_user->user_login,
                     "reporter_user_type" => "MODERATOR"
                   );
      
      // for this one we want to encode it to json
      $json = json_encode($args);
      
      try {
        return ImprRemote::send( $endpoint, $json, 'post', 'http://api.impermium.com', false );
      }
      catch(Exception $e) {
        return false;
      }
    }
    
    return false;
  }

  public static function approve($approved, $cd) {
    // Parse date & reformat
    //$timestamp = $cd['comment_date_gmt'] ....
    $da = date_parse( $cd['comment_date_gmt'] );
    $date = gmmktime( $da['hour'],
                      $da['minute'],
                      $da['second'],
                      $da['month'],
                      $da['day'],
                      $da['year'] );
    $timestamp = strftime('%Y%m%d%H%M%SZ');
    
    // Since we don't have an acutal comment_id at this
    // point lets just hash some stuff together
    $comment_id = md5( get_option('home') . $cd['user_id'] . time() );
    
    $args = array( 'user_id' => ( $cd['user_id'] <= 0 ) ? 'ANONYMOUS' : $cd['user_id'],
                   'post_id' => $comment_id,
                   'content' => $cd['comment_content'],
                   'comment_permalink' => get_permalink($cd['comment_post_ID']),
                   'article_permalink' => get_permalink($cd['comment_post_ID']),
                   'enduser_ip' => $cd['comment_author_IP'],
                   'timestamp' => $timestamp );

    if( $args['user_id'] == 'ANONYMOUS' ) {
      // Attempt to separate the fname & lname from the author field
      preg_match( "#^(.*) ([^ ]*)$#", $cd['comment_author'], $matches );
      
      if( isset($matches[1]) )
        $args['first_name'] = $matches[1];
      
      if( isset($matches[2]) )
        $args['last_name'] = $matches[2];
      
      if( isset($cd['comment_author_url']) )
        $args['user_url'] = $cd['comment_author_url'];
      
      if( isset($cd['comment_author_email']) )
        $args['email_identity'] = $cd['comment_author_email'];
    }

    $res = self::check( $args );
    
    // Uh, is this a bad idea? Effectively using a global variable here
    // but don't know another way to pass this down to the right place
    $_REQUEST['impr_res'] = $res;

    // This is where we'll determine whether or not the comment should be blocked
    // We'll need to add the tags somewhere else
    $tags = isset( $res['4.0']['tags'] ) ? $res['4.0']['tags'] : array();
    
    $approved = self::get_status($tags);

    return $approved;
  }

  public static function filter_comment_clauses( $clauses, $query, $tag ) {
    global $wpdb;
    
//delete_option('impr_tag_cache');
/*
    if( $query->query_vars['count'] )
      $clauses['fields'] = "COUNT( {$wpdb->comments}.comment_ID )";
    else {
      $clauses['fields'] = "{$wpdb->comments}.*, {$wpdb->commentmeta}.meta_value as impr_tags";

      if(!empty($tag)) {
        $tags = explode(',', $tag);
        $tag_array = array( 'relation' => 'OR' );
        foreach( $tags as $k => $t ) {
          $tag_array[$k] = array( 'key' => 'impr_tag_str', 'value' => $t, 'compare' => 'LIKE' );
        }

        $meta_query = get_meta_sql( $tag_array, 'comment', $wpdb->comments, 'comment_ID' );

        $clauses['join'] = $meta_query['join'];
        $clauses['where'] .= ' ' . $meta_query['where'];
      }
    }
*/
    if( !empty($tag) ) {
      if( $tag == 'all' ) {
        if($query->query_vars['count']) {
          $clauses['join'] = "INNER JOIN {$wpdb->commentmeta} AS cm ON " .
                             "{$wpdb->comments}.comment_ID=cm.comment_id AND " .
                             "cm.meta_key='impr_tag_str'";
          $tag_key   = "{$clauses['join']}||{$clauses['where']}";
          $tag_cache = self::get_tag_cache($tag_key);

          $clauses['fields'] .= " + {$tag_cache['count']}";
          $clauses['where']  .= " AND {$wpdb->comments}.comment_ID > {$tag_cache['max']}";
        }
        else {
          $clauses['where'] .= " AND {$wpdb->comments}.comment_ID IN " .
                                    "( SELECT icm.comment_id " .
                                        "FROM {$wpdb->commentmeta} AS icm " .
                                       "WHERE icm.meta_key='impr_tag_str' )";
        }
      }
      else {
        $tags = explode(',', $tag);
        if($query->query_vars['count']) {
          $joins = array();
          $wheres = array();
          foreach($tags as $t) {
            $joins[] = "LEFT JOIN {$wpdb->commentmeta} AS cm_{$t} ON " .
                       "{$wpdb->comments}.comment_ID=cm_{$t}.comment_id AND " .
                       "cm_{$t}.meta_key='impr_tag_{$t}'";
            $wheres[] = "cm_{$t}.meta_key IS NOT NULL";
          }

          //echo "<h2>Hey yo, this is what we got for this bro.</h2><pre>"; print_r($tag_cache); echo "</pre>";

          if(!empty($wheres))
            $clauses['where'] .= " AND (" . implode( ' OR ', $wheres ) . ")";

          if(!empty($joins))
            $clauses['join'] = implode( ' ', $joins );

          $tag_key   = "{$clauses['join']}||{$clauses['where']}";
          $tag_cache = self::get_tag_cache($tag_key);

          $clauses['fields'] .= " + {$tag_cache['count']}";
          $clauses['where']  .= " AND {$wpdb->comments}.comment_ID > {$tag_cache['max']}";
        }
        else {
          $clauses['where'] .= " AND {$wpdb->comments}.comment_ID IN " .
                                      "( SELECT icm.comment_id " .
                                          "FROM {$wpdb->commentmeta} AS icm " .
                                         "WHERE ( icm.meta_key = 'impr_tag_" . implode("' OR icm.meta_key='impr_tag_", $tags) . "') )";
        }
      }
    }

    // We only need this field for determining order so only do it if absolutely necessary
    if( !$query->query_vars['count'] and isset($_REQUEST['orderby']) and $_REQUEST['orderby']=='impr_tags') {
      $clauses['orderby'] = 'impr_tags';
      $clauses['fields'] = "{$wpdb->comments}.*, " .
                           "( SELECT cm.meta_value " .
                               "FROM {$wpdb->commentmeta} AS cm " .
                              "WHERE {$wpdb->comments}.comment_ID=cm.comment_id AND " .
                                    "cm.meta_key='impr_tag_str' ) AS impr_tags";
    }

    return $clauses;
  }
  
  public static function supported_filters( $select=false, $error=false ) {
    $tags = self::supported_tags($error);
    $groups = self::tag_groups();
    
    $filters = array();
    foreach( $tags as $tag => $args ) {
      if(!isset($filters[$args['group']]))
        $filters[$args['group']] = array( 'label' => $groups[$args['group']], 'tags' => array() );
      
      $filters[$args['group']]['tags'][] = $tag;
    }
    
    if( !$select )
      return $filters;
    
    $dropdown = array();
    foreach( $filters as $group => $args ) {
      $dropdown[implode(',',$args['tags'])] = $args['label'];
    }
    
    return $dropdown;
  }
  
  public static function tag_groups() {
    // Filter groups
    return array( 'spam'        => __('Spam'),
                  'bulk'        => __('Bulk'),
                  'profanity'   => __('Profanity'),
                  'insult'      => __('Insult'),
                  'threat'      => __('Threat'),
                  'hate_speech' => __('Hate Speech'),
                  'error'       => __('Error') );
  }
  
  public static function supported_tags( $error=false ) {
    $ary = array( 
      'spam' => array(
        'label' => __('Spam'),
        'info' => __('\'Spam\' applies to comments that have commercial content irrelevant to the discussion at hand.'),
        'default' => 'block',
        'group' => 'spam'
      ),
      'bulk' => array(
        'label' => __('Bulk'),
        'info' => __('\'Bulk\' applies to comments where the same or very similar text is sent multiple times.'),
        'default' => 'block',
        'group' => 'bulk'
      ),
      'mild_profanity' => array(
        'label' => __('Mild Profanity'),
        'info' => __('\'Mild Profanity\' applies to comments that contain mild swear words.'),
        'default' => 'moderate',
        'group' => 'profanity'
      ),
      'strong_profanity' => array(
        'label' => __('Strong Profanity'),
        'info' => __('\'Strong Profanity\' applies to comments that contain strong swear words or slurs.'),
        'default' => 'moderate',
        'group' => 'profanity'
      ),
      'mild_insult' => array(
        'label' => __('Mild Insult'),
        'info' => __('\'Mild Insult\' applies to comments that contain mildly insulting language against a specific person or persons.'),
        'default' => 'moderate',
        'group' => 'insult'
      ),
      'strong_insult' => array(
        'label' => __('Strong Insult'),
        'info' => __('\'Strong Insult\' applies to comments that contain strongly insulting language against a specific person or persons.'),
        'default' => 'moderate',
        'group' => 'insult'
      ),
      'mild_threat' => array(
        'label' => __('Mild Threat'),
        'info' => __('\'Mild Threat\' applies to comments that contain mild threats against a person or group.'),
        'default' => 'moderate',
        'group' => 'threat'
      ),
      'strong_threat' => array(
        'label' => __('Strong Threat'),
        'info' => __('\'Strong Threat\' applies to comments that contain strong threats of physical violence against a person or group.'),
        'default' => 'moderate',
        'group' => 'threat'
      ),
      'hate_speech' => array(
        'label' => __('Hate Speech'),
        'info' => __('\'Hate Speech\' applies to comments that contain strongly offensive content directed against people of a specific race, gender, sexual orientation, etc.'),
        'default' => 'moderate',
        'group' => 'hate_speech'
      )
    );
    
    if($error) {
      $ary['error'] = array( 'label' => __('[error]'),
                             'info' => __('In rare cases, a comment will not get tagged, due to an error in the system. This option allows you to choose the default behavior when a comment comes back with an error.'),
                             'default' => 'allow',
                             'group' => 'error' );
    }

    return $ary;
  }
  
  public static function statically_order_tags($uotags) {
    $atags = array_reverse(array_keys(self::supported_tags()));
    $sot = array();
    foreach($atags as $tag) {
      // if the tag exists in the array then put it into
      // the new statically ordered tag array
      if(in_array($tag,$uotags))
        $sot[] = $tag;
    }
    return $sot;
  }
  
  /** Takes an array of tags and determines the action that should be taken for this comment */
  public static function get_status($tags) {
    $impr_options = ImprOptions::fetch();
    
    $tag_lookup = $impr_options->tag_lookup();
    $block = array_intersect($tag_lookup['block'], $tags);
    
    if(!empty($block))
      return 'spam';
    
    $moderate = array_intersect($tag_lookup['moderate'], $tags);
    
    if(!empty($moderate))
      return 0;
    
    return 1;
  }

  public static function set_tag_cache($tk, $full=false) {
    global $wpdb;
    $tag_cache = get_option("impr_tag_cache");
    //echo "<h2>set_tag_cache 1: impr_tag_cache</h2><pre>";
    //print_r($tag_cache);
    //echo "</pre>";

    if($tag_cache==false)
      $tag_cache=array();

    $tk_array = explode('||', $tk);
 
    //$tags = explode(',',$tk_array[0]);
    $tk_md5 = md5($tk);
    $join = $tk_array[0];
    $where = $tk_array[1];
    $fields = "COUNT(*)";
   
/*
    $join = '';
    $joins = array();
    $wheres = array();
    foreach($tags as $t) {
      $joins[] = "LEFT JOIN {$wpdb->commentmeta} AS cm_{$t} ON " .
                 "{$wpdb->comments}.comment_ID=cm_{$t}.comment_id AND " .
                 "cm_{$t}.meta_key='impr_tag_{$t}'";
      $wheres[] = "cm_{$t}.meta_key IS NOT NULL";
    }

    if(!empty($wheres))
      $where .= " AND (" . implode( ' OR ', $wheres ) . ")";

    if(!empty($joins))
      $join = implode( ' ', $joins );
*/

    $original_where = $where;
    $original_join = $join;
    if(!$full and isset($tag_cache[$tk_md5]) and is_numeric($tag_cache[$tk_md5]['count'])) {
      $fields .= " + {$tag_cache[$tk_md5]['count']}";
      $where .= " AND {$wpdb->comments}.comment_ID > {$tag_cache[$tk]['max']}";
    }
    else
      $where .= " AND {$wpdb->comments}.comment_ID > (SELECT min(comment_id) FROM {$wpdb->commentmeta} WHERE meta_key='impr_tag_str')";

    $count_query = "SELECT {$fields} FROM {$wpdb->comments} {$join} WHERE {$where}";
    $max_query = "SELECT max({$wpdb->comments}.comment_ID) FROM {$wpdb->comments}"; 

    //echo "<h2>Count Query</h2><pre>{$count_query}</pre>";
    //echo "<h2>Max Query</h2><pre>{$max_query}</pre>";
    $count = $wpdb->get_var($count_query);
    $max = $wpdb->get_var($max_query);
    $tag_cache[$tk_md5] = array( 'count' => ( $count === false ? 0 : $count ),
                                 'max'   => $max,
                                 'where' => $original_where,
                                 'join'  => $original_join,
                                 'last'  => time() );

    update_option("impr_tag_cache", $tag_cache);

    $tag_queue = get_option('impr_tag_queue');

    if(($qk = array_search($tk, $tag_queue))!==false) {
      unset($tag_queue[$qk]);
      update_option('impr_tag_queue', $tag_queue);
    }

    //echo "<h2>set_tag_cache 2: impr_tag_cache</h2><pre>";
    //print_r($tag_cache);
    //echo "</pre>";
    
    return $tag_cache;
  }

  public static function queue_tag_cache($tag_key) {
    global $wpdb;

    $tag_queue = get_option('impr_tag_queue');
    $tag_cache = get_option("impr_tag_cache");

    if(!is_array($tag_queue))
      $tag_queue = array();

    if(!is_array($tag_cache))
      $tag_cache = array();

    // We just return zero values when we queue
    $tk_array = explode('||', $tag_key);
    $tk_md5 = md5($tag_key);

    if(!in_array($tag_key, $tag_queue)) {
      $tag_queue[] = $tag_key;
      update_option('impr_tag_queue',$tag_queue);
    }

    if(!isset($tag_cache[$tk_md5])) {
      $join = $tk_array[0];
      $where = $tk_array[1];
      $max = $wpdb->get_var("SELECT max(comment_ID) FROM {$wpdb->comments}");
      $tag_cache[$tk_md5] = array( 'count' => 500,
                                   'max'   => $max,
                                   'where' => $where,
                                   'join'  => $join,
                                   'last'  => time() );
    }

    return $tag_cache;
  }

  public static function process_tag_cache_queue() {
    $url = home_url('index.php');
    $tag_queue = get_option('impr_tag_queue');

    if(!is_array($tag_queue) or empty($tag_queue))
      return;

    $tk = $tag_queue[array_rand($tag_queue)];

    ImprComment::set_tag_cache($tk);
  }

  public static function get_tag_cache($tag_key, $force=false) {
    $tag_cache = get_option("impr_tag_cache");

    //echo "<h2>get_tag_cache: impr_tag_cache</h2><pre>";
    //print_r($tag_cache);
    //echo "</pre>";

    $tag_key_md5 = md5($tag_key);
    if( $tag_cache==false or !isset($tag_cache[$tag_key_md5]) or
        $tag_cache[$tag_key_md5]['last'] < (time()-(60*60*24)) or
        !is_numeric($tag_cache[$tag_key_md5]['count']) or
        !$tag_cache[$tag_key_md5]['count'] )
      $tag_cache = self::queue_tag_cache($tag_key);

    return $tag_cache[$tag_key_md5];
  }
}
