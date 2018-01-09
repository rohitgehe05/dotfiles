<?php
/*
 * OptIn Ninja
 * (c) Web factory Ltd, 2016
 */

 class wf_optin_ninja_links_box extends wf_optin_ninja {
   public static function content() {
     echo '<ul class="lists">';
     echo '<li><a href="#optin-ninja-general-box">General settings</a></li>';
     echo '<li><a href="#optin-ninja-settings-box">Background</a></li>';
     echo '<li><a href="#optin-ninja-first-box">First content box (content and style)</a></li>';
     echo '<li><a href="#optin-ninja-second-box">Second content box (content and style)</a></li>';
     echo '<li><a href="#optin-ninja-popup">Popup/lightbox options</a></li>';
     echo '<li><a href="#optin-ninja-form-settings">Form fields &amp; Autoresponder</a></li>';
     echo '<li><a href="#optin-ninja-autoresponder">Welcome email &amp; local autoresponder</a></li>';
     echo '<li><a href="#optin-ninja-notifications">Notifications</a></li>';
     echo '<li><a href="http://codecanyon.net/user/WebFactory#contact" target="_blank">Support</a></li>';
     echo '<li><a href="' . plugin_dir_url(dirname(__FILE__) . '/../optin-ninja.php' ) . 'documentation/' . '" target="_blank">Documentation</a></li>';
     echo '</ul>';

   }
 } // wf_optin_ninja_links_box