<?php
/**
 * The Net D Payment Gateway class.
 *
 * @class WC_Gateway_Net_D
 * @extends WC_Payment_Gateway
 * @version 1.0.0
 * @category Class
 * @author Todd Miller <todd@rainydaymedia.net>
 */
class WC_Gateway_Net_D extends WC_Payment_Gateway
{
    /** @const string Localization Context */
    const CONTEXT = 'woocommerce-net-d-payment';

    /**
     * __construct function.
     *
     * @access public
     * @return void
     */
    function __construct()
    {
        $this->id                 = 'net_d_payment';
        $this->method_title       = __( 'Net D Payment', self::CONTEXT );
        $this->method_description = __( 'Net D Payment Option for WooCommerce', self::CONTEXT );
        $this->title              = __( 'Net D', self::CONTEXT );
        $this->icon               = '';
        $this->has_fields         = true;
        $this->supports           = array();

        // load settings form fields
        $this->init_form_fields();
        // load the settings
        $this->init_settings();

        // easily access the gateway settings
        foreach ( $this->settings as $key => $val ) {
            $this->$key = $val;
        }

        if ( is_admin() )
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        else
            add_action( 'woocommerce_before_checkout_form', array( $this, 'enqueue_checkout_styles' ) );
    }

    /**
     * enqueue_checkout_styles function
     * adds styles for the net d payment fields on the checkout form
     *
     * @access public
     * @return void
     */
    public function enqueue_checkout_styles()
    {
        wp_enqueue_style( 'woocommerce-net-d-payment-styles', plugin_dir_url( __FILE__ ) . '/woocommerce-net-d-style.css', array(), '1.0.0' );
    }

    /**
     * init_form_fields function
     * builds the form fields for the gateway settings page
     *
     * @access public
     * @return void
     */
    public function init_form_fields()
    {
        $this->form_fields = array(
                'enabled' => array(
                        'title'   => __( 'Enable / Disable ', self::CONTEXT ),
                        'label'   => __( 'Enable this payment gateway', self::CONTEXT ),
                        'type'    => 'checkbox',
                        'default' => 'no'
                    ),
                'days' => array(
                         'title'       => __( 'Enabled Days', self::CONTEXT ),
                         'type'        => 'multiselect',
                         'description' => __( 'Due days available for Net D payments.', self::CONTEXT ),
                         'css'         => 'min-width: 350px;',
                         'options'     => array(
                              '10' => 10,
                              '15' => 15,
                              '30' => 30,
                              '60' => 60
                         )
                    ),
                'all_users' => array(
                        'title'       => __( 'Enabled for All Users', self::CONTEXT ),
                        'type'        => 'checkbox',
                        'desc_tip'    => __( 'Allows all users to pay with Net D, otherwise is enabled per user in their profile.', self::CONTEXT ),
                        'default'     => 0
                    ),
                'title' => array(
                        'title'    => __( 'Title', self::CONTEXT ),
                        'type'     => 'text',
                        'desc_tip' => __( 'Payment title the customer will see during checkout', self::CONTEXT ),
                        'default'  => __( 'Net D Payment', self::CONTEXT )
                    ),
                'description' => array(
                        'title'    => __( 'Description', self::CONTEXT ),
                        'type'     => 'textarea',
                        'desc_tip' => __( 'Payment description the customer will see during checkout', self::CONTEXT ),
                        'default'  => __( 'Pay using Net D and receive an invoice due in the selected number of days.', self::CONTEXT ),
                        'css'      => 'max-width: 350px;'
                    )
            );
    }

    /**
     * process_payment function
     * gateway payment handler after customer submits checkout form
     *
     * @access public
     * @param int $order_id ID of the new order
     * @return array The processing results array
     */
    public function process_payment( $order_id )
    {
        global $woocommerce;

        $order    = new WC_Order( $order_id );
        $due      = $_POST['woocommerce_net_d_due_date'];
        // formatted due date
        $due_date = date('F d, Y', strtotime('+'.$due.' days'));

        // add the selected due date to the order
        add_post_meta( $order_id, '_payment_net_d_days', $due );
        // update the payment title so it has all the new info
        update_post_meta( $order_id, '_payment_method_title', __( 'Net '.$due, self::CONTEXT ) . '  (due '.$due_date.')' );
        // add a note for the backend administration
        $order->add_order_note( __( 'Payment due in '.$due.' days', self::CONTEXT ) . ' ('.$due_date.')' );
        // since we aren't accepting monies, just complete the order
        $order->payment_complete();

        return array(
            'result'   => 'success',
            'redirect' => $this->get_return_url( $order ),
        );
    }

    /**
     * payment_fields function
     * adds the radio selection for enabled days
     *
     * @access public
     * @return void
     */
    public function payment_fields()
    {
?>
        <div class="form-row">
            <label><?php echo $this->description; ?></label>
            <label class="net-d-payment-label">Due Date <span class="required">*</span></label>

        <?php foreach ( $this->days as $day ) : ?>
            <label class="net-d-payment-radio"><input type="radio" name="woocommerce_net_d_due_date" value="<?php echo $day; ?>" /><?php echo $day; ?> Days</label>
        <?php endforeach; ?>

        </div>
<?php
    }

    /**
     * validate_fields function
     * verifies that a selection was made for the due date
     *
     * @access public
     * @return boolean false if no selection, true otherwise
     */
    public function validate_fields()
    {
        if ( ! isset( $_POST['woocommerce_net_d_due_date'] ) ) {
            wc_add_notice( '<strong>Net D Payment</strong> due date is required.', 'error' );
            return false;
        }

        return true;
    }
}
