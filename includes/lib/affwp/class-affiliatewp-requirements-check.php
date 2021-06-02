<?php
/**
 * AffiliateWP Minimum Requirements API
 *
 * For use by AffiliateWP and its add-ons.
 *
 * @package     AffiliateWP
 * @subpackage  Tools
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @version     1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class used by AffiliateWP to enforce minimum requirements for itself and its add-ons.
 *
 * @since 1.0.0
 * @abstract
 */
abstract class AffiliateWP_Requirements_Check {

	/**
	 * Plugin base file.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	private $file = '';

	/**
	 * Plugin basename.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	private $base = '';

	/**
	 * Plugin slug.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	protected $slug = 'affiliate-wp';

	/**
	 * Requirements array.
	 *
	 * @since 1.0.0
	 * @var   array[]
	 */
	protected $requirements = array(
		// PHP.
		'php' => array(
			'minimum' => '5.6',
			'name'    => 'PHP',
			'exists'  => true,
			'current' => false,
			'checked' => false,
			'met'     => false
		),

		// WordPress.
		'wp' => array(
			'minimum' => '5.0.0',
			'name'    => 'WordPress',
			'exists'  => true,
			'current' => false,
			'checked' => false,
			'met'     => false
		),
	);

	/**
	 * Add-on requirements array.
	 *
	 * @since 1.0.0
	 * @var   array
	 */
	protected $addon_requirements = array();

	/**
	 * Sets up the plugin requirements class.
	 *
	 * @since 1.0.0
	 *
	 * @param string $file Main plugin file.
	 */
	public function __construct( $file ) {
		// Setup file & base.
		$this->file = $file;
		$this->base = plugin_basename( $this->get_file() );

		// Merge add-on requirements (if any).
		$this->requirements = array_merge( $this->requirements, $this->addon_requirements );

		// Always load translations.
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
	}

	/**
	 * Retrieves the main plugin file.
	 *
	 * @since 1.0.0
	 *
	 * @return string Main plugin file.
	 */
	public function get_file() {
		return $this->file;
	}

	/**
	 * (Maybe) loads the plugin.
	 *
	 * @since 1.0.0
	 */
	public function maybe_load() {
		// Load or quit.
		$this->met() ? $this->load() : $this->quit();
	}

	/**
	 * Getter for requirements.
	 *
	 * The requirements class automatically supports checking 'wp', 'php', and 'affwp' keyed requirements.
	 *
	 * Add-ons can register custom requirements (or override defaults outlined in `$requirements`) by
	 * defining the `$addon_requirements` property in its own sub-class. If overriding default requirements,
	 * the same keys and metadata - save for the new version numbers - should be used.
	 *
	 * Custom requirement example:
	 *
	 *     protected $addon_requirements = array(
	 *         // AffiliateWP - Affiliate Portal.
	 *         'affwp_portal' => array(
	 *             'minimum' => '1.0.0',
	 *             'name'    => 'AffiliateWP - Affiliate Portal',
	 *             'exists'  => true,
	 *             'current' => false,
	 *             'checked' => false,
	 *             'met'     => false
	 *         ),
	 *     );
	 *
	 * To hook up the version check, a corresponding method for each custom requirement MUST be added.
	 * The simplify this, the requirements class will automatically look for a method named
	 * "check_{requirement_name}".
	 *
	 * For example, for the 'affwp_portal' requirement:
	 *
	 *     public function check_affwp_portal() {
	 *         return get_option( 'affwp_ap_version' );
	 *     }
	 *
	 *
	 * @since 1.0.0
	 *
	 * @return array Plugin requirements.
	 */
	protected function get_requirements() {
		return $this->requirements;
	}

	/**
	 * Quits without loading the plugin.
	 *
	 * @since 1.0.0
	 */
	protected function quit() {
		add_action( 'admin_head',                        array( $this, 'admin_head'        ) );
		add_filter( "plugin_action_links_{$this->base}", array( $this, 'plugin_row_links'  ) );
		add_action( "after_plugin_row_{$this->base}",    array( $this, 'plugin_row_notice' ) );
	}

	//
	// Specific Methods
	//

	/**
	 * Handles actually loading the plugin.
	 *
	 * @since 1.0.0
	 */
	abstract protected function load();

	/**
	 * Install, usually on an activation hook.
	 *
	 * Note: A sub-class extension of this method is typically a good place to call a relevant
	 * install function or set the add-on version option directly.
	 *
	 * @since 1.0.0
	 */
	public function install() {
		// Bootstrap to include all of the necessary files
		$this->bootstrap();
	}

	/**
	 * Bootstraps everything.
	 *
	 * @since 1.0.0
	 */
	public function bootstrap() {
		\Affiliate_WP::instance( $this->get_file() );
	}

