<?php

    /**
     * WC_Gateway_Sagepay_Direct class.
     *
     * @extends WC_Payment_Gateway
     */
    class WC_Gateway_Sagepay_Direct extends WC_Payment_Gateway {

        /**
         * __construct function.
         *
         * @access public
         * @return void
         */
        public function __construct() {

            $this->id                   = 'sagepaydirect';
            $this->method_title         = __( 'SagePay Direct', 'woocommerce_sagepayform' );
            $this->method_description   = __( 'SagePay Direct', 'woocommerce_sagepayform' );
            $this->icon                 = apply_filters( 'wc_sagepaydirect_icon', '' );
            $this->has_fields           = true;

            // Load the form fields
            $this->init_form_fields();

            // Load the settings.
            $this->init_settings();
            $this->init_settings();

            // Get setting values
            $this->enabled			= $this->settings['enabled'];
            $this->title			= $this->settings['title'];
            $this->description		= $this->settings['description'];
            $this->vendor 			= $this->settings['vendor'];
            $this->status			= $this->settings['status'];
			$this->txtype			= $this->settings['txtype'];
			$this->cvv				= isset( $this->settings['cvv'] ) && $this->settings['cvv'] == 'yes' ? true : false;
			$this->cardtypes		= $this->settings['cardtypes'];
			$this->secure			= isset( $this->settings['3dsecure'] ) ? $this->settings['3dsecure'] : "0";
			$this->allowgiftaid 	= "0";
			$this->accounttype 		= "E";
			$this->billingagreement = "0";
			$this->createtoken 		= "0";
			$this->storetoken 		= "0";
			$this->debug			= isset( $this->settings['debug'] ) && $this->settings['debug'] == 'yes' ? true : false;
			$this->notification 	= isset( $this->settings['notification'] ) ? $this->settings['notification'] : get_bloginfo( 'admin_email' );

           	// Sage urls
            if ( $this->status == 'live' ) {
            	// LIVE
				$this->purchaseURL 		= 'https://live.sagepay.com/gateway/service/vspdirect-register.vsp';
				$this->voidURL 			= 'https://live.sagepay.com/gateway/service/void.vsp';
				$this->refundURL 		= 'https://live.sagepay.com/gateway/service/refund.vsp';
				$this->releaseURL 		= 'https://live.sagepay.com/gateway/service/release.vsp';
				$this->repeatURL 		= 'https://live.sagepay.com/gateway/service/repeat.vsp';
				$this->testurlcancel	= 'https://live.sagepay.com/gateway/service/cancel.vsp';
				$this->authoriseURL 	= 'https://live.sagepay.com/gateway/service/authorise.vsp';
				$this->callbackURL 		= 'https://live.sagepay.com/gateway/service/direct3dcallback.vsp';
			} else {
				// TEST
				$this->purchaseURL 		= 'https://test.sagepay.com/gateway/service/vspdirect-register.vsp';
				$this->voidURL 			= 'https://test.sagepay.com/gateway/service/void.vsp';
				$this->refundURL 		= 'https://test.sagepay.com/gateway/service/refund.vsp';
				$this->releaseURL 		= 'https://test.sagepay.com/gateway/service/release.vsp';
				$this->repeatURL 		= 'https://test.sagepay.com/gateway/service/repeat.vsp';
				$this->testurlcancel	= 'https://test.sagepay.com/gateway/service/cancel.vsp';
				$this->authoriseURL 	= 'https://test.sagepay.com/gateway/service/authorise.vsp';
				$this->callbackURL 		= 'https://test.sagepay.com/gateway/service/direct3dcallback.vsp';
			}

			// 3D iframe
            $this->iframe_3d_callback   = esc_url( $this->get_plugin_url() . '/assets/pages/3dcallback.php' );
            $this->iframe_3d_redirect   = esc_url( $this->get_plugin_url() . '/assets/pages/3dredirect.php' );

            $this->vpsprotocol			= '3.00';

            // ReferrerID
            $this->referrerid 			= 'F4D0E135-F056-449E-99E0-EC59917923E1';

            // Supports
            $this->supports 			= array(
            									'products',
            									'refunds',
												'subscriptions',
												'subscription_cancellation',
												'subscription_reactivation',
												'subscription_suspension',
												'subscription_amount_changes',
												'subscription_payment_method_change',
												'subscription_date_changes',
            									'pre-orders'
										);

			// Subscriptions
			if ( class_exists( 'WC_Subscriptions_Order' ) ) {
				add_action( 'scheduled_subscription_payment_' . $this->id, array( $this, 'process_scheduled_subscription_payment' ), 10, 3 );
				add_filter( 'woocommerce_subscriptions_renewal_order_meta_query', array( $this, 'remove_renewal_order_meta' ), 10, 4 );
				add_action( 'woocommerce_subscriptions_changed_failing_payment_method_stripe', array( $this, 'update_failing_payment_method' ), 10, 3 );
				// display the current payment method used for a subscription in the "My Subscriptions" table
				// add_filter( 'woocommerce_my_subscriptions_recurring_payment_method', array( $this, 'maybe_render_subscription_payment_method' ), 10, 3 );
			}

			// Pre-Orders
			if ( class_exists( 'WC_Pre_Orders_Order' ) ) {
				add_action( 'wc_pre_orders_process_pre_order_completion_payment_' . $this->id, array( $this, 'process_pre_order_release_payment' ) );
			}

			// Add test card info to the description if in test mode
			if ( $this->status != 'live' ) {
				$this->description .= ' ' . sprintf( __( '<br />TEST MODE ENABLED.<br />In test mode, you can use the card number 4929 0000 0000 6 with any CVC and a valid expiration date or check the documentation (<a href="%s">Test card details for your test transactions</a>) for more card numbers.', 'woocommerce_sagepayform' ), 'http://www.sagepay.co.uk/support/12/36/test-card-details-for-your-test-transactions' );
				$this->description  = trim( $this->description );
			}

			// Hooks
			add_action( 'woocommerce_receipt_' . $this->id, array($this, 'authorise_3dsecure') );
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

			// SSL Check
			add_action( 'admin_notices', array( $this, $this->id . '_ssl_check') );

			// Scripts
			add_action( 'wp_enqueue_scripts', array( $this, $this->id . '_scripts' ) );

			// Logs
			if ( $this->debug ) {
				$this->log = new WC_Logger();
			}

			// WC version
			$this->wc_version = get_option( 'woocommerce_version' );

        } // END __construct

		/**
		 * Check if this gateway is enabled
		 */
		public function is_available() {

			if ( $this->enabled == "yes" ) {

				if ( ! is_ssl() && ! $this->status == 'live' ) {
					return false;
				}

				// Required fields check
				if ( ! $this->vendor ) {
					return false;
				}

				return true;

			}
			return false;

		}

		/**
    	 * Payment form on checkout page
    	 */
		public function payment_fields() {

			if ( get_option('woocommerce_force_ssl_checkout')=='no' && $this->status=='live' ) {
		     	echo '<div class="error"><p>'.sprintf(__('SagePay Direct is enabled and the force SSL option is disabled; your checkout is not secure! Please enable SSL and ensure your server has a valid SSL certificate.', 'woocommerce_sagepayform') ).'<br /></p></div>';
			}

			if ( $this->description ) { 
				echo '<p>' . $this->description . '</p>'; 
			}

			if ( method_exists( $this, 'credit_card_form' ) ) {

				$this->credit_card_form();
			
			} else {
		
				$this->sagepay_credit_card_form();
		
			}

		}

		/**
    	 * Validate the payment form
    	 */
		public function validate_fields() {

			try {

				$sage_card_number 		= isset($_POST[$this->id . '-card-number']) ? woocommerce_clean($_POST[$this->id . '-card-number']) : '';
				$sage_card_cvc 			= isset($_POST[$this->id . '-card-cvc']) ? woocommerce_clean($_POST[$this->id . '-card-cvc']) : '';
				$sage_card_expiry		= isset($_POST[$this->id . '-card-expiry']) ? woocommerce_clean($_POST[$this->id . '-card-expiry']) : '';

				// Format values
				$sage_card_number    	= str_replace( array( ' ', '-' ), '', $sage_card_number );
				$sage_card_expiry    	= array_map( 'trim', explode( '/', $sage_card_expiry ) );
				$sage_card_exp_month 	= str_pad( $sage_card_expiry[0], 2, "0", STR_PAD_LEFT );
				$sage_card_exp_year  	= $sage_card_expiry[1];

				// Validate values
				if ( ( $this->cvv && !ctype_digit( $sage_card_cvc ) ) || ( $this->cvv && strlen( $sage_card_cvc ) < 3 ) || ( $this->cvv && strlen( $sage_card_cvc ) > 4 ) ) {
					throw new Exception( __( 'Card security code is invalid (only digits are allowed)', 'woocommerce_sagepayform' ) );
				}

				if ( !ctype_digit( $sage_card_exp_month ) || $sage_card_exp_month > 12 || $sage_card_exp_month < 1 ) {
					throw new Exception( __( 'Card expiration month is invalid', 'woocommerce_sagepayform' ) );
				}	

				if ( !ctype_digit( $sage_card_exp_year ) || $sage_card_exp_year < date('y') || strlen($sage_card_exp_year) != 2 ) {
					throw new Exception( __( 'Card expiration year is invalid', 'woocommerce_sagepayform' ) );
				}

				if ( empty( $sage_card_number ) || ! ctype_digit( $sage_card_number ) ) {
					throw new Exception( __( 'Card number is invalid', 'woocommerce_sagepayform' ) );
				}

				return true;

			} catch( Exception $e ) {

				wc_add_notice( $e->getMessage(), 'error' );
				return false;

			}

		}

		/**
		 * Process the payment and return the result
		 */
		function process_payment( $order_id ) {

			session_start();

			$order = new WC_Order( $order_id );

			$sage_card_number 		= isset($_POST[$this->id . '-card-number']) ? woocommerce_clean($_POST[$this->id . '-card-number']) : '';
			$sage_card_cvc 			= isset($_POST[$this->id . '-card-cvc']) ? woocommerce_clean($_POST[$this->id . '-card-cvc']) : '';
			$sage_card_expiry		= isset($_POST[$this->id . '-card-expiry']) ? woocommerce_clean($_POST[$this->id . '-card-expiry']) : '';

			// Format values
			$sage_card_number    = str_replace( array( ' ', '-' ), '', $sage_card_number );
			$sage_card_expiry    = array_map( 'trim', explode( '/', $sage_card_expiry ) );
			$sage_card_exp_month = str_pad( $sage_card_expiry[0], 2, "0", STR_PAD_LEFT );
			$sage_card_exp_year  = $sage_card_expiry[1];

            $VendorTxCode = $order->order_key . '-' . $order->id . '-' . time();

            // SAGE Line 50 Fix
            $VendorTxCode = str_replace( 'order_', '', $VendorTxCode );

            if ( $order->billing_country == 'US' ) {
            	$billing_state = $order->billing_state;
            } else {
            	$billing_state = '';
            }

            if ( $order->shipping_country == 'US' ) {
            	$shipping_state = $order->shipping_state;
            } else {
            	$shipping_state = '';
            }

            // Delivery Address
            if ( empty( $order->shipping_first_name ) ) {
                $DeliveryFirstnames = $order->billing_first_name;
            } else {
            	$DeliveryFirstnames = $order->shipping_first_name;
            }

            if ( empty( $order->shipping_last_name ) ) {
                $DeliverySurname    = $order->billing_last_name;
            } else {
            	$DeliverySurname    = $order->shipping_last_name;
            }

            if ( empty( $order->shipping_address_1 )  ) {
                $DeliveryAddress1   = $order->billing_address_1;
            } else {
            	$DeliveryAddress1   = $order->shipping_address_1;
            }

            if ( empty( $order->shipping_address_2 )  ) {
                $DeliveryAddress2   = $order->billing_address_2;
            } else {
                $DeliveryAddress2   = $order->shipping_address_2;
            }
            
            if ( empty( $order->shipping_city )  ) {
                $DeliveryCity       = $order->billing_city;
            } else {
                $DeliveryCity       = $order->shipping_city;
            }

            if ( $shipping_state == '' ) {
                $DeliveryState      = $billing_state;
            } else {
                $DeliveryState      = $shipping_state;
            }

            if ( empty( $order->shipping_postcode )  ) {
                $DeliveryPostCode   = $order->billing_postcode;
            } else {
                $DeliveryPostCode   = $order->shipping_postcode;
            }

            if ( empty( $order->shipping_country ) ) {
                $DeliveryCountry    = $order->billing_country;
            } else {
                $DeliveryCountry    = $order->shipping_country;
            }

            // Get Customer's IP Address
			if ( !empty($_SERVER['HTTP_CLIENT_IP']) ) {
    			$userip 	= $_SERVER['HTTP_CLIENT_IP'];
			} elseif ( !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ) {
    			$userip 	= $_SERVER['HTTP_X_FORWARDED_FOR'];
			} else {
    			$userip 	= $_SERVER['REMOTE_ADDR'];
			}

			/**
			 * Modify the order amount for subscriptions
			 *
			 * If there is a subscription we get the amount from WC_Subscriptions_Order::get_total_initial_payment( $order )
			 * otherwise it's just the order total
			 *
			 * WC_Subscriptions_Order::get_total_initial_payment( $order ) works out if there is a payment due today, 
			 * if not this value will be 0 and no money will be taken today
			 */
			if( class_exists( 'WC_Subscriptions' ) && WC_Subscriptions_Order::order_contains_subscription( $order_id ) ) {
				$amount = urlencode( WC_Subscriptions_Order::get_total_initial_payment( $order ) );
			} else {
				$amount = urlencode( $order->order_total );
			}

			/**
			 * Pre-Orders
			 *
			 * The AUTHENTICATE and AUTHORISE methods are specifically for use by merchants who are either:
			 * 		Unable to fulfil the majority of orders in less than 6 days or sometimes fulfil them after 30 days.
			 *   	Do not know the exact amount of the transaction at the time the order is placed, for example;
			 *    		items shipped priced by weight or items affected by foreign exchange rates.
			 */
			if( class_exists( 'WC_Pre_Orders' ) && WC_Pre_Orders_Order::order_contains_pre_order( $order_id ) ) {
				$txtype = 'AUTHENTICATE';
			} else {
				$txtype = $this->txtype;
			}

            // Cart Contents for SagePay 'Basket'
            $sage_pay_basket = array();

            // Cart Contents
            $item_loop = 2;
            if ( sizeof( $order->get_items() ) > 0 ) {
	            foreach ( $order->get_items() as $item ) {
	                if ( $item['qty'] ) {

	                    $_product = $order->get_product_from_item( $item );

	                    $item_loop++;

	                    $item_name = $item['name'];

	                    if ( $_product->get_sku() ) {
	                        $item_name = '[' . $_product->get_sku() . ']' .  $item_name;
	                    }
							
						if ( $this->wc_version < '2.4' ) {
							// Deprecated in WC 2.4
							$item_meta 	= new WC_Order_Item_Meta( $item['item_meta'] );

						} else {

							$item_meta  = new WC_Order_Item_Meta( $item, $_product );

						}

						if ( $meta = $item_meta->display( true, true ) ) {
							$item_name .= ' ( ' . $meta . ' )';
						}

	                    $sage_pay_basket[] =
	                        str_replace( ':', '', $item_name )                                                              	// Description
	                        . ':' . $item['qty']                                                                            	// Quantity
	                        . ':' . $order->get_item_total( $item, false )                                                  	// Ex Tax
	                        . ':' . $order->get_item_tax( $item )                                                           	// Tax Amount
	                        . ':' . $order->get_item_total( $item, true )                                                   	// Inc Tax
	                        . ':' . $order->get_line_total( $item, true )                                                   	// Total line cost
	                    ;
	                }
	            }
			}

            // Shipping Cost
            if ( $order->order_shipping ) {
            	$sage_pay_basket[] =
            	    __( 'Shipping', 'woocommerce_sagepayform' )                                                               	// Description
            	    . ':' . 1                                                                                                 	// Quantity
            	    . ':' . number_format( $order->order_shipping, 2, '.', '' )                                               	// Ex Tax
            	    . ':' . number_format( $order->order_shipping_tax, 2, '.', '' )                                           	// Tax Amount
            	    . ':' . number_format( $order->order_shipping + $order->order_shipping_tax, 2, '.', '' )                  	// Inc Tax
            	    . ':' . number_format( $order->order_shipping + $order->order_shipping_tax, 2, '.', '' )                  	// Total line cost
            	;
        	} else {
            	$sage_pay_basket[] =
            	    __( 'Shipping', 'woocommerce_sagepayform' )                                                               	// Description
            	    . ':' . 1                                                                                                 	// Quantity
            	    . ':' . number_format( 0, 2, '.', '' )                                               					  	// Ex Tax
            	    . ':' . number_format( 0, 2, '.', '' )                                           						  	// Tax Amount
            	    . ':' . number_format( 0, 2, '.', '' )                  												  	// Inc Tax
            	    . ':' . number_format( 0, 2, '.', '' )                  												  	// Total line cost
            	;        		
        	}
			
			// Coupon Cost
			if ( $this->wc_version < '2.3' ) {
				// get_order_discount() deprecated in WC 2.3
				$sage_pay_basket[] =
                	__( 'Discount', 'woocommerce_sagepayform' )                                                     			// Description
            	    . ':' . 1                                                                                       			// Quantity
            	    . ':' . number_format( $order->get_order_discount(), 2, '.', '' )                       					// Ex Tax
            	    . ':' . number_format( '0', 2, '.', '' )                       												// Tax Amount
            	    . ':' . number_format( $order->get_order_discount(), 2, '.', '' )                  							// Inc Tax
            	    . ':' . number_format( $order->get_order_discount(), 2, '.', '' )                  							// Total line cost
            	;

			} else {

				$sage_pay_basket[] =
                	__( 'Discount', 'woocommerce_sagepayform' )                                                     			// Description
            	    . ':' . 1                                                                                       			// Quantity
            	    . ':' . number_format( $order->get_total_discount(), 2, '.', '' )                       					// Ex Tax
            	    . ':' . number_format( '0', 2, '.', '' )                       												// Tax Amount
            	    . ':' . number_format( $order->get_total_discount(), 2, '.', '' )                  							// Inc Tax
            	    . ':' . number_format( $order->get_total_discount(), 2, '.', '' )                  							// Total line cost
            	;

			}

            $sage_pay_basket = $item_loop . ':' . implode( ':', $sage_pay_basket );
            $sage_pay_basket = str_replace( "\n", "", $sage_pay_basket );
            $sage_pay_basket = str_replace( "\r", "", $sage_pay_basket );

			// make your query.
			$data	 = "VPSProtocol=" 			. $this->vpsprotocol;
			$data  	.= "&TxType=" 				. $txtype;
			$data  	.= "&Vendor=" 				. $this->vendor;
			$data  	.= "&VendorTxCode=" 		. $VendorTxCode;
			$data  	.= "&Amount=" 				. $amount;
			$data  	.= "&Currency=" 			. get_woocommerce_currency();
			$data  	.= "&Description=" 			. __( 'Order', 'woocommerce_sagepayform' ) . ' ' . str_replace( '#' , '' , $order->get_order_number() );
			$data  	.= "&CardHolder=" 			. urlencode( $order->billing_first_name . ' ' . $order->billing_last_name );
			$data  	.= "&CardNumber=" 			. urlencode( $sage_card_number );
			$data  	.= "&ExpiryDate=" 			. urlencode( $sage_card_exp_month . $sage_card_exp_year );
			$data  	.= "&CV2=" 					. urlencode( $sage_card_cvc );
			$data  	.= "&CardType=" 			. $this->cc_type($sage_card_number);
			$data  	.= "&BillingSurname=" 		. urlencode( $order->billing_last_name );
			$data  	.= "&BillingFirstnames=" 	. urlencode( $order->billing_first_name );
			$data  	.= "&BillingAddress1=" 		. urlencode( $order->billing_address_1 );
			$data  	.= "&BillingAddress2=" 		. urlencode( $order->billing_address_2 );
			$data  	.= "&BillingCity=" 			. urlencode( $order->billing_city );
			$data  	.= "&BillingPostCode=" 		. urlencode( $order->billing_postcode );
			$data  	.= "&BillingCountry=" 		. urlencode( $order->billing_country );
			$data  	.= "&BillingState=" 		. urlencode( $billing_state );
			$data  	.= "&BillingPhone=" 		. urlencode( $order->billing_phone );
			$data  	.= "&DeliverySurname=" 		. urlencode( apply_filters( 'woocommerce_sagepay_direct_deliverysurname', $DeliverySurname ) );
			$data  	.= "&DeliveryFirstnames=" 	. urlencode( apply_filters( 'woocommerce_sagepay_direct_deliveryfirstname', $DeliveryFirstnames ) );
			$data  	.= "&DeliveryAddress1=" 	. urlencode( apply_filters( 'woocommerce_sagepay_direct_deliveryaddress1', $DeliveryAddress1 ) );
			$data  	.= "&DeliveryAddress2=" 	. urlencode( apply_filters( 'woocommerce_sagepay_direct_deliveryaddress2', $DeliveryAddress2 ) );
			$data  	.= "&DeliveryCity=" 		. urlencode( apply_filters( 'woocommerce_sagepay_direct_deliverycity', $DeliveryCity ) );
			$data  	.= "&DeliveryPostCode=" 	. urlencode( apply_filters( 'woocommerce_sagepay_direct_deliverypostcode', $DeliveryPostCode ) );
			$data  	.= "&DeliveryCountry=" 		. urlencode( apply_filters( 'woocommerce_sagepay_direct_deliverycountry', $DeliveryCountry ) );
			$data  	.= "&DeliveryState=" 		. urlencode( apply_filters( 'woocommerce_sagepay_direct_deliverystate', $DeliveryState ) );
			$data  	.= "&DeliveryPhone=" 		. urlencode( apply_filters( 'woocommerce_sagepay_direct_deliveryphone', $order->billing_phone ) );
			$data  	.= "&CustomerEMail=" 		. $order->billing_email;
			$data 	.= "&Basket="				. $sage_pay_basket;
			$data  	.= "&AllowGiftAid=" 		. $this->allowgiftaid;
			$data  	.= "&ApplyAVSCV2=" 			. $this->cvv;
			$data  	.= "&ClientIPAddress=" 		. $userip;
			$data  	.= "&Apply3DSecure=" 		. $this->secure;
			$data  	.= "&AccountType=" 			. $this->accounttype;
			$data  	.= "&BillingAgreement=" 	. $this->billingagreement;
			$data  	.= "&CreateToken=" 			. $this->createtoken;
			$data  	.= "&StoreToken=" 			. $this->storetoken;
			$data  	.= "&ReferrerID=" 			. $this->referrerid;
			$data  	.= "&Website=" 				. site_url();

			/**
			 * Debugging
			 */
  			if ( $this->debug == true ) {

  				$debug_data	 	 = "VPSProtocol=" 			. $this->vpsprotocol;
  				$debug_data  	.= "&TxType=" 				. $txtype;
  				$debug_data  	.= "&Vendor=" 				. $this->vendor;
  				$debug_data  	.= "&VendorTxCode=" 		. $VendorTxCode;
  				$debug_data  	.= "&Amount=" 				. $amount;
  				$debug_data  	.= "&Currency=" 			. get_woocommerce_currency();
  				$debug_data  	.= "&Description=" 			. __( 'Order', 'woocommerce_sagepayform' ) . ' ' . str_replace( '#' , '' , $order->get_order_number() );
  				$debug_data  	.= "&CardHolder=" 			. urlencode( $order->billing_first_name . ' ' . $order->billing_last_name );
  				$debug_data  	.= "&CardType=" 			. $this->cc_type($sage_card_number);
  				$debug_data  	.= "&BillingSurname=" 		. urlencode( $order->billing_last_name );
  				$debug_data  	.= "&BillingFirstnames=" 	. urlencode( $order->billing_first_name );
  				$debug_data  	.= "&BillingAddress1=" 		. urlencode( $order->billing_address_1 );
  				$debug_data  	.= "&BillingAddress2=" 		. urlencode( $order->billing_address_2 );
  				$debug_data  	.= "&BillingCity=" 			. urlencode( $order->billing_city );
  				$debug_data  	.= "&BillingPostCode=" 		. urlencode( $order->billing_postcode );
  				$debug_data  	.= "&BillingCountry=" 		. urlencode( $order->billing_country );
  				$debug_data  	.= "&BillingState=" 		. urlencode( $billing_state );
  				$debug_data  	.= "&BillingPhone=" 		. urlencode( $order->billing_phone );
				$debug_data  	.= "&DeliverySurname=" 		. urlencode( apply_filters( 'woocommerce_sagepay_direct_deliverysurname', $DeliverySurname ) );
				$debug_data  	.= "&DeliveryFirstnames=" 	. urlencode( apply_filters( 'woocommerce_sagepay_direct_deliveryfirstname', $DeliveryFirstnames ) );
				$debug_data  	.= "&DeliveryAddress1=" 	. urlencode( apply_filters( 'woocommerce_sagepay_direct_deliveryaddress1', $DeliveryAddress1 ) );
				$debug_data  	.= "&DeliveryAddress2=" 	. urlencode( apply_filters( 'woocommerce_sagepay_direct_deliveryaddress2', $DeliveryAddress2 ) );
				$debug_data  	.= "&DeliveryCity=" 		. urlencode( apply_filters( 'woocommerce_sagepay_direct_deliverycity', $DeliveryCity ) );
				$debug_data  	.= "&DeliveryPostCode=" 	. urlencode( apply_filters( 'woocommerce_sagepay_direct_deliverypostcode', $DeliveryPostCode ) );
				$debug_data  	.= "&DeliveryCountry=" 		. urlencode( apply_filters( 'woocommerce_sagepay_direct_deliverycountry', $DeliveryCountry ) );
				$debug_data  	.= "&DeliveryState=" 		. urlencode( apply_filters( 'woocommerce_sagepay_direct_deliverystate', $DeliveryState ) );
				$debug_data  	.= "&DeliveryPhone=" 		. urlencode( apply_filters( 'woocommerce_sagepay_direct_deliveryphone', $order->billing_phone ) );
  				$debug_data  	.= "&CustomerEMail=" 		. $order->billing_email;
				$debug_data 	.= "&Basket="				. $sage_pay_basket;
  				$debug_data  	.= "&AllowGiftAid=" 		. $this->allowgiftaid;
  				$debug_data  	.= "&ApplyAVSCV2=" 			. $this->cvv;
  				$debug_data  	.= "&ClientIPAddress=" 		. $userip;
  				$debug_data  	.= "&Apply3DSecure=" 		. $this->secure;
  				$debug_data  	.= "&AccountType=" 			. $this->accounttype;
  				$debug_data  	.= "&BillingAgreement=" 	. $this->billingagreement;
  				$debug_data  	.= "&CreateToken=" 			. $this->createtoken;
  				$debug_data  	.= "&StoreToken=" 			. $this->storetoken;
  				$debug_data  	.= "&ReferrerID=" 			. $this->referrerid;
  				$debug_data  	.= "&Website=" 				. site_url();


				$this->log->add( $this->id, __('=============================================', 'woocommerce_sagepayform') );
				$this->log->add( $this->id, __(' ', 'woocommerce_sagepayform') );
				$this->log->add( $this->id, __('SagePay Log', 'woocommerce_sagepayform') );
				$this->log->add( $this->id, __('' .date('d M Y, H:i:s'), 'woocommerce_sagepayform') );
				$this->log->add( $this->id, __(' ', 'woocommerce_sagepayform') );
				$this->log->add( $this->id, __('=============================================', 'woocommerce_sagepayform') );
				$this->log->add( $this->id, __('Sent to SagePay : ', 'woocommerce_sagepayform') );
				$this->log->add( $this->id, str_replace('&',"\n", strip_tags($debug_data) ) );

			}

			/**
			 * Send $data to Sage
			 * @var [type]
			 */
			$sageresult = $this->sagepay_post( $data, $this->purchaseURL );

			if( isset($sageresult['Status']) && $sageresult['Status']!= '' ) {

				$sageresult = $this->process_response ( $sageresult, $order );

				// Store the $VendorTxCode for refunds
				update_post_meta( $order->id, '_RelatedVendorTxCode' , $VendorTxCode );

				// Store the $txtype for RELEASE
				update_post_meta( $order->id, '_SagePayTransactionType', $txtype );

				return array(
                   		'result'	=> $sageresult['result'],
                   		'redirect'	=> $sageresult['redirect']
                   	);


			} else {

               	/**
                 * Payment Failed! - $sageresult['Status'] is empty
              	 */
				$order->add_order_note( __('Payment failed, contact Sage. This transaction returned no status, you should contact Sage.', 'woocommerce_sagepayform') );

				$this->sagepay_message( (__('Payment error, please contact ' . get_bloginfo( 'admin_email' ), 'woocommerce_sagepayform') ) , 'error' );

			} // isset($sageresult['Status']) && $sageresult['Status']!= ''
	
		}

        /**
         * Authorise 3D Secure payments
         * 
         * @param int $order_id
         */
        function authorise_3dsecure( $order_id ) {

        //	if( session_status() == PHP_SESSION_NONE ) {
        //		session_start();
        //	}

        	if ( $this->is_session_started() === FALSE ) session_start();

           	// woocommerce order instance
           	$order = new WC_Order( $order_id );

            if( isset($_SESSION["MD"]) && isset($_SESSION["PAReq"]) && isset($_SESSION["ACSURL"]) && isset($_SESSION["TermURL"]) ) { 

            	$redirect_page = '<!--Non-IFRAME browser support-->' .
                    '<SCRIPT LANGUAGE="Javascript"> function OnLoadEvent() { document.form.submit(); }</SCRIPT>' .
                    '<html><head><title>3D Secure Verification</title></head>' . 
                    '<body OnLoad="OnLoadEvent();">' .
                    '<form name="form" action="'. $_SESSION['ACSURL'] .'" method="post">' .
                    '<input type="hidden" name="PaReq" value="' . $_SESSION['PAReq'] . '"/>' .                
                    '<input type="hidden" name="MD" value="' . $_SESSION['MD'] . '"/>' .
                    '<input type="hidden" name="TermURL" value="' . $_SESSION['TermURL'] . '"/>' .
                    '<NOSCRIPT>' .
                    '<center><p>Please click button below to Authenticate your card</p><input type="submit" value="Go"/></p></center>' .
                    '</NOSCRIPT>' .
                    '</form></body></html>';

                $iframe_page = '<iframe src="' . $this->iframe_3d_redirect . '" name="3diframe" width="100%" height="500px" >' .
                    $redirect_page .
                    '</iframe>';
                    
                echo $iframe_page;
 
                exit;

            } // else

            if ( isset($_POST['MD']) && isset($_POST['PARes']) ) {

				$redirect_url = $this->get_return_url( $order );

				try {

					// set the URL that will be posted to.
					$url 		 = $this->callbackURL;
					$sage_result = array();

					$data  = 'MD=' . $_POST['MD'];
					$data .='&PaRes=' . $_POST['PARes'];

					/**
					 * Send $data to Sage
					 * @var [type]
					 */
					$sageresult = $this->sagepay_post( $data, $url );

					if( isset( $sageresult['Status']) && $sageresult['Status']!= '' && $sageresult['Status']!= 'REJECTED' ) {

						$sageresult = $this->process_response( $sageresult, $order );

					} elseif( isset( $sageresult['Status']) && $sageresult['Status']!= '' && $sageresult['Status']== 'REJECTED' ) {

						$redirect_url = $order->get_checkout_payment_url();
						throw new Exception( __('3D Secure Payment error, please try again.', 'woocommerce_sagepayform')  );

						unset( $_POST['MD'] );
						unset( $_POST['PARes'] );

					} else {

						$redirect_url = $order->get_checkout_payment_url();

						/**
            	    	 * Payment Failed! - $sageresult['Status'] is empty
            	  		 */
						$order->add_order_note( __('Payment failed at 3D Secure, contact Sage. This transaction returned no status, you should contact Sage.', 'woocommerce_sagepayform') );

						throw new Exception( __('3D Secure Payment error, please try again.' ), 'woocommerce_sagepayform');

						unset( $_POST['MD'] );
						unset( $_POST['PARes'] );

					}

				} catch( Exception $e ) {
					wc_add_notice( $e->getMessage(), 'error' );
				}

				wp_redirect( $redirect_url );
				exit;

            }

        } // end auth_3dsecure
	
		/**
		 * Send the info to Sage for processing
		 * https://test.sagepay.com/showpost/showpost.asp
		 */
        function sagepay_post( $data, $url ) {

			$res = wp_remote_post( $url, array(
												'method' 		=> 'POST',
												'timeout' 		=> 45,
												'redirection' 	=> 5,
												'httpversion' 	=> '1.0',
												'blocking' 		=> true,
												'headers' 		=> array('Content-Type'=> 'application/x-www-form-urlencoded'),
												'body' 			=> $data,
												'cookies' 		=> array()
    										)
										);

			if( is_wp_error( $res ) ) {

				/**
				 * Debugging
				 */
   				if ( $this->debug ) {

					$this->log->add( $this->id, __('=============================================', 'woocommerce_sagepayform') );
					$this->log->add( $this->id, __('Remote Post Error : ', 'woocommerce_sagepayform') );
					$this->log->add( $this->id, print_r( $res->get_error_message(), TRUE ) );
					$this->log->add( $this->id, print_r( strip_tags($res['body']), TRUE ) );

				}

			} else {

				/**
				 * Debugging
				 */
   				if ( $this->debug ) {

					$this->log->add( $this->id, __('=============================================', 'woocommerce_sagepayform') );
					$this->log->add( $this->id, __('SagePay Direct Return : ', 'woocommerce_sagepayform') );
					$this->log->add( $this->id, print_r( strip_tags($res['body']), TRUE ) );

				}

				return $this->sageresponse( $res['body'] );

			}

        }

        /**
         * process_response
         *
         * take the reponse from Sage and do some magic things.
         * 
         * @param  [type] $sageresult [description]
         * @param  [type] $order      [description]
         * @return [type]             [description]
         */
        function process_response( $sageresult, $order ) {
        	global $woocommerce;

       		switch( strtoupper($sageresult['Status']) ) {
                case 'OK':
               	case 'REGISTERED':
               	case 'AUTHENTICATED':
						
					/**
					 * Successful payment
					 */
					$successful_ordernote = '';

					foreach ( $sageresult as $key => $value ) {
						$successful_ordernote .= $key . ' : ' . $value . "\r\n";
					}

					$order->add_order_note( __('Payment completed', 'woocommerce_sagepayform') . '<br />' . $successful_ordernote );

					update_post_meta( $order->id, '_VPSTxId' , str_replace( array('{','}'),'',$sageresult['VPSTxId'] ) );
					update_post_meta( $order->id, '_SecurityKey' , $sageresult['SecurityKey'] );
					update_post_meta( $order->id, '_TxAuthNo' , $sageresult['TxAuthNo'] );

					update_post_meta( $order->id, '_RelatedVPSTxId' , str_replace( array('{','}'),'',$sageresult['VPSTxId'] ) );
					update_post_meta( $order->id, '_RelatedSecurityKey' , $sageresult['SecurityKey'] );
					update_post_meta( $order->id, '_RelatedTxAuthNo' , $sageresult['TxAuthNo'] );

					update_post_meta( $order->id, '_CV2Result' , $sageresult['CV2Result'] );
					update_post_meta( $order->id, '_3DSecureStatus' , $sageresult['3DSecureStatus'] );

					if ( class_exists('WC_Pre_Orders') && WC_Pre_Orders_Order::order_contains_pre_order( $order->id ) ) {
						// mark order as pre-ordered / reduce order stock
						WC_Pre_Orders_Order::mark_order_as_pre_ordered( $order );
					} else {
						$order->payment_complete( str_replace( array('{','}'),'',$sageresult['VPSTxId'] ) );
					}

					/**
					 * Empty awaiting payment session
					 */
					unset( $_SESSION['order_awaiting_payment'] );
						
					/**
					 * Return thank you redirect
					 */
					$redirect = $order->get_checkout_order_received_url();
                	
                    /**
                     * Return thank you page redirect
                     */
                    $sageresult['result'] 	= 'success';
                    $sageresult['redirect'] = $redirect;

                    return $sageresult;

                break;

                case '3DAUTH':

					$_SESSION['MD'] 			= $sageresult['MD'];
					$_SESSION['ACSURL'] 		= $sageresult['ACSURL'];
					$_SESSION['PAReq'] 			= $sageresult['PAReq'];
					$_SESSION['TermURL'] 		= WC_HTTPS::force_https_url( $this->iframe_3d_callback );
					$_SESSION['TermURL'] 		= ( $this->iframe_3d_callback );
					$_SESSION['Complete3d'] 	= $order->get_checkout_payment_url( true );

                    /**
                     * go to the pay page for 3d securing
                     */
           			$sageresult['result'] 	= 'success';
                    $sageresult['redirect'] = $order->get_checkout_payment_url( true );
                    
                    return $sageresult;
                
                break;

                case 'NOTAUTHED':
                case 'MALFORMED':
                case 'INVALID':
                case 'ERROR':
                	/**
                	 * Payment Failed!
                	 */
					$order->add_order_note( __('Payment failed', 'woocommerce_sagepayform') . '<br />' .
													$sageresult['StatusDetail']
												);

					$this->sagepay_message( (__('Payment error', 'woocommerce_sagepayform') . ': ' . $sageresult['StatusDetail'] ) , 'error' );

				break;

				case 'REJECTED':
                	/**
                	 * Payment Failed!
                	 */
					$order->add_order_note( __('Payment failed, there was a problem with 3D Secure', 'woocommerce_sagepayform') . '<br />' .
													$sageresult['StatusDetail']
												);

					$this->sagepay_message( (__('Payment error, there was a problem with 3D Secure', 'woocommerce_sagepayform') . ': ' . $sageresult['StatusDetail'] ) , 'error' );

					$_SESSION['MD'] 			= $sageresult['MD'];
					$_SESSION['ACSURL'] 		= $sageresult['ACSURL'];
					$_SESSION['PAReq'] 			= $sageresult['PAReq'];
					$_SESSION['TermURL'] 		= WC_HTTPS::force_https_url( $this->iframe_3d_callback );
					$_SESSION['TermURL'] 		= ( $this->iframe_3d_callback );
					$_SESSION['Complete3d'] 	= $order->get_checkout_payment_url( true );

                    /**
                     * go to the pay page for 3d securing
                     */
           			$sageresult['result'] 	= 'success';
                    $sageresult['redirect'] = $order->get_checkout_payment_url( true );

                    return $sageresult;

				break;

                default:

                	/**
                	 * Payment Failed!
                	 */
					$order->add_order_note( __('Payment failed, contact Sage. This transaction returned no status, you should contact Sage. ' . $sageresult['StatusDetail'], 'woocommerce_sagepayform') );

					$this->sagepay_message( (__('Payment error, please contact ' . get_bloginfo( 'admin_email' ), 'woocommerce_sagepayform') ) , 'error' );

				break;

			}

        }

		/**
		 * sagepay_message
		 * 
		 * return checkout messages / errors
		 * 
		 * @param  [type] $message [description]
		 * @param  [type] $type    [description]
		 * @return [type]          [description]
		 */
		function sagepay_message( $message, $type ) {

			if ( function_exists( 'wc_add_notice' ) ) {
				return wc_add_notice( $message, $type );
			} else {
				return WC()->add_error( $e->getMessage() );
			}

		}

		/**
		 * sageresponse
		 *
		 * take response from Sage and process it into an array
		 * 
		 * @param  [type] $array [description]
		 * @return [type]        [description]
		 */
		function sageresponse( $array ) {

			$response = array();

            $results  = preg_split( '/$\R?^/m', $array );

            foreach( $results as $result ){ 

            	$value = explode( '=', $result, 2 );
                $response[trim($value[0])] = trim($value[1]);

            }

            return $response;

		}

	    /**
		 * Credit Card Fields.
		 *
		 * Core credit card form which gateways can used if needed.
		 */
    	function sagepay_credit_card_form() {

			wp_enqueue_script( 'wc-credit-card-form' );

			$fields = array(
				'card-number-field' => '<p class="form-row form-row-wide">
					<label for="' . $this->id . '-card-number">' . __( "Card Number", 'woocommerce' ) . ' <span class="required">*</span></label>
					<input id="' . $this->id . '-card-number" class="input-text wc-credit-card-form-card-number" type="text" maxlength="20" autocomplete="off" placeholder="•••• •••• •••• ••••" name="' . $this->id . '-card-number" />
				</p>',
				'card-expiry-field' => '<p class="form-row form-row-first">
					<label for="' . $this->id . '-card-expiry">' . __( "Expiry (MM/YY)", 'woocommerce' ) . ' <span class="required">*</span></label>
					<input id="' . $this->id . '-card-expiry" class="input-text wc-credit-card-form-card-expiry" type="text" autocomplete="off" placeholder="MM / YY" name="' . $this->id . '-card-expiry" />
				</p>',
				'card-cvc-field' => '<p class="form-row form-row-last">
					<label for="' . $this->id . '-card-cvc">' . __( "Card Code", 'woocommerce' ) . ' <span class="required">*</span></label>
					<input id="' . $this->id . '-card-cvc" class="input-text wc-credit-card-form-card-cvc" type="text" autocomplete="off" placeholder="CVC" name="' . $this->id . '-card-cvc" />
				</p>'
			);
			?>
			<fieldset id="<?php echo $this->id; ?>-cc-form">
				<?php do_action( 'woocommerce_credit_card_form_before', $this->id ); ?>
				<?php echo $fields['card-number-field']; ?>
				<?php echo $fields['card-expiry-field']; ?>
				<?php echo $fields['card-cvc-field']; ?>
				<?php do_action( 'woocommerce_credit_card_form_after', $this->id ); ?>
				<div class="clear"></div>
			</fieldset>
			<?php
    	}

		function cc_type($cardNumber) {

    		// Strip non-digits from the number
    		$cardNumber = preg_replace('/\D/', '', $cardNumber);

        	switch($cardNumber) {
    			case( preg_match ('/^(5018|5020|5038|6304|6759|676[1-3])/', $cardNumber) >=1 ):
            		return 'Maestro';
            	case( preg_match ('/^4/', $cardNumber) >= 1 ):
            	    return 'Visa';
            	case( preg_match ('/^5[1-5]/', $cardNumber) >= 1 ):
            	    return 'MC';
            	case( preg_match ('/^3[47]/', $cardNumber) >= 1 ):
            	    return 'Amex';
            	case( preg_match ('/^3(?:0[0-5]|[68])/', $cardNumber) >= 1 ):
            	    return 'Diners';
            	case( preg_match ('/^6(?:011|5)/', $cardNumber) >= 1 ):
            	    return 'Discover';
            	case( preg_match ('/^(?:2131|1800|35\d{3})/', $cardNumber) >= 1 ):
            	    return 'JCB';
            	case( preg_match ('/^(6706|6771|6709)/', $cardNumber) >=1 ):
            		return 'Laser';
            	default:
                	return '';
                	break;
        	}

    	}

        /**
         * init_form_fields function.
         *
         * @access public
         * @return void
         */
        function init_form_fields() {	

			include ( WP_PLUGIN_DIR . '/woocommerce-gateway-sagepay-form/includes/sagepay-direct-admin.php' );

		}

		/**
		 * Returns the plugin's url without a trailing slash
		 *
		 * [get_plugin_url description]
		 * @return [type]
		 */
		public function get_plugin_url() {

			return str_replace('/classes','',untrailingslashit( plugins_url( '/', __FILE__ ) ) );

		}

		
		/**
		 * Check if SSL is enabled and notify the user
	 	 */
		function sagepaydirect_ssl_check() {
	     
		     if ( get_option('woocommerce_force_ssl_checkout')=='no' && $this->enabled=='yes' ) {
	     
		     	echo '<div class="error"><p>'.sprintf(__('SagePay Direct is enabled and the <a href="%s">force SSL option</a> is disabled; your checkout is not secure! Please enable SSL and ensure your server has a valid SSL certificate before going live.', 'woocommerce_sagepayform'), admin_url('admin.php?page=woocommerce')).'</p></div>';
	     
	     	}

		}

		/**
		 * Enqueue scipts for the CC form.
		 */
		function sagepaydirect_scripts() {
			wp_enqueue_style( 'wc-sagepaydirect', $this->get_plugin_url().'/assets/css/checkout.css' );

			if ( ! wp_script_is( 'jquery-payment', 'registered' ) )
				wp_register_script( 'jquery-payment', $this->get_plugin_url().'/assets/js/jquery.payment.js', array( 'jquery' ), '1.0.2', true );

			if ( ! wp_script_is( 'wc-credit-card-form', 'registered' ) )
				wp_register_script( 'wc-credit-card-form', $this->get_plugin_url().'/assets/js/credit-card-form.js', array( 'jquery', 'jquery-payment' ), WOOCOMMERCE_VERSION, true );
		}

		/**
		 * [get_icon description] Add selected card icons to payment method label, defaults to Visa/MC/Amex/Discover
		 * @return [type] [description]
		 */
		public function get_icon() {
			global $woocommerce;
		
			if ( get_option('woocommerce_force_ssl_checkout')=='no' ) :
					// use icon provided by filter
					$icon = '<img src="' . esc_url( $this->get_plugin_url() . '/assets/images/payment-icons.png' ) . '" alt="' . esc_attr( $this->title ) . '" />';			
			else :
					// use icon provided by filter
					$icon = '<img src="' . esc_url( WC_HTTPS::force_https_url( $this->get_plugin_url() . '/assets/images/payment-icons.png' ) ) . '" alt="' . esc_attr( $this->title ) . '" />';		
			endif;

			if ( ! empty( $this->cardtypes ) ) :

				$icon = '';

				if ( get_option('woocommerce_force_ssl_checkout')=='no' ) {

					// display icons for the selected card types
					foreach ( $this->cardtypes as $card_type ) {

						$icon .= '<img src="' . 
									esc_url( $this->get_plugin_url() . '/assets/images/card-' . 
									strtolower( str_replace(' ','-',$card_type) ) . '.png' ) . '" alt="' . 
									esc_attr( strtolower( $card_type ) ) . '" />';
					}

				} else {

					// display icons for the selected card types
					foreach ( $this->cardtypes as $card_type ) {

						$icon .= '<img src="' . 
									esc_url( WC_HTTPS::force_https_url( $this->get_plugin_url() ) . '/assets/images/card-' . 
									strtolower( str_replace(' ','-',$card_type) ) . '.png' ) . '" alt="' . 
									esc_attr( strtolower( $card_type ) ) . '" />';
					}

				}

			endif;

			return apply_filters( 'woocommerce_gateway_icon', $icon, $this->id );
		}

		/**
		 * SagePay Direct Refund Processing
		 * @param  Varien_Object $payment [description]
		 * @param  [type]        $amount  [description]
		 * @return [type]                 [description]
		 */
    	function process_refund( $order_id, $amount = NULL, $reason = '' ) {

    		$order 			= new WC_Order( $order_id );

			$VendorTxCode 	= 'Refund-' . $order_id . '-' . time();

            // SAGE Line 50 Fix
            $VendorTxCode 	= str_replace( 'order_', '', $VendorTxCode );

			// New API Request for cancellations
			$api_request 	 = 'VPSProtocol=' . urlencode( $this->vpsprotocol );
			$api_request 	.= '&TxType=REFUND';
			$api_request   	.= '&Vendor=' . urlencode( $this->vendor );
			$api_request 	.= '&VendorTxCode=' . $VendorTxCode;
			$api_request   	.= '&Amount=' . urlencode( $amount );
			$api_request 	.= '&Currency=' . get_post_meta( $order_id, '_order_currency', true );
			$api_request 	.= '&Description=Refund for order ' . $order_id;
			$api_request	.= '&RelatedVPSTxId=' . get_post_meta( $order_id, '_VPSTxId', true );
			$api_request	.= '&RelatedVendorTxCode=' . get_post_meta( $order_id, '_VendorTxCode', true );
			$api_request	.= '&RelatedSecurityKey=' . get_post_meta( $order_id, '_SecurityKey', true );
			$api_request	.= '&RelatedTxAuthNo=' . get_post_meta( $order_id, '_TxAuthNo', true );

			$result = $this->sagepay_post( $api_request, $this->refundURL );

			if ( 'OK' != $result['Status'] ) {

					$content = 'There was a problem refunding this payment for order ' . $order_id . '. The Transaction ID is ' . $api_request['RelatedVPSTxId'] . '. The API Request is <pre>' . 
						print_r( $api_request, TRUE ) . '</pre>. SagePay returned the error <pre>' . 
						print_r( $result['StatusDetail'], TRUE ) . '</pre> The full returned array is <pre>' . 
						print_r( $result, TRUE ) . '</pre>. ';
					
					wp_mail( $this->notification ,'SagePay Refund Error ' . $result['Status'] . ' ' . time(), $content );

			} else {

				$refund_ordernote = '';

				foreach ( $result as $key => $value ) {
					$refund_ordernote .= $key . ' : ' . $value . "\r\n";
				}

				$order->add_order_note( __('Refund successful', 'woocommerce_sagepayform') . '<br />' . 
										__('Refund Amount : ', 'woocommerce_sagepayform') . $amount . '<br />' .
										__('Refund Reason : ', 'woocommerce_sagepayform') . $reason . '<br />' .
										__('Full return from SagePay', 'woocommerce_sagepayform') . '<br />' .
										$refund_ordernote );
		
				return true;
		
			}

    	} // process_refund

		/**
		 * process scheduled subscription payment
		 */
    	function process_scheduled_subscription_payment( $amount_to_charge, $order, $product_id ) {

    		$order_id = $order->id;

    		$VendorTxCode 	= 'Renewal-' . $order_id . '-' . time();

            // SAGE Line 50 Fix
            $VendorTxCode 	= str_replace( 'order_', '', $VendorTxCode );

			// New API Request for repeat
			$api_request 	 = 'VPSProtocol=' . urlencode( $this->vpsprotocol );
			$api_request 	.= '&TxType=REPEAT';
			$api_request   	.= '&Vendor=' . urlencode( $this->vendor );
			$api_request 	.= '&VendorTxCode=' . $VendorTxCode;
			$api_request   	.= '&Amount=' . urlencode( $amount_to_charge );
			$api_request 	.= '&Currency=' . get_post_meta( $order_id, '_order_currency', true );
			$api_request 	.= '&Description=Repeat payment for order ' . $order_id;
			$api_request	.= '&RelatedVPSTxId=' . get_post_meta( $order_id, '_RelatedVPSTxId', true );
			$api_request	.= '&RelatedVendorTxCode=' . get_post_meta( $order_id, '_RelatedVendorTxCode', true );
			$api_request	.= '&RelatedSecurityKey=' . get_post_meta( $order_id, '_RelatedSecurityKey', true );
			$api_request	.= '&RelatedTxAuthNo=' . get_post_meta( $order_id, '_RelatedTxAuthNo', true );

			$result = $this->sagepay_post( $api_request, $this->repeatURL );

			if ( 'OK' != $result['Status'] ) {

				$content = 'There was a problem renewing this payment for order ' . $order_id . '. The Transaction ID is ' . $api_request['RelatedVPSTxId'] . '. The API Request is <pre>' . 
					print_r( $api_request, TRUE ) . '</pre>. SagePay returned the error <pre>' . 
					print_r( $result['StatusDetail'], TRUE ) . '</pre> The full returned array is <pre>' . 
					print_r( $result, TRUE ) . '</pre>. ';
					
				wp_mail( $this->notification ,'SagePay Renewal Error ' . $result['Status'] . ' ' . time(), $content );

				WC_Subscriptions_Manager::process_subscription_payment_failure_on_order( $order, $product_id );

			} else {

				WC_Subscriptions_Manager::process_subscription_payments_on_order( $order );

				/**
				 * Update the renewal order with the transaction info from Sage 
				 * and update the original order with the renewal order 
				 */
				$renewal_orders = WC_Subscriptions_Renewal_Order::get_renewal_orders( $order_id );
				$renewal_order  = end( array_values($renewal_orders) );
				$this->add_notes_scheduled_subscription_order( $result, $renewal_order, $order_id, $VendorTxCode );
			}

    	} // process scheduled subscription payment

		/**
		 * Update the renewal order with the transaction info from Sage 
		 * and update the original order with the renewal order transaction information.
		 */
    	private function add_notes_scheduled_subscription_order( $sageresult, $order_id, $original_order_id, $VendorTxCode ) {

    		$order = new WC_Order( $order_id );

    		/**
			 * Successful payment
			 */
			$successful_ordernote = '';

			foreach ( $sageresult as $key => $value ) {
				$successful_ordernote .= $key . ' : ' . $value . "\r\n";
			}

			$order->add_order_note( __('Payment completed', 'woocommerce_sagepayform') . '<br />' . $successful_ordernote );

			update_post_meta( $order_id, '_transaction_id', str_replace( array('{','}'),'',$sageresult['VPSTxId'] ) );
			update_post_meta( $order_id, '_VPSTxId' , str_replace( array('{','}'),'',$sageresult['VPSTxId'] ) );
			update_post_meta( $order_id, '_SecurityKey' , $sageresult['SecurityKey'] );
			update_post_meta( $order_id, '_TxAuthNo' , $sageresult['TxAuthNo'] );
			delete_post_meta( $order_id, '_CV2Result' );
			delete_post_meta( $order_id, '_3DSecureStatus' );

			// update the original order with the renewal order transaction information
			update_post_meta( $original_order_id, '_RelatedVPSTxId' , str_replace( array('{','}'),'',$sageresult['VPSTxId'] ) );
			update_post_meta( $original_order_id, '_RelatedVendorTxCode' , $VendorTxCode );
			update_post_meta( $original_order_id, '_RelatedSecurityKey' , $sageresult['SecurityKey'] );
			update_post_meta( $original_order_id, '_RelatedTxAuthNo' , $sageresult['TxAuthNo'] );

    	}

    	/**
		 * Don't transfer Stripe customer/token meta when creating a parent renewal order.
		 *
		 * @access public
		 * @param array $order_meta_query MySQL query for pulling the metadata
		 * @param int $original_order_id Post ID of the order being used to purchased the subscription being renewed
		 * @param int $renewal_order_id Post ID of the order created for renewing the subscription
		 * @param string $new_order_role The role the renewal order is taking, one of 'parent' or 'child'
		 * @return void
		 */
		public function remove_renewal_order_meta( $order_meta_query, $original_order_id, $renewal_order_id, $new_order_role ) {
			if ( 'parent' == $new_order_role ) {
				$order_meta_query .= " AND `meta_key` NOT IN ( '_VPSTxId', '_SecurityKey', '_TxAuthNo', '_RelatedVPSTxId', '_RelatedSecurityKey', '_RelatedTxAuthNo', '_CV2Result', '_3DSecureStatus' ) ";
			}
			return $order_meta_query;
		}

		/**
		 * Update the customer_id for a subscription after using Stripe to complete a payment to make up for
		 * an automatic renewal payment which previously failed.
		 *
		 * @access public
		 * @param WC_Order $original_order The original order in which the subscription was purchased.
		 * @param WC_Order $renewal_order The order which recorded the successful payment (to make up for the failed automatic payment).
		 * @param string $subscription_key A subscription key of the form created by @see WC_Subscriptions_Manager::get_subscription_key()
		 * @return void
		 */
		public function update_failing_payment_method( $original_order, $renewal_order, $subscription_key ) {
			$new_customer_id = get_post_meta( $renewal_order->id, '_stripe_customer_id', true );
			$new_card_id     = get_post_meta( $renewal_order->id, '_stripe_card_id', true );
			update_post_meta( $original_order->id, '_stripe_customer_id', $new_customer_id );
			update_post_meta( $original_order->id, '_stripe_card_id', $new_card_id );
		}

		/**
		 * Render the payment method used for a subscription in the "My Subscriptions" table
		 *
		 * @since 1.7.5
		 * @param string $payment_method_to_display the default payment method text to display
		 * @param array $subscription_details the subscription details
		 * @param WC_Order $order the order containing the subscription
		 * @return string the subscription payment method
		 */
		public function maybe_render_subscription_payment_method( $payment_method_to_display, $subscription_details, WC_Order $order ) {
			// bail for other payment methods
			if ( $this->id !== $order->recurring_payment_method || ! $order->customer_user ) {
				return $payment_method_to_display;
			}

			$user_id         = $order->customer_user;
			$stripe_customer = get_user_meta( $user_id, '_stripe_customer_id', true );

			// If we couldn't find a Stripe customer linked to the account, fallback to the order meta data.
			if ( ! $stripe_customer || ! is_string( $stripe_customer ) ) {
				$stripe_customer = get_post_meta( $order->id, '_stripe_customer_id', true );
			}

			// Card specified?
			$stripe_card = get_post_meta( $order->id, '_stripe_card_id', true );

			// Get cards from API
			// $cards       = $this->get_saved_cards( $stripe_customer );

			if ( $cards ) {
				$found_card = false;
				foreach ( $cards as $card ) {
					if ( $card->id === $stripe_card ) {
						$found_card                = true;
						$payment_method_to_display = sprintf( __( 'Via %s card ending in %s', 'woocommerce-gateway-stripe' ), ( isset( $card->type ) ? $card->type : $card->brand ), $card->last4 );
						break;
					}
				}	
				if ( ! $found_card ) {
					$payment_method_to_display = sprintf( __( 'Via %s card ending in %s', 'woocommerce-gateway-stripe' ), ( isset( $cards[0]->type ) ? $cards[0]->type : $cards[0]->brand ), $cards[0]->last4 );
				}
			}

			return $payment_method_to_display;
		}

    	/**
    	 * [process_pre_order_payments description]
    	 * @return [type] [description]
    	 */
    	function process_pre_order_release_payment( $order ) {

			// the total amount to charge is the the order's total
			$amount_to_charge = $order->get_total();

			$VendorTxCode 	 = 'Authorise-' . $order->id . '-' . time();

			// New API Request for AUTHORISE
			$api_request 	 = 'VPSProtocol=' . urlencode( $this->vpsprotocol );
			$api_request 	.= '&TxType=AUTHORISE';
			$api_request   	.= '&Vendor=' . urlencode( $this->vendor );
			$api_request 	.= '&VendorTxCode=' . $VendorTxCode;
			$api_request   	.= '&Amount=' . urlencode( $amount_to_charge );
			$api_request 	.= '&Currency=' . get_post_meta( $order->id, '_order_currency', true );
			$api_request 	.= '&Description=Payment for pre-order ' . $order->id;
			$api_request	.= '&RelatedVPSTxId=' . get_post_meta( $order->id, '_VPSTxId', true );
			$api_request	.= '&RelatedVendorTxCode=' . get_post_meta( $order->id, '_VendorTxCode', true );
			$api_request	.= '&RelatedSecurityKey=' . get_post_meta( $order->id, '_SecurityKey', true );
			$api_request	.= '&RelatedTxAuthNo=' . get_post_meta( $order->id, '_TxAuthNo', true );

			$result = $this->sagepay_post( $api_request, $this->authoriseURL );

			if ( 'OK' != $result['Status'] ) {

				$content = 'There was a problem AUTHORISE this payment for order ' . $order->id . '. The Transaction ID is ' . $api_request['RelatedVPSTxId'] . '. The API Request is <pre>' . 
					print_r( $api_request, TRUE ) . '</pre>. SagePay returned the error <pre>' . 
					print_r( $result['StatusDetail'], TRUE ) . '</pre> The full returned array is <pre>' . 
					print_r( $result, TRUE ) . '</pre>. ';
					
				wp_mail( $this->notification ,'SagePay AUTHORISE Error ' . $result['Status'] . ' ' . time(), $content );

				/**
				 * failed payment
				 */
				$ordernote = '';

				foreach ( $result as $key => $value ) {
					$ordernote .= $key . ' : ' . $value . "\r\n";
				}

				$order->add_order_note( __('Payment failed', 'woocommerce_sagepayform') . '<br />' . $ordernote );

			} else {
					
				/**
				 * Successful payment
				 */
				$successful_ordernote = '';

				foreach ( $result as $key => $value ) {
					$successful_ordernote .= $key . ' : ' . $value . "\r\n";
				}

				$order->add_order_note( __('Payment completed', 'woocommerce_sagepayform') . '<br />' . $successful_ordernote );

				update_post_meta( $order->id, '_RelatedVPSTxId' , str_replace( array('{','}'),'',$result['VPSTxId'] ) );
				update_post_meta( $order->id, '_RelatedSecurityKey' , $result['SecurityKey'] );
				update_post_meta( $order->id, '_RelatedTxAuthNo' , $result['TxAuthNo'] );
				update_post_meta( $order->id, '_CV2Result' , $result['CV2Result'] );
				update_post_meta( $order->id, '_3DSecureStatus' , $result['3DSecureStatus'] );
				
				// complete the order
				$order->payment_complete( str_replace( array('{','}'),'',$result['VPSTxId'] ) );
		
			}

    	}

		/**
		 * @return bool
		 */
		function is_session_started() {
    		
    		if ( php_sapi_name() !== 'cli' ) {
        		
        		if ( version_compare(phpversion(), '5.4.0', '>=') ) {
            		return session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
        		} else {
            		return session_id() === '' ? FALSE : TRUE;
        		}
    		
    		}
    		
    		return FALSE;
		
		}

	} // END CLASS

