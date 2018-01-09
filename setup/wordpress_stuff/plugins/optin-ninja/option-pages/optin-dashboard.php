<?php
/**
 * OptIn Ninja
 * (c) Web factory Ltd, 2016
 * Dashboard
 */

class wf_optin_dashboard extends wf_optin_ninja {
   static function content() {
    global $wpdb;
	
	echo '<div class="wf_opt_dashboard_header">';
	echo '<img style="margin-bottom: -7px;" src="'.WF_OPT_PLUGINURL.'/images/header-bar-logo.png" />';
	echo '</div>';
	echo '<div class="wrap">'; 
	
	
	// Container Start
    echo '<div class="wf_opt-col-container wf_opt-col-4" style="margin-right: 0px; background-color: transparent;">';
	echo '<div class="wf_opt-title">';
	echo '<h3>Latest Subscribers</h3>';    
	echo '</div>';
    $subscribers = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . WF_OPT_SIGNUPS . " ORDER BY timestamp DESC LIMIT 8");
	
	$cc =0;
	$colors = array(0 => '#E60B0B', 1 => '#FF8888', 2 => '#A50000');
	
	if ($subscribers) {
      foreach ($subscribers as $subscriber) {

        $gravatar = '//www.gravatar.com/avatar/' . md5(strtolower(trim($subscriber->email))) . '?d=blank&s=48';
						
		echo '<div class="dashboard-subscriber-row" style="">';
			echo '<div class="dashboard-subscriber-avatar" style="background-color:'.$colors[$cc%3].'; background-image:url('.WF_OPT_PLUGINURL.'/images/avatar'.($cc%3+1).'.png);"><img src="'.$gravatar.'" /></div>';
			
			echo '<div class="dashboard-subscriber-info">';
				$subscriber_fields=explode(',',$subscriber->name);
				$subscriber_name='';
				foreach($subscriber_fields as $field){
					if(strpos($field,'name') !== false){
						$subscriber_name = str_replace('name = ','',trim($field));
					}
				}
				if (empty($subscriber_name)) {
					  echo '<i>Name not set</i> ('.$subscriber->email.')';
					} else {
					  echo '<b>' . $subscriber_name . '</b>';
					}
				
				
				echo '<div class="dashboard-subscriber-action">';
					echo 'Opted in through <a href="'.get_edit_post_link($subscriber->post_id).'">' . get_the_title($subscriber->post_id) . '</a> optin';
				echo '</div>';
					
		   		
			echo '</div>';
			
			echo '<div class="dashboard-subscriber-time">';		
				echo human_time_diff(strtotime($subscriber->timestamp),  current_time('timestamp')). ' ago';
				echo '</div>';
			
		echo '</div>';
		
		$cc++;		
      } // foreach
    } // if ($subscribers)
	echo '</div>';
	
	
	echo '<div class="wf_opt-col-container wf_opt-col-8" style="margin-right:0px; margin-left:1%; background-color: transparent;">';
		echo '<div class="wf_opt-title">';
		echo '<h3>OptIn performance in the last 14 days</h3>';    
		echo '</div>';
		echo '<div class="lf-content">';
		echo '<div id="optins_chart" style="width: 100%; height: 230px;"></div>';
		echo '</div>';
		echo '<div class="wf_opt-title" style="margin-top: 20px;">';
		echo '<h3>Top performing OptIns</h3>';    
		echo '</div>';
		echo '<div class="lf-content">';
		echo '<div id="optins_top" style="width: 100%; height: 230px;"></div>';
		echo '</div>';
    echo '</div>';
	
	echo '<div class="clearfix"></div>';
	
	
	
	 // Container Start
    echo '<div class="wf_opt-col-container wf_opt-col-8">';
    // Container Title Start
    echo '<div class="wf_opt-title">';
    echo '<h3>World Map Events</h3>';
    // Actions
    echo '<div class="wf_opt-actions">';
    //echo '<a href="#" title="Refresh" alt="Refresh"><i class="dashicons dashicons-update"></i></a>';
    echo '</div>';
    // Container Title End
    echo '</div>';
    // Container Content
    echo '<div class="wf_opt-content" style="padding:0px;">';
    echo '<div id="world_map" style="width: 100%; height: 440px;"></div>';
    echo '</div>';
    echo '</div>';
    // Container End
	
	
	
	
	
