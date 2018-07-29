<?php

class Affiliate_Store_Credit_Upgrades {

	/**
	 * Signals whether the upgrade was successful.
	 *
	 * @access public
	 * @var    bool
	 */
	private $upgraded = false;

	/**
	 * AffiliateWP - Store Credit version.
	 *
	 * @access private
	 * @since  2.3
	 * @var    string
	 */
	private $version;

	/**
	 * Sets up the Upgrades class instance.
	 *
	 * @access public
	 */
	public function __construct() {

		$this->version = get_option( 'affwp_sc_version' );

		add_action( 'admin_init', array( $this, 'init' ), -9999 );

	}

	/**
	 * Initializes upgrade routines for the current version of Affiliate Area Tabs.
	 *
	 * @access public
	 */
	public function init() {

		if ( empty( $this->version ) ) {
			$this->version = '2.2.2'; // last version that didn't have the version option set
		}

		if ( version_compare( $this->version, '2.3', '<' ) ) {
			$this->v23_upgrade();
		}

		// If upgrades have occurred
		if ( $this->upgraded ) {
			update_option( 'affwp_sc_version_upgraded_from', $this->version );
			update_option( 'affwp_sc_version', AFFWP_SC_VERSION );
		}

	}

	/**
	 * Performs database upgrades for version 2.3
	 *
	 * @access private
	 * @since 2.3
	 */
	private function v23_upgrade() {

		// Check that store credit is enabled.
		if ( affiliate_wp()->settings->get( 'store-credit' ) ) {
			affiliate_wp()->settings->set( array( 'store-credit-all-affiliates' => 1 ), true );
		}

		// Upgraded!
		$this->upgraded = true;

	}

}
new Affiliate_Store_Credit_Upgrades;