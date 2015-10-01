<?php

if ( empty($addresses) ) {
    echo '<p>'. __('No address on file. Please add one below.', 'wc_shipping_multiple_address') .'</p>';
} else {
    /* @var $woocommerce Woocommerce */

    echo '<div class="address-container">';
    foreach ( $addresses as $idx => $address ) {

        WC_MS_Compatibility::wc_get_template(
            'address-block.php',
            array(
                'idx'           => $idx,
                'address'       => $address,
                'woocommerce'   => $woocommerce,
                'checkout'      => $checkout,
                'shipFields'    => $shipFields
            ),
            'multi-shipping',
            dirname( WC_Ship_Multiple::FILE ) .'/templates/'
        );

    }
        echo '<div class="clear"></div>';
    echo '</div>';

}

?>

<hr />

<?php
$address_id = '-1';
$address    = array();

if ( isset($_GET['edit']) ):
    $address_id = intval($_GET['edit']);
    $address    = $addresses[ $address_id ];

?>
    <h2><?php _e('Edit address', 'wc_shipping_multiple_address'); ?></h2>
<?php else: ?>
    <h2><?php _e('Add a new address', 'wc_shipping_multiple_address'); ?></h2>
<?php endif; ?>

<form id="add_address_form">
    <div class="shipping_address address_block" id="shipping_address">
        <?php do_action('woocommerce_before_checkout_shipping_form', $checkout); ?>

        <div class="address-column">
            <?php
            foreach ($shipFields as $key => $field) :
                $val    = (isset($address[$key])) ? $address[$key] : '';
                $id     = rtrim( str_replace( '[', '_', $key ), ']' );
                $field['return'] = true;

                echo str_replace( 'name="'. $key .'"', 'name="address['. $id .']"', woocommerce_form_field( $key, $field, $val ) );
            endforeach;

            do_action('woocommerce_after_checkout_shipping_form', $checkout);
            ?>
            <input type="hidden" name="id" id="address_id" value="<?php echo $address_id; ?>" />
            <input type="hidden" name="return" value="list" />
        </div>

    </div>

    <?php if ( $address_id > -1 ): ?>
        <input type="submit" class="button alt" id="use_address" value="<?php _e('Update Address', 'wc_shipping_multiple_address'); ?>" />
    <?php else: ?>
        <input type="submit" class="button alt" id="use_address" value="<?php _e('Save Address', 'wc_shipping_multiple_address'); ?>" />
    <?php endif; ?>

</form>