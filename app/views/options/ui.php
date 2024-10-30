<?php
if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}
?>
<div class="wrap">
  <div id="icon-options-general" class="icon32"><br/></div>
  <h2><?php printf( __('%s Settings'), IMPR_DISPLAY_NAME ); ?></h2>
  <?php require IMPR_VIEWS_PATH . '/shared/errors.php'; ?>
  <br/>

  <form action="<?php echo admin_url('options-general.php?page=impr-options'); ?>" method="post">
    <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('ImprOptionsController::save'); ?>" />
    <input type="hidden" name="action" value="save" />
    
    <div class="impr_api_key" data-key-active="<?php echo ((empty($impr_options->api_key) or !empty($errors)) ? 'false' : 'true'); ?>">
	    <div class="impr-active"><?php printf( __('%1$s is active using API Key <b>%2$s</b>'), IMPR_DISPLAY_NAME, $impr_options->api_key ); ?> (<a href="#" class="impr_edit_api_key"><?php _e('Edit'); ?></a>)</div>
	  
	  </div>
    <div class="impr_enter_api_key">
      <div><?php printf(__('Enter the %s API Key provided to you by your account representative. Don\'t have your API key? Contact your account representative at %s'), IMPR_DISPLAY_NAME, '<a href="mailto:info@impermium.com">info@impermium.com</a>'); ?></div><br/>
	    <label for="<?php echo $impr_options->api_key_str; ?>"><?php _e('API Key:'); ?></label>
      <input type="text" name="<?php echo $impr_options->api_key_str; ?>" id="<?php echo $impr_options->api_key_str; ?>" value="<?php echo $impr_options->api_key; ?>" />
      <?php if(!empty($impr_options->api_key)): ?>
        <span>(<a href="#" class="impr_edit_api_key"><?php _e('Cancel'); ?></a>)</span>
      <?php endif; ?>
      <br/>
      <br/>
    </div>
    <table id="impr_mod_config" cellspacing="0">
      <thead>
        <tr>
          <th class="impr_mod_title">&nbsp;</th>
          <th><?php _e('Allow'); ?></th>
          <th><?php _e('Moderate'); ?></th>
          <th><?php _e('Block'); ?></th>
        </tr>
      </thead>
      <tbody>
      <?php $tags = ImprComment::supported_tags(true); ?>
      <?php $tag_count = 0; ?>
      <?php foreach( $tags as $impr_tag_val => $args ): ?>
        <?php $impr_tag_title = $args['label'];
              $impr_tag_info = $args['info'];
              if(++$tag_count == count($tags)): ?>
          <tr class='impr_error_row'>
        <?php else: ?>
          <tr>
        <?php endif; ?>
          <td class="impr_mod_title"><?php echo $impr_tag_title; ?> <?php ImprCommentsHelper::info_tooltip( $impr_tag_val, $impr_tag_title, $impr_tag_info ) ?></td>
          <td><input type="radio" name="<?php echo $impr_options->tags_str; ?>[<?php echo $impr_tag_val; ?>]" id="<?php echo $impr_options->tags_str; ?>_<?php echo $impr_tag_val; ?>" class="impr_tag_config allow <?php echo $impr_tag_val; ?>" <?php checked( $impr_options->tags[$impr_tag_val], 'allow' ); ?> value='allow' /></td>
          <td><input type="radio" name="<?php echo $impr_options->tags_str; ?>[<?php echo $impr_tag_val; ?>]" id="<?php echo $impr_options->tags_str; ?>_<?php echo $impr_tag_val; ?>" class="impr_tag_config moderate <?php echo $impr_tag_val; ?>" <?php checked( $impr_options->tags[$impr_tag_val], 'moderate' ); ?> value='moderate' /></td>
          <td><input type="radio" name="<?php echo $impr_options->tags_str; ?>[<?php echo $impr_tag_val; ?>]" id="<?php echo $impr_options->tags_str; ?>_<?php echo $impr_tag_val; ?>" class="impr_tag_config block <?php echo $impr_tag_val; ?>" <?php checked( $impr_options->tags[$impr_tag_val], 'block' ); ?> value='block' /></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <br/>
    <input type="submit" class="button-primary impr-button" value="<?php _e('Save', 'wp-impermium'); ?>" />
  </form>
  <?php do_action('impr_sync_ui'); ?>
</div>
