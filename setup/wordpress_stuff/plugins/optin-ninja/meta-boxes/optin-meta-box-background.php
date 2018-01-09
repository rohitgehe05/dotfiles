<?php
/*
 * OptIn Ninja
 * (c) Web factory Ltd, 2016
 */

 class wf_optin_ninja_optin_background extends wf_optin_ninja {
   // background box markup
   static function content() {
     global $post;

     // Background Cover
     $background_cover = array('none' => 'Image width & height', 'cover' => 'Full page cover', 'contain' => 'Contain image within box');

     // Background Position
     $background_position = array('left-top' => 'Left Top', 'left-center' => 'Left Center', 'left-bottom' => 'Left Bottom', 'right-top' => 'Right Top', 'right-center' => 'Right Center', 'right-bottom' => 'Right Bottom', 'center-top' => 'Center Top', 'center-center' => 'Center', 'center-bottom' => 'Center Bottom');

     // Background Repeat
     $background_repeat = array('repeat' => 'Repeat X/Y', 'repeat-x' => 'Repeat X', 'repeat-y' => 'Repeat Y', 'no-repeat' => 'No Repeat', 'space' => 'Space', 'round' => 'Round');

     $meta = get_post_meta($post->ID, 'wf_optin_meta', true);

     $font_size = array();
     for ($i = 10; $i<=32; $i++) {
       $font_size[$i . 'px'] = $i . 'px';
     }

     $field_generator = new wf_field_generator();

     echo $field_generator->start_row();
     echo $field_generator->generate('label', 'Background Type:', 'optin-settings', 'background-type', '', '', '', true);
     echo $field_generator->generate('dropdown', '', 'optin-settings', 'background-type', array('image' => 'Image', 'slider' => 'Slider', 'video' => 'Video'), '', true, false, 'Slider background consists of as many slides as you upload that are animated using one of the selected animations; image background can be one big image or a tiled surface of smaller ones; video plays a selected YouTube video.<br>We have prepared a large gallery of predefined images that you can use after <a href="admin.php?page=wf-optin-ninja-settings">importing them in Settings</a>.<br>Background is not visible when the OptIn is used in a popup.');
     echo $field_generator->end_row();

     // ---------------------
     echo $field_generator->start_row('bg-video');
     echo $field_generator->generate('label', 'YouTube Video ID:', 'optin-settings', 'video-id', '', '', true, true);
     echo $field_generator->generate('input', '', 'optin-settings', 'video-id', '', '', '', '', 'Please only enter the YouTube video ID (ie f-UGhWj1xww). Not the whole URL or the embed code.', '', array('class' => 'regular-text'));
     echo $field_generator->end_row();

     // ---------------------

     echo $field_generator->start_row('bg-slider');
     echo $field_generator->generate('label', 'Slider Images:', 'optin-settings', 'optin-background', '', '', true, true);
     echo $field_generator->generate('upload', 'Upload/select slide', 'optin-settings', 'optin-background', '', '', true, false, 'You can upload new images using the WordPress Media Manager, choose images from it, or simply copy/paste the URL to an image into the field.');
     echo $field_generator->end_row();

     echo $field_generator->start_row('bg-slider');
     echo $field_generator->generate('label', 'Slider Animation:', 'optin-settings', 'slider-animation', '', '', '', true);
     echo $field_generator->generate('dropdown', '', 'optin-settings', 'slider-animation', array('0' => 'None', '1' => 'Fade', '2' => 'Slide Top', '3' => 'Slide Right', '4' => 'Slide Bottom', '5' => 'Slide Left', '6' => 'Carousel Right', '7' => 'Carousel Left'), '', true, false, '', '', array('default' => '3'));
     echo $field_generator->end_row();

     echo $field_generator->start_row('bg-slider');
     echo $field_generator->generate('label', 'Slide Interval:', 'optin-settings', 'slide-interval', '', '', '', true);
     echo $field_generator->generate('dropdown', '', 'optin-settings', 'slide-interval', array('1000' => '1 second', '2000' => '2 seconds', '3000' => '3 seconds', '4000' => '4 seconds', '5000' => '5 seconds', '6000' => '6 seconds', '7000' => '7 seconds', '8000' => '8 seconds'), '', true, false, 'Amount of time each slide is shown/paused between animations.', '', array('default' => '4000'));
     echo $field_generator->end_row();

     // ---------------------

     echo $field_generator->start_row('bg-image');
     echo $field_generator->start_row();
     echo $field_generator->generate('label', 'Background Color:', 'optin-settings', 'background-color', '', '', true);
     echo $field_generator->generate('colorpicker', '', 'optin-settings', 'background-color', '', '', true, false, '', '', array('default' => '#777777'));
     echo $field_generator->end_row();
     echo $field_generator->generate('label', 'Background Image:', 'optin-settings', 'background-image', '', '', true, true);
     echo $field_generator->generate('upload', 'Upload background', 'optin-settings', 'background-image', '', '', false, false, '', '', array('default' => plugins_url('/../images/food.png', __FILE__)));
     echo $field_generator->end_row();

     echo $field_generator->start_row('bg-image');
     echo $field_generator->generate('label', 'Background Position:', 'optin-settings', 'background-position', '', '', true, '', '', '', array());
     echo $field_generator->generate('dropdown', '', 'optin-settings', 'background-position', $background_position, '', '', '', '', '', array('columns' => 'col-2'));
     echo $field_generator->generate('label', 'Background Cover:', 'optin-settings', 'background-cover', '', '', true, '', '', '', array('columns' => 'col-2'));
     echo $field_generator->generate('dropdown', '', 'optin-settings', 'background-cover', $background_cover, '', '', '', '', '', array('columns' => 'col-2'));
     echo $field_generator->generate('label', 'Background Repeat:', 'optin-settings', 'background-repeat', '', '', true, '', '', '', array('columns' => 'col-2'));
     echo $field_generator->generate('dropdown', '', 'optin-settings', 'background-repeat', $background_repeat, '', '', '', '', '', array('columns' => 'col-2'));
     echo $field_generator->end_row();

     wf_field_generator::save_button();
   } // form
 } // wf_optin_ninja_optin_background