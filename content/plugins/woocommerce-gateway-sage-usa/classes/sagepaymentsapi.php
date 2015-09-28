<?php

    /**
     * WC_Gateway_SagePaymentUSA_API class.
     *
     * @extends WC_Payment_Gateway
     */
    class WC_Gateway_SagePaymentUSA_API extends WC_Payment_Gateway {

        /**
         * __construct function.
         *
         * @access public
         * @return void
         */
        public function __construct() {
            $this->id                   		= 'sagepaymentsusaapi';
            $this->method_title         		= __( 'Sage Payment Solutions API', 'woocommerce-spusa' );
            $this->method_description   		= __( 'Activate Sage Payment Solutions API and let your customers pay via Sage Payment Solutions. For help and trouble shooting visit the <a href="'.SPUSADOCSURL.'" target="_blank"> plugin docs page</a> ', 'woocommerce-spusa' );
            $this->icon                 		= apply_filters( 'wc_sagepaymentsusaapi_icon', '' );
            $this->has_fields           		= true;
            $this->liveurl              		= 'https://gateway.sagepayments.net/cgi-bin/eftbankcard.dll?transaction';
            $this->default_order_button_text	= __( 'Pay Securely with Sage', 'woocommerce-spusa' );

            // Load the form fields
            $this->init_form_fields();

            // Load the settings.
            $this->init_settings();

            // Get setting values
            $this->enabled			= $this->settings['enabled'];
            $this->title			= $this->settings['title'];
            $this->description		= $this->settings['description'];
            $this->status			= $this->settings['status'];
            $this->M_id				= $this->settings['M_id'];
			$this->M_key			= $this->settings['M_key'];
			$this->M_id_testing		= $this->settings['M_id_testing'];
			$this->M_key_testing	= $this->settings['M_key_testing'];
			$this->T_code			= $this->settings['T_code'];
			$this->cvv				= $this->settings['cvv'];
			$this->cardtypes		= $this->settings['cardtypes'];
			$this->debug			= isset( $this->settings['debug'] ) && $this->settings['debug'] == 'yes' ? true : false;
			$this->order_button_text= isset( $this->settings['order_button_text'] ) ? $this->settings['order_button_text'] : $this->default_order_button_text;

            // Hooks
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

			// API
            add_action( 'woocommerce_api_wc_gateway_sagepaymentsusaapi', array( $this, 'check_sagepay_response' ) );

            add_action( 'valid-sagepaymentsusaapi-request', array( $this, 'successful_request' ) );
            add_action( 'woocommerce_receipt_sagepaymentsusaapi', array( $this, 'receipt_page' ) );
			
			// SSL Check
			add_action( 'admin_notices', array( $this,'sagepaymentsusaapi_ssl_check') );

			// Scripts
			add_action( 'wp_enqueue_scripts', array( $this, 'sagepaymentsusaapi_scripts' ) );

			/**
			 * What thi gateway supports
			 * @var array
			 */
			$this->supports = array(
  									'products'
									);
				
        } // END __construct

        /**
         * init_form_fields function.
         *
         * @access public
         * @return void
         */
        function init_form_fields() {
			$this->form_fields = array(
				'enabled'           => array(
				    'title'         => __( 'Enable/Disable', 'woocommerce-spusa' ),
				    'label'         => __( 'Enable Sage Payment Solutions API', 'woocommerce-spusa' ),
				    'type'          => 'checkbox',
				    'description'   => '',
				    'default'       => 'no'
				),
				'title'             => array(
				    'title'         => __( 'Title', 'woocommerce-spusa' ),
				    'type'          => 'text',
				    'description'   => __( 'This controls the title which the user sees during checkout.', 'woocommerce-spusa' ),
				    'default'       => __( 'Credit Card via Sage', 'woocommerce-spusa' )
				),
				'description'       => array(
				    'title'         => __( 'Description', 'woocommerce-spusa' ),
				    'type'          => 'textarea',
				    'description'   => __( 'This controls the description which the user sees during checkout.', 'woocommerce-spusa' ),
				    'default'       => 'Pay via Credit / Debit Card with Sage secure card processing.'
				),
				'status'            => array(
				    'title'         => __( 'Status', 'woocommerce-spusa' ),
				    'type'          => 'select',
				    'options'       => array('live'=>'Live','testing'=>'Testing'),
				    'description'   => __( 'Set Sage Bankcard Live/Testing Status.', 'woocommerce-spusa' ),
				    'default'       => 'testing'
				),
				'M_id_testing'      => array(
				    'title'         => __( 'Testing Merchant Identification Number', 'woocommerce-spusa' ),
				    'type'          => 'text',
				    'description'   => __( '12 Digit Merchant Identification Number (Virtual Terminal ID). This should have been supplied by Sage when you created your account.', 'woocommerce-spusa' ),
				    'default'       => ''
				),
				'M_key_testing'     => array(
				    'title'         => __( 'Testing Merchant Key', 'woocommerce-spusa' ),
				    'type'          => 'text',
				    'description'   => __( '12 Digit Merchant Key Required for Gateway Access. This should have been supplied by Sage when you created your account.', 'woocommerce-spusa' ),
				    'default'       => ''
				),
				'M_id'            => array(
				    'title'         => __( 'Live Merchant Identification Number', 'woocommerce-spusa' ),
				    'type'          => 'text',
				    'description'   => __( '12 Digit Merchant Identification Number (Virtual Terminal ID). This should have been supplied by Sage when you created your account.', 'woocommerce-spusa' ),
				    'default'       => ''
				),
				'M_key'            => array(
				    'title'         => __( 'Live Merchant Key', 'woocommerce-spusa' ),
				    'type'          => 'text',
				    'description'   => __( '12 Digit Merchant Key Required for Gateway Access. This should have been supplied by Sage when you created your account.', 'woocommerce-spusa' ),
				    'default'       => ''
				),
				'T_code'            => array(
				    'title'         => __( 'Transaction Processing Code', 'woocommerce-spusa' ),
				    'type'          => 'select',
				    'options'       => array( '01'=>'Sale','02'=>'AuthOnly' ),
				    'description'   => __( 'Set the processing code, leave this set to sale unless you know what you are doing.', 'woocommerce-spusa' ),
				    'default'       => '01'
				),
				'cardtypes'			=> array(
					'title' 		=> __( 'Accepted Cards', 'woocommerce-spusa' ), 
					'type' 			=> 'multiselect',
					'class'			=> 'chosen_select',
					'css'         => 'width: 350px;', 
					'description' 	=> __( 'Select which card types to accept.', 'woocommerce-spusa' ), 
					'default' 		=> '',
					'options' 		=> array(
							'MasterCard'		=> 'MasterCard', 
							'Visa'				=> 'Visa',
							'Discover'			=> 'Discover',
							'American Express' 	=> 'American Express'
						),
				),		
				'cvv' 				=> array(
					'title' 		=> __( 'CVV', 'woocommerce-spusa' ), 
					'label' 		=> __( 'Require customer to enter credit card CVV code', 'woocommerce-spusa' ), 
					'type' 			=> 'checkbox', 
					'description' 	=> __( '', 'woocommerce-spusa' ), 
					'default' 		=> 'no'
				),
				'order_button_text'	=> array(
					'title' 		=> __( 'Checkout Pay Button Text', 'woocommerce-spusa' ),
					'type' 			=> 'text',
					'description' 	=> __( 'This controls the pay button text shown during checkout.', 'woocommerce-spusa' ),
					'default' 		=> $this->default_order_button_text
				),

				'debug'     		=> array(
				    'title'         => __( 'Debug Mode', 'woocommerce_cardstream' ),
				    'type'          => 'checkbox',
				    'options'       => array('no'=>'No','yes'=>'Yes'),
				    'label'     	=> __( 'Enable Debug Mode', 'woocommerce-spusa' ),
				    'default'       => 'no'
				)
			);			
		}

		/**
		 * Returns the plugin's url without a trailing slash
		 */
		public function get_plugin_url() {

			return str_replace('/classes','',untrailingslashit( plugins_url( '/', __FILE__ ) ) );
		}
		
		/**
		 * Check if SSL is enabled and notify the user
	 	 **/
		function sagepaymentsusaapi_ssl_check() {
	     
		     if ( get_option('woocommerce_force_ssl_checkout')=='no' && $this->enabled=='yes' ) :
	     
		     	echo '<div class="error"><p>'.sprintf(__('Sage Payment Solutions API is enabled and the <a href="%s">force SSL option</a> is disabled; your checkout is not secure! Please enable SSL and ensure your server has a valid SSL certificate.', 'woocommerce-spusa'), admin_url('admin.php?page=woocommerce')).'</p></div>';
	     
	     	endif;
		}

		/**
		 * Enqueue Scipts
		 */
		function sagepaymentsusaapi_scripts() {
			wp_enqueue_style( 'wc-spusa', $this->get_plugin_url().'/assets/css/checkout.css' );

			if ( ! wp_script_is( 'jquery-payment', 'registered' ) )
				wp_register_script( 'jquery-payment', $this->get_plugin_url().'/assets/js/jquery.payment.js', array( 'jquery' ), '1.0.2', true );

			if ( ! wp_script_is( 'wc-credit-card-form', 'registered' ) )
				wp_register_script( 'wc-credit-card-form', $this->get_plugin_url().'/assets/js/credit-card-form.js', array( 'jquery', 'jquery-payment' ), WOOCOMMERCE_VERSION, true );
		}

		/**
		 * Add selected card icons to payment method label, defaults to Visa/MC/Amex/Discover
		 */
		public function get_icon() {
			global $woocommerce;

			$icon = '';

			if ( $this->icon ) :
		
				if ( get_option('woocommerce_force_ssl_checkout')=='no' ) :
					// use icon provided by filter
					$icon = '<img src="' . esc_url( $this->icon ) . '" alt="' . esc_attr( $this->title ) . '" />';			
				else :
					// use icon provided by filter
					$icon = '<img src="' . esc_url( WC_HTTPS::force_https_url( $this->icon ) ) . '" alt="' . esc_attr( $this->title ) . '" />';		
				endif;

			elseif ( ! empty( $this->cardtypes ) ) :

				if ( get_option('woocommerce_force_ssl_checkout')=='no' ) {

					// display icons for the selected card types
					foreach ( $this->cardtypes as $card_type ) {

						$icon .= '<img src="' . 
									esc_url( $this->get_plugin_url() . '/assets/card-' . 
									strtolower( str_replace(' ','-',$card_type) ) . '.png' ) . '" alt="' . 
									esc_attr( strtolower( $card_type ) ) . '" />';
					}

				} else {

					// display icons for the selected card types
					foreach ( $this->cardtypes as $card_type ) {

						$icon .= '<img src="' . 
									esc_url( WC_HTTPS::force_https_url( $this->get_plugin_url() ) . '/assets/card-' . 
									strtolower( str_replace(' ','-',$card_type) ) . '.png' ) . '" alt="' . 
									esc_attr( strtolower( $card_type ) ) . '" />';
					}

				}

			endif;

			return apply_filters( 'woocommerce_gateway_icon', $icon, $this->id );
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

		/**
    	 * Payment form on checkout page
    	 */
		public function payment_fields() {
			global $woocommerce;

        	if ( get_option('woocommerce_force_ssl_checkout')=='no' && $this->status=='live' ) :
		     	echo '<div class="error"><p>'.sprintf(__('Sage Payment Solutions API is enabled and the force SSL option is disabled; your checkout is not secure! Please enable SSL and ensure your server has a valid SSL certificate.', 'woocommerce-spusa') ).'<br /></p></div>';
			endif;

			if ($this->description) { ?><p><?php echo $this->description; ?></p><?php }

			if ( method_exists( $this, 'credit_card_form' ) ) {
				$this->credit_card_form();
			} else {
				$this->sagepay_credit_card_form();
			}

		}

	/**
     * Validate the payment form
     */
		function validate_fields() {
			global $woocommerce;

			try {

				$card_number 		= isset($_POST[$this->id . '-card-number']) ? woocommerce_clean($_POST[$this->id . '-card-number']) : '';
				$card_cvc 			= isset($_POST[$this->id . '-card-cvc']) ? woocommerce_clean($_POST[$this->id . '-card-cvc']) : '';
				$card_expiry		= isset($_POST[$this->id . '-card-expiry']) ? woocommerce_clean($_POST[$this->id . '-card-expiry']) : '';

				// Format values
				$card_number    = str_replace( array( ' ', '-' ), '', $card_number );
				$card_expiry    = array_map( 'trim', explode( '/', $card_expiry ) );
				$card_exp_month = str_pad( $card_expiry[0], 2, "0", STR_PAD_LEFT );
				$card_exp_year  = $card_expiry[1];

				// Validate values
				if ( ! ctype_digit( $card_cvc ) ) {
					throw new Exception( __( 'Card security code is invalid (only digits are allowed)', 'woocommerce-spusa' ) );
				}

				if (
					! ctype_digit( $card_exp_month ) ||
					! ctype_digit( $card_exp_year ) ||
					$card_exp_month > 12 ||
					$card_exp_month < 1 ||
					$card_exp_year < date('y')
				) {
					throw new Exception( __( 'Card expiration date is invalid', 'woocommerce-spusa' ) );
				}	

				if ( empty( $card_number ) || ! ctype_digit( $card_number ) ) {
					throw new Exception( __( 'Card number is invalid', 'woocommerce-spusa' ) );
				}

				return true;

			} catch( Exception $e ) {
				if ( function_exists( 'wc_add_notice' ) ) {
					wc_add_notice( $e->getMessage(), 'error' );
				} else {
					$message = ( $e->getMessage() );
						wc_add_notice( $message, 'error' );
				}
				return false;
			}
		}

		/**
		 * Process the payment and return the result
		 **/
		function process_payment( $order_id ) {
			global $woocommerce;

			$order = new WC_Order( $order_id );

			$card_number 		= isset($_POST[$this->id . '-card-number']) ? woocommerce_clean($_POST[$this->id . '-card-number']) : '';
			$card_cvc 			= isset($_POST[$this->id . '-card-cvc']) ? woocommerce_clean($_POST[$this->id . '-card-cvc']) : '';
			$card_expiry		= isset($_POST[$this->id . '-card-expiry']) ? woocommerce_clean($_POST[$this->id . '-card-expiry']) : '';

			// Format values
			$card_number    = str_replace( array( ' ', '-' ), '', $card_number );
			$card_expiry    = array_map( 'trim', explode( '/', $card_expiry ) );
			$card_exp_month = str_pad( $card_expiry[0], 2, "0", STR_PAD_LEFT );
			$card_exp_year  = $card_expiry[1];
			
			// set the URL that will be posted to.
			$eftsecure_url = "https://gateway.sagepayments.net/cgi-bin/eftbankcard.dll?transaction";

			if ( !$order->billing_state || $order->billing_state == '' ) :
				$state = $order->billing_city;
			else :
				$state = $order->billing_state;
			endif;
			 
			// make your query.
			if ( $this->status == 'live' ) :
				$data  = "m_id=" 		. 	$this->M_id;
				$data .= "&m_key=" 		. 	$this->M_key;
			else :
				$data  = "m_id=" 		. 	$this->M_id_testing;
				$data .= "&m_key=" 		. 	$this->M_key_testing;			
			endif;
			$data .= "&T_ordernum=" 	. 	urlencode( $order_id );
			$data .= "&T_amt=" 			. 	urlencode( $order->order_total ); 
			$data .= "&C_name=" 		. 	urlencode( $order->billing_first_name . ' ' . $order->billing_last_name );
			$data .= "&C_address=" 		. 	urlencode( $order->billing_address_1 );
			$data .= "&C_state=" 		. 	urlencode( $state );
			$data .= "&C_city=" 		. 	urlencode( $order->billing_city );
			$data .= "&C_zip=" 			. 	urlencode( $order->billing_postcode );
			$data .= "&C_country=" 		. 	urlencode( $order->billing_country );
			$data .= "&C_email=" 		. 	urlencode( $order->billing_email );
			$data .= "&C_cardnumber=" 	. 	urlencode( $card_number );
			$data .= "&C_exp=" 			. 	urlencode( $card_exp_month . $card_exp_year );
			$data .= "&C_cvv=" 			. 	urlencode( $card_cvc );
			$data .= "&T_code=" 		. 	urlencode( $this->T_code );
			
			/**
			 * Send the info to Sage for processing, use wp_remote_post cos Mike Jolley says so!
			 */
			$res = wp_remote_post( $eftsecure_url, array(
												'method' 		=> 'POST',
												'timeout' 		=> 45,
												'redirection' 	=> 5,
												'httpversion' 	=> '1.0',
												'blocking' 		=> true,
												'headers' 		=> array(),
												'body' 			=> $data,
												'cookies' 		=> array()
    										)
										);

			if( is_wp_error( $res ) ) {
   				$message = (__('Internal error', 'woocommerce-spusa') . '');
   					wc_add_notice( $message, 'error' );
   				if ( $this->debug == true ) {
   					$message = (__('Debugging is on', 'woocommerce-spusa') . '');
   						wc_add_notice( $message, 'error' );
   					$message = (__('Error message : ', 'woocommerce-spusa') . '');
   						wc_add_notice( $message, 'error' );

   					$error_string = $res->get_error_message();
   					$message = ( '<p>' . $error_string . '</p>' );
   						wc_add_notice( $message, 'error' );
   				}

			} else {
 
				/**
				 * Retreive response
				 */
				if ( $res['body'][1] == 'A' ) {
					
					/**
					 * Successful payment
					 */
					$order->add_order_note( __('Payment completed', 'woocommerce-spusa') . '<br />
												(Approval Code: ' . substr($res['body'], 2, 6) . ')<br />
												Approval Msg: ' .substr($res['body'], 8, 32).'<br />
												Reference: ' . substr($res['body'], 46, 10) );
					
					$order->payment_complete( substr($res['body'], 46, 10) );
		
					$woocommerce->cart->empty_cart();

					/**
					 * Empty awaiting payment session
					 */
					unset($_SESSION['order_awaiting_payment']);
						
					/**
					 * Return thank you redirect
					 */
					if ( method_exists( $order, 'get_checkout_order_received_url' ) ) {
                        $redirect = $order->get_checkout_order_received_url();
                	} else {
                        $redirect = add_query_arg( 'key', $order->order_key, add_query_arg( 'order', $order->id, get_permalink( get_option( 'woocommerce_thanks_page_id' ) ) ) );
                	}

                    // Return thank you page redirect
                    return array(
                        'result'	=> 'success',
                        'redirect'	=> $redirect
                    );
	
				} else {
					
					$cancelNote = __('Payment failed', 'woocommerce-spusa') . 
									' (Response Code: ' . substr($res['body'], 2, 6) . '). ' . 
									__('Payment was rejected due to an error', 'woocommerce-spusa') . 
									': "' . substr($res['body'], 8, 32) . '". ';
					$order->add_order_note( $cancelNote );
					$message = (__('Payment error', 'woocommerce-spusa') . ': ' . substr($res['body'], 8, 32) . '');
						wc_add_notice( $message, 'error' );

					if ( $this->debug == true ) {
   						$message = (__('Debugging is on', 'woocommerce-spusa') . '');
   							wc_add_notice( $message, 'error' );

   						$message = ( '<p>' . 'XXXX-XXXX-XXXX-'.substr( $_POST[$this->id . '-card-number'],-4 ) . '<br />' .
   													$_POST[$this->id . '-card-cvc'] . '<br />' .
   													$_POST[$this->id . '-card-expiry'] );
   														wc_add_notice( $message, 'error' );
   						$message = ( print_r( str_replace('&','<br />',$data), TRUE ) );
   							wc_add_notice( $message, 'error' );
   					}

				}
			
			}
	
		} 

		// Process Payment
		public function process_refund( $order_id, $amount = NULL, $reason = '' ) {
  			// Do your refund here. Refund $amount for the order with ID $order_id
			global $woocommerce;

			$order = new WC_Order( $order_id );
			
			// set the URL that will be posted to.
			$eftsecure_url = "https://gateway.sagepayments.net/cgi-bin/eftbankcard.dll?transaction";

			if ( !$order->billing_state || $order->billing_state == '' ) :
				$state = $order->billing_city;
			else :
				$state = $order->billing_state;
			endif;
			 
			// make your query.
			if ( $this->status == 'live' ) :
				$data  = "m_id=" 		. 	$this->M_id;
				$data .= "&m_key=" 		. 	$this->M_key;
			else :
				$data  = "m_id=" 		. 	$this->M_id_testing;
				$data .= "&m_key=" 		. 	$this->M_key_testing;			
			endif;
			$data .= "&T_ordernum=" 	. 	urlencode( $order_id );
			$data .= "&T_amt=" 			. 	urlencode( $amount ); 
			$data .= "&C_name=" 		. 	urlencode( $order->billing_first_name . ' ' . $order->billing_last_name );
			$data .= "&C_address=" 		. 	urlencode( $order->billing_address_1 );
			$data .= "&C_state=" 		. 	urlencode( $state );
			$data .= "&C_city=" 		. 	urlencode( $order->billing_city );
			$data .= "&C_zip=" 			. 	urlencode( $order->billing_postcode );
			$data .= "&C_country=" 		. 	urlencode( $order->billing_country );
			$data .= "&C_email=" 		. 	urlencode( $order->billing_email );
			$data .= "&T_code=" 		. 	urlencode( '06' );
			$data .= "&T_REFERENCE=" 	. 	get_post_meta( $order_id, '_transaction_id', true );
			
			/**
			 * Send the info to Sage for processing
			 */
			$res = wp_remote_post( $eftsecure_url, array(
												'method' 		=> 'POST',
												'timeout' 		=> 45,
												'redirection' 	=> 5,
												'httpversion' 	=> '1.0',
												'blocking' 		=> true,
												'headers' 		=> array(),
												'body' 			=> $data,
												'cookies' 		=> array()
    										)
										);

			if( is_wp_error( $res ) ) {

				// wp_mail( 'andrew@chromeorange.co.uk' , 'Sage Refunds ' . time() , '<pre>' . print_r( $res , TRUE ) . '</pre>' );
				return false;

			} else {
 
				// wp_mail( 'andrew@chromeorange.co.uk' , 'Sage Refunds ' . time() , '<pre>' . print_r( $data , TRUE ) . '</pre>' . '<pre>' . print_r( $res , TRUE ) . '</pre>' );
				return true;

			}  			 
  			
  			
		}

	} // END CLASS
