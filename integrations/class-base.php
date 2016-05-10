<?php

abstract class AffiliateWP_Store_Credit_Base {
	public $context;

	public function __construct() {
		$this->init();

		add_action( 'affwp_set_referral_status', array( $this, 'process_payout' ), 10, 3 );
		add_action( 'affwp_set_referral_status', array( $this, 'edit_payment' ), 10, 3 );
		add_action( 'affwp_process_update_referral', array( $this, 'edit_payment' ) );
		add_action( 'affwp_process_update_referral', array( $this, 'process_payout' ) );
		//add_action( 'affwp_edit_referral_bottom', array( $this, 'get_new_amount' ) );

		add_action( 'affwp_add_referral', array( $this, 'process_payout' ) );
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
	public function get_new_amount() {

		// Get the referral amounts
		$referral = affwp_get_referral( absint( $_GET['referral_id'] ) );
		//
		$old_amount = $referral->amount;
		$new_amount = $data['amount'];


		$current_balance = get_user_meta( $user_id, 'affwp_wc_credit_balance', true );

		$new_balance = $current_balance + $data['amount'];

		// Test retrieval of new amount
		update_user_meta( $user_id, 'affwp_wc_credit_balance', $new_balance );

		// var dump
		echo var_dump( $old_amount );
		echo var_dump( $new_amount );
	}
}
