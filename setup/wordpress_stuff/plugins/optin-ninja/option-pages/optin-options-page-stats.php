<?php
/**
 * OptIn Ninja
 * (c) Web factory Ltd, 2016
 */

class wf_optin_options_stats extends wf_optin_ninja {
  // stats page markup
  static function content() {
    global $wpdb, $active_page, $active_test;

    echo '<div class="wrap optin-stats">';
    echo '<h2>OptIn Ninja Statistics</h2>';

    $active_page = $active_test = '';
    if (isset($_REQUEST['stats-page']) && is_numeric($_REQUEST['stats-page'])) {
      $active_page = $_REQUEST['stats-page'];
    }
    if (isset($_REQUEST['stats-test']) && !empty($_REQUEST['stats-test'])) {
      $active_test = $_REQUEST['stats-test'];
    }

    $optins = get_posts(array('post_type' => 'optin-pages', 'numberposts' => -1));
    $tests = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . WF_OPT_AB);

    echo '<div id="stats-selector">';
    echo '<form method="post" action="' . admin_url('admin.php?page=wf-optin-ninja-stats') . '">';
    echo '<label for="stats-page"><b>OptIn Pages:</b></label> <select id="stats-page" name="stats-page">';
    echo '<option value="">Please select an OptIn Page</option>';
    foreach ($optins as $optin) {
      if ($active_page == $optin->ID) {
        echo '<option value="' . $optin->ID . '" selected="selected">' . get_the_title($optin->ID)  . '</option>';
      } else {
        echo '<option value="' . $optin->ID . '">' . get_the_title($optin->ID)  . '</option>';
      }
    }
    echo '</select> ';
    echo '<button type="submit" href="#" class="button">Show stats</button>';
    echo '</form>';

    echo '<form method="post" action="' . admin_url('admin.php?page=wf-optin-ninja-stats') . '">';
    echo '<label for="stats-test" id="stats-test-label"><b>A/B Tests:</b></label> <select id="stats-test" name="stats-test">';
    echo '<option value="">Please select an A/B Test</option>';
    foreach ($tests as $test) {
      if ($active_test == $test->slug) {
        echo '<option value="' . $test->slug . '" selected="selected">' . $test->name  . '</option>';
      } else {
        echo '<option value="' . $test->slug . '">' . $test->name  . '</option>';
      }
    }
    echo '</select> ';
    echo '<button type="submit" href="#" class="button">Show stats</button>';

    echo '</form>';
    echo '</div>';


