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
		add_action( 'affwp_edit_affiliate_end', array( $this, 'enable_store_credit' ) );
		add_action( 'affwp_new_affiliate_end', array( $this, 'enable_store_credit' ) );
	}

	/**
	 * Register the settings tab
	 *
	 * @access public
	 * @since 2.1.0
	 * @return array The new tab name
	 */
	public function register_settings_tab( $tabs = array() ) {

		$tabs['store-credit'] = __( 'Store Credit', 'affiliate-wp-store-credit' );

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
				'name' => __( 'Enable Store Credit', 'affiliate-wp-recurrring' ),
				'desc' => __( 'Check this box to enable store credit for referrals', 'affiliate-wp-store-credit' ),
				'type' => 'checkbox'
			)
		);

		return $settings;
	}

	/**
	 * Enables store credit on a per-affiliate basis.
	 *
	 * @since  TODO
	 *
	 * @param  object  $affiliate Affiliate object.
	 *
	 * @return void
	 */
	public function enable_store_credit( $affiliate ) {
		if ( ! is_int( $affiliate->affiliate_id ) ) {
			affiliate_wp()->utils->log( 'AffiliateWP Store Credit: Unable to retrieve affiliate ID in enable_store_credit method.' );
			return false;
		}

		$checked = affwp_get_affiliate_meta( $affiliate->affiliate_id, 'store_credit_enabled', true );

		?>

		<tr class="form-row" id="affwp-store-credit-row">

				<th scope="row">
					<label for="enable_store_credit"><?php _e( 'Enable Store Credit?', 'affiliate-wp' ); ?></label>
				</th>

				<td>
					<label class="description">
						<input type="checkbox" name="enable_store_credit" id="enable_store_credit" value="1" <?php checked( 1, $checked, true ); ?>"/>
						<?php _e( 'Enables payouts via store credit for this affiliate.', 'affiliate-wp' ); ?>
					</label>
				</td>

			</tr>

			<?php
	}

}
new AffiliateWP_Store_Credit_Admin;
