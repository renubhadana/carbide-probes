<?php
/**
 *
 */

class Carbide_Probes_Net30 extends WC_Payment_Gateway {

    function __construct()
    {
        $this->id = 'carbide_probes_net30';
        $this->method_title = __( 'Carbide Probes Net30', 'carbide-probes-woocommerce-customization' );
        $this->method_description = __( 'Carbide Probes Net30 Payment Option for WooCommerce', 'carbide-probes-woocommerce-customization' );
        $this->title = __ ( 'Carbide Probes Net30', 'carbide-probes-woocommerce-customization' );
        $this->icon = plugin_dir_url( __FILE__ ) . '/img/net30.png';
        $this->has_fields = true;
        $this->supports = array();
        $this->init_form_fields();
        $this->init_settings();
        foreach ( $this->settings as $key => $val ) {
            $this->$key = $val;
        }

        add_action( 'admin_notices', array( $this, 'do_ssl_check' ) );

        if ( is_admin() ) {
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        }
    }

    public function init_form_fields()
    {
        $this->form_fields = array(
                'enabled' => array(
                        'title' => __( 'Enable / Disable ', 'carbide-probes-woocommerce-customization' ),
                        'label' => __( 'Enable this payment gateway', 'carbide-probes-woocommerce-customization' ),
                        'type' => 'checkbox',
                        'default' => 'no'
                    ),

                'title' => array(
                        'title' => __( 'Title', 'carbide-probes-woocommerce-customization' ),
                        'type' => 'text',
                        'desc_tip' => __( 'Payment title the customer will see during checkout', 'carbide-probes-woocommerce-customization' ),
                        'default' => __( 'Net30', 'carbide-probes-woocommerce-customization' )
                    ),
                'description' => array(
                        'title' => __( 'Description', 'carbide-probes-woocommerce-customization' ),
                        'type' => 'textarea',
                        'desc_tip' => __( 'Payment description the customer will see during checkout', 'carbide-probes-woocommerce-customization' ),
                        'default' => __( 'Pay using Net30 and receive an invoice due in 30 days', 'carbide-probes-woocommerce-customization' ),
                        'css' => 'max-width: 350px;'
                    )
            );
    }
}
