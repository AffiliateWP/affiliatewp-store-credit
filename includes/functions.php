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

	$integration = '';

	if ( class_exists( 'AffiliateWP_Store_Credit_WooCommerce' ) ) {
		$integration = 'woocommerce';
	} elseif ( class_exists( 'AffiliateWP_Store_Credit_EDD' ) && class_exists( 'EDD_Wallet' ) ) {
		$integration = 'edd';
	}

	if ( empty( $integration ) ) {
		return false;
	}

	switch ( $integration ) {

		case 'woocommerce':
			$current_balance = get_user_meta( $user_id, 'affwp_wc_credit_balance', true );
			break;	

		case 'edd':
			$current_balance = edd_wallet()->wallet->balance( $user_id );
			break;

	}

	$current_balance = affwp_currency_filter( affwp_format_amount( $current_balance ) );

	return $current_balance;

}