	echo '<div class="wf_opt-col-container wf_opt-col-4" style="margin-right:0px;">';
    echo '<div class="wf_opt-title">';
    echo '<h3>Subscribers per Country</h3>';
    // Actions
    echo '<div class="wf_opt-actions">';
    echo '</div>';
    echo '</div>';
    echo '<div class="wf_opt-content">';
    echo '<div id="countries_pie" style="width: 100%; height: 400px;"></div>';
    echo '</div>';
    echo '</div>';
	
	
	
	

    
    
    


    echo '<div class="clearfix"></div>';
	
	
	echo '<div class="wf_opt-col-container wf_opt-col-12" style="margin-right:0px;">';
    echo '<div class="wf_opt-title">';
    echo '<h3>Subscribers growth over time</h3>';
    echo '</div>';
    echo '<div class="wf_opt-content">';
    echo '<div id="subgrowth_chart" style="width: 100%; height: 200px;"></div>';
    echo '</div>';
    echo '</div>';
	
	
	
	
	echo '</div>';
   } // content
   
   
   
   static function get_subscribers_stats($last_timestamp = 0) {
    global $wpdb;
	$sub_stats=array();
	
	$sub_growth=array();
	$max_markers = 50;
    $markers = array();
	
	$ip_info = get_option('wf_optin_geodata');
	if(!is_array($ip_info)) $ip_info=array();
	$subscribers = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . WF_OPT_SIGNUPS . " ORDER BY timestamp ASC");
    $countries=array();
	  
    foreach ($subscribers as $subscriber) {
      
	  $sub_date=date('Y-m-d', strtotime($subscriber->timestamp));
	  
	  if(array_key_exists($sub_date, $sub_growth)){
		  $sub_growth[$sub_date]++;
	  } else {
		  $sub_growth[$sub_date]=1;
	  }
	  
	  if(array_key_exists($subscriber->ip, $ip_info)){
	  	$subscriber_location = $ip_info[$subscriber->ip];
	  } else {
		$subscriber_location = wf_optin_api::getUserLocation($subscriber->ip); 
	  }
	  
	  
	  
	  if(!empty($subscriber_location['country'])){
		  if(array_key_exists($subscriber_location['country'], $countries)){
			  $countries[$subscriber_location['country']]++;
		  } else {
			  $countries[$subscriber_location['country']]=1;
		  }
	  } else {
		 if(!array_key_exists('Unknown',$countries)) $countries['Unknown']=1;
		 $countries['Unknown']++; 
	  }
	  
	  
	  
      if (sizeof($markers) > $max_markers) {
        break;
      }
	  
	  if (empty($subscriber_location['country'])) {
        continue;
      }

      $address = '';
      if (!empty($subscriber_location['city'])) {
        $address .= $subscriber_location['city'] . ', ';
      }
      if (!empty($subscriber_location['region'])) {
        $address .= $subscriber_location['region'] . ', ';
      }
      $address .= $subscriber_location['country'];
      
	  
	  
      $markers[] = array('lat' => $subscriber_location['lat'], 'lng' => $subscriber_location['lng'], 'address' => $address, 'timestamp_diff' => human_time_diff(strtotime($subscriber->timestamp),  current_time('timestamp')) );
    } // foreach events
	
	arsort($countries);
	
	$top10countries=array_slice($countries,0,10,true);
	$remaining_countries=array_slice($countries,10,300,true);
	if(count($remaining_countries)>0) $top10countries['Other']=array_sum($remaining_countries);
	
	
	$sub_stats['markers']=$markers;
	$sub_stats['countries']=$top10countries;
	
    return $sub_stats;
  } // get_map_markers
  
  
  
  
  static function get_optins_chart($days_history = 30) {
    global $wpdb;
    $days_history = (int) $days_history;
    
    $stats = $wpdb->get_results($wpdb->prepare('SELECT date as date, views as views, views_box2 as views_box2, conversion as conversion FROM ' . $wpdb->prefix . WF_OPT_STATS . ' WHERE date > %s', date('Y-m-d', (current_time('timestamp') - DAY_IN_SECONDS * $days_history))));
    
	$days = array();
    for ($i = $days_history; $i >= 0; $i--) {
      $date = date('Y-m-d', (current_time('timestamp') - DAY_IN_SECONDS * $i));
      $days[$date] = array('views' => 0, 'views_box2' => 0, 'conversions' => 0, 'conversion_rate' => 0.0);
    }
    foreach ($stats as $day) {
      if(isset($days[$day->date])){
		  $days[$day->date]['views']+=$day->views;
		  $days[$day->date]['views_box2']+=$day->views_box2;
		  $days[$day->date]['conversions']+=$day->conversion;		  
	  } else {
		  $days[$day->date] = array('views' => $day->views, 'views_box2' => $day->views_box2, 'conversions' => $day->conversion, 'conversion_rate' => $day->views? number_format(min((int) $day->conversion / $day->views * 100, 100), 1): number_format(0, 1));
	  }
    }
    $out['history'] = $days;
    
    return $out;
  } // get_optins_chart
  
  static function wf_opt_orderbyconversions($a, $b) {
	  return $b["conversions"] - $a["conversions"];
	}
	
  static function get_top_optins($optin_count) {
    global $wpdb;
    $optin_count = (int) $optin_count;
    
    $optins = $wpdb->get_results('SELECT post_id as post_id, sum(views) as total_views, sum(conversion) as total_conversions FROM ' . $wpdb->prefix . WF_OPT_STATS . ' GROUP BY `post_id` ORDER BY `total_conversions` DESC LIMIT '.$optin_count);
    $top_optins=array();
	
    foreach ($optins as $optin) {
      $top_optins[$optin->post_id] = array('optin' => get_the_title($optin->post_id), 'views' => $optin->total_views, 'conversions' => $optin->total_conversions);	  
    }
	
	

	usort($top_optins, array('wf_optin_dashboard','wf_opt_orderbyconversions'));
    
    return $top_optins;
  } // get_optins_chart
  
  static function get_countries(){
	global $wpdb;
	$ip_info = get_option('wf_optin_geodata');
	$subscribers = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . WF_OPT_SIGNUPS . " ORDER BY timestamp ASC");
    $countries=array();
	$geo_queries = 0;  
    foreach ($subscribers as $subscriber) {
      
	  if(array_key_exists($subscriber->ip, $ip_info)){
	  	$subscriber_location = $ip_info[$subscriber->ip];
	  } else {
		$subscriber_location = wf_optin_api::getUserLocation($subscriber->ip); 
		$geo_queries++;
	  }
	  	  
	  
	  if(!empty($subscriber_location['country'])){
		  if(array_key_exists($subscriber_location['country'], $countries)){
			  $countries[$subscriber_location['country']]++;
		  } else {
			  $countries[$subscriber_location['country']]=1;
		  }
	  } else {
		 if(!array_key_exists('Unknown',$countries)) $countries['Unknown']=1;
		 $countries['Unknown']++; 
	  }
		
	  if($geo_queries >=100) break;	
		
    } // foreach events
	
	arsort($countries);
	
	$top10countries=array_slice($countries,0,10,true);
	$remaining_countries=array_slice($countries,10,300,true);
	if(count($remaining_countries)>0) $top10countries['Other']=array_sum($remaining_countries);
	
    return $top10countries;
	  
  }
  
  static function get_subscriber_growth() {
    global $wpdb;
		
	if ( false === ( $days = get_transient( 'wf_opt_subscriber_growth_chart' ) ) ) {
		$stats = $wpdb->get_results('SELECT date as date, conversion as conversion FROM ' . $wpdb->prefix . WF_OPT_STATS . ' ORDER BY date ASC' );
		$days = array();
		
		foreach ($stats as $day) {
		  @$days[ $day->date ] += (int) $day->conversion;	  
		}
		
		$previous_date='';
		foreach ($days as $date=>$val) {
		  if($previous_date) $days[$date]+=$days[$previous_date];
		  $previous_date=$date;
		}
	
    	set_transient( 'wf_opt_subscriber_growth_chart', $days, 12 * HOUR_IN_SECONDS );
	}
	
    return $days;
  } // get_subscriber_growth
  
  static function setup_js_vars() {
    $sub_stats = self::get_subscribers_stats();
	$optin_stats = self::get_optins_chart(14);
	$top_optins = self::get_top_optins(5);
	
	$countries = self::get_countries();
	$sub_growth = self::get_subscriber_growth();
	
    wp_localize_script('jquery', 'wf_opt_map_markers', $sub_stats['markers']);
    wp_localize_script('jquery', 'wf_opt_countries', $countries);
	wp_localize_script('jquery', 'wf_opt_optin_stats', $optin_stats);
	wp_localize_script('jquery', 'wf_opt_plugin_url', WF_OPT_PLUGINURL);
	wp_localize_script('jquery', 'wf_opt_sub_growth', $sub_growth);
	wp_localize_script('jquery', 'wf_opt_top_optins', $top_optins);
  } // enqueue_files
  
} // wf_optin_dashboard
