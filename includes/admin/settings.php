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
			// Add a "Store Credit" column to the affiliates admin screen.
			add_filter( 'affwp_affiliate_table_columns', array( $this, 'column_store_credit' ), 10, 3 );
			add_filter( 'affwp_affiliate_table_store_credit', array( $this, 'column_store_credit_value' ), 10, 2 );

			// Add the Store Credit Balance to the edit affiliate screen.
			add_action( 'affwp_edit_affiliate_end', array( $this, 'edit_affiliate_store_credit_balance' ), 10, 1 );
		}

	}

	/**
	 * Add a "Store Credit" column to the affiliates screen.
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
			array_slice( $prepared_columns, $offset, NULL, true);

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
	 * Display the store credit balance.
	 *
	 * @access public
	 * @param \AffWP\Affiliate $affiliate The affiliate object being edited.
	 * 
	 * @since 2.2
	 */
	public function edit_affiliate_store_credit_balance( $affiliate ) {
	?>

		<tr class="form-row">
			<th scope="row">
				<?php _e( 'Store Credit', 'affiliate-wp-store-credit' ); ?>
			</th>
			<td><hr /></td>
		</tr>

		<tr class="form-row">
			<th scope="row">
				<?php _e( 'Store Credit Balance', 'affiliate-wp-store-credit' ); ?>
			</th>
			<td>
				<input class="medium-text" type="text" name="store_credit" id="store-credit" value="<?php echo affwp_store_credit_balance( array( 'affiliate_id' => $affiliate->affiliate_id ) ); ?>" disabled="disabled" />
				<p class="description"><?php _e( 'The affiliate\'s store credit balance.', 'affiliate-wp-store-credit' ); ?></p>
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

		$settings['store-credit'] = array(
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
