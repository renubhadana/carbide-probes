<?php
	/**
	 * Change to Protocol 3
	 */
	class WC_Gateway_Sagepay_Form_Update_Notice {
		
		public function __construct() {

			$options = get_option( 'woocommerce_sagepayform_settings' );
            $this->enabled         		= $options['enabled'];
            $this->protocol 			= $options['protocol'];
			
            /**
             * Add admin notice for protocol 2.23 users
             */
            if( $this->enabled == 'yes' && $this->protocol == '2.23' ) {

            	add_action('admin_notices', array($this, 'admin_notice'));

			}

		}
	
		/**
		 * Display a notice about the changes to Sage Protocol 2.23
		 */
		function admin_notice() {
			
			global $current_user ;
			$user_id = $current_user->ID;
		
			/* Check that the user hasn't already clicked to ignore the message */
			if ( current_user_can( 'manage_options' ) ) {

				echo '<div class="update-nag fade">
						<h3 class="alignleft" style="line-height:200%">';

				printf(__('SagePay recently announced that, from July 2015, they will no longer support Protocol 2.23. Please go to your <a href="%1$s">WooCommerce SagePay settings</a> and update your Protocol setting to 3.00. Please visit <a target="_blank" href="%2$s"> the payment gateway documentation</a> for more info.', 'woocommerce_sagepayform'), get_admin_url() . 'admin.php?page=wc-settings&tab=checkout&section=wc_gateway_sagepay_form', 'http://docs.woothemes.com/document/sagepay-form/');
				
				echo '</h3>';

				if ( !function_exists('mcrypt_encrypt') ) {

					echo '<h3 class="alignleft" style="line-height:200%">';
					printf(__('IMPORTANT! Protocol 3.00 requires the PHP Mcrypt library (<a href="%1$s" target="_blank">%1$s</a>) and your hosting does not appear to have support for this. Please contact your host', 'woocommerce_sagepayform'), 'http://php.net/manual/en/book.mcrypt.php');	
					echo '</h3>';

				}

				echo '<br class="clear">';
				echo '</div>';
			}
		
		}

	} // End class
	
	$WC_Gateway_Sagepay_Form_Update_Notice = new WC_Gateway_Sagepay_Form_Update_Notice;