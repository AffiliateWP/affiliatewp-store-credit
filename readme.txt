=== AffiliateWP - Store Credit ===
Plugin Name: AffiliateWP - Store Credit
Plugin URI: https://affiliatewp.com
Description: Pay AffiliateWP referrals as store credit. Currently supports WooCommerce and Easy Digital Downloads.
Author: ramiabraham
Contributors: ryanduff, ramiabraham, mordauk, sumobi, patrickgarman, section214, drewapicture, tubiz
Tags: affiliatewp, affiliates, store credit, woo, woocommerce, easy digital downloads, edd
License: GPLv2 or later
Tested up to: 5.1
Stable tag: 2.3.1
Requires at least: 3.5

== Description ==

> This plugin requires [AffiliateWP](https://affiliatewp.com/ "AffiliateWP") v1.7+ in order to function.

This plugin allows you to pay your affiliates in store credit. At this time it supports the WooCommerce and Easy Digital Downloads integrations in AffiliateWP.

= WooCommerce requirements =

To use this plugin with WooCommerce, you need AffiliateWP and the main WooCommerce plugin.

= Easy Digital Downloads requirements =

To use this plugin with Easy Digital Downloads, you need AffiliateWP, Easy Digital Downloads, and the [EDD Wallet extension](https://easydigitaldownloads.com/downloads/wallet/).

== Installation ==

* Install and activate.

* When marking an AffiliateWP referral paid, it adds the total to the user's credit balance. If for some reason you go back and mark it unpaid, this plugin will also remove the referral amount from the balance.

* On the WooCommerce checkout page, if the user has credit available, it will show a notice and ask them if they want to use it. Based on the credit available and order total, it will create a 1 time use coupon code for the lower amount and automatically apply it to the order. i.e. for a $100 order and $50 credit, it would generate a $50 coupon since the order is more. If the order is $25 and they have $50 in credit, it will generate a coupon for the $25 order total, and leave them with a $25 credit balance after checkout.

* Upon successful checkout, the one time use coupon code is grabbed and the coupon total is deducted from their available balance.

== Frequently Asked Questions ==

* Does this support Easy Digital Downloads?

A: Yes it does!

* Does this support EDD Recurring Payments?

A: Yes it does! Affiliates that refer subscription purchases by customers can receive store credit each time a renewal order is processed if [Recurring Referrals](https://affiliatewp.com/add-ons/pro/recurring-referrals/) is installed on the site.

* Does this support WooCommerce?

A: Yes it does!

* Does this support WooCommerce Subscriptions?

A: Yes it does! Affiliates that have earned store credit and have active subscriptions can have their credit applied to their renewals. Affiliates that refer subscription purchases by customers can receive store credit each time a renewal order is processed if [Recurring Referrals](https://affiliatewp.com/add-ons/pro/recurring-referrals/) is installed on the site.

* Does this support any other e-Commerce plugins?

A: Not at this time.

* Can it be set so only certain affiliates are paid in store credit?

A: Yes!

* Can affiliates select if they wish to be paid in store credit?

A: Yes! There is an option that lets site admins enable that profile setting for affiliates.

== Screenshots ==


== Changelog ==

= Version 2.3.1, February 16, 2019 =
* Fix [WooCommerce integration]: Delete coupon when it is removed from the cart
* Fix [WooCommerce integration]: Invalid coupon amount and store credit deduction when cart is updated after coupon is applied

= Version 2.3, July 29, 2018 =
* New: Added support for applying earned credit to renewal orders in WooCommerce Subscriptions
* New: Added support for redeeming store credit and other coupons in WooCommerce
* New: Added support for earning store credit through Recurring Referrals add-on
* New: Added support for selecting which affiliates should receive store credit for referral payouts
* Fix: Updated incorrect text domain
* Fix: Coupon applied multiple times on subsequent "Apply" button clicks
* Fix: Fatal error in EDD integration when marking referral as paid if EDD Wallet is not active
* Fix: Store credit amounts do not update when editing referral amounts

= 2.2.2 =
* Fix: Fatal error that could occur when store credit is enabled, EDD integration is enabled, but EDD Wallet is not installed and active

= 2.2.1 =
* Fix: Fatal error that could occur when store credit is not enabled from the Store Credit settings tab

= 2.2 =

* New: A shortcode has been added: [affiliate_store_credit]
* New: The edit affiliate admin screen now shows an affiliate's store credit balance
* New: A "Store Credit" column has been added to the "Affiliates" admin screen
* New: A filter has been added for the WooCommerce integration: affwp_store_credit_woocommerce_coupon_data
* Fix: PHP Notice: WC_Cart::get_checkout_url is deprecated.
* Fix: Retrieve the coupon amount via the proper getter method in WooCommerce
* Fix: The WooCommerce store balance could be incorrect if the affiliate's ID did not match their user ID 

= 2.1.3 =

* Fix: Store credits sometimes fail to apply to WooCommerce carts at checkout.

= 2.1.2 =

* Fix: In some cases, payouts would cause an error when the referral status could not be determined.

= 2.1.1 =

* Fix: Plugin version compatibility fix.

= 2.1 =

* New: Store Credit add-on options have moved to their own AffiliateWP tab. It's part of the family now!
* New: An "Available Store Credit" section has been added to the "Statistics" tab of the Affiliate Area 
* Fix [WooCommerce integration]: Store credit can now be used more than once per day by an affiliate when applying it toward the balances of purchases made in WooCommerce. Shop til you drop!
* Fix [WooCommerce integration]: Store credit can now only be used by the affiliate for whom it was created. Credit where credit is due, and only where credit is due!
* New: Added AffiliateWP activation script. Having AffiliateWP installed and activated is probably a good idea if you'd like to use this add-on!
* Fix [languages]: Adds translatable strings for the action_add_checkout_notice method. Bueno!

= 2.0 =

* New: Added support for Easy Digital Downloads

= 1.1 =
* Fix for WooCommerce 2.3.3+: Run checkout actions after cart is loaded from session.

= 1.0 =
* Initial release.


