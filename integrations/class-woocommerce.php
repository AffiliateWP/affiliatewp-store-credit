<?php

class AffiliateWP_Store_Credit_WooCommerce extends AffiliateWP_Store_Credit_Base {

	/**
	 * Get things started
	 *
	 * @access public
	 * @since  2.0.0
	 */
	public function init() {
		$this->context = 'woocommerce';

		add_action( 'woocommerce_before_checkout_form',                  array( $this, 'action_add_checkout_notice' ) );
		add_action( 'woocommerce_cart_loaded_from_session',              array( $this, 'checkout_actions' ) );
		add_action( 'woocommerce_cart_loaded_from_session',              array( $this, 'cart_updated_actions' ) );
		add_action( 'woocommerce_checkout_order_processed',              array( $this, 'validate_coupon_usage' ), 10, 2 );
		add_filter( 'wcs_renewal_order_created',                         array( $this, 'subscription_actions' ), 10, 2 );
		add_action( 'woocommerce_subscription_renewal_payment_complete', array( $this, 'subscription_validate_coupon_usage' ) );
		add_action( 'woocommerce_removed_coupon',                        array( $this, 'delete_coupon_on_removal' ) );
	}

	/**
	 * Add a payment to a referrer
	 *
	 * @access protected
	 * @since  0.1
	 * @param  int $referral_id The referral ID
	 * @return bool false if adding failed, object otherwise
	 */
	protected function add_payment( $referral_id ) {

		// Return if the referral ID isn't valid
		if( ! is_numeric( $referral_id ) ) {
			return;
		}

		// Get the referral object
		$referral = affwp_get_referral( $referral_id );

		// Get the user id
		$user_id  = affwp_get_affiliate_user_id( $referral->affiliate_id );

		// Get the user's current woocommerce credit balance
		$current_balance = get_user_meta( $user_id, 'affwp_wc_credit_balance', true );
		$new_balance     = floatval( (float) $current_balance + (float) $referral->amount );

		return update_user_meta( $user_id, 'affwp_wc_credit_balance', $new_balance );
	}

	/**
	 * Edit a store credit payment
	 *
	 * @access protected
	 * @since  0.1
	 * @param  int $referral_id The referral ID
	 * @return bool false if adding failed, object otherwise
	 */
	protected function edit_payment( $referral_id ) {

		// Return if the referral ID isn't valid
		if( ! is_numeric( $referral_id ) ) {
			return;
		}

		// Get the referral object
		$referral   = affwp_get_referral( $referral_id );

		// Get the referral amounts
		$old_amount = $referral->amount;
		$new_amount = $data['amount'];

		// Get the user id
		$user_id    = affwp_get_affiliate_user_id( $referral->affiliate_id );

		// Get the user's current woocommerce credit balance
		$current_balance = get_user_meta( $user_id, 'affwp_wc_credit_balance', true );

		if ( $new_amount > $old_amount ) {
			$new_balance = floatval( $current_balance + $new_amount );

		} elseif ( $new_amount < $old_amount ) {
			$new_balance = floatval( $current_balance - $new_amount );
		}

		return update_user_meta( $user_id, 'affwp_wc_credit_balance', $new_balance );
	}


	/**
	 * Remove a payment from a referrer
	 *
	 * @access protected
	 * @since  0.1
	 * @param  int $referral_id The referral ID
	 * @return bool false if removing failed, object otherwise
	 */
	protected function remove_payment( $referral_id ) {

		// Return if the referral ID isn't valid
		if( ! is_numeric( $referral_id ) ) {
			return;
		}

		// Get the referral object
		$referral = affwp_get_referral( $referral_id );

		// Get the user id
		$user_id  = affwp_get_affiliate_user_id( $referral->affiliate_id );

		// Get the user's current woocommerce credit balance
		$current_balance = get_user_meta( $user_id, 'affwp_wc_credit_balance', true );
		$new_balance     = floatval( $current_balance - $referral->amount );

		return update_user_meta( $user_id, 'affwp_wc_credit_balance', $new_balance );
	}


