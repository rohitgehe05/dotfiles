/**
 * OptIn Ninja
 * (c) Web factory Ltd, 2016
 */

function center_box(box) {
  window_height = jQuery(window).height();
  center = window_height/2;
  if (box == 1) {
    box_height = jQuery('.first-optin').height();
  } else {
    box_height = jQuery('.second-optin').height();
  }

  if ((box_height + 30) > window_height) {
    return;
  } else {
    jQuery('.wf-optin-container').css('top', center - (box_height / 2));
  }
} // center_box

jQuery(window).load(function(){
  center_box(1);

  jQuery(window).resize(function(){
    if(jQuery('.first-optin').is(':visible')) {
      center_box(1);
    } else {
      center_box(2);
    }
  });
});