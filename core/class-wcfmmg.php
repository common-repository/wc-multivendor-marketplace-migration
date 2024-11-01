<?php

/**
 * WCFM Marketplace Migrator plugin
 *
 * WCFM Marketplace Migrator Core
 *
 * @author 		WC Lovers
 * @package 	wcfmmg/core
 * @version   1.0.0
 */

class WCFMmg {

	public $plugin_base_name;
	public $plugin_url;
	public $plugin_path;
	public $version;
	public $token;
	public $text_domain;
	public $library;
	public $template;
	public $admin;
	public $frontend;
	public $ajax;
	private $file;
	public $wcfmmp_fields;
	public $wcfmmp_non_ajax;
	public $is_marketplace;

	public function __construct($file) {

		$this->file = $file;
		$this->plugin_base_name = plugin_basename( $file );
		$this->plugin_url = trailingslashit(plugins_url('', $plugin = $file));
		$this->plugin_path = trailingslashit(dirname($file));
		$this->token = WCFMmg_TOKEN;
		$this->text_domain = WCFMmg_TEXT_DOMAIN;
		$this->version = WCFMmg_VERSION;
		
		// Installer Hook
		add_action( 'init', array( &$this, 'run_wcfmmg_installer' ) );
		
		add_action( 'init', array( &$this, 'init' ) );
		
	}
	
	/**
	 * Initilize plugin on WP init
	 */
	function init() {
		global $WCFM, $WCFMmg;
		
		// Init Text Domain
		$this->load_plugin_textdomain();
		
		if(!WCFMmg_Dependencies::woocommerce_plugin_active_check()) {
			add_action( 'admin_notices', 'wcfmmg_woocommerce_inactive_notice' );
			return;
		} 
		
		if(!WCFMmg_Dependencies::wcfm_plugin_active_check()) {
			add_action( 'admin_notices', 'wcfmmg_wcfm_inactive_notice' );
			//return;
		} else {
		
			$this->is_marketplace = wcfm_is_marketplace();
			
			if( $this->is_marketplace && ( $this->is_marketplace != 'wcfmmarketplace' ) ) {
				$this->load_class( $this->is_marketplace );
				if( $this->is_marketplace == 'wcvendors' ) $this->wcfm_marketplace = new WCFMmg_WCVendors();
				elseif( $this->is_marketplace == 'wcmarketplace' ) $this->wcfm_marketplace = new WCFMmg_WCMarketplace();
				elseif( $this->is_marketplace == 'wcpvendors' ) $this->wcfm_marketplace = new WCFMmg_WCPVendors();
				elseif( $this->is_marketplace == 'dokan' ) $this->wcfm_marketplace = new WCFMmg_Dokan();
			} else {
				add_action( 'admin_notices', 'wcfmmg_marketplace_inactive_notice' );
				return;
			}
		}
		
		// Load WCFM Marketplace setup class
		// http://localhost/wwd/wp-admin/?page=wcfmmp-setup&step=dashboard
		if ( is_admin() ) {
			$current_page = filter_input( INPUT_GET, 'page' );
			if ( $current_page && $current_page == 'wcfmmg-migrator' ) {
				require_once $this->plugin_path . 'helpers/class-wcfmmg-migrator.php';
			} else {
				add_action( 'admin_notices', 'wcfmmg_active_notice' );
			}
		}
		
		// Init Admin class
		if ( is_admin() ) {
			$this->load_class( 'admin' );
			$this->admin = new WCFMmg_Admin();
		}
		
	}
	
	/**
	 * Load Localisation files.
	 *
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present
	 *
	 * @access public
	 * @return void
	 */
	public function load_plugin_textdomain() {
		$locale = function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
		$locale = apply_filters( 'plugin_locale', $locale, 'wc-multivendor-marketplace-migration' );

		//load_textdomain( 'wc-multivendor-marketplace-migration', WP_LANG_DIR . "/wc-multivendor-marketplace/wc-multivendor-marketplace-migration-$locale.mo");
		load_textdomain( 'wc-multivendor-marketplace-migration', $this->plugin_path . "lang/wc-multivendor-marketplace-migration-$locale.mo");
		load_textdomain( 'wc-multivendor-marketplace-migration', WP_LANG_DIR . "/plugins/wc-multivendor-marketplace-migration-$locale.mo");
	}

	public function load_class($class_name = '') {
		if ('' != $class_name && '' != $this->token) {
			require_once ('class-' . esc_attr($this->token) . '-' . esc_attr($class_name) . '.php');
		} // End If Statement
	}

	// End load_class()

	/**
	 * Install upon activation.
	 *
	 * @access public
	 * @return void
	 */
	static function activate_wcfmmg() {
		global $WCFM, $WCFMmg, $wp_roles;
		
		require_once ( $WCFMmg->plugin_path . 'helpers/class-wcfmmg-install.php' );
		$WCFMmg_Install = new WCFMmg_Install();
		
		update_option('wcfmmg_installed', 1);
	}
	
	/**
	 * Check Installer upon load.
	 *
	 * @access public
	 * @return void
	 */
	function run_wcfmmg_installer() {
		global $WCFM, $WCFMmg, $wpdb;
		
		$wcfm_marketplace_tables = $wpdb->query( "SHOW tables like '{$wpdb->prefix}wcfm_marketplace_store_taxonomies'");
		if( !$wcfm_marketplace_tables ) {
			delete_option( 'wcfmmg_table_install' );
		}
		
		if ( !get_option("wcfmmg_page_install") || !get_option("wcfmmg_table_install") ) {
			require_once ( $WCFMmg->plugin_path . 'helpers/class-wcfmmg-install.php' );
			$WCFMmg_Install = new WCFMmg_Install();
			
			update_option('wcfmmg_installed', 1);
		}
	}

	/**
	 * UnInstall upon deactivation.
	 *
	 * @access public
	 * @return void
	 */
	static function deactivate_wcfmmg() {
		global $WCFM, $WCFMmg;
		
		delete_option('wcfmmg_installed');
	}
	
}