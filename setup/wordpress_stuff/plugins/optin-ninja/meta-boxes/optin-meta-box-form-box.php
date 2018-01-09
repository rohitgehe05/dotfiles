<?php
/*
 * OptIn Ninja
 * (c) Web factory Ltd, 2016
 */

 class wf_optin_ninja_form_box extends wf_optin_ninja {
   public static function content() {
     global $wpdb, $post;
     $options = get_option('wf-optin', array());

     $mail_list_service = array('local' => 'Local database');
     if (isset($options['mailchimp']['status']) && $options['mailchimp']['status'] == 1) {
       $mail_list_service['mail-chimp'] = 'MailChimp';
     }
     if (isset($options['getresponse']['status']) && $options['getresponse']['status'] == 1) {
       $mail_list_service['getresponse'] = 'GetResponse';
     }
     if (isset($options['aweber']['status']) && $options['aweber']['status'] == 1) {
       $mail_list_service['aweber'] = 'Aweber';
     }
     if (isset($options['madmimi']['status']) && $options['madmimi']['status'] == 1) {
       $mail_list_service['madmimi'] = 'Mad Mimi';
     }
     if (isset($options['activecampaign']['status']) && $options['activecampaign']['status'] == 1) {
       $mail_list_service['activecampaign'] = 'ActiveCampaign';
     }
     if (isset($options['cm']['status']) && $options['cm']['status'] == 1 && $options['cm']['client-id']) {
       $mail_list_service['campaignmonitor'] = 'Campaign Monitor';
     }
     if (isset($options['emailoctopus']['status']) && $options['emailoctopus']['status'] == 1) {
       $mail_list_service['emailoctopus'] = 'EmailOctopus';
     }
     $mail_list_service['facebook'] = 'Facebook';
     $mail_list_service['customurl'] = 'Custom HTML Form';

     $form_fields = array('name-email' => 'Name and email', 'email' => 'Email Only');

     $aweber_list = array();
     $mc_list = array();
     $getresponse_list = array();
     $madmimi_list = array();

     $meta = get_post_meta($post->ID, 'wf_optin_meta', true);
     if (!$meta) {
       $meta = array();
     }
     $options = get_option('wf-optin');
     $lists = get_option('wf-optin-lists', array());

     $getresponse_l = false;
     $mail_chimp_l = false;
     $aweber_l = false;
     $redirect_l = false;
     $alert_l = false;
     $prevent_l = false;
     $facebook_l = false;
     $cm_l = false;
     $eo_l = false;

     if (!isset($meta['optin-form']['mail-listing-service'])) {
       $meta['optin-form']['mail-listing-service'] = '';
     }
     if ($meta['optin-form']['mail-listing-service'] == 'mail-chimp') {
       $mail_chimp_l = true;
     } else if ($meta['optin-form']['mail-listing-service'] == 'getresponse') {
       $getresponse_l = true;
     } else if ($meta['optin-form']['mail-listing-service'] == 'aweber') {
       $aweber_l = true;
     } else if ($meta['optin-form']['mail-listing-service'] == 'facebook') {
       $facebook_l = true;
     } else if ($meta['optin-form']['mail-listing-service'] == 'campaignmonitor') {
       $cm_l = true;
     } else if ($meta['optin-form']['mail-listing-service'] == 'emailoctopus') {
       $eo_l = true;
     }

     if (isset($meta['optin-form']['after-subscribe-action']) && $meta['optin-form']['after-subscribe-action'] == 'redirect') {
       $redirect_l = true;
     } else if(isset($meta['optin-form']['after-subscribe-action']) && $meta['optin-form']['after-subscribe-action'] == 'stay-alert') {
       $alert_l = true;
     } else {
       $redirect_l = true;
     }

     if (isset($meta['optin-form']['prevent-from-leaving']) && $meta['optin-form']['prevent-from-leaving'] == 'yes') {
       $prevent_l = true;
     }


     $field_generator = new wf_field_generator();

     $fields = '';
     $fields .= $field_generator->start_row();
     $fields .= $field_generator->generate('label', 'Form Fields:', 'optin-form', 'form-fields', '', '', '', true, '', '', array('columns' => 'col-12'));
     $fields .= $field_generator->generate('dropdown', '', 'optin-form', 'form-fields', $form_fields, '', '', '', 'If you need additional fields or more configuration options have a look at the <a href="http://optin-ninja.webfactoryltd.com/custom-form-fields-addon/" target="_blank">Custom Form Fields add-on</a>.<br>Its drag &amp; drop interface enables you to add as many input fields as needed and configure them to your specs.', '', array('columns' => 'col-12'));
     $fields .= $field_generator->end_row();

     $fields .= $field_generator->start_row('placeholder-name-row');
     $fields .= $field_generator->generate('label', 'Placeholder for Name Field:', 'optin-form', 'placeholder-name', '', '', '', true, '', '', array('columns' => 'col-12'));
     $fields .= $field_generator->generate('input', '', 'optin-form', 'placeholder-name', '', '', '', '', '', '', array('columns' => 'col-12', 'default' => 'Your name', 'style' => 'width: 100%;'));
     $fields .= $field_generator->end_row();

     $fields .= $field_generator->start_row();
     $fields .= $field_generator->generate('label', 'Placeholder for Email Field:', 'optin-form', 'placeholder-email', '', '', '', true, '', '', array('columns' => 'col-12'));
     $fields .= $field_generator->generate('input', '', 'optin-form', 'placeholder-email', '', '', '', '', '', '', array('columns' => 'col-12', 'default' => 'Your email', 'style' => 'width: 100%;'));
     $fields .= $field_generator->end_row();

     echo apply_filters('optin_ninja_form_fields_metabox', $fields);

     echo $field_generator->start_row();
     echo $field_generator->generate('label', 'Autoresponder Service:', 'optin-form', 'mail-listing-service', '', '', '', true, '', '', array('columns' => 'col-12'));
     echo $field_generator->generate('dropdown', '', 'optin-form', 'mail-listing-service', $mail_list_service, '', '', '', 'Autoresponder services can be configured in <a href="admin.php?page=wf-optin-ninja-settings">settings</a> If you are using Facbook or local storage you can configure a <a href="#optin-ninja-autoresponder">welcome email</a>.', '', array('columns' => 'col-12'));
     echo $field_generator->end_row();

     echo $field_generator->start_row('mail-chimp', $mail_chimp_l);
     echo $field_generator->generate('label', 'MailChimp List:', 'optin-form', 'mail-chimp-list', '', '', '', true, '', '', array('columns' => 'col-12'));
     echo $field_generator->generate('dropdown', '', 'optin-form', 'mail-chimp-list', @$lists['mailchimp'], '', '', '', '', '', array('columns' => 'col-12'));
     echo '<p><a class="button refresh-lists" href="admin.php?action=optin_refresh_lists&amp;post=' . @$post->ID . '">Refresh lists</a></p>';
     echo $field_generator->end_row();

     echo $field_generator->start_row('getresponse', $getresponse_l);
     echo $field_generator->generate('label', 'GetResponse List:', 'optin-form', 'getresponse-list', '', '', '', true, '', '', array('columns' => 'col-12'));
     echo $field_generator->generate('dropdown', '', 'optin-form', 'getresponse-list', @$lists['getresponse'], '', '', '', '', '', array('columns' => 'col-12'));
     echo '<p><a class="button refresh-lists" href="admin.php?action=optin_refresh_lists&amp;post=' . @$post->ID . '">Refresh lists</a></p>';
     echo $field_generator->end_row();

     echo $field_generator->start_row('aweber', $aweber_l);
     echo $field_generator->generate('label', 'Aweber List:', 'optin-form', 'aweber-list',  '', '', '', true, '', '', array('columns' => 'col-12'));
     echo $field_generator->generate('dropdown', '', 'optin-form', 'aweber-list', @$lists['aweber'], '', '', '', '', '', array('columns' => 'col-12'));
     echo '<p><a class="button refresh-lists" href="admin.php?action=optin_refresh_lists&amp;post=' . @$post->ID . '">Refresh lists</a></p>';
     echo $field_generator->end_row();

     echo $field_generator->start_row('madmimi');
     echo $field_generator->generate('label', 'Mad Mimi List:', 'optin-form', 'madmimi-list',  '', '', '', true, '', '', array('columns' => 'col-12'));
     echo $field_generator->generate('dropdown', '', 'optin-form', 'madmimi-list', @$lists['madmimi'], '', '', '', '', '', array('columns' => 'col-12'));
     echo '<p><a class="button refresh-lists" href="admin.php?action=optin_refresh_lists&amp;post=' . @$post->ID . '">Refresh lists</a></p>';
     echo $field_generator->end_row();

     echo $field_generator->start_row('activecampaign');
     echo $field_generator->generate('label', 'ActiveCampaign List:', 'optin-form', 'activecampaign-list',  '', '', '', true, '', '', array('columns' => 'col-12'));
     echo $field_generator->generate('dropdown', '', 'optin-form', 'activecampaign-list', @$lists['activecampaign'], '', '', '', '', '', array('columns' => 'col-12'));
     echo '<p><a class="button refresh-lists" href="admin.php?action=optin_refresh_lists&amp;post=' . @$post->ID . '">Refresh lists</a></p>';
     echo $field_generator->end_row();

     echo $field_generator->start_row('campaignmonitor', $cm_l);
     echo $field_generator->generate('label', 'Campaign Monitor List:', 'optin-form', 'campaignmonitor-list', '', '', '', true, '', '', array('columns' => 'col-12'));
     echo $field_generator->generate('dropdown', '', 'optin-form', 'campaignmonitor-list', @$lists['cm'], '', '', '', '', '', array('columns' => 'col-12'));
     echo '<p><a class="button refresh-lists" href="admin.php?action=optin_refresh_lists&amp;post=' . @$post->ID . '">Refresh lists</a></p>';
     echo $field_generator->end_row();
     
     echo $field_generator->start_row('emailoctopus', $eo_l);
     echo $field_generator->generate('label', 'EmailOctopus List:', 'optin-form', 'emailoctopus-list', '', '', '', true, '', '', array('columns' => 'col-12'));
     echo $field_generator->generate('dropdown', '', 'optin-form', 'emailoctopus-list', @$lists['emailoctopus'], '', '', '', '', '', array('columns' => 'col-12'));
     echo '<p><a class="button refresh-lists" href="admin.php?action=optin_refresh_lists&amp;post=' . @$post->ID . '">Refresh lists</a></p>';
     echo $field_generator->end_row();

     echo $field_generator->start_row('facebook', $facebook_l);
     echo $field_generator->generate('label', 'Facebook App ID:', 'optin-form', 'facebook-app-id', '', '', '', true, '', '', array('columns' => 'col-12'));
     echo $field_generator->generate('input', '', 'optin-form', 'facebook-app-id', '', '', '', '', 'Open <a href="https://developers.facebook.com/apps?ref=mb" target="_blank">Facebook developer Apps Manager</a> and copy/paste the App ID. Don\'t forget to configure App\'s domain.', '', array('columns' => 'col-12'));
     echo $field_generator->end_row();

     echo $field_generator->start_row('customurl');
     echo $field_generator->generate('label', 'Form HTML code:', 'optin-form', 'custom-url-html', '', '', '', true, '', '', array('columns' => 'col-12'));
     echo $field_generator->generate('textarea', '', 'optin-form', 'custom-url-html', '', '', '', '', 'Paste complete form HTML code in this field and click "Parse HTML" button to automatically populate the URL and custom fields values.<br><a href="#" class="button button-secondary" id="customurl-parse-html">Parse HTML</a>', '', array('columns' => 'col-12', 'style' => 'width: 100%;'));
     echo $field_generator->end_row();

     echo $field_generator->start_row('customurl');
     echo $field_generator->generate('label', 'Form Action URL:', 'optin-form', 'custom-url', '', '', '', true, '', '', array('columns' => 'col-12'));
     echo $field_generator->generate('input', '', 'optin-form', 'custom-url', '', '', '', '', 'Enter the full URL (starting with http://) where you want the form data submitted to.', '', array('columns' => 'col-12', 'style' => 'width: 100%;'));
     echo $field_generator->end_row();

     echo $field_generator->start_row('customurl');
     echo $field_generator->generate('label', 'Form Email Field:', 'optin-form', 'custom-email-field', '', '', '', true, '', '', array('columns' => 'col-12'));
     echo $field_generator->generate('input', '', 'optin-form', 'custom-email-field', '', '', '', '', 'Name of the form field which holds the email address. In most cases it is "email" but please verify in form\'s HTML code.', '', array('columns' => 'col-12'));
     echo $field_generator->end_row();

     echo $field_generator->start_row('customurl');
     echo $field_generator->generate('label', 'Additional Form Fields:', 'optin-form', 'custom-url-extra', '', '', '', true, '', '', array('columns' => 'col-12'));
     echo $field_generator->generate('input', '', 'optin-form', 'custom-url-extra', '', '', '', '', 'Name and email fields will be sent to the above URL via POST. If you need additional predefined fields enter them in a URL notation, ie: field1=val1&field2=val.', '', array('columns' => 'col-12', 'style' => 'width: 100%;'));
     echo $field_generator->end_row();

     echo $field_generator->start_row();
     echo $field_generator->generate('label', 'After Subscribe Action:', 'optin-form', 'after-subscribe-action', '', '', '', true, '', '', array('columns' => 'col-12'));
     echo $field_generator->generate('dropdown', '', 'optin-form', 'after-subscribe-action', array('redirect' => 'Redirect to URL', 'stay-alert' => 'Stay on page with alert'), '', '', '', 'What should happen after user subscribes?', '', array('columns' => 'col-12', 'default' => 'stay-alert'));
     echo $field_generator->end_row();

     echo $field_generator->start_row('redirect', $redirect_l);
     echo $field_generator->generate('label', 'Redirect URL:', 'optin-form', 'after-subscribe-url', '', '', '', true, '', '', array('columns' => 'col-12'));
     echo $field_generator->generate('input', '', 'optin-form', 'after-subscribe-url', '', '', '', '', 'Please write full URL, starting with http://.', '', array('columns' => 'col-12'));
     echo $field_generator->end_row();

     echo $field_generator->start_row('stay-alert', $alert_l);
     echo $field_generator->generate('label', 'Alert Message:', 'optin-form', 'after-subscribe-alert-message', '', '', '', true, '', '', array('columns' => 'col-12'));
     echo $field_generator->generate('input', '', 'optin-form', 'after-subscribe-alert-message', '', '', '', '', 'Message is shown as a standard JavaScript alert.', '', array('columns' => 'col-12', 'default' => 'Thank you for subscribing!', 'style' => 'width: 100%;'));
     echo $field_generator->end_row();
     
     echo $field_generator->start_row('error-log');
     echo $field_generator->generate('label', 'Autoresponder Service Error Log:', 'optin-form', 'error-log', '', '', '', true, '', '', array('columns' => 'col-12'));
     echo $field_generator->generate('textarea', '', 'optin-form', 'error-log', '', '', '', '', 'Last error from the autoresponder service, including some additional info. Please do not share this data with anyone except our support, if asked to.', '', array('columns' => 'col-12', 'style' => 'width: 100%;'));
     echo $field_generator->end_row();

     wf_field_generator::save_button();
   } // content

   public static function refresh_lists($redirect = true) {
     $options = get_option('wf-optin');
     $lists = array();

     require_once WF_OPT_PLUGIN_DIR . 'api/mailchimp.api.php';
     require_once WF_OPT_PLUGIN_DIR . 'api/aweber_api/aweber_api.php';
     require_once WF_OPT_PLUGIN_DIR . 'api/getresponse.api.php';
     require_once WF_OPT_PLUGIN_DIR . 'api/MadMimi.class.php';
     require_once WF_OPT_PLUGIN_DIR . 'api/activecampaign_api/ActiveCampaign.class.php';
     require_once WF_OPT_PLUGIN_DIR . '/api/campaignmonitor_api/csrest_clients.php';

     // Aweber List Generator
     $lists['aweber'] = array();
     if ($options['aweber']['status'] == 1) {
       try {
         $aweber = new AWeberAPI($options['aweber']['consumer-key'], $options['aweber']['consumer-secret']);
         $account = $aweber->getAccount($options['aweber']['access-key'], $options['aweber']['access-secret']);
         foreach ($account->lists as $list) {
           $lists['aweber'][$list->id] = $list->name;
         }
       } catch(AWeberAPIException $exc) {
         // Nothing happens
       }
     }

     // Mail Chimp List Generator
     $lists['mailchimp'] = array();
     if ($options['mailchimp']['status'] == 1) {
       $api = new MCAPI($options['mail-chimp-api-key']);
       $retval = $api->lists();
       if ($api->errorCode) {
       } else {
         foreach ($retval['data'] as $list) {
           $lists['mailchimp'][$list['id']] = $list['name'];
         }
       }
     }

     // Mad Mimi lists
     $lists['madmimi'] = array();
     if ($options['madmimi']['status'] == 1) {
       $api = new MadMimi($options['madmimi-username'], $options['madmimi-api']);
       $tmp = $api->Lists();
       if ($tmp != 'Unable to authenticate') {
           $tmp = new SimpleXMLElement($tmp);
           foreach ($tmp as $list) {
            $lists['madmimi'][(string) $list->attributes()->id] = (string) $list->attributes()->name;
           }
        }
     }

     // ac lists
     $lists['activecampaign'] = array();
     if ($options['activecampaign']['status'] == 1) {
       $ac = new ActiveCampaign($options['activecampaign-url'], $options['activecampaign-api']);
       $ac = $ac->api("list/list?ids=all");
       if ($ac) {
         foreach ($ac as $tmp) {
           if (!is_object($tmp)) {
             continue;
           }
           $lists['activecampaign'][(string) $tmp->id] = $tmp->name;
         }
       }
     }

     // Get Response List Generator
     $lists['getresponse'] = array();
     if (isset($options['getresponse']['status']) && $options['getresponse']['status'] == '1') {
       $api = new GetResponse($options['getresponse-api-key']);
       if ($api->ping()) {
         $result = (array) $api->getCampaigns();
         if ($result) {
           foreach ($result as $id => $data) {
             $lists['getresponse'][$id] = $data->name;
           }
         }
       }
     } // getresponse

     // CM lists
     $lists['cm'] = array();
     if ($options['cm']['status'] == 1) {
       $api = new CS_REST_Clients($options['cm']['client-id'], array('api_key' => $options['cm-api-key']));
       $result = $api->get_lists();
       if ($result->was_successful()) {
         foreach ($result->response as $list) {
           $lists['cm'][$list->ListID] = $list->Name;
         }
       }
     } // CM
     
     // emailoctopus lists
     $lists['emailoctopus'] = array();
     if ($options['emailoctopus']['status'] == 1) {
       $tmp = wp_remote_get('https://emailoctopus.com/api/1.2/lists?api_key=' . $options['emailoctopus-api-key']);
       if (!is_wp_error($tmp)) {
         $tmp = wp_remote_retrieve_body($tmp);
         $tmp = json_decode($tmp);
         if (!empty($tmp->data)) {
           foreach($tmp->data as $list) {
             $lists['emailoctopus'][$list->id] = $list->name;
           } // foreach
         }
       }
     } // emailoctopus
     
     update_option('wf-optin-lists', $lists);

     if ($redirect !== false) {
       if (isset($_GET['post']) && is_numeric($_GET['post'])) {
         header('location: post.php?post=' . $_GET['post'] . '&action=edit');
       } else {
         header('location: post-new.php?post_type=optin-pages');
       }
     }
   }
 } // wf_optin_ninja_form_box