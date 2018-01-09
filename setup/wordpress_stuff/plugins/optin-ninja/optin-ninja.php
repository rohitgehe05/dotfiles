<?php
/*
Plugin name: OptIn Ninja
Version: 2.20
Author: Web factory Ltd
Plugin URI: http://optin-ninja.webfactoryltd.com/
Author URI: http://www.webfactoryltd.com/
Description: Create highly optimised, 2-step squeeze and landing pages in just a few clicks.
Text Domain: wf_opt
Domain Path: lang
*/


define('WF_OPT_APP', 'af92e2e4'); // WebFactory Aweber APP id; create your own app & replace the APP ID
define('WF_OPT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WF_OPT_PLUGINURL', plugins_url('', __FILE__));

define('WF_OPT_TEMPLATEURL', WF_OPT_PLUGINURL . '/templates/');

define('WF_OPT_TEMPLATEPATH', plugin_dir_path(__FILE__) . 'templates/');
define('WF_OPT_TEMPLATES_SERVER', 'http://optin-ninja.webfactoryltd.com/');


define('WF_OPT_TEMPLATES_OPTION', 'optin_ninja_templates');
define('WF_OPT_INSTALLED_TEMPLATES', 'optin_ninja_installed_templates');



define('WF_OPT_SIGNUPS', 'optin_ninja_signups');
define('WF_OPT_STATS', 'optin_ninja_stats');
define('WF_OPT_AB', 'optin_ninja_ab_tests');
define('WF_OPT_VERSION', '2.20');


// meta boxes
require_once WF_OPT_PLUGIN_DIR . 'meta-boxes/optin-meta-box-general-settings.php';
require_once WF_OPT_PLUGIN_DIR . 'meta-boxes/optin-meta-box-background.php';
require_once WF_OPT_PLUGIN_DIR . 'meta-boxes/optin-meta-box-templates.php';
require_once WF_OPT_PLUGIN_DIR . 'meta-boxes/optin-meta-box-first-box.php';
require_once WF_OPT_PLUGIN_DIR . 'meta-boxes/optin-meta-box-second-box.php';
require_once WF_OPT_PLUGIN_DIR . 'meta-boxes/optin-meta-box-form-box.php';
require_once WF_OPT_PLUGIN_DIR . 'meta-boxes/optin-meta-box-autoresponder.php';
require_once WF_OPT_PLUGIN_DIR . 'meta-boxes/optin-meta-box-popup.php';
require_once WF_OPT_PLUGIN_DIR . 'meta-boxes/optin-meta-box-links.php';
require_once WF_OPT_PLUGIN_DIR . 'meta-boxes/optin-meta-box-notifications.php';
require_once WF_OPT_PLUGIN_DIR . 'optin-field-generator.php';
require_once WF_OPT_PLUGIN_DIR . 'optin-admin-ajax.php';

// option pages
require_once WF_OPT_PLUGIN_DIR . 'option-pages/optin-dashboard.php';
require_once WF_OPT_PLUGIN_DIR . 'option-pages/optin-options-page-ab-tests.php';
require_once WF_OPT_PLUGIN_DIR . 'option-pages/optin-options-page-subscribers.php';
require_once WF_OPT_PLUGIN_DIR . 'option-pages/optin-options-page-stats.php';
require_once WF_OPT_PLUGIN_DIR . 'option-pages/optin-options-page.php';

// frontend AJAX endpoints
require_once WF_OPT_PLUGIN_DIR . 'optin-api-ajax.php';


class wf_optin_ninja {
  static $version = '2.20';

  static function init() {
    self::register_post_type();
  
  
    if (is_admin()) {
      // this plugin requires WP v4.0 and permalinks enabled
      if (!version_compare(get_bloginfo('version'), '4.5',  '>=')) {
        add_action('admin_notices', array(__CLASS__, 'min_version_error_wp'));
        return;
      }
      if (!get_option('permalink_structure')) {
        add_action('admin_notices', array(__CLASS__, 'no_permalinks_error'));
      }
      
      // dashboard widget
      add_action('wp_dashboard_setup', array(__CLASS__, 'add_dashboard_widgets'));

      // aditional links in plugin description
      add_filter('plugin_action_links_' . basename(dirname(__FILE__)) . '/' . basename(__FILE__), array(__CLASS__, 'plugin_action_links'));
      add_filter('plugin_row_meta', array(__CLASS__, 'plugin_meta_links'), 10, 2);

      // options related hooks
      add_action('wp_ajax_optin_reset_stats', array('wf_optin_ajax', 'reset_stats_ajax'));
      add_action('wp_ajax_optin_delete_subs', array('wf_optin_ajax', 'delete_subs_ajax'));
    add_action('wp_ajax_optin_delete_sub', array('wf_optin_ajax', 'delete_sub_ajax'));
      add_action('wp_ajax_optin_dismiss_pointer', array(__CLASS__, 'dismiss_pointer_ajax'));
      add_action('wp_ajax_optin_import_textures', array('wf_optin_options', 'import_textures_ajax'));
      add_action('wp_ajax_optin_import_backgrounds', array('wf_optin_options', 'import_backgrounds_ajax'));

      // misc hooks
      add_action('add_meta_boxes', array(__CLASS__, 'meta_boxes'));
      add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_scripts'));
      add_action('admin_print_footer_scripts', array('wf_optin_options_stats', 'print_flot_scripts'));
      add_action('save_post', array(__CLASS__, 'update_optin_meta'));
      add_action('admin_menu', array(__CLASS__, 'add_options_page'));
      add_filter('custom_menu_order', array(__CLASS__, 'custom_menu_order'));
      add_filter('post_row_actions', array(__CLASS__, 'post_row_actions'), 10, 2);
      add_action('admin_action_optin_clone', array(__CLASS__, 'duplicate_post'));
      add_action('admin_action_optin_ab_tests', array('wf_optin_options_ab_tests', 'process_ab_actions'));
      add_action('admin_action_optin_refresh_lists', array('wf_optin_ninja_form_box', 'refresh_lists'));
      add_filter('posts_where', array(__CLASS__, 'filter_optins_by_id'), 10, 2);
      add_filter('manage_optin-pages_posts_columns', array(__CLASS__, 'manage_columns'));
      add_action('manage_optin-pages_posts_custom_column' , array(__CLASS__, 'manage_column'), 10, 2);
      add_filter('post_updated_messages', array(__CLASS__, 'post_updated_messages'));
      add_action('post_submitbox_start', array(__CLASS__, 'post_submitbox'));
      add_filter('mce_css', array(__CLASS__, 'filter_mce_css'));
    
      add_action('admin_notices', array(__CLASS__, 'notice_get_addon'));
   
      add_action('admin_action_wf_optin_dismiss_notice_popup', array(__CLASS__, 'dismiss_notice'));
      add_action('admin_action_wf_optin_dismiss_notice_fields', array(__CLASS__, 'dismiss_notice_fields'));
      
      // fix
      self::load_google_fonts();
    } else {
      // custom optin URL/template logic
      add_action('send_headers', array(__CLASS__, 'headers_set'));
      add_action('template_redirect', array(__CLASS__, 'redirect_template'), 5);
      add_action('wp_enqueue_scripts', array(__CLASS__, 'frontend_enqueue_scripts'));
      add_shortcode('optin-popup', array(__CLASS__, 'shortcode_optin_popup'));
      add_shortcode('optin_popup', array(__CLASS__, 'shortcode_optin_popup'));
      add_shortcode('optin_test_popup', array(__CLASS__, 'shortcode_optin_test_popup'));
      add_shortcode('optin-test-popup', array(__CLASS__, 'shortcode_optin_test_popup'));
    }
    add_action('wp_footer', array(__CLASS__, 'clean_scripts_queue'), 50);
    // frontend AJAX endpoints
    add_action('wp_ajax_optin_subscribe', array('wf_optin_api', 'subscribe_wrapper'));
    add_action('wp_ajax_nopriv_optin_subscribe', array('wf_optin_api', 'subscribe_wrapper'));

    add_action('wp_ajax_optin_step2_stats', array('wf_optin_api', 'step2_stats'));
    add_action('wp_ajax_nopriv_optin_step2_stats', array('wf_optin_api', 'step2_stats'));
    add_action('wp_ajax_optin_step3_stats', array('wf_optin_api', 'step3_stats'));
    add_action('wp_ajax_nopriv_optin_step3_stats', array('wf_optin_api', 'step3_stats'));
    add_action('wp_ajax_wf_opt_check_licence', array('wf_optin_ninja_optin_templates', 'check_license'));
    add_action('wp_ajax_wf_opt_download_templates', array('wf_optin_ninja_optin_templates', 'download_templates'));
    add_action('wp_ajax_wf_opt_download_template_files', array('wf_optin_ninja_optin_templates', 'download_template_files'));
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
    $settings_link = '<a href="' . admin_url('admin.php?page=wf-optin-ninja') . '" title="Manage OptIn Pages">Manage OptIn Pages</a>';
    array_unshift($links, $settings_link);

    return $links;
  } // plugin_action_links


  // display warning if WP is outdated
  static function min_version_error_wp() {
    echo '<div id="message" class="error"><p>OptIn Ninja <b>requires WordPress version 4.5</b> or higher to function properly. You\'re using WordPress version ' . get_bloginfo('version') . '. Please <a href="' . admin_url('update-core.php') . '" title="Update WP core">update</a>.</p></div>';
  } // min_version_error_wp

  
  // register dashboard widget
  static function add_dashboard_widgets() {
    wp_add_dashboard_widget('optin_ninja', 'OptIn Ninja Quick Stats', array(__CLASS__, 'dashboard_widget'));
  }  // add_dashboard_widgets
  
  
  // echo dashboard widget
  static function dashboard_widget() {
    global $wpdb;
    
    $views = (int) $wpdb->get_var('SELECT SUM(views) FROM ' . $wpdb->prefix . WF_OPT_STATS . ' WHERE date = "' . date('Y-m-d', current_time('timestamp')) . '"');
    $views2 = (int) $wpdb->get_var('SELECT SUM(views_box2) FROM ' . $wpdb->prefix . WF_OPT_STATS . ' WHERE date = "' . date('Y-m-d', current_time('timestamp')) . '"');
    $conv = (int) $wpdb->get_var('SELECT SUM(conversion) FROM ' . $wpdb->prefix . WF_OPT_STATS . ' WHERE date = "' . date('Y-m-d', current_time('timestamp')) . '"');
    $subs = (int) $wpdb->get_var('SELECT COUNT(id) FROM ' . $wpdb->prefix . WF_OPT_SIGNUPS);
    $optins = (int) @wp_count_posts('optin-pages')->publish;
    
    echo '<ul>';
    echo '<li><b>Unique views today:</b> ' . $views . '</li>';
    echo '<li><b>Unique views (step #2) today:</b> ' . $views2 . '</li>';
    echo '<li><b>Conversions today:</b> ' . $conv . '</li>';
    echo '<li><b>Total OptIn Pages:</b> ' . $optins . '</li>';
    echo '<li><b>Total subscribers in local DB:</b> ' . $subs . '</li>';
    echo '</ul>';
    
    echo '<p><a href="' . admin_url('edit.php?post_type=optin-pages') . '">OptIn Pages</a> | <a href="' .  admin_url('admin.php?page=wf-optin-ninja-ab-tests') . '">A/B Tests</a> | <a href="' .  admin_url('admin.php?page=wf-optin-ninja-subscribers') . '">Subscribers</a> | <a href="' .  admin_url('admin.php?page=wf-optin-ninja-stats') . '">Statistics</a> | <a href="' .  admin_url('admin.php?page=wf-optin-ninja-settings') . '">Settings</a></p>';
  } // dashboard_widget

  
  // display warning if WP is outdated
  static function notice_get_addon() {
  $screen = get_current_screen(); 
  if ($screen->base == 'toplevel_page_wf-optin-ninja' || (strpos($screen->base, 'optin-ninja') === false && strpos($screen->id, 'optin-pages') === false)) {
    return;
  }
    
    if (!class_exists('wf_optin_ninja_popups') && !get_option('wf_optin_hide_popup_notice', false)) {
      $dismiss_url = add_query_arg(array('action' => 'wf_optin_dismiss_notice_popup', 'redirect' => $_SERVER['REQUEST_URI']), admin_url('admin.php'));
      echo '<div class="updated"><p>If you want to <b>get more subscribers faster</b> install the OptIn Ninja <b>Auto Popups add-on</b>. It will help you automatically show popups to visitors based on numerous triggers &amp; options.<br> <a href="http://optin-ninja.webfactoryltd.com/auto-popups-addon/" title="Get the Auto Popups add-on" class="button button-primary" target="_blank" style="margin-top: 15px;">Get the add-on</a> &nbsp;&nbsp;<a href="' . esc_url($dismiss_url) . '">I don\'t want more subscribers faster</a></p></div>';     
      return;
    }
        
    
    if (!class_exists('wf_optin_ninja_fields') && !get_option('wf_optin_hide_fields_notice', false)) {
      $dismiss_url = add_query_arg(array('action' => 'wf_optin_dismiss_notice_fields', 'redirect' => $_SERVER['REQUEST_URI']), admin_url('admin.php'));

      echo '<div class="updated"><p>If you want to add an unlimited number of <b>custom fields</b> to any OptIn page install the OptIn Ninja <b>Custom Form Fields add-on</b>. It works with all autoresponders and custom forms.<br> 
    <a href="http://optin-ninja.webfactoryltd.com/custom-form-fields-addon/" title="Get the Custom Form Fields add-on" class="button button-primary" target="_blank" style="margin-top: 15px;">Get more info from your subscribers</a> 
    &nbsp;&nbsp;<a href="' . esc_url($dismiss_url) . '">I don\'t want to collect extra info about my subscribers</a></p></div>';        
    }
  } // notice_get_popup


