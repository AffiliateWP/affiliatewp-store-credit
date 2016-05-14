<?php
/**
 * Increases or decreases earnings if a change in value is detected when editing a referral
 *
 * @access public
 * @since  2.1
 * @return void|boolean false Returns false if not in an admin context
 *
 */
function affwp_store_credit_process_update_referral( $data ) {

    if ( ! is_admin() ) {
        return false;
    }

    if ( ! current_user_can( 'manage_referrals' ) ) {
        wp_die( __( 'You do not have permission to manage referrals', 'affiliatewp-store-credit' ), array( 'response' => 403 ) );
    }

    $referral = affwp_get_referral( absint( $_GET['referral_id'] ) );
    $amount   = $referral->amount;

    if( isset( $data['amount'] ) ) {

        $r = affiliate_wp()->referrals->get( absint( $data['referral_id'] ) );

        if( $data['amount'] < $r->amount ) {
            // decrease credit
            affwp_decrease_affiliate_earnings( $affiliate_id, $amount );
        } else if( $data['amount'] > $r->amount ) {
            // increase credit
            affwp_increase_affiliate_earnings( $affiliate_id, $amount );
        }
    }

}
add_action( 'affwp_process_update_referral', 'affwp_store_credit_process_update_referral', 0 );