	/**
	 * Sets the plugin-specific URL for an external requirements page.
	 *
	 * @since 1.0.0
	 *
	 * @return string Unmet requirements URL.
	 */
	protected function unmet_requirements_url() {
		return '';
	}

	/**
	 * Sets the plugin-specific text to quickly explain what's wrong.
	 *
	 * @since 1.0.0
	 *
	 * @return string Unmet requirements text.
	 */
	private function unmet_requirements_text() {
		esc_html_e( 'This plugin is not fully active.', 'affiliate-wp' );
	}

	/**
	 * Sets the plugin-specific text to describe a single unmet requirement.
	 *
	 * @since 1.0.0
	 *
	 * @return string Unment requirements description text.
	 */
	private function unmet_requirements_description_text() {
		return esc_html__( 'Requires %s (%s), but (%s) is installed.', 'affiliate-wp' );
	}

	/**
	 * Sets the plugin-specific text to describe a single missing requirement.
	 *
	 * @since 1.0.0
	 *
	 * @return string Unmet missing requirements text.
	 */
	private function unmet_requirements_missing_text() {
		return esc_html__( 'Requires %s (%s), but it appears to be missing.', 'affiliate-wp' );
	}

	/**
	 * Sets the plugin-specific text used to link to an external requirements page.
	 *
	 * @since 1.0.0
	 *
	 * @return string Unmet requirements link text.
	 */
	private function unmet_requirements_link() {
		return esc_html__( 'Requirements', 'affiliate-wp' );
	}

	/**
	 * Sets the plugin-specific aria label text to describe the requirements link.
	 *
	 * @since 1.0.0
	 *
	 * @return string Aria label text.
	 */
	protected function unmet_requirements_label() {
		return esc_html__( 'AffiliateWP Requirements', 'affiliate-wp' );
	}

	/**
	 * Sets the plugin-specific text used in CSS to identify attribute IDs and classes.
	 *
	 * @since  1.0.0
	 *
	 * @return string CSS selector.
	 */
	protected function unmet_requirements_name() {
		return 'affwp-requirements';
	}

	//
	// Agnostic Methods
	//

	/**
	 * Sets up the plugin-agnostic method to output the additional plugin row.
	 *
	 * @since 1.0.0
	 */
	public function plugin_row_notice() {
		?><tr class="active <?php echo esc_attr( $this->unmet_requirements_name() ); ?>-row">
		<th class="check-column">
			<span class="dashicons dashicons-warning"></span>
		</th>
		<td class="column-primary">
			<?php $this->unmet_requirements_text(); ?>
		</td>
		<td class="column-description">
			<?php $this->unmet_requirements_description(); ?>
		</td>
		</tr><?php
	}

	/**
	 * Sets up the plugin-agnostic method used to output all unmet requirement information.
	 *
	 * @since 1.0.0
	 */
	private function unmet_requirements_description() {
		foreach ( $this->requirements as $properties ) {
			if ( empty( $properties['met'] ) ) {
				$this->unmet_requirement_description( $properties );
			}
		}
	}

	/**
	 * Sets up the plugin-agnostic method to output specific unmet requirement information
	 *
	 * @since 1.0.0
	 *
	 * @param array $requirement Requirements array.
	 */
	private function unmet_requirement_description( $requirement = array() ) {

		// Requirement exists, but is out of date
		if ( ! empty( $requirement['exists'] ) ) {
			$text = sprintf(
				$this->unmet_requirements_description_text(),
				'<strong>' . esc_html( $requirement['name']    ) . '</strong>',
				'<strong>' . esc_html( $requirement['minimum'] ) . '</strong>',
				'<strong>' . esc_html( $requirement['current'] ) . '</strong>'
			);

			// Requirement could not be found
		} else {
			$text = sprintf(
				$this->unmet_requirements_missing_text(),
				'<strong>' . esc_html( $requirement['name']    ) . '</strong>',
				'<strong>' . esc_html( $requirement['minimum'] ) . '</strong>'
			);
		}

		// Output the description
		echo '<p>' . $text . '</p>';
	}