  static function dismiss_notice() {
    update_option('wf_optin_hide_popup_notice', true);

    if ($_GET['redirect']) {
      wp_redirect($_GET['redirect']);
    } else {
      wp_redirect(admin_url());
    }
    exit;
  } // dismiss_notice
  
  static function dismiss_notice_fields() {
    update_option('wf_optin_hide_fields_notice', true);

    if ($_GET['redirect']) {
      wp_redirect($_GET['redirect']);
    } else {
      wp_redirect(admin_url());
    }
    exit;
  } // dismiss_notice


  static function no_permalinks_error() {
    echo '<div id="message" class="error"><p>OptIn Ninja <b>requires permalinks</b> to be enabled to function properly. Please <a href="' . admin_url('options-permalink.php') . '" title="Update WP core">enable permalinks</a>.</p></div>';
  } // min_version_error_wp


  // modify action links below optin in table view
  static function post_row_actions($actions, $post) {
    if ($post->post_type != 'optin-pages') {
      return $actions;
    }

    unset($actions['inline hide-if-no-js']);
    if (!empty($actions['view'])) {
      $actions['view'] = str_replace('>', ' target="_blank">', $actions['view']);
    }
    $actions['optin_pages_clone'] = '<a href="' . admin_url('admin.php?action=optin_clone&amp;post=' . $post->ID) . '">Clone</a>';
    $actions['optin_pages_stats'] = '<a href="' . admin_url('admin.php?page=wf-optin-ninja-stats&stats-page=' . $post->ID) . '">View statistics</a>';

    return $actions;
  } // post_row_actions


  // add clone link to submitbox
  static function post_submitbox() {
    global $post;

    if (isset($_GET['post']) && $post->post_type == 'optin-pages') {
      echo '<div><a id="clone-optin" href="' . admin_url('admin.php?action=optin_clone&amp;redirect=1&amp;post=' . $post->ID) . '">Clone OptIn';
      echo '</a></div>';
    }
  } // post_submitbox


  // prepare for post cloning
  static function duplicate_post($status = ''){
    if (!(isset($_GET['post']) || isset($_POST['post']) || (isset($_REQUEST['action']) && 'optin_clone' == $_REQUEST['action']))) {
      wp_die('No post to duplicate!');
    }

    $id = (isset($_GET['post']) ? $_GET['post'] : $_POST['post']);
    $post = get_post($id);

    if (isset($post) && $post != null) {
      $new_id = self::duplicate_post_create_duplicate($post);

      if (isset($_GET['redirect']) && $_GET['redirect']) {
        wp_redirect( admin_url( 'post.php?action=edit&post=' . $new_id ) );
      } else {
        wp_redirect( admin_url( 'edit.php?post_type='.$post->post_type) );
      }
      exit;
    } else {
      wp_die('Copy creation failed, could not find original');
    }
  } // duplicate post


  // do actual post cloning
  static function duplicate_post_create_duplicate($post, $status = '') {
    if ($post->post_type == 'revision') {
      return;
    }

    $new_post_author = wp_get_current_user();

    $new_post = array(
    'menu_order' => $post->menu_order,
    'comment_status' => $post->comment_status,
    'ping_status' => $post->ping_status,
    'post_author' => $new_post_author->ID,
    'post_content' => $post->post_content,
    'post_excerpt' => $post->post_excerpt,
    'post_mime_type' => $post->post_mime_type,
    'post_parent' => $post->post_parent,
    'post_password' => $post->post_password,
    'post_status' => 'draft',
    'post_title' => 'Clone of ' . $post->post_title,
    'post_type' => $post->post_type,
    );

    $new_post_id = wp_insert_post($new_post);

    $post_meta_keys = get_post_custom_keys($post->ID);
    if (!empty($post_meta_keys)) {
      foreach ($post_meta_keys as $meta_key) {
        $meta_values = get_post_custom_values($meta_key, $post->ID);
        foreach ($meta_values as $meta_value) {
          $meta_value = maybe_unserialize($meta_value);
          add_post_meta($new_post_id, $meta_key, $meta_value);
        }
      }
    }

    return $new_post_id;
  }


  // modify columns for table view
  static function manage_columns($columns) {
    unset($columns['date']);

    $columns['custom_url'] = 'Custom URL';
    $columns['abtest'] = 'A/B Test';
    $columns['views'] = 'Unique Views';
    $columns['conversions'] = 'Conversions';
    $columns['conversion_rate'] = 'Conversion Rate';
    $columns['stats'] = 'Quick Stats';
    $columns['date'] = 'Date';

    return $columns;
  } // manage_columns


