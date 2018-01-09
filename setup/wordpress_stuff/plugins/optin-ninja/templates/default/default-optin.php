<?php
/**
 * OptIn Ninja
 * (c) Web factory Ltd, 2016
 */

if (!function_exists('get_header')) {
  die();
}

global $post, $wp_styles;
@$wp_styles->queue = array();
	
	
if (isset($_GET['popup'])) {
  $views = @$_COOKIE['optin_ninja_views'];
  $views = @unserialize($views);
  @$views[$post->ID]++;
  setcookie('optin_ninja_views', serialize($views), time() + DAY_IN_SECONDS * 60, '/');
}

$meta = get_post_meta($post->ID, 'wf_optin_meta', true);
$meta['first-optin']['disable-first-box'] = (int) @$meta['first-optin']['disable-first-box'];

wp_enqueue_style('optin', WF_OPT_PLUGINURL . '/templates/style.css', array(), WF_OPT_VERSION);
wp_enqueue_script('optin-fitvid', WF_OPT_PLUGINURL . '/templates/fitvid.js', array('jquery'), WF_OPT_VERSION);
wp_enqueue_script('optin-actions', WF_OPT_PLUGINURL . '/templates/optin.js', array('jquery'), WF_OPT_VERSION);

wp_localize_script('optin-actions', 'optin_vars', array('ajaxurl' => admin_url('admin-ajax.php'), 'postID' => $post->ID, 'ga_events' => (int) $meta['optin-form']['google-analytics-events'], 'ga_track' => (int) !empty($meta['optin-settings']['google-analytics-code']), 'post_title' => get_the_title(), 'after_subscribe_action' => $meta['optin-form']['after-subscribe-action'], 'after_subscribe_url' => $meta['optin-form']['after-subscribe-url'], 'after_subscribe_message' => $meta['optin-form']['after-subscribe-alert-message'], 'video_id' => trim(@$meta['optin-settings']['video-id']), 'custom_fields_addon' => class_exists('wf_optin_ninja_fields') ));

// Supersized background slider
if ($meta['optin-settings']['background-type'] == 'slider') {
  wp_enqueue_style('optin-supersized-css', WF_OPT_PLUGINURL . '/templates/assets/supersized/css/supersized.css', array(), WF_OPT_VERSION);
  wp_enqueue_style('optin-supersized-theme-css', WF_OPT_PLUGINURL . '/templates/assets/supersized/theme/supersized.shutter.css', array(), WF_OPT_VERSION);
  wp_enqueue_script('optin-supersized-easing', WF_OPT_PLUGINURL . '/templates/assets/supersized/js/jquery.easing.min.js', array('jquery'), WF_OPT_VERSION, true);
  wp_enqueue_script('optin-supersized-js', WF_OPT_PLUGINURL . '/templates/assets/supersized/js/supersized.3.2.7.min.js', array('jquery'), WF_OPT_VERSION, true);
  wp_enqueue_script('optin-supersized-shutter', WF_OPT_PLUGINURL . '/templates/assets/supersized/theme/supersized.shutter.min.js', array('jquery'), WF_OPT_VERSION, true);
}

// video background
if ($meta['optin-settings']['background-type'] == 'video') {
  wp_enqueue_script('optin-tubular', WF_OPT_PLUGINURL . '/templates/jquery.tubular.1.0.js', array('jquery'), WF_OPT_VERSION, true);
}

if ($meta['first-optin']['disable-first-box']) {
  wf_optin_ninja::count_stats('2');
} else {
  wf_optin_ninja::count_stats('1');
}

if ($meta['optin-form']['mail-listing-service'] == 'facebook') {
  echo '<html xmlns:fb="http://www.facebook.com/2008/fbml">';
} else {
  echo '<!DOCTYPE html>';
}
?>
<!--[if IE 7]>
<html class="ie ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 7) | !(IE 8) ]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
  <title><?php the_title(); ?></title>
  <link rel="profile" href="http://gmpg.org/xfn/11">

  <?php
