<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * The User Account Shipping Method class.
 *
 * @class WC_User_Shipping_Method
 * @extends WC_Shipping_Method
 * @version 1.0.0
 * @category Class
 * @author Todd Miller <todd@rainydaymedia.net>
 */
class WC_User_Shipping_Method extends WC_Shipping_Method
{
    /** @const string Localization Context */
    const CONTEXT = 'woocommerce-user-shipping-account';

    /**
     * __construct function
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        $this->id = 'user_shipping_method';
        $this->method_title = __( 'Customer Shipping Account', self::CONTEXT );
        $this->title = __( 'Customer Shipping Account', self::CONTEXT );
        $this->method_description = __( 'Allows customers to enter their own shipping accounts', self::CONTEXT );

        // load settings form fields
        $this->init_form_fields();
        // load the settings
        $this->init_settings();

        // easily access the gateway settings
        foreach ( $this->settings as $key => $val ) {
            $this->$key = $val;
        }

        if ( is_admin() )
            add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
        else
            add_filter( 'woocommerce_cart_shipping_method_full_label', array( $this, 'filter_label' ), 99, 2 );
    }

    /**
     * init_form_fields function
     * builds the form fields for the method settings page
     *
     * @access public
     * @return void
     */
    public function init_form_fields()
    {
        $this->form_fields = array(
                'enabled' => array(
                        'title'   => __( 'Enable / Disable ', self::CONTEXT ),
                        'label'   => __( 'Enable this shipping method', self::CONTEXT ),
                        'type'    => 'checkbox',
                        'default' => 'no'
                    ),
                'title' => array(
                        'title'    => __( 'Title', self::CONTEXT ),
                        'type'     => 'text',
                        'desc_tip' => __( 'Shipping method title the customer will see during checkout', self::CONTEXT ),
                        'default'  => __( 'Customer Shipping Account', self::CONTEXT )
                    ),
                'description' => array(
                        'title'    => __( 'Description', self::CONTEXT ),
                        'type'     => 'textarea',
                        'desc_tip' => __( 'Shiping method description the customer will see during checkout', self::CONTEXT ),
                        'default'  => __( 'Use your own shipping account to take advantage of your negotiated rates.', self::CONTEXT ),
                        'css'      => 'max-width: 350px;'
                    )
            );
    }

    /**
     * calculate_shipping function
     * calculates the shipping cost and adds a rate to the rates array
     * for this method, no shipping cost is charged at the POS
     *
     * @access public
     * @param $package I don't know this isn't really relevant for this method
     * @return void
     */
    public function calculate_shipping( $package )
    {
        $rate = array(
                'id' => $this->id,
                'label' => __( 'Customer Shipping Account', self::CONTEXT ),
                'cost'  => '0',
                'taxes' => false,
                'calc_tax' => 'per_order'
            );

        $this->add_rate( $rate );
    }

    /**
     * filter_label function
     * filters out the "Free" text from the label and replaces it with
     * something more relevant to this method
     *
     * @access public
     * @param string $label The current label string
     * @param object $method The method object for this label
     * @return $label The updated label string
     */
    public function filter_label( $label, $method )
    {
        if ( $method->method_id == 'user_shipping_method' )
            return str_replace( 'Free', 'Billed Separately', $label);

        return $label;
    }
}
