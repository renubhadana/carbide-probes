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
add_action( 'plugins_loaded', 'spyr_authorizenet_aim_init', 0 );
function spyr_authorizenet_aim_init() {}


// Add custom action links
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'spyr_authorizenet_aim_action_links' );
function spyr_authorizenet_aim_action_links( $links ) {}
