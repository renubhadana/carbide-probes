<?php

if ( ! defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

class WC_MS_Admin {

    private $wcms;

    public function __construct(WC_Ship_Multiple $wcms) {
        $this->wcms = $wcms;

        // settings styles and scripts
        add_action( 'admin_enqueue_scripts', array( $this, 'settings_scripts' ) );

        // save settings handler
        add_action( 'admin_post_wcms_update', array( $this, 'save_settings' ) );

        add_filter( 'woocommerce_shipping_settings', array( $this, 'shipping_settings' ) );
        add_filter( 'woocommerce_account_settings', array( $this, 'account_settings' ) );
    }

    public static function settings_scripts() {
        $screen = get_current_screen();

        if ( $screen->id != 'woocommerce_page_wc-settings' ) {
            return;
        }

        wp_enqueue_script( 'wc-product-search', plugins_url( 'js/product-search.js', WC_Ship_Multiple::FILE ), array('jquery') );
        wp_localize_script( 'wc-product-search', 'wcms_product_search', array(
            'security' => wp_create_nonce("search-products")
        ) );

    }

    /**
     * unused
     */
    public function save_settings() {
        $settings       = array();
        $methods        = (isset($_POST['shipping_methods'])) ? $_POST['shipping_methods'] : array();
        $products       = (isset($_POST['products'])) ? $_POST['products'] : array();
        $categories     = (isset($_POST['categories'])) ? $_POST['categories'] : array();
        $duplication    = (isset($_POST['cart_duplication']) && $_POST['cart_duplication'] == 1) ? true : false;

        if ( isset($_POST['lang']) && is_array($_POST['lang']) ) {
            update_option( 'wcms_lang', $_POST['lang'] );
        }

        foreach ( $methods as $id => $method ) {
            $row_products   = (isset($products[$id])) ? $products[$id] : array();
            $row_categories = (isset($categories[$id])) ? $categories[$id] : array();

            // there needs to be at least 1 product or category per row
            if ( empty($row_categories) && empty($row_products) ) {
                continue;
            }

            $settings[] = array(
                'products'  => $row_products,
                'categories'=> $row_categories,
                'method'    => $method
            );
        }

        update_option( $this->wcms->meta_key_settings, $settings );
        update_option( '_wcms_cart_duplication', $duplication );

        wp_redirect( add_query_arg( 'saved', 1, 'admin.php?page=wc-ship-multiple-products' ) );
        exit;
    }

    public function shipping_settings($settings) {
        $section_end = array_pop($settings);
        $shipping_table = array_pop($settings);
        $settings[] = array(
            'name'  =>  __( 'Multiple Shipping Addresses', 'wc_shipping_multiple_address' ),
            'desc'  => __( 'Page contents: [woocommerce_select_multiple_addresses] Parent: "Checkout"', 'woocommerce' ),
            'id'    => 'woocommerce_multiple_addresses_page_id',
            'type'  => 'single_select_page',
            'std'   => true,
            'class' => 'chosen_select wc-enhanced-select',
            'css'   => 'min-width:300px;',
            'desc_tip' => false
        );
        $settings[] = $shipping_table;
        $settings[] = $section_end;

        return $settings;
    }

    public function account_settings($settings) {
        foreach ( $settings as $idx => $setting ) {
            if ( $setting['type'] == 'sectionend' && $setting['id'] == 'account_page_options' ){
                $front = array_slice( $settings, 0, $idx );
                $front[] = array(
                    'name'  =>  __( 'Account Shipping Addresses', 'wc_shipping_multiple_address' ),
                    'desc'  => __( 'Page contents: [woocommerce_account_addresses] Parent: "My Account"', 'woocommerce' ),
                    'id'    => 'woocommerce_account_addresses_page_id',
                    'type'  => 'single_select_page',
                    'std'   => true,
                    'class' => 'chosen_select wc-enhanced-select',
                    'css'   => 'min-width:300px;',
                    'desc_tip' => false
                );
                array_splice( $settings, 0, $idx, $front );
                break;
            }
        }

        return $settings;
    }

}