<?php

if (! defined('ABSPATH') )
    exit;

function wcms_get_formatted_address( $address ) {
    foreach ( $address as $key => $value ) {
        if ( strpos( $key, 'shipping_' ) === false ) {
            $address[ 'shipping_'. $key ] = $value;
        }

        $addr_key = str_replace( 'shipping_', '', $key );
        $address[ $addr_key ] = $value;
    }

    return apply_filters( 'wc_ms_formatted_address', WC()->countries->get_formatted_address( $address ), $address );
}

function wcms_count_real_cart_items() {
    global $woocommerce;

    $count = 0;

    foreach ( $woocommerce->cart->get_cart() as $cart_item_key => $cart_item ) {

        if ( !$cart_item['data']->needs_shipping() )
            continue;

        if ( isset($cart_item['bundled_by']) && !empty($cart_item['bundled_by']) )
            continue;

        if ( isset($cart_item['composite_parent']) && !empty($cart_item['composite_parent']) )
            continue;

        $count++;
    }

    return $count;
}

function wcms_get_real_cart_items() {
    global $woocommerce;

    $items = array();

    foreach ( $woocommerce->cart->get_cart() as $cart_item_key => $cart_item ) {

        if ( !$cart_item['data']->needs_shipping() )
            continue;

        if ( isset($cart_item['bundled_by']) && !empty($cart_item['bundled_by']) )
            continue;

        if ( isset($cart_item['composite_parent']) && !empty($cart_item['composite_parent']) )
            continue;

        $items[$cart_item_key] = $cart_item;
    }

    return $items;
}

function wcms_get_product( $product_id ) {
    if ( function_exists( 'get_product' ) ) {
        return get_product( $product_id );
    } else {
        return new WC_Product( $product_id );
    }
}

function wcms_session_get( $name ) {
    global $woocommerce;

    if ( isset( $woocommerce->session ) ) {
        // WC 2.0
        if ( isset( $woocommerce->session->$name ) ) return $woocommerce->session->$name;
    } else {
        // old style
        if ( isset( $_SESSION[ $name ] ) ) return $_SESSION[ $name ];
    }

    return null;
}

function wcms_session_isset( $name ) {
    global $woocommerce;

    if ( isset($woocommerce->session) ) {
        // WC 2.0
        return (isset( $woocommerce->session->$name ));
    } else {
        return (isset( $_SESSION[$name] ));
    }
}

function wcms_session_set( $name, $value ) {
    global $woocommerce;

    if ( isset( $woocommerce->session ) ) {
        // WC 2.0
        unset( $woocommerce->session->$name );
        $woocommerce->session->$name = $value;
    } else {
        // old style
        $_SESSION[ $name ] = $value;
    }
}

function wcms_session_delete( $name ) {
    global $woocommerce;

    if ( isset( $woocommerce->session ) ) {
        // WC 2.0
        unset( $woocommerce->session->$name );
    } else {
        // old style
        unset( $_SESSION[ $name ] );
    }
}