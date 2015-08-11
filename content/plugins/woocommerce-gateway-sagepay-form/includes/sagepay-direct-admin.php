<?php
			$this->form_fields = array(
				'enabled'           => array(
				    'title'         => __( 'Enable/Disable', 'woocommerce_sagepaydirect' ),
				    'label'         => __( 'Enable SagePay Direct for WooCommerce', 'woocommerce_sagepaydirect' ),
				    'type'          => 'checkbox',
				    'description'   => '',
				    'default'       => 'no'
				),
				'title'             => array(
				    'title'         => __( 'Title', 'woocommerce_sagepaydirect' ),
				    'type'          => 'text',
				    'description'   => __( 'This controls the title which the user sees during checkout.', 'woocommerce_sagepaydirect' ),
				    'default'       => __( 'Credit Card via Sage', 'woocommerce_sagepaydirect' )
				),
				'description'       => array(
				    'title'         => __( 'Description', 'woocommerce_sagepaydirect' ),
				    'type'          => 'textarea',
				    'description'   => __( 'This controls the description which the user sees during checkout.', 'woocommerce_sagepaydirect' ),
				    'default'       => 'Pay via Credit / Debit Card with Sage secure card processing.'
				),
				'vendor'      		=> array(
				    'title'         => __( 'SagePay Vendor Name', 'woocommerce_sagepaydirect' ),
				    'type'          => 'text',
				    'description'   => __( 'Used to authenticate your site. This should contain the Sage Pay Vendor Name supplied by Sage Pay when your account was created. Vendor Login Name', 'woocommerce_sagepaydirect' ),
				    'default'       => ''
				),
				'status'            => array(
				    'title'         => __( 'Status', 'woocommerce_sagepaydirect' ),
				    'type'          => 'select',
				    'options'       => array('live'=>'Live','testing'=>'Testing'),
				    'description'   => __( 'Set Sage Bankcard Live/Testing Status.', 'woocommerce_sagepaydirect' ),
				    'default'       => 'testing'
				),
				'txtype'            => array(
				    'title'         => __( 'Status', 'woocommerce_sagepaydirect' ),
				    'type'          => 'select',
				    'options'       => array('PAYMENT'=>'Take Payment Immediately','DEFERRED'=>'Deferred Payment','AUTHENTICATE'=>'Authenticate Only'),
				    'description'   => __( 'Normally this should be set to "Take Payment Immediately"', 'woocommerce_sagepaydirect' ),
				    'default'       => 'PAYMENT'
				),
				'cardtypes'			=> array(
					'title' 		=> __( 'Accepted Cards', 'woocommerce_sagepaydirect' ), 
					'type' 			=> 'multiselect',
					'class'			=> 'chosen_select',
					'css'         	=> 'width: 350px;', 
					'description' 	=> __( 'Select which card types to accept.', 'woocommerce_sagepaydirect' ), 
					'default' 		=> '',
					'options' 		=> array(
							'MasterCard'		=> 'MasterCard',
							'MasterCard Debit'	=> 'MasterCard Debit',
							'Visa'				=> 'Visa',
							'Visa Debit'		=> 'Visa Debit',
							'Discover'			=> 'Discover',
							'American Express' 	=> 'American Express',
							'Maestro'			=> 'Maestro',
							'JCB'				=> 'JCB',
							'Laser'				=> 'Laser'
						),
				),		
				'cvv' 				=> array(
					'title' 		=> __( 'CVV', 'woocommerce_sagepaydirect' ), 
					'label' 		=> __( 'Require customer to enter credit card CVV code', 'woocommerce_sagepaydirect' ), 
					'type' 			=> 'checkbox', 
					'description' 	=> __( '', 'woocommerce_sagepaydirect' ), 
					'default' 		=> 'no'
				),
				'3dsecure' 			=> array(
					'title' 		=> __( '3D Secure', 'woocommerce_sagepaydirect' ),
					'type'			=> 'select',
					'css'         	=> 'width: 350px;', 
					'description' 	=> __( '3D Secure Settings.', 'woocommerce_sagepaydirect' ), 
					'default' 		=> '',
					'options' 		=> array(
							'0'		=> 'If 3D-Secure checks are possible and rules allow, perform the checks and apply the authorisation rules. (default)',
							'1'		=> 'Force 3D-Secure checks for this transaction if possible and apply rules for authorisation. ',
							'2'		=> 'Do not perform 3D-Secure checks for this transaction and always authorise.',
							'3'		=> 'Force 3D-Secure checks for this transaction if possible but ALWAYS obtain an auth code, irrespective of rule base.'
						),
				),		
				'debug'     		=> array(
				    'title'         => __( 'Debug Mode', 'woocommerce_sagepaydirect' ),
				    'type'          => 'checkbox',
				    'options'       => array('no'=>'No','yes'=>'Yes'),
				    'label'     	=> __( 'Enable Debug Mode', 'woocommerce_sagepaydirect' ),
				    'default'       => 'no'
				),
				'notification'		=> array(
				    'title'         => __( 'Notification Email Address', 'woocommerce_sagepaydirect' ),
				    'type'          => 'text',
				    'description'   => __( 'Add an email address that will be notified in the event of a failure', 'woocommerce_sagepaydirect' ),
				    'default'       => get_bloginfo( 'admin_email' )
				),

			);