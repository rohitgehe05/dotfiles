/**
 * OptIn Ninja
 * (c) Web factory Ltd, 2016
 */


jQuery(document).ready(function($){
  $('.colorpicker').wpColorPicker();
  
  $('.wf_opt_settings_button').on('click', function(e){
	 $('.wf_opt_settings_tab'+jQuery(this).data('box')).hide();
	 $('#wf_opt_settings_tab'+jQuery(this).data('box')+'_'+jQuery(this).data('tab')).show();
	 jQuery('.wf_opt_settings_button'+jQuery(this).data('box')).removeClass('active');
	 jQuery(this).addClass('active');
  });
  
  $('.optin_template_preview_box_wrapper').on('click', function(e) {
     if (e.target == e.currentTarget) {
       $('.optin_template_preview_box_cancel').trigger('click');
       e.preventDefault();
       return;
     }
  });
  
  $('#wf_opt_copy_settings').on('click', function(e){
  	e.preventDefault();
	jQuery('#second-optin_content-font').val(jQuery('#first-optin_content-font').val());
	jQuery('#second-optin_content-font-size').val(jQuery('#first-optin_content-font-size').val());
	jQuery('#second-optin_box-font-color').iris('color', jQuery('#first-optin_box-font-color').val());
	
	jQuery('#second-optin_button-text').val(jQuery('#first-optin_button-text').val());
	jQuery('#second-optin_button-font').val(jQuery('#first-optin_button-font').val());
	jQuery('#second-optin_button-font-size').val(jQuery('#first-optin_button-font-size').val());
	jQuery('#second-optin_button-border-radius').val(jQuery('#first-optin_button-border-radius').val());
	jQuery('#second-optin_button-background-color').iris('color', jQuery('#first-optin_button-background-color').val());
	jQuery('#second-optin_button-text-color').iris('color', jQuery('#first-optin_button-text-color').val());
	jQuery('#second-optin_footer-text').val(jQuery('#first-optin_footer-text').val());
	
	jQuery('#second-optin_box-background-color').iris('color', jQuery('#first-optin_box-background-color').val());
	jQuery('#second-optin_box-shadow-color').iris('color', jQuery('#first-optin_box-shadow-color').val());	
	jQuery('#second-optin_bg-opacity').val(jQuery('#first-optin_bg-opacity').val());
	jQuery('#second-optin_background-position').val(jQuery('#first-optin_background-position').val());
	jQuery('#second-optin_background-cover').val(jQuery('#first-optin_background-cover').val());
	jQuery('#second-optin_background-repeat').val(jQuery('#first-optin_background-repeat').val());
	jQuery('#second-optin_background-image').val(jQuery('#first-optin_background-image').val());
	
	jQuery('#second-optin_box-border-radius').val(jQuery('#first-optin_box-border-radius').val());
	jQuery('#second-optin_box-border-width').val(jQuery('#first-optin_box-border-width').val());
	jQuery('#second-optin_box-border-color').iris('color', jQuery('#first-optin_box-border-color').val());
	
	$('html, body').animate({
        scrollTop: $("#optin-ninja-second-box").offset().top
    }, 500);
	
  });
  
  $('#wf_opt_copy_settings2').on('click', function(e){
  	e.preventDefault();
	jQuery('#first-optin_content-font').val(jQuery('#second-optin_content-font').val());
	jQuery('#first-optin_content-font-size').val(jQuery('#second-optin_content-font-size').val());
	jQuery('#first-optin_box-font-color').iris('color', jQuery('#second-optin_box-font-color').val());
	
	jQuery('#first-optin_button-text').val(jQuery('#second-optin_button-text').val());
	jQuery('#first-optin_button-font').val(jQuery('#second-optin_button-font').val());
	jQuery('#first-optin_button-font-size').val(jQuery('#second-optin_button-font-size').val());
	jQuery('#first-optin_button-border-radius').val(jQuery('#second-optin_button-border-radius').val());
	jQuery('#first-optin_button-background-color').iris('color', jQuery('#second-optin_button-background-color').val());
	jQuery('#first-optin_button-text-color').iris('color', jQuery('#second-optin_button-text-color').val());
	jQuery('#first-optin_footer-text').val(jQuery('#second-optin_footer-text').val());
	
	jQuery('#first-optin_box-background-color').iris('color', jQuery('#second-optin_box-background-color').val());
	jQuery('#first-optin_box-shadow-color').iris('color', jQuery('#second-optin_box-shadow-color').val());	
	jQuery('#first-optin_bg-opacity').val(jQuery('#second-optin_bg-opacity').val());
	jQuery('#first-optin_background-position').val(jQuery('#second-optin_background-position').val());
	jQuery('#first-optin_background-cover').val(jQuery('#second-optin_background-cover').val());
	jQuery('#first-optin_background-repeat').val(jQuery('#second-optin_background-repeat').val());
	jQuery('#first-optin_background-image').val(jQuery('#second-optin_background-image').val());
	
	jQuery('#first-optin_box-border-radius').val(jQuery('#second-optin_box-border-radius').val());
	jQuery('#first-optin_box-border-width').val(jQuery('#second-optin_box-border-width').val());
	jQuery('#first-optin_box-border-color').iris('color', jQuery('#second-optin_box-border-color').val());
	
	$('html, body').animate({
        scrollTop: $("#optin-ninja-first-box").offset().top
    }, 500);	
  });
  
  
  /* Templates */
  
  $('#wf_opt_verify_licence').on('click', function(e) {
	  
	  e.preventDefault();
      button = this;
      $(button).addClass('loading');
	  
	  $.ajax({type:'POST',
           url: ajaxurl,
           data: {
             action: 'wf_opt_check_licence',
             product_key: $('#wf_opt_product_key').val()
           },
           dataType: 'json',
           }).done(function(response){
             if (response.success) {
			   alert(response.data);
         $('#optin_templates_wrapper').show(); 
         $('#purchase_key_wrapper').hide();
         wf_opt_refresh_templates();  
             } else {
               $('#wf_opt_product_key').focus().select();
               alert(response.data);
             }
           }).fail(function(response) {
             alert('An undocumented error has occured. Please reload the page and try again.');
           }).always(function(response) {
             $('#wf_opt_verify_licence').removeClass('loading');
           });
	  
  });
  
  $('#wf_opt_download_templates').on('click', function(e) {
  	e.preventDefault();
  	wf_opt_refresh_templates();  
  });
  
  var templates_total=0;
  var current_template=0;
  
  function wf_opt_template_download_progress(){
	  var button = $('#wf_opt_download_templates');
      
	  $.ajax({type:'POST',
           url: ajaxurl,
		   data: {
             action: 'wf_opt_download_template_files',
			 template_index: current_template
           },
           dataType: 'json',
           }).done(function(response){  
		   	 
			 if(response.data<templates_total){
				 current_template++;
				 $(button).val('Downloading '+current_template+'/'+templates_total); 
				 wf_opt_template_download_progress();				 
			 } else {
				$(button).val('Download Finished'); 
				$(button).removeClass('loading');
				location.reload(); 
			 }
           }).fail(function(response) {
			  $(button).val('Refresh Templates');  
			  $(button).removeClass('loading');
			  alert('An undocumented error has occured. Please reload the page and try again.');
           }); 		   
  }
	  
	  
  function wf_opt_refresh_templates(){
	  //template_counter=setInterval(check_template_download_progress,1000);
  
      button = $('#wf_opt_download_templates');
      $(button).addClass('loading');
	  
	  $.ajax({type:'POST',
           url: ajaxurl,
		   data: {
             action: 'wf_opt_download_templates'
           },
           dataType: 'json',
           }).done(function(response){
             if (response.success) {
			   templates_total = response.data;
			   $('#wf_opt_download_templates').val('Downloading 1/'+templates_total);
			   wf_opt_template_download_progress();
             } else {
               alert(response.data);
               $(button).removeClass('loading');
             }
           }).fail(function(response) {
			 alert('An undocumented error has occured. Please reload the page and try again.');
       $(button).removeClass('loading');
           }).always(function(response) {
             
           }); 
	  
  }
  
  var preview_template='';
  $('.optin_template_box').on('click', function() {
	$('.optin_template_box').removeClass('selected');  
	$(this).addClass('selected');  
	
	preview_template=$(this).data('optin_template');
	var preview_html = '';
  preview_html+= '<a class="optin_template_preview_box_cancel"><i class="dashicons dashicons-no"></i></a>';
  preview_html+= '<div class="optin_template_preview_box_thumbnail"><img src="'+$(this).data('optin_template-thumb')+'" /></div>';
	preview_html+= '<div class="optin_template_preview_box_title">'+$(this).data('optin_template-name')+'</div>';
	preview_html+= '<div class="optin_template_preview_box_description">'+$(this).data('optin_template-description')+'</div>';
	preview_html+= '<div class="button-holder">';
  if($(this).data('optin_template-url')) { 
		preview_html+='<a href="'+$(this).data('optin_template-url')+'" target="_blank" class="optin_template_preview_box_preview button button-secondary">Preview</a>';
	}
  preview_html+='<a class="optin_template_preview_box_apply button button-primary">Apply</a>';
  preview_html+='</div>';
	preview_html+='<div class="optin_template_preview_box_tip">Applying the template will only update box styles. It won\'t change the content.</div>';
	//preview_html+='<div class="optin_template_preview_box_cancel button"><i data-fip-value="icon-cancel" class="icon-cancel"></i>Cancel</div>';
	
	$('.optin_template_preview_box').html(preview_html);  
    $('.optin_template_preview_box_wrapper').show();  
    
  });
  
  $('.optin_template_preview_box').on('click', '.optin_template_preview_box_cancel', function(){
	  $('.optin_template_preview_box_wrapper').hide(); 
	  $('.optin_template_preview_box').html(''); 
	  preview_template='';
  });
  
  $('.optin_template_preview_box').on('click', '.optin_template_preview_box_apply', function(){
	  $('.optin_template_preview_box_wrapper').hide(); 
	  $('.optin_template_preview_box').html(''); 
	  $('#optin_template').val(preview_template);
	  $('#post').submit();
  });
  
  
  /* END Templates */
  
  
  // auto expand debug textarea
  $('#optin-form_error-log').on('focus', function() {
    $(this).height($(this).prop('scrollHeight') + 'px');
  });
  $('#optin-form_error-log').on('focusout', function() {
    $(this).height('60px');
  });

  $('a.preview-font').on('click', function(e) {
    e.preventDefault();

    url = $(this).attr('href');
    font = $(this).parents('.col-2').next('.col-2').find('select').val();
    win = window.open(url + font + '#___plusone_0', '_blank');
    win.focus();

    return false;
  });

  $('#customurl-parse-html').on('click', function(e) {
    e.preventDefault();
    html = $.parseHTML('<div id="parse-tmp">' + $('#optin-form_custom-url-html').val() + '</div>');

    $('#optin-form_custom-url').val($('form', html).attr('action'));

    params = '';
    $('input[type=hidden]', html).each(function(ind, el) {
      params += $(el).attr('name') + '=' + $(el).attr('value') + '&';
    });

    $('#optin-form_custom-url-extra').val(params);

    inputs = '';
    $('input[type=text]', html).each(function(ind, el) {
      name = $(el).attr('name');
      if (name == 'email' || name == 'from') {
        $('#optin-form_custom-email-field').val(name);
        inputs = '';
        return false;
      } else {
        inputs += ' ' + $(el).attr('name') + "\n";
      }
    });
    if (inputs) {
      alert('Important! Your custom form does NOT contain the "email" input field. The field in your form has a different name. Please check which field it is and enter the value in the "Form Email Field". Here is the list of fields we found:\n' + inputs);
    } else {
      alert('Done parsing the form!');
    }
  });

  // Media Upload
  if ($('.set_custom_images').length > 0) {
    if ( typeof wp !== 'undefined' && wp.media && wp.media.editor) {
      $('.wrap').on('click', '.set_custom_images', function(e) {
        e.preventDefault();
        var button = $(this);
        var id = button.prev();
        wp.media.editor.send.attachment = function(props, attachment) {
          var size = props.size;
          var att = attachment.sizes;
          var att2 = att[size];
          id.val(att2.url);
        };
        wp.media.editor.open(button);
      });
    }
  }

  // Add Background Field
  $('.add-new-bg').click(function(){
    var holder = $(this).prev('.bg-input-holder');
    var input = $('.bg-input:last-of-type', holder);
    var clone = $(input).clone();
    $('input', clone).val('');
    clone.appendTo(holder);
    return false;
  });

  // Remove Background
  $('.remove-bg').click(function(){
    $(this).parent().remove();
    return false;
  });

  // Remove Background
  $('.remove-bg2').click(function(){
    $(this).siblings('input').val('');
    return false;
  });

  // notifications
  $('#notifications_email_notifications').change(function(){
    if ($(this).val() == '1') {
      $('.notifications_email').show();
    } else {
      $('.notifications_email').hide();
    }
  });
  $('#notifications_email_notifications').trigger('change');

  $('#notifications_push_notifications').change(function(){
    if ($(this).val() == '1') {
      $('.notifications_push').show();
    } else {
      $('.notifications_push').hide();
    }
  });
  $('#notifications_push_notifications').trigger('change');

  // Mail Services
  $('#optin-form_mail-listing-service').change(function(){
    var selected = $(this).val();
    $('.aweber, .getresponse, .mail-chimp, .facebook, .customurl, .madmimi, .activecampaign, .campaignmonitor').hide();
    $('.' + selected).show();
  });
  $('#optin-form_mail-listing-service').trigger('change');

  // After Subscribe
  $('#optin-form_after-subscribe-action').change(function(){
    var selected = $(this).val();
    $('.redirect, .stay-alert').hide();
    $('.' + selected).show();
  });
  $('#optin-form_after-subscribe-action').trigger('change');

  // Prevent from Leaving
  $('#optin-form_prevent-from-leaving').change(function(){
    var selected = $(this).val();
    if (selected == 'yes') {
      $('.prevent-alert').show()
    } else {
      $('.prevent-alert').hide()
    }
  });
  $('#optin-form_prevent-from-leaving').trigger('change');

  // background type
  $('#optin-settings_background-type').change(function(){
    var selected = $(this).val();
    if (selected == 'image') {
      $('.bg-slider').hide();
      $('.bg-image').show();
      $('.bg-video').hide();
    } else if (selected == 'video') {
      $('.bg-slider').hide();
      $('.bg-image').hide();
      $('.bg-video').show();
    } else {
      $('.bg-slider').show();
      $('.bg-image').hide();
      $('.bg-video').hide();
    }
  });
  $('#optin-settings_background-type').trigger('change');

  // form fields
  $('#optin-form_form-fields').change(function(){
    selected = $(this).val();
    if (selected == 'email') {
      $('.placeholder-name-row').hide();
    } else {
      $('.placeholder-name-row').show();
    }
  });
  $('#optin-form_form-fields').trigger('change');

  $('.refresh-lists').on('click', function() {
    if(confirm('Page will reload. All unsaved changes will be lost. Continue?')) {
      return true;
    } else {
      return false;
    }
  });

  $('#clone-optin').on('click', function() {
    if(confirm('Page will not be saved before cloning. All unsaved changes will be lost. Continue?')) {
      return true;
    } else {
      return false;
    }
  });

  // auto popup
  $('#popup__optin_auto_popup').change(function(){
    selected = $(this).val();
    if (selected == '1') {
      $('#optin-ninja-conditional-options').show();
    } else {
      $('#optin-ninja-conditional-options').hide();
    }
  });
  $('#popup__optin_auto_popup').trigger('change');

// --------------------------------

  $('#wf-optin-ninja-options-page-tabs').tabs({
    active: optin_active_tab(),
    activate: function(event, ui) { $.cookie('wf_optin_options', $(this).tabs('option', 'active'), { expires: 30 });    },
    beforeActivate: function(event, ui) {
      var old_tab = ui.oldTab;
      var new_tab = ui.newTab;

      $(old_tab).removeClass('nav-tab-active');
      $(new_tab).addClass('nav-tab-active');
    },
    create: function(event, ui) {
      $('#wf-optin-ninja-options-page-tabs .ui-state-active').addClass('nav-tab-active');
    }
  });

  function optin_active_tab() {
    return parseInt(0 + $.cookie('wf_optin_options'), 10);
  }

  $('#optin-import-bg').on('click', function() {
    if (!confirm('Importing can take a while. Please be patient. Continue?')) {
      return;
    }
    $(this).attr('disabled', 'disabled').html('Please wait ...');
    $.post(ajaxurl, {action: 'optin_import_backgrounds'}, function(response) {
      $('#optin-import-bg').removeAttr('disabled').html('Import background images');
      alert('Import complete!');
    });

    return false;
  });

  $('#optin-import-textures').on('click', function() {
	if (!confirm('Importing can take a while. Please be patient. Continue?')) {
      return;
    }
    $(this).attr('disabled', 'disabled').html('Please wait ...');
    $.post(ajaxurl, {action: 'optin_import_textures'}, function(response) {
      $('#optin-import-textures').removeAttr('disabled').html('Import background textures');
      alert('Import complete!');
    });

    return false;
  });

  $('#optin-reset-stats').on('click', function() {
    if (confirm('Are you sure you want to reset all statistics? There is no undo!')) {
      $.post(ajaxurl, {action: 'optin_reset_stats'}, function(response) {
        alert('All statistics have been reset!');
      });
    }

    return false;
  });

  $('#optin-delete-subs').on('click', function() {
    if (confirm('Are you sure you want to delete all subscribers? There is no undo!')) {
      $.post(ajaxurl, {action: 'optin_delete_subs'}, function(response) {
        alert('All subscribers have been deleted!');
      });
    }

    return false;
  });

  $('.optin-del-ab').on('click', function() {
    if (confirm('Are you sure you want to delete the selected A/B Test?')) {
      return true;
    } else {
      return false;
    }
  });
  
  $('a.delete-subscriber').on('click', function(e) {
	  e.preventDefault();
	  
    if (confirm('Are you sure you want to delete the selected subscriber? There is no undo!')) {
      $.post(ajaxurl, {action: 'optin_delete_sub', 'sub_id': $(this).data('subscriber-id')}, function(response) { });
	  $(this).parents('tr').remove();
	  $('#datatables tbody tr').removeClass('even').removeClass('odd');
	  $('#datatables tbody tr:odd').addClass('odd');
	  $('#datatables tbody tr:even').addClass('even');
    }

    return false;
  });

  $('.optin-ab-shortcode').on('click', function(e) {
    e.preventDefault();

    if ($(this).attr('data-ab-disabled') == '0') {
      tmp = '<p>Any link can open the OptIn A/B Test in a popup if you add the <i>optin-popup</i> class to it and set the link (href parameter) to <i>' + $(this).attr('data-ab-href') + '</i>. Example:<br>&lt;a href="' + $(this).attr('data-ab-href') + '" class="optin-popup" data-optin-position="center"&gt;click here&lt;/a&gt;</p>';
      tmp += '<p>You can also use the shortcode:<br><i>[optin-test-popup position="center" id="' + $(this).attr('data-ab-id') + '" class="optional-class"]click here[/optin-test-popup]</i></p><p>Available values for the position parameter are: left top, center top, right top, left center, center center, right center, left bottom, center bottom, right bottom, left, center, right.</p>';
      $('#dialog-ab-shortcode').html(tmp);
    }

    $('#dialog-ab-shortcode').dialog({
      autoOpen: false,
      modal: true,
      title: 'A/B Test Popup Shortcode',
      dialogClass: '',
      resizable: false,
      draggable: false,
      closeOnEscape: true,
      width: 'auto',
      height: 'auto',
      open: function() {
        $('.ui-dialog :button').blur();
      },
      close: function() {
      }
    }).dialog('open');
  })
});

