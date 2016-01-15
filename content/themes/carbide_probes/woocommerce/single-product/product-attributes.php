<?php
/**
 * Product attributes
 *
 * Used by list_attributes() in the products class
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.1.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$has_row    = false;
$alt        = 1;
$attributes = $product->get_attributes();

ob_start();

?>
<table class="shop_attributes">

	<?php if ( $product->enable_dimensions_display() ) : ?>

		<?php if ( $product->has_weight() ) : $has_row = true; ?>
			<tr class="<?php if ( ( $alt = $alt * -1 ) == 1 ) echo 'alt'; ?>">
				<th><?php _e( 'Weight', 'woocommerce' ) ?></th>
				<td class="product_weight"><?php echo $product->get_weight() . ' ' . esc_attr( get_option( 'woocommerce_weight_unit' ) ); ?></td>
			</tr>
		<?php endif; ?>

		<?php if ( $product->has_dimensions() ) : $has_row = true; ?>
			<tr class="<?php if ( ( $alt = $alt * -1 ) == 1 ) echo 'alt'; ?>">
				<th><?php _e( 'Dimensions', 'woocommerce' ) ?></th>
				<td class="product_dimensions"><?php echo $product->get_dimensions(); ?></td>
			</tr>
		<?php endif; ?>

	<?php endif; ?>

    <?php
        // custom code from Todd Miller <todd@rainydaymedia.net>
        // re-order the attributes to put the OEM stuff at the end.
        if ( array_key_exists( 'pa_oem-manufacturer', $attributes ) )
            $attributes['pa_oem-manufacturer']['position'] = '7';
        if ( array_key_exists( 'pa_oem-model-number', $attributes ) )
            $attributes['pa_oem-model-number']['position'] = '8';
        if ( array_key_exists( 'pa_oem-part-number', $attributes ) )
            $attributes['pa_oem-part-number']['position'] = '9';

        $tmp = Array();
        foreach( $attributes as $ma ) {
            $tmp[] = $ma['position'];
        }

        array_multisort( $tmp, $attributes );
    ?>

	<?php foreach ( $attributes as $attribute ) :
		if ( empty( $attribute['is_visible'] ) || ( $attribute['is_taxonomy'] && ! taxonomy_exists( $attribute['name'] ) ) ) {
			continue;
        } else if ( empty ( wc_get_product_terms( $product->id, $attribute['name'], array( 'fields' => 'names' ) ) ) ) {
            // if there's no value in this attribute, don't display it
            continue;
		} else {
			$has_row = true;
		}
		?>
		<tr class="<?php if ( ( $alt = $alt * -1 ) == 1 ) echo 'alt'; ?>">
			<th><?php echo wc_attribute_label( $attribute['name'] ); ?></th>
			<td><?php
				if ( $attribute['is_taxonomy'] ) {

					$values = wc_get_product_terms( $product->id, $attribute['name'], array( 'fields' => 'names' ) );
                    //echo apply_filters( 'woocommerce_attribute', wpautop( wptexturize( implode( ', ', $values ) ) ), $attribute, $values );

                    // custom code from Todd Miller <todd@rainydaymedia.net>
                    // certain attributes have measurement units attached (either in or mm)
                    // we need to display the converted opposite unit as well
                    if ( $attribute['name'] === 'pa_length' ) {
                        $length_units = wc_get_product_terms( $product->id, 'pa_length-units', array( 'fields' => 'names' ) );
                        $converted    = rdm_convert( $values[0], $length_units[0] );
                        echo wpautop( $values[0] . ' ' . $length_units[0] . ' (' . $converted . ')' );

                    } else if ( $attribute['name'] === 'pa_ewl' ) {
                        $ewl_units = wc_get_product_terms( $product->id, 'pa_ewl-units', array( 'fields' => 'names' ) );
                        $converted = rdm_convert( $values[0], $ewl_units[0] );
                        echo wpautop( $values[0] . ' ' . $ewl_units[0] . ' (' . $converted . ')' );

                    } else if ( $attribute['name'] === 'pa_radius' ) {
                        $radius_units = wc_get_product_terms( $product->id, 'pa_radius-units', array( 'fields' => 'names' ) );
                        $converted = rdm_convert( $values[0], $radius_units[0] );
                        echo wpautop( $values[0] . ' ' . $radius_units[0] . ' (' . $converted . ')' );

                    } else if ( $attribute['name'] === 'pa_diameter' ) {
                        $diameter_units = wc_get_product_terms( $product->id, 'pa_diameter-units', array( 'fields' => 'names' ) );
                        $converted = rdm_convert( $values[0], $diameter_units[0] );
                        echo wpautop( $values[0] . ' ' . $diameter_units[0] . ' (' . $converted . ')' );

                    } else if ( $attribute['name'] === 'pa_ball-diameter' ) {
                        $ball_units = wc_get_product_terms( $product->id, 'pa_ball-units', array( 'fields' => 'names' ) );
                        $converted = rdm_convert( $values[0], $ball_units[0] );
                        echo wpautop( $values[0] . ' ' . $ball_units[0] . ' (' . $converted . ')' );

                    } else {
                        echo apply_filters( 'woocommerce_attribute', wpautop( wptexturize( implode( ', ', $values ) ) ), $attribute, $values );
                    }

				} else {

					// Convert pipes to commas and display values
					$values = array_map( 'trim', explode( WC_DELIMITER, $attribute['value'] ) );
					echo apply_filters( 'woocommerce_attribute', wpautop( wptexturize( implode( ', ', $values ) ) ), $attribute, $values );

				}
			?></td>
		</tr>
	<?php endforeach; ?>

</table>
<?php
if ( $has_row ) {
	echo ob_get_clean();
} else {
	ob_end_clean();
}

/**
 * converts the given value and measurement to the opposite measurement
 * eg. inches will be converted to mm and vice-versa
 */
function rdm_convert ($value, $units) {
    if ( $units === 'in' ) {
        $converted = number_format( $value / 0.03937, 4 );
        return $converted . ' mm';
    } else if ( $units === 'mm' ) {
        $converted = number_format( $value * 0.03937, 4 );
        return $converted . ' in';
    }
}
