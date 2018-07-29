<?php

class AffiliateWP_Store_Credit_Admin {

	/**
	 * Get things started
	 *
	 * @access public
	 * @since 2.0.0
	 * @return void
	 */
	public function __construct() {

		add_filter( 'affwp_settings_tabs', array( $this, 'register_settings_tab' ) );
		add_filter( 'affwp_settings', array( $this, 'register_settings' ) );

		if ( affiliate_wp()->settings->get( 'store-credit' ) ) {

			// Add a "Store Credit & Payout Method" columns to the affiliates admin screen.
			add_filter( 'affwp_affiliate_table_columns', array( $this, 'column_store_credit' ), 10, 3 );
			add_filter( 'affwp_affiliate_table_store_credit', array( $this, 'column_store_credit_value' ), 10, 2 );
			add_filter( 'affwp_affiliate_table_payout_method', array( $this, 'column_payment_method_value' ), 10, 2 );

			// Add the Store Credit Balance to the edit affiliate screen.
			add_action( 'affwp_edit_affiliate_end', array( $this, 'edit_affiliate_store_credit_settings' ), 10, 1 );

			// Save affiliate Store Credit option in the affiliate meta table.
			add_action( 'affwp_update_affiliate', array( $this, 'update_affiliate' ), 0 );

		}

	}

	/**
	 * Add a "Store Credit & Payment Method" columns to the affiliates screen.
	 * 
	 * @since 2.2
	 *
	 * @param array  $prepared_columns Prepared columns.
	 * @param array  $columns  The columns for this list table.
	 * @param object $instance List table instance.
	 * 
	 * @return array $prepared_columns Prepared columns.
	 */
	public function column_store_credit( $prepared_columns, $columns, $instance ) {

		$offset = 6;

		$prepared_columns = array_slice( $prepared_columns, 0, $offset, true ) +
		                    array( 'store_credit' => __( 'Store Credit', 'affiliate-wp-store-credit' ) ) +
		                    array( 'payout_method' => __( 'Payout Method', 'affiliate-wp-store-credit' ) ) +
		                    array_slice( $prepared_columns, $offset, null, true );

		return $prepared_columns;
	}

	/**
	 * Show the store credit balance for each affiliate.
	 * 
	 * @since 2.2
	 *
	 * @param string $value    The column data.
	 * @param object $affiliate The current affiliate object.
	 * 
	 * @return string $value   The affiliate's store credit balance.
	 */
	public function column_store_credit_value( $value, $affiliate ) {
		$value = affwp_store_credit_balance( array( 'affiliate_id' => $affiliate->affiliate_id ) );

		return $value;
	}

	/**
	 * Show the payment method for each affiliate.
	 *
	 * @since 2.3
	 *
	 * @param string $value     The column data.
	 * @param object $affiliate The current affiliate object.
	 *
	 * @return string $value   The affiliate's payment method.
	 */
	public function column_payment_method_value( $value, $affiliate ) {

		$value = $this->get_payout_method( $affiliate->affiliate_id );

		return $value;
	}

	/**
	 * Display the store credit settings.
	 *
	 * @access public
	 * @param \AffWP\Affiliate $affiliate The affiliate object being edited.
	 *
	 * @since 2.2
	 */
	public function edit_affiliate_store_credit_settings( $affiliate ) {

		$checked = affwp_get_affiliate_meta( $affiliate->affiliate_id, 'store_credit_enabled', true );

		?>

		<table class="form-table">
			<tr><th scope="row"><label for="affwp_settings[store_credit_header]"><?php _e( 'Store Credit', 'affiliatewp-store-credit' ); ?></label></th><td><hr></td></tr>
		</table>

		<?php if ( ! affiliate_wp()->settings->get( 'store-credit-all-affiliates' ) ): ?>

			<table class="form-table">

			<tr class="form-row">

				<th scope="row">
					<label for="enable_store_credit"><?php _e( 'Enable Store Credit?', 'affiliatewp-store-credit' ); ?></label>
				</th>

				<td>
					<input type="checkbox" name="enable_store_credit" id="enable_store_credit" value="1" <?php checked( 1, $checked, true ); ?> />
					<p class="description"><?php _e( 'Enable payouts via store credit for this affiliate.', 'affiliatewp-store-credit' ); ?></p>
				</td>

			</tr>

		</table>

		<?php endif; ?>

		<table class="form-table">

			<tr class="form-row">

				<th scope="row">
					<label for="store_credit"><?php _e( 'Store Credit Balance', 'affiliatewp-store-credit' ); ?></label>
				</th>

				<td>
					<input class="medium-text" type="text" name="store_credit" id="store-credit" value="<?php echo affwp_store_credit_balance( array( 'affiliate_id' => $affiliate->affiliate_id ) ); ?>" disabled="disabled" />
					<p class="description"><?php _e( 'The affiliate\'s store credit balance.', 'affiliatewp-store-credit' ); ?></p>
				</td>

			</tr>

		</table>

		<?php
	}

