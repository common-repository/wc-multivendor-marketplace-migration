<?php

/**
 * WCFMmg plugin Install
 *
 * Plugin install script which adds default pages, taxonomies, and database tables to WordPress. Runs on activation and upgrade.
 *
 * @author 		WC Lovers
 * @package 	wcfmmg/helpers
 * @version   1.0.0
 */
 
class WCFMmg_Install {

	public $arr = array();

	public function __construct() {
		global $WCFM, $WCFMmg, $WCFM_Query;
		
		if ( !get_option( 'wcfmmg_table_install' ) ) {
			$this->wcfmmg_create_tables();
			update_option("wcfmmg_table_install", 1);
		}
		
		self::wcfmmg_user_role();
		
		if( !get_option( 'wcfmmg_installed' ) && apply_filters( 'wcfmmg_enable_setup_wizard', true ) ) {
			set_transient( '_wcfmmg_activation_redirect', 1, 30 );
		}
	}
	
	/**
	 * Create WCFMmp Marketplace tables
	 * @global object $wpdb
	 * From Version 1.0.0
	 */
	function wcfmmg_create_tables() {
		global $wpdb;
		$collate = '';
		if ($wpdb->has_cap('collation')) {
				$collate = $wpdb->get_charset_collate();
		}
		$create_tables_query = array();
		
		$create_tables_query[] = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "wcfm_marketplace_orders` (
															`ID` bigint(20) NOT NULL AUTO_INCREMENT,
															`vendor_id` bigint(20) NOT NULL,
															`order_id` bigint(20) NOT NULL,
															`customer_id` bigint(20) NOT NULL,
															`payment_method` varchar(255) NOT NULL,
															`product_id` bigint(20) NOT NULL,
															`variation_id` bigint(20) NOT NULL DEFAULT 0,
															`quantity` bigint(20) NOT NULL DEFAULT 1,
															`product_price` varchar(255) NULL DEFAULT 0,
															`purchase_price` varchar(20) NOT NULL DEFAULT 0,
															`item_id` bigint(20) NOT NULL,
															`item_type` varchar(255) NULL,
															`item_sub_total` varchar(255) NULL DEFAULT 0,
															`item_total` varchar(255) NULL DEFAULT 0,
															`shipping` varchar(255) NOT NULL DEFAULT 0,
															`tax` varchar(255) NOT NULL DEFAULT 0,
															`shipping_tax_amount` varchar(255) NOT NULL DEFAULT 0,
															`commission_amount` varchar(255) NOT NULL DEFAULT 0,
															`discount_amount` varchar(255) NOT NULL DEFAULT 0,
															`discount_type` varchar(255) NOT NULL,
															`other_amount` varchar(255) NOT NULL DEFAULT 0,
															`other_amount_type` varchar(255) NOT NULL,
															`withdrawal_id` bigint(20) NOT NULL DEFAULT 0,
															`refunded_id` bigint(20) NOT NULL DEFAULT 0,
															`refunded_amount` varchar(255) NOT NULL DEFAULT 0,
															`withdraw_charges` varchar(255) NOT NULL DEFAULT 0,
															`total_commission` varchar(255) NOT NULL DEFAULT 0,
															`order_status` varchar(255) NOT NULL,
															`shipping_status` varchar(255) NOT NULL DEFAULT 'pending',
															`commission_status` varchar(100) NOT NULL DEFAULT 'pending',
															`withdraw_status` varchar(100) NOT NULL DEFAULT 'pending',
															`refund_status` varchar(100) NOT NULL DEFAULT 'pending',
															`is_refunded` tinyint(1) NOT NULL default 0,
															`is_partially_refunded` tinyint(1) NOT NULL default 0,
															`is_withdrawable` tinyint(1) NOT NULL default 0,
															`is_auto_withdrawal` tinyint(1) NOT NULL default 0,
															`is_trashed` tinyint(1) NOT NULL default 0,			
															`commission_paid_date` timestamp NULL,
															`created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,				
															PRIMARY KEY (`ID`),
															CONSTRAINT marketplace_orders UNIQUE (order_id, vendor_id, item_id)
															) $collate;";
		
		$create_tables_query[] = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "wcfm_marketplace_orders_meta` (
															`ID` bigint(20) NOT NULL AUTO_INCREMENT,
															`order_commission_id` bigint(20) NOT NULL default 0,
															`key` VARCHAR(200) NOT NULL,
															`value` longtext NOT NULL,
															PRIMARY KEY (`ID`)
															) $collate;";
															
		$create_tables_query[] = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "wcfm_marketplace_withdraw_request` (
															`ID` bigint(20) NOT NULL AUTO_INCREMENT,
															`vendor_id` bigint(20) NOT NULL,
															`order_ids` varchar(765) NOT NULL,
															`commission_ids` varchar(765) NOT NULL,
															`payment_method` varchar(255) NOT NULL,
															`withdraw_amount` varchar(255) NOT NULL DEFAULT 0,
															`withdraw_charges` varchar(255) NOT NULL DEFAULT 0,
															`withdraw_status` varchar(100) NOT NULL DEFAULT 'pending',
															`withdraw_mode` varchar(100) NOT NULL DEFAULT 'by_request',
															`withdraw_note` longtext NOT NULL,
															`is_auto_withdrawal` tinyint(1) NOT NULL default 0,
															`withdraw_paid_date` timestamp NULL,
															`created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,				
															PRIMARY KEY (`ID`)
															) $collate;";
															
	  $create_tables_query[] = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "wcfm_marketplace_withdraw_request_meta` (
															`ID` bigint(20) NOT NULL AUTO_INCREMENT,
															`withdraw_id` bigint(20) NOT NULL,
															`key` varchar(255) NOT NULL,
															`value` varchar(255) NOT NULL,
															PRIMARY KEY (`ID`)
															) $collate;";
															
		$create_tables_query[] = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "wcfm_marketplace_reverse_withdrawal` (
															`ID` bigint(20) NOT NULL AUTO_INCREMENT,
															`vendor_id` bigint(20) NOT NULL,
															`order_id` bigint(20) NOT NULL,
															`commission_id` bigint(20) NOT NULL,
															`payment_method` varchar(255) NOT NULL,
															`gross_total` varchar(255) NOT NULL DEFAULT 0,
															`commission` varchar(255) NOT NULL DEFAULT 0,
															`balance` varchar(255) NOT NULL DEFAULT 0,
															`withdraw_status` varchar(100) NOT NULL DEFAULT 'pending',
															`withdraw_note` longtext NOT NULL,
															`withdraw_paid_date` timestamp NULL,
															`created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,				
															PRIMARY KEY (`ID`)
															) $collate;";
															
		$create_tables_query[] = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "wcfm_marketplace_reverse_withdrawal_meta` (
															`ID` bigint(20) NOT NULL AUTO_INCREMENT,
															`reverse_withdraw_id` bigint(20) NOT NULL,
															`key` varchar(255) NOT NULL,
															`value` varchar(255) NOT NULL,
															PRIMARY KEY (`ID`)
															) $collate;";
															
		$create_tables_query[] = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "wcfm_marketplace_refund_request` (
															`ID` bigint(20) NOT NULL AUTO_INCREMENT,
															`vendor_id` bigint(20) NOT NULL,
															`requested_by` bigint(20) NOT NULL,
															`approved_by` bigint(20) NOT NULL,
															`order_id` bigint(20) NOT NULL,
															`commission_id` bigint(20) NOT NULL,
															`item_id` bigint(20) NOT NULL,
															`refunded_amount` varchar(255) NOT NULL DEFAULT 0,
															`refund_status` varchar(100) NOT NULL DEFAULT 'pending',
															`refund_reason` longtext NOT NULL,
															`is_partially_refunded` tinyint(1) NOT NULL default 0,
															`refund_paid_date` timestamp NULL,
															`created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,				
															PRIMARY KEY (`ID`)
															) $collate;";
															
		$create_tables_query[] = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "wcfm_marketplace_refund_request_meta` (
															`ID` bigint(20) NOT NULL AUTO_INCREMENT,
															`refund_id` bigint(20) NOT NULL,
															`key` varchar(255) NOT NULL,
															`value` varchar(255) NOT NULL,
															PRIMARY KEY (`ID`)
															) $collate;";
															
		$create_tables_query[] = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "wcfm_marketplace_vendor_ledger` (
															`ID` bigint(20) NOT NULL AUTO_INCREMENT,
															`vendor_id` bigint(20) NOT NULL,
															`credit` varchar(100) NOT NULL,
															`debit` varchar(100) NOT NULL,
															`reference_id` bigint(20) NOT NULL,
															`reference` varchar(100) NOT NULL,
															`reference_details` text NOT NULL,
															`reference_status` varchar(100) NOT NULL DEFAULT 'pending',
															`reference_update_date` timestamp NULL,
															`created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,				
															PRIMARY KEY (`ID`)
															) $collate;";
															
		$create_tables_query[] = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "wcfm_marketplace_reviews` (
															`ID` bigint(20) NOT NULL AUTO_INCREMENT,
															`vendor_id` bigint(20) NOT NULL,
															`author_id` bigint(20) NOT NULL,
															`author_name` varchar(255) NOT NULL,
															`author_email` varchar(255) NOT NULL,
															`review_title` longtext NOT NULL,
															`review_description` longtext NOT NULL,
															`review_rating` varchar(100) NOT NULL,
															`approved` tinyint(1) NOT NULL default 0,
															`created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,				
															PRIMARY KEY (`ID`)
															) $collate;";
															
		$create_tables_query[] = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "wcfm_marketplace_review_rating_meta` (
															`ID` bigint(20) NOT NULL AUTO_INCREMENT,
															`review_id` bigint(20) NOT NULL,
															`key` varchar(255) NOT NULL,
															`value` varchar(255) NOT NULL,
															`type` VARCHAR(200) NOT NULL,
															PRIMARY KEY (`ID`)
															) $collate;";
															
		$create_tables_query[] = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "wcfm_marketplace_reviews_response` (
															`ID` bigint(20) NOT NULL AUTO_INCREMENT,
															`review_id` bigint(20) NOT NULL default 0,
															`vendor_id` bigint(20) NOT NULL default 0,
															`author_id` bigint(20) NOT NULL,
															`author_name` varchar(255) NOT NULL,
															`author_email` varchar(255) NOT NULL,
															`reply` longtext NOT NULL,
															`reply_by` bigint(20) NOT NULL default 0,
															`posted` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,	
															PRIMARY KEY (`ID`)
															) $collate;";		
															
		$create_tables_query[] = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "wcfm_marketplace_reviews_response_meta` (
															`ID` bigint(20) NOT NULL AUTO_INCREMENT,
															`review_response_id` bigint(20) NOT NULL,
															`key` varchar(255) NOT NULL,
															`value` varchar(255) NOT NULL,
															PRIMARY KEY (`ID`)
															) $collate;";
															
