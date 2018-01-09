<?php
/*
 * OptIn Ninja
 * (c) Web factory Ltd, 2016
 */

 class wf_optin_ninja_autoresponder extends wf_optin_ninja {
   static function content() {
     global $post;

     $meta = get_post_meta($post->ID, 'wf_optin_meta', true);
     if (!$meta) {
       $meta = array();
     }

     if(!isset($meta['autoresponder']['body'])) {
       $content = '<h2>Hi {user-name}</h2>
Thank you for subscribing. You can expect a lot of great emails from us.';
     } else {
       $content = $meta['autoresponder']['body'];
     }

     $field_generator = new wf_field_generator();

     echo '<p><b>Important!</b> Welcome emails are sent only if you use Facebook or Local database setting for the <a href="#optin-form_placeholder-email">autoresponder</a>. If you\'re using a 3rd party autoresponder please send emails trough that service.</p>';

     echo $field_generator->start_row();
     echo $field_generator->generate('label', 'Send Emails:', 'autoresponder', 'send_email', '', '', '', true, '', '');
     echo $field_generator->generate('dropdown', '', 'autoresponder', 'send_email', array('0' => 'No', '1' => 'Yes'), '', true, false, 'Email is sent after a visitor successfully subscribes.', '');
     echo $field_generator->end_row();

     echo $field_generator->start_row();
     echo $field_generator->generate('label', 'From:', 'autoresponder', 'from', '', '', true, '', '', '', array());
     echo $field_generator->generate('input', '', 'autoresponder', 'from', '', '', '', '', 'Write only the email address or use the "Name &lt;email@test.com&gt; format."', '', array('class' => 'regular-text', 'default' => 'Admin <' . get_option('admin_email') . '>'));
     echo $field_generator->end_row();

     echo $field_generator->start_row();
     echo $field_generator->generate('label', 'Subject:', 'autoresponder', 'subject', '', '', true, '', '', '', array());
     echo $field_generator->generate('input', '', 'autoresponder', 'subject', '', '', '', '', 'You can use 2 variables in the subject and email body: {user-name} and {user-email}.', '', array('class' => 'regular-text', 'default' => 'Thank you for subscribing!'));
     echo $field_generator->end_row();

     wp_editor($content, 'wf_optin_meta_autoresponder-body', array('dfw' => true, 'tabfocus_elements' => 'insert-media-button,save-post', 'editor_height' => 360, 'resize' => 1, 'textarea_name' => 'wf_optin_meta[autoresponder][body]', 'drag_drop_upload' => 1));

     echo '<br>';
     wf_field_generator::save_button();
   } // content
 } // wf_optin_ninja_second_box