/**
 * OptIn Ninja
 * (c) Web factory Ltd, 2017
 */

jQuery(function($) {
    $('body').append('<div id="wf-optin-dialog"><a href="#" title="Close OptIn" id="wf-optin-close"></a><div id="wf-optin-loading"></div></div>');
    optin_iframe = $('<iframe id="wf-optin-iframe" frameborder="0" marginwidth="0" marginheight="0"></iframe>');
    $('#wf-optin-dialog').append(optin_iframe).dialog({
      autoOpen: false,
      modal: true,
      position: { my: 'center', at: 'center', of: window },
      dialogClass: 'wf-optin-dialog',
      resizable: false,
      draggable: false,
      closeOnEscape: false,
      width: 'auto',
      height: 'auto',
      open: function() {
        $('body, html').css({ overflow: 'hidden' });
      },
      close: function() {
        $('body, html').css({ overflow: 'auto' });
        optin_iframe.attr('src', '');
      }
    });
    optin_iframe.on('load', function() {
      $('#wf-optin-loading').hide();
    });
    $(window).on('resize', function() {
        wf_center_optin_dialog();
    });

    // target our links
    $('a.optin-popup').on('click', function(e) {
      e.preventDefault();
      wf_optin_open_popup($(this).attr('href'), $(this).attr('data-optin-position'));
    });

    // popup close button
    $('#wf-optin-close').live('click', function(e) {
      e.preventDefault();
      $('#wf-optin-dialog').dialog('close');
    });
});

function wf_optin_validate_position(position) {
  predefined = ['left top',    'center top',    'right top',
                'left center', 'center center', 'right center',
                'left bottom', 'center bottom', 'right bottom',
                'left', 'center', 'right'];

  position = position || 'center';
  position = position.toLowerCase();
  if (jQuery.inArray(position, predefined) == -1) {
    return 'center center';
  } else {
    return position;
  }
} // wf_optin_validate_position

var global_time = 0;
function wf_center_optin_dialog() {
  time = new Date().getTime();
  sec = parseInt(time / 1000);
  if (sec - global_time < 2) {
    return;
  }
  global_time = sec;

  position = jQuery('#wf-optin-dialog').dialog('option', 'position');
  jQuery('#wf-optin-dialog').dialog('option', 'position', position);
} // wf_center_optin_dialog

function wf_optin_open_popup(url, position) {
  if (jQuery('.wf-optin-dialog').is(':visible')) {
    return false;
  }

  position = wf_optin_validate_position(position);

  optin_iframe.attr({ width: 620, src: url + '?popup' });
  jQuery('#wf-optin-loading').show();
  jQuery('#wf-optin-dialog').dialog('option', 'position', { my: position, at: position, of: window });
  jQuery('#wf-optin-dialog').dialog('open');

  return true;
} // wf_optin_open_popup