// -------------------------

/*!
 * jQuery Cookie Plugin v1.3.1
 * https://github.com/carhartl/jquery-cookie
 *
 * Copyright 2013 Klaus Hartl
 * Released under the MIT license
 */
(function (factory) {
  if (typeof define === 'function' && define.amd) {
    // AMD. Register as anonymous module.
    define(['jquery'], factory);
  } else {
    // Browser globals.
    factory(jQuery);
  }
}(function ($) {

  var pluses = /\+/g;

  function raw(s) {
    return s;
  }

  function decoded(s) {
    return decodeURIComponent(s.replace(pluses, ' '));
  }

  function converted(s) {
    if (s.indexOf('"') === 0) {
      // This is a quoted cookie as according to RFC2068, unescape
      s = s.slice(1, -1).replace(/\\"/g, '"').replace(/\\\\/g, '\\');
    }
    try {
      return config.json ? JSON.parse(s) : s;
    } catch(er) {}
  }

  var config = $.cookie = function (key, value, options) {

    // write
    if (value !== undefined) {
      options = $.extend({}, config.defaults, options);

      if (typeof options.expires === 'number') {
        var days = options.expires, t = options.expires = new Date();
        t.setDate(t.getDate() + days);
      }

      value = config.json ? JSON.stringify(value) : String(value);

      return (document.cookie = [
        config.raw ? key : encodeURIComponent(key),
        '=',
        config.raw ? value : encodeURIComponent(value),
        options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
        options.path    ? '; path=' + options.path : '',
        options.domain  ? '; domain=' + options.domain : '',
        options.secure  ? '; secure' : ''
      ].join(''));
    }

    // read
    var decode = config.raw ? raw : decoded;
    var cookies = document.cookie.split('; ');
    var result = key ? undefined : {};
    for (var i = 0, l = cookies.length; i < l; i++) {
      var parts = cookies[i].split('=');
      var name = decode(parts.shift());
      var cookie = decode(parts.join('='));

      if (key && key === name) {
        result = converted(cookie);
        break;
      }

      if (!key) {
        result[name] = converted(cookie);
      }
    }

    return result;
  };

  config.defaults = {};

  $.removeCookie = function (key, options) {
    if ($.cookie(key) !== undefined) {
      // Must not alter options, thus extending a fresh object...
      $.cookie(key, '', $.extend({}, options, { expires: -1 }));
      return true;
    }
    return false;
  };

}));