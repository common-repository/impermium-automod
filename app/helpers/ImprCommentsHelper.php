<?php
if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}
class ImprCommentsHelper {
  public static function abuse_filter_select( $field_value='', $id_name=null ) {
	  $options = array_merge( array( 'all' => __('All forms of abuse') ), ImprComment::supported_filters( true ) );
    
    ImprAppHelper::select_statement( 'impr_tag', $field_value, $options, null, __('Filter by abuse...') );
    ?>
    <a href="#" class="impr_filter_tag button" data-location="<?php echo preg_replace( "#[\&\?]impr_tag=[^\&]*#", "", $_SERVER['REQUEST_URI']); ?>"><?php _e('Filter'); ?></a>
    <?php
  }
  
  public static function info_tooltip( $tag, $title, $info ) {
    ?>
    <img class="impr-tooltip" src="<?php echo IMPR_IMAGES_URL; ?>/info.png" width="10px" height="10px" data-title="<?php echo $title; ?>" data-info="<?php echo $info; ?>" data-tag="<?php echo $tag; ?>" />
    <?php
  }
}
