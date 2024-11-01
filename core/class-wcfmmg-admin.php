<?php
/**
 * WCFMmg plugin Migrator core
 *
 * WCfMmg Migrator Admin
 *
 * @author 		WC Lovers
 * @package 	wcfmmp/core
 * @version   1.0.0
 */
class WCFMmg_Admin {
	
	public function __construct() {
 		global $WCFM, $WCFMmg;
 		
 		// Browse WCFM Marketplace setup page
 		add_action( 'admin_init', array( &$this, 'wcfmmg_redirect_to_setup' ), 5 );
 		
 		if(WCFMmg_Dependencies::woocommerce_plugin_active_check() && WCFMmg_Dependencies::wcfm_plugin_active_check()) {
			/**
			 * Register our WCFM Marketplace to the admin_menu action hook
			 */
			//add_action( 'admin_menu', array( &$this, 'wcfmmp_options_page' ) );
		}

 	}
 	
 	/**
	 * WCFM Marketplace activation redirect transient
	 */
	function wcfmmg_redirect_to_setup(){
		if ( get_transient( '_wc_activation_redirect' ) ) {
			delete_transient( '_wc_activation_redirect' );
			return;
		}
		if ( get_transient( '_wcfmmg_activation_redirect' ) ) {
			delete_transient( '_wcfmmg_activation_redirect' );
			if ( ( ! empty( $_GET['page'] ) && in_array( $_GET['page'], array( 'wcfmmg-migrator' ) ) ) || is_network_admin() || isset( $_GET['activate-multi'] ) ) {
			  return;
			}
			wp_safe_redirect( admin_url( 'index.php?page=wcfmmg-migrator' ) );
			exit;
		}
	}
	
	/**
	 * WCFM Marketplace Menu at WP Menu
	 */
	function wcfmmp_options_page() {
    global $menu, $WCFMmp;
    
    if( function_exists( 'get_wcfm_settings_url' ) ) {
    	add_menu_page( __( 'Marketplace', 'wc-multivendor-marketplace' ), __( 'Marketplace', 'wc-multivendor-marketplace' ), 'manage_options', 'wcfm_settings_form_marketplace_head', null, null, '55' );
    	$menu[55] = array( __( 'Marketplace', 'wc-multivendor-marketplace' ), 'manage_options', get_wcfm_settings_url() . '#wcfm_settings_form_marketplace_head', '', 'open-if-no-js menu-top', '', $WCFMmp->plugin_url . 'assets/images/wcfmmp_icon.svg' );
    }
  }  
}