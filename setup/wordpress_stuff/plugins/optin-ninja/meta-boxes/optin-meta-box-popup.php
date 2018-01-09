<?php
/*
 * OptIn Ninja
 * (c) Web factory Ltd, 2016
 */

class wf_optin_ninja_popup extends wf_optin_ninja {
   static function content() {
     global $post;

     $options = get_option('wf-optin', array());

     if (isset($options['disable_popup']) && $options['disable_popup']) {
       echo '<p><b>Popups are disabled</b>. You can enable them in <a href="' . admin_url('admin.php?page=wf-optin-ninja-settings') . '">settings</a>.</p>';
     } else {
       $link = get_permalink($post->ID);
       $link = str_replace(home_url(), '', $link);

       echo '<h4>Manually opening a popup via link</h4>';
       echo '<ul><li>any link can open an OptIn in a popup if you add the <i>optin-popup</i> class to it and set the link (href parameter) to <i>'. $link .'</i>. Example:<br>
       &lt;a href="' . $link . '" class="optin-popup"&gt;click here&lt;/a&gt;</li>';
       echo '<li>you can also use the shortcode:<br>
       <i>[optin-popup id="' . $post->ID . '" class="optional-class" position="center"]click here[/optin-popup]</i></li>';
       echo '<li>only one popup can be open at a time; second one can\'t be opened until the first one is closed</li>';
       echo '<li>use the<br><i>wf_optin_open_popup( \'' . $link . '\', \'center\' )</i><br> JS function to open popups from your custom JS code</li>';
       echo '<li>available values for the position parameter are: left top, center top, right top, left center, center center, right center, left bottom, center bottom, right bottom, left, center, right</li>';
       echo '</ul>';

       echo '<h4>Automatically opening a popup based on predefined conditions</h4>';
       echo '<ul><li><a href="http://codecanyon.net/item/auto-popups-addon-for-optin-ninja/9316664?ref=WebFactory" target="_blank">Auto Popups add-on</a> enables you to automatically open popups based on many preset conditions and targeting options</li>';
       echo '<li>you can filter users who have already subscribed, users who are or are not logged in, and limit the amount of times the user can see a popup</li>';
       echo '<li>popups can be targeted to open only on the front/home page, on the search page, on a specified page, post or category</li>';
       echo '<li>popups can be opened when the page loads, or a certain amount of time after the page loads; they can also be opened when user intents to exit the page, or when a certain page object comes into view after user scrolls to it</li>';
       echo '</ul>';
     } // if popup
   } // content
} // wf_optin_ninja_popup