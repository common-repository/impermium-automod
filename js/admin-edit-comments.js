(function ($) {
  $(document).ready(function(e) {
    $('select[name=action] option[value=spam]').text('Mark as Blocked');
    $('select[name=action] option[value=unspam]').text('Unblock');
    $('input[name=delete_all]').val('Delete All');
    
    $('.impr_filter_tag').click( function(e) {
      e.preventDefault();
      
      var tag = $('#impr_tag').val();
      var loc = $(this).attr('data-location');
      
      if( tag != '' ) {
        var delim = '?';
        if(loc.match(/\?/))
          delim = '&';
        
        loc = loc + delim + 'impr_tag=' + tag;
      }
      else {
        loc = loc;
      }
      
      window.location.href = loc;
    });
    
    $('.spam-undo-inside').replaceWith('<div class="spam-undo-inside">Comment by <strong></strong> marked as blocked. <span class="undo unspam"><a href="#">Undo</a></span></div>');
    
    // No way to move the delete button except to man-handle it with javascript
    // TODO: Bring this back into play when we bring back abuse filtering
    //var move_it = $('input[name=delete_all]').remove();
    //$('a.impr_filter_tag.button').parent().append(move_it[0]);
    
    var message = $('#moderated.updated p').html();
    
    if( message != null && message != undefined && message != '' ) {
      message = message.replace(/spam/, 'blocked').replace(/Spam/, 'Blocked').replace(/SPAM/, 'BLOCKED');
      $('#moderated.updated p').html(message);
    }
  });
})(jQuery);