<?php
/**
 * Admin View: Page - About
 *
 * @var string $view
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<br><br><br>
<hr>
<div class="feature-section one-col">
	<p class="lead-description"><?php _e("WooCommerce Customer Relationship helps you manage your customers more effectively with a tweak to the customer page, task and call filters as well as email shortcodes.", 'wc_crm'); ?></p>
</div>
<div class="feature-section two-col">
	<div class="col">
		<h3><?php _e( 'Redesigned Customers Page', 'woocommerce' ); ?></h3>
		<img id="ae-logo" src="<?php echo esc_url( WC_CRM()->assets_url ); ?>img/about-images/redesigned.jpg" alt="Actuality Extensions" />
		<p><?php _e( 'We have tweaked the customers page slightly to make use of the space available when viewed on bigger screens. The look and feel still remains the same for our existing users comfort. The billing and shipping fields have been moved vertically in one column above the map of their address.', 'wc_crm' ); ?></p>
	</div>
	<div class="col">
		<h3><?php _e( 'Email Shortcodes', 'woocommerce' ); ?></h3>
		<img id="ae-logo" src="<?php echo esc_url( WC_CRM()->assets_url ); ?>img/about-images/email-shortcodes.jpg" alt="Actuality Extensions" />
		<p><?php _e( 'Sending emails with automated generated information just got easier. Taking inspiration from our Order Status & Actions Manager plugin, we have included the ability to include shortcodes in the email which fetches information from the customers profile, making the process of sending emails to one or multiple customers even easier. ', 'wc_crm' ); ?></p>
	</div>
</div>
<div class="feature-section two-col">
	<div class="col">
		<h3><?php _e( 'Calls & Tasks Filters', 'woocommerce' ); ?></h3>
		<img id="ae-logo" src="<?php echo esc_url( WC_CRM()->assets_url ); ?>img/about-images/calls.jpg" alt="Actuality Extensions" />
		<p><?php _e( 'Organising through the calls and tasks created for the customer was a nightmare. We have made this easier by introducing filters for the common fields found in both calls and tasks. This allows you to narrow down that specific call you made to Jack in March 2015.', 'wc_crm' ); ?></p>
	</div>
	<div class="col">
		<h3><?php _e( 'Search Parameters', 'woocommerce' ); ?></h3>
		<img id="ae-logo" src="<?php echo esc_url( WC_CRM()->assets_url ); ?>img/about-images/search-parameters.jpg" alt="Actuality Extensions" />
		<p><?php _e( 'There are multiple fields to search for in the customers card. You can now assign which field to look in when searching for customers in the search box on the Customers page. Simply go to Customers > Settings > Search Parameters and you can now define exactly what fields to look in when querying a search.', 'wc_crm' ); ?></p>
	</div>
</div>