	/**
	 * Add notice on checkout if user can checkout with coupon
	 *
	 * @access public
	 * @since  0.1
	 * @return void
	 */
	public function action_add_checkout_notice() {
		$balance        = (float) get_user_meta( get_current_user_id(), 'affwp_wc_credit_balance', true );
		$cart_coupons   = WC()->cart->get_applied_coupons();
		$coupon_applied = $this->check_for_coupon( $cart_coupons );

		$notice_subject = __( 'You have an account balance of', 'affiliatewp-store-credit' );
		$notice_query   = __( 'Would you like to use it now?', 'affiliatewp-store-credit' );
		$notice_action  = __( 'Apply', 'affiliatewp-store-credit' );

		// If the user has a credit balance,
		// and has not already generated and applied a coupon code
		if( $balance && ! $coupon_applied ) {
			wc_print_notice(
				sprintf( '%1$s <strong>%2$s</strong>. %3$s <a href="%4$s" class="button">%5$s</a>',
					$notice_subject,
					wc_price( $balance ),
					$notice_query,
					add_query_arg( 'affwp_wc_apply_credit', 'true', esc_url( wc_get_checkout_url() ) ),
					$notice_action
				),
			'notice' );
		}
	}


	/**
	 * Process checkout actions
	 *
	 * @access public
	 * @since  0.1
	 * @return void
	 */
	public function checkout_actions() {
		if( isset( $_GET['affwp_wc_apply_credit'] ) && $_GET['affwp_wc_apply_credit'] ) {
			$user_id           = get_current_user_id();

			// Get the credit balance and cart total
			$credit_balance    = (float) get_user_meta( $user_id, 'affwp_wc_credit_balance', true );
			$cart_total        = (float) $this->calculate_cart_subtotal();

			// Determine the max possible coupon value
			$coupon_total      = $this->calculate_coupon_amount( $credit_balance, $cart_total );

			// Bail if the coupon value was 0
			if( $coupon_total <= 0 ) {
				return;
			}

			// Attempt to generate a coupon code
			$coupon_code        = $this->generate_coupon( $user_id, $coupon_total );

			// If a coupon code was successfully generated, apply it
			if( $coupon_code ) {
				WC()->cart->add_discount( $coupon_code );
				wp_redirect( remove_query_arg( 'affwp_wc_apply_credit' ) ); exit;
			}
		}
	}


	/**
	 * Calculate the cart subtotal
	 *
	 * @access protected
	 * @since  0.1
	 * @return float $cart_subtotal The subtotal
	 */
	protected function calculate_cart_subtotal() {
		$cart_subtotal = ( 'excl' == WC()->cart->tax_display_cart ) ? WC()->cart->subtotal_ex_tax : WC()->cart->subtotal;

		return $cart_subtotal;
	}


	/**
	 * Calculate the amount of a coupon
	 *
	 * @access protected
	 * @since  0.1
	 * @param  float $credit_balance The balance of a users account
	 * @param  float $cart_total The value of the current cart
	 * @return float $coupon_amount The coupon amount
	 */
	protected function calculate_coupon_amount( $credit_balance, $cart_total ) {

		// If either of these are empty, return 0
		if( ! $credit_balance || ! $cart_total ) {
			return 0;
		}

		if( $credit_balance > $cart_total ) {
			$coupon_amount  = $cart_total;
		} else {
			$coupon_amount  = $credit_balance;
		}

		return $coupon_amount;
	}


	/**
	 * Generate a coupon
	 *
	 * @access protected
	 * @since  0.1
	 * @param  int $user_id The ID of a given user
	 * @param  float $amount The amount of the coupon
	 * @return mixed string $coupon_code The coupon code if successful, false otherwise
	 */
	protected function generate_coupon( $user_id = 0, $amount = 0 ){

		$amount = floatval( $amount );

		if( $amount <= 0 ) {
			return false;
		}

		$affiliate = affiliate_wp()->affiliates->get_by( 'user_id', $user_id );

		if ( $affiliate ) {
			$affiliate_id = $affiliate->affiliate_id;
		}

		if ( ! $affiliate_id ) {
			return false;
		}

		$user_id      = ( $user_id ) ? $user_id : get_current_user_id();
		$user_info    = get_userdata( $user_id );
		$affiliate_id = is_int( $affiliate_id ) ? $affiliate_id : affwp_get_affiliate_id( $user_id );
		$date         = current_time( 'YmdHi' );
		$coupon_code  = 'AFFILIATE-CREDIT-' . $date . '_' . $user_id;
		$expires      = $this->coupon_expires();

		// Get the user email and affiliate payment email addresses, to match against customer_email below.
		$user_emails  = array();
		$user_emails  = array(
			$user_info->user_email,
			affwp_get_affiliate_payment_email( $affiliate_id )
		);

		$coupon_data = apply_filters( 'affwp_store_credit_woocommerce_coupon_data', array(
			'discount_type'    => 'fixed_cart',
			'coupon_amount'    => $amount,
			'individual_use'   => 'no',
			'usage_limit'      => '1',
			'usage_count'      => '0',
			'expiry_date'      => $expires,
			'apply_before_tax' => 'yes',
			'free_shipping'    => 'no',
			'customer_email'   => $user_emails,
		) );

		$coupon = array(
			'post_title'   => $coupon_code,
			'post_content' => '',
			'post_status'  => 'publish',
			'post_author'  => 1,
			'post_type'    => 'shop_coupon',
			'meta_input'   => $coupon_data
		);

		$new_coupon_id = wp_insert_post( $coupon );

		if ( $new_coupon_id ) {
			return $coupon_code;
		}

		return false;
	}


