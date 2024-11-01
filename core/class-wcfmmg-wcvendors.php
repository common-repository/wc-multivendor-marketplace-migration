<?php

/**
 * WC Marketplace to WCFM Vendors Migrator plugin
 *
 * WCFM Marketplace Migrator Core WC Vendors
 *
 * @author 		WC Lovers
 * @package 	wcfmmg/core
 * @version   1.0.0
 */

class WCFMmg_WCVendors {
	
	public function __construct() {
		add_filter( 'wcfm_allwoed_vendor_user_roles', array( &$this, 'wcvendors_allwoed_vendor_user_roles' ), 900 );
	}
	
	function wcvendors_allwoed_vendor_user_roles( $user_roles ) {
		return array( 'vendor' );
	}
	
	public function store_setting_migrate( $vendor_id ) {
		global $WCFM, $WCFMmg;
		
		if( !$vendor_id ) return false;
		
		$vendor_data = array();
		
		$vendor_user = get_userdata( $vendor_id );
		
		$vendor_data['store_name']  = get_user_meta( $vendor_id, 'pv_shop_name', true );
		$vendor_data['gravatar']    = get_user_meta( $vendor_id, '_wcv_store_icon_id', true );
		$vendor_data['banner_type'] = 'single_img';
		$vendor_data['banner']      = get_user_meta( $vendor_id, '_wcv_store_banner_id', true );
		$vendor_data['list_banner'] = $vendor_data['banner'];
		$vendor_data['phone']       = get_user_meta( $vendor_id, '_wcv_store_phone', true );
		$vendor_data['email']       = $vendor_user->user_email;
		
		// Store Address
		$vendor_data['address'] = array();
		$vendor_data['address']['street_1']  = get_user_meta( $vendor_id, '_wcv_store_address1', true );
		$vendor_data['address']['street_2']  = get_user_meta( $vendor_id, '_wcv_store_address2', true );
		$vendor_data['address']['country']   = get_user_meta( $vendor_id, '_wcv_store_country', true );
		$vendor_data['address']['city']      = get_user_meta( $vendor_id, '_wcv_store_city', true );
		$vendor_data['address']['state']     = get_user_meta( $vendor_id, '_wcv_store_state', true );
		$vendor_data['address']['zip']       = get_user_meta( $vendor_id, '_wcv_store_postcode', true );
		
		// Store Location
		$vendor_data['find_address']   = get_user_meta( $vendor_id, '_wcv_store_address1', true ) ? get_user_meta( $vendor_id, '_wcv_store_address1', true ) : '';
		$vendor_data['store_location'] = '';
		$vendor_data['store_lat']      = 0;
		$vendor_data['store_lng']      = 0;
		
		// Customer Support
		$vendor_data['customer_support'] = array();
		$vendor_data['customer_support']['phone']    = get_user_meta( $vendor_id, '_wcv_store_phone', true );
		$vendor_data['customer_support']['email']    = $vendor_user->user_email;
		$vendor_data['customer_support']['address1'] = get_user_meta( $vendor_id, '_wcv_store_address1', true );
		$vendor_data['customer_support']['address2'] = get_user_meta( $vendor_id, '_wcv_store_address2', true );
		$vendor_data['customer_support']['country']  = get_user_meta( $vendor_id, '_wcv_store_country', true );
		$vendor_data['customer_support']['city']     = get_user_meta( $vendor_id, '_wcv_store_city', true );
		$vendor_data['customer_support']['state']    = get_user_meta( $vendor_id, '_wcv_store_state', true );
		$vendor_data['customer_support']['zip']      = get_user_meta( $vendor_id, '_wcv_store_postcode', true );
		
		// Store Policy
		$wcfm_policy_vendor_options = array();
		$wcv_shipping = (array) get_user_meta( $vendor_id, '_wcv_shipping', true );
		$wcfm_policy_vendor_options['policy_tab_title']    = ''; 
		$wcfm_policy_vendor_options['shipping_policy']     = ( isset( $wcv_shipping['shipping_policy'] ) ) ? $wcv_shipping['shipping_policy'] : '';
		$wcfm_policy_vendor_options['refund_policy']       = ( isset( $wcv_shipping['return_policy'] ) ) ? $wcv_shipping['return_policy'] : '';
		$wcfm_policy_vendor_options['cancellation_policy'] = ( isset( $wcv_shipping['return_policy'] ) ) ? $wcv_shipping['return_policy'] : '';
		update_user_meta( $vendor_id, 'wcfm_policy_vendor_options', $wcfm_policy_vendor_options );
		
		// Vendor Payment Details
		$_vendor_payment_mode = 'paypal';
		
		$vendor_data['payment'] = array();
		$vendor_data['payment']['method']                 = $_vendor_payment_mode;
		$vendor_data['payment']['paypal']['email']        = get_user_meta( $vendor_id, 'pv_paypal', true );
		$vendor_data['payment']['bank']['ac_number']      = get_user_meta( $vendor_id, 'wcv_bank_account_number', true );
		$vendor_data['payment']['bank']['bank_name']      = get_user_meta( $vendor_id, 'wcv_bank_name', true );
		$vendor_data['payment']['bank']['routing_number'] = get_user_meta( $vendor_id, 'wcv_bank_routing_number', true );
		$vendor_data['payment']['bank']['bank_addr']      = ''; //get_user_meta( $vendor_id, '_vendor_bank_address', true );
		$vendor_data['payment']['bank']['iban']           = get_user_meta( $vendor_id, 'wcv_bank_iban', true );
		$vendor_data['payment']['bank']['swift']          = get_user_meta( $vendor_id, 'wcv_bank_bic_swift', true );
		$vendor_data['payment']['bank']['ac_name']        = get_user_meta( $vendor_id, 'wcv_bank_account_name', true );
		$vendor_data['payment']['bank']['ac_cur']         = ''; //get_user_meta( $vendor_id, '_vendor_destination_currency', true );
		$vendor_data['payment']['bank']['ac_type']        = ''; //get_user_meta( $vendor_id, '_vendor_bank_account_type', true );
		
		if( !empty($vendor_data['payment']['bank']['ac_number']) ) {
			$vendor_data['payment']['method'] = 'bank_transfer';
		}
		
		$vendor_data['wcfm_vacation_mode']             = ( get_user_meta( $vendor_id, 'wcfm_vacation_mode', true ) ) ? get_user_meta( $vendor_id, 'wcfm_vacation_mode', true ) : 'no';
		$vendor_data['wcfm_disable_vacation_purchase'] = ( get_user_meta( $vendor_id, 'wcfm_disable_vacation_purchase', true ) ) ? get_user_meta( $vendor_id, 'wcfm_disable_vacation_purchase', true ) : 'no';
		$vendor_data['wcfm_vacation_mode_type']        = ( get_user_meta( $vendor_id, 'wcfm_vacation_mode_type', true ) ) ? get_user_meta( $vendor_id, 'wcfm_vacation_mode_type', true ) : 'instant';
		$vendor_data['wcfm_vacation_start_date']       = ( get_user_meta( $vendor_id, 'wcfm_vacation_start_date', true ) ) ? get_user_meta( $vendor_id, 'wcfm_vacation_start_date', true ) : '';
		$vendor_data['wcfm_vacation_end_date']         = ( get_user_meta( $vendor_id, 'wcfm_vacation_end_date', true ) ) ? get_user_meta( $vendor_id, 'wcfm_vacation_end_date', true ) : '';
		$vendor_data['wcfm_vacation_mode_msg']         = get_user_meta( $vendor_id, 'wcfm_vacation_mode_msg', true );
		
		$wcfm_profile_social_fields = array( 
																				'_wcv_twitter_username'        => 'twitter',
																				'_wcv_facebook_url'            => 'fb',
																				'_wcv_instagram_username'      => 'instagram',
																				'_wcv_youtube_url'             => 'youtube',
																				'_wcv_linkedin_url'            => 'linkdin',
																				'_wcv_googleplus_url'          => 'gplus',
																				'_wcv_snapchat_username'       => 'snapchat',
																				'_wcv_pinterest_url'           => 'pinterest',
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
		$store_slug = get_user_meta( $vendor_id, 'pv_shop_slug', true );
		wp_update_user( array( 'ID' => $vendor_id, 'user_nicename' => wc_clean( $store_slug ) ) );
		
		// Set Store name
		update_user_meta( $vendor_id, 'store_name', $vendor_data['store_name'] );
		update_user_meta( $vendor_id, 'wcfmmp_store_name', $vendor_data['store_name'] );
		
		// Set Vendor Shipping
		$wcfmmp_shipping = array ( '_wcfmmp_user_shipping_enable' => 'yes', '_wcfmmp_user_shipping_type' => 'by_zone' );
		update_user_meta( $vendor_id, '_wcfmmp_shipping', $wcfmmp_shipping );
		
		// Store Description
		$seller_info = get_user_meta( $vendor_id, 'pv_seller_info', true );
		update_user_meta( $vendor_id, '_store_short_description', $seller_info );
		$store_description = get_user_meta( $vendor_id, 'pv_shop_description', true );
		update_user_meta( $vendor_id, '_store_description', $store_description );
		
		// Store Commission
		$vendor_data['commission'] = array();
		$commission_type    = get_user_meta( $vendor_id, '_wcv_commission_type', true );
		$commission_fixed   = get_user_meta( $vendor_id, '_wcv_commission_amount', true );
		$commission_percent = get_user_meta( $vendor_id, '_wcv_commission_percent', true );
		$commission_rate    = get_user_meta( $vendor_id, 'pv_custom_commission_rate', true );
		$commission_fee		  = get_user_meta( $vendor_id, '_wcv_commission_fee', 	 true );
		
		$vendor_data['commission']['commission_mode']    = 'global';
		if( $commission_fixed || $commission_percent || $commission_rate ) {
			if( !$commission_percent && $commission_rate ) $commission_percent = $commission_rate;
			if( !$commission_fixed && $commission_rate ) $commission_fixed = $commission_rate;
			if ($commission_type == 'percent_fee') {
				$vendor_data['commission']['commission_mode']    = 'percent_fixed';
				$vendor_data['commission']['commission_fixed']   = $commission_fee;
				$vendor_data['commission']['commission_percent'] = $commission_percent; 
			} elseif ($commission_type == 'fixed_fee') {
				$vendor_data['commission']['commission_mode']    = 'fixed';
				$vendor_data['commission']['commission_fixed']   = (float) $commission_fixed + (float) $commission_fee;
				$vendor_data['commission']['commission_percent'] = $commission_percent; 
			} else {
				$vendor_data['commission']['commission_mode']    = $commission_type;
				$vendor_data['commission']['commission_fixed']   = $commission_fixed;
				$vendor_data['commission']['commission_percent'] = $commission_percent; 
			}
		}
		if( version_compare( WCV_VERSION, '2.0.0', '<' ) ) {
			if(WC_Vendors::$pv_options->get_option( 'give_shipping' )) {
				$vendor_data['commission']['get_shipping'] = 'yes';
			}
		} else {
			if( get_option('wcvendors_vendor_give_shipping') ) {
				$vendor_data['commission']['get_shipping'] = 'yes';
			}
		}
		if( version_compare( WCV_VERSION, '2.0.0', '<' ) ) {
			if(WC_Vendors::$pv_options->get_option( 'give_tax' )) {
				$vendor_data['commission']['get_tax'] = 'yes';
			}
		} else {
			if( get_option('wcvendors_vendor_give_taxes') ) {
				$vendor_data['commission']['get_tax'] = 'yes';
			}
		}
		
		// Store Genral Setting
		update_user_meta( $vendor_id, 'wcfmmp_profile_settings', $vendor_data );
		
		return true;
	}
	
	public function store_product_migrate( $vendor_id ) {
		global $WCFM, $WCFMmg, $wpdb;
		
		if( !$vendor_id ) return false;
		
		$wcfm_get_vendor_products = $WCFM->wcfm_vendor_support->wcfm_get_products_by_vendor( $vendor_id );
		
		if( !empty( $wcfm_get_vendor_products ) ) {
			foreach( $wcfm_get_vendor_products as $product_id => $wcfm_get_vendor_product ) {
				
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
				$commission_type       = get_post_meta( $product_id, 'wcv_commission_type', true);
				$commission_percentage = get_post_meta( $product_id, 'wcv_commission_percent', true);
				$commission_fixed      = get_post_meta( $product_id, 'wcv_commission_amount', true);
				$commission_rate 	     = get_post_meta( $product_id, 'pv_commission_rate', true );
				$commission_fee 	     = get_post_meta( $product_id, 'wcv_commission_fee', true );
				
				$product_commission_data = array();
				$product_commission_data['commission_mode']    = 'global';
				if( $commission_rate || $commission_fixed || $commission_percentage ) {
					if( !$commission_percentage && $commission_rate ) $commission_percentage = $commission_rate;
					if( !$commission_fixed && $commission_rate ) $commission_fixed = $commission_rate;
					if ($commission_type == 'percent_fee') {
						$product_commission_data['commission_mode']    = 'percent_fixed';
						$product_commission_data['commission_fixed']   = $commission_fee;
						$product_commission_data['commission_percent'] = $commission_percentage;
					} elseif ($commission_type == 'fixed_fee') {
						$product_commission_data['commission_mode']    = 'fixed';
						$product_commission_data['commission_fixed']   = (float) $commission_fixed + (float) $commission_fee;
						$product_commission_data['commission_percent'] = $commission_percentage;
					} else {
						$product_commission_data['commission_mode']    = $commission_type;
						$product_commission_data['commission_fixed']   = $commission_fixed;
						$product_commission_data['commission_percent'] = $commission_percentage;
					}
				}
				update_post_meta( $product_id, '_wcfmmp_commission', $product_commission_data );
			}
			
		}
		
		return true;
	}
	
	public function store_order_migrate( $vendor_id ) {
		global $WCFM, $WCFMmg, $wpdb;
		
		if( !$vendor_id ) return false;
		
		$offset = 0;
		$post_count = 9999;
  	while( $offset < $post_count ) {
			$sql  = 'SELECT * FROM ' . $wpdb->prefix . 'pv_commission AS commission';
			$sql .= ' WHERE 1=1';
			$sql .= " AND `vendor_id` = {$vendor_id}";
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
									$line_item = new WC_Order_Item_Product( $item );
									$product  = $line_item->get_product();
									$product_id = $line_item->get_product_id();
									$variation_id = $line_item->get_variation_id();
									if( $product_id ) {
										
										if( ($product_id != $vendor_order->product_id) && ($variation_id != $vendor_order->product_id) ) continue;
										
										// Updating Order Item meta with Vendor ID
										wc_update_order_item_meta( $order_item_id, '_vendor_id', $vendor_id );
										
										$purchase_price = get_post_meta( $product_id, '_purchase_price', true );
										if( !$purchase_price ) $purchase_price = $product->get_price();
										
										$customer_id = 0;
										if ( $order->get_user_id() ) 
											$customer_id = $order->get_user_id();
										
										$payment_method = ! empty( $order->get_payment_method() ) ? $order->get_payment_method() : '';
										
										$commission_status = 'pending';
										$shipping_status   = 'pending';
										$withdraw_status   = 'pending';
										if( $vendor_order->status == 'paid' ) {
											$shipping_status = 'shipped';
											$commission_status = 'shipped';
										}
										
										if( in_array( $order_status, array( 'processing',  'completed' ) ) ) {
											$commission_status = $order_status;
										}
										
										if( $vendor_order->status == 'paid' ) {
											$commission_status = 'completed';
											$withdraw_status   = 'completed';
										} elseif( $vendor_order->status == 'reversed' ) {
											$is_trashed = 1;
											$is_withdrawable = 0;
											$commission_status = 'cancelled';
											$withdraw_status   = 'cancelled';
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
										
										
										$shipping_cost = (float) $vendor_order->total_shipping;
										$shipping_tax  = 0;
										
										$commission_amount = (float) $vendor_order->total_due;
										
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
										
										if( version_compare( WCV_VERSION, '2.0.0', '<' ) ) {
											if(WC_Vendors::$pv_options->get_option( 'give_shipping' )) {
												$total_commission += (float) $shipping_cost;
												$gross_shipping_cost = $shipping_cost;
												$grosse_total 		  += (float) $gross_shipping_cost;
											}
										} else {
											if( get_option('wcvendors_vendor_give_shipping') ) {
												$total_commission += (float) $shipping_cost;
												$gross_shipping_cost = $shipping_cost;
												$grosse_total 		  += (float) $gross_shipping_cost;
											}
										}
										if( version_compare( WCV_VERSION, '2.0.0', '<' ) ) {
											if(WC_Vendors::$pv_options->get_option( 'give_tax' )) {
												$total_commission += (float) $vendor_order->tax;
												$gross_tax_cost      = (float) $vendor_order->tax;
												$grosse_total 		  += (float) $gross_tax_cost;
												$gross_shipping_tax  = $shipping_tax;
												$grosse_total 		  += (float) $gross_shipping_tax;
											}
										} else {
											if( get_option('wcvendors_vendor_give_taxes') ) {
												$total_commission += (float) $vendor_order->tax;
												$gross_tax_cost      = (float) $vendor_order->tax;
												$grosse_total 		  += (float) $gross_tax_cost;
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
															, $product->get_price()
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
															, $vendor_order->time
															, $vendor_order->time
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
																		, $vendor_order->time
																		, $vendor_order->time
														)
													);
											
											// Withdrawal Create
											if( $vendor_order->status == 'paid' ) {
												$_vendor_payment_mode = 'paypal'; //get_user_meta( $vendor_id, '_vendor_payment_mode', true );
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
																		, $vendor_order->time
																		, $vendor_order->time
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
																		, $vendor_order->time
																		, $vendor_order->time
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
									} else {
										wcfm_log( "Missing Product Order Skip: " . $vendor_id . " => " . $order_id );
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
		
		
		// WC Vendors Pro Vendor Feedback
		$wcv_feedback_tables = $wpdb->query( "SHOW tables like '{$wpdb->prefix}wcv_feedback'");
		if( $wcv_feedback_tables ) {
			$offset = 0;
			$post_count = 9999;
			while( $offset < $post_count ) {
				$vendor_reviews =  $wpdb->get_results(
																							"SELECT *
																							FROM {$wpdb->prefix}wcv_feedback 
																							WHERE vendor_id='$vendor_id' 
																							ORDER BY id ASC
																							LIMIT $offset, 10"
																					);
				
				
				if( !empty( $vendor_reviews ) ) {
					foreach( $vendor_reviews as $vendor_review ) {
						
						$review_rating = $vendor_review->rating;
						
						$userdata = get_userdata( $vendor_review->customer_id );
						$first_name = $userdata->first_name;
						$last_name  = $userdata->last_name;
						$display_name  = $userdata->display_name;
						if( $first_name ) {
							$customer_name = $first_name . ' ' . $last_name;
						} else {
							$customer_name = $display_name;
						}
						$customer_email = $userdata->user_email;
						
						$wcfm_review_submit = "INSERT into {$wpdb->prefix}wcfm_marketplace_reviews 
															(`vendor_id`, `author_id`, `author_name`, `author_email`, `review_title`, `review_description`, `review_rating`, `approved`, `created`)
															VALUES
															({$vendor_id}, {$vendor_review->customer_id}, '{$customer_name}', '{$customer_email}', '{$vendor_review->rating_title}', '{$vendor_review->comments}', '{$review_rating}', {$approved}, '{$vendor_review->postdate}')";
														
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
																						({$wcfm_review_id}, 'product', '{$vendor_review->product_id}', 'rating_product')";
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
							
							update_user_meta( $vendor_id, '_wcfmmp_last_author_id', $vendor_review->customer_id );
							update_user_meta( $vendor_id, '_wcfmmp_last_author_name', $customer_name );
						}
					}
				} else {
					break;
				}
				$offset += 10;
			}
		}
		
		update_user_meta( $vendor_id, '_wcfmmp_total_review_count', $total_review_count );
		update_user_meta( $vendor_id, '_wcfmmp_total_review_rating', $total_review_rating );
		
		if( $total_review_count ) $avg_review_rating = $total_review_rating/$total_review_count;
		update_user_meta( $vendor_id, '_wcfmmp_avg_review_rating', $avg_review_rating );
		
		$category_review_rating = update_user_meta( $vendor_id, '_wcfmmp_category_review_rating', $category_review_rating );
		
		return true;
	}
	
	public function store_vendor_migrate( $vendor_id ) {
		global $WCFM, $WCFMmg;
		
		if( !$vendor_id ) return false;
		
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