  // get data for custom table columns
  static function manage_column($column, $post_id) {
    global $wpdb;

    $views = (int) $wpdb->get_var('SELECT SUM(views) FROM ' . $wpdb->prefix . WF_OPT_STATS . ' WHERE post_id = ' . (int) $post_id);
    $conv = (int) $wpdb->get_var('SELECT SUM(conversion) FROM ' . $wpdb->prefix . WF_OPT_STATS . ' WHERE post_id = ' . (int) $post_id);
    $box2 = (int) $wpdb->get_var('SELECT SUM(views_box2) FROM ' . $wpdb->prefix . WF_OPT_STATS . ' WHERE post_id = ' . (int) $post_id);

    $meta = get_post_meta($post_id, 'wf_optin_meta', true);
    $meta['first-optin']['disable-first-box'] = (int) @$meta['first-optin']['disable-first-box'];

    switch ($column) {
        case 'custom_url':
          $customurl = get_post_meta($post_id, '_optin-custom-url', true);
          if ($customurl) {
            echo '<a href="' . home_url() . $customurl . '" target="_blank">' . $customurl . '</a>';
          } else {
            echo '<i>none</i>';
          }
        break;
        case 'abtest':
            $abtest = get_post_meta($post_id, '_ab-test', true);
            if (!$abtest) {
              echo '<i>none</i>';
            } else {
              $tmp = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . WF_OPT_AB . ' WHERE slug = %s', $abtest));
              if ($tmp) {
                echo '<a href="' . admin_url('admin.php?page=wf-optin-ninja-ab-tests') .'">' . $tmp->name . '</a>';
              } else {
                echo '<i>none</i>';
              }
            }
        break;
        case 'views':
            if ($meta['first-optin']['disable-first-box']) {
              echo $box2;
            } else {
              echo $views;
            }
        break;
        case 'conversions':
            echo $conv;
        break;
        case 'conversion_rate':
            if (!$conv) {
              echo '0 %';
            } else {
              if ($meta['first-optin']['disable-first-box']) {
                echo number_format(100 * $conv / $box2, 1) . ' %';
              } else {
                echo number_format(100 * $conv / $views, 1) . ' %';
              }
            }
        break;
        case 'stats':
            if (!$meta['first-optin']['disable-first-box']) {
              if (!$box2) {
                $box2p = 0;
              } else {
                $box2p = number_format(100 * $box2 / $views, 1);
              }
              if (!$conv) {
                $convp = 0;
              } else {
                $convp = number_format(100 * $conv / $views, 1);
              }
              echo '<span class="quick-stats-wrapper"><span class="inline-stats">' . $views . '<br>views</span><span class="inline-stats-arrow">' . $box2p .'%</span><span class="inline-stats">' . $box2 . '<br>box #2 views</span><span class="inline-stats-arrow">' . $convp . '%</span><span class="inline-stats">' . $conv . '</br>conv</span></span>';
            } else {
                if (!$conv) {
                  $convp = 0;
                } else {
                  $convp = number_format(100 * $conv / $box2, 1);
                }
                echo '<span class="quick-stats-wrapper"><span class="inline-stats">' . $box2 . '<br>box #2 views</span><span class="inline-stats-arrow">' . $convp . '%</span><span class="inline-stats">' . $conv . '</br>conv</span></span>';
            }
        break;
    }
  } // manage_column


  // remove scripts/styles from other plugins/themes when using our template
  static function clean_scripts_queue() {
    global $wp_scripts, $wp_styles, $wp, $post;

    if (!isset($post->post_type) || $post->post_type != 'optin-pages') {
      return;
    }

    foreach ($wp_styles->queue as $script) {
      if (strpos($script, 'optin') === false && strpos($script, 'wp-') === false && strpos($script, 'wp_') === false) {
        wp_dequeue_style($script);
      }
    }

    foreach ($wp_scripts->queue as $script) {
      if (strpos($script, 'optin') === false && strpos($script, 'wp-') === false && strpos($script, 'wp_') === false) {
        wp_dequeue_script($script);
      }
    }
  } // clean_scripts_queue

   // complete logic for custom URL/template
   static function headers_set() {
     global $post, $doing_test, $wp;
     @session_start();
     $get_post = $doing_test = false;

     $uri_slug = strtolower($wp->request);
     $uri_slug = str_replace(home_url(), '', $uri_slug);
     $uri_slug = trim($uri_slug, '/');
     if ($uri_slug) {
       $uri_slug = '/' . $uri_slug . '/';
     } else {
       $uri_slug = '/';
     }

     // try with a test that's already been shown
     if (isset($_SESSION['optin-ninja']['ab-test']) && is_numeric($_SESSION['optin-ninja']['ab-test'])) {
      $get_post = get_posts(array('post_type' => 'optin-pages', 'meta_key' => '_ab-test', 'meta_value' => $uri_slug, 'numberposts' => 1, 'posts_per_page' => 1, 'p' => $_SESSION['optin-ninja']['ab-test']));
      $doing_test = $uri_slug;
     }

     // no shown test, try test for 1st time
     if (!$get_post) {
       $get_post = get_posts(array('post_type' => 'optin-pages', 'meta_key' => '_ab-test', 'meta_value' => $uri_slug, 'orderby' => 'rand', 'numberposts' => 1, 'posts_per_page' => 1));

       if ($get_post) {
         $_SESSION['optin-ninja']['ab-test'] = $get_post[0]->ID;
       }
       $doing_test = $uri_slug;
     }

     // single page
     if (!$get_post) {
       $get_post = get_posts(array('post_type' => 'optin-pages', 'meta_key' => '_optin-custom-url', 'meta_value' =>$uri_slug, 'numberposts' => 1, 'posts_per_page' => 1));
     }

     if ($get_post && is_array($get_post)) {
       $post = $get_post[0];
       load_template(WF_OPT_TEMPLATEPATH . '/default/default-optin.php');
       exit;
     }
   } // headers_set


   // use default URL but custom template
   static function redirect_template() {
     $post_type = get_query_var('post_type');

     if ($post_type == 'optin-pages' && !is_404()) {
       remove_action('wp_footer', 'wp_admin_bar_render', 1000);

       load_template(WF_OPT_TEMPLATEPATH . '/default/default-optin.php');
       exit;
     }
   } // redirect_template
   
   
   static function dashboard(){
    echo "Dashboard";   
   }

   // additional option pages
   static function add_options_page() {
     add_menu_page('OptIn Ninja', 'OptIn Ninja', 'manage_options', 'wf-optin-ninja', array('wf_optin_dashboard', 'content'),'dashicons-chart-area');
   add_submenu_page('wf-optin-ninja', 'Dashboard', 'Dashboard', 'manage_options', 'wf-optin-ninja', array('wf_optin_dashboard', 'content'));
     
   
     //add_submenu_page('wf-optin-ninja', 'Optin Pages', 'Optin Pages', 'manage_options', 'edit.php?post_type=optin-pages');
     add_submenu_page('wf-optin-ninja', 'Add New Optin Page', 'Add New Optin Page', 'manage_options', 'post-new.php?post_type=optin-pages');
     add_submenu_page('wf-optin-ninja', 'A/B Tests', 'A/B Tests', 'manage_options', 'wf-optin-ninja-ab-tests', array('wf_optin_options_ab_tests', 'content'));
     add_submenu_page('wf-optin-ninja', 'Subscribers', 'Subscribers', 'manage_options', 'wf-optin-ninja-subscribers', array('wf_optin_options_subscribers', 'content'));
     add_submenu_page('wf-optin-ninja', 'Statistics', 'Statistics', 'manage_options', 'wf-optin-ninja-stats', array('wf_optin_options_stats', 'content'));
     add_submenu_page('wf-optin-ninja', 'Settings', 'Settings', 'manage_options', 'wf-optin-ninja-settings', array('wf_optin_options', 'content'));
   } // add_options_page
   
   static function custom_menu_order($menu_order) {
    global $submenu, $menu;

    $tmp = $submenu['wf-optin-ninja'];

    $submenu['wf-optin-ninja'] = array();
    $submenu['wf-optin-ninja'][] = $tmp[1];
    $submenu['wf-optin-ninja'][] = $tmp[0];
    $submenu['wf-optin-ninja'][] = $tmp[2];
    $submenu['wf-optin-ninja'][] = $tmp[3];
    $submenu['wf-optin-ninja'][] = $tmp[4];
    $submenu['wf-optin-ninja'][] = $tmp[5];
    $submenu['wf-optin-ninja'][] = $tmp[6];

    return $menu_order;
  } // custom_menu_order


   // custom filter to get optins for a single A/B test
   static function filter_optins_by_id($where, $query) {
     if (!is_admin() || @$query->query['post_type'] != 'optin-pages') {
       return $where;
     }

     if (isset($_GET['id_filter']) && !empty($_GET['id_filter'])) {
       $ids = trim($_GET['id_filter']);
       $where .= " AND ID IN ($ids) ";
     }

     return $where;
   } // filter_optins_by_id


   // save all page meta
   static function update_optin_meta() {
     global $post;

     if (isset($_POST['wf_optin_meta']) && $_POST['wf_optin_meta']) {
       $data = $_POST['wf_optin_meta'];
       update_post_meta($post->ID, 'wf_optin_meta', $data);
     }
   
   $template_data = get_option(WF_OPT_INSTALLED_TEMPLATES);
     
  
   
   if(!empty($_POST['optin_template'])){  
     $template_layout = unserialize($template_data[$_POST['optin_template']]['layout']);
    $data['optin-settings'] = array_merge($data['optin-settings'],$template_layout['optin-settings']);
    $data['first-optin'] = array_merge($data['first-optin'],$template_layout['first-optin']);
    $data['second-optin'] = array_merge($data['second-optin'],$template_layout['second-optin']);
    update_post_meta($post->ID, 'optin_template', $_POST['optin_template']);
    update_post_meta($post->ID, 'wf_optin_meta', $data);
   }

     if (isset($_POST['_optin-custom-url'])) {
       if (trim($_POST['_optin-custom-url']) == '') {
         $url = '';
       } else {
         $url = trim(strtolower(sanitize_title($_POST['_optin-custom-url'])), '/');
         if ($url) {
           $url = '/' . $url . '/';
         } else {
           $url = '/';
         }
       }
       update_post_meta($post->ID, '_optin-custom-url', $url);
     }

     if (isset($_POST['_ab-test'])) {
       update_post_meta($post->ID, '_ab-test', $_POST['_ab-test']);
     }
     if (isset($_POST['_optin_auto_popup'])) {
       update_post_meta($post->ID, '_optin_auto_popup', $_POST['_optin_auto_popup']);
     }
   } // update_optin_meta


   // register custom post type
   static function register_post_type() {
     $labels = array('name'               => 'OptIn Pages',
                     'singular_name'      => 'OptIn Page',
                     'menu_name'          => 'OptIn Ninja',
                     'name_admin_bar'     => 'OptIn Page',
                     'add_new'            => 'Add New OptIn Page',
                     'add_new_item'       => 'Add New OptIn Page',
                     'new_item'           => 'New OptIn',
                     'edit_item'          => 'Edit OptIn',
                     'view_item'          => 'View OptIn',
                     'all_items'          => 'OptIn Pages',
                     'search_items'       => 'Search Optin\'s',
                     'parent_item_colon'  => 'Parent',
                     'not_found'          => 'No OptIn Pages found',
                     'not_found_in_trash' => 'No OptIn Pages found in trash');

     $args = array('labels'             => $labels,
                   'description'        => 'OptIn Ninja Pages',
                   'public'             => true,
                   'exclude_from_search'=> true,
                   'publicly_queryable' => true,
                   'show_ui'            => true,
                   'show_in_menu'       => 'wf-optin-ninja',
                   'show_in_nav_menus'  => false,
                   'show_in_admin_bar'  => true,
                   'query_var'          => false,
                   'rewrite'            => array('slug' => 'optin-ninja'),
                   'capability_type'    => 'post',
                   'has_archive'        => false,
                   'hierarchical'       => false,
                   'can_export'         => true,
                   'menu_position'      => 200,
                   'menu_icon'          => 'dashicons-chart-area',
                   'supports'           => array('title', 'slug', 'revisions'));

     register_post_type('optin-pages', $args);

     $tmp = get_option('wf_opt_flush_rewrite', false);
     if (!$tmp) {
       flush_rewrite_rules();
       require_once(ABSPATH . 'wp-admin/includes/post.php');
       self::import_demo_data();
       update_option('wf_opt_flush_rewrite', true);
     }
   } // register_post_type


   // customize messages for custom post type
   static function post_updated_messages($messages) {
    $post             = get_post();
    $post_type        = get_post_type($post);
    $post_type_object = get_post_type_object($post_type);

    if ($post_type != 'optin-pages') {
      return $messages;
    }

    $messages['optin-pages'] = array(
      0  => '',
      1  => 'OptIn Page updated. ',
      2  => 'Custom field updated.',
      3  => 'Custom field deleted.',
      4  => 'OptIn Page updated.',
      5  => isset( $_GET['revision'] ) ? sprintf( 'OptIn Page restored to revision from %s', wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
      6  => 'OptIn Page published.',
      7  => 'OptIn Page saved.',
      8  => 'OptIn Page submitted.',
      9  => sprintf(
        'OptIn Page scheduled for: <strong>%1$s</strong>.',
        date_i18n( 'M j, Y @ G:i', strtotime( $post->post_date ) )
      ),
      10 => 'OptIn Page updated.'
    );

    if ( $post_type_object->publicly_queryable ) {
      $permalink = get_permalink( $post->ID );

      $view_link = sprintf('<a href="%s">%s</a>', esc_url( $permalink ),  'View OptIn Page');
      $messages[ $post_type ][1] .= $view_link;
      $messages[ $post_type ][6] .= $view_link;
      $messages[ $post_type ][9] .= $view_link;

      $preview_permalink = add_query_arg( 'preview', 'true', $permalink );
      $preview_link = sprintf('<a target="_blank" href="%s">%s</a>', esc_url( $preview_permalink ), 'Preview OptIn Page');
      $messages[ $post_type ][8]  .= $preview_link;
      $messages[ $post_type ][10] .= $preview_link;
    }

    return $messages;
  } // post_updated_messages

   // register all meta boxes
   static function meta_boxes() {
     add_meta_box('optin-ninja-links', 'Navigation', array('wf_optin_ninja_links_box', 'content'), 'optin-pages', 'side');
     add_meta_box('optin-ninja-general-box', 'General Settings', array('wf_optin_ninja_optin_general_settings', 'content'), 'optin-pages', 'normal', 'high');
     add_meta_box('optin-ninja-templates-box', 'Templates', array('wf_optin_ninja_optin_templates', 'content'), 'optin-pages', 'normal', 'high');
     add_meta_box('optin-ninja-settings-box', 'Background', array('wf_optin_ninja_optin_background', 'content'), 'optin-pages', 'normal', 'high');
     add_meta_box('optin-ninja-first-box', 'First Content Box', array('wf_optin_ninja_first_box', 'content'), 'optin-pages', 'normal', 'high');
     add_meta_box('optin-ninja-second-box', 'Second Content Box', array('wf_optin_ninja_second_box', 'content'), 'optin-pages', 'normal', 'high');
     add_meta_box('optin-ninja-form-settings', 'Form Settings', array('wf_optin_ninja_form_box', 'content'), 'optin-pages', 'normal', 'high');
     add_meta_box('optin-ninja-autoresponder', 'Welcome Email', array('wf_optin_ninja_autoresponder', 'content'), 'optin-pages', 'normal', 'high');
     add_meta_box('optin-ninja-popup', 'Popup / Lightbox', array('wf_optin_ninja_popup', 'content'), 'optin-pages', 'normal', 'high');
     add_meta_box('optin-ninja-notifications', 'Notifications', array('wf_optin_ninja_notifications_box', 'content'), 'optin-pages', 'normal', 'high');
   } // meta_boxes


   // permanently dismiss pointer
   static function dismiss_pointer_ajax() {
     $pointers = get_option('wf-optin-pointers');
     $pointer = trim($_POST['pointer']);

     if (!$pointers) {
       die('1');
     }

     for ($i = 0; $i < sizeof($pointers); $i++) {
       if ($pointers[$i]['target'] == $pointer) {
         unset($pointers[$i]);
         break;
       }
     }

     $pointers = array_values($pointers);
     update_option('wf-optin-pointers', $pointers);
     die('1');
   } // dismiss_pointer_ajax

   // frontend scripts / styles enqueue
   static function frontend_enqueue_scripts() {
     $options = get_option('wf-optin', array());

     if (isset($options['disable_popup']) && $options['disable_popup']) {
       return;
     }

     //wp_localize_script('jquery', 'optin_path', plugins_url('', __FILE__));
     wp_enqueue_script('jquery-ui-dialog');
     wp_enqueue_style('wp-jquery-ui-dialog');

     wp_enqueue_script('optin-ninja-frontend', plugins_url('js/optin-ninja-frontend.js', __FILE__), array('jquery'), WF_OPT_VERSION, true);
     wp_enqueue_style('optin-ninja-frontend', plugins_url('css/optin-ninja-frontend.css', __FILE__), array(), WF_OPT_VERSION);
   } // frontend_enqueue_scripts

   // all scripts / styles enqueue
   static function enqueue_scripts() {
     $screen = get_current_screen();

     wp_localize_script('jquery', 'optin_path', plugins_url('', __FILE__));

     $pointers = get_option('wf-optin-pointers');
     if ($pointers) {
       wp_enqueue_script('optin-ninja-pointers', plugins_url('js/optin-pointers.js', __FILE__), array('jquery'), WF_OPT_VERSION, true);
       wp_enqueue_style('wp-pointer');
       wp_enqueue_script('wp-pointer');
       wp_localize_script('wp-pointer', 'optin_pointers', $pointers);
     }
     
     if (strpos($screen->id, 'wf-optin-ninja') !== false || strpos($screen->id, 'optin-pages') !== false) {
       global $post;

       wp_dequeue_style('jquery-ui-css'); // EDD fix

       wp_enqueue_media();
       wp_enqueue_script('wp-color-picker');
       wp_enqueue_script('jquery-ui-tabs');
       wp_enqueue_style('wp-color-picker');

       wp_enqueue_style('optin-ninja', plugins_url('css/optin-ninja-admin.css', __FILE__), array(), WF_OPT_VERSION);
       wp_enqueue_script('optin-ninja', plugins_url('js/optin-admin.js', __FILE__), array('jquery', 'jquery-ui-tabs'), WF_OPT_VERSION, true);
     }
   
     // dashboard
     if ($screen->id == 'toplevel_page_wf-optin-ninja') {
       wp_enqueue_style('optin-ninja', plugins_url('css/optin-ninja-admin.css', __FILE__), array(), WF_OPT_VERSION);

       wp_enqueue_script('optin-ninja-google-chart', "//www.gstatic.com/charts/loader.js", false, false, true);
       wp_enqueue_script('optin-ninja-google-maps', '//maps.google.com/maps/api/js?key=AIzaSyArcXkQ15FoOTS2Z7El2SJHDIlTMW7Rxxg', false, false, true);
       wp_enqueue_script('optin-ninja-gmap3', WF_OPT_PLUGINURL . '/js/gmap3.min.js', array('jquery'), WF_OPT_VERSION, true);
       wp_enqueue_script('optin-ninja-dashboard', WF_OPT_PLUGINURL . '/js/optin-admin-dashboard.js', array('jquery'), WF_OPT_VERSION, true);
     wf_optin_dashboard::setup_js_vars();
     }

     if (strpos($screen->id, 'wf-optin-ninja-ab-tests') !== false) {
    wp_enqueue_script('jquery-ui-dialog');
        wp_enqueue_style('wp-jquery-ui-dialog');
     }
   
   if (strpos($screen->id, 'wf-optin-ninja-settings') !== false) {
    wp_enqueue_style('optin-ninja', plugins_url('css/optin-ninja-admin.css', __FILE__), array(), WF_OPT_VERSION);
     }

     if (strpos($screen->id, 'wf-optin-ninja-subscribers') !== false) {
       wp_enqueue_style('optin-datatables', plugins_url('js/datatables/jquery.dataTables.css', __FILE__), array(), WF_OPT_VERSION);
        wp_enqueue_style('optin-datatables-buttons', plugins_url('js/datatables/dataTables.tableTools.css', __FILE__), array(), WF_OPT_VERSION);
       wp_enqueue_script('optin-datatables', plugins_url('js/datatables/jquery.dataTables.min.js', __FILE__), array(), WF_OPT_VERSION, true);
       wp_enqueue_script('optin-datatables-buttons', plugins_url('js/datatables/dataTables.tableTools.min.js', __FILE__), array(), WF_OPT_VERSION, true);
       wp_enqueue_script('optin-datatables-init', plugins_url('js/datatables/init.js', __FILE__), array(), WF_OPT_VERSION, true);
     }

     if (strpos($screen->id, 'wf-optin-ninja-stats') !== false) {
       wp_enqueue_script('flot-charts-js', plugins_url('js/flot/jquery.flot.min.js', __FILE__), array('jquery'), WF_OPT_VERSION, true);
       wp_enqueue_script('flot-time', plugins_url('js/flot/jquery.flot.time.min.js', __FILE__), array('jquery'), WF_OPT_VERSION, true);
       wp_enqueue_script('flot-resize', plugins_url('js/flot/jquery.flot.resize.min.js', __FILE__), array('jquery'), WF_OPT_VERSION, true);
       wp_enqueue_script('flot-interpolate', plugins_url('js/flot/jquery.flot.interpolate.js', __FILE__), array('jquery'), WF_OPT_VERSION, true);
     }
   } // enqueue_scripts

  static function filter_mce_css($mce_css) {
    global $current_screen;

    if ('optin-pages' === $current_screen->post_type) {
      if (!empty($mce_css)) {
        $mce_css .= ',';
      }
      $mce_css .= plugins_url('css/optin-ninja-editor.css?ver' . WF_OPT_VERSION, __FILE__ );
    }
    return $mce_css;
  } // filter_mce_css


  // removes hooks from wp_footer() that are not controlled by OptIn Ninja
  static function clean_footer() {
    global $wp_filter;

    foreach ($wp_filter['wp_footer'] as $ind => $tmp2) {
      foreach ($wp_filter['wp_footer'][$ind] as $name => $tmp) {
        if (strpos($name, 'optin') === false &&
            strpos($name, 'wp_') === false) {
          remove_action('wp_footer', $name, 10);
        }
      }
    }
  } // clean_footer


   // try stopping users from leaving
  static function prevent_from_leaving() {
     global $post;
     $meta = get_post_meta($post->ID, 'wf_optin_meta', true);
     $prevent = $meta['optin-form']['prevent-from-leaving'];
     $message = $meta['optin-form']['prevent-alert-message'];

     if ($prevent == 'yes') {
       return "jQuery(window).bind('beforeunload', function(){
                 if (!subscribed && !window.parent.document.getElementById('wf-optin-iframe')) {
                  return '" . addslashes($message) . "';
                 } else {
                   return;
                 }
               });";
     } else {
       return '';
     }
  } // prevent_from_leaving


  // send email and mobile notifications after user subscribes
  static function send_notifications($optin_meta, $fields, $optin_id, $force = false) {
    if ((isset($optin_meta['notifications']['email_notifications']) && $optin_meta['notifications']['email_notifications'] == '1') || $force) {

      $sub_custom = '';
      $sub_name = @$fields['name'];
      $sub_email = $fields['email'];
      $optin_name = get_the_title($optin_id);
      $optin_url = get_permalink($optin_id);

      if (class_exists('wf_optin_ninja_fields')) {
        foreach ($fields as $key => $value) {
          if ($key == 'name' || $key == 'email') {
           continue;
          }
          $sub_custom .= '  ' . $key . ': ' . $value . "\n";
        }
        $sub_custom = rtrim($sub_custom, "\n");
      }

      $subject = $optin_meta['notifications']['email_subject'];
      $subject = str_replace(array('{subscriber-name}', '{subscriber-email}', '{subscriber-custom}', '{optin-name}', '{optin-url}'), array($sub_name, $sub_email, $sub_custom, $optin_name, $optin_url), $subject);
      $body = $optin_meta['notifications']['email_body'];
      $body = str_replace(array('{subscriber-name}', '{subscriber-email}', '{subscriber-custom}', '{optin-name}', '{optin-url}'), array($sub_name, $sub_email, $sub_custom, $optin_name, $optin_url), $body);

      wp_mail($optin_meta['notifications']['email_to'], $subject, $body);
    }

    if ((isset($optin_meta['notifications']['push_notifications']) && $optin_meta['notifications']['push_notifications'] == '1') || $force) {
      require_once WF_OPT_PLUGIN_DIR . '/api/pushover.php';
      $options = get_option('wf-optin', array());

      if (!isset($options['pushover']['status']) || $options['pushover']['status'] != '1') {
        return;
      }

      $push = new Pushover();
      $push->setToken($options['pushover-key']);
      $push->setTimestamp(time());

      $sub_custom = '';
      $sub_name = @$fields['name'];
      $sub_email = $fields['email'];
      $optin_name = get_the_title($optin_id);
      $optin_url = get_permalink($optin_id);

      if (class_exists('wf_optin_ninja_fields')) {
        foreach ($fields as $key => $value) {
          if ($key == 'name' || $key == 'email') {
           continue;
          }
          $sub_custom .= '  ' . $key . ': ' . $value . "\n";
        }
        $sub_custom = rtrim($sub_custom, "\n");
      }

      $subject = $optin_meta['notifications']['push_subject'];
      $subject = str_replace(array('{subscriber-name}', '{subscriber-email}', '{subscriber-custom}', '{optin-name}', '{optin-url}'), array($sub_name, $sub_email, $sub_custom, $optin_name, $optin_url), $subject);
      $body = $optin_meta['notifications']['push_body'];
      $body = str_replace(array('{subscriber-name}', '{subscriber-email}', '{subscriber-custom}', '{optin-name}', '{optin-url}'), array($sub_name, $sub_email, $sub_custom, $optin_name, $optin_url), $body);

      $push->setTitle($subject);
      $push->setMessage($body);
      $push->setSound($optin_meta['notifications']['push_sound']);
      //$push->setDebug(true);

      $users = explode("\n", $optin_meta['notifications']['push_to']);
      foreach ($users as $user) {
        $user = trim($user);
        if (!$user) {
          continue;
        }
        $tmp = explode(':', $user);
        if (sizeof($tmp) == 2) {
          $user = trim($tmp[0]);
          $device = trim($tmp[1]);
        } else {
          $user = trim($tmp[0]);
          $device = '';
        }

        $push->setUser($user);
        $push->setDevice($device);
        $res = $push->send();
      } // foreach users
    } // if push
  } // send__notifications


  // manages all stats++
  static function count_stats($step = '1', $post_id = 0) {
     global $wpdb, $post, $doing_test;
     @session_start();

     if ($post_id) {
       $id = $post_id;
     } elseif (isset($post->ID)) {
       $id = $post->ID;
     } else {
       $id = (int) $_POST['post'];
     }

     if (!$id) {
       return;
     }

     if (!isset($_SESSION['optin-ninja'][$id])) {
      $_SESSION['optin-ninja'][$id] = array();
     }

     if ($step == '1') {
       if (!isset($_SESSION['optin-ninja'][$id][1])) {
         // page view
         $_SESSION['optin-ninja'][$id][1] = true;

         $wpdb->query($wpdb->prepare("INSERT INTO " . $wpdb->prefix . WF_OPT_STATS . " (date, post_id, views)
                       VALUES (%s, %d, %d)
                       ON DUPLICATE KEY UPDATE views = views + 1", array(current_time('mysql'), $id, 1)));
         // test view
         if ($doing_test) {
           $wpdb->query($wpdb->prepare("UPDATE " . $wpdb->prefix . WF_OPT_AB . " SET views = views + 1
                       WHERE slug = %s", $doing_test));
         }
       }
     } elseif ($step == '2') {
       if (!isset($_SESSION['optin-ninja'][$id][2])) {
         // box #2 view
         $_SESSION['optin-ninja'][$id][2] = true;

         $wpdb->query($wpdb->prepare("INSERT INTO " . $wpdb->prefix . WF_OPT_STATS . " (date, post_id, views_box2)
                       VALUES (%s, %d, %d)
                       ON DUPLICATE KEY UPDATE views_box2 = views_box2 + 1", array(current_time('mysql'), $id, 1)));
       }
     } elseif ($step == '3') { // todo dirty fix
       if (1 || !isset($_SESSION['optin-ninja'][$id][3])) {
         // conversion / subscription
         $_SESSION['optin-ninja'][$id][3] = true;

         $wpdb->query($wpdb->prepare("INSERT INTO " . $wpdb->prefix . WF_OPT_STATS . " (date, post_id, conversion)
                       VALUES (%s, %d, %d)
                       ON DUPLICATE KEY UPDATE conversion = conversion + 1", array(current_time('mysql'), $id, 1)));
       }
     }
   } // stats


   // save list of google font in options
   static function load_google_fonts() {
     $fonts = get_option('wf-optin-google-fonts');

     if (empty($fonts) || is_object($fonts) || !isset($fonts['ABeeZee'])) {
       $fonts = array('ABeeZee' => 'ABeeZee', 'Abel' => 'Abel', 'Abril+Fatface' => 'Abril Fatface', 'Aclonica' => 'Aclonica', 'Acme' => 'Acme', 'Actor' => 'Actor', 'Adamina' => 'Adamina', 'Advent+Pro' => 'Advent Pro', 'Aguafina+Script' => 'Aguafina Script', 'Akronim' => 'Akronim', 'Aladin' => 'Aladin', 'Aldrich' => 'Aldrich', 'Alegreya' => 'Alegreya', 'Alegreya+SC' => 'Alegreya SC', 'Alex+Brush' => 'Alex Brush', 'Alfa+Slab+One' => 'Alfa Slab One', 'Alice' => 'Alice', 'Alike' => 'Alike', 'Alike+Angular' => 'Alike Angular', 'Allan' => 'Allan', 'Allerta' => 'Allerta', 'Allerta+Stencil' => 'Allerta Stencil', 'Allura' => 'Allura', 'Almendra' => 'Almendra', 'Almendra+Display' => 'Almendra Display', 'Almendra+SC' => 'Almendra SC', 'Amarante' => 'Amarante', 'Amaranth' => 'Amaranth', 'Amatic+SC' => 'Amatic SC', 'Amethysta' => 'Amethysta', 'Anaheim' => 'Anaheim', 'Andada' => 'Andada', 'Andika' => 'Andika', 'Angkor' => 'Angkor', 'Annie+Use+Your+Telescope' => 'Annie Use Your Telescope', 'Anonymous+Pro' => 'Anonymous Pro', 'Antic' => 'Antic', 'Antic+Didone' => 'Antic Didone', 'Antic+Slab' => 'Antic Slab', 'Anton' => 'Anton', 'Arapey' => 'Arapey', 'Arbutus' => 'Arbutus', 'Arbutus+Slab' => 'Arbutus Slab', 'Architects+Daughter' => 'Architects Daughter', 'Archivo+Black' => 'Archivo Black', 'Archivo+Narrow' => 'Archivo Narrow', 'Arimo' => 'Arimo', 'Arizonia' => 'Arizonia', 'Armata' => 'Armata', 'Artifika' => 'Artifika', 'Arvo' => 'Arvo', 'Asap' => 'Asap', 'Asset' => 'Asset', 'Astloch' => 'Astloch', 'Asul' => 'Asul', 'Atomic+Age' => 'Atomic Age', 'Aubrey' => 'Aubrey', 'Audiowide' => 'Audiowide', 'Autour+One' => 'Autour One', 'Average' => 'Average', 'Average+Sans' => 'Average Sans', 'Averia+Gruesa+Libre' => 'Averia Gruesa Libre', 'Averia+Libre' => 'Averia Libre', 'Averia+Sans+Libre' => 'Averia Sans Libre', 'Averia+Serif+Libre' => 'Averia Serif Libre', 'Bad+Script' => 'Bad Script', 'Balthazar' => 'Balthazar', 'Bangers' => 'Bangers', 'Basic' => 'Basic', 'Battambang' => 'Battambang', 'Baumans' => 'Baumans', 'Bayon' => 'Bayon', 'Belgrano' => 'Belgrano', 'Belleza' => 'Belleza', 'BenchNine' => 'BenchNine', 'Bentham' => 'Bentham', 'Berkshire+Swash' => 'Berkshire Swash', 'Bevan' => 'Bevan', 'Bigelow+Rules' => 'Bigelow Rules', 'Bigshot+One' => 'Bigshot One', 'Bilbo' => 'Bilbo', 'Bilbo+Swash+Caps' => 'Bilbo Swash Caps', 'Bitter' => 'Bitter', 'Black+Ops+One' => 'Black Ops One', 'Bokor' => 'Bokor', 'Bonbon' => 'Bonbon', 'Boogaloo' => 'Boogaloo', 'Bowlby+One' => 'Bowlby One', 'Bowlby+One+SC' => 'Bowlby One SC', 'Brawler' => 'Brawler', 'Bree+Serif' => 'Bree Serif', 'Bubblegum+Sans' => 'Bubblegum Sans', 'Bubbler+One' => 'Bubbler One', 'Buda' => 'Buda', 'Buenard' => 'Buenard', 'Butcherman' => 'Butcherman', 'Butterfly+Kids' => 'Butterfly Kids', 'Cabin' => 'Cabin', 'Cabin+Condensed' => 'Cabin Condensed', 'Cabin+Sketch' => 'Cabin Sketch', 'Caesar+Dressing' => 'Caesar Dressing', 'Cagliostro' => 'Cagliostro', 'Calligraffitti' => 'Calligraffitti', 'Cambo' => 'Cambo', 'Candal' => 'Candal', 'Cantarell' => 'Cantarell', 'Cantata+One' => 'Cantata One', 'Cantora+One' => 'Cantora One', 'Capriola' => 'Capriola', 'Cardo' => 'Cardo', 'Carme' => 'Carme', 'Carrois+Gothic' => 'Carrois Gothic', 'Carrois+Gothic+SC' => 'Carrois Gothic SC', 'Carter+One' => 'Carter One', 'Caudex' => 'Caudex', 'Cedarville+Cursive' => 'Cedarville Cursive', 'Ceviche+One' => 'Ceviche One', 'Changa+One' => 'Changa One', 'Chango' => 'Chango', 'Chau+Philomene+One' => 'Chau Philomene One', 'Chela+One' => 'Chela One', 'Chelsea+Market' => 'Chelsea Market', 'Chenla' => 'Chenla', 'Cherry+Cream+Soda' => 'Cherry Cream Soda', 'Cherry+Swash' => 'Cherry Swash', 'Chewy' => 'Chewy', 'Chicle' => 'Chicle', 'Chivo' => 'Chivo', 'Cinzel' => 'Cinzel', 'Cinzel+Decorative' => 'Cinzel Decorative', 'Clicker+Script' => 'Clicker Script', 'Coda' => 'Coda', 'Coda+Caption' => 'Coda Caption', 'Codystar' => 'Codystar', 'Combo' => 'Combo', 'Comfortaa' => 'Comfortaa', 'Coming+Soon' => 'Coming Soon', 'Concert+One' => 'Concert One', 'Condiment' => 'Condiment', 'Content' => 'Content', 'Contrail+One' => 'Contrail One', 'Convergence' => 'Convergence', 'Cookie' => 'Cookie', 'Copse' => 'Copse', 'Corben' => 'Corben', 'Courgette' => 'Courgette', 'Cousine' => 'Cousine', 'Coustard' => 'Coustard', 'Covered+By+Your+Grace' => 'Covered By Your Grace', 'Crafty+Girls' => 'Crafty Girls', 'Creepster' => 'Creepster', 'Crete+Round' => 'Crete Round', 'Crimson+Text' => 'Crimson Text', 'Croissant+One' => 'Croissant One', 'Crushed' => 'Crushed', 'Cuprum' => 'Cuprum', 'Cutive' => 'Cutive', 'Cutive+Mono' => 'Cutive Mono', 'Damion' => 'Damion', 'Dancing+Script' => 'Dancing Script', 'Dangrek' => 'Dangrek', 'Dawning+of+a+New+Day' => 'Dawning of a New Day', 'Days+One' => 'Days One', 'Delius' => 'Delius', 'Delius+Swash+Caps' => 'Delius Swash Caps', 'Delius+Unicase' => 'Delius Unicase', 'Della+Respira' => 'Della Respira', 'Denk+One' => 'Denk One', 'Devonshire' => 'Devonshire', 'Didact+Gothic' => 'Didact Gothic', 'Diplomata' => 'Diplomata', 'Diplomata+SC' => 'Diplomata SC', 'Domine' => 'Domine', 'Donegal+One' => 'Donegal One', 'Doppio+One' => 'Doppio One', 'Dorsa' => 'Dorsa', 'Dosis' => 'Dosis', 'Dr+Sugiyama' => 'Dr Sugiyama', 'Droid+Sans' => 'Droid Sans', 'Droid+Sans+Mono' => 'Droid Sans Mono', 'Droid+Serif' => 'Droid Serif', 'Duru+Sans' => 'Duru Sans', 'Dynalight' => 'Dynalight', 'EB+Garamond' => 'EB Garamond', 'Eagle+Lake' => 'Eagle Lake', 'Eater' => 'Eater', 'Economica' => 'Economica', 'Electrolize' => 'Electrolize', 'Elsie' => 'Elsie', 'Elsie+Swash+Caps' => 'Elsie Swash Caps', 'Emblema+One' => 'Emblema One', 'Emilys+Candy' => 'Emilys Candy', 'Engagement' => 'Engagement', 'Englebert' => 'Englebert', 'Enriqueta' => 'Enriqueta', 'Erica+One' => 'Erica One', 'Esteban' => 'Esteban', 'Euphoria+Script' => 'Euphoria Script', 'Ewert' => 'Ewert', 'Exo' => 'Exo', 'Expletus+Sans' => 'Expletus Sans', 'Fanwood+Text' => 'Fanwood Text', 'Fascinate' => 'Fascinate', 'Fascinate+Inline' => 'Fascinate Inline', 'Faster+One' => 'Faster One', 'Fasthand' => 'Fasthand', 'Federant' => 'Federant', 'Federo' => 'Federo', 'Felipa' => 'Felipa', 'Fenix' => 'Fenix', 'Finger+Paint' => 'Finger Paint', 'Fjalla+One' => 'Fjalla One', 'Fjord+One' => 'Fjord One', 'Flamenco' => 'Flamenco', 'Flavors' => 'Flavors', 'Fondamento' => 'Fondamento', 'Fontdiner+Swanky' => 'Fontdiner Swanky', 'Forum' => 'Forum', 'Francois+One' => 'Francois One', 'Freckle+Face' => 'Freckle Face', 'Fredericka+the+Great' => 'Fredericka the Great', 'Fredoka+One' => 'Fredoka One', 'Freehand' => 'Freehand', 'Fresca' => 'Fresca', 'Frijole' => 'Frijole', 'Fugaz+One' => 'Fugaz One', 'GFS+Didot' => 'GFS Didot', 'GFS+Neohellenic' => 'GFS Neohellenic', 'Gafata' => 'Gafata', 'Galdeano' => 'Galdeano', 'Galindo' => 'Galindo', 'Gentium+Basic' => 'Gentium Basic', 'Gentium+Book+Basic' => 'Gentium Book Basic', 'Geo' => 'Geo', 'Geostar' => 'Geostar', 'Geostar+Fill' => 'Geostar Fill', 'Germania+One' => 'Germania One', 'Gilda+Display' => 'Gilda Display', 'Give+You+Glory' => 'Give You Glory', 'Glass+Antiqua' => 'Glass Antiqua', 'Glegoo' => 'Glegoo', 'Gloria+Hallelujah' => 'Gloria Hallelujah', 'Goblin+One' => 'Goblin One', 'Gochi+Hand' => 'Gochi Hand', 'Gorditas' => 'Gorditas', 'Goudy+Bookletter+1911' => 'Goudy Bookletter 1911', 'Graduate' => 'Graduate', 'Grand+Hotel' => 'Grand Hotel', 'Gravitas+One' => 'Gravitas One', 'Great+Vibes' => 'Great Vibes', 'Griffy' => 'Griffy', 'Gruppo' => 'Gruppo', 'Gudea' => 'Gudea', 'Habibi' => 'Habibi', 'Hammersmith+One' => 'Hammersmith One', 'Hanalei' => 'Hanalei', 'Hanalei+Fill' => 'Hanalei Fill', 'Handlee' => 'Handlee', 'Hanuman' => 'Hanuman', 'Happy+Monkey' => 'Happy Monkey', 'Headland+One' => 'Headland One', 'Henny+Penny' => 'Henny Penny', 'Herr+Von+Muellerhoff' => 'Herr Von Muellerhoff', 'Holtwood+One+SC' => 'Holtwood One SC', 'Homemade+Apple' => 'Homemade Apple', 'Homenaje' => 'Homenaje', 'IM+Fell+DW+Pica' => 'IM Fell DW Pica', 'IM+Fell+DW+Pica+SC' => 'IM Fell DW Pica SC', 'IM+Fell+Double+Pica' => 'IM Fell Double Pica', 'IM+Fell+Double+Pica+SC' => 'IM Fell Double Pica SC', 'IM+Fell+English' => 'IM Fell English', 'IM+Fell+English+SC' => 'IM Fell English SC', 'IM+Fell+French+Canon' => 'IM Fell French Canon', 'IM+Fell+French+Canon+SC' => 'IM Fell French Canon SC', 'IM+Fell+Great+Primer' => 'IM Fell Great Primer', 'IM+Fell+Great+Primer+SC' => 'IM Fell Great Primer SC', 'Iceberg' => 'Iceberg', 'Iceland' => 'Iceland', 'Imprima' => 'Imprima', 'Inconsolata' => 'Inconsolata', 'Inder' => 'Inder', 'Indie+Flower' => 'Indie Flower', 'Inika' => 'Inika', 'Irish+Grover' => 'Irish Grover', 'Istok+Web' => 'Istok Web', 'Italiana' => 'Italiana', 'Italianno' => 'Italianno', 'Jacques+Francois' => 'Jacques Francois', 'Jacques+Francois+Shadow' => 'Jacques Francois Shadow', 'Jim+Nightshade' => 'Jim Nightshade', 'Jockey+One' => 'Jockey One', 'Jolly+Lodger' => 'Jolly Lodger', 'Josefin+Sans' => 'Josefin Sans', 'Josefin+Slab' => 'Josefin Slab', 'Joti+One' => 'Joti One', 'Judson' => 'Judson', 'Julee' => 'Julee', 'Julius+Sans+One' => 'Julius Sans One', 'Junge' => 'Junge', 'Jura' => 'Jura', 'Just+Another+Hand' => 'Just Another Hand', 'Just+Me+Again+Down+Here' => 'Just Me Again Down Here', 'Kameron' => 'Kameron', 'Karla' => 'Karla', 'Kaushan+Script' => 'Kaushan Script', 'Keania+One' => 'Keania One', 'Kelly+Slab' => 'Kelly Slab', 'Kenia' => 'Kenia', 'Khmer' => 'Khmer', 'Kite+One' => 'Kite One', 'Knewave' => 'Knewave', 'Kotta+One' => 'Kotta One', 'Koulen' => 'Koulen', 'Kranky' => 'Kranky', 'Kreon' => 'Kreon', 'Kristi' => 'Kristi', 'Krona+One' => 'Krona One', 'La+Belle+Aurore' => 'La Belle Aurore', 'Lancelot' => 'Lancelot', 'Lato' => 'Lato', 'League+Script' => 'League Script', 'Leckerli+One' => 'Leckerli One', 'Ledger' => 'Ledger', 'Lekton' => 'Lekton', 'Lemon' => 'Lemon', 'Libre+Baskerville' => 'Libre Baskerville', 'Life+Savers' => 'Life Savers', 'Lilita+One' => 'Lilita One', 'Limelight' => 'Limelight', 'Linden+Hill' => 'Linden Hill', 'Lobster' => 'Lobster', 'Lobster+Two' => 'Lobster Two', 'Londrina+Outline' => 'Londrina Outline', 'Londrina+Shadow' => 'Londrina Shadow', 'Londrina+Sketch' => 'Londrina Sketch', 'Londrina+Solid' => 'Londrina Solid', 'Lora' => 'Lora', 'Love+Ya+Like+A+Sister' => 'Love Ya Like A Sister', 'Loved+by+the+King' => 'Loved by the King', 'Lovers+Quarrel' => 'Lovers Quarrel', 'Luckiest+Guy' => 'Luckiest Guy', 'Lusitana' => 'Lusitana', 'Lustria' => 'Lustria', 'Macondo' => 'Macondo', 'Macondo+Swash+Caps' => 'Macondo Swash Caps', 'Magra' => 'Magra', 'Maiden+Orange' => 'Maiden Orange', 'Mako' => 'Mako', 'Marcellus' => 'Marcellus', 'Marcellus+SC' => 'Marcellus SC', 'Marck+Script' => 'Marck Script', 'Margarine' => 'Margarine', 'Marko+One' => 'Marko One', 'Marmelad' => 'Marmelad', 'Marvel' => 'Marvel', 'Mate' => 'Mate', 'Mate+SC' => 'Mate SC', 'Maven+Pro' => 'Maven Pro', 'McLaren' => 'McLaren', 'Meddon' => 'Meddon', 'MedievalSharp' => 'MedievalSharp', 'Medula+One' => 'Medula One', 'Megrim' => 'Megrim', 'Meie+Script' => 'Meie Script', 'Merienda' => 'Merienda', 'Merienda+One' => 'Merienda One', 'Merriweather' => 'Merriweather', 'Metal' => 'Metal', 'Metal+Mania' => 'Metal Mania', 'Metamorphous' => 'Metamorphous', 'Metrophobic' => 'Metrophobic', 'Michroma' => 'Michroma', 'Milonga' => 'Milonga', 'Miltonian' => 'Miltonian', 'Miltonian+Tattoo' => 'Miltonian Tattoo', 'Miniver' => 'Miniver', 'Miss+Fajardose' => 'Miss Fajardose', 'Modern+Antiqua' => 'Modern Antiqua', 'Molengo' => 'Molengo', 'Molle' => 'Molle', 'Monda' => 'Monda', 'Monofett' => 'Monofett', 'Monoton' => 'Monoton', 'Monsieur+La+Doulaise' => 'Monsieur La Doulaise', 'Montaga' => 'Montaga', 'Montez' => 'Montez', 'Montserrat' => 'Montserrat', 'Montserrat+Alternates' => 'Montserrat Alternates', 'Montserrat+Subrayada' => 'Montserrat Subrayada', 'Moul' => 'Moul', 'Moulpali' => 'Moulpali', 'Mountains+of+Christmas' => 'Mountains of Christmas', 'Mouse+Memoirs' => 'Mouse Memoirs', 'Mr+Bedfort' => 'Mr Bedfort', 'Mr+Dafoe' => 'Mr Dafoe', 'Mr+De+Haviland' => 'Mr De Haviland', 'Mrs+Saint+Delafield' => 'Mrs Saint Delafield', 'Mrs+Sheppards' => 'Mrs Sheppards', 'Muli' => 'Muli', 'Mystery+Quest' => 'Mystery Quest', 'Neucha' => 'Neucha', 'Neuton' => 'Neuton', 'New+Rocker' => 'New Rocker', 'News+Cycle' => 'News Cycle', 'Niconne' => 'Niconne', 'Nixie+One' => 'Nixie One', 'Nobile' => 'Nobile', 'Nokora' => 'Nokora', 'Norican' => 'Norican', 'Nosifer' => 'Nosifer', 'Nothing+You+Could+Do' => 'Nothing You Could Do', 'Noticia+Text' => 'Noticia Text', 'Nova+Cut' => 'Nova Cut', 'Nova+Flat' => 'Nova Flat', 'Nova+Mono' => 'Nova Mono', 'Nova+Oval' => 'Nova Oval', 'Nova+Round' => 'Nova Round', 'Nova+Script' => 'Nova Script', 'Nova+Slim' => 'Nova Slim', 'Nova+Square' => 'Nova Square', 'Numans' => 'Numans', 'Nunito' => 'Nunito', 'Odor+Mean+Chey' => 'Odor Mean Chey', 'Offside' => 'Offside', 'Old+Standard+TT' => 'Old Standard TT', 'Oldenburg' => 'Oldenburg', 'Oleo+Script' => 'Oleo Script', 'Oleo+Script+Swash+Caps' => 'Oleo Script Swash Caps', 'Open+Sans' => 'Open Sans', 'Open+Sans+Condensed' => 'Open Sans Condensed', 'Oranienbaum' => 'Oranienbaum', 'Orbitron' => 'Orbitron', 'Oregano' => 'Oregano', 'Orienta' => 'Orienta', 'Original+Surfer' => 'Original Surfer', 'Oswald' => 'Oswald', 'Over+the+Rainbow' => 'Over the Rainbow', 'Overlock' => 'Overlock', 'Overlock+SC' => 'Overlock SC', 'Ovo' => 'Ovo', 'Oxygen' => 'Oxygen', 'Oxygen+Mono' => 'Oxygen Mono', 'PT+Mono' => 'PT Mono', 'PT+Sans' => 'PT Sans', 'PT+Sans+Caption' => 'PT Sans Caption', 'PT+Sans+Narrow' => 'PT Sans Narrow', 'PT+Serif' => 'PT Serif', 'PT+Serif+Caption' => 'PT Serif Caption', 'Pacifico' => 'Pacifico', 'Paprika' => 'Paprika', 'Parisienne' => 'Parisienne', 'Passero+One' => 'Passero One', 'Passion+One' => 'Passion One', 'Patrick+Hand' => 'Patrick Hand', 'Patua+One' => 'Patua One', 'Paytone+One' => 'Paytone One', 'Peralta' => 'Peralta', 'Permanent+Marker' => 'Permanent Marker', 'Petit+Formal+Script' => 'Petit Formal Script', 'Petrona' => 'Petrona', 'Philosopher' => 'Philosopher', 'Piedra' => 'Piedra', 'Pinyon+Script' => 'Pinyon Script', 'Pirata+One' => 'Pirata One', 'Plaster' => 'Plaster', 'Play' => 'Play', 'Playball' => 'Playball', 'Playfair+Display' => 'Playfair Display', 'Playfair+Display+SC' => 'Playfair Display SC', 'Podkova' => 'Podkova', 'Poiret+One' => 'Poiret One', 'Poller+One' => 'Poller One', 'Poly' => 'Poly', 'Pompiere' => 'Pompiere', 'Pontano+Sans' => 'Pontano Sans', 'Port+Lligat+Sans' => 'Port Lligat Sans', 'Port+Lligat+Slab' => 'Port Lligat Slab', 'Prata' => 'Prata', 'Preahvihear' => 'Preahvihear', 'Press+Start+2P' => 'Press Start 2P', 'Princess+Sofia' => 'Princess Sofia', 'Prociono' => 'Prociono', 'Prosto+One' => 'Prosto One', 'Puritan' => 'Puritan', 'Purple+Purse' => 'Purple Purse', 'Quando' => 'Quando', 'Quantico' => 'Quantico', 'Quattrocento' => 'Quattrocento', 'Quattrocento+Sans' => 'Quattrocento Sans', 'Questrial' => 'Questrial', 'Quicksand' => 'Quicksand', 'Quintessential' => 'Quintessential', 'Qwigley' => 'Qwigley', 'Racing+Sans+One' => 'Racing Sans One', 'Radley' => 'Radley', 'Raleway' => 'Raleway', 'Raleway+Dots' => 'Raleway Dots', 'Rambla' => 'Rambla', 'Rammetto+One' => 'Rammetto One', 'Ranchers' => 'Ranchers', 'Rancho' => 'Rancho', 'Rationale' => 'Rationale', 'Redressed' => 'Redressed', 'Reenie+Beanie' => 'Reenie Beanie', 'Revalia' => 'Revalia', 'Ribeye' => 'Ribeye', 'Ribeye+Marrow' => 'Ribeye Marrow', 'Righteous' => 'Righteous', 'Risque' => 'Risque', 'Rochester' => 'Rochester', 'Rock+Salt' => 'Rock Salt', 'Rokkitt' => 'Rokkitt', 'Romanesco' => 'Romanesco', 'Ropa+Sans' => 'Ropa Sans', 'Rosario' => 'Rosario', 'Rosarivo' => 'Rosarivo', 'Rouge+Script' => 'Rouge Script', 'Ruda' => 'Ruda', 'Rufina' => 'Rufina', 'Ruge+Boogie' => 'Ruge Boogie', 'Ruluko' => 'Ruluko', 'Rum+Raisin' => 'Rum Raisin', 'Ruslan+Display' => 'Ruslan Display', 'Russo+One' => 'Russo One', 'Ruthie' => 'Ruthie', 'Rye' => 'Rye', 'Sacramento' => 'Sacramento', 'Sail' => 'Sail', 'Salsa' => 'Salsa', 'Sanchez' => 'Sanchez', 'Sancreek' => 'Sancreek', 'Sansita+One' => 'Sansita One', 'Sarina' => 'Sarina', 'Satisfy' => 'Satisfy', 'Scada' => 'Scada', 'Schoolbell' => 'Schoolbell', 'Seaweed+Script' => 'Seaweed Script', 'Sevillana' => 'Sevillana', 'Seymour+One' => 'Seymour One', 'Shadows+Into+Light' => 'Shadows Into Light', 'Shadows+Into+Light+Two' => 'Shadows Into Light Two', 'Shanti' => 'Shanti', 'Share' => 'Share', 'Share+Tech' => 'Share Tech', 'Share+Tech+Mono' => 'Share Tech Mono', 'Shojumaru' => 'Shojumaru', 'Short+Stack' => 'Short Stack', 'Siemreap' => 'Siemreap', 'Sigmar+One' => 'Sigmar One', 'Signika' => 'Signika', 'Signika+Negative' => 'Signika Negative', 'Simonetta' => 'Simonetta', 'Sirin+Stencil' => 'Sirin Stencil', 'Six+Caps' => 'Six Caps', 'Skranji' => 'Skranji', 'Slackey' => 'Slackey', 'Smokum' => 'Smokum', 'Smythe' => 'Smythe', 'Sniglet' => 'Sniglet', 'Snippet' => 'Snippet', 'Snowburst+One' => 'Snowburst One', 'Sofadi+One' => 'Sofadi One', 'Sofia' => 'Sofia', 'Sonsie+One' => 'Sonsie One', 'Sorts+Mill+Goudy' => 'Sorts Mill Goudy', 'Source+Code+Pro' => 'Source Code Pro', 'Source+Sans+Pro' => 'Source Sans Pro', 'Special+Elite' => 'Special Elite', 'Spicy+Rice' => 'Spicy Rice', 'Spinnaker' => 'Spinnaker', 'Spirax' => 'Spirax', 'Squada+One' => 'Squada One', 'Stalemate' => 'Stalemate', 'Stalinist+One' => 'Stalinist One', 'Stardos+Stencil' => 'Stardos Stencil', 'Stint+Ultra+Condensed' => 'Stint Ultra Condensed', 'Stint+Ultra+Expanded' => 'Stint Ultra Expanded', 'Stoke' => 'Stoke', 'Strait' => 'Strait', 'Sue+Ellen+Francisco' => 'Sue Ellen Francisco', 'Sunshiney' => 'Sunshiney', 'Supermercado+One' => 'Supermercado One', 'Suwannaphum' => 'Suwannaphum', 'Swanky+and+Moo+Moo' => 'Swanky and Moo Moo', 'Syncopate' => 'Syncopate', 'Tangerine' => 'Tangerine', 'Taprom' => 'Taprom', 'Telex' => 'Telex', 'Tenor+Sans' => 'Tenor Sans', 'Text+Me+One' => 'Text Me One', 'The+Girl+Next+Door' => 'The Girl Next Door', 'Tienne' => 'Tienne', 'Tinos' => 'Tinos', 'Titan+One' => 'Titan One', 'Titillium+Web' => 'Titillium Web', 'Trade+Winds' => 'Trade Winds', 'Trocchi' => 'Trocchi', 'Trochut' => 'Trochut', 'Trykker' => 'Trykker', 'Tulpen+One' => 'Tulpen One', 'Ubuntu' => 'Ubuntu', 'Ubuntu+Condensed' => 'Ubuntu Condensed', 'Ubuntu+Mono' => 'Ubuntu Mono', 'Ultra' => 'Ultra', 'Uncial+Antiqua' => 'Uncial Antiqua', 'Underdog' => 'Underdog', 'Unica+One' => 'Unica One', 'UnifrakturCook' => 'UnifrakturCook', 'UnifrakturMaguntia' => 'UnifrakturMaguntia', 'Unkempt' => 'Unkempt', 'Unlock' => 'Unlock', 'Unna' => 'Unna', 'VT323' => 'VT323', 'Vampiro+One' => 'Vampiro One', 'Varela' => 'Varela', 'Varela+Round' => 'Varela Round', 'Vast+Shadow' => 'Vast Shadow', 'Vibur' => 'Vibur', 'Vidaloka' => 'Vidaloka', 'Viga' => 'Viga', 'Voces' => 'Voces', 'Volkhov' => 'Volkhov', 'Vollkorn' => 'Vollkorn', 'Voltaire' => 'Voltaire', 'Waiting+for+the+Sunrise' => 'Waiting for the Sunrise', 'Wallpoet' => 'Wallpoet', 'Walter+Turncoat' => 'Walter Turncoat', 'Warnes' => 'Warnes', 'Wellfleet' => 'Wellfleet', 'Wendy+One' => 'Wendy One', 'Wire+One' => 'Wire One', 'Yanone+Kaffeesatz' => 'Yanone Kaffeesatz', 'Yellowtail' => 'Yellowtail', 'Yeseva+One' => 'Yeseva One', 'Yesteryear' => 'Yesteryear', 'Zeyada' => 'Zeyada');
       update_option('wf-optin-google-fonts', $fonts);
     }
   } // load_google_fonts


   // save all pointers in options
   static function load_pointers() {
     $pointers[] = array('target' => '#menu-posts-optin-pages', 'edge' => 'left', 'align' => 'right', 'content' => 'Thank you for installing <b>OptIn Ninja</b>! Use <a href="edit.php?post_type=optin-pages">this menu</a> to manage OptIn Pages and all other settings.');
     $pointers[] = array('target'=> 'tr.type-optin-pages:last', 'edge' => 'top', 'align' => 'center', 'content' => 'We\'ve already setup a couple of demo OptIn Pages so that you can get a quick start. Feel free to edit them, or delete if you want to start from scratch.');
     $pointers[] = array('target'=> '#stats-test-label', 'edge' => 'top', 'align' => 'left', 'content' => 'Our demo data also contains dummy statistics so that you can see how your graphs will look like. You can reset all stats in the <a href="admin.php?page=wf-optin-ninja-settings">Settings</a>.');
     $pointers[] = array('target'=> '.post-new-php.post-type-optin-pages #title', 'edge' => 'top', 'align' => 'left', 'content' => 'Did you know that in most cases preset settings will be more than enough to get your Page going. Just enter the title, content for two content boxes and you\'re all set.');
     $pointers[] = array('target'=> '#tag-url', 'edge' => 'left', 'align' => 'center', 'content' => 'A/B Tests can have the same URL as some OptIn Pages - their URL will override the Pages\'s one. You can create as many tests as needed and assign unlimited Pages to them. But each Page can belong to only one Test.');
     $pointers[] = array('target'=> '#wf-optin-ninja-options-page-tabs li:nth-child(2)', 'edge' => 'top', 'align' => 'left', 'content' => 'Autoresponders have to be configured before they\'re used in OptIn Pages. If you are not fond of 3rd party services feel free to use our built-in Subscibers Database.');
     $pointers[] = array('target'=> '#datatables', 'edge' => 'top', 'align' => 'left', 'content' => 'These are just some dummy subscribers to show you how things look. Feel free to delete them in the <a href="admin.php?page=wf-optin-ninja-settings">Settings</a>.');

     update_option('wf-optin-pointers', $pointers);
   } // load_pointers

   // setup everything when plugin is  activated
   static function activate() {
     require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

     self::load_google_fonts();
     self::load_pointers();

     // setup custom tables
     global $wpdb;

     $signups = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "" .  WF_OPT_SIGNUPS . "` (
                 `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                 `name` text NOT NULL,
                 `email` varchar(64) NOT NULL,
                 `post_id` int(10) unsigned NOT NULL,
                 `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                 `ip` varchar(15) NOT NULL DEFAULT '0.0.0.0',
                 PRIMARY KEY (`id`),
                 UNIQUE KEY `email` (`email`,`post_id`)
                 ) ENGINE=MyISAM DEFAULT CHARSET=utf8";

     $stats = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "" . WF_OPT_STATS . "` (
               `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
               `date` date NOT NULL,
               `post_id` int(10) unsigned NOT NULL,
               `views` mediumint(8) unsigned NOT NULL DEFAULT '0',
               `views_box2` mediumint(8) unsigned NOT NULL DEFAULT '0',
               `conversion` mediumint(8) unsigned NOT NULL DEFAULT '0',
               PRIMARY KEY (`id`),
               UNIQUE KEY `date` (`date`,`post_id`)
               ) ENGINE=MyISAM DEFAULT CHARSET=utf8";

     $ab_tests = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "" . WF_OPT_AB . "` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                  `name` varchar(128) NOT NULL,
                  `slug` varchar(64) NOT NULL,
                  `views` int(10) unsigned NOT NULL DEFAULT '0',
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `slug` (`slug`)
                  ) ENGINE=MyISAM DEFAULT CHARSET=utf8";

     dbDelta($signups);
     dbDelta($stats);
     dbDelta($ab_tests);
   } // activate


   static function import_demo_data() {
     global $wpdb;

     $tmp = self::import_xml('optin-pages.xml');
     if ($tmp) {
       $page1 = get_page_by_title('Sample OptIn #1', OBJECT, 'optin-pages');
       $page2 = get_page_by_title('Sample OptIn #2', OBJECT, 'optin-pages');

       if (!$page1 || !$page2) {
         return;
       }

       $meta = unserialize(base64_decode('YTo0OntzOjEwOiJvcHRpbi1mb3JtIjthOjExOntzOjExOiJmb3JtLWZpZWxkcyI7czoxMDoibmFtZS1lbWFpbCI7czoxNjoicGxhY2Vob2xkZXItbmFtZSI7czo5OiJZb3VyIG5hbWUiO3M6MTc6InBsYWNlaG9sZGVyLWVtYWlsIjtzOjEwOiJZb3VyIGVtYWlsIjtzOjIwOiJtYWlsLWxpc3Rpbmctc2VydmljZSI7czo1OiJsb2NhbCI7czoxNToiZmFjZWJvb2stYXBwLWlkIjtzOjA6IiI7czoyMjoiYWZ0ZXItc3Vic2NyaWJlLWFjdGlvbiI7czoxMDoic3RheS1hbGVydCI7czoxOToiYWZ0ZXItc3Vic2NyaWJlLXVybCI7czowOiIiO3M6Mjk6ImFmdGVyLXN1YnNjcmliZS1hbGVydC1tZXNzYWdlIjtzOjI2OiJUaGFuayB5b3UgZm9yIHN1YnNjcmliaW5nISI7czoyMzoiZ29vZ2xlLWFuYWx5dGljcy1ldmVudHMiO3M6MToiMCI7czoyMDoicHJldmVudC1mcm9tLWxlYXZpbmciO3M6MzoieWVzIjtzOjIxOiJwcmV2ZW50LWFsZXJ0LW1lc3NhZ2UiO3M6Njk6IkFyZSB5b3Ugc3VyZSB5b3Ugd2FudCB0byBsZWF2ZT8gWW91IHdpbGwgbWlzcyBvdXQgb24gdGhpcyBncmVhdCBkZWFsISI7fXM6MTQ6Im9wdGluLXNldHRpbmdzIjthOjExOntzOjIxOiJnb29nbGUtYW5hbHl0aWNzLWNvZGUiO3M6MDoiIjtzOjk6ImhlYWQtY29kZSI7czowOiIiO3M6MTU6ImJhY2tncm91bmQtdHlwZSI7czo1OiJpbWFnZSI7czoxNjoib3B0aW4tYmFja2dyb3VuZCI7YToxOntpOjA7czowOiIiO31zOjE2OiJzbGlkZXItYW5pbWF0aW9uIjtzOjE6IjAiO3M6MTQ6InNsaWRlLWludGVydmFsIjtzOjQ6IjEwMDAiO3M6MTY6ImJhY2tncm91bmQtY29sb3IiO3M6NzoiIzc3Nzc3NyI7czoxNjoiYmFja2dyb3VuZC1pbWFnZSI7czo2NToiaHR0cDovL3dwdGVzdDMzLnd0L3dwLWNvbnRlbnQvcGx1Z2lucy9vcHRpbi1uaW5qYS9pbWFnZXMvZm9vZC5wbmciO3M6MTk6ImJhY2tncm91bmQtcG9zaXRpb24iO3M6ODoibGVmdC10b3AiO3M6MTY6ImJhY2tncm91bmQtY292ZXIiO3M6NDoibm9uZSI7czoxNzoiYmFja2dyb3VuZC1yZXBlYXQiO3M6NjoicmVwZWF0Ijt9czoxMToiZmlyc3Qtb3B0aW4iO2E6MTU6e3M6MTA6Im9wdGluLXRleHQiO3M6NzI6IjxoMT7CoEJveCAjMSBDb250ZW50PC9oMT4NCkVkaXQgdGhpcyB0ZXh0IGFuZCByZXBsYWNlIGl0IHdpdGggeW91ciBjb3B5LiI7czoxNzoiYm94LWJvcmRlci1yYWRpdXMiO3M6MjoiMTAiO3M6MTY6ImJveC1ib3JkZXItd2lkdGgiO3M6MzoiMnB4IjtzOjE2OiJib3gtYm9yZGVyLWNvbG9yIjtzOjc6IiNiY2JjYmMiO3M6MjA6ImJveC1iYWNrZ3JvdW5kLWNvbG9yIjtzOjc6IiNmMmYyZjIiO3M6MTY6ImJveC1zaGFkb3ctY29sb3IiO3M6NzoiI2YyZjJmMiI7czoxNDoiYm94LWZvbnQtY29sb3IiO3M6NzoiIzExMTExMSI7czoxMjoiY29udGVudC1mb250IjtzOjk6Ik9wZW4rU2FucyI7czoxNzoiY29udGVudC1mb250LXNpemUiO3M6NDoiMThweCI7czoxMToiYnV0dG9uLXRleHQiO3M6MTg6IkdldCB0aGlzIGRlYWwgTk9XISI7czoxMToiYnV0dG9uLWZvbnQiO3M6NToiQmV2YW4iO3M6MTY6ImJ1dHRvbi1mb250LXNpemUiO3M6NDoiMjJweCI7czoyMDoiYnV0dG9uLWJvcmRlci1yYWRpdXMiO3M6MToiNSI7czoyMzoiYnV0dG9uLWJhY2tncm91bmQtY29sb3IiO3M6NzoiI0ZBQzU2NCI7czoxNzoiYnV0dG9uLXRleHQtY29sb3IiO3M6NzoiI2ZmZmZmZiI7fXM6MTI6InNlY29uZC1vcHRpbiI7YToxNTp7czoxMDoib3B0aW4tdGV4dCI7czo3MjoiPGgxPsKgQm94ICMyIENvbnRlbnQ8L2gxPg0KRWRpdCB0aGlzIHRleHQgYW5kIHJlcGxhY2UgaXQgd2l0aCB5b3VyIGNvcHkuIjtzOjE3OiJib3gtYm9yZGVyLXJhZGl1cyI7czoyOiIxMCI7czoxNjoiYm94LWJvcmRlci13aWR0aCI7czozOiIycHgiO3M6MTY6ImJveC1ib3JkZXItY29sb3IiO3M6NzoiI2JjYmNiYyI7czoyMDoiYm94LWJhY2tncm91bmQtY29sb3IiO3M6NzoiI2YyZjJmMiI7czoxNjoiYm94LXNoYWRvdy1jb2xvciI7czo3OiIjZjJmMmYyIjtzOjE0OiJib3gtZm9udC1jb2xvciI7czo3OiIjMTExMTExIjtzOjEyOiJjb250ZW50LWZvbnQiO3M6OToiT3BlbitTYW5zIjtzOjE3OiJjb250ZW50LWZvbnQtc2l6ZSI7czo0OiIxOHB4IjtzOjExOiJidXR0b24tdGV4dCI7czoyOToiU3Vic2NyaWJlIGFuZCBkb24ndCBtaXNzIG91dCEiO3M6MTE6ImJ1dHRvbi1mb250IjtzOjU6IkJldmFuIjtzOjE2OiJidXR0b24tZm9udC1zaXplIjtzOjQ6IjIycHgiO3M6MjA6ImJ1dHRvbi1ib3JkZXItcmFkaXVzIjtzOjE6IjUiO3M6MjM6ImJ1dHRvbi1iYWNrZ3JvdW5kLWNvbG9yIjtzOjc6IiNGQUM1NjQiO3M6MTc6ImJ1dHRvbi10ZXh0LWNvbG9yIjtzOjc6IiNmZmZmZmYiO319'));
       $meta['optin-settings']['background-image'] = plugins_url('/images/food.png', __FILE__);
       update_post_meta($page1->ID, 'wf_optin_meta', $meta);
       $meta = unserialize(base64_decode('YTo0OntzOjEwOiJvcHRpbi1mb3JtIjthOjExOntzOjExOiJmb3JtLWZpZWxkcyI7czoxMDoibmFtZS1lbWFpbCI7czoxNjoicGxhY2Vob2xkZXItbmFtZSI7czo5OiJZb3VyIG5hbWUiO3M6MTc6InBsYWNlaG9sZGVyLWVtYWlsIjtzOjEwOiJZb3VyIGVtYWlsIjtzOjIwOiJtYWlsLWxpc3Rpbmctc2VydmljZSI7czo1OiJsb2NhbCI7czoxNToiZmFjZWJvb2stYXBwLWlkIjtzOjA6IiI7czoyMjoiYWZ0ZXItc3Vic2NyaWJlLWFjdGlvbiI7czoxMDoic3RheS1hbGVydCI7czoxOToiYWZ0ZXItc3Vic2NyaWJlLXVybCI7czowOiIiO3M6Mjk6ImFmdGVyLXN1YnNjcmliZS1hbGVydC1tZXNzYWdlIjtzOjI2OiJUaGFuayB5b3UgZm9yIHN1YnNjcmliaW5nISI7czoyMzoiZ29vZ2xlLWFuYWx5dGljcy1ldmVudHMiO3M6MToiMCI7czoyMDoicHJldmVudC1mcm9tLWxlYXZpbmciO3M6MzoieWVzIjtzOjIxOiJwcmV2ZW50LWFsZXJ0LW1lc3NhZ2UiO3M6Njk6IkFyZSB5b3Ugc3VyZSB5b3Ugd2FudCB0byBsZWF2ZT8gWW91IHdpbGwgbWlzcyBvdXQgb24gdGhpcyBncmVhdCBkZWFsISI7fXM6MTQ6Im9wdGluLXNldHRpbmdzIjthOjExOntzOjIxOiJnb29nbGUtYW5hbHl0aWNzLWNvZGUiO3M6MDoiIjtzOjk6ImhlYWQtY29kZSI7czowOiIiO3M6MTU6ImJhY2tncm91bmQtdHlwZSI7czo1OiJpbWFnZSI7czoxNjoib3B0aW4tYmFja2dyb3VuZCI7YToxOntpOjA7czowOiIiO31zOjE2OiJzbGlkZXItYW5pbWF0aW9uIjtzOjE6IjAiO3M6MTQ6InNsaWRlLWludGVydmFsIjtzOjQ6IjEwMDAiO3M6MTY6ImJhY2tncm91bmQtY29sb3IiO3M6NzoiIzc3Nzc3NyI7czoxNjoiYmFja2dyb3VuZC1pbWFnZSI7czo2NToiaHR0cDovL3dwdGVzdDMzLnd0L3dwLWNvbnRlbnQvcGx1Z2lucy9vcHRpbi1uaW5qYS9pbWFnZXMvZm9vZC5wbmciO3M6MTk6ImJhY2tncm91bmQtcG9zaXRpb24iO3M6ODoibGVmdC10b3AiO3M6MTY6ImJhY2tncm91bmQtY292ZXIiO3M6NDoibm9uZSI7czoxNzoiYmFja2dyb3VuZC1yZXBlYXQiO3M6NjoicmVwZWF0Ijt9czoxMToiZmlyc3Qtb3B0aW4iO2E6MTU6e3M6MTA6Im9wdGluLXRleHQiO3M6MTA5OiI8aDE+wqA8c3BhbiBzdHlsZT0iY29sb3I6ICNlZjUwNDc7Ij5Cb3ggIzEgQ29udGVudDwvc3Bhbj48L2gxPg0KRWRpdCB0aGlzIHRleHQgYW5kIHJlcGxhY2UgaXQgd2l0aCB5b3VyIGNvcHkuIjtzOjE3OiJib3gtYm9yZGVyLXJhZGl1cyI7czoyOiIxMCI7czoxNjoiYm94LWJvcmRlci13aWR0aCI7czozOiIycHgiO3M6MTY6ImJveC1ib3JkZXItY29sb3IiO3M6NzoiI2JjYmNiYyI7czoyMDoiYm94LWJhY2tncm91bmQtY29sb3IiO3M6NzoiI2YyZjJmMiI7czoxNjoiYm94LXNoYWRvdy1jb2xvciI7czo3OiIjZjJmMmYyIjtzOjE0OiJib3gtZm9udC1jb2xvciI7czo3OiIjMTExMTExIjtzOjEyOiJjb250ZW50LWZvbnQiO3M6OToiT3BlbitTYW5zIjtzOjE3OiJjb250ZW50LWZvbnQtc2l6ZSI7czo0OiIxOHB4IjtzOjExOiJidXR0b24tdGV4dCI7czoxOToiR2V0IGluIG9uIHRoaXMgTk9XISI7czoxMToiYnV0dG9uLWZvbnQiO3M6NToiQmV2YW4iO3M6MTY6ImJ1dHRvbi1mb250LXNpemUiO3M6NDoiMjJweCI7czoyMDoiYnV0dG9uLWJvcmRlci1yYWRpdXMiO3M6MToiNSI7czoyMzoiYnV0dG9uLWJhY2tncm91bmQtY29sb3IiO3M6NzoiI2VmNTA0NyI7czoxNzoiYnV0dG9uLXRleHQtY29sb3IiO3M6NzoiI2ZmZmZmZiI7fXM6MTI6InNlY29uZC1vcHRpbiI7YToxNTp7czoxMDoib3B0aW4tdGV4dCI7czoxMDk6IjxoMT7CoDxzcGFuIHN0eWxlPSJjb2xvcjogI2VmNTA0NzsiPkJveCAjMiBDb250ZW50PC9zcGFuPjwvaDE+DQpFZGl0IHRoaXMgdGV4dCBhbmQgcmVwbGFjZSBpdCB3aXRoIHlvdXIgY29weS4iO3M6MTc6ImJveC1ib3JkZXItcmFkaXVzIjtzOjI6IjEwIjtzOjE2OiJib3gtYm9yZGVyLXdpZHRoIjtzOjM6IjJweCI7czoxNjoiYm94LWJvcmRlci1jb2xvciI7czo3OiIjYmNiY2JjIjtzOjIwOiJib3gtYmFja2dyb3VuZC1jb2xvciI7czo3OiIjZjJmMmYyIjtzOjE2OiJib3gtc2hhZG93LWNvbG9yIjtzOjc6IiNmMmYyZjIiO3M6MTQ6ImJveC1mb250LWNvbG9yIjtzOjc6IiMxMTExMTEiO3M6MTI6ImNvbnRlbnQtZm9udCI7czo5OiJPcGVuK1NhbnMiO3M6MTc6ImNvbnRlbnQtZm9udC1zaXplIjtzOjQ6IjE4cHgiO3M6MTE6ImJ1dHRvbi10ZXh0IjtzOjMyOiJHZXQgdGhlIHNwZWNpYWwgb2ZmZXIgdmlhIGVtYWlsISI7czoxMToiYnV0dG9uLWZvbnQiO3M6NToiQmV2YW4iO3M6MTY6ImJ1dHRvbi1mb250LXNpemUiO3M6NDoiMjJweCI7czoyMDoiYnV0dG9uLWJvcmRlci1yYWRpdXMiO3M6MToiNSI7czoyMzoiYnV0dG9uLWJhY2tncm91bmQtY29sb3IiO3M6NzoiI2VmNTA0NyI7czoxNzoiYnV0dG9uLXRleHQtY29sb3IiO3M6NzoiI2ZmZmZmZiI7fX0='));
       $meta['optin-settings']['background-image'] = plugins_url('/images/food.png', __FILE__);
       update_post_meta($page2->ID, 'wf_optin_meta', $meta);

       $wpdb->query('insert into ' . $wpdb->prefix . WF_OPT_AB . ' (`name`,`slug`,`views`) values (\'Sample A/B Test\',\'/sample-test/\',378)');

       $subs = "insert into " . $wpdb->prefix . WF_OPT_SIGNUPS . " (`id`,`name`,`email`,`post_id`,`timestamp`,`ip`) values (1,'John','john@test.com',ID1,'2017-04-22 12:33:00','107.170.145.187'),(4,'Jenny','jenny@test.com',ID2,'2017-04-24 05:32:00','54.240.196.185'),(2,'Jerry','jerry@test.com',ID2,'2017-04-21 08:33:00','70.39.185.232'),(3,'Mike','mike@test.com',ID2,'2017-04-25 02:33:00','157.83.125.12'),(5,'Ingrid Doe','ingrid@test.com',ID1,'2017-04-23 11:33:00','192.206.151.131')";
       $subs = str_replace('ID1', $page1->ID, $subs);
       $subs = str_replace('ID2', $page2->ID, $subs);
       $wpdb->query($subs);

       $stats = "insert into " . $wpdb->prefix . WF_OPT_STATS . " (`id`,`date`,`post_id`,`views`,`views_box2`,`conversion`) values (1,'2017-04-25',ID1,22,18,1),(2,'2017-04-25',ID2,20,15,0),(3,'2017-04-24',ID1,25,18,0),(4,'2017-04-24',ID2,20,20,0),(5,'2017-04-23',ID1,28,15,1),(6,'2017-04-23',ID2,22,20,1),(7,'2017-04-22',ID1,35,29,0),(8,'2017-04-22',ID2,27,26,0),(9,'2017-04-21',ID1,38,30,0),(10,'2017-04-21',ID2,28,25,0),(11,'2017-04-20',ID1,37,31,0),(12,'2017-04-20',ID2,27,24,0),(13,'2017-04-19',ID1,40,33,0),(14,'2017-04-19',ID2,32,30,1),(15,'2017-04-18',ID1,38,30,0),(16,'2017-04-18',ID2,30,25,1),(17,'2017-04-17',ID1,35,33,0),(18,'2017-04-17',ID2,29,29,0),(19,'2017-04-16',ID1,34,31,0),(20,'2017-04-16',ID2,30,28,0)";
       $stats = str_replace('ID1', $page1->ID, $stats);
       $stats = str_replace('ID2', $page2->ID, $stats);
       $wpdb->query($stats);
     }
   } // import_demo_data


   // convert color from hex to RGB
  static function hex2rgb($hex) {
     $hex = str_replace("#", "", $hex);

     if(strlen($hex) == 3) {
        $r = hexdec(substr($hex,0,1).substr($hex,0,1));
        $g = hexdec(substr($hex,1,1).substr($hex,1,1));
        $b = hexdec(substr($hex,2,1).substr($hex,2,1));
     } else {
        $r = hexdec(substr($hex,0,2));
        $g = hexdec(substr($hex,2,2));
        $b = hexdec(substr($hex,4,2));
     }
     $rgb = array($r, $g, $b);

     return $rgb;
  } // hex2rgb


   // imports WP export XML file
   static function import_xml($file){
    $error = false;
    if (!defined('WP_LOAD_IMPORTERS')) {
      define('WP_LOAD_IMPORTERS', true);
    }
    require_once ABSPATH . 'wp-admin/includes/import.php';

    if (!class_exists('WP_Import')) {
      $class_wp_import = WF_OPT_PLUGIN_DIR . '/wordpress-importer/wordpress-importer.php';
      if (file_exists($class_wp_import)) {
        require_once($class_wp_import);
      } else {
        $error = true;
      }
    }

    if ($error) {
      return false;
    } else {
      if(!is_file(WF_OPT_PLUGIN_DIR . '/xml/' . $file)) {
        return false;
      } else {
        ob_start();
        $wp_import = new wp_import();
        $wp_import->fetch_attachments = true;
        $wp_import->import(WF_OPT_PLUGIN_DIR . '/xml/' . $file);
        ob_end_clean();
      }
    }

    return true;
  } // import_xml


  static function shortcode_optin_popup($atts, $content = 'OptIn popup link'){
    $out = '';
    $atts = shortcode_atts(array(
        'id' => 0,
        'position' => 'center center',
        'class' => '',
        ), $atts);

    $link = get_permalink($atts['id']);
    if (!$link) {
      return 'Wrong OptIn Page ID.';
    }
    //$link = str_replace(home_url(), '', $link);
    $class = 'optin-popup ' . trim($atts['class']);
    $position = self::sanitize_position($atts['position']);

    $out = '<a data-optin-position="' . $position . '" href="' . $link . '" class="' . $class . '">' . $content . '</a>';

    return $out;
  } // shortcode_optin_popup

  static function sanitize_position($position) {
    $predefined = array('left top',    'center top',    'right top',
                        'left center', 'center center', 'right center',
                        'left bottom', 'center bottom', 'right bottom',
                        'left', 'center', 'right');

    $position = strtolower($position);
    if (!in_array($position, $predefined)) {
      return 'center center';
    } else {
      return $position;
    }
  } // sanitize_position


  static function shortcode_optin_test_popup($atts, $content = 'OptIn popup link'){
    global $wpdb;
    $out = '';
    $atts = shortcode_atts(array(
        'id' => 0,
        'class' => '',
        'position' => 'center',
        ), $atts);

    $link = $wpdb->get_var($wpdb->prepare('SELECT slug FROM ' . $wpdb->prefix . WF_OPT_AB . ' WHERE id = %d', $atts['id']));
    if (!$link) {
      return 'Wrong OptIn A/B Test ID.';
    }
    $link = home_url() . $link;
    $class = 'optin-popup ' . trim($atts['class']);
    $position = self::sanitize_position($atts['position']);

    $out = '<a data-optin-position="' . $position . '" href="' . $link . '" class="' . $class . '">' . $content . '</a>';

    return $out;
  } // shortcode_optin_test_popup

   // clean up on deactivate
   static function deactivate() {
     global $wpdb;

     $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . WF_OPT_AB);
     $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . WF_OPT_SIGNUPS);
     $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . WF_OPT_STATS);

     $optins = get_posts(array('post_type' => 'optin-pages', 'number' => -1));
     if ($optins) {
       foreach($optins as $optin) {
         wp_delete_post($optin->ID, true);
       }
     }

     delete_option('wf-optin-pointers');
     delete_option('wf-optin');
     delete_option('wf-optin-lists');
     delete_option('wf-optin-google-fonts');
     delete_option('wf_opt_flush_rewrite');
     delete_option('wf_optin_geodata');

     flush_rewrite_rules();
   } // deactivate
} // class wf_optin_ninja

add_action('init', array('wf_optin_ninja', 'init'));
register_activation_hook(__FILE__, array('wf_optin_ninja', 'activate'));
register_uninstall_hook(__FILE__, array('wf_optin_ninja', 'deactivate'));
