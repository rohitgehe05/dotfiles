<?php
/**
 * OptIn Ninja
 * (c) Web factory Ltd, 2017
 * Admin Ajax
 */

 class wf_optin_ajax extends wf_optin_ninja {
   // reset stats table
   static public function reset_stats_ajax() {
     global $wpdb;

     $wpdb->query('TRUNCATE TABLE ' . $wpdb->prefix . WF_OPT_STATS);

     die('1');
   } // reset_stats_ajax

   // reset subscribers table
   static public function delete_subs_ajax() {
     global $wpdb;

     $wpdb->query('TRUNCATE TABLE ' . $wpdb->prefix . WF_OPT_SIGNUPS);

     die('1');
   } // delete_subs_ajax
   
   static public function delete_sub_ajax() {
     global $wpdb;
	 
	 $sub_id = (int) $_POST['sub_id'];
	 if (empty($sub_id)) {
		 die();
	 }

     $wpdb->query('DELETE FROM ' . $wpdb->prefix . WF_OPT_SIGNUPS . ' WHERE id = ' . $sub_id  . ' LIMIT 1');

     die('1');
   } // delete_sub_ajax
 } // wf_optin_ajax
 