// Google Analytics Code
if ($meta['optin-settings']['google-analytics-code'] != '') {
  echo $meta['optin-settings']['google-analytics-code'];
}

  /* first box */
  $settings = $meta['first-optin'];
    echo '<style type="text/css">' . "\r\n";
    echo '.first-optin {' . "\r\n";

    // Background Color
    if ($settings['box-background-color'] != '' && $settings['bg-opacity'] != '') {
      $color = wf_optin_ninja::hex2rgb($settings['box-background-color']);
      echo 'background-color: rgba(' . implode(',', $color) . ', ' . $settings['bg-opacity'] . ') !important;' . "\r\n";
    }

    if ($settings['background-image']) {
      echo 'background-image: url("' . $settings['background-image'] . '"); ';
    }
    if ($settings['background-cover'] != 'none') {
      echo 'background-size: ' . $settings['background-cover'] . '; ';
    } else {
      echo 'background-position: ' . str_replace('-', ' ', $settings["background-position"]) . '; ';
      echo 'background-repeat: ' . $settings["background-repeat"] . '; ';
    }

    // Font Color
    if ($settings['box-font-color'] != '') {
      echo 'color:' . $settings['box-font-color'] . ' !important;' . "\r\n";
    }

    // Font Size
    if ($settings['content-font-size'] != '') {
      echo 'font-size:' . $settings['content-font-size'] . ' !important;' . "\r\n";
    }

    // Box Border Radius
    if ($settings['box-border-radius'] != '') {
      echo 'border-radius:' . $settings['box-border-radius'] . 'px !important;' . "\r\n";
    }

    // Box Border Width
    if ($settings['box-border-width'] != '0px') {
      echo 'border:' . $settings['box-border-width'] . ' solid ' . $settings['box-border-color'] .';' . "\r\n";
    }

    // Box Shadow Setup
    if ($settings['box-shadow-color'] != '') {
      echo 'box-shadow: 0 0 5px ' . $settings['box-shadow-color'] . ' !important;' . "\r\n";
    }

    echo '}' . "\r\n";


    // First Optin Button Setup
    echo '.first-optin .btn {' . "\r\n";
    if ($settings['button-border-radius'] != '') {
      echo 'border-radius:' . $settings['button-border-radius'] . 'px;' . "\r\n";
    }

    if ($settings['button-background-color'] != '') {
      echo 'background-color:' . $settings['button-background-color'] . ';' . "\r\n";
    }

    if ($settings['button-text-color'] != '') {
      echo 'color:' . $settings['button-text-color'] . ';' . "\r\n";
    }

    if ($settings['button-font-size'] != '') {
      echo 'font-size:' . $settings['button-font-size'] . ' !important;' . "\r\n";
    }

    echo '}' . "\r\n";


    echo '</style>' . "\r\n";
  // first box

  // second box
  $settings = $meta['second-optin'];
    echo '<style type="text/css">' . "\r\n";
    echo '.second-optin {' . "\r\n";

    // Background Color
    if ($settings['box-background-color'] != '' && $settings['bg-opacity'] != '') {
      $color = wf_optin_ninja::hex2rgb($settings['box-background-color']);
      echo 'background-color: rgba(' . implode(',', $color) . ', ' . $settings['bg-opacity'] . ') !important;' . "\r\n";
    }

    if ($settings['background-image']) {
      echo 'background-image: url("' . $settings['background-image'] . '"); ';
    }
    if ($settings['background-cover'] != 'none') {
      echo 'background-size: ' . $settings['background-cover'] . '; ';
    } else {
      echo 'background-position: ' . str_replace('-', ' ', $settings["background-position"]) . '; ';
      echo 'background-repeat: ' . $settings["background-repeat"] . '; ';
    }

    // Font Color
    if ($settings['box-font-color'] != '') {
      echo 'color:' . $settings['box-font-color'] . ' !important;' . "\r\n";
    }

    // Font Size
    if ($settings['content-font-size'] != '') {
      echo 'font-size:' . $settings['content-font-size'] . ' !important;' . "\r\n";
    }

    // Box Border Radius
    if ($settings['box-border-radius'] != '') {
      echo 'border-radius:' . $settings['box-border-radius'] . 'px !important;' . "\r\n";
    }

    // Box Border Width
    if ($settings['box-border-width'] != '0px') {
      echo 'border:' . $settings['box-border-width'] . ' solid ' . $settings['box-border-color'] .';' . "\r\n";
    }

    // Box Shadow Setup
    if ($settings['box-shadow-color'] != '') {
      echo 'box-shadow: 0 0 5px ' . $settings['box-shadow-color'] . ' !important;' . "\r\n";
    }


    echo '}' . "\r\n";

    // Second Optin Button Setup
    echo '.second-optin .btn {' . "\r\n";
    if ($settings['button-border-radius'] != '') {
      echo 'border-radius:' . $settings['button-border-radius'] . 'px;' . "\r\n";
    }

    if ($settings['button-background-color'] != '') {
      echo 'background-color:' . $settings['button-background-color'] . ';' . "\r\n";
    }

    if ($settings['button-text-color'] != '') {
      echo 'color:' . $settings['button-text-color'] . ';' . "\r\n";
    }

    if ($settings['button-font-size'] != '') {
      echo 'font-size:' . $settings['button-font-size'] . ' !important;' . "\r\n";
    }
    echo '}' . "\r\n";

    // Second Optin Input Setup
    echo '.second-optin input, .second-optin textarea, .second-optin select {' . "\r\n";
    if ($settings['input-text-color'] != '') {
      echo 'color:' . $settings['input-text-color'] . ' !important;' . "\r\n";
    }
    if ($settings['input-background-color'] != '') {
      echo 'background-color:' . $settings['input-background-color'] . ' !important;' . "\r\n";
    }
    if ($settings['input-border-color'] != '') {
      echo 'border-color:' . $settings['input-border-color'] . ' !important;' . "\r\n";
    }
    if ($settings['input-border-radius'] != '') {
      echo 'border-radius:' . $settings['input-border-radius'] . 'px !important;' . "\r\n";
    }
    echo '}';

    echo '</style>' . "\r\n";
  // second box

   /* Optin Settings */
   $settings = $meta['optin-settings'];
   // Background is single image? or slideshow?
   if ($settings['background-type'] == 'image') {
     echo '<style type="text/css" id="bg-settings">';
     echo 'body {
             background-image: url("' . $settings['background-image'] . '"); ';
       if ($settings['background-cover'] != 'none') {
         echo 'background-size: ' . $settings['background-cover'] . '; ';
         echo 'background-repeat: ' . $settings["background-repeat"] . '; ';
       } else {
         echo 'background-position: ' . str_replace('-', ' ', $settings["background-position"]) . '; ';
         echo 'background-repeat: ' . $settings["background-repeat"] . '; ';
       }
     echo 'background-color: ' .  $settings['background-color'] .'; ';
     echo '}';
     echo '</style>';
   }


  // Google Fonts Active?
  $settings = $meta['first-optin'];
  if ($settings['content-font'] != '') {
    echo '<link href="//fonts.googleapis.com/css?family=' . $settings['content-font'] . ':400,300,300italic,400italic,600,600italic,700,700italic,800,800italic" rel="stylesheet" type="text/css">' . "\r\n";
    echo '<style type="text/css">';
    echo '.first-optin {' . "\r\n";
    echo 'font-family:"' . str_replace('+', ' ', $settings['content-font']) . '", sans-serif;' . "\r\n";
    echo 'font-size:' . $settings['content-font-size'] . ';' . "\r\n";
    echo '}' . "\r\n";
    echo '</style>';
  } // if ($settings['content-font'] != '')

  // Button Text
  if ($settings['button-font-size'] != '') {
    echo '<link href="//fonts.googleapis.com/css?family=' . $settings['button-font'] . ':400,300,300italic,400italic,600,600italic,700,700italic,800,800italic" rel="stylesheet" type="text/css">' . "\r\n";
    echo '<style type="text/css">';
    echo '.first-optin .btn {' . "\r\n";
    echo 'font-family:"' . str_replace('+', ' ', $settings['button-font']) . '", sans-serif;' . "\r\n";
    echo '}' . "\r\n";
    echo '</style>';
  } // if ($settings['button-font-size'] != '')

  $settings = $meta['second-optin'];
  if ($settings['content-font'] != '') {
    echo '<link href="//fonts.googleapis.com/css?family=' . $settings['content-font'] . ':400,300,300italic,400italic,600,600italic,700,700italic,800,800italic" rel="stylesheet" type="text/css">' . "\r\n";
    echo '<style type="text/css">';
    echo '.second-optin {' . "\r\n";
    echo 'font-family:"' . str_replace('+', ' ', $settings['content-font']) . '", sans-serif;' . "\r\n";
    echo 'font-size:' . $settings['content-font-size'] . ';' . "\r\n";
    echo '}' . "\r\n";
    echo '</style>';
  } // if ($settings['content-font'] != '')

  // Button Text
  if ($settings['button-font-size'] != '') {
    echo '<link href="//fonts.googleapis.com/css?family=' . $settings['button-font'] . ':400,300,300italic,400italic,600,600italic,700,700italic,800,800italic" rel="stylesheet" type="text/css">' . "\r\n";
    echo '<style type="text/css">';
    echo '.second-optin .btn {' . "\r\n";
    echo 'font-family:"' . str_replace('+', ' ', $settings['button-font']) . '", sans-serif;' . "\r\n";
    echo '}' . "\r\n";
    echo '</style>';
  } // if ($settings['button-font-size'] != '')

  if ($meta['optin-settings']['head-code'] != '') {
    echo $meta['optin-settings']['head-code'];
  }
