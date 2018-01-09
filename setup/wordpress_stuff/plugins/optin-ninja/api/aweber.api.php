<?php
require_once('aweber_api/aweber_api.php');

class aweber_api {

  var $consumer_key;
  var $consumer_secret;
  var $access_key;
  var $access_secret;

  function aweber_api($post_id, $auth_code) {
    $auth = AWeberAPI::getDataFromAweberID($auth_code);
    list($consumerKey, $consumerSecret, $accessKey, $accessSecret) = $auth;

    $meta = get_post_meta($post_id, 'wf_optin_meta', true);

    $meta['optin-form']['aweber-consumer-key'] = $auth[0];
    $meta['optin-form']['aweber-consumer-secret'] = $auth[1];
    $meta['optin-form']['aweber-access-key'] = $auth[2];
    $meta['optin-form']['aweber-access-secret'] = $auth[3];

    update_post_meta($post_id, 'wf_optin_meta', $meta);
  } // aweber_api


} // aweber_api