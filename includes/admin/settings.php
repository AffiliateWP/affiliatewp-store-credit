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
		add_filter( 'affwp_settings_integrations', array( $this, 'register_settings' ) );
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
			'name' => __( 'Enable Store Credit', 'affiliatewp-store-credit' ),
			'desc' => __( 'Check this box to enable store credit for referrals', 'affiliatewp-store-credit' ),
			'type' => 'checkbox'
		);

		return $settings;
	}
}
new AffiliateWP_Store_Credit_Admin;