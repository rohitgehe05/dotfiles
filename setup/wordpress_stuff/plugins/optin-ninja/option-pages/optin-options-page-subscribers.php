<?php
/**
 * OptIn Ninja
 * (c) Web factory Ltd, 2016
 */

class wf_optin_options_subscribers extends wf_optin_ninja {
  static function content() {
    global $wpdb;

    echo '<div class="wrap">';
    echo '<h2>OptIn Ninja Subscribers</h2>';

    // Get Users
    $subscribers = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . WF_OPT_SIGNUPS . " ORDER BY id DESC");

    echo '<table class="" id="datatables">';

    // Table Head
    echo '<thead>';
    echo '<tr>';
    echo '<th>ID</th>';
    echo '<th>Email</th>';
    echo '<th>Additional Fields</th>';
    echo '<th>Date</th>';
    echo '<th>OptIn Page</th>';
	echo '<th>Location</th>';
	echo '<th>&nbsp;</th>';
    echo '</tr>';
    echo '</thead>';

    // Table Body
    echo '<tbody>';

    if ($subscribers) {
      $x = 0;
      foreach ($subscribers as $subscriber) {

        $class = "alternate";
        if ($x == 2) {
          $x = 0;
          $class = "";
        }

        $list = $wpdb->get_var("SELECT post_title FROM " . $wpdb->posts . " WHERE ID='" . $subscriber->post_id . "'");

        if ($subscriber->name && strpos($subscriber->name, '=') === false) {
          $fields = 'name = ' . $subscriber->name;
        } else {
          $fields = $subscriber->name;
        }

		$tmp = wf_optin_api::getUserLocation($subscriber->ip);
		//var_dump($subscriber, $tmp); die();
        // Row
        echo '<tr>';
        echo '<td>' . $subscriber->id . '</td>';
        echo '<td>' . $subscriber->email . '</td>';
        echo '<td>' . $fields . '</td>';
        echo '<td>' . date(get_option('date_format') . ' @ ' . get_option('time_format'), strtotime($subscriber->timestamp)) . '</td>';
        echo '<td><a href="' . admin_url('post.php?post=' . $subscriber->post_id . '&action=edit') . '">' . $list . '</a></td>';
		echo '<td>' . (empty($tmp['country'])? 'Unknown': $tmp['country']) . '</td>';
		echo '<td><a href="#" data-subscriber-id="' . $subscriber->id . '" class="button button-secondary delete-subscriber">Delete</a></td>';
        echo '</tr>';
        $x++;
      } // foreach
    } // if ($subscribers)

    echo '</tbody>';
    echo '</table>';

    echo '</div>';
  } // subscribers_page
} // wf_optin_options_content