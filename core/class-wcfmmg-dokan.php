<?php

/**
 * WC Marketplace to WCFM Vendors Migrator plugin
 *
 * WCFM Marketplace Migrator Core Dokan Multivendor
 *
 * @author 		WC Lovers
 * @package 	wcfmmg/core
 * @version   1.0.0
 */

class WCFMmg_Dokan {
	
	public function __construct() {
		add_filter( 'wcfm_allwoed_vendor_user_roles', array( &$this, 'dokan_allwoed_vendor_user_roles' ), 900 );
	}
	
	function dokan_allwoed_vendor_user_roles( $user_roles ) {
		return array( 'seller' );
	}
	
	public function store_setting_migrate( $vendor_id ) {
		global $WCFM, $WCFMmg;
		
		if( !$vendor_id ) return false;
		
		$vendor_data = array();
		
		$vendor_user = get_userdata( $vendor_id );
		
		$vendor_data = get_user_meta( $vendor_id, 'dokan_profile_settings', true );
		
		if( !$vendor_data || ( $vendor_data && !is_array( $vendor_data ) ) ) $vendor_data = array(); 
		
		$vendor_data['banner_type'] = 'single_img';
		$vendor_data['list_banner'] = isset( $vendor_data['banner'] ) ? $vendor_data['banner'] : '';
		$vendor_data['store_name']  = isset( $vendor_data['store_name'] ) ? $vendor_data['store_name'] : $vendor_user->display_name;
		$vendor_data['email']       = $vendor_user->user_email;
		
		// Store Location
		$vendor_data['find_address']   = isset( $vendor_data['find_address'] ) ? esc_attr( $vendor_data['find_address'] ) : '';
		$vendor_data['store_location'] = isset( $vendor_data['location'] ) ? esc_attr( $vendor_data['location'] ) : '';
		$vendor_data['store_lat']      = 0;
		$vendor_data['store_lng']      = 0;
		
		// Customer Support
		$vendor_data['customer_support'] = array();
		$vendor_data['customer_support']['phone']    = isset( $vendor_data['phone'] ) ? esc_attr( $vendor_data['phone'] ) : '';
		$vendor_data['customer_support']['email']    = $vendor_user->user_email;
		$vendor_data['customer_support']['address1'] = isset( $vendor_data['address']['street_1'] ) ? $vendor_data['address']['street_1'] : '';
		$vendor_data['customer_support']['address2'] = isset( $vendor_data['address']['street_2'] ) ? $vendor_data['address']['street_2'] : '';
		$vendor_data['customer_support']['country']  = isset( $vendor_data['address']['country'] ) ? $vendor_data['address']['country'] : '';
		$vendor_data['customer_support']['city']     = isset( $vendor_data['address']['city'] ) ? $vendor_data['address']['city'] : '';
		$vendor_data['customer_support']['state']    = isset( $vendor_data['address']['state'] ) ? $vendor_data['address']['state'] : '';
		$vendor_data['customer_support']['zip']      = isset( $vendor_data['address']['zip'] ) ? $vendor_data['address']['zip'] : '';
		
		// Store Policy
		$wcfm_policy_vendor_options = array();
		$wcfm_policy_vendor_options['policy_tab_title']    = ''; 
		$wcfm_policy_vendor_options['shipping_policy']     = get_user_meta( $vendor_id, '_dps_ship_policy', true );
		$wcfm_policy_vendor_options['refund_policy']       = get_user_meta( $vendor_id, '_dps_refund_policy', true );
		$wcfm_policy_vendor_options['cancellation_policy'] = get_user_meta( $vendor_id, '_dps_refund_policy', true );
		update_user_meta( $vendor_id, 'wcfm_policy_vendor_options', $wcfm_policy_vendor_options );
		
		// Store SEO
		$vendor_data['store_seo']['wcfmmp-seo-meta-title']     = isset( $vendor_data['store_seo']['dokan-seo-meta-title'] ) ? $vendor_data['store_seo']['dokan-seo-meta-title'] : '';
		$vendor_data['store_seo']['wcfmmp-seo-meta-desc']      = isset( $vendor_data['store_seo']['dokan-seo-meta-desc'] ) ? $vendor_data['store_seo']['dokan-seo-meta-desc'] : '';
		$vendor_data['store_seo']['wcfmmp-seo-meta-keywords']  = isset( $vendor_data['store_seo']['dokan-seo-meta-keywords'] ) ? $vendor_data['store_seo']['dokan-seo-meta-keywords'] : '';
		$vendor_data['store_seo']['wcfmmp-seo-og-title']       = isset( $vendor_data['store_seo']['dokan-seo-og-title'] ) ? $vendor_data['store_seo']['dokan-seo-og-title'] : '';
		$vendor_data['store_seo']['wcfmmp-seo-og-desc']        = isset( $vendor_data['store_seo']['dokan-seo-og-desc'] ) ? $vendor_data['store_seo']['dokan-seo-og-desc'] : '';
		$vendor_data['store_seo']['wcfmmp-seo-og-image']       = isset( $vendor_data['store_seo']['dokan-seo-og-image'] ) ? $vendor_data['store_seo']['dokan-seo-og-image'] : 0;
		$vendor_data['store_seo']['wcfmmp-seo-twitter-title']  = isset( $vendor_data['store_seo']['dokan-seo-twitter-title'] ) ? $vendor_data['store_seo']['dokan-seo-twitter-title'] : '';
		$vendor_data['store_seo']['wcfmmp-seo-twitter-desc']   = isset( $vendor_data['store_seo']['dokan-seo-twitter-desc'] ) ? $vendor_data['store_seo']['dokan-seo-twitter-desc'] : '';
		$vendor_data['store_seo']['wcfmmp-seo-twitter-image']  = isset( $vendor_data['store_seo']['dokan-seo-twitter-image'] ) ? $vendor_data['store_seo']['dokan-seo-twitter-image'] : 0;
		
		// Set Store name
		update_user_meta( $vendor_id, 'store_name', $vendor_data['store_name'] );
		update_user_meta( $vendor_id, 'wcfmmp_store_name', $vendor_data['store_name'] );
		
		// Set Vendor Shipping
		$wcfmmp_shipping = array ( '_wcfmmp_user_shipping_enable' => 'yes', '_wcfmmp_user_shipping_type' => 'by_zone' );
		update_user_meta( $vendor_id, '_wcfmmp_shipping', $wcfmmp_shipping );
		
		// Store Commission
		$vendor_data['commission'] = array();
		$commission_type    = get_user_meta( $vendor_id, 'dokan_admin_percentage_type', true );
		$commission_value   = get_user_meta( $vendor_id, 'dokan_admin_percentage', true );
		
		$vendor_data['commission']['commission_mode']    = 'global';
		if( $commission_value ) {
			if( ($commission_type == 'percentage') ) $commission_type = 'percent';
			if ( $commission_type == 'percent') {
				$vendor_data['commission']['commission_mode']    = $commission_type;
				$vendor_data['commission']['commission_percent'] = $commission_value; 
			} else {
				$vendor_data['commission']['commission_mode']    = $commission_type;
				$vendor_data['commission']['commission_fixed']   = $commission_value;
			}
		}
		$vendor_data['commission']['get_shipping'] = 'yes';
		$vendor_data['commission']['get_tax'] = 'yes';
		
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
			$sql  = 'SELECT * FROM ' . $wpdb->prefix . 'dokan_orders AS commission';
			$sql .= ' WHERE 1=1';
			$sql .= " AND `seller_id` = {$vendor_id}";
			//$sql .= " AND `order_status` = 'wc-processing'";
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
							
							$payment_method = ! empty( $order->get_payment_method() ) ? $order->get_payment_method() : '';
										
							$order_date = ( version_compare( WC_VERSION, '2.7', '<' ) ) ? $order->order_date : $order->get_date_created();
							
							$items = $order->get_items('line_item');
							if( !empty( $items ) ) {
								foreach( $items as $order_item_id => $item ) {
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
									
									// Updating Order Item meta with Vendor ID
									wc_update_order_item_meta( $order_item_id, '_vendor_id', $vendor_id );
									
									$customer_id = 0;
									if ( $order->get_user_id() ) 
										$customer_id = $order->get_user_id();
									
									$commission_status = 'pending';
									$shipping_status   = 'pending';
									$withdraw_status   = 'pending';
									
									if( in_array( $order_status, array( 'processing',  'completed' ) ) ) {
										$commission_status = $order_status;
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
									
									$discount_amount = 0;
									$discount_type = '';
									$other_amount = 0;
									$other_amount_type = '';
									$withdraw_charges = 0;
									$refunded_amount = 0;
									$grosse_total      = $gross_tax_cost = $gross_shipping_cost = $gross_shipping_tax = $gross_sales_total = 0;
									
									$discount_amount     = ( $line_item->get_subtotal() - $line_item->get_total() );
										
									$grosse_total        = $line_item->get_subtotal();
									$gross_sales_total   = $grosse_total;
									
									$shipping_cost = $order->get_total_shipping();
									if( $shipping_cost ) $shipping_cost = (float) $shipping_cost / count($items);
									else $shipping_cost = 0;
									$shipping_tax  = 0;
									
									$line_tax = $line_item->get_total_tax();
									if( !$line_tax ) $line_tax = 0;
									
									$total_commission  = $vendor_order->net_amount;
									if( $total_commission ) $total_commission = (float) $total_commission / count($items);
									$commission_amount = $total_commission - $shipping_cost - (float) $line_tax;
									
									$total_commission  =  round( $total_commission, 2 );
									$commission_amount = round( $commission_amount, 2 );
									
									$gross_shipping_cost = $shipping_cost;
									$grosse_total 		  += (float) $gross_shipping_cost;
									
									$gross_tax_cost      = (float) $line_tax;
									$grosse_total 		  += (float) $gross_tax_cost;
									
									$gross_sales_total  += (float) $gross_shipping_cost;
									$gross_sales_total  += (float) $gross_tax_cost;
									$gross_sales_total  += (float) $gross_shipping_tax;
									
									try {
										$sql = $wpdb->prepare(
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
														, $line_tax
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
														, $order_date
														, $order_date
										);
										wcfm_log($sql);
										$wpdb->query( $sql );
										$commission_id = $wpdb->insert_id;
									} catch( Exception $e ) {
										wcfm_log("Order Migration Error: " . $ex->getMessage());
									}
									
									if( $commission_id ) {
									
										// Commission Ledger Update
										$reference_details = sprintf( __( 'Commission for %s order.', 'wc-multivendor-marketplace-migration' ), '<b>' . get_the_title( $product_id ) . '</b>' );
										try {
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
																, $order_date
																, $order_date
												)
											);
										} catch( Exception $ex ) {
											wcfm_log("Ledger Update Error: " . $ex->getMessage());
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
		
		// Withdrawal Migration
		$offset = 0;
		$post_count = 9999;
  	while( $offset < $post_count ) {
			$sql  = 'SELECT * FROM ' . $wpdb->prefix . 'dokan_withdraw AS commission';
			$sql .= ' WHERE 1=1';
			$sql .= " AND `user_id` = {$vendor_id}";
			$sql .= " ORDER BY `id` DESC";
			$sql .= " LIMIT 10";
			$sql .= " OFFSET {$offset}";
			
			$vendor_withdrawals = $wpdb->get_results( $sql );
			
			if( !empty( $vendor_withdrawals ) ) {
				foreach( $vendor_withdrawals as $vendor_withdraw ) {
		
					$withdraw_status = 'pending'; 
					if( $vendor_withdraw->status == 1 ) $withdraw_status = 'completed'; 
					
					$withdraw_method = $vendor_withdraw->method;
					if( $withdraw_method == 'bank' ) $withdraw_method = 'bank_transfer';
					
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
													, withdraw_note
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
													, %s
													, %d
													, %s
													, %s
													) ON DUPLICATE KEY UPDATE `created` = now()"
											, $vendor_id
											, 0
											, 0
											, $withdraw_method
											, $vendor_withdraw->amount
											, 0
											, $withdraw_status
											, 'by_paymode'
											, $vendor_withdraw->note
											, 0
											, $vendor_withdraw->date
											, $vendor_withdraw->date
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
											, $vendor_withdraw->amount
											, $withdraw_request_id
											, 'withdraw'
											, $reference_details
											, $withdraw_status
											, $vendor_withdraw->date
											, $vendor_withdraw->date
							)
						);
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