<?php
if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}
class ImprAppHelper {
  public static function select_statement($field_name, $field_value='', $options=array(), $id_name=null, $blank_option=false) {
	  $id_name = is_null($id_name) ? $field_name : $id_name;
    ?>
      <select name="<?php echo $field_name; ?>" id="<?php echo $id_name; ?>">
      <?php if($blank_option): ?>
        <option value=""><?php echo $blank_option; ?></option>
      <?php endif; ?>
	  <?php
	  foreach($options as $value => $label) {
	    ?>
	      <option value="<?php echo esc_attr( $value ); ?>"<?php echo selected($field_value, $value); ?>><?php echo $label; ?>&nbsp;</option>
	    <?php
	  }
	  ?>
	    </select>
    <?php
  }
}