<?php
/*
Plugin name: OptIn Ninja - Auto Popups add-on
Version: 1.15
Author: Web factory Ltd
Plugin URI: http://optin-ninja.webfactoryltd.com/auto-popups-addon/
Author URI: http://www.webfactoryltd.com/
Description: Automatically open OptIn Pages in a popup based on numerous filters and triggers.
Text Domain: wf_opt_popups
Domain Path: lang
*/


define('WF_OPT_POPUPS_VERSION', '1.15');


class wf_optin_ninja_popups {
  static $footer_js = '';
  static $version = '1.15';

  static function init() {
    if (is_admin()) {
      // this plugin requires WP v3.8 and permalinks enabled
      if (!version_compare(get_bloginfo('version'), '3.8',  '>=')) {
        add_action('admin_notices', array(__CLASS__, 'min_version_error_wp'));
      }

      // this plugin requires OptIn Ninja v1.75
      if (!defined('WF_OPT_VERSION') || !version_compare(WF_OPT_VERSION, '1.75',  '>=')) {
        add_action('admin_notices', array(__CLASS__, 'min_version_error_optin_ninja'));
        return;
      }

      // aditional links in plugin description
      add_filter('plugin_action_links_' . basename(dirname(__FILE__)) . '/' . basename(__FILE__), array(__CLASS__, 'plugin_action_links'));
      add_filter('plugin_row_meta', array(__CLASS__, 'plugin_meta_links'), 10, 2);

      // misc hooks
      add_action('add_meta_boxes', array(__CLASS__, 'meta_boxes'), 20);
      add_filter('manage_optin-pages_posts_columns' , array(__CLASS__, 'manage_columns'), 20, 1);
      add_action('manage_optin-pages_posts_custom_column' , array(__CLASS__, 'manage_column'), 10, 2);
    } else {
      add_action('wp_enqueue_scripts', array(__CLASS__, 'frontend_enqueue_scripts'));
      add_action('wp_footer', array(__CLASS__, 'clean_footer_scripts'));
      add_action('wp_print_footer_scripts', array(__CLASS__, 'debug_popup'));
      add_action('wp_print_footer_scripts', array(__CLASS__, 'auto_popup_scripts'));
    }
  } // init


  // add links to plugin's description in plugins table
  static function plugin_meta_links($links, $file) {
    $documentation_link = '<a target="_blank" href="' . plugin_dir_url(__FILE__) . 'documentation/' .
                          '" title="View documentation">Documentation</a>';
    $support_link = '<a target="_blank" href="http://codecanyon.net/user/WebFactory#contact" title="Contact Web factory">Support</a>';

    if ($file == plugin_basename(__FILE__)) {
      $links[] = $documentation_link;
      $links[] = $support_link;
    }

    return $links;
  } // plugin_meta_links


  // add settings link to plugins page
  static function plugin_action_links($links) {
    $settings_link = '<a href="' . admin_url('edit.php?post_type=optin-pages') . '" title="Manage OptIn Pages">Manage OptIn Pages</a>';
    array_unshift($links, $settings_link);

    return $links;
  } // plugin_action_links


  // display warning if WP is outdated
  static function min_version_error_wp() {
    echo '<div id="message" class="error"><p>OptIn Ninja <b>requires WordPress version 3.8</b> or higher to function properly. You\'re using WordPress version ' . get_bloginfo('version') . '. Please <a href="' . admin_url('update-core.php') . '" title="Update WP core">update</a>.</p></div>';
  } // min_version_error_wp


  // display warning if OptIn Ninja is missing or outdated
  static function min_version_error_optin_ninja() {
    echo '<div id="message" class="error"><p>Auto Popups add-on for OptIn Ninja <b>requires OptIn Ninja version 1.75</b> or higher to function properly. Please activate OptIn Ninja plugin or purchase it on <a href="http://codecanyon.net/item/optin-ninja-ultimate-squeeze-page-generator/7615273?ref=WebFactory" title="OptIn Ninja">CodeCanyon</a>.</p></div>';
  } // min_version_error_optin_ninja


