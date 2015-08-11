<?php
/*
Plugin Name: WooCommerce SagePay Form and SagePay Direct Gateway
Plugin URI: http://woothemes.com/woocommerce
Description: Extends WooCommerce. Provides a SagePay Form / SagePay Direct gateway for WooCommerce. http://www.sagepay.com. For support please contact http://support.woothemes.com.
Version: 3.1.0
Author: Add On Enterprises
Author URI: http://www.addonenterprises.com
*/

/*  Copyright 2013  Add On Enterprises  (email : support@addonenterprises.com)

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
woothemes_queue_update( plugin_basename( __FILE__ ), '6bc0cca47d0274d8ef9b164f6fbec1cc', '18599' );


/**
 * Init SagePay Gateway after WooCommerce has loaded
 */
add_action( 'plugins_loaded', 'init_sagepay_gateway', 0 );

/**
 * Localization
 */
load_plugin_textdomain( 'woocommerce_sagepayform', false, dirname( plugin_basename( __FILE__ ) ) . '/' );

function init_sagepay_gateway() {

    if ( ! class_exists( 'WC_Payment_Gateway' ) )
    	return;

    /**
     * add_sagepay_form_gateway function.
     *
     * @access public
     * @param mixed $methods
     * @return void
     */
	include('classes/class-sagepay-form.php');
    include('classes/class-sagepay-form-admin-notice.php');

    function add_sagepay_form_gateway($methods) {
        $methods[] = 'WC_Gateway_Sagepay_Form';
        return $methods;
    }

    add_filter( 'woocommerce_payment_gateways', 'add_sagepay_form_gateway' );

    /**
     * add_sagepay_direct_gateway function.
     *
     * @access public
     * @param mixed $methods
     * @return void
     */
    include('classes/sagepay-direct-class.php');

    /**
     * Add the Gateway to WooCommerce
     */
    function add_sagepay_direct_gateway($methods) {
        $methods[] = 'WC_Gateway_Sagepay_Direct';
        return $methods;
    }

    add_filter('woocommerce_payment_gateways', 'add_sagepay_direct_gateway' );  
}