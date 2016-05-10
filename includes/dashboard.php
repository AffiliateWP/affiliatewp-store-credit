<?php

class AffiliateWP_Store_Credit_Dashboard {

	/**
	 * Get things started
	 *
	 * @access public
	 * @since 2.1.0
	 * @return void
	 */
	public function __construct() {

		add_action( 'affwp_affiliate_dashboard_notices', array( $this, 'the_store_credit' ) );
	}

	/**
	 * Get the store credit balance available to the affiliate
	 *
	 * @access public
	 * @since 2.1.0
	 * @return void
	 */
	public function get_store_credit() {

		// Get the user id
		$affiliate_id    = affwp_get_affiliate_id();

		// Get the current store credit balance
		$current_balance = get_user_meta( $affiliate_id, 'affwp_wc_credit_balance', true );

		// Don't show anything if the affiliate has a zero balance
		if ( $current_balance <= 0 ) {
			return;
		}

		$current_balance = affwp_currency_filter( $current_balance );

		return $current_balance;
	}

	/**
	 * Show the store credit balance available to the affiliate
	 *
	 * @access public
	 * @since  2.1.0
	 * @return mixed string A filterable text-only $phrase and the current store credit amount, if any.
	 *                      Defaults to "You have a store credit balance of"
	 */
	public function the_store_credit() {

		// Bail if no store credit balance
		if ( ! $this->get_store_credit() ) {
			return;
		}

		// The phrase to return
		$phrase       = __( 'You have a store credit balance of', 'affiliate-wp-store-credit' );

		apply_filters( 'affwp_show_store_credit_phrase', $phrase );

		// Get the store credit available, add to phrase
		$store_credit = wp_sprintf( $phrase . ' %1s.',
			$this->get_store_credit()
		);


		// Create final output
		$output       = '<div class="affwp-notice affwp-store-credit-notice">';
		$output      .= esc_html__( $store_credit, 'affiliate-wp-store-credit' );
		$output      .= do_action( 'affwp_store_credit_dashboard_notice' );
		$output      .= '</div>';


		echo $output;
	}
}
new AffiliateWP_Store_Credit_Dashboard;