?>
</head>
<body>
<div id="video-wrapper">
<div class="wf-optin-container">
<?php
  if (!$meta['first-optin']['disable-first-box']) {
?>
  <div class="wf-optin-box box-border-radius box-background-color first-optin">
  <div class="wf-optin-box-inner">

    <?php
    echo apply_filters('the_content', $meta['first-optin']['optin-text']);

    if ($meta['first-optin']['button-text']) {
      echo '<a class="btn btn-primary btn-lg btn-block next-optin" href="#">' . $meta['first-optin']['button-text'] . '</a>';
    } else {
      echo '<a class="btn btn-primary btn-lg btn-block next-optin" href="#">Click to continue</a>';
    }

    if (isset($meta['first-optin']['footer-text']) && !empty($meta['first-optin']['footer-text'])) {
      echo '<p class="footer">' . $meta['first-optin']['footer-text'] . '</p>';
    }
    ?>
  </div>
  </div>
<?php
  }
  if (!$meta['first-optin']['disable-first-box']) {
    echo '<div class="wf-optin-box box-border-radius box-background-color second-optin" style="display: none;">';
  } else {
    echo '<div class="wf-optin-box box-border-radius box-background-color second-optin">';
  }
?>
  <div class="wf-optin-box-inner">

    <?php
    echo apply_filters('the_content', $meta['second-optin']['optin-text']);

    if ($meta['optin-form']['mail-listing-service'] != 'facebook') {
      echo '<form method="post" action="#" class="ajax-subscribe">';
      $css = '';

      $fields = '';

      if (class_exists('wf_optin_ninja_fields')) {
        $fields = apply_filters('optin_ninja_template_fields', $fields, $post->ID);
      } else {
        if ($meta['optin-form']['form-fields'] != 'email') {
        // First Name
          $fields .= '<div class="name-input" style="' . $css . '">';
          $fields .= '<input data-required="1" type="text" name="name" id="name" value="" placeholder="' . $meta['optin-form']['placeholder-name'] . '" data-error="Please input your name" />';
          $fields .= '</div>';
        }

        // E-mail Input
        $fields .= '<div class="email-input">';
        $fields .= '<input data-required="1" type="text" name="email" id="email" value="" placeholder="' . $meta['optin-form']['placeholder-email'] . '" data-error="Please input your e-mail" />';
        $fields .= '</div>';
      }
      echo $fields;

      // Submit Button
      echo '<div class="submit-input">';
      echo '<button data-default-value="' . $meta['second-optin']['button-text'] . '" id="submit" name="submit" class="btn btn-primary btn-lg btn-block">' . $meta['second-optin']['button-text'] . '</button>';
      echo '</div>';

      echo '</form>';
    } else { // facebook
      echo '<div id="fb-root"></div>';
      echo '<div class="ajax-subscribe">';
      echo '<div class="submit-input">';
      echo '<button data-default-value="' . $meta['second-optin']['button-text'] . '" id="submit" name="submit" class="btn btn-primary btn-lg btn-block">' . $meta['second-optin']['button-text'] . '</button>';
      echo '</div></div>';
    } // if ($meta['optin-form']['mail-listing-service'] != 'facebook')

    if (isset($meta['second-optin']['footer-text']) && !empty($meta['second-optin']['footer-text'])) {
      echo '<p class="footer">' . $meta['second-optin']['footer-text'] . '</p>';
    }
