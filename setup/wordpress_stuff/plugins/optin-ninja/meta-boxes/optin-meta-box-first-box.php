<?php
/*
 * OptIn Ninja
 * (c) Web factory Ltd, 2016
 */

 class wf_optin_ninja_first_box extends wf_optin_ninja {
   static function content() {
     global $post;
     $field_generator = new wf_field_generator();

     $border_radius = array();
     for($i=0;$i<=30;$i++) {
       $border_radius[$i] = $i . ' px';
     }

     // Background Cover
     $background_cover = array('none' => 'Image width & height', 'cover' => 'Full page cover', 'contain' => 'Contain image within box');

     // Background Position
     $background_position = array('left-top' => 'Left Top', 'left-center' => 'Left Center', 'left-bottom' => 'Left Bottom', 'right-top' => 'Right Top', 'right-center' => 'Right Center', 'right-bottom' => 'Right Bottom', 'center-top' => 'Center Top', 'center-center' => 'Center', 'center-bottom' => 'Center Bottom');

     // Background Repeat
     $background_repeat = array('repeat' => 'Repeat X/Y', 'repeat-x' => 'Repeat X', 'repeat-y' => 'Repeat Y', 'no-repeat' => 'No Repeat', 'space' => 'Space', 'round' => 'Round');

     $meta = get_post_meta($post->ID, 'wf_optin_meta', true);
     if (!$meta) {
       $meta = array();
     }

     if(!isset($meta['first-optin']['optin-text'])) {
       $content = '<h1> Box #1 Content</h1>
Edit this text and replace it with your copy.';
     } else {
       $content = $meta['first-optin']['optin-text'];
     }

     echo $field_generator->start_row();
     echo $field_generator->generate('label', 'Disable First Content Box:', 'first-optin', 'disable-first-box', '', '', '', true, '', '', array());
     echo $field_generator->generate('dropdown', '', 'first-optin', 'disable-first-box', array('0' => 'No', '1' => 'Yes'), '', true, false, 'If disabled users will imediatelly see the second content box. Stats for an OptIn page will not bo correct if you use it for some time with and then some time without the first box. Clone the OptIn page and then enable/disable the first box in order to keep the stats relevant.', '');
     echo $field_generator->end_row();

     wp_editor($content, 'wf_optin_meta_first-optin_optin-text', array('dfw' => true, 'tabfocus_elements' => 'insert-media-button,save-post', 'editor_height' => 360, 'resize' => 1, 'textarea_name' => 'wf_optin_meta[first-optin][optin-text]', 'drag_drop_upload' => 1));

     $font_size = array();
     for ($i = 0; $i<=45; $i++) {
       $font_size[$i . 'px'] = $i . 'px';
     }
	 
	 echo '<div style="clear:both; overflow:auto;">';
	 
		 echo '<div class="optin-settings-tabs-buttons">';
		 echo '<ul>
		 <li data-tab="content" data-box="1" class="wf_opt_settings_button wf_opt_settings_button1 active"><span>Content</span></li>
		 <li data-tab="button" data-box="1" class="wf_opt_settings_button wf_opt_settings_button1"><span>Button</span></li>
		 <li data-tab="background" data-box="1" class="wf_opt_settings_button wf_opt_settings_button1"><span>Box Background</span></li>
		 <li data-tab="border" data-box="1" class="wf_opt_settings_button wf_opt_settings_button1"><span>Box Border</span></li>
		 </ul>';
		 echo '</div>';
		 
		 echo '<div class="optin-settings-tabs">';
		 
		 echo '<div class="wf_opt_settings_tab wf_opt_settings_tab1"  id="wf_opt_settings_tab1_content">';
	
		 echo $field_generator->generate('label', 'Content Font: <a href="https://www.google.com/fonts/specimen/" class="button-secondary preview-font">Preview</a>', 'first-optin', 'content-font', '', '', '', true, '', '', array('columns' => 'col-2'));
		 echo $field_generator->generate('dropdown', '', 'first-optin', 'content-font', get_option('wf-optin-google-fonts'), '', '', '', '', '', array('columns' => 'col-2', 'default' => 'Open+Sans'));
		 echo $field_generator->generate('label', 'Content Font Size:', 'first-optin', 'content-font-size', '', '', true, '', '', '', array('columns' => 'col-2'));
		 echo $field_generator->generate('dropdown', '', 'first-optin', 'content-font-size', $font_size, '', '', '', '', '', array('columns' => 'col-2', 'default' => '18px'));
		 echo $field_generator->generate('label', 'Box Font Color:', 'first-optin', 'box-font-color', '', '', true, '', '', '', array('columns' => 'col-2'));
		 echo $field_generator->generate('colorpicker', '', 'first-optin', 'box-font-color', '', '', '', '', '', '', array('columns' => 'col-2', 'default' => '#111111'));
	
		 echo '</div>';
		 
		 echo '<div class="wf_opt_settings_tab wf_opt_settings_tab1"  id="wf_opt_settings_tab1_button" style="display:none">';
		 echo $field_generator->generate('label', 'Goto Box #2 Button Text:', 'first-optin', 'button-text', '', '', true, '', '', '', array('columns' => 'col-2'));
		 echo $field_generator->generate('input', '', 'first-optin', 'button-text', '', '', '', '', '', '', array('columns' => 'col-2', 'default' => 'Get this deal NOW!'));
		 echo $field_generator->generate('label', 'Button Font: <a href="https://www.google.com/fonts/specimen/" class="button-secondary preview-font first-box2">Preview</a>', 'first-optin', 'button-font', '', '', true, '', '', '', array('columns' => 'col-2'));
		 echo $field_generator->generate('dropdown', '', 'first-optin', 'button-font', get_option('wf-optin-google-fonts'), '', '', '', '', '', array('columns' => 'col-2', 'default' => 'Bevan'));
		 echo $field_generator->generate('label', 'Button Text Font Size:', 'first-optin', 'button-font-size', '', '', true, '', '', '', array('columns' => 'col-2'));
		 echo $field_generator->generate('dropdown', '', 'first-optin', 'button-font-size', $font_size, '', '', '', '', '', array('columns' => 'col-2', 'default' => '22px'));
		 echo $field_generator->generate('label', 'Button Border Radius:', 'first-optin', 'button-border-radius', '', '', true, '', '', '', array('columns' => 'col-2'));
		 echo $field_generator->generate('dropdown', '', 'first-optin', 'button-border-radius', $border_radius, '', '', '', '', '', array('columns' => 'col-2', 'default' => '5px'));
		 echo $field_generator->generate('label', 'Button Background Color:', 'first-optin', 'button-background-color', '', '', true, '', '', '', array('columns' => 'col-2'));
		 echo $field_generator->generate('colorpicker', '', 'first-optin', 'button-background-color', '', '', '', '', '', '', array('columns' => 'col-2', 'default' => '#FAC564'));
		 echo $field_generator->generate('label', 'Button Text Color:', 'first-optin', 'button-text-color', '', '', true, '', '', '', array('columns' => 'col-2'));
		 echo $field_generator->generate('colorpicker', '', 'first-optin', 'button-text-color', '', '', '', '', '', '', array('columns' => 'col-2', 'default' => '#ffffff'));
		 echo $field_generator->generate('label', 'Footer Text (below button):', 'first-optin', 'footer-text', '', '', true, '', '', '', array());
		 echo $field_generator->generate('input', '', 'first-optin', 'footer-text', '', '', '', '', '', '', array('class' => 'regular-text', 'default' => ''));
		 
		 echo '</div>';
		 
		 
		 echo '<div class="wf_opt_settings_tab wf_opt_settings_tab1"  id="wf_opt_settings_tab1_background" style="display:none">';
		 echo $field_generator->generate('label', 'Box Background Color:', 'first-optin', 'box-background-color', '', '', true, '', '', '', array('columns' => 'col-2'));
		 echo $field_generator->generate('colorpicker', '', 'first-optin', 'box-background-color', '', '', '', '', '', '', array('columns' => 'col-2', 'default' => '#f2f2f2'));
		 echo $field_generator->generate('label', 'Box Shadow Color:', 'first-optin', 'box-shadow-color', '', '', true, '', '', '', array('columns' => 'col-2'));
		 echo $field_generator->generate('colorpicker', '', 'first-optin', 'box-shadow-color', '', '', '', '', '', '', array('columns' => 'col-2', 'default' => '#f2f2f2'));
		 echo $field_generator->generate('label', 'Background Opacity:', 'first-optin', 'bg-opacity', '', '', true, '', '', '', array('columns' => 'col-2'));
		 echo $field_generator->generate('input', '', 'first-optin', 'bg-opacity', '', '', '', '', '', '', array('columns' => 'col-2', 'default' => '1', 'class' => 'small-text'));
		 echo $field_generator->generate('label', 'Background Position:', 'first-optin', 'background-position', '', '', true, '', '', '', array());
		 echo $field_generator->generate('dropdown', '', 'first-optin', 'background-position', $background_position, '', '', '', '', '', array('columns' => 'col-2'));
		 echo $field_generator->generate('label', 'Background Cover:', 'first-optin', 'background-cover', '', '', true, '', '', '', array('columns' => 'col-2'));
		 echo $field_generator->generate('dropdown', '', 'first-optin', 'background-cover', $background_cover, '', '', '', '', '', array('columns' => 'col-2'));
		 echo $field_generator->generate('label', 'Background Repeat:', 'first-optin', 'background-repeat', '', '', true, '', '', '', array('columns' => 'col-2'));
		 echo $field_generator->generate('dropdown', '', 'first-optin', 'background-repeat', $background_repeat, '', '', '', '', '', array('columns' => 'col-2'));
		 echo $field_generator->generate('label', 'Background Image:', 'first-optin', 'background-image', '', '', true, true);
		 echo $field_generator->generate('upload', 'Upload background', 'first-optin', 'background-image', '', '', false, false, '', '', array('default' => ''));
		 echo '</div>';
		 
		 
		 echo '<div class="wf_opt_settings_tab wf_opt_settings_tab1"  id="wf_opt_settings_tab1_border" style="display:none">';
		 echo $field_generator->generate('label', 'Box Border Radius:', 'first-optin', 'box-border-radius', '', '', '', true, '', '', array('columns' => 'col-2'));
		 echo $field_generator->generate('dropdown', '', 'first-optin', 'box-border-radius', $border_radius, '', '', '', '', '', array('columns' => 'col-2', 'default' => '10px'));
		 echo $field_generator->generate('label', 'Box Border Width:', 'first-optin', 'box-border-width', '', '', '', true, '', '', array('columns' => 'col-2'));
		 echo $field_generator->generate('dropdown', '', 'first-optin', 'box-border-width', $font_size, '', '', '', '', '', array('columns' => 'col-2', 'default' => '2px'));
		 echo $field_generator->generate('label', 'Box Border Color:', 'first-optin', 'box-border-color', '', '', true, '', '', '', array('columns' => 'col-2'));
		 echo $field_generator->generate('colorpicker', '', 'first-optin', 'box-border-color', '', '', '', '', '', '', array('columns' => 'col-2', 'default' => '#bcbcbc'));
		 
		 echo '</div>';
		 echo '</div>';
	 echo '</div>';
	 
	 
	 echo '<div class="clearfix"></div>';
	 echo '<br />';
	 
     wf_field_generator::save_button();
	 
	 echo '<input type="button" class="button" id="wf_opt_copy_settings" value="Copy Style Settings to Box 2" style="float:right;" />';
	 
   } // content
 } // wf_optin_ninja_first_box