<?php
/*
Plugin Name: Woocommerce User Shipping Account
Plugin URI: http://carbideprobes.com
Description: Adds a Customer Shipping Account method and allows users to add a shipping account to their profile.
Version: 1.0.0
Author: Todd Miller <todd@rainydaymedia.net>
Author URI: http://rainydaymedia.net
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * woocommerce_user_shipping_account_init function
 * initialize the shipping method and various hooks
 *
 * @return void
 */
function woocommerce_user_shipping_account_init()
{
    // WC not enabled - don't bother
    if ( ! class_exists( 'WC_Shipping_Method' ) )
        return;

    include_once( 'wc-user-shipping-method.php' );

    add_filter( 'woocommerce_shipping_methods', 'woocommerce_user_shipping_account_add_method' );

    if ( is_admin() && current_user_can( 'edit_users' ) ) {
        // hooks for an admin editing a user profile
        add_action( 'edit_user_profile', 'woocommerce_user_shipping_account_add_user_options', 99 );
        add_action( 'edit_user_profile_update', 'woocommerce_user_shipping_account_save_user_options' );
    }

    // hooks for a user editing their own profile
    add_action( 'show_user_profile', 'woocommerce_user_shipping_account_add_user_options', 99 );
    add_action( 'personal_options_update', 'woocommerce_user_shipping_account_save_user_options' );

    // hook into the checkout process to validate the user shipping account
    add_action( 'woocommerce_checkout_process', 'woocommerce_user_shipping_account_validation' );
    // hook in to add a note with the user's shipping account info
    add_action( 'woocommerce_checkout_order_processed', 'woocommerce_user_shipping_account_update_order', 99, 2 );
    // hook in to add the customer shipping account number to the order 'totals'
    add_filter( 'woocommerce_get_order_item_totals', 'woocommerce_user_shipping_account_update_emails', 99, 2 );
}
add_action( 'plugins_loaded', 'woocommerce_user_shipping_account_init', 0 );

/**
 * woocommerce_user_shipping_account_add_method function
 * adds the shipping method to the available methods array
 *
 * @param array $methods The available shipping methods
 * @return array The new available methods array
 */
function woocommerce_user_shipping_account_add_method( $methods ) {
    $methods[] = 'WC_User_Shipping_Method';
    return $methods;
}

/**
 * woocommerce_user_shipping_account_action_links function
 * adds a quick link to the user shipping account settings page in the plugin's action links
 *
 * @param array $links The action links array
 * @return array The new action links array
 */
function woocommerce_user_shipping_account_action_links( $links )
{
    $plugin_links = array(
        '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=shipping&section=wc_user_shipping_method' ) . '">' . __( 'Settings', 'woocommerce-user-shipping-account' ) . '</a>'
    );

    return array_merge( $plugin_links, $links );
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'woocommerce_user_shipping_account_action_links' );

/**
 * woocommerce_user_shipping_account_add_user_options function
 * outputs the options on the user profile page for the shipping company and account
 *
 * @param object $user The selected WP User object
 * @return void
 */
function woocommerce_user_shipping_account_add_user_options( $user )
{
?>

    <h3>Customer Shipping Account</h3>

    <table class="form-table">
        <tr>
            <th>
                <label>Shipping Company</label>
            </th>
            <td>
                <select id="woocommerce_user_shipping_company" name="woocommerce_user_shipping_company">
                    <option value="ups" <?php if (get_the_author_meta( 'woocommerce_user_shipping_company', $user->ID ) == 'ups' ) echo 'selected'; ?>>UPS</option>
                    <option value="fedex" <?php if (get_the_author_meta( 'woocommerce_user_shipping_company', $user->ID ) == 'fedex' ) echo 'selected'; ?>>FedEx</option>
                </select>
            </td>
        </tr>
        <tr>
            <th>
                <label>Account Number</label>
            </th>
            <td>
                <input type="text" id="woocommerce_user_shipping_account" name="woocommerce_user_shipping_account" value="<?php the_author_meta( 'woocommerce_user_shipping_account', $user->ID ); ?>" />
            </td>
        </tr>
    </table>

<?php
}

/**
 * woocommerce_user_shipping_account_save_user_options function
 * updates the user's shipping account settings
 *
 * @param int $user_id The ID of the user being updated
 * @return void
 */
function woocommerce_user_shipping_account_save_user_options( $user_id )
{
    update_user_meta( $user_id, 'woocommerce_user_shipping_company', $_POST['woocommerce_user_shipping_company'] );
    update_user_meta( $user_id, 'woocommerce_user_shipping_account', esc_html( $_POST['woocommerce_user_shipping_account'] ) );
}

/**
 * woocommerce_user_shipping_account_validation function
 * verifies the user actually has an account set up in their profile
 *
 * @return void
 */
function woocommerce_user_shipping_account_validation()
{
    $method  = isset( $_POST['shipping_method'] ) ? $_POST['shipping_method'] : '';

    // only worried about this if the selected method is this one
    if ( isset( $method ) && $method[0] == 'user_shipping_method' ) {
        //$user_id = get_current_user_id();
        //$shipper = get_the_author_meta( 'woocommerce_user_shipping_company', $user_id );
        //$account = get_the_author_meta( 'woocommerce_user_shipping_account', $user_id );
        $account = $_POST['shipping_customer_account'];

        if ( $account == '' ) {
            wc_add_notice( 'Please enter your <strong>shipping account number</strong>.', 'error' );
        }
    }
}

/**
 * woocommerce_user_shipping_account_update_order function
 * adds the user's shipping account into to the order for easy access
 *
 * @param int $order_id The ID of the new order
 * @param array $posted The processed array of the posted form fields
 * @return void
 */
function woocommerce_user_shipping_account_update_order( $order_id, $posted )
{
    $order = new WC_Order( $order_id );

    // only worried about this if the selected method is this one
    if ( isset( $posted['shipping_method'] ) && $posted['shipping_method'][0] == 'user_shipping_method' ) {
        $account = $_POST['shipping_customer_account'];

        // add the customer's shipping account to the order
        add_post_meta( $order_id, '_customer_shipping_account', $account );
        $order->add_order_note( __( 'Customer Shipping Account: ', 'woocommerce-user-shipping-account' ) . ' ' . $account );
    }
}

/**
 * woocommerce_user_shipping_account_update_emails function
 * inserts the customer shipping account into the shipping method in all emails
 *
 * @param array $fields The current order totals fields array
 * @param object $order The instance of this order's object
 * @return array The updated $fields object
 */
function woocommerce_user_shipping_account_update_emails( $fields, $order )
{
    if ( $fields['shipping']['value'] == 'Customer Shipping Account' ) {
        $fields['shipping']['value'] = $fields['shipping']['value'] . ' (' . get_post_meta( $order->id, '_customer_shipping_account', true ) . ')';
    }

    return $fields;
}