	/**
	 * Validate a coupon
	 *
	 * @access public
	 * @since  0.1
	 * @param  int     $order_id   The ID of an order
	 * @param  object  $data       The order data
	 * @return void|boolean false  Calls the processed_used_coupon() method if
	 *                             the user ID matches the user ID provided within
	 *
	 */
	public function validate_coupon_usage( $order_id, $data ) {

		// Get the order object
		$order   = new WC_Order( $order_id );

		// Get the user ID associated with the order
		$user_id = $order->get_user_id();

		// Grab an array of coupons used
		$coupons = $order->get_used_coupons();

		// If the order has coupons
		if( $coupon_code = $this->check_for_coupon( $coupons ) ) {

			// Bail if the user ID in the coupon does not match the current user.
			$user_id_from_coupon = intval( substr( $coupon_code, stripos( $coupon_code, '_' ) +1 ) );

			if ( intval( $user_id ) === $user_id_from_coupon ) {
				// Process the coupon usage and remove the amount from the user's credit balance
				$this->process_used_coupon( $user_id, $coupon_code );
			} else {
				return false;
			}
		}
	}


	/**
	 * Check for a coupon
	 *
	 * @access protected
	 * @since  0.1
	 * @param  array $coupons Coupons to check
	 * @return mixed $coupon_code if found, false otherwise
	 */
	protected function check_for_coupon( $coupons = array() ) {
		if( ! empty( $coupons ) ) {
			foreach ( $coupons as $coupon_code ) {

				// Return coupon code if an affiliate credit coupon is found
				if ( false !== stripos( $coupon_code, 'AFFILIATE-CREDIT-' ) ) {
					return $coupon_code;
				}
			}
		}

		return false;
	}


	/**
	 * Process a used coupon
	 *
	 * @access protected
	 * @since  0.1
	 * @param  int $user_id The ID of a given user
	 * @param  string $coupon_code The coupon to process
	 * @return mixed object if successful, false otherwise
	 */
	protected function process_used_coupon( $user_id = 0, $coupon_code = '' ) {

		if( ! $user_id || ! $coupon_code ) {
			return;
		}

		$coupon        = new WC_Coupon( $coupon_code );
		$coupon_amount = $coupon->get_amount();

		if( ! $coupon_amount ) {
			return;
		}

		// Get the user's current woocommerce credit balance
		$current_balance = get_user_meta( $user_id, 'affwp_wc_credit_balance', true );
		$new_balance     = floatval( $current_balance - $coupon_amount );

		return update_user_meta( $user_id, 'affwp_wc_credit_balance', $new_balance );
	}


	/**
	 * Process subscription renewal actions
	 *
	 * @access public
	 * @since  2.3
	 *
	 * @param object $renewal_order The renewal order object
	 * @param object $subscription  The subscription object
	 *
	 * @return object $renewal_order Renewal order object
	 */
	public function subscription_actions( $renewal_order, $subscription ) {

		$store_credit_woocommerce_subscriptions_enabled = affiliate_wp()->settings->get( 'store-credit-woocommerce-subscriptions' );

		if ( ! $store_credit_woocommerce_subscriptions_enabled ) {
			return $renewal_order;
		}

		if ( ! $renewal_order instanceof WC_Order ) {
			return $renewal_order;
		}

		$user_id = $subscription->get_user_id();

		// Get the credit balance and cart total.
		$credit_balance = (float) get_user_meta( $user_id, 'affwp_wc_credit_balance', true );
		$order_total    = (float) $renewal_order->get_total();

		// Determine the max possible coupon value.
		$coupon_total = $this->calculate_coupon_amount( $credit_balance, $order_total );

		// Bail if the coupon value was 0.
		if ( $coupon_total <= 0 ) {
			return $renewal_order;
		}

		// Attempt to generate a coupon code.
		$coupon_code = $this->generate_coupon( $user_id, $coupon_total );

		if ( $coupon_code ) {
			$renewal_order->apply_coupon( $coupon_code );
		}

		return $renewal_order;
	}


