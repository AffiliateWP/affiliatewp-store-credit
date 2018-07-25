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
	 * the integration being created.
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
	 * Processes payouts.
	 *
	 * @since  0.1
	 * @access public
	 * @param  int    $referral_id  The referral ID.
	 * @param  string $new_status   The new status.
	 * @param  string $old_status   The old status.
	 * @return void
	 */
	public function process_payout( $referral_id = 0, $new_status = '', $old_status = '' ) {

		$affwp_store_credit = affiliatewp_store_credit();

		// Bail if no referral ID is provided. new referral status, or old referral status is not provided.
		if ( ! $referral_id || 0 === $referral_id ) {
			$affwp_store_credit->log( 'AffiliateWP Store Credit: The referral ID could not be determined when processing this payout.' );
			return;
		}

		$referral = affwp_get_referral( $referral_id );

		// Bail if the affiliate is not enabled to receive store credit.
		if ( ! $this->can_receive_store_credit( $referral->affiliate_id ) ) {
			$affwp_store_credit->log( 'AffiliateWP Store Credit: This affiliate is not enabled to receive store credit.' );
			return;
		}

		// Bail if the new referral status for this referral is unset or an empty string.
		if ( ! isset( $new_status ) || '' === $new_status ) {
			$affwp_store_credit->log( 'AffiliateWP Store Credit: The new referral status could not be determined.' );
			return;
		}

		// Also bail if the old referral status for this referral is unset or an empty string.
		if ( ! isset( $old_status ) || '' === $old_status ) {
			$affwp_store_credit->log( 'AffiliateWP Store Credit: The old referral status could not be determined.' );
			return;
		}

		if( 'paid' === $new_status ) {
			$this->add_payment( $referral_id );
		} elseif( ( 'paid' === $old_status ) && ( 'unpaid' === $new_status ) ) {
			$this->remove_payment( $referral_id );
		}
	}

	/**
	 * Can the affiliate receive store credit?
	 *
	 * @since  2.3
	 *
	 * @param  int $affiliate_id The affiliate ID.
	 * @return bool
	 */
	public function can_receive_store_credit( $affiliate_id = 0 ) {

		// get global setting
		$global_store_credit_enabled = affiliate_wp()->settings->get( 'store-credit-all-affiliates' );

		// all affiliates can receive store credit
		if ( $global_store_credit_enabled ) {
			return true;
		}

		return (bool) affwp_get_affiliate_meta( $affiliate_id, 'store_credit_enabled', true );

	}

}
