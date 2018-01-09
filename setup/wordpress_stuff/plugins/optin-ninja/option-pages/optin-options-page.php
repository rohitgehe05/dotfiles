<?php
/**
 * OptIn Ninja
 * (c) Web factory Ltd, 2016
 * Options Page
 */

class wf_optin_options extends wf_optin_ninja {
   static function save_options() {
     // Include API files
     require_once WF_OPT_PLUGIN_DIR . '/api/aweber_api/aweber_api.php';
     require_once WF_OPT_PLUGIN_DIR . '/api/mailchimp.api.php';
     require_once WF_OPT_PLUGIN_DIR . '/api/getresponse.api.php';
     require_once WF_OPT_PLUGIN_DIR . '/api/MadMimi.class.php';
     require_once WF_OPT_PLUGIN_DIR . '/api/activecampaign_api/ActiveCampaign.class.php';
     require_once WF_OPT_PLUGIN_DIR . '/api/campaignmonitor_api/csrest_general.php';
     require_once WF_OPT_PLUGIN_DIR . '/api/pushover.php';

     if (isset($_POST['wf_optin'])) {
       $posted = $_POST['wf_optin'];
       $options = get_option('wf-optin', array());

       $defaults = array('mail-chimp-api-key' => '', 'emailoctopus-api-key' => '', 'getresponse-api-key' => '', 'aweber-auth-code' => '', 'local_save' => 0, 'madmimi-api' => '', 'madmimi-username' => '', 'activecampaign-url' => '', 'activecampaign-api' => '', 'cm-api' => '', 'cm-api-key' => '', 'pushover-key' => '', 'pushover-users' => '', 'disable_popup' => 0);
       $defaults['mailchimp']['status'] = 0;
       $defaults['aweber']['status'] = 0;
       $defaults['getresponse']['status'] = 0;
       $defaults['madmimi']['status'] = 0;
       $defaults['activecampaign']['status'] = 0;
       $defaults['cm']['status'] = 0;
       $defaults['pushover']['status'] = 0;
       $defaults['emailoctopus']['status'] = 0;
       $options = array_merge($defaults, $options);

       if ($options['aweber-auth-code'] != $posted['aweber-auth-code']) {
         // AweberAPI
         try {
           $auth = AWeberAPI::getDataFromAweberID($posted['aweber-auth-code']);
           if ($auth) {
             list($consumerKey, $consumerSecret, $accessKey, $accessSecret) = $auth;
             // Save Generated Keys
             $options['aweber-auth-code'] = $posted['aweber-auth-code'];
             $options['aweber']['consumer-key'] = $consumerKey;
             $options['aweber']['consumer-secret'] = $consumerSecret;
             $options['aweber']['access-key'] = $accessKey;
             $options['aweber']['access-secret'] = $accessSecret;
             $options['aweber']['status'] = '1';
           } else {
             $options['aweber']['status'] = '0';
             $options['aweber-auth-code'] = $posted['aweber-auth-code'];
           }
         } catch(AWeberAPIException $exc) {
           $options['aweber']['status'] = '0';
           $options['aweber-auth-code'] = $posted['aweber-auth-code'];
         }
       }

       if ($options['mail-chimp-api-key'] != $posted['mail-chimp-api-key']) {
         // Regenerate other key
         $options['mail-chimp-api-key'] = $posted['mail-chimp-api-key'];
         $api = new MCAPI($posted['mail-chimp-api-key']);
         $retval = $api->lists();
         $options['mailchimp']['status'] = '1';
         if ($api->errorCode) {
           $options['mailchimp']['status'] = '0';
         }
       }
       
       // emailoctopus
       if ($options['emailoctopus-api-key'] != $posted['emailoctopus-api-key']) {
         $options['emailoctopus-api-key'] = $posted['emailoctopus-api-key'];

         $tmp = wp_remote_get('https://emailoctopus.com/api/1.2/lists?api_key=' . $posted['emailoctopus-api-key']);
         if (!is_wp_error($tmp)) {
           $tmp = wp_remote_retrieve_body($tmp);
           $tmp = json_decode($tmp);
           if (!empty($tmp->error)) {
             $options['emailoctopus']['status'] = '0';
           } else {
             $options['emailoctopus']['status'] = '1';
           }
         } else {
           $options['emailoctopus']['status'] = '0';
         }
       } // eo

       // madmimi
       if ($options['madmimi-api'] != $posted['madmimi-api'] || $options['madmimi-username'] != $posted['madmimi-username']) {
         $api = new MadMimi($posted['madmimi-username'], $posted['madmimi-api']);
         if ($api->lists() == 'Unable to authenticate' || $api->lists() == false) {
           $options['madmimi']['status'] = '0';
         } else {
           $options['madmimi']['status'] = '1';
         }
         $options['madmimi-api'] = $posted['madmimi-api'];
         $options['madmimi-username'] = $posted['madmimi-username'];
       }

       // ac
       if ($options['activecampaign-api'] != $posted['activecampaign-api'] || $options['activecampaign-url'] != $posted['activecampaign-url']) {
         $ac = new ActiveCampaign($posted['activecampaign-url'], $posted['activecampaign-api']);
         if (!(int)$ac->credentials_test()) {
           $options['activecampaign']['status'] = '0';
         } else {
           $options['activecampaign']['status'] = '1';
         }

         $options['activecampaign-api'] = $posted['activecampaign-api'];
         $options['activecampaign-url'] = $posted['activecampaign-url'];
       }

       // getresponse
       if ($options['getresponse-api-key'] != $posted['getresponse-api-key']) {
         $options['getresponse-api-key'] = $posted['getresponse-api-key'];
         $api = new GetResponse($options['getresponse-api-key']);
         $result = $api->ping();
         if ($result) {
           $options['getresponse']['status'] = '1';
         } else {
           $options['getresponse']['status'] = '0';
         }
       }

       // CM
       if ($options['cm-api-key'] != $posted['cm-api-key']) {
         $options['cm-api-key'] = $posted['cm-api-key'];
         $api = new CS_REST_General(array('api_key' => $posted['cm-api-key']));
         $result = $api->get_clients();

         if($result->was_successful()) {
           $options['cm']['status'] = '1';
           $options['cm']['client-id'] = $result->response[0]->ClientID;
         } else {
           $options['cm']['status'] = '0';
         }
       } // cm

       // PO
       if ($options['pushover-key'] != $posted['pushover-key'] || $options['pushover-users'] != $posted['pushover-users']) {
         $options['pushover-key'] = $posted['pushover-key'];
         $options['pushover-users'] = $posted['pushover-users'];
         $options['pushover']['status'] = '0';

         $push = new Pushover();
         $push->setToken($options['pushover-key']);
         $push->setTitle('test');
         $push->setMessage('test');
         $push->setDebug(true);
         $push->setUser('test');
         $res = $push->send();

         if($res['output']->token == 'invalid') {
           $options['pushover']['status'] = '0';
         } else {
           $options['pushover']['status'] = '1';
         }
       } // cm

       $options['local_save'] = (int) @$posted['local_save'];
       $options['disable_popup'] = (int) @$posted['disable_popup'];

       update_option('wf-optin', $options);
       add_settings_error('optin', 'optin-saved', 'Settings saved.', 'updated');

       wf_optin_ninja_form_box::refresh_lists(false);
     }

   } // save_options