	/**
	 * Validate a coupon for a subscription order
	 *
	 * @access public
	 * @since  2.3
	 *
	 * @param  object $subscription The subscription object
	 *
	 * @return void|false
	 *
	 */
	public function subscription_validate_coupon_usage( $subscription ) {

		$store_credit_woocommerce_subscriptions_enabled = affiliate_wp()->settings->get( 'store-credit-woocommerce-subscriptions' );

		if ( ! $store_credit_woocommerce_subscriptions_enabled ) {
			return;
		}

		$last_order = $subscription->get_last_order( 'all' );

		// Get the user ID associated with the order.
		$user_id = $last_order->get_user_id();

		// Grab an array of coupons used.
		$coupons = $last_order->get_used_coupons();

		// If the order has coupons.
		if ( $coupon_code = $this->check_for_coupon( $coupons ) ) {

			// Bail if the user ID in the coupon does not match the current user.
			$user_id_from_coupon = intval( substr( $coupon_code, stripos( $coupon_code, '_' ) + 1 ) );

			if ( intval( $user_id ) === $user_id_from_coupon ) {
				// Process the coupon usage and remove the amount from the user's credit balance
				$this->process_used_coupon( $user_id, $coupon_code );
			} else {
				return false;
			}

		}

	}

	/**
	 * Update the coupon when a cart action occurs
	 *
	 * @access public
	 * @since  2.3.1
	 *
	 * @return void
	 */
	public function cart_updated_actions() {

		$coupons = WC()->cart->get_applied_coupons();

		if ( $coupon_code = $this->check_for_coupon( $coupons ) ) {

			$cart_total = (float) $this->calculate_cart_subtotal();

			$coupon        = new WC_Coupon( $coupon_code );
			$coupon_amount = (float) $coupon->get_amount();

			// Delete and remove coupon when the cart is emptied
			if ( 0 == $cart_total ) {

				$coupon_id = $coupon->get_id();

				if ( ! empty( $coupon_id ) ) {
					WC()->cart->remove_coupon( $coupon_code );
					wp_delete_post( $coupon_id );
				}

				return;
			}

			// Update coupon amount when the cart is updated
			if ( $cart_total == $coupon_amount ) {
				return;
			}

			$user_id = get_current_user_id();

			// Get the credit balance and cart total
			$credit_balance = (float) get_user_meta( $user_id, 'affwp_wc_credit_balance', true );

			// Determine the max possible coupon value
			$coupon_total = $this->calculate_coupon_amount( $credit_balance, $cart_total );

			$coupon_id = $coupon->get_id();

			if ( ! empty( $coupon_id ) ) {
				// Update coupon amount with the new max possible coupon value
				update_post_meta( $coupon_id, 'coupon_amount', $coupon_total );
			}

		}

	}

	/**
	 * Delete a coupon when it is removed
	 *
	 * @access public
	 * @since  2.3.1
	 *
	 * @param string $coupon_code The coupon code
	 *
	 * @return void
	 */
	public function delete_coupon_on_removal( $coupon_code ) {

		if ( false !== stripos( $coupon_code, 'AFFILIATE-CREDIT-' ) ) {

			$applied_coupons = isset( WC()->cart->applied_coupons ) ? WC()->cart->applied_coupons : array();

			if ( ! in_array( $coupon_code, $applied_coupons ) ) {

				$coupon    = new WC_Coupon( $coupon_code );
				$coupon_id = $coupon->get_id();

				if ( ! empty( $coupon_id ) ) {
					wp_delete_post( $coupon_id );
				}

			}

		}

	}

}
new AffiliateWP_Store_Credit_WooCommerce;
