<?php
/**
 * OptIn Ninja
 * (c) Web factory Ltd, 2017
 * API Ajax
 */

 class wf_optin_api extends wf_optin_ninja {
   // main wrapper function
   static function subscribe_wrapper() {
     $post_id = $_POST['post_id'];
     $meta = get_post_meta($post_id, 'wf_optin_meta', true);
     $autoresponder = $meta['optin-form']['mail-listing-service'];

     if(!$autoresponder) {
       die('0');
     }

     if(!is_array(@$_POST['fields'])) {
       parse_str(@$_POST['fields'], $fields);    
     } else {
       $fields = @$_POST['fields'];
     }
     $fields['email'] = sanitize_email($fields['email']);
     $fields = apply_filters('optin_ninja_ajax_form_fields', $fields, $post_id);

     switch ($autoresponder) {
       case 'mail-chimp':
         $result = self::mailchimp($meta, $fields, $post_id);
       break;
       case 'madmimi':
         $result = self::madmimi($meta, $fields, $post_id);
       break;
       case 'aweber':
         $result = self::aweber($meta, $fields, $post_id);
       break;
       case 'campaignmonitor':
         $result = self::campaignmonitor($meta, $fields, $post_id);
       break;
       case 'activecampaign':
         $result = self::activecampaign($meta, $fields, $post_id);
       break;
       case 'getresponse':
         $result = self::getresponse($meta, $fields, $post_id);
       break;
       case 'emailoctopus':
         $result = self::emailoctopus($meta, $fields, $post_id);
       break;
       case 'customurl':
         $result = self::customurl($meta, $fields, $post_id);
       break;
       case 'local':
       case 'facebook':
         $result = self::local_db($meta, $fields, $post_id);
       break;
       default:
         die('Unknown autoresponder.');
     } // switch


     if ($result == 3) {
       die('3');
     } elseif ($result == 1) {
       self::save_subscriber($post_id, $fields);
       wf_optin_ninja::count_stats('3', $post_id);
       wf_optin_ninja::send_notifications($meta, $fields, $post_id, false);
       die('1');
     } elseif ($result == 0) {
       die('0');
     } else {
       die('0');
     }
   } // autoresponder_wrapper
   
   
   static function log_error($optin_id, $autoresponder, $meta, $fields, $data) {
     $out = '';
     
     $out .= 'Date & time: ' . date(get_option('date_format') . ' @ ' . get_option('time_format'), current_time('timestamp')) . "\n";
     $out .= 'Autoresponder: ' . $autoresponder . "\n";
     $out .= 'Raw submitted fields: ' . var_export($fields, true) . "\n";
     $out .= "Autoresponder response: \n" . var_export($data, true);
     
     $tmp = get_post_meta($optin_id, 'wf_optin_meta', true);
     $tmp['optin-form']['error-log'] = $out;
     update_post_meta($optin_id, 'wf_optin_meta', $tmp);
   } // log_error


   static function mailchimp($meta, $fields, $optin_id) {
     $options = get_option('wf-optin');

     require_once('api/mailchimp.api.php');

     $api = new MCAPI($options['mail-chimp-api-key']);
     $list_id = $meta['optin-form']['mail-chimp-list'];

     $member_info = $api->listMemberInfo($list_id, $fields['email']);

     if (isset($member_info['errors']) && $member_info['errors'] == 1) {
       if (class_exists('wf_optin_ninja_fields')) {
         $email = $fields['email'];
         unset($fields['email']);
         $tmp = $api->listSubscribe($list_id, $email, $fields, 'html', false);
       } else {
         $tmp = $api->listSubscribe($list_id, $fields['email'], array('FNAME' => $fields['name']));
       }
       if($tmp === true) {
         return 1;
       } else {
         self::log_error($optin_id, 'mailchimp', $meta, $fields, $api);
         return 0;
       }
     } else {
       // Member Exists
       return 3;
     }
   } // mailchimp


   static function getresponse($meta, $fields, $optin_id) {
     $options = get_option('wf-optin');
     require_once 'api/getresponse.api.php';

     $api = new GetResponse($options['getresponse-api-key']);

     if(!$api->ping()) {
       self::log_error($optin_id, 'getresponse', $meta, $fields, $api);
       // api error
       return 0;
     }

     $test = (array)$api->getContactsByEmail($fields['email']);
     if (sizeof($test)) {
       // already subscribed
       return 3;
     }


     $fields2 = $fields;
     unset($fields2['name'], $fields2['email']);

     $subscribe = $api->addContact($meta['optin-form']['getresponse-list'], $fields['name'], $fields['email'],'standard', 0, $fields2);
     if (@$subscribe->queued == 1) {
       // ok
       return 1;
     } elseif ($subscribe == -1) {
       // already subscribed
       return 3;
     } else {
       self::log_error($optin_id, 'getresponse', $meta, $fields, $api);
       return 0;
     }
   } // getresponse


   static function madmimi($meta, $fields, $optin_id) {
     $options = get_option('wf-optin');
     require_once WF_OPT_PLUGIN_DIR . '/api/MadMimi.class.php';

     $api = new MadMimi($options['madmimi-username'], $options['madmimi-api']);

     if ($api->lists() == 'Unable to authenticate' || $api->lists() == false) {
       self::log_error($optin_id, 'madmimi', $meta, $fields, $api);
       return 0;
     }

     $check = $api->Memberships($fields['email']);
     if ($check) {
       $check = new SimpleXMLElement($check);
       foreach ($check as $tmp) {
         if ($meta['optin-form']['madmimi-list'] == (string) $tmp->attributes()->id) {
           return 3;
         }
       }
     }

     if (class_exists('wf_optin_ninja_fields')) {
       $fields['add_list'] = $meta['optin-form']['madmimi-list'];
       $subscribe = $api->AddUser($fields, 0);
     } else {
       $subscribe = $api->AddUser(array('email' => $fields['email'], 'firstName' => $fields['name'], 'add_list' => $meta['optin-form']['madmimi-list']), 0);
     }

     if ($subscribe) {
       return 1;
     } else {
       self::log_error($optin_id, 'madmimi', $meta, $fields, $api);
       return 0;
     }
   } // madmimi

   static function campaignmonitor($meta, $fields, $optin_id) {
     $options = get_option('wf-optin');
     $fields2 = array();
     require_once WF_OPT_PLUGIN_DIR . '/api/campaignmonitor_api/csrest_subscribers.php';

     $api = new CS_REST_Subscribers($meta['optin-form']['campaignmonitor-list'], array('api_key' => $options['cm-api-key']));

     $check = $api->get($fields['email']);
     if($check->was_successful()) {
       return 3;
     }

     foreach ($fields as $key => $val) {
       if ($key == 'email' || $key == 'name') {
         continue;
       }
       $fields2[] = array('Key' => $key, 'Value' => $val);
     }

     $result = $api->add(array('EmailAddress' => $fields['email'], 'Name' => $fields['name'], 'CustomFields' => $fields2, 'Resubscribe' => false));

     if($result->was_successful()) {
       return 1;
     } else {
       self::log_error($optin_id, 'campaignmonitor', $meta, $fields, $api);
       return 0;
     }
   } // cm

   static function activecampaign($meta, $fields, $optin_id) {
     $options = get_option('wf-optin');
     require_once WF_OPT_PLUGIN_DIR . '/api/activecampaign_api/ActiveCampaign.class.php';
     $ac = new ActiveCampaign($options['activecampaign-url'], $options['activecampaign-api']);
     $list_id = $meta['optin-form']['activecampaign-list'];

     if (class_exists('wf_optin_ninja_fields')) {
       foreach ($fields as $field => $val) {
          if ($field == 'email' || $field == 'first_name' || $field == 'last_name'  || $field == 'orgname'  || $field == 'tags'  || $field == 'ip4') {
           $tmp = $field;
         } else {
           $tmp = strtoupper($field);
           $tmp = "field[%{$tmp}%,0]";
         }
         $contact[$tmp] = $val;
       } // foreach
       $contact["p[{$list_id}]"] = $list_id;
       $contact["status[{$list_id}]"] = 1;
     } else {
       $contact = array('email' => $fields['email'], 'first_name' => $fields['name'], "p[{$list_id}]" => $list_id, "status[{$list_id}]" => 1);
     }
     if (!(int)$ac->credentials_test()) {
       self::log_error($optin_id, 'activecampaign', $meta, $fields, $ac);
       return 0;
     }

     $exists = $ac->api('contact/view?email=' . $fields['email'], $contact);
     if (!isset($exists->id)) {
        // add
        $add = $ac->api('contact/add', $contact);
        if ((int) $add->success) {
          return 1;
        } else {
          self::log_error($optin_id, 'activecampaign', $meta, $fields, $add);
          return 0;
        }
     } else {
       // already subscribed
       return 3;
     }
   } // AC

   static function aweber($meta, $fields, $optin_id) {
     $options = get_option('wf-optin');
     require_once 'api/aweber.api.php';

       try {
         $aweber = new AWeberAPI($options['aweber']['consumer-key'], $options['aweber']['consumer-secret']);
         $account = $aweber->getAccount($options['aweber']['access-key'], $options['aweber']['access-secret']);

         $listURL = "/accounts/" . $account->id . "/lists/" . $meta['optin-form']['aweber-list'];
         $list = $account->loadFromUrl($listURL);

         $params = array(
           'email' => $fields['email'],
           'name' => $fields['name']);
         unset($fields['email'], $fields['name']);
         if ($fields) {
           $params['custom_fields'] = $fields;
         }

         $subscribers = $list->subscribers;
         $new_subscriber = $subscribers->create($params);

         return 1;
     } catch(AWeberAPIException $exc) {
/*
       print "<h3>AWeberAPIException:</h3>";
       print " <li> Type: $exc->type              <br>";
       print " <li> Msg : $exc->message           <br>";
       print " <li> Docs: $exc->documentation_url <br>";
       print "<hr>";
*/
       if ($exc->message == 'email: Subscriber already subscribed and has not confirmed.') {
         return 3;
       } else {
         self::log_error($optin_id, 'aweber', $meta, $fields, $exc->message);
         return 0;
       }
     }
   } // aweber


   // save subscriber in local db
   static function local_db($meta, $fields, $optin_id) {
     $tmp = self::save_subscriber($optin_id, $fields, true);

     if (!$tmp) {
       return 3;
     } else {
       self::autorespond(@$fields['name'], $fields['email'], $optin_id);
       return 1;
     }
   } // local_db


   // subscribe via FB
   static function fb_subscriber($meta, $fields, $optin_id) {
     $tmp = self::save_subscriber($optin_id, $fields, true);

     if (!$tmp) {
       return 3;
     } else {
       self::autorespond($fields['name'], $fields['email'], $optin_id);
       return 1;
     }
   } // fb_subscriber

   
   // post subscriber data to EmailOctopus
   static function emailoctopus($meta, $fields, $optin_id) {
     $options = get_option('wf-optin');

     $api_key = $options['emailoctopus-api-key'];
     $list_id = $meta['optin-form']['emailoctopus-list'];
     
     $data = array('api_key' => $api_key, 'email_address' => $fields['email'], 'first_name' => $fields['name'], 'last_name' => '', 'subscribed' => true);
     
     $response = wp_remote_post('https://emailoctopus.com/api/1.2/lists/' . $list_id . '/contacts', array(
        'method' => 'POST',
        'timeout' => 25,
        'redirection' => 5,
        'sslverify' => false,
        'httpversion' => '1.0',
        'blocking' => true,
        'headers' => array(),
        'body' => $data
      ));

      if (is_wp_error($response)) {
         self::log_error($optin_id, 'emailoctopus', $meta, $fields, $response->get_error_message());
         return 0;
      } else {
         $body = json_decode(wp_remote_retrieve_body($response));
         if (!empty($body->error) && $body->error->code == 'MEMBER_EXISTS_WITH_EMAIL_ADDRESS') {
           return 3;
         } elseif (!empty($body->error)) {
           self::log_error($optin_id, 'emailoctopus', $meta, $fields, $body->error->message);
           return 0;
         }
         return 1;
      }
   } // emailoctopus   

   
   // post subscriber data to a custom url
   static function customurl($meta, $fields, $optin_id) {
     if ($meta['optin-form']['custom-url-extra']) {
       parse_str(htmlspecialchars_decode($meta['optin-form']['custom-url-extra']), $fields2);
     } else {
       $fields2 = array();
     }

     $email_field = 'email';
     if ($meta['optin-form']['custom-email-field']) {
       $email_field = $meta['optin-form']['custom-email-field'];
     }

     if (class_exists('wf_optin_ninja_fields')) {
       $fields2 = array_merge($fields2, $fields);
       $fields2['optin_page_id'] = $optin_id;
       $fields2['optin_page_title'] = get_the_title($optin_id);
     } else {
       $fields2['name'] = $fields['name'];
       $fields2[$email_field] = $fields['email'];
       $fields2['optin_page_id'] = $optin_id;
       $fields2['optin_page_title'] = get_the_title($optin_id);
     }

     $response = wp_remote_post($meta['optin-form']['custom-url'], array(
      'method' => 'POST',
      'timeout' => 45,
      'redirection' => 3,
      'httpversion' => '1.0',
      'blocking' => true,
      'headers' => array(),
      'body' => $fields2));

     if (is_wp_error($response)) {
       self::log_error($optin_id, 'customurl', $meta, $fields, $response);
       return 0;
     } else {
       return 1;
     }
   } // custom_url

   static function getUserIP() {
		if( array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ) {
			if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',')>0) {
				$addr = explode(",",$_SERVER['HTTP_X_FORWARDED_FOR']);
				return trim($addr[0]);
			} else {
				return $_SERVER['HTTP_X_FORWARDED_FOR'];
			}
		}
		else if(!empty($_SERVER['REMOTE_ADDR'])){
			return $_SERVER['REMOTE_ADDR'];
		} else {
			return 'unknown.ip';	
		}
	}  	
	
   static function getUserLocation($user_ip){
	   $ip_info = get_option('wf_optin_geodata', array());
	   if (!empty($ip_info[$user_ip])) {
		   return $ip_info[$user_ip];
	   }
		$geo_req = wp_remote_get('http://freegeoip.net/json/'.$user_ip, array('sslverify' => false));
		if (!is_wp_error($geo_req) && !empty($geo_req['response']['code']) && $geo_req['response']['code'] == 200) {
			$geo_data = json_decode(stripslashes($geo_req['body']));
			$ip_info[$user_ip]=array(
				'lat'=>$geo_data->latitude, 
				'lng'=>$geo_data->longitude, 
				'city' => $geo_data->city,
				'region' => $geo_data->region_name,
				'zip' => $geo_data->zip_code,
				'country' =>$geo_data->country_name
			);
			update_option('wf_optin_geodata',$ip_info);
			return $ip_info[$user_ip];
		} else {
			return '';
		}
   }
	
   // save subscriber data to local DB
   static function save_subscriber($post_id, $fields, $force = false) {
     global $wpdb;
     $save = 0;

     $email = $fields['email'];
     unset($fields['email']);
     $custom = '';
     foreach ($fields as $key => $val) {
       $custom .= "$key = $val, ";
     }
     $custom = trim($custom, ', ');


     $option = get_option('wf-optin');
     
	 $ip = self::getUserIP();
	 $geodata = self::getUserLocation($ip);
     $save = $wpdb->query($wpdb->prepare('INSERT IGNORE INTO ' . $wpdb->prefix . WF_OPT_SIGNUPS . ' (name, email, post_id, ip, timestamp) VALUES (%s, %s, %d, %s, %s)', $custom, $email, $post_id, $ip, current_time('mysql')));
     

     $subscribed = @$_COOKIE['optin_ninja_subscribed'];
     $subscribed = @unserialize($subscribed);
     $subscribed[$post_id] = true;
     setcookie('optin_ninja_subscribed', serialize($subscribed), time() + DAY_IN_SECONDS * 60, '/');

     if (!$save) {
       return 0;
     } else {
       return 1;
     }
   } // save_subscriber


   static function step2_stats() {
     wf_optin_ninja::count_stats('2');

     die('1');
   } // step2_stats


   // send welcome email
   static public function autorespond($name, $email, $post_id) {
     $options = get_post_meta((int) $post_id, 'wf_optin_meta', true);

     if (!isset($options['autoresponder']['send_email']) || $options['autoresponder']['send_email'] != '1') {
       return;
     }

     $subject = $options['autoresponder']['subject'];
     $subject = str_replace(array('{user-name}', '{user-email}'), array($name, $email), $subject);
     $body = $options['autoresponder']['body'];
     $body = str_replace(array('{user-name}', '{user-email}'), array($name, $email), $body);
     $body = apply_filters('the_content', $body);

     $headers = array();
     $headers[] = 'From: ' . $options['autoresponder']['from'] . "\r\n";
     $headers[] = 'Content-type: text/html' . "\r\n";
     @wp_mail($email, $subject, $body, $headers);
   } // autorespond
 } // wf_optin_api
 