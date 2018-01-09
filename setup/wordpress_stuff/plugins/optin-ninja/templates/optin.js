/**
 * OptIn Ninja
 * (c) Web factory Ltd, 2016
 */

jQuery(window).load(function(){
  if(jQuery('.first-optin').is(':visible')) {
    center_box(1);
  } else {
    center_box(2);
  }

  jQuery(window).resize(function(){
    if(jQuery('.first-optin').is(':visible')) {
      center_box(1);
    } else {
      center_box(2);
    }
  });
});

jQuery(document).ready(function($){
  if (window.parent.document.getElementById('wf-optin-iframe')) {
    jQuery('.wf-optin-container').css('margin', 0);
    jQuery('.wf-optin-box').css('width', '99%');
    jQuery('body').css('background', 'transparent');
  }

  

  $(".wf-optin-box-inner").fitVids();

  $('.second-optin #submit').click(function(){
    $('.ajax-subscribe').trigger('submit');

    return false;
  });

  $(".ajax-subscribe #name, .ajax-subscribe #email").keypress(function(event) {
    if (event.which == 13) {
        event.preventDefault();
        $('.ajax-subscribe').trigger('submit');
    }
  });

  $('.next-optin').click(function(){
    $('.first-optin').fadeOut(500, function(){
      center_box(2);
      $('.second-optin').fadeIn(500, function() {
				
        $.post(optin_vars.ajaxurl, {action: 'optin_step2_stats', post:optin_vars.postID},
        function (response) {
          if (parseInt(optin_vars.ga_events) && parseInt(optin_vars.ga_track)) {
            if (typeof _gaq != 'undefined') {
              _gaq.push(['_trackEvent', 'OptIn Pages', 'Show Box #2', optin_vars.post_title]);
            }
            if (typeof ga != 'undefined') {
              ga('send', 'event', 'OptIn Pages', 'Show Box #2', optin_vars.post_title);
            }
          }
        }).fail(function() {
             var wp_opt_current_domain=window.location.href.split('/'); 
			 var wp_opt_current_ajaxurl=optin_vars.ajaxurl.split('/'); 
			 
			 if(wp_opt_current_ajaxurl[2] != wp_opt_current_domain[2]){
				 wp_opt_current_ajaxurl[2]=wp_opt_current_domain[2];
				 optin_vars.ajaxurl=wp_opt_current_ajaxurl.join('/');
				 $.post(optin_vars.ajaxurl, {action: 'optin_step2_stats', post:optin_vars.postID},
				 function (response) {
				  if (parseInt(optin_vars.ga_events) && parseInt(optin_vars.ga_track)) {
					if (typeof _gaq != 'undefined') {
					  _gaq.push(['_trackEvent', 'OptIn Pages', 'Show Box #2', optin_vars.post_title]);
					}
					if (typeof ga != 'undefined') {
					  ga('send', 'event', 'OptIn Pages', 'Show Box #2', optin_vars.post_title);
					}
				  }
				}).fail(function(){
					end_submit();
					alert('An undocumented error has occured. Please reload the page and try again.');
				});;
		     }
        });
      });
    });
    return false;
  });
});

function exit_optin() {
    subscribed = true;

    if (optin_vars.after_subscribe_action == 'stay-alert' && optin_vars.after_subscribe_message) {
      alert(optin_vars.after_subscribe_message);
    }

    if (optin_vars.after_subscribe_action == 'redirect' && optin_vars.after_subscribe_url) {
      window.top.location.href = optin_vars.after_subscribe_url;
    }

    if (parseInt(optin_vars.ga_events) && parseInt(optin_vars.ga_track)) {
      if (typeof _gaq != 'undefined') {
        _gaq.push(['_trackEvent', 'OptIn Pages', 'Subscribed', optin_vars.post_title]);
      }
      if (typeof ga != 'undefined') {
        ga('send', 'event', 'OptIn Pages', 'Subscribed', optin_vars.post_title);
      }
    }
}

// Validate Form Fields
function validate_fields() {

  error = false;
  emailfilter = /^\w+[\+\.\w-]*@([\w-]+\.)*\w+[\w-]*\.([a-z]{2,4}|\d+)$/i

  jQuery('textarea, input[type=text], input[type=checkbox], select', '.ajax-subscribe').removeClass('error');

  jQuery('textarea, input[type=text], input[type=checkbox], select', '.ajax-subscribe').each(function(ind, el) {
    if (jQuery(el).data('required')) {
      if (jQuery(el).attr('type') == 'checkbox') {
        val = jQuery(el).is(':checked');
      } else {
        val = jQuery(el).val();
      }

      if(!val) {
        error = true;
        jQuery(el).addClass('error');
      }

      if(jQuery(el).attr('name') == 'email' && (val.length < 6 || emailfilter.test(val) == false)) {
        error = true;
        jQuery(el).addClass('error');
      }
    }
  });

  if (error) {
    return false;
  } else {
    return true;
  }
} // validate_fields

function validate_fields_custom() {
  error = false;
  emailfilter = /^\w+[\+\.\w-]*@([\w-]+\.)*\w+[\w-]*\.([a-z]{2,4}|\d+)$/i

  jQuery('textarea, input[type=text], input[type=checkbox], select', '.ajax-subscribe').removeClass('error');

  jQuery('textarea, input[type=text], input[type=checkbox], select', '.ajax-subscribe').each(function(ind, el) {
    if (jQuery(el).data('required')) {
      if (jQuery(el).attr('type') == 'checkbox') {
        val = jQuery(el).is(':checked');
      } else {
        val = jQuery(el).val();
      }

      if(!val) {
        error = true;
        jQuery(el).addClass('error');
      }

      if(jQuery(el).attr('name') == 'email' && (val.length < 6 || emailfilter.test(val) == false)) {
        error = true;
        jQuery(el).addClass('error');
      }
    }
  });

  if (error) {
    return false;
  } else {
    return true;
  }
} // validate_fields

function begin_submit() {
  jQuery('#submit').html('Please wait ...');
  jQuery('#submit').addClass('loading').addClass('disabled');
  jQuery('#submit').attr('disabled', 'disabled');
}

function end_submit() {
  jQuery('#submit').html(jQuery('#submit').attr('data-default-value'));
  jQuery('#submit').removeClass('loading').removeClass('disabled');
  jQuery('#submit').removeAttr('disabled');
}

function center_box(box) {
  window_height = jQuery(window).height();
  center = window_height/2;
  if (box == 1) {
    box_height = jQuery('.first-optin').outerHeight();
  } else {
    box_height = jQuery('.second-optin').outerHeight();
  }

  if ((box_height + 40) > window_height) {
    jQuery('.wf-optin-container').css('top', 0);
  } else {
    jQuery('.wf-optin-container').css('top', center - (box_height / 2));
  }

  if (window.parent.document.getElementById('wf-optin-iframe')) {
    window.parent.document.getElementById('wf-optin-iframe').style.height = (box_height + 30) + 'px';
    window.parent.wf_center_optin_dialog();
  }
} // center_box