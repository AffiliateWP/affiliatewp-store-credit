<?php
/**
 * Plugin Name:     AffiliateWP - Store Credit
 * Plugin URI:      https://affiliatewp.com
 * Description:     Pay AffiliateWP referrals as store credit
 * Author:          AffiliateWP, LLC
 * Contributors:    ryanduff, ramiabraham, mordauk, sumobi, patrickgarman, section214
 * Version:         2.2
 * Author URI:      https://affiliatewp.com
 * Text Domain:     affiliatewp-store-credit
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}


final class AffiliateWP_Store_Credit {

	/**
	 * The AffiliateWP_Store_Credit singleton instance.
	 *
	 * @since 0.1
	 * @var AffiliateWP_Store_Credit instance.
	 */
	private static $instance;

	/**
	 * Plugin directory.
	 *
	 * @since 0.1
	 * @var   string $plugin_dir
	 */
	private static $plugin_dir;

	/**
	 * The plugin version.
	 *
	 * @since 0.1
	 * @var   float $version
	 */
	private static $version;

	/**
	 * True if the AffiliateWP core debugger is active.
	 *
	 * @since 2.1.2
	 * @var   boolean $debug  Debug variable.
	 */
	public $debug;

	/**
	 * Holds the instance of Affiliate_WP_Logging.
	 *
	 * @since 2.1.2
	 * @var   array $logs  Error logs.
	 */
	public $logs;

	/**
	 * Main AffiliateWP_Store_Credit instance
	 *
	 * @since 2.0.0
	 * @static
	 * @staticvar array $instance
	 * @return The one true AffiliateWP_Store_Credit
	 */
	public static function instance() {
		if( ! isset( self::$instance ) && ! ( self::$instance instanceof AffiliateWP_Store_Credit ) ) {
			self::$instance = new AffiliateWP_Store_Credit;

			self::$plugin_dir = plugin_dir_path( __FILE__ );
			self::$version = '2.2';

			self::$instance->load_textdomain();
			self::$instance->includes();
			self::$instance->init();
		}

		return self::$instance;
	}


	/**
	 * Throws an error on object clone.
	 *
	 * @since 2.0.0
	 * @access protected
	 * @return void
	 */
	public function __clone() {
		// Cloning instance of the class is forbidden
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'affiliatewp-store-credit' ), '2.1.1' );
	}


	/**
	 * Disables unserializing of the class.
	 *
	 * @since 2.0.0
	 * @access protected
	 * @return void
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'affiliatewp-store-credit' ), '2.1.1' );
	}


	/**
	 * Loads the plugin language files.
	 *
	 * @since 0.1
	 * @access public
	 * @return void
	 */
	public function load_textdomain() {
		// Set filter for plugin language directory
		$lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
		$lang_dir = apply_filters( 'affiliatewp_store_credit_languages_directory', $lang_dir );

		// Traditional WordPress plugin locale filter
		$locale = apply_filters( 'plugin_locale', get_locale(), 'affiliatewp-store-credit' );
		$mofile = sprintf( '%1$s-%2$s.mo', 'affiliatewp-store-credit', $locale );

		// Setup paths to current locale file
		$mofile_local = $lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/affiliatewp-store-credit/' . $mofile;

		if( file_exists( $mofile_global ) ) {
			// Look in global /wp-content/languages/affiliatewp-store-credit/ folder
			load_textdomain( 'affiliatewp-store-credit', $mofile_global );
		} elseif( file_exists( $mofile_local ) ) {
			// Look in local /wp-content/plugins/affiliatewp-store-credit/ folder
			load_textdomain( 'affiliatewp-store-credit', $mofile_local );
		} else {
			// Load the default language files
			load_plugin_textdomain( 'affiliatewp-store-credit', false, $lang_dir );
		}
	}


	/**
	 * Includes required files.
	 *
	 * @since 2.0.0
	 * @access private
	 * @return void
	 */
	private function includes() {
		if( is_admin() ) {
			require_once self::$plugin_dir . 'includes/admin/settings.php';
		}

		// Check that store credit is enabled
		if( ! affiliate_wp()->settings->get( 'store-credit' ) ) {
			return;
		}

		require_once self::$plugin_dir . 'integrations/class-base.php';

		// Load the class for each integration enabled
		foreach( affiliate_wp()->integrations->get_enabled_integrations() as $filename => $integration ) {
			if( file_exists( self::$plugin_dir . 'integrations/class-' . $filename . '.php' ) ) {
				require_once self::$plugin_dir . 'integrations/class-' . $filename . '.php';
			}
		}

		// Front-end; renders in affiliate dashboard statistics area
		require_once self::$plugin_dir . 'includes/dashboard.php';

		// Shortcode.
		require_once self::$plugin_dir . 'includes/class-shortcode.php';

		// Functions.
		require_once self::$plugin_dir . 'includes/functions.php';
	}


	/**
	 * Defines init processes for this instance.
	 *
	 * @since  2.1.2
	 *
	 * @return void
	 */
	public function init() {
		$this->debug = (bool) affiliate_wp()->settings->get( 'debug_mode', false );

		if( $this->debug ) {
			$this->logs = new Affiliate_WP_Logging;
		}
	}

	/**
	 * Writes a log message.
	 *
	 * @access  public
	 * @since   2.1.2
	 *
	 * @param string $message An optional message to log. Default is an empty string.
	 */
	public function log( $message = '' ) {

		if ( $this->debug ) {
			$this->logs->log( $message );
		}
	}
}

/**
 * The main function responsible for returning the one true AffiliateWP_Store_Credit
 * instance to functions everywhere.
 *
 * @since 2.0.0
 * @return object The one true AffiliateWP_Store_Credit instance
 */
function affiliatewp_store_credit() {
	if ( ! class_exists( 'Affiliate_WP' ) ) {
        if ( ! class_exists( 'AffiliateWP_Activation' ) ) {
            require_once 'includes/class-activation.php';
        }

        $activation = new AffiliateWP_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
        $activation = $activation->run();
    } else {
        return AffiliateWP_Store_Credit::instance();
    }
}
add_action( 'plugins_loaded', 'affiliatewp_store_credit', 100 );
