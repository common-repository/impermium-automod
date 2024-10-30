<p><?php printf(__('Don\'t have an %1$s API key yet?  %2$sRequest one now%3$s'), IMPR_DISPLAY_NAME, "<a href=\"#\" class=\"impr_show_add_request_form\">",'</a>'); ?></p>
<div class="impr_add_request_form">
  <h3><?php _e('Request an API Key'); ?></h3>
  <span class="description"><?php _e('To contact a sales person to request an API key, please fill out this form.'); ?></span>
  <form id="contact" action="https://www.salesforce.com/servlet/servlet.WebToLead?encoding=UTF-8" method="POST">
    <input type=hidden name="oid" value="00DU0000000H08Q">
    <input type=hidden name="retURL" value="<?php echo $success_url; ?>">

    <fieldset>
      <input name="send" type="hidden" value="1" />
      <div class="formrow">
        <label for="first_name"><?php _e("First Name:"); ?></label>
        <input name="first_name" id="first_name" type="text" class="text required" value="<?php echo $first_name; ?>" />
      </div>
      <div class="formrow">
        <label for="last_name"><?php _e("Last Name:"); ?></label>
        <input name="last_name" id="last_name" type="text" class="text required" value="<?php echo $last_name; ?>" />
      </div>
      <div class="formrow">
        <label for="Title"><?php _e("Title:"); ?></label>
        <input name="title" id="Title" type="text" class="text" value="<?php echo $title; ?>" />
      </div>
      <div class="formrow">
        <label for="Company"><?php _e("Company:"); ?></label>
        <input name="company" id="Company" type="text" class="text" value="<?php echo $company; ?>" />
      </div>
      <div class="formrow">
        <label for="Email"><?php _e("Email:"); ?></label>
        <input name="email" id="Email" type="text" class="text required email" value="<?php echo $email; ?>" />
      </div>
      <div class="formrow">
        <label for="phone"><?php _e("Phone:"); ?></label>
        <input name="phone" id="phone" type="text" class="text" value="<?php echo $phone; ?>" />
      </div>
      <div class="formrow">
        <label for="Website"><?php _e("Web Site:"); ?></label>
        <input name="URL" id="Website" type="text" class="text required" value="<?php echo $website; ?>" />
      </div>
      <input type="hidden" id="lead_source" name="lead_source" value="Web">
      <div class="submitrow">
        <input type="submit" name="Submit" id="Submit" class="button" value="<?php _e('Request API Key'); ?>" /> <?php printf(__('or %1$sCancel%2$s'), "<a href=\"#\" class=\"impr_show_add_request_form\">", "</a>"); ?>
      </div>
    </fieldset>
  </form>
</div>