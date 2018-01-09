<?php
/*
 * OptIn Ninja
 * (c) Web factory Ltd, 2017
 */


class wf_field_generator extends wf_optin_ninja {
  static function save_button() {
    global $post;

    if(isset($post->ID) && $post->post_status != 'auto-draft') {
      echo '<input name="save" type="submit" class="button button-primary button-large" id="publish" accesskey="p" value="Update">';
    } else {
      //echo '<input type="submit" name="publish" id="publish" class="button button-primary button-large" value="Publish" accesskey="p">';
    }
  }


   function start_row($class = '', $visible = true) {
     if ($visible) {
       $output = '<div class="meta-container ' . $class . '" style="">';
     } else {
       $output = '<div class="meta-container ' . $class . '" style="display:none;">';
     }
     return $output;
   } // start_row
	
   	

   function end_row() {
     $output = '</div>';
     return $output;
   } // end_row


   function generate($type, $label, $box, $name, $value = '', $selected = '', $multiple = true, $inline = false, $desc = '', $standalone_meta = false, $params = array()) {
     global $post;
     $output = '';

     extract($params);

     if (!isset($columns)) {
       $columns = 'col-6';
       if ($type == 'label') {
         $columns = 'col-2';
       }
     }

     if (!isset($align)) {
       $align = 'left';
     }

     if (!isset($default)) {
       $default = '';
     }

     if ($value == '') {
       $value = get_post_meta($post->ID, 'wf_optin_meta', true);
       if (!$value) {
         $value = array();
       }
       if (isset($value[$box][$name])) {
         $value = $value[$box][$name];
       } else {
         $value = '';
       }
     }

     if ($value == '' && get_post_meta($post->ID, 'wf_optin_meta', true) == false) {
       $value = $default;
       $selected = $default;
     }

     if ($type == 'dropdown') {
       $saved_value = get_post_meta($post->ID, 'wf_optin_meta', true);
       if (!$saved_value) {
         $saved_value = array();
       }
       if (isset($saved_value[$box][$name])) {
         $saved_value = $saved_value[$box][$name];
       } else {
         $saved_value = '';
       }

       $selected = $saved_value;
       if (!$saved_value) {
         $saved_value = get_post_meta($post->ID, $name, true);
         $selected = $saved_value;
       }

       if ($selected == '' && get_post_meta($post->ID, 'wf_optin_meta', true) == false) {
         $selected = $default;
       }

     }

     if ($standalone_meta && $type != 'dropdown') {
       $value = get_post_meta($post->ID, $name, true);
     }
     if ($standalone_meta && $type == 'dropdown') {
       $selected = get_post_meta($post->ID, $name, true);
     }

     switch ($type) {
       // Label
       case 'label':
         $output .= '<div class="label ' . $columns . ' ' . $align.  '">';
         $output .= self::label($box, $name, $label, $inline, $desc);
         $output .= '</div>';
       break;
       // Dropdown
       case 'dropdown':
         $output .= '<div class="' . $columns . '">';
         $output .= self::dropdown($box, $name, $value, $selected, $standalone_meta, $desc);
         $output .= '</div>';
       break;
       // Input
       case 'input':
         $output .= '<div class="' . $columns . '">';
         $output .= self::input($box, $name, $value, $desc, $standalone_meta, $params);
         $output .= '</div>';
       break;
       // date
       case 'date':
         $output .= '<div class="' . $columns . '">';
         $output .= self::date($box, $name, $value, $desc, $standalone_meta, $params);
         $output .= '</div>';
       break;
       // Upload
       case 'upload':
         $output .= '<div class="' . $columns . '">';
         $output .= self::upload($label, $box, $name, $value, $multiple, $desc);
         $output .= '</div>';
       break;
       // Colorpicker
       case 'colorpicker':
         $output .= '<div class="' . $columns . '">';
         $output .= self::colorpicker($box, $name, $value, $desc);
         $output .= '</div>';
       break;
       // Textarea
       case 'textarea':
         $output .= '<div class="' . $columns . '">';
         $output .= self::textarea($box, $name, $value, $desc);
         $output .= '</div>';
       break;
       // Break
       case 'break':
         $output .= '<br/>';
       break;
     }

     return $output;
   } // generate


   function textarea($box, $name, $value = '', $desc = '') {
     $output = '';

     $output .= '<textarea id="' . $box . '_' . $name . '" name="wf_optin_meta[' . $box . '][' . $name . ']" class="widefat" rows="4">' . $value . '</textarea>';

     if ($desc) {
       $output .= '<p class="description">' . $desc . '</p>';
     }

     return $output;
   } // textarea


   function colorpicker($box, $name, $value = '', $desc = '') {
     $output = '';

     $output .= '<input id="' . $box . '_' . $name . '" name="wf_optin_meta[' . $box . '][' . $name . ']" type="text" class="colorpicker" value="' . $value . '" data-default-color="#ffffff" />';

     if ($desc) {
       $output .= '<p class="description">' . $desc . '</p>';
     }

     return $output;
   } // colorpicker


