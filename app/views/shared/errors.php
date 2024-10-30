<?php if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');} ?>
<?php if( isset($message) ): ?>
  <div class="updated fade below-h2" style="padding: 10px;"><strong><?php echo esc_html($message); ?></strong></div>
<?php endif; ?>
<?php
  if( isset($errors) and count($errors) > 0 ):
?>
<div class="error">
  <ul>
  <?php
    foreach( $errors as $error ):
      ?>
      <li><strong><?php _e('ERROR', 'wp-impermium'); ?></strong>: <?php echo $error; ?></li>
      <?php
    endforeach;
  ?>
  </ul>
</div>
<?php
  endif;