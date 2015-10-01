<div class="address-block">
<?php
global $woocommerce;
$page_id    = woocommerce_get_page_id( 'account_addresses' );
$form_link  = get_permalink( $page_id );

$formatted_address = wcms_get_formatted_address( $address );
if ( $formatted_address ) {
    echo '<address>'.$formatted_address.'</address>';

    $edit_link = add_query_arg( array('edit' =>  $idx), $form_link );
?>
    <div class="buttons">
        <a class="button" href="<?php echo $edit_link; ?>"><?php _e('Edit', 'wc_shipping_multiple_address'); ?></a>
        <a class="button ms_delete_address" data-idx="<?php echo $idx; ?>" href="#"><?php _e('Delete', 'wc_shipping_multiple_address'); ?></a>
    </div>
<?php
}
?>
</div>