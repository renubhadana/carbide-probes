<?php
/*
Plugin Name: WooCommerce Sage Payments USA Gateway
Plugin URI: http://woothemes.com/woocommerce
Description: Extends WooCommerce. Provides Sage Payment Solutions API payment gateway.
Version: 1.0.7
Author: Add On Enterprises
Author URI: http://www.addonenterprises.com
*/

/*  Copyright 2013  Andrew Benbow  (email : andrew@chromeorange.co.uk)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

    /**
     * Required functions
     */
    if ( ! function_exists( 'woothemes_queue_update' ) )
        require_once( 'woo-includes/woo-functions.php' );

    /**
     * Plugin updates
     */
    woothemes_queue_update( plugin_basename( __FILE__ ), '6b04e48d7f0d2dc52abce05a118af09d', '201957' );

    /**
     * Localization
     */
	load_plugin_textdomain( 'SPUSALANGUAGE', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	/**
     * Defines
     */
	define( 'SPUSAVERSION' 		, '1.0.7' );
    define( 'SPUSASETTINGS'     , admin_url( 'admin.php?page=wc-settings&tab=checkout&section=wc_gateway_sagepaymentusa_api' ) );
	define( 'SPUSALANGUAGE'		, 'woocommerce-spusa' );
	define( 'SPUSASUPPORTURL' 	, 'http://support.woothemes.com/' );
	define( 'SPUSADOCSURL' 		, 'http://docs.woothemes.com/document/sage-payments-usa/');

	/**
	 * Init Sage Payment Solutions after WooCommerce has loaded
	 */
	add_action( 'plugins_loaded', 'init_sagepaymentusa_gateway', 0 );

	function init_sagepaymentusa_gateway() {

    if ( ! class_exists( 'WC_Payment_Gateway' ) )
    	return;

        /**
         * Load Admin Class
         * Used for plugin links.
         */
        class WC_Gateway_SageUSA_admin {

            public function __construct() {

                add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this,'plugin_links' ) );

            }

            /**
             * Plugin page links
             */
            function plugin_links( $links ) {

                $plugin_links = array(
                    '<a href="' . SPUSASETTINGS . '">' . __( 'Settings', 'woocommerce-spusa' ) . '</a>',
                    '<a href="' . SPUSASUPPORTURL . '">' . __( 'Support', 'woocommerce-spusa' ) . '</a>',
                    '<a href="' . SPUSADOCSURL . '">' . __( 'Docs', 'woocommerce-spusa' ) . '</a>',
                );

                return array_merge( $plugin_links, $links );
            }

        }

		if ( is_admin() ) :
			$GLOBALS['WC_Gateway_SageUSA_admin'] = new WC_Gateway_SageUSA_admin();
		endif;

		/**
		 * Include the gateway classes
		 */
		 include('classes/sagepaymentsapi.php');

    	/**
    	 * add_sagepaymentsapi_gateway function.
    	 *
    	 * @access public
    	 * @param mixed $methods
    	 * @return void
    	 */
    	function add_sagepaymentsapi_gateway($methods) {
        	$methods[] = 'WC_Gateway_SagePaymentUSA_API';
        	return $methods;
    	}

    	add_filter( 'woocommerce_payment_gateways', 'add_sagepaymentsapi_gateway' );

	} // EOF init_sagepaymentusa_gateway
