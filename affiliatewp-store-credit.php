<?php
/**
 * Plugin Name: AffiliateWP - Store Credit
 * Plugin URI: https://affiliatewp.com
 * Description: Pay AffiliateWP referrals as store credit
 * Author: Sandhills Development, LLC
 * Author URI: https://sandhillsdev.com
 * Contributors: ryanduff, ramiabraham, mordauk, sumobi, patrickgarman, section214, tubiz, paninapress
 * Version: 2.3.4
 * Text Domain: affiliatewp-store-credit
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'AffiliateWP_Requirements_Check' ) ) {
	require_once dirname( __FILE__ ) . '/includes/lib/affwp/class-affiliatewp-requirements-check.php';
}

/**
 * Class used to check requirements for and bootstrap the plugin.
 *
 * @since 2.4
 *
 * @see Affiliate_WP_Requirements_Check
 */
class AffiliateWP_SC_Requirements_Check extends AffiliateWP_Requirements_Check {

	/**
	 * Plugin slug.
	 *
	 * @since 2.4
	 * @var   string
	 */
	protected $slug = 'affiliatewp-store-credit';

	/**
	 * Bootstrap everything.
	 *
	 * @since 2.4
	 */
	public function bootstrap() {
		if ( ! class_exists( 'Affiliate_WP' ) ) {

			if ( ! class_exists( 'AffiliateWP_Activation' ) ) {
				require_once 'includes/lib/affwp/class-affiliatewp-activation.php';
			}

			// AffiliateWP activation
			if ( ! class_exists( 'Affiliate_WP' ) ) {
				$activation = new AffiliateWP_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
				$activation = $activation->run();
			}
		} else {
			\AffiliateWP_Store_Credit::instance( __FILE__ );
		}
	}

	/**
	 * Loads the add-on.
	 *
	 * @since 2.4
	 */
	protected function load() {
		// Maybe include the bundled bootstrapper.
		if ( ! class_exists( 'AffiliateWP_Store_Credit' ) ) {
			require_once dirname( __FILE__ ) . '/includes/class-affiliatewp-store-credit.php';
		}

		// Maybe hook-in the bootstrapper.
		if ( class_exists( 'AffiliateWP_Store_Credit' ) ) {

			// Bootstrap to plugins_loaded.
			add_action( 'plugins_loaded', array( $this, 'bootstrap' ), 100 );

			// Register the activation hook.
			register_activation_hook( __FILE__, array( $this, 'install' ) );
		}
	}

	/**
	 * Install, usually on an activation hook.
	 *
	 * @since 2.4
	 */
	public function install() {
		// Bootstrap to include all of the necessary files
		$this->bootstrap();

		if ( defined( 'AFFWP_SC_VERSION' ) ) {
			update_option( 'affwp_sc_version', AFFWP_SC_VERSION );
		}
	}

	/**
	 * Plugin-specific aria label text to describe the requirements link.
	 *
	 * @since 2.4
	 *
	 * @return string Aria label text.
	 */
	protected function unmet_requirements_label() {
		return esc_html__( 'AffiliateWP - Store Credit Requirements', 'affiliatewp-store-credit' );
	}

	/**
	 * Plugin-specific text used in CSS to identify attribute IDs and classes.
	 *
	 * @since 2.4
	 *
	 * @return string CSS selector.
	 */
	protected function unmet_requirements_name() {
		return 'affiliatewp-store-credit-requirements';
	}

	/**
	 * Plugin specific URL for an external requirements page.
	 *
	 * @since 2.4
	 *
	 * @return string Unmet requirements URL.
	 */
	protected function unmet_requirements_url() {
		return 'https://docs.affiliatewp.com/article/2361-minimum-requirements-roadmaps';
	}

}

$requirements = new AffiliateWP_SC_Requirements_Check( __FILE__ );

$requirements->maybe_load();
