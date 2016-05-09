<?php

abstract class AffiliateWP_Store_Credit_Base {
	public $context;

	public function __construct() {
		$this->init();

		add_action( 'affwp_set_referral_status', array( $this, 'process_payout' ), 10, 3 );
		add_action( 'affwp_set_referral_status', array( $this, 'edit_payment' ), 10, 3 );
		add_action( 'affwp_process_update_referral', array( $this, 'edit_payment' ) );
		add_action( 'affwp_process_update_referral', array( $this, 'process_payout' ) );
	}

	public function init() {}


	/**
	 * Process payouts
	 *
	 * @since 0.1
	 * @access public
	 * @param int $referral_id The referral ID
	 * @param string $new_status The new status
	 * @param string $old_status The old status
	 * @return void
	 */
	public function process_payout( $referral_id, $new_status, $old_status ) {
		if( 'paid' === $new_status ) {
			$this->add_payment( $referral_id );
		} elseif( ( 'paid' === $old_status ) && ( 'unpaid' === $new_status ) ) {
			$this->remove_payment( $referral_id );
		}
	}
}