?>

  </div>
  </div>
</div>
</div>
<?php
  wf_optin_ninja::clean_footer();
  wp_footer();
?>
<script type="text/javascript">
var subscribed = false;
  jQuery(document).ready(function($){
<?php
    if ($meta['optin-form']['mail-listing-service'] == 'facebook') {
?>
    $.ajaxSetup({ cache: 0 });
    $.getScript('//connect.facebook.net/en_UK/all.js', function(){
      FB.init({appId: '<?php echo $meta['optin-form']['facebook-app-id'] ?>'});
      FB.getLoginStatus(updateStatusCallback);
    });

    function updateStatusCallback(status){
      return;
    } // updateStatusCallback

    $('#submit').on('click', function() {
      begin_submit();
      FB.login(function(response) {
        if (response.authResponse) {
          subscribe_by_facebook();
        } else {
          end_submit();
        }
      }, {scope: 'email'});

      return false;
    });

    function subscribe_by_facebook() {
      FB.api('/me', function(response) {
        if (response.name != '' && response.email != '') {
          fields = {name:response.name, email:response.email};
          $.post(optin_vars.ajaxurl, {action: 'optin_subscribe', 'fields': fields, post_id: optin_vars.postID},
           function (response) {
            end_submit();
            if (response == '3') {
              alert('You are already subscribed to our list.');
            } else if (response == '1') {
              exit_optin();
            } else {
              alert('An undocumented error has occured. Please reload the page and try again.');
            }
          });
        }
      });
    } // subscribe_by_facebook
    <?php
    } else {
    ?>
    $('.ajax-subscribe').submit(function(){
        if (validate_fields()) {
          begin_submit();
          fields = $('.ajax-subscribe input, .ajax-subscribe textarea, .ajax-subscribe select').serialize();
          $.post(optin_vars.ajaxurl, {action: 'optin_subscribe', 'fields': fields, post_id: optin_vars.postID},
          function (response) {
            end_submit();
            if (response == '1') {
              exit_optin();
            } else if (response == '3') {
              alert('You are already subscribed to our list.');
            } else {
              alert('An undocumented error has occured. Please reload the page and try again.');
            }
          });
        }

        return false;
      });
    <?php
    }
    if (($meta['optin-settings']['background-type']) == 'video') {
    ?>
    if (!window.parent.document.getElementById('wf-optin-iframe')) {
      $('#video-wrapper').tubular({ videoId: optin_vars.video_id, mute: true, repeat: true });
    }

    <?php
    }
    if (($meta['optin-settings']['background-type']) == 'slider') {
    ?>
    <!-- Supersized Background Slider Setup -->
    if (!window.parent.document.getElementById('wf-optin-iframe')) {
    $.supersized({
		  // Functionality
		  slideshow: 1,			// Slideshow on/off
			autoplay:	1,			// Slideshow starts playing automatically
			slide_interval: <?php echo (int) $meta['optin-settings']['slide-interval']; ?>,		// Length between transitions
			transition: <?php echo (int) $meta['optin-settings']['slider-animation']; ?>, 			// 0-None, 1-Fade, 2-Slide Top, 3-Slide Right, 4-Slide Bottom, 5-Slide Left, 6-Carousel Right, 7-Carousel Left
			transition_speed:	1000,		// Speed of transition
			new_window:	1,			// Image links open in new window/tab
			pause_hover: 0,			// Pause slideshow on hover
			keyboard_nav: 1,			// Keyboard navigation on/off
			performance: 1,			// 0-Normal, 1-Hybrid speed/quality, 2-Optimizes image quality, 3-Optimizes transition speed // (Only works for Firefox/IE, not Webkit)
			image_protect: 1,			// Disables image dragging and right click with Javascript

			// Size & Position
			min_width: 0,			// Min width allowed (in pixels)
			min_height: 0,			// Min height allowed (in pixels)
			vertical_center: 1,			// Vertically center background
			horizontal_center: 1,			// Horizontally center background
			fit_always:	0,			// Image will never exceed browser width or height (Ignores min. dimensions)
			fit_portrait: 1,			// Portrait images will not exceed browser height
	  	fit_landscape: 0,			// Landscape images will not exceed browser width

			// Components
			slide_links:	'blank',	// Individual links for each slide (Options: false, 'num', 'name', 'blank')
			thumb_links: 1,			// Individual thumb links for each slide
			thumbnail_navigation: 0,			// Thumbnail navigation
			slides:  	[
      <?php
      $output = '';
      foreach ($meta['optin-settings']['optin-background'] as $key => $value) {
        $output .= '{image : "' . $value . '"},';
      }
      $output = rtrim($output, ',');
      echo $output;
      ?>],

			// Theme Options
			progress_bar:	1,			// Timer for each slide
			mouse_scrub:	0
    });
    } else {
      $('#supersized-loader').hide();
    }
    <?php } ?>

    <?php
    // Prevent From Leaving without Subscribing
    echo wf_optin_ninja::prevent_from_leaving();
    ?>
  }); // onload
</script>
</body>
</html>