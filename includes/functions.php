<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get the affiliate's WooCommerce store credit balance.
 *
 * @access public
 * @since 2.1.0
 * 
 * @return string $current_balance The affiliate's current store credit balance.
 */
function affwp_store_credit_balance( $args = array() ) {

	// Get the affiliate ID.
	$affiliate_id = ! empty( $args['affiliate_id'] ) ? $args['affiliate_id'] : affwp_get_affiliate_id();

	// Get the affiliate's user ID.
	$user_id = affwp_get_affiliate_user_id( $affiliate_id );

	// Get the current store credit balance.
	$current_balance = get_user_meta( $user_id, 'affwp_wc_credit_balance', true );

	// Don't show anything if the affiliate has a zero balance
	// we may wish to extend this method to provide a default zero-balance
	// for affiliates that have a store credit balance of zero.
	if ( $current_balance <= 0 ) {
		return;
	}

	$current_balance = affwp_currency_filter( affwp_format_amount( $current_balance ) );

	return $current_balance;

}