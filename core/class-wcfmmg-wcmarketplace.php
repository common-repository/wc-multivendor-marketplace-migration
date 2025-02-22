<?php

/**
 * WC Marketplace to WCFM Marketplace Migrator plugin
 *
 * WCFM Marketplace Migrator Core WC Marketplace
 *
 * @author 		WC Lovers
 * @package 	wcfmmg/core
 * @version   1.0.0
 */

class WCFMmg_WCMarketplace {
	
	public function __construct() {
		add_filter( 'wcfm_allwoed_vendor_user_roles', array( &$this, 'wcmp_allwoed_vendor_user_roles' ), 900 );
	}
	
	function wcmp_allwoed_vendor_user_roles( $user_roles ) {
		return array( 'dc_vendor' );
	}
	
	public function store_setting_migrate( $vendor_id ) {
		global $WCFM, $WCFMmg, $WCMp;
		
		if( !$vendor_id ) return false;
		
		$vendor_data = array();
		
		$vendor = new WCMp_Vendor( $vendor_id );
		if( !$vendor ) return false;
		
		$vendor_user = get_userdata( $vendor_id );
		
		$vendor_data['store_name']  = $vendor->page_title;
		$vendor_data['gravatar']    = get_user_meta( $vendor_id, '_vendor_image', true );
		if( $vendor_data['gravatar'] && !is_numeric( $vendor_data['gravatar'] ) ) $vendor_data['gravatar'] = get_attachment_id_by_url( $vendor_data['gravatar'] );
		$vendor_data['banner_type'] = 'single_img';
		$vendor_data['banner']      = get_user_meta( $vendor_id, '_vendor_banner', true );
		if( $vendor_data['banner'] && !is_numeric( $vendor_data['banner'] ) ) $vendor_data['banner'] = get_attachment_id_by_url( $vendor_data['banner'] );
		$vendor_data['list_banner'] = $vendor_data['banner'];
		$vendor_data['phone']       = get_user_meta( $vendor_id, '_vendor_phone', true );
		$vendor_data['email']       = $vendor_user->user_email;
		
		$vendor_data['address'] = array();
		$vendor_data['address']['street_1']  = get_user_meta( $vendor_id, '_vendor_address_1', true );
		$vendor_data['address']['street_2']  = get_user_meta( $vendor_id, '_vendor_address_2', true );
		$vendor_data['address']['country']   = get_user_meta( $vendor_id, '_vendor_country', true );
		$vendor_data['address']['city']      = get_user_meta( $vendor_id, '_vendor_city', true );
		$vendor_data['address']['state']     = get_user_meta( $vendor_id, '_vendor_state', true );
		$vendor_data['address']['zip']       = get_user_meta( $vendor_id, '_vendor_postcode', true );
		
		$vendor_data['find_address']   = get_user_meta( $vendor_id, '_find_address', true ) ? get_user_meta( $vendor_id, '_find_address', true ) : '';
		$vendor_data['store_location'] = get_user_meta( $vendor_id, '_store_location', true ) ? get_user_meta( $vendor_id, '_store_location', true ) : '';
		$vendor_data['store_lat']      = get_user_meta( $vendor_id, '_store_lat', true ) ? get_user_meta( $vendor_id, '_store_lat', true ) : 0;
		$vendor_data['store_lng']      = get_user_meta( $vendor_id, '_store_lng', true ) ? get_user_meta( $vendor_id, '_store_lng', true ) : 0;
		
		$vendor_data['customer_support'] = array();
		$vendor_data['customer_support']['phone']    = get_user_meta( $vendor_id, '_vendor_customer_phone', true );
		$vendor_data['customer_support']['email']    = get_user_meta( $vendor_id, '_vendor_customer_email', true );
		$vendor_data['customer_support']['address1'] = get_user_meta( $vendor_id, '_vendor_csd_return_address1', true );
		$vendor_data['customer_support']['address2'] = get_user_meta( $vendor_id, '_vendor_csd_return_address2', true );
		$vendor_data['customer_support']['country']  = get_user_meta( $vendor_id, '_vendor_csd_return_country', true );
		$vendor_data['customer_support']['city']     = get_user_meta( $vendor_id, '_vendor_csd_return_city', true );
		$vendor_data['customer_support']['state']    = get_user_meta( $vendor_id, '_vendor_csd_return_state', true );
		$vendor_data['customer_support']['zip']      = get_user_meta( $vendor_id, '_vendor_csd_return_zip', true );
		
		$wcfm_policy_vendor_options = array();
		$wcfm_policy_vendor_options['policy_tab_title']    = get_user_meta( $vendor_id, '_vendor_policy_tab_title', true ); 
		$wcfm_policy_vendor_options['shipping_policy']     = get_user_meta( $vendor_id, '_vendor_shipping_policy', true );
		$wcfm_policy_vendor_options['refund_policy']       = get_user_meta( $vendor_id, '_vendor_refund_policy', true );
		$wcfm_policy_vendor_options['cancellation_policy'] = get_user_meta( $vendor_id, '_vendor_cancellation_policy', true );
		update_user_meta( $vendor_id, 'wcfm_policy_vendor_options', $wcfm_policy_vendor_options );
		
		$_vendor_payment_mode = get_user_meta( $vendor_id, '_vendor_payment_mode', true );
		
		$vendor_data['payment'] = array();
		$vendor_data['payment']['method']                 = $_vendor_payment_mode;
		$vendor_data['payment']['paypal']['email']        = get_user_meta( $vendor_id, '_vendor_paypal_email', true );
		$vendor_data['payment']['bank']['ac_number']      = get_user_meta( $vendor_id, '_vendor_bank_account_number', true );
		$vendor_data['payment']['bank']['bank_name']      = get_user_meta( $vendor_id, '_vendor_bank_name', true );
		$vendor_data['payment']['bank']['routing_number'] = get_user_meta( $vendor_id, '_vendor_aba_routing_number', true );
		$vendor_data['payment']['bank']['bank_addr']      = get_user_meta( $vendor_id, '_vendor_bank_address', true );
		$vendor_data['payment']['bank']['iban']           = get_user_meta( $vendor_id, '_vendor_iban', true );
		$vendor_data['payment']['bank']['ac_name']        = get_user_meta( $vendor_id, '_vendor_account_holder_name', true );
		$vendor_data['payment']['bank']['ac_cur']         = get_user_meta( $vendor_id, '_vendor_destination_currency', true );
		$vendor_data['payment']['bank']['ac_type']        = get_user_meta( $vendor_id, '_vendor_bank_account_type', true );
		
		if( $_vendor_payment_mode == 'stripe_masspay' ) {
			$vendor_data['payment']['method'] = 'stripe';
		} elseif( $_vendor_payment_mode == 'direct_bank' ){
			$vendor_data['payment']['method'] = 'bank_transfer';
		}
		
		$vendor_data['wcfm_vacation_mode']             = ( get_user_meta( $vendor_id, 'wcfm_vacation_mode', true ) ) ? get_user_meta( $vendor_id, 'wcfm_vacation_mode', true ) : 'no';
		$vendor_data['wcfm_disable_vacation_purchase'] = ( get_user_meta( $vendor_id, 'wcfm_disable_vacation_purchase', true ) ) ? get_user_meta( $vendor_id, 'wcfm_disable_vacation_purchase', true ) : 'no';
		$vendor_data['wcfm_vacation_mode_type']        = ( get_user_meta( $vendor_id, 'wcfm_vacation_mode_type', true ) ) ? get_user_meta( $vendor_id, 'wcfm_vacation_mode_type', true ) : 'instant';
		$vendor_data['wcfm_vacation_start_date']       = ( get_user_meta( $vendor_id, 'wcfm_vacation_start_date', true ) ) ? get_user_meta( $vendor_id, 'wcfm_vacation_start_date', true ) : '';
		$vendor_data['wcfm_vacation_end_date']         = ( get_user_meta( $vendor_id, 'wcfm_vacation_end_date', true ) ) ? get_user_meta( $vendor_id, 'wcfm_vacation_end_date', true ) : '';
		$vendor_data['wcfm_vacation_mode_msg']         = get_user_meta( $vendor_id, 'wcfm_vacation_mode_msg', true );
		
		$wcfm_profile_social_fields = array( 
																				'_vendor_twitter_profile'      => 'twitter',
																				'_vendor_fb_profile'           => 'fb',
																				'_vendor_instagram'            => 'instagram',
																				'_vendor_youtube'              => 'youtube',
																				'_vendor_linkdin_profile'      => 'linkdin',
																				'_vendor_google_plus_profile'  => 'gplus',
																				'_vendor_snapchat'             => 'snapchat',
																				'_vendor_pinterest'            => 'pinterest',
																				'googleplus'                   => 'google_plus',
																				//'twitter'                      => 'twitter',
																				'facebook'                     => 'facebook',
																			);
		// Store Social Profile
		$vendor_data['social'] = array();
		foreach( $wcfm_profile_social_fields as $wcfm_profile_social_key => $wcfm_profile_social_field ) {
			$vendor_data['social'][$wcfm_profile_social_field] = get_user_meta( $vendor_id, $wcfm_profile_social_key, true );
		}
		
		// Set Store Slug
		$store_slug = $vendor->page_slug;
		wp_update_user( array( 'ID' => $vendor_id, 'user_nicename' => wc_clean( $store_slug ) ) );
		
		// Set Store name
		update_user_meta( $vendor_id, 'store_name', $vendor_data['store_name'] );
		update_user_meta( $vendor_id, 'wcfmmp_store_name', $vendor_data['store_name'] );
		
		// Set Vendor Shipping
		$wcfmmp_shipping = array ( '_wcfmmp_user_shipping_enable' => 'yes', '_wcfmmp_user_shipping_type' => 'by_zone' );
		update_user_meta( $vendor_id, '_wcfmmp_shipping', $wcfmmp_shipping );
		
		// Store Description
		$store_description = get_user_meta( $vendor_id, '_vendor_description', true );
		update_user_meta( $vendor_id, '_store_description', $store_description );
		
		// Store Commission
		$vendor_data['commission'] = array();
		$payment_settings   = get_option('wcmp_payment_settings_name');
		$commission_type    = isset($payment_settings['commission_type']) ? $payment_settings['commission_type'] : 'percent';
		$commission_fixed   = get_user_meta( $vendor_id, '_vendor_commission', true );
		$commission_percent = get_user_meta( $vendor_id, '_vendor_commission_percentage', true );
		$vendor_data['commission']['commission_mode']    = 'global';
		if( $commission_fixed || $commission_percent ) {
			if( ($commission_type == 'fixed_with_percentage') || ($commission_type == 'fixed_with_percentage_qty') ) $commission_type = 'percent_fixed';
			if( ($commission_type == 'percentage') ) $commission_type = 'percent';
			$vendor_data['commission']['commission_mode']    = $commission_type;
			$vendor_data['commission']['commission_fixed']   = $commission_fixed;
			$vendor_data['commission']['commission_percent'] = $commission_percent; 
		}
		if( $WCMp->vendor_caps->vendor_payment_settings('give_shipping') ) {
			$vendor_data['commission']['get_shipping'] = 'yes';
		}
		if( $WCMp->vendor_caps->vendor_payment_settings('give_tax') ) {
			$vendor_data['commission']['get_tax'] = 'yes';
		}
		
		// Store Genral Setting
		update_user_meta( $vendor_id, 'wcfmmp_profile_settings', $vendor_data );
		
		return true;
	}
	
