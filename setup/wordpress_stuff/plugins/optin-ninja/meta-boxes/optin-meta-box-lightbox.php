<?php
/*
 * OptIn Ninja
 * (c) Web factory Ltd, 2014
 */

class wf_optin_ninja_lightbox extends wf_optin_ninja {
   static function content() {
     global $post;
      
     $options = get_option('wf-optin', array());

     $field_generator = new wf_field_generator();

     if (isset($options['disable_popup']) && $options['disable_popup']) {
       echo '<p><b>Popups are disabled</b>. You can enable them in <a href="' . admin_url('edit.php?post_type=optin-pages&page=wf-optin-ninja-settings') . '">settings</a>.</p>';
     } else {
       $link = get_permalink($post->ID);
       $link = str_replace(home_url(), '', $link);
       echo '<p>Any link can open the OptIn in a popup if you add the <i>optin-popup</i> class to it and set the link (href parameter) to <i>'. $link .'</i>. Example:<br>
       &lt;a href="' . $link . '" class="optin-popup"&gt;click here&lt;/a&gt;</p>';
       
       echo '<p>You can also use the shortcode:<br>
       <i>[optin-popup id="' . $post->ID . '" class="optional-class"]click here[/optin-popup]</i></p>';
     }
     
     //wf_field_generator::save_button();
   } // content
} // wf_optin_ninja_lightbox