  // register meta box
  static function meta_boxes() {
    remove_meta_box('optin-ninja-popup', 'optin-pages', 'normal');
    add_meta_box('optin-ninja-popup', 'Auto Popup / Lightbox', array(__CLASS__, 'popup_meta_box'), 'optin-pages', 'normal', 'high');
  } // meta_boxes


  // modify columns for table view
  static function manage_columns($columns) {
    $new = array();

    foreach($columns as $key => $title) {
      $new[$key] = $title;
      if ($key == 'abtest') {
        $new['auto_popup'] = 'Auto Popup';
      }
    }

    return $new;
  } // manage_columns


  // get data for custom table columns
  static function manage_column($column, $post_id) {
    switch ($column) {
        case 'auto_popup':
          $tmp = get_post_meta($post_id, '_optin_auto_popup', true);
          if ($tmp) {
            echo 'yes';
          } else {
            echo 'no';
          }
        break;
    }
  } // manage_column


  // frontend scripts enqueue
  static function frontend_enqueue_scripts() {
     $options = get_option('wf-optin', array());

     if (isset($options['disable_popup']) && $options['disable_popup']) {
       return;
     }

     wp_enqueue_script('optin-ninja-inview', plugins_url('js/jquery.inview.min.js', __FILE__), array('jquery'), WF_OPT_POPUPS_VERSION, true);
     wp_enqueue_script('optin-ninja-ouibounce', plugins_url('js/ouibounce.min.js', __FILE__), array('jquery'), WF_OPT_POPUPS_VERSION, true);
  } // frontend_enqueue_scripts


