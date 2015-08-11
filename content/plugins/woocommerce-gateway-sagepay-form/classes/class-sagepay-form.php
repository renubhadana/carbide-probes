<?php

    /**
     * WC_Gateway_Sagepay_Form class.
     *
     * @extends WC_Payment_Gateway
     */
    class WC_Gateway_Sagepay_Form extends WC_Payment_Gateway {

        /**
         * __construct function.
         *
         * @access public
         * @return void
         */
        public function __construct() {

            $this->id                   = 'sagepayform';
            $this->method_title         = __( 'SagePay Form', 'woocommerce_sagepayform' );
            $this->method_description   = $this->sagepay_system_status();
            $this->icon                 = apply_filters( 'wc_sagepayform_icon', '' );
            $this->has_fields           = false;
            $this->liveurl              = 'https://live.sagepay.com/gateway/service/vspform-register.vsp';
            $this->testurl              = 'https://test.sagepay.com/gateway/service/vspform-register.vsp';
            $this->simurl               = 'https://test.sagepay.com/Simulator/VSPFormGateway.asp';

            $this->successurl 			= WC()->api_request_url( get_class( $this ) );

            // Default values
			$this->default_enabled				= 'no';
			$this->default_title 				= __( 'Credit Card via SagePay', 'woocommerce_sagepayform' );
			$this->default_description  		= __( 'Pay via Credit / Debit Card with SagePay secure card processing.', 'woocommerce_sagepayform' );
			$this->default_order_button_text  	= __( 'Pay securely with SagePay', 'woocommerce_sagepayform' );
  			$this->default_status				= 'testing';
  			$this->default_cardtypes			= '';
  			$this->default_protocol				= '3.00';
  			$this->default_vendor				= '';
			$this->default_vendorpwd			= '';
			$this->default_testvendorpwd		= '';
			$this->default_simvendorpwd 		= '';
			$this->default_email				= get_bloginfo('admin_email');
			$this->default_sendemail			= '1';
			$this->default_txtype				= 'PAYMENT';
			$this->default_allow_gift_aid		= 'yes';
			$this->default_apply_avs_cv2		= '0';
			$this->default_apply_3dsecure		= '0';
			$this->default_debug 				= false;
			$this->default_sagelink				= 0;
			$this->default_sagelogo				= 0;

			$this->default_enablesurcharges 	= 'no';
			$this->default_VISAsurcharges   	= '';
			$this->default_DELTAsurcharges  	= '';
			$this->default_UKEsurcharges   		= '';
			$this->default_MCsurcharges   		= '';
			$this->default_MCDEBITsurcharges   	= '';
			$this->default_MAESTROsurcharges   	= '';
			$this->default_AMEXsurcharges   	= '';
			$this->default_DCsurcharges   		= '';
			$this->default_JCBsurcharges   		= '';
			$this->default_LASERsurcharges   	= '';

            // ReferrerID
            $this->referrerid 			= 'F4D0E135-F056-449E-99E0-EC59917923E1';

            // Load the form fields
            $this->init_form_fields();

            // Load the settings.
            $this->init_settings();

            // Get setting values
            $this->enabled         		= isset( $this->settings['enabled'] ) && $this->settings['enabled'] == 'yes' ? 'yes' : $this->default_enabled;
            $this->title 				= isset( $this->settings['title'] ) ? $this->settings['title'] : $this->default_title;
			$this->description  		= isset( $this->settings['description'] ) ? $this->settings['description'] : $this->default_description;
			$this->order_button_text  	= isset( $this->settings['order_button_text'] ) ? $this->settings['order_button_text'] : $this->default_order_button_text;
  			$this->status				= isset( $this->settings['status'] ) ? $this->settings['status'] : $this->default_status;
            $this->cardtypes			= isset( $this->settings['cardtypes'] ) ? $this->settings['cardtypes'] : $this->default_cardtypes;
            $this->protocol 			= isset( $this->settings['protocol'] ) ? $this->settings['protocol'] : $this->default_protocol;
            $this->vendor           	= isset( $this->settings['vendor'] ) ? $this->settings['vendor'] : $this->default_vendor;
            $this->vendorpwd        	= isset( $this->settings['vendorpwd'] ) ? $this->settings['vendorpwd'] : $this->default_vendorpwd;
            $this->testvendorpwd    	= isset( $this->settings['testvendorpwd'] ) ? $this->settings['testvendorpwd'] : $this->default_testvendorpwd;
            $this->simvendorpwd     	= isset( $this->settings['simvendorpwd'] ) ? $this->settings['simvendorpwd'] : $this->default_simvendorpwd;
            $this->email            	= isset( $this->settings['email'] ) ? $this->settings['email'] : $this->default_email;
            $this->sendemail        	= isset( $this->settings['sendemail'] ) ? $this->settings['sendemail'] : $this->default_sendemail;
            $this->txtype           	= isset( $this->settings['txtype'] ) ? $this->settings['txtype'] : $this->default_txtype;
            $this->allow_gift_aid   	= isset( $this->settings['allow_gift_aid'] ) && $this->settings['allow_gift_aid'] == 'yes' ? 1 : 0;
            $this->apply_avs_cv2    	= isset( $this->settings['apply_avs_cv2'] ) ? $this->settings['apply_avs_cv2'] : $this->default_apply_avs_cv2;
            $this->apply_3dsecure   	= isset( $this->settings['apply_3dsecure'] ) ? $this->settings['apply_3dsecure'] : $this->default_apply_3dsecure;
            $this->debug				= isset( $this->settings['debugmode'] ) && $this->settings['debugmode'] == 'yes' ? true : $this->default_debug;
            $this->sagelink				= isset( $this->settings['sagelink'] ) && $this->settings['sagelink'] == 'yes' ? '1' : $this->default_sagelink;
            $this->sagelogo				= isset( $this->settings['sagelogo'] ) && $this->settings['sagelogo'] == 'yes' ? '1' : $this->default_sagelogo;

            $this->enablesurcharges 	= isset( $this->settings['enablesurcharges'] ) && $this->settings['enablesurcharges'] == 'yes' ? 'yes' : $this->default_enablesurcharges;
			$this->VISAsurcharges   	= isset( $this->settings['visasurcharges'] ) ? $this->settings['visasurcharges'] : $this->default_VISAsurcharges;
			$this->DELTAsurcharges  	= isset( $this->settings['visadebitsurcharges'] ) ? $this->settings['visadebitsurcharges'] : $this->default_DELTAsurcharges;
			$this->UKEsurcharges   		= isset( $this->settings['visaelectronsurcharges'] ) ? $this->settings['visaelectronsurcharges'] : $this->default_UKEsurcharges;
			$this->MCsurcharges   		= isset( $this->settings['mcsurcharges'] ) ? $this->settings['mcsurcharges'] : $this->default_MCsurcharges;
			$this->MCDEBITsurcharges   	= isset( $this->settings['mcdebitsurcharges'] ) ? $this->settings['mcdebitsurcharges'] : $this->default_MCDEBITsurcharges;
			$this->MAESTROsurcharges   	= isset( $this->settings['maestrosurcharges'] ) ? $this->settings['maestrosurcharges'] : $this->default_MAESTROsurcharges;
			$this->AMEXsurcharges   	= isset( $this->settings['amexsurcharges'] ) ? $this->settings['amexsurcharges'] : $this->default_AMEXsurcharges;
			$this->DCsurcharges   		= isset( $this->settings['dinerssurcharges'] ) ? $this->settings['dinerssurcharges'] : $this->default_DCsurcharges;
			$this->JCBsurcharges   		= isset( $this->settings['jcbsurcharges'] ) ? $this->settings['jcbsurcharges'] : $this->default_JCBsurcharges;
			$this->LASERsurcharges   	= isset( $this->settings['lasersurcharges'] ) ? $this->settings['lasersurcharges'] : $this->default_LASERsurcharges;

			$this->link 				= 'http://www.sagepay.co.uk/support/online-shoppers/about-sage-pay';
			

			/* 1.6.6 */
			add_action( 'woocommerce_update_options_payment_gateways', array( $this, 'process_admin_options' ) );

			/* 2.0.0 */
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

			/**
			 *  API
			 *  
			 *  woocommerce_api_{lower case class name}
			 */
            add_action( 'woocommerce_api_wc_gateway_sagepay_form', array( $this, 'check_sagepay_response' ) );

            add_action( 'valid_sagepayform_request', array( $this, 'successful_request' ) );
            add_action( 'woocommerce_receipt_sagepayform', array( $this, 'receipt_page' ) );

            // Supports
            $this->supports = array(
            						'products',
							);

            // Logs
			if ( $this->debug ) {
				$this->log = new WC_Logger();
			}

			// WC version
			$this->wc_version = get_option( 'woocommerce_version' );

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
				    'title'         => __( 'Enable/Disable', 'woocommerce_sagepayform' ),
				    'label'         => __( 'Enable SagePay Form', 'woocommerce_sagepayform' ),
				    'type'          => 'checkbox',
				    'description'   => '',
				    'default'       => $this->default_enabled
				),
				'initial_options' 	=> array(
					'title' 		=> __( 'Initial Setup Options', 'woocommerce_sagepayform' ),
					'type' 			=> 'title',
					'description' 	=> __( '<div style="display:block; border-bottom:1px dotted #000; width:100%;"></div>', 'woocommerce_sagepayform' )
				),
				'debugmode'         => array(
				    'title'         => __( 'Debug Mode', 'woocommerce_sagepayform' ),
				    'type'          => 'checkbox',
				    'options'       => array('no'=>'No','yes'=>'Yes'),
				    'label'     	=> __( 'Enable Debug Mode', 'woocommerce_sagepayform' ),
				    'default'       => $this->default_debug
				),
				'status'            => array(
				    'title'         => __( 'Status', 'woocommerce_sagepayform' ),
				    'type'          => 'select',
				    'options'       => array('live'=>'Live','testing'=>'Testing','sim'=>'Simulate'),
				    'description'   => __( 'Set SagePay Live/Testing Status.', 'woocommerce_sagepayform' ),
				    'default'       => $this->default_status
				),
				'vendor'            => array(
				    'title'         => __( 'Vendor Name', 'woocommerce_sagepayform' ),
				    'type'          => 'text',
				    'description'   => __( 'This should have been supplied by SagePay when you created your account.', 'woocommerce_sagepayform' ),
				    'default'       => $this->default_vendor
				),
				'vendorpwd'         => array(
				    'title'         => __( 'LIVE Encryption Password', 'woocommerce_sagepayform' ),
				    'type'          => 'text',
				    'description'   => __( 'This should have been supplied by SagePay when you created your account. This NOT the vendor password', 'woocommerce_sagepayform' ),
				    'default'       => $this->default_vendorpwd
				),
				'testvendorpwd'     => array(
				    'title'         => __( 'Testing Encryption Password', 'woocommerce_sagepayform' ),
				    'type'          => 'text',
				    'description'   => __( 'This should have been supplied by SagePay when you created your account. This NOT the vendor password', 'woocommerce_sagepayform' ),
				    'default'       => $this->default_testvendorpwd
				),
				'simvendorpwd'      => array(
				    'title'         => __( 'Simulation Encryption Password', 'woocommerce_sagepayform' ),
				    'type'          => 'text',
				    'description'   => __( 'This may have been supplied by SagePay when you created your account. This NOT the vendor password', 'woocommerce_sagepayform' ),
				    'default'       => $this->default_simvendorpwd
				),
				'protocol'          => array(
				    'title'         => __( 'Protocol', 'woocommerce_sagepayform' ),
				    'type'          => 'select',
				    'options'       => array('2.23'=>'Protocol 2.23','3.00'=>'Protocol 3.00 - Default'),
				    'description'   => __( 'Set SagePay Form Protocol. ' . ( !function_exists('mcrypt_encrypt') ? '<strong>Protocol 3 requires PHP mcrypt be installed, your server does not have this available, this is something that needs to be done by your host.</strong>' : '') , 'woocommerce_sagepayform' ),
				    'default'       => $this->default_protocol
				),
				'txtype'            => array(
				    'title'         => __( "SagePay Transaction Type", 'woocommerce_sagepayform' ),
				    'type'          => 'select',
				    'options'       => array('PAYMENT'=>'PAYMENT','DEFERRED'=>'DEFERRED','AUTHENTICATE'=>'AUTHENTICATE'),
				    'description'   => __( "<br/>By default a PAYMENT transaction type is used to gain an authorisation from the bank, then settle that transaction early the following morning, committing the funds to be taken from your customer's card.<br/><br/>In some cases you may not wish to take the funds from the card immediately, but merely place a shadow on the customer's card to ensure they cannot subsequently spend those funds elsewhere, and then only take the money when you are ready to ship the goods. This type of transaction is called a DEFERRED transaction.<br/><br/>The AUTHENTICATE and AUTHORISE methods are specifically for use by merchants who are either (i) unable to fulfil the majority of orders in less than 6 days (or sometimes need to fulfil them after 30 days) or (ii) do not know the exact amount of the transaction at the time the order is placed (for example, items shipped priced by weight, or items affected by foreign exchange rates).<br/><br/>Unlike normal PAYMENT or DEFERRED transactions, AUTHENTICATE transactions do not obtain an authorisation at the time the order is placed. Instead the card and card holder are validated using the 3D-Secure mechanism provided by the card-schemes and card issuing banks, with a view to later authorisation.", 'woocommerce_sagepayform' ),
				    'default'       => $this->default_txtype
				),

				'checkout_options' 	=> array(
					'title' 		=> __( 'Checkout Options', 'woocommerce_sagepayform' ),
					'type' 			=> 'title',
					'description' 	=> __( '<div style="display:block; border-bottom:1px dotted #000; width:100%;">This section controls what is shown on the checkout page.</div>', 'woocommerce_sagepayform' )
				),
				'title'             => array(
				    'title'         => __( 'Title', 'woocommerce_sagepayform' ),
				    'type'          => 'text',
				    'description'   => __( 'This controls the title which the user sees during checkout.', 'woocommerce_sagepayform' ),
				    'default'       => $this->default_title
				),
				'description'       => array(
				    'title'         => __( 'Description', 'woocommerce_sagepayform' ),
				    'type'          => 'textarea',
				    'description'   => __( 'This controls the description which the user sees during checkout.', 'woocommerce_sagepayform' ),
				    'default'       => $this->default_description
				),
				'order_button_text'		=> array(
					'title' 		=> __( 'Checkout Pay Button Text', 'woocommerce_sagepayform' ),
					'type' 			=> 'text',
					'description' 	=> __( 'This controls the pay button text shown during checkout.', 'woocommerce_sagepayform' ),
					'default' 		=> $this->default_order_button_text
				),
				'cardtypes'			=> array(
					'title' 		=> __( 'Accepted Cards', 'woocommerce_sagepayform' ), 
					'type' 			=> 'multiselect',
					'class'			=> 'chosen_select',
					'css'         	=> 'width: 350px;', 
					'description' 	=> __( 'Select which card types to accept.', 'woocommerce_sagepayform' ), 
					'default' 		=> $this->default_cardtypes,
					'options' 		=> array(
							'MasterCard'		=> 'MasterCard',
							'Maestro'			=> 'Maestro', 
							'Visa'				=> 'Visa',
							'Visa Debit'		=> 'Visa Debit',
							'American Express' 	=> 'American Express',
							'PayPal'			=> 'PayPal'
						),
				),
				'sagelink' 			=> array(
					'title' 		=> __( '"What is SagePay" Link', 'woocommerce_sagepayform' ),
					'type' 			=> 'select',
					'options' 		=> array('yes'=>'Yes','no'=>'No'),
					'description' 	=> __( 'Include a "What is SagePay" link on the checkout to give customers more confidence. (If the SagePay logo option is set to yes then the logo becomes the link)', 'woocommerce_sagepayform' ),
					'default' 		=> $this->default_sagelink
				),
				'sagelogo' 			=> array(
					'title' 		=> __( 'SagePay Logo', 'woocommerce_sagepayform' ),
					'type' 			=> 'select',
					'options' 		=> array('yes'=>'Yes','no'=>'No'),
					'description' 	=> __( 'Include the SagePay logo on the checkout.', 'woocommerce_sagepayform' ),
					'default' 		=> $this->default_sagelogo	
				),
				
				'sagepay_options' 	=> array(
					'title' 		=> __( 'SagePay Options', 'woocommerce_sagepayform' ),
					'type' 			=> 'title',
					'description' 	=> __( '<div style="display:block; border-bottom:1px dotted #000; width:100%;"> </div>', 'woocommerce_sagepayform' )
				),
				'email'             => array(
				    'title'         => __( 'Vendor Email Address', 'woocommerce_sagepayform' ),
				    'type'          => 'text',
				    'description'   => __( 'Please enter your email address; If provided, an e-mail will be sent to this address when each transaction completes (successfully or otherwise). If you wish to use multiple email addresses, you should add them using the : (colon) character as a separator e.g. <code>me@mail1.com:me@mail2.com</code>', 'woocommerce_sagepayform' ),
				    'default'       => $this->default_email
				),
				'sendemail'         => array(
				    'title'         => __( 'Transaction Email Status', 'woocommerce_sagepayform' ),
				    'type'          => 'select',
				    'options'       => array('0'=>'Do not send either customer or vendor e-mails','1'=>'Send customer and vendor e-mails if addresses are provided','2'=>'Send vendor e-mail but NOT the customer e-mail'),
				    'default'       => $this->default_sendemail
				),
				'allow_gift_aid'        => array(
				    'title'         => __( 'Allow Gift Aid', 'woocommerce_sagepayform' ),
				    'type'          => 'checkbox',
				    'description'   => __( 'Enable this to allow the gift aid acceptance box to appear on the payment page. This option only makes a difference if your vendor account is Gift Aid enabled.', 'woocommerce_sagepayform' ),
				    'default'       => $this->default_allow_gift_aid
				),
				'apply_avs_cv2'         => array(
				    'title'         => __( 'AVS / CV2 Status', 'woocommerce_sagepayform' ),
				    'type'          => 'select',
				    'options'       => array('0'=>'If AVS/CV2 enabled then check them. If rules apply, use rules.','1'=>'Force AVS/CV2 checks even if not enabled for the account. If rules apply, use rules.','2'=>'Force NO AVS/CV2 checks even if enabled on account.','3'=>'Force AVS/CV2 checks even if not enabled for the account but DON’T apply any rules.'),
				    'description'   => __( 'Using this flag you can fine tune the AVS/CV2 checks and rule set you’ve defined at a transaction level. This is useful in circumstances where direct and trusted customer contact has been established and you wish to override the default security checks.', 'woocommerce_sagepayform' ),
				    'default'       => $this->default_apply_avs_cv2
				),
				'apply_3dsecure'    => array(
				    'title'         => __( '3D Secure Status', 'woocommerce_sagepayform' ),
				    'type'          => 'select',
				    'options'       => array('0'=>'If 3D-Secure checks are possible and rules allow, perform the checks and apply the authorisation rules','1'=>'Force 3D-Secure checks for this transaction if possible and apply rules for authorisation.','2'=>'Do not perform 3D-Secure checks for this transaction and always authorise.','3'=>'Force 3D-Secure checks for this transaction if possible but ALWAYS obtain an auth code, irrespective of rule base.'),
				    'description'   => __( 'Using this flag you can fine tune the 3D Secure checks and rule set you’ve defined at a transaction level. This is useful in circumstances where direct and trusted customer contact has been established and you wish to override the default security checks.', 'woocommerce_sagepayform' ),
				    'default'       => $this->default_apply_3dsecure
				),
				'surcharge_options' 	=> array(
					'title' 		=> __( 'Optionally Setup Surcharges', 'woocommerce_sagepayform' ),
					'type' 			=> 'title',
					'description' 	=> __( '<div style="display:block; border-bottom:1px dotted #000; width:100%;">You can create surcharges for specific card types if required, these are shown to the customer once they have selected their card type and added to the order total.<br /><br />The format should be method|value, where method is either P for percentage or F for fixed and the surchage value eg P|5 would give a 5% surcharge, F|2.50 would give a fixed surchage of 2.50. Leave blank for no surcharge for that payment method</div>', 'woocommerce_sagepayform' )
				),
				'enablesurcharges'  => array(
				    'title'         => __( 'Sage Surcharges', 'woocommerce_sagepayform' ),
				    'type'          => 'checkbox',
				    'options'       => array('no'=>'No','yes'=>'Yes'),
				    'label'     	=> __( 'Enable Sage Surcharges. <strong>Surcharges REQUIRE Protocol 3.00</strong>', 'woocommerce_sagepayform' ),
				    'default'       => $this->default_enablesurcharges
				),
				'visasurcharges'   	=> array(
				    'title'         => __( 'Surcharge for Visa Card', 'woocommerce_sagepayform' ),
				    'type'          => 'text',
				    'description'   => __( '', 'woocommerce_sagepayform' ),
				    'default'       => $this->default_VISAsurcharges
				),
				'visadebitsurcharges'=> array(
				    'title'         => __( 'Surcharge for Visa Debit / Delta Card', 'woocommerce_sagepayform' ),
				    'type'          => 'text',
				    'description'   => __( '', 'woocommerce_sagepayform' ),
				    'default'       => $this->default_DELTAsurcharges
				),
				'visaelectronsurcharges'=> array(
				    'title'         => __( 'Surcharge for Visa Electron', 'woocommerce_sagepayform' ),
				    'type'          => 'text',
				    'description'   => __( '', 'woocommerce_sagepayform' ),
				    'default'       => $this->default_UKEsurcharges
				),
				'mcsurcharges'   	=> array(
				    'title'         => __( 'Surcharge for MasterCard', 'woocommerce_sagepayform' ),
				    'type'          => 'text',
				    'description'   => __( '', 'woocommerce_sagepayform' ),
				    'default'       => $this->default_MCsurcharges
				),
				'mcdebitsurcharges' => array(
				    'title'         => __( 'Surcharge for MasterCard Debit Card', 'woocommerce_sagepayform' ),
				    'type'          => 'text',
				    'description'   => __( '', 'woocommerce_sagepayform' ),
				    'default'       => $this->default_MCDEBITsurcharges
				),
				'maestrosurcharges' => array(
				    'title'         => __( 'Surcharge for Maestro Card', 'woocommerce_sagepayform' ),
				    'type'          => 'text',
				    'description'   => __( '', 'woocommerce_sagepayform' ),
				    'default'       => $this->default_MAESTROsurcharges
				),
				'amexsurcharges'   	=> array(
				    'title'         => __( 'Surcharge for American Express', 'woocommerce_sagepayform' ),
				    'type'          => 'text',
				    'description'   => __( '', 'woocommerce_sagepayform' ),
				    'default'       => $this->default_AMEXsurcharges
				),
				'dinerssurcharges'	=> array(
				    'title'         => __( 'Surcharge for Diners Card', 'woocommerce_sagepayform' ),
				    'type'          => 'text',
				    'description'   => __( '', 'woocommerce_sagepayform' ),
				    'default'       => $this->default_DCsurcharges
				),
				'jcbsurcharges' 	=> array(
				    'title'         => __( 'Surcharge for JCB Card', 'woocommerce_sagepayform' ),
				    'type'          => 'text',
				    'description'   => __( '', 'woocommerce_sagepayform' ),
				    'default'       => $this->default_JCBsurcharges
				),
				'lasersurcharges' 	=> array(
				    'title'         => __( 'Surcharge for Laser Card', 'woocommerce_sagepayform' ),
				    'type'          => 'text',
				    'description'   => __( '', 'woocommerce_sagepayform' ),
				    'default'       => $this->default_LASERsurcharges
				),
			);

        } // END init_form_fields

		/**
		 * Returns the plugin's url without a trailing slash
		 */
		public function get_plugin_url() {

			return str_replace( '/classes', '/', untrailingslashit( plugins_url( '/', __FILE__ ) ) );

		}

		/**
		 * Add selected card icons to payment method label, defaults to Visa/MC/Amex/Discover
		 */
		public function get_icon() {
			global $woocommerce;

			$icon = '';

			if ( $this->icon ) {
		
				if ( get_option('woocommerce_force_ssl_checkout')=='no' ) :
					// use icon provided by filter
					$icon = '<img src="' . esc_url( $this->icon ) . '" alt="' . esc_attr( $this->title ) . '" />';			
				else :
					// use icon provided by filter
					$icon = '<img src="' . esc_url( WC_HTTPS::force_https_url( $this->icon ) ) . '" alt="' . esc_attr( $this->title ) . '" />';		
				endif;

			} elseif ( ! empty( $this->cardtypes ) ) {

				if ( get_option('woocommerce_force_ssl_checkout')=='no' ) {

					// display icons for the selected card types
					foreach ( $this->cardtypes as $card_type ) {

						$icon .= '<img src="' . 
									esc_url( $this->get_plugin_url() . '/images/card-' . 
									strtolower( str_replace(' ','-',$card_type) ) . '.png' ) . '" alt="' . 
									esc_attr( strtolower( $card_type ) ) . '" />';
					}

				} else {

					// display icons for the selected card types
					foreach ( $this->cardtypes as $card_type ) {

						$icon .= '<img src="' . 
									esc_url( WC_HTTPS::force_https_url( $this->get_plugin_url() ) . '/images/card-' . 
									strtolower( str_replace(' ','-',$card_type) ) . '.png' ) . '" alt="' . 
									esc_attr( strtolower( $card_type ) ) . '" />';
					}

				}

			}
			
			/**
			 * Add SagePay link
			 */
			if ( $this->sagelink && !$this->sagelogo ) {
				$what_is_sagepay = sprintf( '<a href="%1$s" class="about_sagepayform" style="float: right; line-height: 12px; font-size: 0.83em;" onclick="javascript:window.open(\'%1$s\',\'What is SagePay\',\'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=1060, height=700\'); return false;" title="' . esc_attr__( 'What is SagePay?', 'woocommerce_sagepayform' ) . '">' . esc_attr__( 'What is SagePay?', 'woocommerce_sagepayform' ) . '</a>', esc_url( $this->link ) );
			} else {
				$what_is_sagepay = '';
			}

			/**
			 * Add SagePay logo
			 */
			if ( $this->sagelogo  ) {

				if( $this->sagelink ) {

					if ( get_option('woocommerce_force_ssl_checkout')=='no' ) {
						// use icon provided by filter
						$icon = $icon . sprintf( '<a href="%1$s" onclick="javascript:window.open(\'%1$s\',\'What is SagePay\',\'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=1060, height=700\'); return false;" title="' . esc_attr__( 'What is SagePay?', 'woocommerce_sagepayform' ) . '">' . '<img src="' . esc_url( $this->get_plugin_url() . 'images/sagepaylogo.png' ) . '" alt="Payments By SagePay" />' . '</a>', esc_url( $this->link ) );		
					} else {
						// use icon provided by filter
						$icon = $icon . sprintf( '<a href="%1$s" onclick="javascript:window.open(\'%1$s\',\'What is SagePay\',\'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=1060, height=700\'); return false;" title="' . esc_attr__( 'What is SagePay?', 'woocommerce_sagepayform' ) . '">' . '<img src="' . esc_url( WC_HTTPS::force_https_url( $this->get_plugin_url() . 'images/sagepaylogo.png' ) ) . '" alt="Payments By SagePay" />' . '</a>', esc_url( $this->link ) );		
					}

				} else {

					if ( get_option('woocommerce_force_ssl_checkout')=='no' ) {
						// use icon provided by filter
						$icon = $icon . '<img src="' . esc_url( $this->get_plugin_url() . 'images/sagepaylogo.png' ) . '" alt="Payments By SagePay" style="float: right;"/>';			
					} else {
						// use icon provided by filter
						$icon = $icon . '<img src="' . esc_url( WC_HTTPS::force_https_url( $this->get_plugin_url() . 'images/sagepaylogo.png' ) ) . '" alt="Payments By SagePay" style="float: right;"/>';		
					}

				}

			}

			return apply_filters( 'woocommerce_gateway_icon', $icon . $what_is_sagepay, $this->id );
		}

        /**
         * Generate the form button
         */
        public function generate_sagepay_form( $order_id ) {
            global $woocommerce;

            $order = new WC_Order( $order_id );

            // Replace unwanted characters
            $unwanted_array = array('Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o','ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );


            // WooCommerce 2.1
            if ( function_exists( 'wc_enqueue_js' ) ) {
				wc_enqueue_js('
					jQuery("body").block({
						message: "<img src=\"' . esc_url( apply_filters( 'woocommerce_ajax_loader_url', $woocommerce->plugin_url() . '/assets/images/ajax-loader@2x.gif' ) ) . '\" alt=\"Redirecting&hellip;\" style=\"float:left; margin-right: 10px;\" />'.__('Thank you for your order. We are now redirecting you to SagePay to make payment.', 'woocommerce_sagepayform').'",
						overlayCSS:
						{
							background: "#fff",
							opacity: 0.6
						},
						css: {
				       		padding:        20,
				        	textAlign:      "center",
				        	color:          "#555",
				        	border:         "3px solid #aaa",
				        	backgroundColor:"#fff",
				        	cursor:         "wait",
				        	lineHeight:		"32px"
				    	}
					});
					jQuery("#submit_sagepayform_payment_form").click();
				');
			} else {
				$woocommerce->add_inline_js('
					jQuery("body").block({
						message: "<img src=\"' . esc_url( apply_filters( 'woocommerce_ajax_loader_url', $woocommerce->plugin_url() . '/assets/images/ajax-loader.gif' ) ) . '\" alt=\"Redirecting&hellip;\" style=\"float:left; margin-right: 10px;\" />'.__('Thank you for your order. We are now redirecting you to SagePay to make payment.', 'woocommerce_sagepayform').'",
						overlayCSS:
						{
							background: "#fff",
							opacity: 0.6
						},
						css: {
				       		padding:        20,
				        	textAlign:      "center",
				        	color:          "#555",
				        	border:         "3px solid #aaa",
				        	backgroundColor:"#fff",
				        	cursor:         "wait",
				        	lineHeight:		"32px"
				    	}
					});
					jQuery("#submit_sagepayform_payment_form").click();
				');
			}


            if ( $this->status == 'testing' ) {
                $sagepayform_adr = $this->testurl;
            } elseif ( $this->status == 'sim' ) {
                $sagepayform_adr = $this->simurl;
            } else {
                $sagepayform_adr = $this->liveurl;
            }

            // Post Test Only
            // $sagepayform_adr = 'https://test.sagepay.com/showpost/showpost.asp';

            /**
             * Set the form protocol
             * If surcharges are set then it MUST be Protocol 3.00
             * Otherwise use the customer's choice
             *
             * Override this setting if mcrypt is not available
             */
			if ( $this->enablesurcharges == 'yes' && function_exists('mcrypt_encrypt') ) {
            	$sagepayform  = '<input type="hidden" name="VPSProtocol" value="3.00" />';
			} elseif ( $this->protocol && function_exists('mcrypt_encrypt') ) {
				$sagepayform  = '<input type="hidden" name="VPSProtocol" value="' . $this->protocol . '" />';
			} else {
				$sagepayform  = '<input type="hidden" name="VPSProtocol" value="2.23" />';
			}

            $sagepayform .= '<input type="hidden" name="TxType" value="' . $this->txtype . '" />';
            $sagepayform .= '<input type="hidden" name="Vendor" value="' . $this->vendor . '" />';

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

			/**
			 * Setup the surcharges if necessary
			 */
			$surchargexml = ''; 
			if ( $this->enablesurcharges == 'yes' ) {
				$cardtypes = array(
									'VISAsurcharges',
									'DELTAsurcharges',
									'UKEsurcharges',
									'MCsurcharges',
									'MCDEBITsurcharges',
									'MAESTROsurcharges',
									'AMEXsurcharges',
									'DCsurcharges',
									'JCBsurcharges',
									'LASERsurcharges'
									);

				$surchargexml = '<surcharges>' . "\r\n";
				
				// Set up arrays for str_replace
				$surchargeType = array('F','P');
				$surchargeTypeReplacement = array('fixed','percentage');
				
				foreach ( $cardtypes as $cardtype ) :
				
					if ( $this->$cardtype != '' ) {
						
						$surchargevalue = explode( '|',$this->$cardtype );
						
						$surchargexml .= '<surcharge>' . "\r\n";
						$surchargexml .= '<paymentType>' . str_replace( 'surcharges','',$cardtype ) . '</paymentType>' . "\r\n";
						$surchargexml .= '<' . str_replace($surchargeType,$surchargeTypeReplacement,$surchargevalue[0]). '>' . 
												$surchargevalue[1] . 
										 '</' .str_replace($surchargeType,$surchargeTypeReplacement,$surchargevalue[0]). '>' . "\r\n";
						$surchargexml .= '</surcharge>' . "\r\n";

					}
				
				endforeach;
				
				$surchargexml .= '</surcharges>' . "\r\n";
				
			}

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

            $VendorTxCode = $order->order_key . '-' . $order->id . '-' . time();

            // SAGE Line 50 Fix
            $VendorTxCode = str_replace( 'order_', '', $VendorTxCode );

            // Just in case
            $VendorTxCode = str_replace( ' ', '', $VendorTxCode );

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

            // Bring it all together into one string
            $sage_pay_args_array = array(
                'VendorTxCode'      => $VendorTxCode,
                'Amount'            => $order->get_total(),
                'Currency'          => get_woocommerce_currency(),
                'Description'       => __( 'Order', 'woocommerce_sagepayform' ) . ' ' . str_replace( '#' , '' , $order->get_order_number() ),
                'SuccessURL'        => str_replace( 'https:', 'http:', $this->successurl ),
                'FailureURL'        => apply_filters( 'woocommerce_sagepayform_cancelurl' , html_entity_decode( $order->get_cancel_order_url() ), $order_id, $order->order_key ),
                'CustomerName'      => $order->billing_first_name . ' ' . $order->billing_last_name,
                'CustomerEMail'     => $order->billing_email,
                'VendorEMail'       => $this->email,
                'SendEMail'         => $this->sendemail,

                // Billing Address info
                'BillingFirstnames' => $order->billing_first_name,
                'BillingSurname'    => $order->billing_last_name,
                'BillingAddress1'   => $order->billing_address_1,
                'BillingAddress2'   => $order->billing_address_2,
                'BillingCity'       => $order->billing_city,
                'BillingState'      => $billing_state,
                'BillingPostCode'   => $order->billing_postcode,
                'BillingCountry'    => $order->billing_country,
                'BillingPhone'      => $order->billing_phone,
                'CustomerEMail'     => $order->billing_email,

                // Shipping Address info
                'DeliveryFirstnames'=> apply_filters( 'woocommerce_sagepay_form_deliveryfirstname', $DeliveryFirstnames ),
                'DeliverySurname'   => apply_filters( 'woocommerce_sagepay_form_deliverysurname', $DeliverySurname ),
                'DeliveryAddress1'  => apply_filters( 'woocommerce_sagepay_form_deliveryaddress1', $DeliveryAddress1 ),
                'DeliveryAddress2'  => apply_filters( 'woocommerce_sagepay_form_deliveryaddress2', $DeliveryAddress2 ),
                'DeliveryCity'      => apply_filters( 'woocommerce_sagepay_form_deliverycity', $DeliveryCity ),
                'DeliveryState'     => apply_filters( 'woocommerce_sagepay_form_deliverystate', $DeliveryState ),
                'DeliveryPostCode'  => apply_filters( 'woocommerce_sagepay_form_deliverypostcode', $DeliveryPostCode ),
                'DeliveryCountry'   => apply_filters( 'woocommerce_sagepay_form_deliverycountry', $DeliveryCountry ),
                'DeliveryPhone'     => apply_filters( 'woocommerce_sagepay_form_deliveryphone', $order->billing_phone ),

                // BASKET
                'Basket'            => strtr( $sage_pay_basket, $unwanted_array ),

                // Settings
                'AllowGiftAid'      => $this->allow_gift_aid,
                'ApplyAVSCV2'       => $this->apply_avs_cv2,
                'Apply3DSecure'     => $this->apply_3dsecure,
				
				// SurchargeXML
				'surchargeXML'		=> $surchargexml,
				'ReferrerID'		=> $this->referrerid
            );

			/**
			 * Logging all the things
			 */
			if ( $this->debug == true ) {
				// Start the log off with a new line
				$this->log->add( $this->id, __('=============================================', 'woocommerce_sagepayform') );
				$this->log->add( $this->id, __('============= SagePay Logging ================', 'woocommerce_sagepayform') );
				$this->log->add( $this->id, __('============= ' .date('d M Y, H:i:s'). ' =============', 'woocommerce_sagepayform') );
				$this->log->add( $this->id, __('=============================================', 'woocommerce_sagepayform') );
   				$this->log->add( $this->id, print_r( $sage_pay_args_array, TRUE ) );
   			}

            $sage_pay_args = array();

            foreach( $sage_pay_args_array as $param => $value ) {
				
				// Remove all the non-english things
     			$value = strtr( $value, $unwanted_array );

     			$value = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $value);
				$sage_pay_args[] = $param . "=" . $value;

			}	

            $sage_pay_args = implode( '&', $sage_pay_args );

            if ( $this->status == 'testing' && $this->testvendorpwd ) {
                $vendorpwd = $this->testvendorpwd;
            } elseif ( $this->status == 'sim' && $this->simvendorpwd ) {
           		$vendorpwd = $this->simvendorpwd; 
           	} else {
           		$vendorpwd = $this->vendorpwd;
           	}

           	if ( ( $this->enablesurcharges == 'yes' && function_exists('mcrypt_encrypt') ) || ( $this->protocol == '3.00' && function_exists('mcrypt_encrypt') ) ) {
            	$sagepaycrypt_xor = $sage_pay_args;
            	$sagepaycrypt_b64 = $this->encrypt( $sage_pay_args, $vendorpwd );
			} else {
				$sagepaycrypt_xor = $this->simpleXor( $sage_pay_args, $vendorpwd );
				$sagepaycrypt_b64 = base64_encode( $sagepaycrypt_xor );
			}

            $sagepaycrypt     = '<input type="hidden" name="Crypt" value="' . $sagepaycrypt_b64 . '" />';

            // This is the form. 
            return  '<form action="' . $sagepayform_adr . '" method="post" id="sagepayform_payment_form">
                    ' . $sagepayform . '
                    ' . $sagepaycrypt . '
                    <input type="submit" class="button-alt" id="submit_sagepayform_payment_form" value="' . __( 'Pay via SagePay', 'woocommerce_sagepayform' ) . '" /> <a class="button cancel" href="' . $order->get_cancel_order_url() . '">' . __( 'Cancel order &amp; restore cart', 'woocommerce_sagepayform' ) . '</a>
                	</form>';

        }

        /**
         * process_payment function.
         *
         * @access public
         * @param mixed $order_id
         * @return void
         */
        function process_payment( $order_id ) {
            $order = new WC_Order( $order_id );

            return array(
                'result'    => 'success',
            	'redirect'	=> $order->get_checkout_payment_url( true )
            );
            
        }

        /**
         * receipt_page function.
         *
         * @access public
         * @param mixed $order
         * @return void
         */
        function receipt_page( $order ) {
            echo '<p>' . __( 'Thank you for your order, please click the button below to pay with SagePay.', 'woocommerce_sagepayform' ) . '</p>';
            echo $this->generate_sagepay_form( $order );
        }

        /**
         * check_sagepay_response function.
         *
         * @access public
         * @return void
         */
        function check_sagepay_response() {

        	@ob_clean();

            if ( isset( $_GET["crypt"] ) ) {

            	if ( $this->status == 'testing' && $this->testvendorpwd ) {
                	$vendorpwd = $this->testvendorpwd;
            	} elseif ( $this->status == 'sim' && $this->simvendorpwd ) {
            	    $vendorpwd = $this->simvendorpwd;
            	} else {
                	$vendorpwd = $this->vendorpwd;
            	}

                $crypt = $_GET["crypt"];

           		if ( ( $this->enablesurcharges == 'yes' && function_exists('mcrypt_encrypt') ) || ( $this->protocol == '3.00' && function_exists('mcrypt_encrypt') ) ) {
            		$sagepay_return_data   = $this->decrypt( $crypt, $vendorpwd );
				} else {
					$sagepay_return_data   = $this->simpleXor( $this->base64Decode( $_GET["crypt"] ), $vendorpwd );
				}

				$sagepay_return_values = $this->getTokens( $sagepay_return_data );

				/**
				 * Logging all the things
				 */
				if ( $this->debug == true ) {
					$this->log->add( $this->id, __(' ', 'woocommerce_sagepayform') );
					$this->log->add( $this->id, __(' ', 'woocommerce_sagepayform') );
					$this->log->add( $this->id, __('============= SagePay Return ================', 'woocommerce_sagepayform') );				
   					$this->log->add( $this->id, print_r( $sagepay_return_values, TRUE ) );
   					$this->log->add( $this->id, __('=============================================', 'woocommerce_sagepayform') );
   					$this->log->add( $this->id, __('=============================================', 'woocommerce_sagepayform') );
   				}

                if ( isset( $sagepay_return_values['VPSTxId'] ) ) {
                    do_action( "valid_sagepayform_request", $sagepay_return_values );
                }

            } else {

            	wp_die( "Sage Request Failure<br />" . 'Check the WooCommerce SagePay Settings for error messages', "Sage Failure", array( 'response' => 200 ) );
            }
        }


        /**
         * successful_request function.
         *
         * @access public
         * @param mixed $sagepay_return_values
         * @return void
         */
        function successful_request( $sagepay_return_values ) {

            // Custom holds post ID
            if ( ! empty( $sagepay_return_values['Status'] ) && ! empty( $sagepay_return_values['VendorTxCode'] ) ) {

                $VendorTxCode    = explode( '-', $sagepay_return_values['VendorTxCode'] );

                // SAGE Line 50 Fix
                $order_key       = 'order_' . $VendorTxCode[0];
                $order_id        = $VendorTxCode[1];

                $accepted_status = array( 'OK', 'NOTAUTHED', 'MALFORMED', 'INVALID', 'ABORT', 'REJECTED', 'AUTHENTICATED', 'REGISTERED', 'ERROR' );

                if ( ! in_array( $sagepay_return_values['Status'], $accepted_status ) ) {
                    echo "<p>" . $sagepay_return_values['Status'] . " NOT FOUND!</p>";
                    exit;
                }

                $order = new WC_Order( (int) $order_id );

                $order_key_array = array( 'wc_', 'order_', 'wc_order_', 'order_wc_' );

                if ( str_replace($order_key_array,'',$order->order_key) !== str_replace($order_key_array,'',$order_key) ) {
                    echo "<p>" . $order->order_key . " AND " . $order_key . " DO NOT MATCH!</p>";
                    exit;
                }

                if ( $order->status !== 'completed' ) {
                	// We are here so lets check status and do actions
 
                    switch ( strtolower( $sagepay_return_values['Status'] ) ) {
                        case 'ok' :
                        	// Payment completed
							$ordernotes  = 'SagePay payment completed';
							$ordernotes .= '<br />Status : ' 		. isset( $sagepay_return_values['Status'] ) ? $sagepay_return_values['Status'] : '';
							$ordernotes .= '<br />StatusDetail : ' 	. isset( $sagepay_return_values['StatusDetail'] ) ? $sagepay_return_values['StatusDetail'] : '';
							$ordernotes .= '<br />VendorTxCode : ' 	. isset( $sagepay_return_values['VendorTxCode'] ) ? $sagepay_return_values['VendorTxCode'] : '';
							$ordernotes .= '<br />VPSTxId : ' 		. isset( $sagepay_return_values['VPSTxId'] ) ? $sagepay_return_values['VPSTxId'] : '';
							$ordernotes .= '<br />TxAuthNo : ' 		. isset( $sagepay_return_values['TxAuthNo'] ) ? $sagepay_return_values['TxAuthNo'] : '';
							$ordernotes .= '<br />Amount : ' 		. isset( $sagepay_return_values['Amount'] ) ? $sagepay_return_values['Amount'] : '';
							$ordernotes .= '<br />AVSCV2 : ' 		. isset( $sagepay_return_values['AVSCV2'] ) ? $sagepay_return_values['AVSCV2'] : '';
							$ordernotes .= '<br />AddressResult : ' . isset( $sagepay_return_values['AddressResult'] ) ? $sagepay_return_values['AddressResult'] : '';
							$ordernotes .= '<br />PostCodeResult : '. isset( $sagepay_return_values['PostCodeResult'] ) ? $sagepay_return_values['PostCodeResult'] : '';
							$ordernotes .= '<br />CV2Result : ' 	. isset( $sagepay_return_values['CV2Result'] ) ? $sagepay_return_values['CV2Result'] : '';
							$ordernotes .= '<br />GiftAid : ' 		. isset( $sagepay_return_values['GiftAid'] ) ? $sagepay_return_values['GiftAid'] : '';
							$ordernotes .= '<br />3DSecureStatus : '. isset( $sagepay_return_values['3DSecureStatus'] ) ? $sagepay_return_values['3DSecureStatus'] : '';
							$ordernotes .= '<br />CardType : ' 		. isset( $sagepay_return_values['CardType'] ) ? $sagepay_return_values['CardType'] : '';
							$ordernotes .= '<br />Last4Digits : ' 	. isset( $sagepay_return_values['Last4Digits'] ) ? $sagepay_return_values['Last4Digits'] : '';
							$ordernotes .= '<br />DeclineCode : ' 	. isset( $sagepay_return_values['DeclineCode'] ) ? $sagepay_return_values['DeclineCode'] : '';
							$ordernotes .= '<br />BankAuthCode : ' 	. isset( $sagepay_return_values['BankAuthCode'] ) ? $sagepay_return_values['BankAuthCode'] : '';

							// Add fee to order if there is a SagePay surcharge
							if ( isset($sagepay_return_values['Surcharge']) ) :

   								$item_id = woocommerce_add_order_item( $order_id, array(
 									'order_item_name' 		=> 'SagePay Surcharge',
 									'order_item_type' 		=> 'fee'
 								) );

 								// Add line item meta
 								if ( $item_id ) :
								 	woocommerce_add_order_item_meta( $item_id, '_tax_class', '' );
								 	woocommerce_add_order_item_meta( $item_id, '_line_total', $sagepay_return_values['Surcharge'] );
								 	woocommerce_add_order_item_meta( $item_id, '_line_tax', '' );
 								endif;

 								$old_order_total = get_post_meta( $order_id, '_order_total', TRUE );
 								update_post_meta( $order_id, '_order_total', $old_order_total + $sagepay_return_values['Surcharge'] );

 								$ordernotes .= '<br /><br />Order total updated';
 								$ordernotes .= '<br />Surcharge : ' 	. $sagepay_return_values['Surcharge'];

 							endif;

                            $order->add_order_note( $ordernotes );
                            $order->payment_complete( $sagepay_return_values['VPSTxId'] );

                        break;
                        case 'notauthed' :
                        	// Message
                            $order->update_status( 'on-hold', sprintf( __( 'Payment %s via SagePay.', 'woocommerce_sagepayform' ), woocommerce_clean( $sagepay_return_values['Status'] ) ) );
                        break;
						case 'authenticated' :
                            // Message
							$ordernotes  = 'SagePay payment authenticated - No funds have been collected at this time, please log in to yout SagePay control panel to collect the funds';
							$ordernotes .= '<br />Status : ' . $sagepay_return_values['Status'];
							$ordernotes .= '<br />StatusDetail : ' . $sagepay_return_values['StatusDetail'];
							$ordernotes .= '<br />VendorTxCode : ' . $sagepay_return_values['VendorTxCode'];
							$ordernotes .= '<br />VPSTxId : ' . $sagepay_return_values['VPSTxId'];
							$ordernotes .= '<br />TxAuthNo : ' . $sagepay_return_values['TxAuthNo'];
							$ordernotes .= '<br />Amount : ' . $sagepay_return_values['Amount'];
							$ordernotes .= '<br />AVSCV2 : ' . $sagepay_return_values['AVSCV2'];
							$ordernotes .= '<br />AddressResult : ' . $sagepay_return_values['AddressResult'];
							$ordernotes .= '<br />PostCodeResult : ' . $sagepay_return_values['PostCodeResult'];
							$ordernotes .= '<br />CV2Result : ' . $sagepay_return_values['CV2Result'];
							$ordernotes .= '<br />GiftAid : ' . $sagepay_return_values['GiftAid'];
							$ordernotes .= '<br />3DSecureStatus : ' . $sagepay_return_values['3DSecureStatus'];
							$ordernotes .= '<br />AddressStatus : ' . $sagepay_return_values['CAVV'];
							$ordernotes .= '<br />CardType : ' . $sagepay_return_values['CardType'];
							$ordernotes .= '<br />Last4Digits : ' . $sagepay_return_values['Last4Digits'];
							$ordernotes .= '<br />Surcharge : ' . $sagepay_return_values['Surcharge'];
							$ordernotes .= '<br />DeclineCode : ' . $sagepay_return_values['DeclineCode'];
							$ordernotes .= '<br />BankAuthCode : ' . $sagepay_return_values['BankAuthCode'];

							// Add fee to order if there is a SagePay surcharge
							if ( $sagepay_return_values['Surcharge'] ) :
                            	
   								$item_id = woocommerce_add_order_item( $order_id, array(
 									'order_item_name' 		=> 'SagePay Surcharge',
 									'order_item_type' 		=> 'fee'
 								) );

 								// Add line item meta
 								if ( $item_id ) :
								 	woocommerce_add_order_item_meta( $item_id, '_tax_class', '' );
								 	woocommerce_add_order_item_meta( $item_id, '_line_total', $sagepay_return_values['Surcharge'] );
								 	woocommerce_add_order_item_meta( $item_id, '_line_tax', '' );
 								endif;

 								$old_order_total = get_post_meta( $order_id, '_order_total', TRUE );
 								update_post_meta( $order_id, '_order_total', $old_order_total + $sagepay_return_values['Surcharge'] );

 								$ordernotes .= '<br /><br />Order total updated';

 							endif;
							
                            $order->add_order_note( $ordernotes );
                            $order->update_status( 'pending', sprintf( __( 'Payment %s via SagePay.', 'woocommerce_sagepayform' ), woocommerce_clean( $sagepay_return_values['Status'] ) ) );
							$order->add_order_note( __(  ) );

                        break;
                        case 'registered' :
                        	// Message
                            $order->update_status( 'on-hold', sprintf( __( 'Payment %s via SagePay.', 'woocommerce_sagepayform' ), woocommerce_clean( $sagepay_return_values['Status'] ) ) );
							$order->add_order_note( __( 'SagePay payment registered - 3D Secure check failed', 'woocommerce_sagepayform' ) );
                        break;
                        case 'malformed' :
                        case 'invalid' :
                        case 'abort' :
                        case 'rejected' :
                        case 'error' :
                        	// Failed order
                            $order->update_status('failed', sprintf( __( 'Payment %s via SagePay.', 'woocommerce_sagepayform' ), woocommerce_clean( $sagepay_return_values['Status'] ) ) );
                        break;
                    }
                }

                wp_redirect( $this->get_return_url( $order ) );
                exit;
            }

        }

        /**
         * debug_email function.
         *
         * @access public
         * @param mixed $debugemail (default: NULL)
         * @param mixed $subject (default: NULL)
         * @param mixed $message (default: NULL)
         * @param string $debugmode (default: 'no')
         * @return void
         */
        function debug_email( $debugemail = NULL, $subject = NULL, $message = NULL, $debugmode = 'no' ) {

            if ( $debugmode == 'yes' ) {

                if ( $debugemail == NULL ) {
                    $debugemail = get_option( 'admin_email' );
                }

                if ( $subject == NULL ) {
                    $subject = 'SagePay Debug Email From: ' . get_bloginfo( 'name' ) . ' ' . time();
                } else {
                    $subject = $subject . ' ' . get_bloginfo( 'name' ) . ' ' . time();
                }

                $headers        = 'From: ' . get_option( 'admin_email' ) . "\r\n";
                $attachments    = '';

                wp_mail( $debugemail, $subject, $message, $headers, $attachments );
            }

        } // END debug_email

        /**
         * [sagepay_system_status description]
         * @return [type] [description]
         */
        function sagepay_system_status() {

        	$description = __( 'SagePay Form works by sending the user to <a href="http://www.sagepay.com">SagePay</a> to enter their payment information.', 'woocommerce_sagepayform' );

			if ( function_exists( 'ini_get' ) && extension_loaded( 'suhosin' ) ) {
				
				if( ini_get('suhosin.get.max_value_length') < 2000 || ini_get('suhosin.get.max_vars') < 2000 ) {

					$description .= '<div style="padding:5px; margin:5px 0; border:3px solid #000; background:#FFF;">';
				
					$description .= __( '<h3>Warning</h3>', 'woocommerce_sagepayform' );
					$description .= __( '<p><strong>Your server configuration may need to be adjusted for SagePay Form to work correctly. Please place a test order to make sure your customers will be returned to your site correctly</strong></p>', 'woocommerce_sagepayform' );
					$description .= __( '<p>If you experience an issue after paying - you will probably see a white screen with a notice to check your WooCommerce SagePay Form settings - please ask your host to increase the following values</p>', 'woocommerce_sagepayform' );

					$description .= sprintf(__( '<p>suhosin.get.max_value_length = %s IDEAL VALUE : 2000</br />', 'woocommerce_sagepayform' ), size_format( wc_let_to_num( ini_get('suhosin.get.max_value_length') ) ) );

					$description .= sprintf(__( 'suhosin.get.max_vars = %s IDEAL VALUE : 2000</p>', 'woocommerce_sagepayform' ), size_format( wc_let_to_num( ini_get('suhosin.get.max_vars') ) ) );

					$description .= __( '<p>If you have successfully placed a test order and were returned to your "Thank you for ordering" page then you can ignore this warning</p>', 'woocommerce_sagepayform' );

					$description .= '</div>';

				}

			}

			return $description;

		}

        /**
         * base64Decode function.
         *
         * @access public
         * @param mixed $scrambled
         * @return void
         */
        function base64Decode( $scrambled )   {
            // Initialise output variable
            $output = "";

            // Fix plus to space conversion issue
            $scrambled = str_replace( " ", "+", $scrambled );

            // Do decoding
            $output = base64_decode( $scrambled );

            // Return the result
            return $output;
        }


        /**
         * simpleXor function.
         *
         * @access public
         * @param mixed $text
         * @param mixed $key
         * @return void
         */
        function simpleXor( $text, $key ) {
            // Initialise key array
            $key_ascii_array = array();

            // Initialise output variable
            $output = "";

            // Convert $key into array of ASCII values
            for ( $i = 0; $i < strlen( $key ); $i++ ) {
                $key_ascii_array[ $i ] = ord( substr( $key, $i, 1 ) );
            }

            // Step through string a character at a time
            for ( $i = 0; $i < strlen( $text ); $i++ ) {
                // Get ASCII code from string, get ASCII code from key (loop through with MOD), XOR the
                // two, get the character from the result
                $output .= chr( ord( substr( $text, $i, 1 ) ) ^ ( $key_ascii_array[ $i % strlen( $key ) ] ) );
            }

            // Return the result
            return $output;
        }

        /**
         * Protocol 3 Encryption function
         * @param  [type] $securekey [description]
         * @param  [type] $input     [description]
         * @return [type]            [description]
         *
         * This function requires php mcrypt
         */
		function encrypt( $input,$securekey ) { 
    		
    		$pkinput = $this->addPKCS5Padding( $input );
    		$crypt = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $securekey, $pkinput, MCRYPT_MODE_CBC, $securekey);
    		return "@" . bin2hex( $crypt );

		}	

    	/**
    	 * Protocol 3 Decryption function
    	 * @param  [type] $securekey [description]
    	 * @param  [type] $input     [description]
    	 * @return [type]            [description]
    	 *
    	 * This function requires php mcrypt
    	 */
    	function decrypt( $input,$securekey ) {

    		// remove the first char which is @ to flag this is AES encrypted
        	$input = substr($input,1);
       
        	// HEX decoding
        	$input = pack('H*', $input);

        	return mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $securekey, $input, MCRYPT_MODE_CBC, $securekey); 
        }

		function addPKCS5Padding( $input ) {
		   $blocksize = 16;
		   $padding = "";

		   // Pad input to an even block size boundary
		   $padlength = $blocksize - (strlen($input) % $blocksize);
		   for($i = 1; $i <= $padlength; $i++) {
		      $padding .= chr($padlength);
		   }
   
		   return $input . $padding;
		}

        /**
         * getTokens function.
         *
         * @access public
         * @param mixed $query_string
         * @return void
         */
        function getTokens( $query_string ) {

        	$output = '';

        	if ( isset($query_string) && $query_string != '' ) {
            	// List the possible tokens
            	$tokens = array(
            	    "Status",
            	    "StatusDetail",
            	    "VendorTxCode",
            	    "VPSTxId",
            	    "TxAuthNo",
            	    "Amount",
            	    "AVSCV2",
            	    "AddressResult",
            	    "PostCodeResult",
            	    "CV2Result",
            	    "GiftAid",
            	    "3DSecureStatus",
            	    "CAVV",
            	    "CardType",
            	    "Last4Digits",
             	    "Surcharge",
            	    "DeclineCode",
					"BankAuthCode"
            	);

            	// Initialise arrays
            	$output = array();
            	$tokens_found = array();

            	// Get the next token in the sequence
            	for ( $i = count( $tokens ) - 1; $i >= 0; $i-- ) {
            	    // Find the position in the string
             	   $start = strpos( $query_string, $tokens[$i] );

            	    // If token is present record its position and name
            	    if ( $start !== false ) {

						if( !isset($tokens_found[$i]) ) {
            				$tokens_found[$i] = new StdClass();
        				}

            	        $tokens_found[$i]->start = $start;
            	        $tokens_found[$i]->token = $tokens[$i];
            	    }

            	}

            	// Sort in order of position
            	sort( $tokens_found );

            	// Go through the result array, getting the token values
            	for ( $i = 0; $i < count( $tokens_found ); $i++ ) {
            		// Get the start point of the value
            	    $valueStart = $tokens_found[$i]->start + strlen( $tokens_found[ $i ]->token ) + 1;

             	   // Get the length of the value
             	   if ( $i == ( count( $tokens_found ) - 1 ) ) {
                    $output[$tokens_found[ $i ]->token] = substr( $query_string, $valueStart );
            	    } else {
            	        $valueLength = $tokens_found[ $i + 1 ]->start - $tokens_found[ $i ]->start - strlen( $tokens_found[ $i ]->token) - 2;
            	        $output[ $tokens_found[ $i ]->token] = substr( $query_string, $valueStart, $valueLength );
            	    }

            	}

            }

            // Return the output array
            return $output;

        }

	} // END CLASS