	public function store_product_migrate( $vendor_id ) {
		global $WCFM, $WCFMmg, $WCMp, $wpdb;
		
		if( !$vendor_id ) return false;
		
		$wcfm_get_vendor_products = $WCFM->wcfm_vendor_support->wcfm_get_products_by_vendor( $vendor_id );
		
		if( !empty( $wcfm_get_vendor_products ) ) {
			foreach( $wcfm_get_vendor_products as $product_id => $wcfm_get_vendor_product ) {
				$arg = array(
							'ID' => $product_id,
							'post_author' => $vendor_id,
						);
				wp_update_post( $arg );
				
				// Store Categories
				$pcategories = get_the_terms( $product_id, 'product_cat' );
				if( !empty($pcategories) ) {
					foreach($pcategories as $pkey => $product_term) {
						
						$wpdb->query(
							$wpdb->prepare(
								"INSERT INTO `{$wpdb->prefix}wcfm_marketplace_store_taxonomies` 
										( vendor_id
										, product_id
										, term
										, parent
										, taxonomy
										, lang
										) VALUES ( %d
										, %d
										, %d
										, %d
										, %s
										, %s
										)"
								, $vendor_id
								, $product_id
								, $product_term->term_id
								, $product_term->parent
								, 'product_cat'
								, ''
							)
						);
					}
				}
				
				// Product Commission
				$commission_per_poduct = get_post_meta( $product_id, '_commission_per_product', true);
				$commission_percentage_per_poduct = get_post_meta( $product_id, '_commission_percentage_per_product', true);
				$commission_fixed_with_percentage = get_post_meta( $product_id, '_commission_fixed_with_percentage', true);
				$commission_fixed_with_percentage_qty = get_post_meta( $product_id, '_commission_fixed_with_percentage_qty', true);
				
				$product_commission_data = array();
				$product_commission_data['commission_mode']    = 'global';
				if( $commission_per_poduct || $commission_fixed_with_percentage || $commission_percentage_per_poduct || $commission_fixed_with_percentage_qty ) {
					if ($WCMp->vendor_caps->payment_cap['commission_type'] == 'fixed_with_percentage') {
						$product_commission_data['commission_mode']    = 'percent_fixed';
						$product_commission_data['commission_fixed']   = $commission_fixed_with_percentage;
						$product_commission_data['commission_percent'] = $commission_percentage_per_poduct;
					} elseif ($WCMp->vendor_caps->payment_cap['commission_type'] == 'fixed_with_percentage_qty') {
						$product_commission_data['commission_mode']    = 'percent_fixed';
						$product_commission_data['commission_fixed']   = $commission_fixed_with_percentage_qty;
						$product_commission_data['commission_percent'] = $commission_percentage_per_poduct;
					} else {
						$product_commission_data['commission_mode']    = 'fixed';
						$product_commission_data['commission_fixed']   = $commission_per_poduct;
						$product_commission_data['commission_percent'] = $commission_fixed_with_percentage;
					}
				}
				update_post_meta( $product_id, '_wcfmmp_commission', $product_commission_data );
			}
			
		}
		
		return true;
	}
	
