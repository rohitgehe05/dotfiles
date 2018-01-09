<?php
/**
 * OptIn Ninja
 * (c) Web factory Ltd, 2016
 */

class wf_optin_options_ab_tests extends wf_optin_ninja {
   static function process_ab_actions() {
     global $wpdb, $wp_settings_errors;

     if (isset($_POST['tag-name']) && isset($_POST['tag-url'])) {
       $name = sanitize_text_field(trim($_POST['tag-name']));
       $url = trim(strtolower(sanitize_title($_POST['tag-url'])), '/');
       if ($url) {
         $url = '/' . $url . '/';
       } else {
         $url = '/';
       }

       if (!$name) {
         $tmp = $wpdb->get_row('SHOW TABLE STATUS LIKE \'' . $wpdb->prefix . WF_OPT_AB . '\'');
         $name = 'A/B Test #' . $tmp->Auto_increment;
       }

       $tmp = $wpdb->insert($wpdb->prefix . WF_OPT_AB, array('name' => $name, 'slug' => $url), array('%s', '%s'));
       if ($tmp) {
         add_settings_error('optin', 'optin-ab-saved', 'New A/B Test added.', 'updated');
       } else {
         add_settings_error('optin', 'optin-ab-saved', 'A/B Test with that URL already exists.', 'error');
       }

        header('location: admin.php?page=wf-optin-ninja-ab-tests&settings-updated=true');
        set_transient('settings_errors', $wp_settings_errors);
     }

       if (isset($_GET['action_do']) && $_GET['action_do'] == 'delete' && isset($_GET['test-id']) && is_numeric($_GET['test-id'])) {
         $tmp = $wpdb->delete($wpdb->prefix . WF_OPT_AB, array('id' => (int) $_GET['test-id']), array('%d'));
         if ($tmp) {
           add_settings_error('optin', 'optin-ab-saved', 'A/B Test #' . (int) $_GET['test-id'] . ' deleted.', 'updated');
         } else {
           add_settings_error('optin', 'optin-ab-saved', 'Error deleting A/B Test #' . (int) $_GET['test-id'] . '.', 'error');
         }

         header('location: admin.php?page=wf-optin-ninja-ab-tests&settings-updated=true');
         set_transient('settings_errors', $wp_settings_errors);
       }
   } // process_ab_actions


   // complete a/b test page markup
   static function content() {
     global $wpdb;
     $options = get_option('wf-optin', array());
     $tests = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . WF_OPT_AB . " ORDER BY id ASC");

     settings_errors('optin');
     echo '<div class="wrap">';
     echo '<h2>OptIn Ninja A/B Tests</h2><br>';

     echo '<div id="dialog-ab-shortcode">';
     if (isset($options['disable_popup']) && $options['disable_popup']) {
       echo '<p><b>Popups are disabled</b>. You can enable them in <a href="' . admin_url('admin.php?page=wf-optin-ninja-settings') . '">settings</a>.</p>';
       $disabled = 1;
     } else {
       $disabled = 0;
     }
     echo '</div>';

     // Col Right Start
     echo '<div id="col-right">';
     echo '<div class="col-wrap">';

     echo '<table class="wp-list-table widefat fixed">';
     echo '<thead>';
     echo '<tr>';
     echo '<th>Name</th>';
     echo '<th>URL</th>';
     echo '<th># of OptIn pages</th>';
     echo '<th>Unique Views</th>';
     echo '<th>&nbsp;</th>';
     echo '</tr>';
     echo '</thead>';

     echo '<tbody>';

     if ($tests) {
       $i = 0;
       foreach ($tests as $test) {
         $ids = '';
         $tmp = get_posts(array('post_type' => 'optin-pages', 'meta_key' => '_ab-test', 'meta_value' => $test->slug));
         foreach ($tmp as $tmp2) {
           $ids .= $tmp2->ID . ',';
         }
         $ids = trim($ids, ',');
         $i++;
         if ($i % 2) {
          echo '<tr class="alternate">';
         } else {
           echo '<tr>';
         }
         echo '<td>' . $test->name . '</td>';
         echo '<td><a href="' . home_url() . $test->slug . '" target="_blank">' . $test->slug . ' </a></td>';
         if ($ids) {
           echo '<td><a title="View pages in this test" href="admin.php?id_filter=' . $ids .'"> ' . sizeof($tmp) . ' </a></td>';
         } else {
           echo '<td>0</td>';
         }
         echo '<td>' . (int) $test->views . ' (<a href="admin.php?page=wf-optin-ninja-stats&stats-test=' . $test->slug . '">details</a>)</td>';
         echo '<td><a class="optin-ab-shortcode" href="#" data-ab-disabled="' . $disabled. '" data-ab-id="' . $test->id . '" data-ab-href="' . $test->slug . '">View popup shortcode</a>&nbsp; <a class="optin-del-ab" href="admin.php?action=optin_ab_tests&action_do=delete&test-id=' . $test->id . '">Delete</a></td>';
         echo '</tr>';
       } // foreach
     }

     echo '</tbody>';
     echo '</table>';
     echo '<div class="tablenav bottom optin-bottom"><span class="displaying-num">' . sizeof($tests) . ' item' . (sizeof($tests) == 1? '': 's') . '</span></div>';

     if (!$tests) {
       echo '<p>No A/B test found. Use the form on the left to create new ones.</p>';
     }

     echo '</div>';
     echo '</div>';
     // Col Right End

     // Col Left Start
     echo '<div id="col-left">';
     echo '<div class="col-wrap">';

     echo '<form method="post" action="admin.php?action=optin_ab_tests">';
     echo '<div class="form-wrap">';
     echo '<h3>Add new A/B test</h3>';
     echo '<div class="form-field">
           <label for="tag-name">Name:</label>
           <input type="text" size="40" value="" id="tag-name" name="tag-name">
           <p>This is just for easier handling in the admin; it\'s not shown anywhere public.</p>
           </div>';
     echo '<div class="form-field">
           <label for="tag-url">URL:</label>
           <span style="display: inline-block;">' . home_url() . '</span>
           <input type="text" size="40" value="" id="tag-url" name="tag-url" style="display: inline-block;width:50%;" />
           <p>Please note that this slug will override any other WP objects on that URL. So if you leave it blank your homepage will open that A/B test. Slashes will be automatically added to the beginning and the end of the slug as needed.</p>
           </div>';

     echo '<p class="submit"><input type="submit" value="Add New A/B Test" class="button button-primary" id="submit" name="submit"></p><br>';

          echo '<p><b>Notes:</b><br>OptIn pages are added to A/B tests in the OptIn edit screen. Each OptIn can be assigned to only one test and they will be randomly shown to users on a per-session basis.<br>
          In order to perserve the continuity of A/B Tests and their statistics, tests can\'t be edited, only deleted and recreated.<br>
          Deleting an A/B Test does not delete the OptIn pages that are associated with it.</p>';

     echo '</form>';
     echo '</div>';
     echo '</div>';
     echo '</div>';
     // Col Left End

     echo '</div>';
   } // ab_tests
} // wf_optin_options_ab_tests