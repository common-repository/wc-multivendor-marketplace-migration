<?php
/**
 * Plugin Name: WCFM - Migrate to WooCommerce Multivendor Marketplace 
 * Plugin URI: https://wclovers.com/knowledgebase_category/wcfm-marketplace/
 * Description: Migrate your WC Markerplace or WC Vendors Marketplace or Dokan Multivendor store to WooCommerce Multivendor Marketplace (WCFM Marketplace).
 * Author: WC Lovers
 * Version: 1.0.9
 * Author URI: https://wclovers.com
 *
 * Text Domain: wc-multivendor-marketplace-migration
 * Domain Path: /lang/
 *
 * WC requires at least: 3.0.0
 * WC tested up to: 6.2.0
 *
 */

if(!defined('ABSPATH')) exit; // Exit if accessed directly

if ( ! class_exists( 'WCFMmg_Dependencies' ) )
	require_once 'helpers/class-wcfmmg-dependencies.php';

require_once 'helpers/wcfmmg-core-functions.php';
require_once 'wc-multivendor-marketplace-migration-config.php';

if(!defined('WCFMmg_TOKEN')) exit;
if(!defined('WCFMmg_TEXT_DOMAIN')) exit;


if(!class_exists('WCFMmg')) {
	include_once( 'core/class-wcfmmg.php' );
	global $WCFMmg;
	$WCFMmg = new WCFMmg( __FILE__ );
	$GLOBALS['WCFMmg'] = $WCFMmg;
	
	// Activation Hooks
	register_activation_hook( __FILE__, array('wcfmmg', 'activate_wcfmmg') );
	register_activation_hook( __FILE__, 'flush_rewrite_rules' );
	
	// Deactivation Hooks
	register_deactivation_hook( __FILE__, array('wcfmmg', 'deactivate_wcfmmg') );
}
?>