	public function store_order_migrate( $vendor_id ) {
		global $WCFM, $WCFMmg, $WCMp, $wpdb;
		
		if( !$vendor_id ) return false;
		
		$offset = 0;
		$post_count = 9999;
  	while( $offset < $post_count ) {
			$sql  = 'SELECT * FROM ' . $wpdb->prefix . 'wcmp_vendor_orders AS commission';
			$sql .= ' WHERE 1=1';
			$sql .= " AND `vendor_id` = {$vendor_id}";
			$sql .= " AND `is_trashed` != 1";
			$sql .= " ORDER BY `order_id` DESC";
			$sql .= " LIMIT 10";
			$sql .= " OFFSET {$offset}";
			
			$vendor_orders = $wpdb->get_results( $sql );
			
			if( !empty( $vendor_orders ) ) {
				foreach( $vendor_orders as $vendor_order ) {
					$order_id = $vendor_order->order_id;
					if( FALSE === get_post_status( $order_id ) ) {
						wcfm_log( "Deleted Order Skip: " . $vendor_id . " => " . $order_id );
						continue;
					} else {
						$order = wc_get_order( $order_id );
						
						if( is_a( $order , 'WC_Order' ) ) {
						
							$order_status = $order->get_status();
							
							$items = $order->get_items('line_item');
							if( !empty( $items ) ) {
								foreach( $items as $order_item_id => $item ) {
									
									if( $order_item_id != $vendor_order->order_item_id ) continue;
									
									$line_item = new WC_Order_Item_Product( $item );
									
									$product_id = $line_item->get_product_id();
									$variation_id = $line_item->get_variation_id();
									
									if( $product_id ) {
										$product        = $line_item->get_product();
										$product_price  = $product->get_price();
									} else {
										$product_id     = 0;
										$variation_id   = 0;
										$product_price  = $line_item->get_subtotal() / $line_item->get_quantity();
									}
									
									$purchase_price = $product_price;
									
									//wcfm_log( "Migrating Order: " . $vendor_id . " => " . $order_id );
									
									// Updating Order Item meta with Vendor ID
									wc_update_order_item_meta( $order_item_id, '_vendor_id', $vendor_id );
									
									$customer_id = 0;
									if ( $order->get_user_id() ) 
										$customer_id = $order->get_user_id();
									
									$payment_method = ! empty( $order->get_payment_method() ) ? $order->get_payment_method() : '';
									
									$commission_status = 'pending';
									$shipping_status   = 'pending';
									$withdraw_status   = 'pending';
									if( $vendor_order->shipping_status ) {
										$shipping_status = 'shipped';
										$commission_status = 'shipped';
									}
									
									if( in_array( $order_status, array( 'processing',  'completed' ) ) ) {
										$commission_status = $order_status;
									}
									
									if( $vendor_order->commission_status == 'paid' ) {
										$commission_status = 'completed';
										$withdraw_status   = 'completed';
									}
									
									$is_withdrawable = 1;
									$is_auto_withdrawal = 0;
									
									$is_trashed = 0;
									if( in_array( $order_status, array( 'failed', 'cancelled', 'refunded' ) ) ) {
										$is_trashed = 1;
										$is_withdrawable = 0;
										$commission_status = 'cancelled';
										$withdraw_status   = 'cancelled';
									}
									
									
									$shipping_cost = (float) $vendor_order->shipping;
									$shipping_tax = (float) $vendor_order->shipping_tax_amount;
									
									$commission_amount = (float) $vendor_order->commission_amount;
									
									$discount_amount = 0;
									$discount_type = '';
									$other_amount = 0;
									$other_amount_type = '';
									$withdraw_charges = 0;
									$refunded_amount = 0;
									$grosse_total     = $gross_tax_cost = $gross_shipping_cost = $gross_shipping_tax = $gross_sales_total = 0;
									$total_commission = $commission_amount;
									
									$discount_amount     = ( $line_item->get_subtotal() - $line_item->get_total() );
										
									$grosse_total        = $line_item->get_subtotal();
									$gross_sales_total   = $grosse_total;
										
									if( $get_shipping = $WCMp->vendor_caps->vendor_payment_settings('give_shipping') ) {
										$total_commission   += (float) $shipping_cost;
										$gross_shipping_cost = $shipping_cost;
										$grosse_total 		  += (float) $gross_shipping_cost;
									}
									if( $WCMp->vendor_caps->vendor_payment_settings('give_tax') ) {
										$total_commission   += (float) $vendor_order->tax;
										$gross_tax_cost      = (float) $vendor_order->tax;
										$grosse_total 		  += (float) $gross_tax_cost;
										if( $get_shipping ) {
											$total_commission   += (float) $shipping_tax;
											$gross_shipping_tax  = $shipping_tax;
											$grosse_total 		  += (float) $gross_shipping_tax;
										}
									}
									
									$gross_sales_total  += (float) $gross_shipping_cost;
									$gross_sales_total  += (float) $gross_tax_cost;
									$gross_sales_total  += (float) $gross_shipping_tax;
									
									try {
										$wpdb->query(
														$wpdb->prepare(
															"INSERT INTO `{$wpdb->prefix}wcfm_marketplace_orders` 
																	( vendor_id
																	, order_id
																	, customer_id
																	, payment_method
																	, product_id
																	, variation_id
																	, quantity
																	, product_price
																	, purchase_price
																	, item_id
																	, item_type
																	, item_sub_total
																	, item_total
																	, shipping
																	, tax
																	, shipping_tax_amount
																	, commission_amount
																	, discount_amount
																	, discount_type
																	, other_amount
																	, other_amount_type
																	, refunded_amount
																	, withdraw_charges
																	, total_commission
																	, order_status
																	, shipping_status 
																	, withdraw_status
																	, commission_status
																	, is_withdrawable
																	, is_auto_withdrawal
																	, is_trashed
																	, commission_paid_date
																	, created
																	) VALUES ( %d
																	, %d
																	, %d
																	, %s
																	, %d
																	, %d 
																	, %d
																	, %s
																	, %s
																	, %s
																	, %s
																	, %s
																	, %s
																	, %s
																	, %s
																	, %s
																	, %s
																	, %s
																	, %s
																	, %s
																	, %s
																	, %s
																	, %s
																	, %s
																	, %s
																	, %s
																	, %s
																	, %s
																	, %d
																	, %d
																	, %d
																	, %s
																	, %s
																	) ON DUPLICATE KEY UPDATE `created` = now()"
															, $vendor_id
															, $order_id
															, $customer_id
															, $payment_method
															, $product_id
															, $variation_id
															, $line_item->get_quantity()
															, $product_price
															, $purchase_price
															, $order_item_id
															, $line_item->get_type()
															, $line_item->get_subtotal()
															, $line_item->get_total()
															, $shipping_cost
															, $line_item->get_total_tax()
															, $shipping_tax
															, $commission_amount
															, $discount_amount
															, $discount_type
															, $other_amount
															, $other_amount_type
															, $refunded_amount
															, $withdraw_charges
															, $total_commission
															, $order_status
															, $shipping_status 
															, $withdraw_status
															, $commission_status
															, $is_withdrawable
															, $is_auto_withdrawal
															, $is_trashed
															, $vendor_order->created
															, $vendor_order->created
											)
										);
										$commission_id = $wpdb->insert_id;
									} catch( Exception $e ) {
										wcfm_log("Order Migration Error: " . $ex->getMessage());
									}
									
									if( $commission_id ) {
									
										// Commission Ledger Update
										$reference_details = sprintf( __( 'Commission for %s order.', 'wc-multivendor-marketplace-migration' ), '<b>' . get_the_title( $product_id ) . '</b>' );
										$wpdb->query(
														$wpdb->prepare(
															"INSERT INTO `{$wpdb->prefix}wcfm_marketplace_vendor_ledger` 
																	( vendor_id
																	, credit
																	, debit
																	, reference_id
																	, reference
																	, reference_details
																	, reference_status
																	, reference_update_date
																	, created
																	) VALUES ( %d
																	, %s
																	, %s
																	, %d
																	, %s
																	, %s
																	, %s 
																	, %s
																	, %s
																	) ON DUPLICATE KEY UPDATE `created` = now()"
															, $vendor_id
															, $total_commission
															, 0
															, $commission_id
															, 'order'
															, $reference_details
															, $commission_status
															, $vendor_order->created
															, $vendor_order->created
											)
										);
										
										// Withdrawal Create
										if( $vendor_order->commission_status == 'paid' ) {
											$_vendor_payment_mode = get_user_meta( $vendor_id, '_vendor_payment_mode', true );
											$wpdb->query(
																$wpdb->prepare(
																	"INSERT INTO `{$wpdb->prefix}wcfm_marketplace_withdraw_request` 
																			( vendor_id
																			, order_ids
																			, commission_ids
																			, payment_method
																			, withdraw_amount
																			, withdraw_charges
																			, withdraw_status
																			, withdraw_mode
																			, is_auto_withdrawal
																			, withdraw_paid_date
																			, created
																			) VALUES ( %d
																			, %s
																			, %s
																			, %s
																			, %s
																			, %s
																			, %s 
																			, %s
																			, %d
																			, %s
																			, %s
																			) ON DUPLICATE KEY UPDATE `created` = now()"
																	, $vendor_id
																	, $order_id
																	, $commission_id
																	, $_vendor_payment_mode
																	, $total_commission
																	, $withdraw_charges
																	, 'completed'
																	, 'by_paymode'
																	, $is_auto_withdrawal
																	, $vendor_order->created
																	, $vendor_order->created
													)
												);
												$withdraw_request_id = $wpdb->insert_id;
												
												// Withdrawal Ledger Update
												if( $withdraw_request_id ) {
													$reference_details = sprintf( __( 'Withdrawal by request.', 'wc-multivendor-marketplace-migration' ) );
													$wpdb->query(
																$wpdb->prepare(
																	"INSERT INTO `{$wpdb->prefix}wcfm_marketplace_vendor_ledger` 
																			( vendor_id
																			, credit
																			, debit
																			, reference_id
																			, reference
																			, reference_details
																			, reference_status
																			, reference_update_date
																			, created
																			) VALUES ( %d
																			, %s
																			, %s
																			, %d
																			, %s
																			, %s
																			, %s 
																			, %s
																			, %s
																			) ON DUPLICATE KEY UPDATE `created` = now()"
																	, $vendor_id
																	, 0
																	, $total_commission
																	, $withdraw_request_id
																	, 'withdraw'
																	, $reference_details
																	, 'completed'
																	, $vendor_order->created
																	, $vendor_order->created
													)
												);
											}
										}
										
										// Update Commission Metas
										$this->wcfmmp_update_commission_meta( $commission_id, 'currency', $order->get_currency() );
										$this->wcfmmp_update_commission_meta( $commission_id, 'gross_total', $grosse_total );
										$this->wcfmmp_update_commission_meta( $commission_id, 'gross_sales_total', $gross_sales_total );
										$this->wcfmmp_update_commission_meta( $commission_id, 'gross_shipping_cost', $gross_shipping_cost );
										$this->wcfmmp_update_commission_meta( $commission_id, 'gross_shipping_tax', $gross_shipping_tax );
										$this->wcfmmp_update_commission_meta( $commission_id, 'gross_tax_cost', $gross_tax_cost );
										//$this->wcfmmp_update_commission_meta( $commission_id, 'commission_rule', serialize( $commission_rule ) );
										
										// Updating Order Item meta processed
										wc_update_order_item_meta( $order_item_id, '_wcfmmp_order_item_processed', $commission_id );
									}
								}
								update_post_meta( $order_id, '_wcfmmp_order_processed', true );
							}
						} else {
							wcfm_log( "Non Order Skip: " . $vendor_id . " => " . $order_id );
						}
					}
				}
			} else {
				break;
			}
			$offset += 10;
		}
		
		
		return true;
	}
	
