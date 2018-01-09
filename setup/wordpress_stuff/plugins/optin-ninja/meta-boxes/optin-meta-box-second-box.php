<?php
/*
 * OptIn Ninja
 * (c) Web factory Ltd, 2016
 */

 class wf_optin_ninja_second_box extends wf_optin_ninja {
   static function content() {
     global $post;

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

     if(!isset($meta['second-optin']['optin-text'])) {
       $content = '<h1> Box #2 Content</h1>
Edit this text and replace it with your copy.';
     } else {
       $content = $meta['second-optin']['optin-text'];
     }

     wp_editor($content, 'wf_optin_meta_second-optin_optin-text', array('dfw' => true, 'tabfocus_elements' => 'insert-media-button,save-post', 'editor_height' => 360, 'resize' => 1, 'textarea_name' => 'wf_optin_meta[second-optin][optin-text]', 'drag_drop_upload' => 1));

     $font_size = array();
     for ($i = 0; $i<=45; $i++) {
       $font_size[$i . 'px'] = $i . 'px';
     }
	
     $field_generator = new wf_field_generator();
	
     echo '<div style="clear:both; overflow:auto;">';
	 
		 echo '<div class="optin-settings-tabs-buttons">';
		 echo '<ul>
		 <li data-tab="content" data-box="2" class="wf_opt_settings_button wf_opt_settings_button2 active"><span>Content</span></li>
		 <li data-tab="button" data-box="2" class="wf_opt_settings_button wf_opt_settings_button2"><span>Button</span></li>
		 <li data-tab="background" data-box="2" class="wf_opt_settings_button wf_opt_settings_button2"><span>Box Background</span></li>
		 <li data-tab="border" data-box="2" class="wf_opt_settings_button wf_opt_settings_button2"><span>Box Border</span></li>
		 <li data-tab="inputs" data-box="2" class="wf_opt_settings_button wf_opt_settings_button2"><span>Inputs</span></li>
		 </ul>';
		 echo '</div>';
		 
		 
		 
		 echo '<div class="optin-settings-tabs">';		 
		 
			 echo '<div class="wf_opt_settings_tab wf_opt_settings_tab2" id="wf_opt_settings_tab2_content">';
			 echo $field_generator->generate('label', 'Content Font: <a href="https://www.google.com/fonts/specimen/" class="button-secondary preview-font first-box2">Preview</a>', 'second-optin', 'content-font', '', '', '', true, '', '', array('columns' => 'col-2'));
			 echo $field_generator->generate('dropdown', '', 'second-optin', 'content-font', get_option('wf-optin-google-fonts'), '', '', '', '', '', array('columns' => 'col-2', 'default' => 'Open+Sans'));
			 echo $field_generator->generate('label', 'Content Font Size:', 'second-optin', 'content-font-size', '', '', true, '', '', '', array('columns' => 'col-2'));
			 echo $field_generator->generate('dropdown', '', 'second-optin', 'content-font-size', $font_size, '', '', '', '', '', array('columns' => 'col-2', 'default' => '18px'));
			 echo $field_generator->generate('label', 'Box Font Color:', 'second-optin', 'box-font-color', '', '', true, '', '', '', array('columns' => 'col-2'));
			 echo $field_generator->generate('colorpicker', '', 'second-optin', 'box-font-color', '', '', '', '', '', '', array('columns' => 'col-2', 'default' => '#111111'));
			 echo '</div>';		 
			 
			 
			 echo '<div class="wf_opt_settings_tab wf_opt_settings_tab2" id="wf_opt_settings_tab2_button" style="display:none">';
			 echo $field_generator->generate('label', 'Subscribe Button Text:', 'second-optin', 'button-text', '', '', true, '', '', '', array('columns' => 'col-2'));
			 echo $field_generator->generate('input', '', 'second-optin', 'button-text', '', '', '', '', '', '', array('columns' => 'col-2', 'default' => 'Subscribe and don\'t miss out!'));
			 echo $field_generator->generate('label', 'Button Font: <a href="https://www.google.com/fonts/specimen/" class="button-secondary preview-font first-box2">Preview</a>', 'second-optin', 'button-font', '', '', true, '', '', '', array('columns' => 'col-2'));
			 echo $field_generator->generate('dropdown', '', 'second-optin', 'button-font', get_option('wf-optin-google-fonts'), '', '', '', '', '', array('columns' => 'col-2', 'default' => 'Bevan'));
			 echo $field_generator->generate('label', 'Button Text Font Size:', 'second-optin', 'button-font-size', '', '', true, '', '', '', array('columns' => 'col-2'));
			 echo $field_generator->generate('dropdown', '', 'second-optin', 'button-font-size', $font_size, '', '', '', '', '', array('columns' => 'col-2', 'default' => '22px'));
			 echo $field_generator->generate('label', 'Button Border Radius:', 'second-optin', 'button-border-radius', '', '', true, '', '', '', array('columns' => 'col-2'));
			 echo $field_generator->generate('dropdown', '', 'second-optin', 'button-border-radius', $border_radius, '', '', '', '', '', array('columns' => 'col-2', 'default' => '5px'));
			 echo $field_generator->generate('label', 'Button Background Color:', 'second-optin', 'button-background-color', '', '', true, '', '', '', array('columns' => 'col-2'));
			 echo $field_generator->generate('colorpicker', '', 'second-optin', 'button-background-color', '', '', '', '', '', '', array('columns' => 'col-2', 'default' => '#FAC564'));
			 echo $field_generator->generate('label', 'Button Text Color:', 'second-optin', 'button-text-color', '', '', true, '', '', '', array('columns' => 'col-2'));
			 echo $field_generator->generate('colorpicker', '', 'second-optin', 'button-text-color', '', '', '', '', '', '', array('columns' => 'col-2', 'default' => '#ffffff'));
			 echo $field_generator->generate('label', 'Footer Text (below button):', 'second-optin', 'footer-text', '', '', true, '', '', '', array());
			 echo $field_generator->generate('input', '', 'second-optin', 'footer-text', '', '', '', '', '', '', array('class' => 'regular-text', 'default' => 'Don\'t worry, we hate spam as much as you do!'));
			 echo '</div>';
			 
			 
			 echo '<div class="wf_opt_settings_tab wf_opt_settings_tab2" id="wf_opt_settings_tab2_background" style="display:none">';
			 echo $field_generator->generate('label', 'Box Background Color:', 'second-optin', 'box-background-color', '', '', true, '', '', '', array('columns' => 'col-2'));
			 echo $field_generator->generate('colorpicker', '', 'second-optin', 'box-background-color', '', '', '', '', '', '', array('columns' => 'col-2', 'default' => '#f2f2f2'));
			 echo $field_generator->generate('label', 'Box Shadow Color:', 'second-optin', 'box-shadow-color', '', '', true, '', '', '', array('columns' => 'col-2'));
			 echo $field_generator->generate('colorpicker', '', 'second-optin', 'box-shadow-color', '', '', '', '', '', '', array('columns' => 'col-2', 'default' => '#f2f2f2'));
			 echo $field_generator->generate('label', 'Background Opacity:', 'second-optin', 'bg-opacity', '', '', true, '', '', '', array('columns' => 'col-2'));
			 echo $field_generator->generate('input', '', 'second-optin', 'bg-opacity', '', '', '', '', '', '', array('columns' => 'col-2', 'default' => '1', 'class' => 'small-text'));
			 echo $field_generator->generate('label', 'Background Position:', 'second-optin', 'background-position', '', '', true, '', '', '', array());
			 echo $field_generator->generate('dropdown', '', 'second-optin', 'background-position', $background_position, '', '', '', '', '', array('columns' => 'col-2'));
			 echo $field_generator->generate('label', 'Background Cover:', 'second-optin', 'background-cover', '', '', true, '', '', '', array('columns' => 'col-2'));
			 echo $field_generator->generate('dropdown', '', 'second-optin', 'background-cover', $background_cover, '', '', '', '', '', array('columns' => 'col-2'));
			 echo $field_generator->generate('label', 'Background Repeat:', 'second-optin', 'background-repeat', '', '', true, '', '', '', array('columns' => 'col-2'));
			 echo $field_generator->generate('dropdown', '', 'second-optin', 'background-repeat', $background_repeat, '', '', '', '', '', array('columns' => 'col-2'));
			 echo $field_generator->generate('label', 'Background Image:', 'second-optin', 'background-image', '', '', true, true);
			 echo $field_generator->generate('upload', 'Upload background', 'second-optin', 'background-image', '', '', false, false, '', '', array('default' => ''));
			 echo '</div>';
			 
			 echo '<div class="wf_opt_settings_tab wf_opt_settings_tab2" id="wf_opt_settings_tab2_border" style="display:none">';
			 echo $field_generator->generate('label', 'Box Border Radius:', 'second-optin', 'box-border-radius', '', '', '', true, '', '', array('columns' => 'col-2'));
			 echo $field_generator->generate('dropdown', '', 'second-optin', 'box-border-radius', $border_radius, '', '', '', '', '', array('columns' => 'col-2', 'default' => '10px'));
			 echo $field_generator->generate('label', 'Box Border Width:', 'second-optin', 'box-border-width', '', '', '', true, '', '', array('columns' => 'col-2'));
			 echo $field_generator->generate('dropdown', '', 'second-optin', 'box-border-width', $font_size, '', '', '', '', '', array('columns' => 'col-2', 'default' => '2px'));
			 echo $field_generator->generate('label', 'Box Border Color:', 'second-optin', 'box-border-color', '', '', true, '', '', '', array('columns' => 'col-2'));
			 echo $field_generator->generate('colorpicker', '', 'second-optin', 'box-border-color', '', '', '', '', '', '', array('columns' => 'col-2', 'default' => '#bcbcbc'));
			 echo '</div>';
			 
			 echo '<div class="wf_opt_settings_tab wf_opt_settings_tab2" id="wf_opt_settings_tab2_inputs" style="display:none">';
			 echo $field_generator->generate('label', 'Input Fields Border Radius:', 'second-optin', 'input-border-radius', '', '', '', true, '', '', array('columns' => 'col-2'));
			 echo $field_generator->generate('dropdown', '', 'second-optin', 'input-border-radius', $border_radius, '', '', '', '', '', array('columns' => 'col-2', 'default' => '10px'));
			 echo $field_generator->generate('label', 'Input Fields Background Color:', 'second-optin', 'input-background-color', '', '', true, '', '', '', array('columns' => 'col-2'));
			 echo $field_generator->generate('colorpicker', '', 'second-optin', 'input-background-color', '', '', '', '', '', '', array('columns' => 'col-2', 'default' => '#ffffff'));
			 echo $field_generator->generate('label', 'Input Fields Text Color:', 'second-optin', 'input-text-color', '', '', true, '', '', '', array('columns' => 'col-2'));
			 echo $field_generator->generate('colorpicker', '', 'second-optin', 'input-text-color', '', '', '', '', '', '', array('columns' => 'col-2', 'default' => '#000000'));
			 echo $field_generator->generate('label', 'Input Fields Border Color:', 'second-optin', 'input-border-color', '', '', true, '', '', '', array('columns' => 'col-2'));
			 echo $field_generator->generate('colorpicker', '', 'second-optin', 'input-border-color', '', '', '', '', '', '', array('columns' => 'col-2', 'default' => '#000000'));
     		 echo '</div>';
			 
			 
		 echo '</div>';
	 echo '</div>';
	 
	 
	 echo '<div class="clearfix"></div>';
	 
	 
     echo '<br />';
	 
     wf_field_generator::save_button();
	 
	 echo '<input type="button" class="button" id="wf_opt_copy_settings2" value="Copy Style Settings to Box 1" style="float:right;" />';
   } // content
 } // wf_optin_ninja_second_box