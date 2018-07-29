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
		add_action( 'affwp_affiliate_dashboard_before_submit', array( $this, 'enable_store_credit_output' ), 10, 2 );
		add_action( 'affwp_update_affiliate_profile_settings', array( $this, 'save_profile_settings' ), 10, 1 );
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
		if ( ! affwp_store_credit_balance() ) {
			return;
		}

		// The notice to return indicating the affiliate has a balance
		$notice = __( 'You have a store credit balance of', 'affiliatewp-store-credit' );

		$notice = apply_filters( 'affwp_store_credit_affiliate_notice', $notice );

		// Get the store credit available, add to phrase
		$store_credit = wp_sprintf( ' %1s %2s.',
			$notice,
			affwp_store_credit_balance()
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

	/**
	 * Output the Enable payout via store credit field in the Affiliate Dashboard -> Settings
	 *
	 * @since 2.3
	 *
	 * @param  int $affiliate_id The Affiliates ID
	 * @param  int $user_id      The User ID for this affiliate
	 *
	 * @return void
	 */
	public function enable_store_credit_output( $affiliate_id, $user_id) {

		$store_credit_enabled = affiliate_wp()->settings->get( 'store-credit' );

		if ( ! $store_credit_enabled ) {
			return;
		}

		$global_store_credit_enabled = affiliate_wp()->settings->get( 'store-credit-all-affiliates' );

		if ( $global_store_credit_enabled ) {
			return;
		}

		$change_payment_method = affiliate_wp()->settings->get( 'store-credit-change-payment-method' );

		if ( ! $change_payment_method ) {
			return;
		}

		$affiliate_store_credit_enabled = affwp_get_affiliate_meta( $affiliate_id, 'store_credit_enabled', true );

		?>

		<div class="affwp-wrap affwp-sc-enable-store-credit-wrap">
			<label for="affwp-sc-enable-store-credit">
				<input type="checkbox" name="affwp_sc_enable_store_credit" id="affwp-sc-enable-store-credit" value="1" <?php checked( $affiliate_store_credit_enabled, true ); ?> />
				<?php _e( 'Enable payout via store credit', 'affiliatewp-store-credit' ); ?>
			</label>
		</div>

	<?php }

	/**
	 * Save the changes made to the Settings page of the Affiliate Dashboard
	 *
	 * @since 2.3
	 *
	 * @param  array $data Data from the saving of the form
	 *
	 * @return void
	 */
	public function save_profile_settings( $data ) {

		$change_payment_method = affiliate_wp()->settings->get( 'store-credit-change-payment-method' );

		if ( ! $change_payment_method ) {
			return;
		}

		$enable_store_credit = isset( $data['affwp_sc_enable_store_credit'] ) ? $data['affwp_sc_enable_store_credit'] : '';

		if ( $enable_store_credit ) {
			affwp_update_affiliate_meta( $data['affiliate_id'], 'store_credit_enabled', $enable_store_credit );
		} else {
			affwp_delete_affiliate_meta( $data['affiliate_id'], 'store_credit_enabled' );
		}

	}

}
new AffiliateWP_Store_Credit_Dashboard;
