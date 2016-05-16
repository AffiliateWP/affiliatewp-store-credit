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

		add_action( 'affwp_affiliate_dashboard_after_campaign_stats', array( $this, 'the_store_credit' ) );
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
		// we may wish to extend this method to provide a default zero-balance
		// for affiliates that have a store credit balance of zero.
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
	 * @return mixed string A filterable text-only $notice and the current store credit amount, if any.
	 *                      Defaults to "You have a store credit balance of".
	 */
	public function the_store_credit() {

		// Bail if there is no store credit balance for the affiliate
		if ( ! $this->get_store_credit() ) {
			return;
		}

		// The notice to return indicating the affiliate has a balance
		$notice = __( 'You have a store credit balance of', 'affiliatewp-store-credit' );

		$notice = apply_filters( 'affwp_store_credit_affiliate_notice', $notice );

		// Get the store credit available, add to phrase
		$store_credit = wp_sprintf( ' %1s %2s.',
			$notice,
			$this->get_store_credit()
		);

		?>

		<table class="affwp-table affwp-store-credit-table">
			<thead>
				<tr>
					<th><?php _e( 'Available Store Credit', 'affiliatewp-store-credit' ); ?></th>
				</tr>
			</thead>

			<tbody>
				<tr>
					<td><?php esc_html_e( $store_credit, 'affiliatewp-store-credit' ); ?></td>
				</tr>
			</tbody>
		</table>

	<?php }
}
new AffiliateWP_Store_Credit_Dashboard;