  static function clean_footer_scripts() {
    global $post;
    $out = '';
    $inview = $ouibounce = false;

    if (@$post->post_type == 'optin-pages') {
      return;
    }

    $options = get_option('wf-optin', array());
    if (isset($options['disable_popup']) && $options['disable_popup']) {
      return;
    }

    $optins = get_posts(array('post_type' => 'optin-pages', 'meta_key' => '_optin_auto_popup', 'meta_value' => '1', 'posts_per_page' => 500, 'orderby' => 'title', 'status' => 'published', 'supress_filters' => 0));
    foreach ($optins as $optin) {
      // check optin's conditionals
      if (!self::check_page_conditionals($optin->ID) || !self::check_user_conditionals($optin->ID)) {
        continue;
      }

      $options = get_post_meta($optin->ID, 'wf_optin_meta', true);
      $options = $options['popup'];

      // URL is page's default or A/B test's if option is enabled
      if ($options['load_ab_test'] && get_post_meta($optin->ID, '_ab-test', true)) {
        $url = get_post_meta($optin->ID, '_ab-test', true);
      } else {
        $url = get_permalink($optin->ID);
        //$url = str_replace(home_url(), '', $url);
      }

      if ($options['on_pageload']) {
        $out .= 'setTimeout(function(){ wf_optin_open_popup("' . $url . '", "' . $options['position'] . '"); }, ' . $options['on_pageload'] . '); ';
      }

      if ($options['on_exit_intent']) {
        $ouibounce = true;
        $out .= 'ouibounce(false, { aggressive: true, timer: 2, callback: function() { wf_optin_open_popup("' . $url . '", "' . $options['position'] . '"); } }); ';
      }

      if ($options['in_view']) {
        $inview = true;
        $out .= ' var wf_popup_inview_' . $optin->ID . '=0; ';
        $out .= 'jQuery("' . $options['in_view'] . '").bind("inview", function(event, visible) {
                 if (!wf_popup_inview_' . $optin->ID . ') { wf_popup_inview_' . $optin->ID . '=1; wf_optin_open_popup("' . $url . '", "' . $options['position'] . '"); } }); ';
      }
    } // foreach optin

    if (!$ouibounce) {
      wp_dequeue_script('optin-ninja-ouibounce');
    }
    if (!$inview) {
      wp_dequeue_script('optin-ninja-inview');
    }
    self::$footer_js = $out;
  } // clean_footer_scripts


  // markup for popup options in meta box
  static function popup_meta_box() {
     global $post;

     $options = get_option('wf-optin', array());
     $meta = get_post_meta($post->ID, 'wf_optin_meta', true);
     if (!$meta) {
       $meta = array();
     }
     $field_generator = new wf_field_generator();

     if (isset($options['disable_popup']) && $options['disable_popup']) {
       echo '<p><b>Popups are disabled</b>. You can enable them in <a href="' . admin_url('edit.php?post_type=optin-pages&page=wf-optin-ninja-settings') . '">settings</a>.</p>';
     } else {
       $link = get_permalink($post->ID);
       $link = str_replace(home_url(), '', $link);

       $posts_tmp = get_posts(array('posts_per_page' => 500, 'orderby' => 'title', 'order' => 'ASC'));
       $posts[0] = 'Disable rule';
       foreach ($posts_tmp as $post_tmp) {
         $posts[$post_tmp->ID] = $post_tmp->post_title;
       }

       echo '<h4>Manually opening a popup via link</h4>';
       echo '<ul><li>any link can open an OptIn in a popup if you add the <i>optin-popup</i> class to it and set the link (href parameter) to <i>'. $link .'</i>. Example:<br>
       &lt;a href="' . $link . '" class="optin-popup"&gt;click here&lt;/a&gt;</li>';
       echo '<li>you can also use the shortcode:<br>
       <i>[optin-popup id="' . $post->ID . '" class="optional-class" position="center"]click here[/optin-popup]</i></li>';
       echo '<li>use the <i>wf_optin_open_popup( \'' . $link . '\', \'center\' )</i> JS function to open popups from your custom JS code</li>';
       echo '<li>only one popup can be open at the same time; second one can\'t be opened until first one is closed</li>';
       echo '<li>available values for the position parameter are: left top, center top, right top, left center, center center, right center, left bottom, center bottom, right bottom, left, center, right</li>';
       echo '</ul>';

       echo '<h4>Automatically opening a popup based on predefined conditions</h4>';
       echo '<ul><li>multiple OptIns can be automatically opened on a single page, but only one at a time; second popup can\'t be opened until the first one is closed</li>';
       echo '<li>if you want to make sure conditions are properly set open any page on your site with the "debug-optin-popup-' . get_option('wf_opt_popups_unique') . '" parameter (ie <a href="' . get_home_url() . '/?debug-optin-popup-' . get_option('wf_opt_popups_unique') .'" target="_blank">' . get_home_url() . '/?debug-optin-popup-' . get_option('wf_opt_popups_unique') . '</a>) and you\'ll get detailed debug info</li></ul>';

       echo $field_generator->start_row();
       echo $field_generator->generate('label', 'Enable Automatic Popup:', 'popup', '_optin_auto_popup', '', '', '', true, '', '');
       echo $field_generator->generate('dropdown', '', 'popup', '_optin_auto_popup', array('0' => 'No', '1' => 'Yes'), '', '', '', 'This setting only enables automatic popups via conditional rules; you still have to configure the rules below to get an OptIn to show up automatically.', true);
       echo $field_generator->end_row();
       
       echo $field_generator->start_row();
       echo $field_generator->generate('label', 'Automatically End Popop Date:', 'popup', 'last_date', '', '', '', true, '', '');
       echo $field_generator->generate('date', '', 'popup', 'last_date', '', '', true, false, 'If a date is defined the popup will stop automatically opening on the day after. Only use the following date format: yyyy-mm-dd');
       echo $field_generator->end_row();

       echo '<div id="optin-ninja-conditional-options">';
       echo $field_generator->start_row();
       echo $field_generator->generate('label', 'A/B Test:', 'popup', 'load_ab_test', '', '', '', true, '', '');
       echo $field_generator->generate('dropdown', '', 'popup', 'load_ab_test', array('0' => 'Open this OptIn Page, not its A/B Test', '1' => 'Open A/B Test that this OptIn Page belongs to (if applicable)'), '', true, false, 'If the page belongs to an A/B Test you can opt to load that test in the popup, instead of the page itself.');
       echo $field_generator->end_row();

       echo '<hr>';
       echo $field_generator->start_row();
       echo $field_generator->generate('label', 'Filter Subscribed Users:', 'popup', 'filter_subscribed', '', '', '', true, '', '');
       echo $field_generator->generate('dropdown', '', 'popup', 'filter_subscribed', array('0' => 'Show to all users', '1' => 'Do not show to users that already subscribed to this OptIn'), '', true, false, 'If the user has already subscribed to the list attached to this OptIn there is no reason to show him the popup again.');
       echo $field_generator->end_row();

       echo $field_generator->start_row();
       echo $field_generator->generate('label', 'Filter Logged in Users:', 'popup', 'filter_loggedin', '', '', '', true, '', '');
       echo $field_generator->generate('dropdown', '', 'popup', 'filter_loggedin', array('0' => 'Show to all users', '1' => 'Show only to logged in users', '2' => 'Show only to logged out users'), '', true, false, 'If you want to show a popup only to your logged in users enable this option.');
       echo $field_generator->end_row();

       echo $field_generator->start_row();
       echo $field_generator->generate('label', 'Limit per User:', 'popup', 'limit_per_user', '', '', '', true, '', '');
       echo $field_generator->generate('dropdown', '', 'popup', 'limit_per_user', array('0' => 'Show unlimited times', '1' => 'Show max 1 time per user', '2' => 'Show max 2 times per user', '3' => 'Show max 3 times per user', '4' => 'Show max 4 times per user', '5' => 'Show max 5 times per user'), '', true, false, 'Maximum amount of times a user can see the popup.', '');
       echo $field_generator->end_row();

       echo '<hr>';
       echo '<p>By default, if none of the option below are set, the popup will be shown on all pages. If you enable any option then that condition has to be met in order for the popup to show. Ie enabling home page and search page options means the popup will only be shown on the home and search pages.</p>';
       echo $field_generator->start_row();
       echo $field_generator->generate('label', 'Show on Home/Front Page:', 'popup', 'on_home', '', '', '', true, '', '');
       echo $field_generator->generate('dropdown', '', 'popup', 'on_home', array('0' => 'Disable rule', '1' => 'Enabled'), '', true, false, 'Popup will be shown when is_home() or is_front_page() conditionals return true.', '');
       echo $field_generator->end_row();

       echo $field_generator->start_row();
       echo $field_generator->generate('label', 'Show on Archive Pages:', 'popup', 'on_archive', '', '', '', true, '', '');
       echo $field_generator->generate('dropdown', '', 'popup', 'on_archive', array('0' => 'Disable rule', '1' => 'Enabled'), '', true, false, 'Popup will be shown when is_archive() conditional returns true.', '');
       echo $field_generator->end_row();

       echo $field_generator->start_row();
       echo $field_generator->generate('label', 'Show on Search Page:', 'popup', 'on_search', '', '', '', true, '', '');
       echo $field_generator->generate('dropdown', '', 'popup', 'on_search', array('0' => 'Disable rule', '1' => 'Enabled'), '', true, false, 'Popup will be shown when is_search() conditional returns true.', '');
       echo $field_generator->end_row();

       echo $field_generator->start_row();
       echo $field_generator->generate('label', 'Show on Post:', 'popup', 'on_post', '', '', '', true, '', '');
       echo $field_generator->generate('dropdown', '', 'popup', 'on_post', $posts, '', true, false, 'Popup will be shown on the selected post.', '');
       echo $field_generator->end_row();

       echo $field_generator->start_row();
       echo $field_generator->generate('label', 'Show on Page:', 'popup', 'on_page', '', '', '', true, '', '');
       echo '<div class="col-6">';
       wp_dropdown_pages(array('selected' => @$meta['popup']['on_page'], 'name' => 'wf_optin_meta[popup][on_page]', 'id' => 'popup_on_page', 'show_option_none' => 'Disable rule', 'option_none_value' => '0'));
       echo '<p class="description">Popup will be shown on the selected page.</p>';
       echo '</div>';
       echo $field_generator->end_row();

       echo $field_generator->start_row();
       echo $field_generator->generate('label', 'Show on/in Category:', 'popup', 'on_category', '', '', '', true, '', '');
       echo '<div class="col-6">';
       wp_dropdown_categories(array('hierarchical' => 1, 'show_count' => 1, 'hide_empty' => 0, 'selected' => @$meta['popup']['on_category'], 'name' => 'wf_optin_meta[popup][on_category]', 'id' => 'popup_on_category', 'show_option_none' => 'Disable rule', 'option_none_value' => '0'));
       echo '<p class="description">Popup up will be shown on the category archive pages and on all posts that are a part of that category.</p>';
       echo '</div>';
       echo $field_generator->end_row();

       echo '<hr>';
       echo '<p>If all conditions/filters above are met then the pop will be trigered by one of the events below. You have to enable at least one event for the popup to show.</p>';

       echo $field_generator->start_row();
       echo $field_generator->generate('label', 'Popup position:', 'popup', 'position', '', '', '', true, '', '');
       echo $field_generator->generate('dropdown', '', 'popup', 'position', array('center center' => 'Center center', 'left top' => 'Left top', 'center top' => 'Center top', 'right top' => 'Right top', 'left center' => 'Left center', 'right center' => 'Right center', 'left bottom' => 'Left bottom', 'center bottom' => 'Center bottom', 'right bottom' => 'Right bottom'), '', true, false, 'Default popup position is in the middle of the screen but you can choose any one of 9 options.', '');
       echo $field_generator->end_row();

       echo $field_generator->start_row();
       echo $field_generator->generate('label', 'Show on Page Load:', 'popup', 'on_pageload', '', '', '', true, '', '');
       echo $field_generator->generate('dropdown', '', 'popup', 'on_pageload', array('0' => 'Disabled', '1' => 'Show imediatelly after page loads', '2000' => 'Show after 2 seconds', '3000' => 'Show after 3 seconds', '5000' => 'Show after 5 seconds', '10000' => 'Show after 10 seconds', '20000' => 'Show after 20 seconds', '30000' => 'Show after 30 seconds', '45000' => 'Show after 45 seconds', '60000' => 'Show after a minute'), '', true, false, 'Shows the popup after page loads, imediatelly or after a predefined time.', '');
       echo $field_generator->end_row();

       echo $field_generator->start_row();
       echo $field_generator->generate('label', 'Show on Exit Intent:', 'popup', 'on_exit_intent', '', '', '', true, '', '');
       echo $field_generator->generate('dropdown', '', 'popup', 'on_exit_intent', array('0' => 'Disabled', '1' => 'Enabled'), '', true, false, 'Popup will be shown when user\'s mouse gesture suggest that he is leaving the page by moving the cursor to the upper part of the screen in order to close the tab/window. This does not block users from closing the tab/window.', '');
       echo $field_generator->end_row();

       echo $field_generator->start_row();
       echo $field_generator->generate('label', 'Show When Content Comes in View:', 'popup', 'in_view', '', '', '', true, '', '');
       echo $field_generator->generate('input', '', 'popup', 'in_view', '', '', '', '', 'Usefull when users scrolls down to the footer or some other section of the page. Enter any selector/expression that jQuery can handle, ie: #my-box or .fancy-section; without any quotes! Please double-check your input as it is impossible for the plugin to verify it.', '');
       echo $field_generator->end_row();

       echo '</div>';
       echo '<br>';
       wf_field_generator::save_button();
     } // if popup
  } // popup_meta_box


  // load popups that pass conditional options
  static function auto_popup_scripts() {
    if (self::$footer_js) {
      echo "\n" . '<script id="wf_optin_popups_js" type="text/javascript"> ';
      echo self::$footer_js;
      echo ' </script>' . "\n";
    }
  } // auto_popup_scripts


  // check if popup should be displayed based on user based conditional options
  static function check_user_conditionals($popup_id) {
    $options = get_post_meta($popup_id, 'wf_optin_meta', true);
    $options = $options['popup'];

    if (!empty($options['last_date']) && strlen($options['last_date']) <= 10 && strlen($options['last_date']) >=8) {
      if (date('Y-m-d') > $options['last_date']) {
        return false;
      }
    }
    
    if ($options['filter_subscribed']) {
      if (self::is_user_subscribed($popup_id)) {
        return false;
      }
    }

    if ($options['filter_loggedin']) {
      if ($options['filter_loggedin'] == 1 && !is_user_logged_in()) {
        return false;
      } elseif ($options['filter_loggedin'] == 2 && is_user_logged_in()) {
        return false;
      }
    }

    if ($options['limit_per_user']) {
      if (self::get_user_popup_views($popup_id) >= $options['limit_per_user']) {
        return false;
      }
    }

    return true;
  } // check_user_conditionals


  // check if popup should be displayed based on page based conditional options
  static function check_page_conditionals($popup_id) {
    global $post;
    $checked = false;
    $options = get_post_meta($popup_id, 'wf_optin_meta', true);
    $options = $options['popup'];
    
    if (!empty($options['last_date']) && strlen($options['last_date']) <= 10 && strlen($options['last_date']) >=8) {
      if (date('Y-m-d') > $options['last_date']) {
        return false;
      }
    }

    if ($options['on_home']) {
      $checked = true;
      if (is_home() || is_front_page()) {
        return true;
      }
    }

    if ($options['on_archive']) {
      $checked = true;
      if (is_archive()) {
        return true;
      }
    }

    if ($options['on_search']) {
      $checked = true;
      if (is_search()) {
        return true;
      }
    }

    if ($options['on_post']) {
      $checked = true;
      if($options['on_post'] == $post->ID) {
        return true;
      }
    }

    if ($options['on_page']) {
      $checked = true;
      if($options['on_page'] == $post->ID) {
        return true;
      }
    }

    if ($options['on_category']) {
      $checked = true;
      if(is_category($options['on_category']) || in_category($options['on_category'], $popup_id)) {
        return true;
      }
    }

    if ($checked == true) {
      return false;
    } else {
      return true;
    }
  } // check_page_conditionals


  // debugs conditional statements for popup
  static function debug_popup() {
    global $post;

    if (@$post->post_type == 'optin-pages' || !isset($_GET['debug-optin-popup-' . get_option('wf_opt_popups_unique')])) {
      return;
    }

    $options = get_option('wf-optin', array());

    $out = '<div id="wf_optin_popup_debug" style="z-index: 9999; position: fixed; border: 1px solid red; top: 0; left: 0; background-color: #eee; color: #000; padding: 20px;">';
    if (isset($options['disable_popup']) && $options['disable_popup']) {
      $out .= 'Popups are globally <b>disabled</b> in plugin\'s options. No popups will be shown.<br><br>';
    }
    $optins = get_posts(array('post_type' => 'optin-pages', 'meta_key' => '_optin_auto_popup', 'meta_value' => '1', 'posts_per_page' => 500, 'orderby' => 'title', 'status' => 'published', 'supress_filters' => 0));
    $out .= 'OptIn Pages with enabled auto popup: ' . sizeof($optins) . '.<br>Debugger only shows conditionals that are enabled and affect popup visibility. Ones that are disabled are ignored.<br>';
    foreach ($optins as $optin) {
      $options = get_post_meta($optin->ID, 'wf_optin_meta', true);
      $options = $options['popup'];

      $out .= '<br><b>' . $optin->post_title . '</b> (ID: ' . $optin->ID .  ')';
      if ($options['load_ab_test']) {
        $out .= '<br>A/B test (if defined) will be loaded instead of page';
      }
      if ($options['filter_subscribed']) {
        $out .= '<br>Filter Subscribed Users - ';
        if (self::is_user_subscribed($optin->ID)) {
          $out .= '<u>failed</u>, user is already subscribed';
        } else {
          $out .= 'passed, user is not subscribed';
        }
      }
      if ($options['filter_loggedin']) {
        if ($options['filter_loggedin'] == 1 && !is_user_logged_in()) {
          $out .= '<br>Filter Logged in Users - <u>failed</u>, user not logged in';
        } elseif ($options['filter_loggedin'] == 2 && is_user_logged_in()) {
          $out .= '<br>Filter Logged in Users - <u>failed</u>, user logged in';
        } else {
          $out .= '<br>Filter Logged in Users - passed';
        }
      }
      if ($options['limit_per_user']) {
        $out .= '<br>Limit per User - ';
        if (self::get_user_popup_views($optin->ID) < $options['limit_per_user']) {
          $out .= 'passed '. self::get_user_popup_views($optin->ID) . '/' . $options['limit_per_user'];
        } else {
          $out .= '<u>failed</u> '. self::get_user_popup_views($optin->ID) . '/' . $options['limit_per_user'];
        }
      }
      if ($options['on_home']) {
        if (!is_home() && !is_front_page()) {
          $out .= '<br>Show on Home/Front Page - <u>failed</u>, not home, not front page';
        } else {
          $out .= '<br>Show on Home/Front Page - passed';
        }
      }
      if ($options['on_archive']) {
        if (!is_archive()) {
          $out .= '<br>Show on Archive Pages - <u>failed</u>';
        } else {
          $out .= '<br>Show on Archive Pages - passed';
        }
      }
      if ($options['on_search']) {
        if (!is_search()) {
          $out .= '<br>Show on Search Page - <u>failed</u>';
        } else {
          $out .= '<br>Show on Search Page - passed';
        }
      }
      if ($options['on_post']) {
        if($options['on_post'] != $post->ID) {
          $out .= '<br>Show on Post - <u>failed</u>, ID ' . $options['on_post'] . ' != ' . (int) $post->ID;
        } else {
          $out .= '<br>Show on Post - passed';
        }
      }
      if ($options['on_page']) {
        if($options['on_page'] != $post->ID) {
          $out .= '<br>Show on Page - <u>failed</u>, ID ' . $options['on_page'] . ' != ' . (int) $post->ID;
        } else {
          $out .= '<br>Show on Page - passed';
        }
      }
      if ($options['on_category']) {
        if(!is_category($options['on_category']) && !in_category($options['on_category'], $post->ID)) {
          $out .= '<br>Show on/in Category - <u>failed</u>, not on_category() or in_category()';
        } else {
          $out .= '<br>Show on/in Category - passed';
        }
      }
    }

    $out .= '</div>';
    echo $out;
  } // debug_popup


  // checks if user has already subscribed to a selected OptIn
  static function is_user_subscribed($post_id) {
    $cookie = unserialize($_COOKIE['optin_ninja_subscribed']);
    if (isset($cookie[$post_id]) && $cookie[$post_id]) {
      return true;
    } else {
      return false;
    }
  } // is_user_subscribed


  // returns the number of times user has seen a popup
  static function get_user_popup_views($post_id) {
    $cookie = unserialize($_COOKIE['optin_ninja_views']);
    if (isset($cookie[$post_id])) {
      return $cookie[$post_id];
    } else {
      return 0;
    }
  } // is_user_subscribed


  // setup everything when plugin is  activated
  static function activate() {
    add_option('wf_opt_popups_unique', rand(10000, 99999));
  } // activate


  // clean up on deactivate
  static function deactivate() {
    delete_option('wf_opt_popups_unique');
  } // deactivate
} // class wf_optin_ninja_popups


add_action('init', array('wf_optin_ninja_popups', 'init'));
register_activation_hook(__FILE__, array('wf_optin_ninja_popups', 'activate'));
register_deactivation_hook(__FILE__, array('wf_optin_ninja_popups', 'deactivate'));