   function label($box, $name, $string, $inline, $desc) {
     $class = 'class="optin-label"';
     if ($inline) {
       $class = 'class="optin-label top-label"';
     }
     $output = '<label for="' . $box . '_' . $name . '" ' . $class . '>' . $string . '</label>';
     if ($desc != '') {
       $output .= '<p class="description">' . $desc . '</p>';
     }
     return $output;
   } // label


   function upload($button_string, $box, $name, $value = '', $multiple = false, $desc) {
     $output = '';
     if ($multiple) {
       $tmp = '[]';
     } else {
       $tmp = '';
     }

     $output .= '<div class="bg-input-holder">';

     if (is_array($value)) {
       foreach ($value as $key => $val) {
       $output .= '<div class="bg-input"><input type="text" value="' . $val . '" class="regular-text process_custom_images" id="' . $box . '_' . $name . '" name="wf_optin_meta[' . $box . '][' . $name . ']' . $tmp . '" /> <button class="set_custom_images button">' . $button_string . '</button>';
       if ($multiple || sizeof($value) > 1) {
        $output .= ' <a href="#" class="button remove-bg"><div class="dashicons dashicons-post-trash"></div> Remove slide</a></div>';
     } else {
        $output .= ' <a href="#" class="button remove-bg2"><div class="dashicons dashicons-post-trash"></div> Clear image</a></div>';
     }
       }
     } else {
       $output .= '<div class="bg-input"><input type="text" value="' . $value . '" class="regular-text process_custom_images" id="' . $box . '_' . $name . '"  name="wf_optin_meta[' . $box . '][' . $name . ']' . $tmp .'" />
                 <button class="set_custom_images button">' . $button_string . '</button> ';
     if ($multiple || sizeof($value) > 1) {
        $output .= ' <a href="#" class="button remove-bg"><div class="dashicons dashicons-post-trash"></div> Remove slide</a></div>';
     } else {
        $output .= ' <a href="#" class="button remove-bg2"><div class="dashicons dashicons-post-trash"></div> Clear image</a></div>';
     }
     }

     $output .= '</div>';

     if ($multiple) {
       $output .= '<a href="#" class="button add-new-bg"><div class="dashicons dashicons-plus"></div> Add another slide</a>';
     }

     if ($desc) {
       $output .= '<p class="description">' . $desc . '</p>';
     }

     return $output;
   } // upload


   function dropdown($box, $name, $value, $selected = '', $standalone_meta = false, $desc = '') {
     $output = '';

     $id = $name;
     if ($standalone_meta) {
       $name = $name;
     } else {
       $name = 'wf_optin_meta[' . $box . '][' . $name . ']';
     }

     $output .= '<select id="' . $box . '_' . $id . '" name="' . $name . '">';

     if (is_array($value)) {
       foreach ($value as $key => $val) {
         if (is_array($val)) $val = $key;
         if ($selected == $key) {
           $output .= '<option value="' . $key . '" selected="selected">' . $val . '</option>';
         } else {
           $output .= '<option value="' . $key . '">' . $val . '</option>';
         }
       }
     } else if (is_object($value)) {
       foreach ($value as $key => $val) {
         if ($selected == $key) {
           $output .= '<option value="' . $key . '" selected="selected">' . $val . '</option>';
         } else {
           $output .= '<option value="' . $key . '">' . $val . '</option>';
         }
       }
     }

     $output .= '</select>';

     if ($desc) {
       $output .= '<p class="description">' . $desc . '</p>';
     }

     return $output;
   } // dropdown


   function input($box, $name, $value = '', $desc = '', $standalone_meta = false, $params = array()) {
     $output = '';

     $name_new = 'wf_optin_meta[' . $box . '][' . $name . ']';
     if ($standalone_meta) {
       $name_new = $name;
     }

     $tmp = '';
     if ($params) {
       foreach ($params as $param => $val) {
         if ($param[0] == '_') {
           continue;
         }
         $tmp .= ' ' . $param . '="' . $val . '"';
       }
     }

     if (isset($params['_before_field'])) {
       $output = '<span>' . $params['_before_field'] . '</span>' . $output;
     }
     $output .= '<input id="' . $box . '_' . $name . '" type="text" name="' . $name_new . '" value="' . $value . '"' . $tmp . ' />';
     if ($desc) {
       $output .= '<p class="description">' . $desc . '</p>';
     }

     return $output;
   } // input
   
   function date($box, $name, $value = '', $desc = '', $standalone_meta = false, $params = array()) {
     $output = '';

     $name_new = 'wf_optin_meta[' . $box . '][' . $name . ']';
     if ($standalone_meta) {
       $name_new = $name;
     }

     $tmp = '';
     if ($params) {
       foreach ($params as $param => $val) {
         if ($param[0] == '_') {
           continue;
         }
         $tmp .= ' ' . $param . '="' . $val . '"';
       }
     }

     if (isset($params['_before_field'])) {
       $output = '<span>' . $params['_before_field'] . '</span>' . $output;
     }
     $output .= '<input id="' . $box . '_' . $name . '" type="date" name="' . $name_new . '" value="' . $value . '"' . $tmp . ' />';
     if ($desc) {
       $output .= '<p class="description">' . $desc . '</p>';
     }

     return $output;
   } // date
 } // wf_field_generator
 