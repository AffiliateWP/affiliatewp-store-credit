<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class AffiliateWP_Store_Credit_Shortcode {

	public function __construct() {
		add_shortcode( 'affiliate_store_credit', array( $this, 'shortcode' ) );
	}

	/**
	 * [affiliate_store_credit] shortcode.
	 *
	 * @since 2.2
	 */
	public function shortcode( $atts, $content = null ) {

		if ( ! ( affwp_is_affiliate() && affwp_is_active_affiliate() ) ) {
			return;
		}

		ob_start();

		echo affwp_store_credit_balance();

		$content = ob_get_clean();

		return do_shortcode( $content );
	}

}
new AffiliateWP_Store_Credit_Shortcode;
