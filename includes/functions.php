<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get the store credit balance available to the affiliate
 *
 * @access public
 * @since 2.1.0
 * @return void
 */
function affwp_store_credit_balance() {

	// Get the user id
	$affiliate_id = affwp_get_affiliate_id();

	// Get the current store credit balance
	$current_balance = get_user_meta( $affiliate_id, 'affwp_wc_credit_balance', true );

	// Don't show anything if the affiliate has a zero balance
	// we may wish to extend this method to provide a default zero-balance
	// for affiliates that have a store credit balance of zero.
	if ( $current_balance <= 0 ) {
		return;
	}

	$current_balance = affwp_currency_filter( $current_balance );

	return $current_balance;

}