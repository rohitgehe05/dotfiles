<?php
/*
 * OptIn Ninja
 * (c) Web factory Ltd, 2016
 */

 class wf_optin_ninja_notifications_box extends wf_optin_ninja {
   public static function content() {
     global $post;
     $current_user = wp_get_current_user();
     $options = get_option('wf-optin', array());

     $meta = get_post_meta($post->ID, 'wf_optin_meta', true);
     if (!$meta) {
       $meta = array();
     }

     if (!isset($meta['notifications'])) {
       $meta['notifications'] = array('email_notifications' => 0, 'push_notifications' => 0);
     }

     $sounds = array('pushover' => 'Pushover (default)',
                     'bike' => 'Bike',
                     'bugle' => 'Bugle',
                     'cashregister' => 'Cash Register',
                     'classical' => 'Classical',
                     'cosmic' => 'Cosmic',
                     'falling' => 'Falling',
                     'gamelan' => 'Gamelan',
                     'incoming' => 'Incoming',
                     'intermission' => 'Intermission',
                     'magic' => 'Magic',
                     'mechanical' => 'Mechanical',
                     'pianobar' => 'Piano Bar',
                     'siren' => 'Siren',
                     'spacealarm' => 'Space Alarm',
                     'tugboat' => 'Tug Boat',
                     'alien' => 'Alien Alarm (long)',
                     'climb' => 'Climb (long)',
                     'persistent' => 'Persistent (long)',
                     'echo' => 'Pushover Echo (long)',
                     'updown' => 'Up Down (long)',
                     'none' => 'None (silent)');

     $field_generator = new wf_field_generator();
     echo '<p>Whenever a new user successfully subscribes via the OptIn you can receive one or more notifications.<br>';
     echo 'Push notifications are sent via Pushover service. Please configure them in <a href="' . admin_url('admin.php?page=wf-optin-ninja-settings') . '">settings</a>.<br>';
     echo 'Following variables are available in <i>subject</i> and <i>message</i> fields:</p>';
     echo '<ul style="margin-left: 20px; margin-top: -0.5em;" class="lists">';
     echo '<li>{subscriber-name} - name of the person who subscribed (if field is enabled)</li>';
     echo '<li>{subscriber-email} - email address of the person who subscribed</li>';
     echo '<li>{subscriber-custom}';
     if (!class_exists('wf_optin_ninja_fields')) {
       echo ' - activate <a href="http://codecanyon.net/item/custom-form-fields-addon-for-optin-ninja/10767401" target="_blank">Custom Form Fields add-on</a> to enable it';
     } else {
       echo ' - all custom fields defined for the optin except email and name; formatted in key = value lines';
     }
     echo '</li>';
     echo '<li>{optin-name} - OptIn\'s name</li>';
     echo '<li>{optin-url} - OptIn\'s full URL</li>';
     echo '</ul>';

     echo $field_generator->start_row();
     echo $field_generator->generate('label', 'Email Notifications:', 'notifications', 'email_notifications', '', '', '', true, '', '');
     echo $field_generator->generate('dropdown', '', 'notifications', 'email_notifications', array('0' => 'Disabled', '1' => 'Enabled'), '', true, false, '', '');
     echo $field_generator->end_row();

     echo $field_generator->start_row('notifications_email', $meta['notifications']['email_notifications']);
     echo $field_generator->generate('label', 'Recipients:', 'notifications', 'email_to', '', '', true, '', '', '', array());
     echo $field_generator->generate('input', '', 'notifications', 'email_to', '', '', '', '', 'Separate multiple emails with a comma, ie: "email1@sample.com, email2@sample.com"', '', array('class' => 'regular-text', 'default' => $current_user->user_email));
     echo $field_generator->end_row();
     echo $field_generator->start_row('notifications_email', $meta['notifications']['email_notifications']);
     echo $field_generator->generate('label', 'Subject:', 'notifications', 'email_subject', '', '', true, '', '', '', array());
     echo $field_generator->generate('input', '', 'notifications', 'email_subject', '', '', '', '', '', '', array('class' => 'regular-text', 'default' => 'New subscriber on {optin-name} optin'));
     echo $field_generator->end_row();
     echo $field_generator->start_row('notifications_email', $meta['notifications']['email_notifications']);
     echo $field_generator->generate('label', 'Message:', 'notifications', 'email_body', '', '', true, true, '','');
     echo $field_generator->generate('textarea', '', 'notifications', 'email_body', '', '', true, false, '', '', array('default' => 'The following person subscribed to your list powered by OptIn Ninja;
  Name: {subscriber-name}
  Email: {subscriber-email}
  OptIn Name: {optin-name} - {optin-url}'));
     echo $field_generator->end_row();

     echo $field_generator->start_row('notifications_email', $meta['notifications']['email_notifications']);
     echo '<hr>';
     echo $field_generator->end_row();

     // -------------------------------------
     if (@$options['pushover']['status'] == '1') {
       echo $field_generator->start_row();
       echo $field_generator->generate('label', 'Mobile Push Notifications:', 'notifications', 'push_notifications', '', '', '', true, '', '');
       echo $field_generator->generate('dropdown', '', 'notifications', 'push_notifications', array('0' => 'Disabled', '1' => 'Enabled'), '', true, false, '', '');
       echo $field_generator->end_row();
       echo $field_generator->start_row('notifications_push', $meta['notifications']['push_notifications']);
       echo $field_generator->generate('label', 'Recipients:', 'notifications', 'push_to', '', '', true, true, '','');
       echo $field_generator->generate('textarea', '', 'notifications', 'push_to', '', '', true, false, 'Enter one user key per line.<br>Regardless of the number of devices you have you only have <b>one</b> unique key and notifications sent to that key will be received on all devices. If you want to send on a per-device basis enter the key in this format: <i>key:device-name</i>.', '', array('default' => $options['pushover-users']));
       echo $field_generator->end_row();
       echo $field_generator->start_row('notifications_push', $meta['notifications']['push_notifications']);
       echo $field_generator->generate('label', 'Sound:', 'notifications', 'push_sound', '', '', '', true, '', '', array());
       echo $field_generator->generate('dropdown', '', 'notifications', 'push_sound', $sounds, '', '', '', '', '', array());
       echo $field_generator->end_row();
       echo $field_generator->start_row('notifications_push', $meta['notifications']['push_notifications']);
       echo $field_generator->generate('label', 'Subject:', 'notifications', 'push_subject', '', '', true, '', '', '', array());
       echo $field_generator->generate('input', '', 'notifications', 'push_subject', '', '', '', '', '', '', array('class' => 'regular-text', 'default' => 'New subscriber on {optin-name} optin'));
       echo $field_generator->end_row();
       echo $field_generator->start_row('notifications_push', $meta['notifications']['push_notifications']);
       echo $field_generator->generate('label', 'Message:', 'notifications', 'push_body', '', '', true, true, '','');
       echo $field_generator->generate('textarea', '', 'notifications', 'push_body', '', '', true, false, '', '', array('default' => 'The following person subscribed to your list powered by OptIn Ninja;
    Name: {subscriber-name}
    Email: {subscriber-email}
    OptIn Name: {optin-name} - {optin-url}'));
       echo $field_generator->end_row();
     }

     wf_field_generator::save_button();
   }
 } // wf_optin_ninja_notifications_box