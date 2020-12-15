jQuery(document).ready(function ($) {
  
  var referralRateTypeField = $( 'input[name="affwp_settings[store-credit-referral-rate-type]"]' );
  var flatRateBasisField = $( '.affwp-store-credit-referral-rate-type-field' );

  // Toggle Store Credit Referral Rate Type
  referralRateTypeField.on( 'change', function( e ){
    if( 'flat' !== e.target.value ){
      flatRateBasisField.hide();
    }else{
      flatRateBasisField.show();
    }
  } );

});