		$create_tables_query[] = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "wcfm_marketplace_store_taxonomies` (
															`ID` bigint(20) NOT NULL AUTO_INCREMENT,
															`vendor_id` bigint(20) NOT NULL,
															`product_id` bigint(20) NOT NULL,
															`term` bigint(20) NOT NULL,
															`parent` bigint(20) DEFAULT 0,
															`taxonomy` varchar(100) NOT NULL,
															`lang` varchar(20) DEFAULT NULL,
															PRIMARY KEY (`ID`)
															) $collate;";			
															
		$create_tables_query[] = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "wcfm_marketplace_product_multivendor` (
															`ID` bigint(20) NOT NULL AUTO_INCREMENT,
															`product_id` bigint(20) NOT NULL,
															`parent_product_id` bigint(20) NOT NULL,
															`vendor_id` bigint(20) NOT NULL,
															`product_price` varchar(100) NOT NULL default 0,
															`created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,				
															PRIMARY KEY (`ID`),
															CONSTRAINT marketplace_product_multivendor UNIQUE (product_id, parent_product_id, vendor_id)
															) $collate;";
    
    $create_tables_query[] = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "wcfm_marketplace_shipping_zone_methods` (
															`instance_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                              `method_id` varchar(255) NOT NULL DEFAULT '',
                              `zone_id` int(11) unsigned NOT NULL,
                              `vendor_id` int(11) NOT NULL,
                              `is_enabled` tinyint(1) NOT NULL DEFAULT '1',
                              `settings` longtext,
                              PRIMARY KEY (`instance_id`)
															) $collate;";												
															
		$create_tables_query[] = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "wcfm_marketplace_shipping_zone_locations` (
															`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                              `vendor_id` int(11) DEFAULT NULL,
                              `zone_id` int(11) DEFAULT NULL,
                              `location_code` varchar(255) DEFAULT NULL,
                              `location_type` varchar(255) DEFAULT NULL,
                              PRIMARY KEY (`id`)
															) $collate;";			
															
		foreach ($create_tables_query as $create_table_query) {
			$wpdb->query($create_table_query);
		}
	}
	
	/**
	 * Register vendor user role
	 *
	 * @access public
	 * @return void
	 */
	public static function wcfmmg_user_role() {

		add_role('wcfm_vendor', apply_filters('wcfm_vendor_role', __('Store Vendor', 'wc-multivendor-marketplace')), array(
			  'level_6'                	      => true,
				'level_5'                	      => true,
				'level_4'                	      => true,
				'level_3'                	      => true,
				'level_2'                	      => true,
				'level_1'                	      => true,
				'level_0'                	      => true, 
				
				'read'                          => true,
				'edit_post'                     => true,
				'edit_posts'                    => true,
				'edit_others_posts'             => true,
				'edit_published_posts'          => true,
				'delete_posts'                  => true,
				
				'edit_shop_coupons'             => true,
				'manage_shop_coupons'           => true,
				'read_shop_coupons'             => true,
				'publish_shop_coupons'          => true,
				'edit_published_shop_coupons'   => true,
				'delete_shop_coupons'           => true,
				'delete_published_shop_coupons' => true,
				
				'edit_others_pages'             => true,
				'edit_published_pages'          => true,
				
				'upload_files'                  => true,
				'delete_attachments'            => true,
				'unfiltered_html'               => true,
				
				'assign_product_terms'          => true,
				
				'manage_product'                => true,
				'read_product'                  => true,
				'read_shop_coupon'              => true,
				'edit_product'                  => true,
				'delete_product'                => true,
				'edit_products'                 => true,
				'delete_products'               => true,
				'delete_published_products'     => true,
				'publish_products'              => true,
				'edit_published_products'       => true,
				'view_woocommerce_reports'      => true,
				
				'export'                        => true,
				'import'                        => true,
		));
	}
}

?>