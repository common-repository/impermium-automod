<?php
if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}
class ImprCommentsController {
  public static function load_hooks() {
    add_filter( 'pre_comment_approved', 'ImprCommentsController::check_comment', 10, 2 );
    add_action( 'comment_post', 'ImprCommentsController::tag_comment', 10, 2 );
    //add_action( 'manage_comments_nav', 'ImprCommentsController::abuse_filter' );
    add_filter( 'comment_status_links', 'ImprCommentsController::comment_status_links', 10, 1 );
    add_filter( 'manage_edit-comments_columns', 'ImprCommentsController::add_comment_column', 10, 1 );
    add_action( 'manage_comments_custom_column', 'ImprCommentsController::manage_comment_column', 10, 2 );
    //add_filter( 'manage_edit-comments_sortable_columns', 'ImprCommentsController::register_sortable', 10, 1 );
    add_filter( 'comment_row_actions', 'ImprCommentsController::row_actions', 10, 2 );
    add_action( 'spam_comment', 'ImprCommentsController::block_comment', 10, 1 );
    add_action( 'unspam_comment', 'ImprCommentsController::unblock_comment', 10, 1 );
    add_filter( 'comments_clauses', 'ImprCommentsController::comment_clauses', 10, 2 ); // For comment counts

    //add_filter( 'cron_schedules', 'ImprCommentsController::add_cron_intervals' );
    
    /*
    add_action('impr_process_tag_cache', 'ImprCommentsController::process_tag_cache_queue');
    if( !wp_next_scheduled( 'impr_process_tag_cache' ) ) {
      wp_schedule_event( time(), '1minute', 'impr_process_tag_cache' );
    }
    */
  }

  /*
  public static function add_cron_intervals( $schedules ) {
    $schedules['1minute'] = array(
       'interval' => 60,
       'display' => __('Every 1 Minute')
    );
    return $schedules;
  }
  */

  public static function check_comment( $approved, $comment_data ) {
    return ImprComment::approve( $approved, $comment_data );
  }
  
  public static function tag_comment( $comment_id, $approved ) {
    if( isset( $_REQUEST['impr_res']['4.0']['tags'] ) and
        is_array( $_REQUEST['impr_res']['4.0']['tags'] ) and
        !empty( $_REQUEST['impr_res']['4.0']['tags'] ) ) {

      foreach( $_REQUEST['impr_res']['4.0']['tags'] as $tag ) {
        add_comment_meta( $comment_id, "impr_tag_{$tag}", 1 );
      }

      add_comment_meta( $comment_id, 'impr_tag_str',
                        implode( ',', ImprComment::statically_order_tags( $_REQUEST['impr_res']['4.0']['tags'] ) ) );
    }
  }

  public static function abuse_filter() {
    $post_val = isset($_REQUEST['impr_tag']) ? $_REQUEST['impr_tag'] : '';
    ImprCommentsHelper::abuse_filter_select( $post_val );
  }

  public static function comment_status_links($status_links) {
    $status_links['spam'] = preg_replace('#\>\W*Spam\W*\<#', '>' . __('Blocked') . ' <', $status_links['spam']);
    return $status_links;
  }

  public static function add_comment_column( $columns ) {
    $columns['impr_tags'] = __('Status');
    return $columns;
  }

  public static function manage_comment_column( $column_name, $comment_id ) {
    $impr_options = ImprOptions::fetch();
    
    if($column_name == 'impr_tags') {
      $metas = get_comment_meta( $comment_id, 'impr_tag_str', true );
      if( $metas != false )
        echo "<span style=\"color: red;\">{$metas}</span>";
      else
        echo '';
    }
  }
  
  // Register the column as sortable
  public static function register_sortable( $columns ) {
    $columns['impr_tags'] = 'impr_tags';
    return $columns;
  }
  
  public static function row_actions($actions, $comment) {
    if( isset($actions['spam'] ) ) {
      $actions['spam'] = preg_replace('#Spam#', __('Block'), $actions['spam']);
      $actions['spam'] = preg_replace('#Mark this comment as spam#', __('Mark this comment as blocked'), $actions['spam']);
    }
    
    if( isset($actions['unspam'] ) )
      $actions['unspam'] = preg_replace('#Not Spam#', __('Unblock'), $actions['unspam']);
    
    return $actions;	
  }

  public static function comment_clauses( $clauses, $query ) {
    if( is_admin() and
        ( isset( $_REQUEST['impr_tag'] ) or
        ( isset( $_REQUEST['orderby'] ) and
	$_REQUEST['orderby']=='impr_tags' ) ) ) {
      $tag = isset($_REQUEST['impr_tag']) ? $_REQUEST['impr_tag'] : '';
	    
      $clauses = ImprComment::filter_comment_clauses( $clauses, $query, $tag );
    }

    return $clauses;
  }

  public static function block_comment($comment_id) {
    $comment = get_comment($comment_id);
    ImprComment::report_blocked($comment);
  }

  public static function unblock_comment($comment_id) {
    $comment = get_comment($comment_id);
    ImprComment::report_blocked($comment, 'allow'); // Un-block the comment with impermium
  }

  public static function process_tag_cache_queue() {
    ImprComment::process_tag_cache_queue();
  }
}
