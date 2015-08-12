<?php
/*
Plugin Name: WooCommerce Carbide Probes Customizations
Plugin URI: http://carbideprobes.com
Description: Adds customizations to WooCommerce for Net-30 payment options and customer shipping accounts.
Version: 1.0.0
Author: Todd Miller <todd@rainydaymedia.net>
Author URI: http://rainydaymedia.net
*/

// Include our Gateway Class and Register Payment Gateway with WooCommerce
add_action( 'plugins_loaded', 'carbide_probes_woocommerce_customization_init', 0 );
function carbide_probes_woocommerce_customization_init()
{
    if ( ! class_exists( 'WC_Payment_Gateway' ) )
        return;

    include_once( 'woocommerce-carbide-probes-net30.php' );

    add_filter( 'woocommerce_payment_gateways', 'carbide_probes_add_net30_gateway' );
    function carbide_probes_add_net30_gateway( $methods ) {
        $methods[] = 'Carbide_Probes_Net30';
        return $methods;
    }
}


// Add custom action links
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'carbide_probes_woocommerce_customization_action_links' );
function carbide_probes_woocommerce_customization_action_links( $links )
{
    $plugin_links = array(
        '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout' ) . '">' . __( 'Settings', 'carbide-probes-woocommerce-customization' ) . '</a>'
    );

    return array_merge( $plugin_links, $links );
}
