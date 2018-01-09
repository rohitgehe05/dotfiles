<?php
/*
 * OptIn Ninja
 * (c) Web factory Ltd, 2016
 */

 class wf_optin_ninja_optin_templates extends wf_optin_ninja {
   // background box markup
   static $template_folder_url = '';
   static $img_src_to_upload_url = array();
   
   
   static function check_license(){
     $product_key = @trim($_POST['product_key']);
     
     if (strlen($product_key) != 36) {
       wp_send_json_error('Error - Invalid Product Key format.');  
     }
       
     $auth_query = '&verify-key-only=1&product_key=' . $product_key;
     $lc_request = wp_remote_get(WF_OPT_TEMPLATES_SERVER . '?fetch-templates=true' . $auth_query, array('sslverify' => false, 'timeout' => 120 ));
     
	   
	   if (!is_wp_error($lc_request) && !empty($lc_request['response']['code']) && $lc_request['response']['code'] == 200) {
		   $lc_response = json_decode($lc_request['body'], true);
		   if(!empty($lc_response['success']) && $lc_response['success'] === true){
         update_option('wf_opt_product_key', $product_key);
			   wp_send_json_success('Purchase Code verified and saved. Templates will start downloading. Please do not refresh the page.');
		   } else {
          update_option('wf_opt_product_key', '');
		      wp_send_json_error('Error - Invalid Product Key.');  
		   }
	   } else {
		   wp_send_json_error('Error - Could not connect to licensing server.');
	   }
       
	   die();
   } // check_license
   
   static function fix_img_path(&$item, $key) {
    if (fnmatch("*.jpg", $item) || fnmatch("*.jpeg", $item) || fnmatch("*.png", $item) || fnmatch("*.gif", $item) ) {	
      if (!empty($item)) {
        $image_url = self::$template_folder_url . $item;
        #$image = wp_remote_get($image_url);
		$media = media_sideload_image($image_url, 0, '', 'src');
		if(!is_wp_error($media)) {
			self::$img_src_to_upload_url[$item] = $media;
		}
      }
      // $item
    }
  } // fix_img_path
  
  	
   static function download_template_file($template_key='') {
    // Template key
    if(!empty($template_key)){
		$key = trim($template_key);
	} else {
		$key = trim($_POST['template_key']);
	}
	
	$wf_opt_product_key = get_option('wf_opt_product_key');
    if(empty($wf_opt_product_key)){
		wp_send_json_error('Product key invalid.');
	}
	
	$auth_query = '&product_key='.$wf_opt_product_key;
    
    // Fetch template setup array

    $call_proxy = wp_remote_get(WF_OPT_TEMPLATES_SERVER . '?fetch-templates=true&template_key=' . $key . $auth_query, array('sslverify' => false, 'timeout' => 120));
	
		// Decode template setup array
    $template = json_decode($call_proxy['body']);

    // Get template data url
    $template_data_url = $template->data->template_data;
    self::$template_folder_url = $template->data->template_url;

    // Remote get template data url
    $template_data = wp_remote_get($template_data_url, array('sslverify' => false, 'timeout' => 120 ));

    // If the template data url exists and is reachable
    if (!empty($template_data['response']['code']) && $template_data['response']['code'] == 200) {
      $optin_layout = base64_decode($template_data['body']);
      $optin_layout = unserialize($optin_layout);
	  array_walk_recursive($optin_layout, array(__CLASS__, 'fix_img_path'));
	  
	  $screenshot = media_sideload_image($template->data->template_url.'/_screenshot.jpg', 0, '', 'src');
	  // After recursive - MUST
      $optin_layout = json_encode($optin_layout);
      foreach (self::$img_src_to_upload_url as $img_key => $img_url) {
        $optin_layout = str_replace($img_key, $img_url, $optin_layout);
      }
      // Json decode before saving
      $optin_layout = json_decode($optin_layout, true);
      $optin_layout = serialize($optin_layout);
      $installed_templates = get_option(WF_OPT_INSTALLED_TEMPLATES);
	  $installed_templates[$key]['version'] = $template->data->template_version;
      $installed_templates[$key]['types'] = $template->data->template_types;
      $installed_templates[$key]['thumb'] = $template->data->thumb;
      $installed_templates[$key]['name'] = $template->data->name;
      $installed_templates[$key]['url'] = $template->data->url;
      $installed_templates[$key]['layout'] = $optin_layout;
	  $installed_templates[$key]['description'] = $template->data->description;
	  $installed_templates[$key]['screenshot'] = $screenshot;

      ksort($installed_templates);

      update_option(WF_OPT_INSTALLED_TEMPLATES, $installed_templates);	 
	  return true; 
    }

    // Error
    wp_send_json_error('Download failed, maybe template data file does not exist.');
  } // download_template_file
   
   static function download_template_files(){
	    set_time_limit(600);
		$current_template = (int)$_POST['template_index'];	    
		$templates = get_option(WF_OPT_TEMPLATES_OPTION);
		$templates_skip=$current_template;
		foreach($templates as $template_key => $template){
			if($templates_skip) {
				$templates_skip--;
				continue;
			}			
			self::download_template_file($template_key);
			$current_template++;
			wp_send_json_success($current_template);
			die(); 
		}
		  
   }
   
   
   static function download_templates() {
	set_time_limit(600);
    // Send a call to server
    
	$wf_opt_product_key = get_option('wf_opt_product_key');
    if(empty($wf_opt_product_key)){
		wp_send_json_error('Invalid product key.');
	}
    $auth_query = '&product_key='.$wf_opt_product_key;
    $call_proxy = wp_remote_get(WF_OPT_TEMPLATES_SERVER . '?fetch-templates=true' . $auth_query, array('sslverify' => false, 'timeout' => 120 ));

    if (is_wp_error($call_proxy) || empty($call_proxy['body'])) {
      wp_send_json_error('Error - License invalid.');
    } else {
      $body = json_decode($call_proxy['body']);
		
      if ($body->success) {
        $templates = json_decode($call_proxy['body'],true);
        $templates = $templates['data']['templates'];
        update_option(WF_OPT_TEMPLATES_OPTION, $templates);		
        update_option(WF_OPT_INSTALLED_TEMPLATES, false);    
        wp_send_json_success(count($templates));
      } else {
        wp_send_json_error('Error - License invalid.'.$call_proxy['body']);
      }
    }

    wp_send_json_error('Error occured.');
	die();
  } // download_templates
  
  
  
   static function content() {
     global $post;
	 
	 
     $optin_template=get_post_meta($post->ID, 'optin_template', true);
	 $selected = '';
	 
	 // Get installed templates
     $installed_templates = get_option(WF_OPT_INSTALLED_TEMPLATES);
   
   $wf_opt_product_key = get_option('wf_opt_product_key');
   if (!$wf_opt_product_key) {
     update_option('wf_opt_product_key', '23102017');  
   }
	 
	 echo '<div id="purchase_key_wrapper" style="'.( empty($wf_opt_product_key)?'':'display:none;' ).'">';
     echo '<p>In order to use the templates you have to verify your purchase of OptIn Ninja by entering a valid, unique purchase code. Please note that a single regular license grants you use on only one domain (excluding localhost). Have a look at the <a href="http://optin-ninja.webfactoryltd.com/optin-templates/" target="_blank">template previews</a>.</p>';
	 echo '<label for="wf_opt_product_key">Purchase Code: </label>';
	 echo '<input type="text" name="wf_opt_product_key" id="wf_opt_product_key" style="width: 300px;" value="'.(isset($wf_opt_product_key)?$wf_opt_product_key:'').'" />';
	 echo '<input type="button" id="wf_opt_verify_licence"  class="button button-secondary" value="Save & validate purchase code" /><br />';
	 echo '<p>How to find the purchase code? Open your <a href="http://codecanyon.net/downloads" target="_blank">CodeCanyon downloads</a> page and find OptIn Ninja on it. Click the green "Download" button and select the last item - "License certificate &amp; purchase code (text)". Save the file to your computer, open it and copy/paste the purchase code.</p>';
	 echo '</div>';
	 
	 
	 
	 echo '<div id="optin_templates_wrapper" style="'.( !empty($wf_opt_product_key)?'':'display:none;' ).'">';
		 echo '<input type="hidden" name="optin_template" id="optin_template" value="">';
		 echo '<div class="optin_template_preview_box_wrapper"><div class="optin_template_preview_box"></div></div>';
		 
		 /*	 
		 echo '<div class="optin_template_box" data-optin_template="default" data-optin_template-description="Default template" data-optin_template-name="Default template" data-optin_template-url="" data-optin_template-thumb="' . WF_OPT_TEMPLATEURL . 'default.png">';
		 echo '<img src="' . WF_OPT_TEMPLATEURL . 'default.png">';
		 echo '<span>User Defined</span>';
		 echo '</div>';
		  */ 
		 if (!empty($installed_templates)) {
			 foreach ($installed_templates as $key => $template) {
					   
			   // Is this template selected
			   if (!empty($optin_template)) {
				 $selected = '';
				 if ($optin_template != 'user-defined') {
				   if ($optin_template == $key) {
					 $selected = 'selected';
				   }
				 }
			   }
			   
			   
			   echo '<div class="optin_template_box ' . $selected . '" data-optin_template="' . $key . '" data-optin_template-description="' . @$template['description'] . '" data-optin_template-name="' . $template['name'] . '" data-optin_template-url="' . @$template['url'] . '" data-optin_template-thumb="' . $template['thumb'] . '" >';
			   if (!empty($template['thumb'])) {
				 echo '<img src="' . $template['screenshot'] . '">';
			   }
			   
			   echo '<span>' . $template['name'] . '</span>';
			   echo '</div>';
			 }
		 }
		 
		 echo '<div class="clear"></div>';
		 echo '<p style="float: right;" >Due to their size and quantity templates have to be downloaded separately. <input type="button" class="button button-primary" id="wf_opt_download_templates" value="Refresh Templates List"></p>';
		 echo '<div class="clear"></div>';
	 echo '</div>';
	 
   } // form
 } // wf_optin_ninja_optin_templates