(function ($) {
  $(document).ready(function(e) {
	  $('.impr_show_add_request_form').click( function(e) {
	    e.preventDefault();
	    $('.impr_add_request_form').slideToggle();
	    $('.impr_enter_api_key').slideToggle();
	  });
	  
	  var api_key_active;
	  api_key_active = $('.impr_api_key').attr('data-key-active');
	  
	  if(api_key_active=='true') {
      $('.impr_api_key').show();
      $('.impr_enter_api_key').hide();
      $('#impr_mod_config').show();
	  }
	  else {
      $('.impr_api_key').hide();
      $('.impr_enter_api_key').show();
      $('#impr_mod_config').hide();
	  }
	  
	  $('.impr_edit_api_key').click( function(e) {
	    e.preventDefault();
	    $('.impr_api_key').toggle();
	    $('.impr_enter_api_key').toggle();
	    $('#impr_mod_config').toggle();
	  });
	  
	  $("#contact").validate({
      errorClass: "impr-error",
      submitHandler: function(form) {
        form.submit();
      }
    });
  
    $('.impr-tooltip').mouseover( function() {
      $(this).pointer( { "content":  "<h3>" + $(this).attr('data-title') + "</h3><p>" + $(this).attr('data-info') + "</p>",
                         "position": {"edge":"left","align":"center"},
                         "buttons": function() {
                           // intentionally left blank to eliminate "dismiss" button
                         }
                       }
      ).pointer("open");
    });
    
    $('.impr-tooltip').mouseout( function() {
      $(this).pointer("close");
    });
    
  });
})(jQuery);