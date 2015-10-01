<?php

if ( ! defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

class WC_MS_Gifts {

    private $wcms;

    public function __construct( WC_Ship_Multiple $wcms ) {

        $this->wcms = $wcms;

        add_action( 'wc_ms_shipping_package_block', array( __CLASS__, 'render_gift_form'), 10, 2 );

        add_action( 'woocommerce_checkout_update_order_meta', array( __CLASS__, 'store_order_gift_data'), 20, 2 );

        // Modify the packages, shipping methods and addresses in the session
        add_filter( 'wc_ms_checkout_session_packages', array( __CLASS__, 'apply_gift_data_to_packages' ), 30 );

        add_action( 'wc_ms_order_package_block_before_address', array( __CLASS__, 'render_gift_data'), 10, 3 );
    }

    /**
     * Show the gift checkbox on the shipping packages blocks
     */
    public static function render_gift_form( $loop, $package ) {
        global $wcms;

        if ( isset( $wcms->gateway_settings['gift_packages'] ) && $wcms->gateway_settings['gift_packages'] != 'yes' )
            return;

        ?>
        <div class="gift-form">
            <p>
                <label>
                    <input type="checkbox" class="chk-gift" name="shipping_gift[<?php echo $loop; ?>]" value="yes" data-index="<?php echo $loop; ?>" />
                    <?php _e( 'This is a gift', 'wc_shipping_multiple_address' ); ?>
                </label>
            </p>
        </div>

    <?php
    }

    /**
     * Modify the 'wcms_packages' session data to attach gift data from POST
     * and at the same time, populate the WC_Gift_Checkout::gifts array
     */
    public static function apply_gift_data_to_packages( $packages ) {

        if (! isset($_POST['shipping_gift']) || empty($_POST['shipping_gift']) )
            return $packages;


        foreach ( $_POST['shipping_gift'] as $idx => $value ) {

            if ( $value != 'yes' ) {
                continue;
            }

            if ( !isset( $packages[ $idx ] ) ) {
                continue;
            }

            $packages[ $idx ]['gift'] = true;

        }

        return $packages;

    }

    public static function store_order_gift_data( $order_id ) {

        if (! isset($_POST['shipping_gift']) || empty($_POST['shipping_gift']) )
            return;

        $packages = get_post_meta( $order_id, '_wcms_packages', true );

        foreach ( $_POST['shipping_gift'] as $idx => $value ) {

            if ( $value != 'yes' )
                continue;

            if (! array_key_exists( $idx, $packages ) )
                continue;

            update_post_meta( $order_id, '_gift_'. $idx, true );

        }
    }

    public static function render_gift_data( $order, $package, $package_index ) {
        $packages       = get_post_meta( $order->id, '_wcms_packages', true );
        $order_is_gift  = (get_post_meta( $order->id, '_gift_'. $package_index, true ) == true ) ? true : false;

        if ( $order_is_gift && count( $packages ) == 1 ) {
            // inject the gift data into the only package
            // because multishipping doesn't process gift
            // data when there's only one package
            $package['gift'] = true;
        }

        if ( isset( $package['gift'] ) && true == $package['gift'] ) {
            ?>
            <div class="gift-package">
                <h5><div class="dashicons dashicons-yes"></div><?php _e('This is a Gift', 'wc_shipping_multiple_address'); ?></h5>
            </div>
            <?php

        }

        return;
    }

}