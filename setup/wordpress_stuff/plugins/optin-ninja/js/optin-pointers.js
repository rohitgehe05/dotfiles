/**
 * OptIn Ninja
 * (c) Web factory Ltd, 2016
 */

jQuery(document).ready(function($){
  if (typeof optin_pointers  == 'undefined') {
    return;
  }

  $.each(optin_pointers, function(index, pointer) {
    $(pointer.target).pointer({
        content: '<h3>OptIn Ninja</h3><p>' + pointer.content + '</p>',
        position: {
            edge: pointer.edge,
            align: pointer.align
        },
        width: 320,
        close: function() {
                $.post(ajaxurl, {
                    pointer: pointer.target,
                    action: 'optin_dismiss_pointer'
                });
        }
      }).pointer('open');
  });
});