    if (!$active_page && !$active_test) {
      echo '<p>Please select an OptIn Page or an A/B Test to view their statistics.</p>';
    } elseif ($active_test) {
      echo '<div id="graph-selector">';
      echo '<b>Show graphs for:</b> <input type="checkbox" id="graph-views" value="views"> <label for="graph-views">1st Box Views</label>';
      echo ' <input type="checkbox" id="graph-views2" value="views2"> <label for="graph-views2">2nd Box Views</label>';
      echo ' <input checked="checked" type="checkbox" id="graph-conversion" value="conversions"> <label for="graph-conversion">Conversions</label>';
      echo '</div>';
      echo '<div id="optin-graph"></div>';

      echo '<table id="small-stats"><tr><th>Page Title</th><th>Box #1 Unique Views</th><th>Box #2 Unique Views</th><th>Conversions</th></tr>';
      $pages = get_posts(array('post_type' => 'optin-pages', 'meta_key' => '_ab-test', 'meta_value' => $active_test, 'orderby' => 'id', 'numberposts' => -1, 'posts_per_page' => -1));
      foreach ($pages as $page) {
        $views = (int) $wpdb->get_var("SELECT SUM(views) FROM " . $wpdb->prefix . WF_OPT_STATS . " WHERE post_id='" . $page->ID . "' ORDER by date ASC");
        $views_box2 = (int) $wpdb->get_var("SELECT SUM(views_box2) FROM " . $wpdb->prefix . WF_OPT_STATS . " WHERE post_id='" . $page->ID . "' ORDER by date ASC");
        $conversions = (int) $wpdb->get_var("SELECT SUM(conversion) FROM " . $wpdb->prefix . WF_OPT_STATS . " WHERE post_id='" . $page->ID . "' ORDER by date ASC");
        echo "<tr><td>$page->post_title</td><td>$views</td><td>$views_box2</td><td>$conversions</td></tr>";
      }
      echo '</table>';
      if (!$pages) {
        echo '<p>This A/B test doesn\'t have any pages associated with it.</p>';
      }

    } elseif ($active_page) {
        $page = get_page($active_page, OBJECT);
        $views = (int) $wpdb->get_var("SELECT SUM(views) FROM " . $wpdb->prefix . WF_OPT_STATS . " WHERE post_id='" . $active_page . "' ORDER by date ASC");
        $views_box2 = (int) $wpdb->get_var("SELECT SUM(views_box2) FROM " . $wpdb->prefix . WF_OPT_STATS . " WHERE post_id='" . $active_page . "' ORDER by date ASC");
        $conversions = (int) $wpdb->get_var("SELECT SUM(conversion) FROM " . $wpdb->prefix . WF_OPT_STATS . " WHERE post_id='" . $active_page . "' ORDER by date ASC");
        echo '<div id="optin-graph"></div>';
        echo '<table id="small-stats"><tr><th>Page Title</th><th>Box #1 Unique Views</th><th>Box #2 Unique Views</th><th>Conversions</th></tr>';
        echo "<tr><td>$page->post_title</td><td>$views</td><td>$views_box2</td><td>$conversions</td></tr>";
        echo '</table>';
    }
    echo '</div>';
  } // stats_page


  // custom scripts for stats graph
   static public function print_flot_scripts() {
     global $wpdb, $active_page, $active_test;
     $screen = get_current_screen();
     $js = '';

     $optin_ID = '';
     if ($active_page) {
       $optin_ID = $active_page;

       $views = $wpdb->get_results("SELECT date, views FROM " . $wpdb->prefix . WF_OPT_STATS . " WHERE post_id='" . $optin_ID . "' ORDER by date ASC");
       $views_box2 = $wpdb->get_results("SELECT date, views_box2 FROM " . $wpdb->prefix . WF_OPT_STATS . " WHERE post_id='" . $optin_ID . "' ORDER by date ASC");
       $conversions = $wpdb->get_results("SELECT date, conversion FROM " . $wpdb->prefix . WF_OPT_STATS . " WHERE post_id='" . $optin_ID . "' ORDER by date ASC");

       $js .= '<script type="text/javascript">';
       // DataSets Start
       $js .= 'var datasets = {';

       // Views
       $js .= '"views": {label: "Box #1 Unique Views",
                        data: [';
       $data = '';
       foreach ($views as $view) {
         $data .= '[' . strtotime($view->date)*1000 .', ' . $view->views . '],';
       }
       $data = rtrim($data, ',');
       $js .= $data . ']},';

       // Views Box2
       $js .= '"views2": {label: "Box #2 Unique Views",
                        data: [';
       $data = '';
       foreach ($views_box2 as $view) {
         $data .= '[' . strtotime($view->date)*1000 .', ' . $view->views_box2 . '],';
       }
       $data = rtrim($data, ',');
       $js .= $data . ']},';

       // Conversion
       $js .= '"conversion": {label: "Conversions",
                        data: [';
       $data = '';
       foreach ($conversions as $conversion) {
         $data .= '[' . strtotime($conversion->date)*1000 .', ' . $conversion->conversion . '],';
       }
       $data = rtrim($data, ',');
       $js .= $data . ']}';


       $js .= '};';
       // DataSets End

       $js .= '</script>';
       $js .= '<script type="text/javascript" src="' . plugins_url('/../js/optin-flot-page.js?ver=' . WF_OPT_VERSION, __FILE__) . '"></script>';
     }

     if ($active_test) {
       $pages = get_posts(array('post_type' => 'optin-pages', 'meta_key' => '_ab-test', 'meta_value' => $active_test, 'orderby' => 'id', 'numberposts' => -1, 'posts_per_page' => -1));


       if (!$pages) {
         // echo '<p>Selected Test does not have any Pages associated with it.</p>';
       } else {
         $js .= '<script type="text/javascript">';
         $js .= 'var datasets = {';

         foreach ($pages as $page) {
           $views = $wpdb->get_results("SELECT date, views FROM " . $wpdb->prefix . WF_OPT_STATS . " WHERE post_id='" . $page->ID . "' ORDER by date ASC");
           $views_box2 = $wpdb->get_results("SELECT date, views_box2 FROM " . $wpdb->prefix . WF_OPT_STATS . " WHERE post_id='" . $page->ID . "' ORDER by date ASC");
           $conversions = $wpdb->get_results("SELECT date, conversion FROM " . $wpdb->prefix . WF_OPT_STATS . " WHERE post_id='" . $page->ID . "' ORDER by date ASC");


       // Views
       $js .= '"views-' . $page->ID  . '": {optin_type: "views", label: "' . $page->post_title . ' Box #1 Unique Views",
                        data: [';
       $data = '';
       foreach ($views as $view) {
         $data .= '[' . strtotime($view->date)*1000 .', ' . $view->views . '],';
       }
       $data = rtrim($data, ',');
       $js .= $data . ']},';

       // Views Box2
       $js .= '"views2-' . $page->ID  . '": {optin_type: "views2", label: "' . $page->post_title . ' Box #2 Unique Views",
                        data: [';
       $data = '';
       foreach ($views_box2 as $view) {
         $data .= '[' . strtotime($view->date)*1000 .', ' . $view->views_box2 . '],';
       }
       $data = rtrim($data, ',');
       $js .= $data . ']},';

       // Conversion
       $js .= '"conversion-' . $page->ID . '": {optin_type: "conversions", label: "' . $page->post_title . ' Conversions",
                        data: [';
       $data = '';
       foreach ($conversions as $conversion) {
         $data .= '[' . strtotime($conversion->date)*1000 .', ' . $conversion->conversion . '],';
       }
       $data = rtrim($data, ',');
        $js .= $data . ']},';
       } // foreach page

       $js .= '};';
       $js .= '</script>';
       $js .= '<script type="text/javascript" src="' . plugins_url('/../js/optin-flot-test.js?ver=' . WF_OPT_VERSION, __FILE__) . '"></script>';
       } // if $pages
     } // if test

     echo $js;
   } // print_flot_scripts
} // wf_optin_options_stats