	/**
	 * Update Commission metas
	 */
	public function wcfmmp_update_commission_meta( $commission_id, $key, $value ) {
		global $WCFM, $WCFMmp, $wpdb;
		
		$wpdb->query(
						$wpdb->prepare(
							"INSERT INTO `{$wpdb->prefix}wcfm_marketplace_orders_meta` 
									( order_commission_id
									, `key`
									, `value`
									) VALUES ( %d
									, %s
									, %s
									)"
							, $commission_id
							, $key
							, $value
			)
		);
		$commission_meta_id = $wpdb->insert_id;
		return $commission_meta_id;
	}
	
	public function store_review_migrate( $vendor_id ) {
		global $WCFM, $WCFMmg, $wpdb;
		
		if( !$vendor_id ) return false;
		
		$status_filter   = '1';
		$approved        = 1;
		$review_title    = '';
		
		$total_review_count  = 0;
		$total_review_rating = 0;
		$avg_review_rating   = 0;
		$category_review_rating = array();
		
		$wcfm_review_categories = array( 
																		array('category'       => __( 'Feature', 'wc-multivendor-marketplace' )),
																		array('category'       => __( 'Varity', 'wc-multivendor-marketplace' )),
																		array('category'       => __( 'Flexibility', 'wc-multivendor-marketplace' )),
																		array('category'       => __( 'Delivery', 'wc-multivendor-marketplace' )),
																		array('category'       => __( 'Support', 'wc-frontend-manager' )), 
																		);
		
		$offset = 0;
		$post_count = 9999;
  	while( $offset < $post_count ) {
			$vendor_reviews =  $wpdb->get_results(
																						"SELECT c.comment_content, c.comment_ID, c.comment_author,
																								c.comment_author_email, c.comment_author_url,
																								p.post_title, c.user_id, c.comment_post_ID, c.comment_approved,
																								c.comment_date
																						FROM $wpdb->comments as c, $wpdb->posts as p
																						WHERE p.post_author='$vendor_id' AND
																								p.post_status='publish' AND
																								c.comment_post_ID=p.ID AND
																								c.comment_approved='$status_filter' AND
																								p.post_type='product' ORDER BY c.comment_ID ASC
																						LIMIT $offset, 10"
																				);
			
			
			if( !empty( $vendor_reviews ) ) {
				foreach( $vendor_reviews as $vendor_review ) {
					
					if ( get_option( 'woocommerce_enable_review_rating' ) == 'yes' ) {
						$review_rating =  intval( get_comment_meta( $vendor_review->comment_ID, 'rating', true ) );
					} else {
						$review_rating = 5;
					}
					
					$wcfm_review_submit = "INSERT into {$wpdb->prefix}wcfm_marketplace_reviews 
														(`vendor_id`, `author_id`, `author_name`, `author_email`, `review_title`, `review_description`, `review_rating`, `approved`, `created`)
														VALUES
														({$vendor_id}, {$vendor_review->user_id}, '{$vendor_review->comment_author}', '{$vendor_review->comment_author_email}', '{$review_title}', '{$vendor_review->comment_content}', '{$review_rating}', {$approved}, '{$vendor_review->comment_date}')";
													
					$wpdb->query($wcfm_review_submit);
					$wcfm_review_id = $wpdb->insert_id;
					
					if( $wcfm_review_id ) {
					
						// Updating Review Meta
						foreach( $wcfm_review_categories as $wcfm_review_cat_key => $wcfm_review_category ) {
							$wcfm_review_meta_update = "INSERT into {$wpdb->prefix}wcfm_marketplace_review_rating_meta 
																					(`review_id`, `key`, `value`, `type`)
																					VALUES
																					({$wcfm_review_id}, '{$wcfm_review_category['category']}', '{$review_rating}', 'rating_category')";
							$wpdb->query($wcfm_review_meta_update);
						}
						
						// Updating Review Meta - Product
						$wcfm_review_meta_update = "INSERT into {$wpdb->prefix}wcfm_marketplace_review_rating_meta 
																					(`review_id`, `key`, `value`, `type`)
																					VALUES
																					({$wcfm_review_id}, 'product', '{$vendor_review->comment_post_ID}', 'rating_product')";
						$wpdb->query($wcfm_review_meta_update);
						
						$total_review_count++;
						
						$total_review_rating += (float) $review_rating;
						
						foreach( $wcfm_review_categories as $wcfm_review_cat_key => $wcfm_review_category ) {
							$total_category_review_rating = 0;
							$avg_category_review_rating = 0;
							if( $category_review_rating && !empty( $category_review_rating ) && isset( $category_review_rating[$wcfm_review_cat_key] ) ) {
								$total_category_review_rating = $category_review_rating[$wcfm_review_cat_key]['total'];
								$avg_category_review_rating   = $category_review_rating[$wcfm_review_cat_key]['avg'];
							}
							$total_category_review_rating += (float) $review_rating;
							$avg_category_review_rating    = $total_category_review_rating/$total_review_count;
							$category_review_rating[$wcfm_review_cat_key]['total'] = $total_category_review_rating;
							$category_review_rating[$wcfm_review_cat_key]['avg']   = $avg_category_review_rating;
						}
						
						update_user_meta( $vendor_id, '_wcfmmp_last_author_id', $vendor_review->user_id );
						update_user_meta( $vendor_id, '_wcfmmp_last_author_name', $vendor_review->comment_author );
					}
				}
			} else {
				break;
			}
			$offset += 10;
		}
		
		update_user_meta( $vendor_id, '_wcfmmp_total_review_count', $total_review_count );
		update_user_meta( $vendor_id, '_wcfmmp_total_review_rating', $total_review_rating );
		
		if( $total_review_count ) $avg_review_rating = $total_review_rating/$total_review_count;
		update_user_meta( $vendor_id, '_wcfmmp_avg_review_rating', $avg_review_rating );
		
		$category_review_rating = update_user_meta( $vendor_id, '_wcfmmp_category_review_rating', $category_review_rating );
		
		return true;
	}
	
