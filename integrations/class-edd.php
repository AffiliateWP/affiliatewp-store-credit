<?php

class AffiliateWP_Store_Credit_EDD extends AffiliateWP_Store_Credit_Base {

	/**
	 * Get things started
	 *
	 * @access public
	 * @since  2.0.0
	 * @return void
	 */
	public function init() {

		$this->context = 'edd';

		// Make sure Wallet is installed
		if( ! class_exists( 'EDD_Wallet' ) ) {
			add_action( 'admin_notices', array( $this, 'missing_edd_wallet' ) );
			return;
		}

		add_filter( 'edd_wallet_activity_type', array( $this, 'setup_wallet_type' ), 10, 2 );
		add_filter( 'edd_wallet_activity_actions', array( $this, 'setup_wallet_actions' ), 10, 2 );

	}

	/**
	 * Display a notice if EDD Wallet is missing
	 *
	 * @access public
	 * @since  2.0.0
	 * @return void
	 */
	public function missing_edd_wallet() {
		echo '<div class="error"><p>' . __( 'AffiliateWP - Store Credit EDD integration requires the EDD Wallet extension!', 'affiliatewp-store-credit' ) . '</p></div>';
	}

	/**
	 * Add a payment to a referrer
	 *
	 * @access protected
	 * @since  2.0.0
	 * @param  int $referral_id The referral ID
	 * @return
	 */
	protected function add_payment( $referral_id ) {

		// Return if EDD Wallet is not active.
		if ( ! class_exists( 'EDD_Wallet' ) ) {
			return;
		}

		// Return if the referral ID isn't valid
		if( ! is_numeric( $referral_id ) ) {
			return;
		}

		// Get the referral object
		$referral = affwp_get_referral( $referral_id );

		// Get the user id
		$user_id = affwp_get_affiliate_user_id( $referral->affiliate_id );

		// Deposit the funds into the users' wallet
		edd_wallet()->wallet->deposit( $user_id, $referral->amount, 'referral-payout', $referral_id );

		return;
	}


	/**
	 * Remove a payment from a referrer
	 *
	 * @access protected
	 * @since  2.0.0
	 * @param  int $referral_id The referral ID
	 * @return
	 */
	protected function remove_payment( $referral_id ) {

		// Return if EDD Wallet is not active.
		if ( ! class_exists( 'EDD_Wallet' ) ) {
			return;
		}

		// Return if the referral ID isn't valid
		if( ! is_numeric( $referral_id ) ) {
			return;
		}

		// Get the referral object
		$referral = affwp_get_referral( $referral_id );

		// Get the user id
		$user_id = affwp_get_affiliate_user_id( $referral->affiliate_id );

		// Get the users' balance
		$balance = edd_wallet()->wallet->balance( $user_id );

		if( (float) $balance >= (float) $referral->amount ) {
			// Withdraw the funds from the users' wallet
			edd_wallet()->wallet->withdraw( $user_id, $referral->amount, 'referral-cancel', $referral_id );
		}

		return;
	}


	/**
	 * Setup custom Wallet type
	 *
	 * @access public
	 * @since  2.0.0
	 * @param  string $item_type The item type
	 * @param  object $item The item object
	 * @return string The textual item type
	 */
	public function setup_wallet_type( $item_type, $item ) {
		switch( $item_type ) {
			case 'referral-payout' :
				return __( 'Referral Payout', 'affiliatewp-store-credit' );
				break;
			case 'referral-cancel' :
				return __( 'Referral Cancelled', 'affiliatewp-store-credit' );
				break;
			default :
				return $item_type;
				break;
		}
	}


	/**
	 * Setup custom Wallet actions
	 *
	 * @access public
	 * @since  2.0.0
	 * @param  string $item_actions The item actions
	 * @param  object $item The item object
	 * @return string The available actions
	 */
	public function setup_wallet_actions( $item_actions, $item ) {
		switch( $item->type ) {
			case 'referral-payout' :
			case 'referral-cancel' :
				return '<a title="' . __( 'View Details for Referral', 'affiliatewp-store-credit' ) . ' ' . $item->payment_id . '" href="' . admin_url( 'admin.php?page=affiliate-wp-referrals&action=edit_referral&referral_id=' . $item->payment_id ) . '">' . __( 'View Details', 'affiliatewp-store-credit' ) . '</a>';
				break;
			default :
				return $item_actions;
				break;
		}
	}
}
new AffiliateWP_Store_Credit_EDD;