	/**
	 * Sets up the plugin-agnostic method to output unmet requirements styling
	 *
	 * @since 1.0.0
	 */
	public function admin_head() {

		// Get the requirements row name
		$name = $this->unmet_requirements_name(); ?>

		<style id="<?php echo esc_attr( $name ); ?>">
			.plugins tr[data-plugin="<?php echo esc_html( $this->base ); ?>"] th,
			.plugins tr[data-plugin="<?php echo esc_html( $this->base ); ?>"] td,
			.plugins .<?php echo esc_html( $name ); ?>-row th,
			.plugins .<?php echo esc_html( $name ); ?>-row td {
				background: #fff5f5;
			}
			.plugins tr[data-plugin="<?php echo esc_html( $this->base ); ?>"] th {
				box-shadow: none;
			}
			.plugins .<?php echo esc_html( $name ); ?>-row th span {
				margin-left: 6px;
				color: #dc3232;
			}
			.plugins tr[data-plugin="<?php echo esc_html( $this->base ); ?>"] th,
			.plugins .<?php echo esc_html( $name ); ?>-row th.check-column {
				border-left: 4px solid #dc3232 !important;
			}
			.plugins .<?php echo esc_html( $name ); ?>-row .column-description p {
				margin: 0;
				padding: 0;
			}
			.plugins .<?php echo esc_html( $name ); ?>-row .column-description p:not(:last-of-type) {
				margin-bottom: 8px;
			}
		</style>
		<?php
	}

	/**
	 * Sets up the plugin-agnostic method to add the "Requirements" link to row actions
	 *
	 * @since 1.0.0
	 *
	 * @param array $links Requirement links.
	 * @return array Requirement links with markup.
	 */
	public function plugin_row_links( $links = array() ) {

		// Add the Requirements link
		$links['requirements'] =
			'<a href="' . esc_url( $this->unmet_requirements_url() ) . '" aria-label="' . esc_attr( $this->unmet_requirements_label() ) . '">'
			. esc_html( $this->unmet_requirements_link() )
			. '</a>';

		// Return links with Requirements link
		return $links;
	}

	//
	// Checkers
	//

	/**
	 * Sets up the plugin-specific requirements checker.
	 *
	 * @since 1.0.0
	 */
	private function check() {

		// Loop through requirements
		foreach ( $this->requirements as $dependency => $properties ) {

			if ( method_exists( $this, 'check_' . $dependency ) ) {
				$version = call_user_func( array( $this, 'check_' . $dependency ) );
			} else {
				$version = false;
			}

			// Merge to original array
			if ( ! empty( $version ) ) {
				$this->requirements[ $dependency ] = array_merge( $this->requirements[ $dependency ], array(
					'current' => $version,
					'checked' => true,
					'met'     => version_compare( $version, $properties['minimum'], '>=' )
				) );
			}
		}
	}

	/**
	 * Checks the PHP version.
	 *
	 * @since 1.0.0
	 *
	 * @return string PHP version.
	 */
	protected function check_php() {
		return phpversion();
	}

	/**
	 * Checks the WordPress version.
	 *
	 * @since 1.0.0
	 *
	 * @return string WordPress version.
	 */
	protected function check_wp() {
		return get_bloginfo( 'version' );
	}

	/**
	 * Checks the AffiliateWP version.
	 *
	 * Add-ons can use this built-in check for the AffiliateWP version by defining
	 * an 'affwp' requirement via the `$addon_requirements` property.
	 *
	 * @since 1.0.0
	 *
	 * @return string AffiliateWP version.
	 */
	protected function check_affwp() {
		return get_option( 'affwp_version' );
	}

	/**
	 * Determines if all requirements been met.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if met, otherwise false.
	 */
	public function met() {

		// Run the check
		$this->check();

		// Default to true (any false below wins)
		$retval  = true;
		$to_meet = wp_list_pluck( $this->requirements, 'met' );

		// Look for unmet dependencies, and exit if so
		foreach ( $to_meet as $met ) {
			if ( empty( $met ) ) {
				$retval = false;
				continue;
			}
		}

		// Return
		return $retval;
	}

	//
	// Translations
	//

	/**
	 * Handles loading the plugin-specific text-domain.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function load_textdomain() {
		// Set filter for plugin's languages directory.
		$lang_dir = dirname( plugin_basename( $this->get_file() ) ) . '/languages/';

		/**
		 * Filters the languages directory for AffiliateWP - Affiliate Portal plugin.
		 *
		 * @since 1.0
		 *
		 * @param string $lang_dir Language directory.
		 */
		$lang_dir = apply_filters( $this->base . '_languages_directory', $lang_dir );

		// Traditional WordPress plugin locale filter.
		$locale   = apply_filters( 'plugin_locale',  get_locale(), $this->slug );
		$mofile   = sprintf( '%1$s-%2$s.mo', $this->slug, $locale );

		// Setup paths to current locale file.
		$mofile_local  = $lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/' . $lang_dir . '/' . $mofile;

		if ( file_exists( $mofile_global ) ) {
			// Look in global /wp-content/languages/{plugin_dir}/ folder.
			load_textdomain( $this->slug, $mofile_global );
		} elseif ( file_exists( $mofile_local ) ) {
			// Look in local /wp-content/plugins/{plugin_dir}/languages/ folder.
			load_textdomain( $this->slug, $mofile_local );
		} else {
			// Load the default language files.
			load_plugin_textdomain( $this->slug, false, $lang_dir );
		}
	}

}