	public function store_vendor_migrate( $vendor_id ) {
		global $WCFM, $WCFMmg, $WCMp;
		
		if( !$vendor_id ) return false;
		
		// Deleting Vendor Term
		$vendor_term_id = get_user_meta( $vendor_id, '_vendor_term_id', true );
		wp_delete_term( absint($vendor_term_id), 'dc_vendor_shop' );
		
		// Deleting Vendor Shipping Class
		$shipping_class_id = get_user_meta( $vendor_id, 'shipping_class_id', true );
		wp_delete_term( absint($shipping_class_id), 'product_shipping_class' );
		
		$member_user = new WP_User(absint($vendor_id));
		$member_user->set_role('wcfm_vendor');
		update_user_meta( $vendor_id, 'wcfm_register_member', 'yes' );
		
		update_user_meta( $vendor_id, 'show_admin_bar_front', false );
		update_user_meta( $vendor_id, '_wcfm_email_verified', true );
		update_user_meta( $vendor_id, '_wcfm_email_verified_for', $member_user->user_email );
		update_user_meta( $vendor_id, 'wcemailverified', 'true' );
		update_user_meta( $vendor_id, '_wcfm_sms_verified', true );
		
		// WCFM Unique IDs
		update_user_meta( $vendor_id, '_wcfmmp_profile_id', $vendor_id );
		update_user_meta( $vendor_id, '_wcfmmp_unique_id', current_time( 'timestamp' ) );
		
		return true;
	}
}