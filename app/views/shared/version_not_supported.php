<div class="error" style="padding-top: 5px; padding-bottom: 5px;">
<?php printf(__('<b>Warning:</b> Version %1$s of %2$s is not supported. Please %3$sUpdate %4$s%5$s.'), IMPR_VERSION, IMPR_DISPLAY_NAME, '<a href="' . wp_nonce_url('update.php?action=upgrade-plugin&plugin=' . IMPR_PLUGIN_SLUG, 'upgrade-plugin_' . IMPR_PLUGIN_SLUG) .'">', IMPR_DISPLAY_NAME, '</a>'); ?>
</div>
