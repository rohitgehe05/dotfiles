<?php
/*
 * OptIn Ninja
 * (c) Web factory Ltd, 2016
 */

 class wf_optin_ninja_optin_general_settings extends wf_optin_ninja {
   static function content() {
     global $post, $wpdb;

     // A/B Tests
     $tests = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . WF_OPT_AB . " ORDER by name DESC");
     $tests_array = array();
     $tests_array[0] = '- None -';
     if ($tests) {
       foreach ($tests as $test) {
         $tests_array[$test->slug] = $test->name;
       }
     }

     $field_generator = new wf_field_generator();

     echo $field_generator->start_row();
     echo $field_generator->generate('label', 'Custom URL:', 'optin-settings', '_optin-custom-url', '', '', true, '', '', '');
     echo $field_generator->generate('input', 'Custom URL', 'optin-settings', '_optin-custom-url', '', '', '','', 'If the default URL (see above) doesn\'t suite your needs you can define a custom one. It has to start and end with a forward slash -  "/". Putting in just "/" will make the OptIn Page appear on the site\'s home page.', true, array('class' => 'normal-text code', '_before_field' => home_url()));
     echo $field_generator->end_row();
     echo $field_generator->start_row();
     echo $field_generator->generate('label', 'A/B Test:', 'optin-form', '_ab-test', '', '', '', true, '', '');
     echo $field_generator->generate('dropdown', '', 'optin-form', '_ab-test', $tests_array, '', '', '', 'Each OptIn can belong to a single test. If you change the Test while people are using it stats will no longer be relative. <a href="admin.php?page=wf-optin-ninja-ab-tests">Manage A/B Tests</a>', true);
     echo $field_generator->end_row();

     echo $field_generator->start_row();
     echo $field_generator->generate('label', 'Google Analytics Tracking Code:', 'optin-settings', 'google-analytics-code', '', '', true, true, '','');
     echo $field_generator->generate('textarea', '', 'optin-settings', 'google-analytics-code', '', '', true, false, 'Copy/paste the complete tracking code Google generates (&lt;script&gt; and everything).', '');
     echo $field_generator->end_row();

     echo $field_generator->start_row();
     echo $field_generator->generate('label', 'Track Google Analytics Events:', 'optin-form', 'google-analytics-events', '', '', '', true, '', '');
     echo $field_generator->generate('dropdown', '', 'optin-form', 'google-analytics-events', array('0' => 'No', '1' => 'Yes'), '', true, false, 'Track second content box views and conversions as GA events. Event tracking supports Universal Analytics and "old" Google Analytics.', '');
     echo $field_generator->end_row();

     echo $field_generator->start_row();
     echo $field_generator->generate('label', 'Prevent From Leaving:', 'optin-form', 'prevent-from-leaving', '', '', '', true, '', '');
     echo $field_generator->generate('dropdown', '', 'optin-form', 'prevent-from-leaving', array('no' => 'No', 'yes' => 'Yes'), '', true, false, 'Visitors who try to navigate away from the OptIn Page without subscribing will be shown a custom message. Please note that implementation of this feature varies across different browsers and the feature is automatically disabled when the OptIn is used in a popup.', '', array('default' => 'yes'));
     echo $field_generator->end_row();

     echo $field_generator->start_row('prevent-alert');
     echo $field_generator->generate('label', 'Message:', 'optin-form', 'prevent-alert-message', '', '', '', true);
     echo $field_generator->generate('input', '', 'optin-form', 'prevent-alert-message', '', '', true, false, 'Example: Are you sure you want to leave without subscribing?', '', array('default' => 'Are you sure you want to leave? You will miss out on this great deal!', 'style' => 'width: 100%;'));
     echo $field_generator->end_row();

     echo $field_generator->start_row();
     echo $field_generator->generate('label', 'Custom HTML Head Code:', 'optin-settings', 'head-code', '', '', true, true, '','');
     echo $field_generator->generate('textarea', '', 'optin-settings', 'head-code', '', '', true, false, 'This code gets inserted at the end of the HTML\'s &lt;head&gt; tag so you can place any custom CSS or JS in it. Don\'t forget &lt;script&gt; or &lt;style&gt; tags.', '');
     echo $field_generator->end_row();

     wf_field_generator::save_button();
   } // form
 } // wf_optin_ninja_optin_general_settings