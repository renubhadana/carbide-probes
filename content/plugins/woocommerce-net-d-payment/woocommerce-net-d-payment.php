<?php
/*
Plugin Name: Woocommerce Net D Payment
Plugin URI: https://gitlab.com/RDMCrew/woocommerce-net-d-payment
Description: Enables Net D payment options for WooCommerce.
Version: 1.0.0
Author: Todd Miller <todd@rainydaymedia.net>
Author URI: http://rainydaymedia.net
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * woocommerce_net_d_payments_init function
 * initialize the payment gateway and various hooks
 *
 * @return void
 */
function woocommerce_net_d_payments_init()
{
    // WC not enabled - don't bother
    if ( ! class_exists( 'WC_Payment_Gateway' ) )
        return;

    include_once( 'woocommerce-net-d.php' );

    add_filter( 'woocommerce_payment_gateways', 'woocommerce_net_d_payments_add_gateways' );

    if ( is_admin() && current_user_can( 'edit_users' ) ) {
        // need to trigger on two actions - one if the user is viewing their
        // own profile, and one if they're viewing someone else's
        add_action( 'edit_user_profile', 'woocommerce_net_d_payment_add_user_options', 99 );
        add_action( 'show_user_profile', 'woocommerce_net_d_payment_add_user_options', 99 );
        add_action( 'edit_user_profile_update', 'woocommerce_net_d_payment_save_user_options' );
        add_action( 'personal_options_update', 'woocommerce_net_d_payment_save_user_options' );
    }

    if ( ! is_admin() )
        add_filter( 'woocommerce_available_payment_gateways', 'woocommerce_net_d_payment_filter_gateways', 99 );
}
add_action( 'plugins_loaded', 'woocommerce_net_d_payments_init', 0 );

/**
 * woocommerce_net_d_payments_add_gateways function
 * adds the net d payment gateway to available gateways array
 *
 * @param array $methods The available payment gateways
 * @return array The new available gateways array
 */
function woocommerce_net_d_payments_add_gateways( $methods ) {
    $methods[] = 'WC_Gateway_Net_D'; // reference the class name here
    return $methods;
}

/**
 * woocommerce_net_d_payment_action_links function
 * adds a quick link to the net d payment settings page in the plugin's action links
 *
 * @param array $links The action links array
 * @return array The new action links array
 */
function woocommerce_net_d_payment_action_links( $links )
{
    $plugin_links = array(
        '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=wc_gateway_net_d' ) . '">' . __( 'Settings', 'woocommerce-net-d-payment' ) . '</a>'
    );

    return array_merge( $plugin_links, $links );
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'woocommerce_net_d_payment_action_links' );

/**
 * woocommerce_net_d_payment_add_user_options function
 * outputs the checkbox option to enable net d on the user profile page
 *
 * @param object $user The selected WP User object
 * @return void
 */
function woocommerce_net_d_payment_add_user_options( $user )
{
    $options = get_option( 'woocommerce_net_d_payment_settings' );

    // don't need the option if net d is enabled for all users
    if ( $options['all_users'] === 'yes' )
        return;

?>
    <h3>Net D Payment Options</h3>

    <table class="form-table">
        <tr>
            <th>
                <label>Enable Net D Payments</label>
            </th>
            <td>
                <input type="checkbox" id="woocommerce_net_d" name="woocommerce_net_d" value="true" <?php if (get_the_author_meta( 'woocommerce_net_d', $user->ID ) ) echo 'checked'; ?> />
            </td>
        </tr>
    </table>

<?php
}

/**
 * woocommerce_net_d_payment_save_user_options function
 * updates the user's net d setting
 *
 * @param int $user_id The ID of the user being updated
 * @return void
 */
function woocommerce_net_d_payment_save_user_options( $user_id )
{
    update_user_meta( $user_id, 'woocommerce_net_d', $_POST['woocommerce_net_d'] );
}

/**
 * woocommerce_net_d_payment_filter_gateways function
 * filters out the net d payment gateway if it is not available for the user
 *
 * @param array $gateways The available payment gateways
 * @return array The new available gateways array
 */
function woocommerce_net_d_payment_filter_gateways( $gateways )
{
    global $user_ID;
    $options = get_option( 'woocommerce_net_d_payment_settings' );

    if ( array_key_exists( 'net_d_payment', $gateways ) && ( ! get_the_author_meta( 'woocommerce_net_d', $user_ID ) && $options['all_users'] === 'no' ) )
        unset( $gateways['net_d_payment'] );

    return $gateways;
}
