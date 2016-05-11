<?php

abstract class AffiliateWP_Store_Credit_Base {

	/**
	 * Defines the context of this integration
	 * The $context variable should be defined in $this->init()
	 *
	 * @var $context  A string defining the name of the integration
	 */
	public $context;

	public function __construct() {
		$this->init();

		add_action( 'affwp_set_referral_status', array( $this, 'process_payout' ), 10, 3 );
	}

	/**
	 * Define the $this->context here,
	 * as well any hooks specific to
	 * the integration being created
	 *
	 * @since  2.0.0
	 *
	 * @return void
	 */
	public function init() {}


	/**
	 * Validates usage of the coupon.
	 *
	 * @since  2.1
	 *
	 * @return void  Since the manners by which coupon usage may be
	 *               validated vary greatly by integration, this
	 *               method does not supply any direct validation
	 *               itself.
	 *
	 *               Generalized validation, such as typecasting,
	 *               defining arbitrary $desired and $actual vars,
	 *               and comparisons may be added as integrations
	 *               continue to be extended in this add-on.
	 */
	public function validate_coupon_usage() {

	}

	/**
	 * Process payouts
	 *
	 * @since  0.1
	 * @access public
	 * @param  int $referral_id The referral ID
	 * @param  string $new_status The new status
	 * @param  string $old_status The old status
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