   // import +300 textures into media library
   public static function import_textures_ajax() {
     wf_optin_ninja::import_xml('background-patterns.xml');

     die('1');
   } // import_textures_ajax

   // import +30 backgrounds into media library
   public static function import_backgrounds_ajax() {
     wf_optin_ninja::import_xml('background-images.xml');

     die('1');
   } // import_backgrounds_ajax

   // complete options page markup
   static function content() {
     self::save_options();

     $defaults = array('mail-chimp-api-key' => '', 'getresponse-api-key' => '', 'emailoctopus-api-key' => '', 'aweber-auth-code' => '', 'local_save' => 0, 'madmimi-api' => '', 'madmimi-username' => '', 'disable_popup' => 0, 'activecampaign-url' => '', 'activecampaign-api' => '');
     $defaults['mailchimp']['status'] = 0;
     $defaults['aweber']['status'] = 0;
     $defaults['getresponse']['status'] = 0;
     $defaults['madmimi']['status'] = 0;
     $defaults['activecampaign']['status'] = 0;
     $defaults['emailoctopus']['status'] = 0;
     $defaults['cm']['status'] = 0;

     $options = get_option('wf-optin', array());
     $options = array_merge($defaults, $options);
     $lists = get_option('wf-optin-lists', array());

     settings_errors();
     echo '<div class="wrap">';
     echo '<h2>OptIn Ninja Settings</h2><br>';

     $tabs = array('general' => 'General', 'miscellaneous' => 'Miscellaneous', 'mailchimp' => 'MailChimp', 'aweber' => 'Aweber', 'getresponse' => 'GetResponse', 'madmimi' => 'Mad Mimi', 'activecampaign' => 'ActiveCampaign', 'cm' => 'Campaign Monitor', 'eo' => 'EmailOctopus', 'pushover' => 'Pushover');

     echo '<div id="wf-optin-ninja-options-page-tabs">';
     echo '<h2 class="nav-tab-wrapper">';
     echo '<ul class="nav-tab-wrapper">';
     foreach( $tabs as $tab => $name ){
       echo "<li class='nav-tab'><a href='#$tab'>$name</a></li>";
     }
     echo '</ul>';
     echo '</h2>';

     echo '<form id="optin_options" action="admin.php?page=wf-optin-ninja-settings" method="post">';
     echo '<div class="container">';

     // general
     echo '<div id="general">';
     echo '<table class="form-table">';
     echo '<tbody>';
     echo '<tr>
           <th scope="row"><label for="disable_popup">Disable Popup Functionality</label></th>
           <td><input name="wf_optin[disable_popup]" type="checkbox" id="disable_popup" value="1"' . checked('1', $options['disable_popup'], false) . '/><span class="description">If you are not using the popup functionality at all you can disable it and prevent OptIn Ninja from including additional CSS/JS files on your site. Default: unchecked.</span></td>
           </tr>';

      echo '</tbody>';
     echo '</table>';
     echo '<p class="submit"><input type="submit" value="Save Settings" class="button button-primary" id="submit" name="submit"></p>';
     echo '</div>';

     // misc
     echo '<div id="miscellaneous">';
     echo '<table class="form-table">';
     echo '<tbody>';
     echo '<tr>
           <th class="top-align" scope="row">
             <a href="#" id="optin-import-textures" class="button action">Import background textures</a>
           </th>
           <td>
             <p class="description">356 background textures (12MB) will be imported into your WP media library. Please note that if you have numerous image sizes configure the total amount of disk space needed will be a lot more than 12MB.<br>
             Images are marked with "optin-texture" so you can easily find them among other media items. If you\'re having problems importing you can download the images <a href="http://optin-ninja.webfactoryltd.com/wp-content/uploads/2014/04/optin-ninja-textures.zip">here</a>.</p>
           </td>
           </tr>';
     echo '<tr>
           <th class="top-align" scope="row">
             <a href="#" id="optin-import-bg" class="button action">Import background images</a>
           </th>
           <td>
           <p class="description">35 beautiful HD background images (30MB) will be imported into your WP media library. Please note that if you have numerous image sizes configure the total amount of disk space needed will be a lot more than 30MB.<br>
           Images are marked with "optin-bg" so you can easily find them among other media items. If you\'re having problems importing you can download the images <a href="http://optin-ninja.webfactoryltd.com/wp-content/uploads/2014/04/optin-ninja-backgrounds.zip">here</a>.</p>
           </td>
           </tr>';
     echo '<tr>
           <th class="top-align" scope="row">
             <a href="#" id="optin-reset-stats" class="button action">Reset all statistics</a>
           </th>
           <td>
             <p class="description">This will erase all OptIn pages statistics (view, conversion, everything). There is no undo!</p>
           </td>
           </tr>';
     echo '<tr>
           <th class="top-align" scope="row">
             <a href="#" id="optin-delete-subs" class="button action">Delete all subscribers</a>
           </th>
           <td>
             <p class="description">This will erase all subscribers in the local database. It will not affect any autoresponder services. There is no undo!</p>
           </td>
           </tr>';
     echo '</tbody>';
     echo '</table>';
     echo '</div>';

     // MailChimp
     echo '<div id="mailchimp">';
     echo '<table class="form-table">';
     echo '<tbody>';
     echo '<tr>
           <th class="top-align" scope="row">
             <label class="optin-label top-label" for="mail-chimp-api-key">MailChimp API key:</label>
           </th>
           <td>
             <input type="text" class="regular-text code" value="' . $options['mail-chimp-api-key'] . '" name="wf_optin[mail-chimp-api-key]" id="mail-chimp-api-key" />
             <p class="description">Login to your <a href="http://www.mailchimp.com/" target="_blank">MailChimp account</a>, go to <i>account - extras - API</a>, generate a new API key and paste it here.</p>
           </td>
           </tr>';
     echo '<tr>';
     echo '<th class="top-align" scope="row">';
     echo '<label>API key status:</label>';
     echo '</th>';
     echo '<td>';

     // MC Status
     if ($options['mailchimp']['status'] == '1') {
       echo '<div class="dashicons dashicons-yes"></div> Valid API key.';
     } else {
       echo '<div class="dashicons dashicons-no"></div> Invalid MailChimp API key provided!';
     }

     echo '</td>';
     echo '</tr>';
     echo '<th class="top-align" scope="row">';
     echo '<label>Available lists:</label>';
     echo '</th>';
     echo '<td>';
     if (isset($lists['mailchimp']) && $lists['mailchimp'] && is_array($lists['mailchimp'])) {
       echo '<ul class="lists">';
       foreach ($lists['mailchimp'] as $tmp) {
         echo '<li>' . $tmp . '</li>';
       }
       echo '</ul>';
     } else {
       echo 'none';
     }
     echo '</td>';
     echo '</tr>';
     echo '</tbody>';
     echo '</table>';
     echo '<p class="submit"><input type="submit" value="Save Settings" class="button button-primary" id="submit" name="submit"></p>';
     echo '</div>';

     // Aweber
     echo '<div id="aweber">';
     echo '<table class="form-table">';
     echo '<tbody>';
     echo '<tr>';
     echo '<td colspan="2">Please authorize your account with OptIn Ninja\'s Aweber application. <a href="https://auth.aweber.com/1.0/oauth/authorize_app/' . WF_OPT_APP . '" class="button" target="_blank" style="margin-top: -5px;">Open the authorisation page</a>, login with your account and click "Allow Access". Then copy/paste the generated authorisation code in the field below.';
     echo '</td>';
     echo '</tr>';
     echo '<tr>
           <th class="top-align" scope="row">
             <label for="aweber-auth-code" class="optin-label top-label">Aweber authorization code:</label>
           </th>
           <td>
             <input type="text" class="regular-text code" value="' . $options['aweber-auth-code'] . '" name="wf_optin[aweber-auth-code]" id="aweber-auth-code"/>
           </td>
           </tr>';
     echo '<tr>';
     echo '<th class="top-align" scope="row">';
     echo '<label>Authorization code status:</label>';
     echo '</th>';
     echo '<td>';

     // Aweber Status
     if ($options['aweber']['status'] == '1') {
       echo '<div class="dashicons dashicons-yes"></div> Valid authorization code!';
     } else {
       echo '<div class="dashicons dashicons-no"></div> Invalid authorization code. Please generate and save a new one.';
     }

     echo '</td>';
     echo '</tr>';
     echo '<th class="top-align" scope="row">';
     echo '<label>Available lists:</label>';
     echo '</th>';
     echo '<td>';
     if (isset($lists['aweber']) && $lists['aweber'] && is_array($lists['aweber'])) {
       echo '<ul class="lists">';
       foreach ($lists['aweber'] as $tmp) {
         echo '<li>' . $tmp . '</li>';
       }
       echo '</ul>';
     } else {
       echo 'none';
     }
     echo '</td>';
     echo '</tr>';
     echo '</tbody>';
     echo '</table>';
     echo '<p class="submit"><input type="submit" value="Save Settings" class="button button-primary" id="submit" name="submit"></p>';
     echo '</div>';

     // Get Response
     echo '<div id="getresponse">';
     echo '<table class="form-table">';
     echo '<tbody>';
     echo '<tr>
           <th class="top-align" scope="row">
             <label for="getresponse-api-key" class="optin-label top-label">GetResponse API key:</label>
           </th>
           <td>
             <input type="text" class="regular-text code" value="' . $options['getresponse-api-key'] . '" name="wf_optin[getresponse-api-key]" id="getresponse-api-key"/>
             <p class="description">Login to your <a href="https://app.getresponse.com/main.html" target="_blank">GetResponse account</a>, go to <i>account details - GetResponse API</i>, enable the secret API key and paste it here.</p>
           </td>
           </tr>';
     echo '<tr>';
     echo '<th scope="row">';
     echo '<label>API key status:</label>';
     echo '</th>';
     echo '<td>';

     // get response Status
     if ($options['getresponse']['status'] == '1') {
       echo '<div class="dashicons dashicons-yes"></div> Valid API key.';
     } else {
       echo '<div class="dashicons dashicons-no"></div> Invalid GetResponse API key provided!';
     }

     echo '</td>';
     echo '</tr>';
     echo '<th class="top-align" scope="row">';
     echo '<label>Available lists:</label>';
     echo '</th>';
     echo '<td>';
     if (isset($lists['getresponse']) && $lists['getresponse'] && is_array($lists['getresponse'])) {
       echo '<ul class="lists">';
       foreach ($lists['getresponse'] as $tmp) {
         echo '<li>' . $tmp . '</li>';
       }
       echo '</ul>';
     } else {
       echo 'none';
     }
     echo '</td>';
     echo '</tr>';
     echo '</tbody>';
     echo '</table>';
     echo '<p class="submit"><input type="submit" value="Save Settings" class="button button-primary" id="submit" name="submit"></p>';
     echo '</div>';
     
     // campaign monitor
     echo '<div id="eo">';
     echo '<table class="form-table">';
     echo '<tbody>';
     echo '<tr>
           <th class="top-align" scope="row">
             <label for="emailoctopus-api-key" class="optin-label top-label">EmailOctopus API key:</label>
           </th>
           <td>
             <input type="text" class="regular-text code" value="' . @$options['emailoctopus-api-key'] . '" name="wf_optin[emailoctopus-api-key]" id="emailoctopus-api-key"/>
             <p class="description">Login to your <a href="https://emailoctopus.com/dashboard/" target="_blank">EmailOctopus account</a>, go to <i>API</i>, click "Create a key", copy and paste the key here.</p>
           </td>
           </tr>';
     echo '<tr>';
     echo '<th scope="row">';
     echo '<label>API key status:</label>';
     echo '</th>';
     echo '<td>';

     // eo Status
     if ($options['emailoctopus']['status'] == '1') {
       echo '<div class="dashicons dashicons-yes"></div> Valid API key.';
     } else {
       echo '<div class="dashicons dashicons-no"></div> Invalid Emailoctopus API key provided!';
     }

     echo '</td>';
     echo '</tr>';
     echo '<th class="top-align" scope="row">';
     echo '<label>Available lists:</label>';
     echo '</th>';
     echo '<td>';
     if (isset($lists['emailoctopus']) && $lists['emailoctopus'] && is_array($lists['emailoctopus'])) {
       echo '<ul class="lists">';
       foreach ($lists['emailoctopus'] as $tmp) {
         echo '<li>' . $tmp . '</li>';
       }
       echo '</ul>';
     } else {
       echo 'none';
     }
     echo '</td>';
     echo '</tr>';
     echo '</tbody>';
     echo '</table>';
     echo '<p class="submit"><input type="submit" value="Save Settings" class="button button-primary" id="submit" name="submit"></p>';
     echo '</div>';
     // emailoctopus

     // campaign monitor
     echo '<div id="cm">';
     echo '<table class="form-table">';
     echo '<tbody>';
     echo '<tr>
           <th class="top-align" scope="row">
             <label for="cm-api-key" class="optin-label top-label">Campaign Monitor API key:</label>
           </th>
           <td>
             <input type="text" class="regular-text code" value="' . @$options['cm-api-key'] . '" name="wf_optin[cm-api-key]" id="cm-api-key"/>
             <p class="description">Login to your Campaign Monitor (Createsend) account, go to <i>account settings</i>, click "Show API key", copy and paste it here.</p>
           </td>
           </tr>';
     echo '<tr>';
     echo '<th scope="row">';
     echo '<label>API key status:</label>';
     echo '</th>';
     echo '<td>';

     // CM Status
     if ($options['cm']['status'] == '1') {
       echo '<div class="dashicons dashicons-yes"></div> Valid API key.';
     } else {
       echo '<div class="dashicons dashicons-no"></div> Invalid Campaign Monitor API key provided!';
     }

     echo '</td>';
     echo '</tr>';
     echo '<th class="top-align" scope="row">';
     echo '<label>Available lists:</label>';
     echo '</th>';
     echo '<td>';
     if (isset($lists['cm']) && $lists['cm'] && is_array($lists['cm'])) {
       echo '<ul class="lists">';
       foreach ($lists['cm'] as $tmp) {
         echo '<li>' . $tmp . '</li>';
       }
       echo '</ul>';
     } else {
       echo 'none';
     }
     echo '</td>';
     echo '</tr>';
     echo '</tbody>';
     echo '</table>';
     echo '<p class="submit"><input type="submit" value="Save Settings" class="button button-primary" id="submit" name="submit"></p>';
     echo '</div>';
     
     // mad mimi
     echo '<div id="madmimi">';
     echo '<table class="form-table">';
     echo '<tbody>';
     echo '<tr>
           <th class="top-align" scope="row">
             <label class="optin-label top-label" for="madmimi-username">Mad Mimi email:</label>
           </th>
           <td>
             <input type="text" class="regular-text code" value="' . $options['madmimi-username'] . '" name="wf_optin[madmimi-username]" id="madmimi-username" />
           </td>
           </tr>';
     echo '<tr>
           <th class="top-align" scope="row">
             <label class="optin-label top-label" for="madmimi-api">Mad Mimi API key:</label>
           </th>
           <td>
             <input type="text" class="regular-text code" value="' . $options['madmimi-api'] . '" name="wf_optin[madmimi-api]" id="madmimi-api" />
             <p class="description">Login to your <a href="https://madmimi.com/user/edit?account_info_tabs=account_info_authorizations" target="_blank">Mad Mimi account</a>, go to <i>account</a>, find the API key in the right sidebar and copy it here.</p>
           </td>
           </tr>';
     echo '<tr>';
     echo '<th class="top-align" scope="row">';
     echo '<label>API key status:</label>';
     echo '</th>';
     echo '<td>';

     // mm Status
     if ($options['madmimi']['status'] == '1') {
       echo '<div class="dashicons dashicons-yes"></div> Valid API key.';
     } else {
       echo '<div class="dashicons dashicons-no"></div> Invalid Mad Mimi API key provided!';
     }

     echo '</td>';
     echo '</tr>';
     echo '<th class="top-align" scope="row">';
     echo '<label>Available lists:</label>';
     echo '</th>';
     echo '<td>';
     if (isset($lists['madmimi']) && $lists['madmimi'] && is_array($lists['madmimi'])) {
       echo '<ul class="lists">';
       foreach ($lists['madmimi'] as $tmp) {
         echo '<li>' . $tmp . '</li>';
       }
       echo '</ul>';
     } else {
       echo 'none';
     }
     echo '</td>';
     echo '</tr>';
     echo '</tbody>';
     echo '</table>';
     echo '<p class="submit"><input type="submit" value="Save Settings" class="button button-primary" id="submit" name="submit"></p>';
     echo '</div>';

     // ac
     echo '<div id="activecampaign">';
     echo '<table class="form-table">';
     echo '<tbody>';
     echo '<tr>
           <th class="top-align" scope="row">
             <label class="optin-label top-label" for="activecampaign-url">ActiveCampaign API URL:</label>
           </th>
           <td>
             <input type="text" class="regular-text code" value="' . $options['activecampaign-url'] . '" name="wf_optin[activecampaign-url]" id="activecampaign-url" />
           </td>
           </tr>';
     echo '<tr>
           <th class="top-align" scope="row">
             <label class="optin-label top-label" for="activecampaign-api">ActiveCampaign API key:</label>
           </th>
           <td>
             <input type="text" class="regular-text code" value="' . $options['activecampaign-api'] . '" name="wf_optin[activecampaign-api]" id="activecampaign-api" />
             <p class="description">Login to your <a href="http://www.activecampaign.com/login/" target="_blank">ActiveCampaign account</a>, go to <i>My Settings</a>, API tab and copy/paste the API info here.</p>
           </td>
           </tr>';
     echo '<tr>';
     echo '<th class="top-align" scope="row">';
     echo '<label>API key status:</label>';
     echo '</th>';
     echo '<td>';

     // ac Status
     if ($options['activecampaign']['status'] == '1') {
       echo '<div class="dashicons dashicons-yes"></div> Valid API key.';
     } else {
       echo '<div class="dashicons dashicons-no"></div> Invalid ActiveCampaign API key provided!';
     }

     echo '</td>';
     echo '</tr>';
     echo '<th class="top-align" scope="row">';
     echo '<label>Available lists:</label>';
     echo '</th>';
     echo '<td>';
     if (isset($lists['activecampaign']) && $lists['activecampaign'] && is_array($lists['activecampaign'])) {
       echo '<ul class="lists">';
       foreach ($lists['activecampaign'] as $tmp) {
         echo '<li>' . $tmp . '</li>';
       }
       echo '</ul>';
     } else {
       echo 'none';
     }
     echo '</td>';
     echo '</tr>';

     echo '</tbody>';
     echo '</table>';
     echo '<p class="submit"><input type="submit" value="Save Settings" class="button button-primary" id="submit" name="submit"></p>';
     echo '</div>';

     // pushover
     echo '<div id="pushover">';
     echo '<table class="form-table">';
     echo '<tbody>';
     echo '<tr><td colspan="2">';
     echo '<ol>';
     echo '<li><a href="https://pushover.net/login" target="_blank">Login or create a new account</a> on Pushover. It is free.</li>';
     echo '<li><a href="https://pushover.net/apps/build" target="_blank">Create a new application</a>. Name will be visible on your device, and icon if you upload it.</li>';
     echo '<li>Copy/paste application\'s API token/key below.</li>';
     echo '<li>Install the <a href="https://pushover.net/clients" target="_blank">Pushover app</a> on one of your devices. For testing, desktop browser app is more than enough.</li>';
     echo '</ol>';
     echo '</td></tr>';
     echo '<tr>
           <th class="top-align" scope="row">
             <label class="optin-label top-label" for="pushover-key">Pushover API Token/Key:</label>
           </th>
           <td>
             <input type="text" class="regular-text code" value="' . @$options['pushover-key'] . '" name="wf_optin[pushover-key]" id="pushover-key" />
           </td>
           </tr>';
     echo '<tr>
           <th class="top-align" scope="row">
             <label class="optin-label top-label" for="pushover-users">Pushover User Keys:</label>
           </th>
           <td>
             <textarea cols="60" rows="5" name="wf_optin[pushover-users]" id="pushover-users" class="regular-text code">' . @$options['pushover-users'] . '</textarea>
             <p class="description">You can enter frequently used user keys here, so that you don\'t have to enter them again in every optin settings.<br>Enter one user key per line.<br>
             Regardless of the number of devices you have you only have <b>one</b> unique key and notifications sent to that key will be received on all devices.<br>
             If you want to send on a per-device basis enter the key in this format: <i>key:device-name</i>.</p>
           </td>
           </tr>';
     echo '<tr>';
     echo '<th class="top-align" scope="row">';
     echo '<label>API key status:</label>';
     echo '</th>';
     echo '<td>';

     // pushover Status
     if (@$options['pushover']['status'] == '1') {
       echo '<div class="dashicons dashicons-yes"></div> Valid API key.';
     } else {
       echo '<div class="dashicons dashicons-no"></div> Invalid Pushover API key provided!';
     }

     echo '</td>';
     echo '</tr>';

     echo '</tbody>';
     echo '</table>';
     echo '<p class="submit"><input type="submit" value="Save Settings" class="button button-primary" id="submit" name="submit"></p>';
     echo '</div>';

     

     echo '</div>';
     echo '</form>';

     echo '</div>';
     echo '</div>';
   } // options_page
} // wf_optin_options
