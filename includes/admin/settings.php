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
		add_action( 'affwp_edit_affiliate_end', array( $this, 'store_credit_balance' ), 10, 1 );
	}

	/**
	 * Display the store credit balance.
	 *
	 * @access public
	 * @param \AffWP\Affiliate $affiliate The affiliate object being edited.
	 * 
	 * @since 2.2
	 */
	public function store_credit_balance( $affiliate ) {
	?>

		<tr class="form-row">
			<th scope="row" id="direct-link-tracking">
				<?php _e( 'Store Credit', 'affiliate-wp-store-credit' ); ?>
			</th>
			<td><hr /></td>
		</tr>

		<tr class="form-row direct-link-row">
			<th scope="row">
				<?php _e( 'Store Credit Balance', 'affiliate-wp-store-credit' ); ?>
			</th>
			<td>
				<input class="medium-text" type="text" name="store_credit" id="store-credit" value="<?php echo affwp_store_credit_balance( $affiliate->affiliate_id ); ?>" disabled="disabled" />
			</td>
		</tr>

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

}
new AffiliateWP_Store_Credit_Admin;
