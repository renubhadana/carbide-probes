<?php

if ( ! defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

class WC_MS_Notes {

    private $wcms;

    public function __construct( WC_Ship_Multiple $wcms ) {
        $this->wcms = $wcms;

        add_action( 'wc_ms_shipping_package_block', array( __CLASS__, 'render_note_form'), 10, 2 );

        add_action( 'woocommerce_checkout_update_order_meta', array( __CLASS__, 'store_order_notes'), 20, 2 );

        // Modify the packages, shipping methods and addresses in the session
        add_filter( 'wc_ms_checkout_session_packages', array( __CLASS__, 'apply_notes_to_packages' ), 30 );

        add_action( 'wc_ms_order_package_block_before_address', array( __CLASS__, 'render_notes'), 11, 3 );
        add_action( 'wc_ms_order_package_block_before_address', array( __CLASS__, 'render_dates'), 11, 3 );
    }

    /**
     * Show the note checkbox on the shipping packages blocks
     */
    public static function render_note_form( $loop, $package ) {
        global $wcms;

        if ( !isset( $wcms->gateway_settings['checkout_notes'] ) ) {
            $wcms->gateway_settings['checkout_notes'] = 'yes';
        }

        $show_notes = ( !empty($wcms->gateway_settings['checkout_notes']) && $wcms->gateway_settings['checkout_notes'] == 'yes' ) ? true : false;
        $show_datepicker = ( !empty($wcms->gateway_settings['checkout_datepicker']) && $wcms->gateway_settings['checkout_datepicker'] == 'yes' ) ? true : false;

        if ( $show_datepicker ):
            $value    = '';
            $postdata = array();

            if ( !empty( $_POST['post_data'] ) ) {
                parse_str( $_POST['post_data'], $postdata );
            }

            if ( isset( $postdata['shipping_date'] ) && isset( $postdata['shipping_date'][ $loop ]) ) {
                $value = $postdata['shipping_date'][ $loop ];
            }
        ?>
        <div class="datepicker-form">
            <p>
                <label>
                    <?php _e( 'Shipping Date', 'wc_shipping_multiple_address' ); ?>
                </label>
                <input type="text" class="datepicker ms_shipping_date" name="shipping_date[<?php echo $loop; ?>]" data-index="<?php echo $loop; ?>" value="<?php echo esc_attr( $value ); ?>" />
            </p>
        </div>
        <?php
        endif;

        if ( $show_notes ):
        ?>
        <div class="note-form">
            <p>
                <label>
                    <?php _e( 'Note', 'wc_shipping_multiple_address' ); ?>
                </label>
                <textarea name="shipping_note[<?php echo $loop; ?>]" rows="2" cols="30" class="ms_shipping_note" data-index="<?php echo $loop; ?>"></textarea>
            </p>
        </div>
        <?php
        endif;

    }

    /**
     * Modify the 'wcms_packages' session data to attach notes from POST
     */
    public static function apply_notes_to_packages( $packages ) {

        if ( !empty($_POST['shipping_note']) ) {
            foreach ( $_POST['shipping_note'] as $idx => $value ) {

                if ( !isset( $packages[ $idx ] ) ) {
                    continue;
                }

                $packages[ $idx ]['note'] = esc_html( $value );

            }
        }

        if ( !empty($_POST['shipping_date']) ) {
            foreach ( $_POST['shipping_date'] as $idx => $value ) {

                if ( !isset( $packages[ $idx ] ) ) {
                    continue;
                }

                $packages[ $idx ]['date'] = esc_html( $value );

            }
        }

        return $packages;

    }

    public static function store_order_notes( $order_id ) {
        $packages = get_post_meta( $order_id, '_wcms_packages', true );

        if ( !empty($_POST['shipping_note']) ) {


            foreach ( $_POST['shipping_note'] as $idx => $value ) {

                if (! array_key_exists( $idx, $packages ) )
                    continue;

                update_post_meta( $order_id, '_note_'. $idx, true );

            }
        }

        if ( !empty($_POST['shipping_date']) ) {


            foreach ( $_POST['shipping_date'] as $idx => $value ) {

                if (! array_key_exists( $idx, $packages ) )
                    continue;

                update_post_meta( $order_id, '_date_'. $idx, true );

            }
        }

    }

    public static function render_notes( $order, $package, $package_index ) {

        if ( isset( $package['note'] ) && !empty( $package['note'] ) ) {
        ?>
            <ul class="order_notes">
                <li class="note">
                    <div class="note_content">
                        <?php echo esc_html( $package['note'] ); ?>
                    </div>
                </li>
            </ul>
        <?php
        }

        return;
    }

    public static function render_dates( $order, $package, $package_index ) {

        if ( isset( $package['date'] ) && !empty( $package['date'] ) ) {
            ?>
            <ul class="order_notes">
                <li class="note">
                    <div class="note_content">
                        <?php printf( __('Shipping Date: %s', 'wc_shipping_multiple_address'), esc_html( $package['date'] ) ); ?>
                    </div>
                </li>
            </ul>
        <?php
        }

        return;
    }

}