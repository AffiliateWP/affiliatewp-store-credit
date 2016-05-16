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
		add_action( 'affwp_process_update_referral', array( $this, 'process_payout' ), 0 );
		add_action( 'affwp_add_referral', array( $this, 'process_payout' ) );
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
	 * Set the expiration date of the coupon, if available
	 *
	 * @since  2.1
	 *
	 * @return int|date The future date on which this coupon expires.
	 *                  Defaults to 2 days after coupon creation.
	 */
	public function coupon_expires() {

		$expires = date( 'Y-m-d-s', strtotime( '+2 days', current_time( 'timestamp' ) ) );

		return apply_filters( 'affwp_store_credit_expires', $expires );
	}

	/**
	 * Validates usage of the coupon.
	 *
	 * @since  2.1
	 * @param  int     $order_id   The ID of an order
	 * @param  object  $data       The order data
	 *
	 * @return void    Since the manners by which coupon usage may be
	 *                 validated vary greatly by integration, this
	 *                 method does not supply any direct validation
	 *                 itself.
	 *
	 *                 Generalized validation, such as typecasting,
	 *                 defining arbitrary $desired and $actual vars,
	 *                 and comparisons may be added as integrations
	 *                 continue to be extended in this add-on.
	 */
	public function validate_coupon_usage( $order_id, $data ) {
		$order_id = '';
		$data     = '';
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
