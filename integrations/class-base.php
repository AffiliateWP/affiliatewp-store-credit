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

		add_filter( 'affwp_get_affiliate_rate', array( $this, 'get_affiliate_rate' ), 10, 4 );

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
		if ( ! $this->can_receive_store_credit( $referral->affiliate_id, $referral ) ) {
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
	 * @param int            $affiliate_id Affiliate ID.
	 * @param AffWP\Referral $referral     Referral object.
	 *
	 * @return bool
	 */
	public function can_receive_store_credit( $affiliate_id = 0, $referral ) {

		$ret = false;

		// Get global setting.
		$global_store_credit_enabled = affiliate_wp()->settings->get( 'store-credit-all-affiliates' );

		// All affiliates can receive store credit.
		if ( $global_store_credit_enabled ) {

			$ret = true;

		} else {

			$ret = affwp_get_affiliate_meta( $affiliate_id, 'store_credit_enabled', true );

		}

		/**
		 * Filters whether the affiliate can receive store credit.
		 *
		 * @since 2.3
		 *
		 * @param bool           True if the affiliate can receive store credit, otherwise false.
		 *                       Defaults to false.
		 * @param int            $affiliate Affiliate ID.
		 * @param AffWP\Referral $referral  Referral object.
		 */
		return apply_filters( 'affwp_store_credit_can_receive_store_credit', $ret, $affiliate_id, $referral );

	}

	/**
	 * Get affiliate rate for store credit.
	 *
	 * @since  2.3.4
	 * @access public
	 * @param  float  $rate  				The affiliate rate.
	 * @param  int 		$affiliate_id	Affiliate ID.
	 * @param  string $type   			Rate type, usually 'flat' or 'percentage'.
	 * @param  string $reference 		$reference    Optional. Reference. Default empty.
	 * @return float	Affiliate rate
	 */
	public function get_affiliate_rate( $rate, $affiliate_id, $type, $reference ) {
		// Check if store credit active.
		if ( ! affiliate_wp()->settings->get( 'store-credit' ) ) {
			return $rate;
		}

		// Check if store credit active on user.
		if( ! $this->can_receive_store_credit( $affiliate_id, null ) ) {
			return $rate;
		}

		// Check if referral rate set for store credit.
		$referral_rate_store_credit = affiliate_wp()->settings->get( 'store-credit-referral-rate', '' );
		if( empty( $referral_rate_store_credit ) ) {
			return $rate;
		}

		// Calculate referral rate for store credit.
		$referral_rate_store_credit = affwp_abs_number_round( $referral_rate_store_credit );
		$referral_rate_store_credit = ( 'percentage' === $type ) ? $referral_rate_store_credit / 100 : $referral_rate_store_credit;

		return $referral_rate_store_credit;
	}

}