	/**
	 * Register the settings tab
	 *
	 * @access public
	 * @since 2.1.0
	 * @return array The new tab name
	 */
	public function register_settings_tab( $tabs = array() ) {

		$tabs['store-credit'] = __( 'Store Credit', 'affiliatewp-store-credit' );

		return $tabs;
	}

	/**
	 * Add our settings
	 *
	 * @access public
	 * @since 2.0.0
	 * @param array $settings The existing settings
	 * @return array $settings The updated settings
	 */
	public function register_settings( $settings = array() ) {

		$settings[ 'store-credit' ] = array(
			'store-credit' => array(
				'name' => __( 'Enable Store Credit', 'affiliatewp-store-credit' ),
				'desc' => __( 'Check this box to enable store credit for referrals.', 'affiliatewp-store-credit' ),
				'type' => 'checkbox'
			),
			'store-credit-all-affiliates' => array(
				'name' => __( 'Enable For All Affiliates?', 'affiliatewp-store-credit' ),
				'desc' => __( 'Check this box to allow all affiliates to receive store credit.', 'affiliatewp-store-credit' ),
				'type' => 'checkbox'
			),
			'store-credit-change-payment-method' => array(
				'name' => __( 'Enable Store Credit Opt-In', 'affiliatewp-store-credit' ),
				'desc' => __( 'Check this box to allow affiliates to enable payout via store credit from their affiliate dashboard.', 'affiliatewp-store-credit' ),
				'type' => 'checkbox',
			),
		);

		return $settings;
	}

	/**
	 * Save affiliate store credit option in the affiliate meta table.
	 *
	 * @since  2.3
	 */
	public function update_affiliate( $data ) {

		if ( empty( $data['affiliate_id'] ) ) {
			return false;
		}

		if ( ! current_user_can( 'manage_affiliates' ) ) {
			return;
		}

		$enable_store_credit = isset( $data['enable_store_credit'] ) ? $data['enable_store_credit'] : '';

		if ( $enable_store_credit ) {
			affwp_update_affiliate_meta( $data['affiliate_id'], 'store_credit_enabled', $enable_store_credit );
		} else {
			affwp_delete_affiliate_meta( $data['affiliate_id'], 'store_credit_enabled' );
		}

	}

	/**
	 * Get the payment method set for the affiliate.
	 *
	 * @since  2.3
	 *
	 * @param int $affiliate_id The affiliate ID
	 *
	 * @return string $payment method The payment method set for the affiliate
	 */
	public function get_payout_method( $affiliate_id = 0 ) {

		$payment_method = __( 'Cash', 'affiliate-wp-store-credit' );

		$global_store_credit_enabled = affiliate_wp()->settings->get( 'store-credit-all-affiliates' );

		if ( $global_store_credit_enabled ) {

			$payment_method = __( 'Store Credit', 'affiliate-wp-store-credit' );

		} else {

			$affiliate_store_credit_enabled = affwp_get_affiliate_meta( $affiliate_id, 'store_credit_enabled', true );

			if ( $affiliate_store_credit_enabled ) {

				$payment_method = __( 'Store Credit', 'affiliate-wp-store-credit' );

			}

		}

		return $payment_method;

	}

}
new AffiliateWP_Store_Credit_Admin;
