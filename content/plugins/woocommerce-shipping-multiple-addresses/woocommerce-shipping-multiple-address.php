<?php
/**
  * Plugin Name: WooCommerce Ship to Multiple Addresses
  * Plugin URI: http://woothemes.com/woocommerce
  * Description: Allow customers to ship orders with multiple products or quantities to separate addresses instead of forcing them to place multiple orders for different delivery addresses.
  * Version: 3.3.2
  * Author: 75nineteen Media LLC
  * Author URI: http://www.75nineteen.com
  * Text domain: wc_shipping_multiple_address

  * Copyright 2015 75nineteen Media LLC.  (email : scott@75nineteen.com)
  * 
  * This program is free software: you can redistribute it and/or modify
  * it under the terms of the GNU General Public License as published by
  * the Free Software Foundation, either version 3 of the License, or
  * (at your option) any later version.
  * 
  * This program is distributed in the hope that it will be useful,
  * but WITHOUT ANY WARRANTY; without even the implied warranty of
  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  * GNU General Public License for more details.
  * 
  * You should have received a copy of the GNU General Public License
  * along with this program.  If not, see <http://www.gnu.org/licenses/>.
  */

/**
 * Required functions
 */
if ( ! function_exists( 'woothemes_queue_update' ) )
	require_once( 'woo-includes/woo-functions.php' );

require_once( 'class.ms_compat.php' );

/**
 * Plugin updates
 */
woothemes_queue_update( plugin_basename( __FILE__ ), 'aa0eb6f777846d329952d5b891d6f8cc', '18741' );

if ( is_woocommerce_active() ) {

    /**
     * Localisation
     **/
    load_plugin_textdomain( 'wc_shipping_multiple_address', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

    class WC_Ship_Multiple {

        const FILE = __FILE__;

        public $checkout;
        public $notes;
        public $gifts;

        public $meta_key_order      = '_shipping_methods';
        public $meta_key_settings   = '_shipping_settings';
        public $settings            = null;
        public $gateway_settings    = null;
        public static $lang         = array(
            'notification'  => 'You may use multiple shipping addresses on this cart',
            'btn_items'     => 'Set Addresses'
        );

        public function __construct() {
            // install
            register_activation_hook(__FILE__, array( $this, 'install' ) );

            // load the shipping options
            $this->settings = get_option( $this->meta_key_settings, array());

            /* WCMS Front */

            add_filter( 'body_class', array( $this, 'output_body_class' ) );
            add_action( 'wp_enqueue_scripts', array( $this, 'front_scripts' ), 1 );
            add_action( 'woocommerce_view_order', array( $this, 'view_order' ) );
            add_action( 'woocommerce_email_after_order_table', array( $this, 'email_shipping_table' ) );
            add_filter( 'woocommerce_cart_shipping_packages', array( $this, 'shipping_packages' ) );
            add_action( 'woocommerce_order_details_after_order_table', array($this, 'list_order_item_addresses') );

            // cleanup
            add_action( 'wp_logout', array( $this, 'clear_session' ) );
            add_action( 'woocommerce_cart_emptied', array( $this, 'clear_session' ) );
            add_action( 'woocommerce_cart_updated', array( $this, 'cart_updated' ) );


            // shortcode
            add_shortcode( 'woocommerce_select_multiple_addresses', array( $this, 'draw_form' ) );
            add_shortcode( 'woocommerce_account_addresses', array( $this, 'account_addresses' ) );

            // delete address request
            add_action( 'template_redirect', array($this, 'delete_address') );

            // save shipping addresses
            add_action( 'template_redirect', array( $this, 'save_addresses' ) );
            add_action( 'template_redirect', array( $this, 'address_book' ) );

            // my account
            add_action( 'woocommerce_after_my_account', array( $this, 'my_account' ) );

            // address book
            add_action( 'wp_enqueue_scripts', array( $this, 'front_scripts' ) );
            add_action( 'wp_ajax_wc_ms_delete_address', array( $this, 'ajax_delete_address' ) );
            add_action( 'wp_ajax_wc_save_to_address_book', array( $this, 'save_address_book' ) );
            add_action( 'wp_ajax_nopriv_wc_save_to_address_book', array( $this, 'save_address_book' ) );
            add_action( 'wp_ajax_wc_duplicate_cart', array( $this, 'duplicate_cart_ajax' ) );
            add_action( 'wp_ajax_nopriv_wc_duplicate_cart', array( $this, 'duplicate_cart_ajax' ) );

            // duplicate cart POST
            add_action( 'template_redirect', array($this, 'duplicate_cart_post') );

            // inline script
            add_action( 'wp_footer', array( $this, 'inline_scripts' ) );
            add_action( 'woocommerce_cart_totals_after_shipping', array(&$this, 'remove_shipping_calculator') );

            // override needs shipping method and totals
            add_action( 'woocommerce_init', array( $this, 'wc_init' ) );


            /* WCMS Cart */
            add_action( 'woocommerce_cart_actions', array( $this, 'show_duplicate_cart_button' ) );


            include_once( 'multi-shipping.php' );

            $settings   = get_option( 'woocommerce_multiple_shipping_settings', array() );
            $this->gateway_settings = $settings;

            if ( isset($settings['lang_notification']) ) {
                self::$lang['notification'] = $settings['lang_notification'];
            }

            if ( isset($settings['lang_btn_items']) ) {
                self::$lang['btn_items'] = $settings['lang_btn_items'];
            }

            include_once 'includes/wcms-post-types.php';

            include_once 'includes/functions.php';
            include_once 'includes/wcms-gifts.php';
            include_once 'includes/wcms-notes.php';
            include_once 'includes/wcms-checkout.php';
            include_once 'includes/wcms-admin.php';
            include_once 'includes/wcms-order.php';
            include_once 'includes/wcms-order-shipment.php';

            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            if (
                is_plugin_active( 'se_woocommerce/shippingeasy_order.php' ) ||
                is_plugin_active( 'woocommerce-shippingeasy/woocommerce-shippingeasy.php' )
            ) {
                include_once 'includes/wcms-shipping-easy.php';
            }

            $this->gifts    = new WC_MS_Gifts( $this );
            $this->notes    = new WC_MS_Notes( $this );
            $this->checkout = new WC_MS_Checkout( $this );
            $this->admin    = new WC_MS_Admin( $this );
            $this->order    = new WC_MS_Order( $this );
            $this->shipments= new WC_MS_Order_Shipment( $this );

            include_once 'includes/wcms-shipworks.php';
        }

        function install() {
            global $woocommerce;

            $page_id = woocommerce_get_page_id( 'multiple_addresses' );

            if ( $page_id == -1 || get_post( $page_id ) == null) {
                // get the checkout page
                $checkout_id = woocommerce_get_page_id( 'checkout' );

                // add page and assign
                $page = array(
                    'menu_order'        => 0,
                    'comment_status'    => 'closed',
                    'ping_status'       => 'closed',
                    'post_author'       => 1,
                    'post_content'      => '[woocommerce_select_multiple_addresses]',
                    'post_name'         => 'shipping-addresses',
                    'post_parent'       => $checkout_id,
                    'post_title'        => 'Shipping Addresses',
                    'post_type'         => 'page',
                    'post_status'       => 'publish',
                    'post_category'     => array(1)
                );

                $page_id = wp_insert_post($page);

                update_option( 'woocommerce_multiple_addresses_page_id', $page_id);
            }

            $page_id = woocommerce_get_page_id( 'account_addresses' );

            if ($page_id == -1 || get_post( $page_id ) == null ) {
                // get the checkout page
                $account_id = woocommerce_get_page_id( 'myaccount' );

                // add page and assign
                $page = array(
                    'menu_order'        => 0,
                    'comment_status'    => 'closed',
                    'ping_status'       => 'closed',
                    'post_author'       => 1,
                    'post_content'      => '[woocommerce_account_addresses]',
                    'post_name'         => 'account-addresses',
                    'post_parent'       => $account_id,
                    'post_title'        => 'Shipping Addresses',
                    'post_type'         => 'page',
                    'post_status'       => 'publish',
                    'post_category'     => array(1)
                );

                $page_id = wp_insert_post($page);

                update_option( 'woocommerce_account_addresses_page_id', $page_id);
            }
        }

        public function is_multiship_enabled() {
            $enabled = true;

            // role-based shipping methods
            if ( class_exists( 'WC_Role_Methods' ) ) {
                global $current_user;

                $enabled = false;

                if ( !isset( $wp_roles ) )
                    $wp_roles = new WP_Roles();

                $the_roles          = $wp_roles->roles;
                $current_user_roles = array();

                if ( is_user_logged_in() ) {
                    $user = new WP_User( $current_user->ID );
                    if ( !empty( $user->roles ) && is_array( $user->roles ) ) {
                        foreach ( $user->roles as $role ) {
                            $current_user_roles[] = strtolower($the_roles[$role]['name']);
                        }
                    }
                } else {
                    $current_user_roles[] = 'Guest';
                }

                $role_methods   = WC_Role_Methods::get_instance();

                foreach ( $current_user_roles as $user_role ) {
                    if ( $role_methods->check_rolea_methods( $user_role, 'multiple_shipping' ) ) {
                        $enabled = true;
                        break;
                    }
                }

            }


            return apply_filters( 'wc_ms_is_multiship_enabled', $enabled );
        }

        function wc_init() {
            global $woocommerce;

            add_action( 'woocommerce_before_order_total', array( $this, 'display_shipping_methods' ) );
            add_action( 'woocommerce_review_order_before_order_total', array( $this, 'display_shipping_methods' ) );
        }

        /**
         * unused
         */
        public function menu() {
            add_submenu_page( 'woocommerce', __( 'Multiple Shipping Settings', 'wc_shipping_multiple_address' ),  __( 'Multiple Shipping', 'wc_shipping_multiple_address' ) , 'manage_woocommerce', 'wc-ship-multiple-products', array( $this, 'settings' ) );
        }


        /**
         * unused
         */
        public function settings() {
            include 'settings.php';
        }



        function product_options() {
            global $post, $thepostid, $woocommerce;

            $settings   = $this->settings;
            $thepostid  = $post->ID;

            $ship       = $woocommerce->shipping;

            $shipping_methods   = $woocommerce->shipping->load_shipping_methods(false);
            $ship_methods_array = array();
            $categories_array   = array();

            foreach ($shipping_methods as $id => $object) {
                if ($object->enabled == 'yes' && $id != 'multiple_shipping' ) {
                    $ship_methods_array[$id] = $object->method_title;
                }
            }

            //$origin     = $this->get_product_origin( $thepostid );
            $method     = $this->get_product_shipping_method( $thepostid );
            ?>
            <p style="border-top: 1px solid #DFDFDF;">
                <strong><?php _e( 'Shipping Options', 'periship' ); ?></strong>
            </p>
            <p class="form-field method_field">
                <label for="product_method"><?php _e( 'Shipping Methods', 'wc_shipping_multiple_address' ); ?></label>
                <select name="product_method[]" id="product_method" class="chzn-select" multiple>
                    <option value=""></option>
                    <?php
                    foreach ($ship_methods_array as $value => $label):
                        $selected = (in_array($value, $method)) ? 'selected' : '';
                    ?>
                    <option value="<?php echo $value; ?>" <?php echo $selected; ?>><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
            </p>
            <script type="text/javascript">jQuery("#product_method").chosen();</script>
            <?php
        }

        function process_metabox( $post_id ) {
            $settings = $this->settings;

            $zip_origin = null;
            $method     = ( isset($_POST['product_method']) && !empty($_POST['product_method']) ) ? $_POST['product_method'] : false;

            if (! $method ) return;

            // remove all instances of this product is first
            foreach ( $settings as $idx => $setting ) {
                if ( in_array($post_id, $setting['products']) ) {
                    foreach ( $setting['products'] as $pid => $id ) {
                        if ( $id == $post_id ) unset($settings[$idx]['products'][$pid]);
                    }
                }
            }

            // look for a matching zip code
            $matched    = false;
            $zip_match  = false;
            foreach ( $settings as $idx => $setting ) {

                if ( $setting['zip'] == $zip_origin ) {
                    $zip_match = $idx;
                    // methods must match
                    if ( $method && count(array_diff($setting['method'], $method)) == 0 ) {
                        // zip and method matched
                        // add to existing setting
                        $matched = true;
                        $settings[$idx]['products'][] = $post_id;
                        break;
                    }
                }

            }

            if (! $matched ) {
                $settings[] = array(
                    'zip'       => $zip_origin,
                    'products'  => array($post_id),
                    'categories'=> array(),
                    'method'    => $method
                );
            }

            // finally, do some cleanup
            foreach ( $settings as $idx => $setting ) {
                if ( empty($setting['products']) && empty($setting['categories']) ) {
                    unset($settings[$idx]);
                }
            }
            $settings = array_merge($settings, array());

            // update the settings
            update_option( $this->meta_key_settings, $settings );
        }

        function my_account() {
            global $woocommerce;
            $user = wp_get_current_user();

            if ($user->ID == 0) return;

            $page_id    = woocommerce_get_page_id( 'account_addresses' );
            $form_link  = get_permalink( $page_id );
            $otherAddr  = get_user_meta($user->ID, 'wc_other_addresses', true);

            WC_MS_Compatibility::wc_get_template(
                'my-account-addresses.php',
                array(
                    'user'          => $user,
                    'addresses'     => $otherAddr,
                    'form_url'      => $form_link
                ),
                'multi-shipping',
                dirname( __FILE__ ) .'/templates/'
            );

        }

        function front_scripts() {
            global $woocommerce, $post;

            $page_ids = array(
                woocommerce_get_page_id( 'account_addresses' ),
                woocommerce_get_page_id( 'multiple_addresses' ),
                woocommerce_get_page_id( 'myaccount' ),
                woocommerce_get_page_id( 'checkout' ),
                woocommerce_get_page_id( 'cart')
            );

            if ( $post && !in_array( $post->ID, $page_ids ) ) {
                return;
            }

            $user = wp_get_current_user();

            wp_enqueue_script( 'jquery',                null );
            wp_enqueue_script( 'jquery-ui-core',        null, array( 'jquery' ) );
            wp_enqueue_script( 'jquery-ui-mouse',       null, array( 'jquery-ui-core' ) );
            wp_enqueue_script( 'jquery-ui-draggable',   null, array( 'jquery-ui-core' ) );
            wp_enqueue_script( 'jquery-ui-droppable',   null, array( 'jquery-ui-core' ) );
            wp_enqueue_script( 'jquery-ui-datepicker',  null, array( 'jquery-ui-core' ) );
            wp_enqueue_script( 'jquery-masonry',        null, array('jquery-ui-core') );
            wp_enqueue_script( 'thickbox' );
            wp_enqueue_style(  'thickbox' );
            wp_enqueue_script( 'jquery-blockui' );

            // touchpunch to support mobile browsers
            wp_enqueue_script( 'jquery-ui-touch-punch', plugins_url( 'js/jquery.ui.touch-punch.min.js', __FILE__ ), array('jquery-ui-mouse', 'jquery-ui-widget') );

            if ($user->ID != 0) {
                wp_enqueue_script( 'multiple_shipping_script', plugins_url( 'js/front.js', __FILE__) );

                wp_localize_script( 'multiple_shipping_script', 'WC_Shipping', array(
                        // URL to wp-admin/admin-ajax.php to process the request
                        'ajaxurl'          => admin_url( 'admin-ajax.php' )
                    )
                );

                $page_id = woocommerce_get_page_id( 'account_addresses' );
                $url = get_permalink($page_id);
                $url = add_query_arg( 'height', '400', add_query_arg( 'width', '400', add_query_arg( 'addressbook', '1', $url)));
                ?>
                <script type="text/javascript">
                var address = null;
                var wc_ship_url = '<?php echo $url; ?>';
                </script>
                <?php
            }

            wp_enqueue_script( 'jquery-tiptip', plugins_url( 'js/jquery.tiptip.js', __FILE__ ), array('jquery', 'jquery-ui-core') );

            wp_enqueue_script( 'modernizr', plugins_url( 'js/modernizr.js', __FILE__ ) );
            wp_enqueue_script( 'multiple_shipping_checkout', plugins_url( 'js/woocommerce-checkout.js', __FILE__), array( 'woocommerce', 'jquery-ui-draggable', 'jquery-ui-droppable', 'jquery-ui-mouse', ) );

            if ( function_exists('wc_add_notice') ) {
                wp_localize_script( 'multiple_shipping_checkout', 'WCMS', apply_filters( 'wc_ms_multiple_shipping_checkout_locale', array(
                        // URL to wp-admin/admin-ajax.php to process the request
                        'ajaxurl'   => admin_url( 'admin-ajax.php' ),
                        'base_url'  => plugins_url( '', __FILE__),
                        'wc_url'    => $woocommerce->plugin_url(),
                        'countries' => json_encode( array_merge( $woocommerce->countries->get_allowed_country_states(), $woocommerce->countries->get_shipping_country_states() ) ),
                        'select_state_text' => esc_attr__( 'Select an option&hellip;', 'woocommerce' ),
                    ) )
                );

                if ( WC_MS_Compatibility::is_wc_version_gte('2.3') ) {
                    wp_enqueue_script('select2', '//cdnjs.cloudflare.com/ajax/libs/select2/3.5.2/select2.min.js', array('jquery'), '3.5.2' );
                    wp_enqueue_style('select2', '//cdnjs.cloudflare.com/ajax/libs/select2/3.5.2/select2.min.css', array(), '3.5.2' );
                }

                wp_register_script( 'wcms-country-select', plugins_url() .'/woocommerce-shipping-multiple-addresses/js/country-select.js', array( 'jquery' ), WC_VERSION, true );
                wp_localize_script( 'wcms-country-select', 'wcms_country_select_params', apply_filters( 'wc_country_select_params', array(
                            'countries'              => json_encode( array_merge( WC()->countries->get_allowed_country_states(), WC()->countries->get_shipping_country_states() ) ),
                            'i18n_select_state_text' => esc_attr__( 'Select an option&hellip;', 'woocommerce' ),
                        ) ) );
                wp_enqueue_script('wcms-country-select');
            } else {
                wp_localize_script( 'multiple_shipping_checkout', 'WCMS', array(
                        // URL to wp-admin/admin-ajax.php to process the request
                        'ajaxurl'   => admin_url( 'admin-ajax.php' ),
                        'base_url'  => plugins_url( '', __FILE__),
                        'wc_url'    => $woocommerce->plugin_url(),
                        'countries' => json_encode( $woocommerce->countries->get_allowed_country_states() ),
                        'select_state_text' => esc_attr__( 'Select an option&hellip;', 'woocommerce' ),
                    )
                );
            }

            wp_enqueue_style( 'multiple_shipping_styles', plugins_url( 'css/front.css', __FILE__) );
            wp_enqueue_style( 'tiptip', plugins_url( 'css/jquery.tiptip.css', __FILE__) );

            global $wp_scripts;
            $ui_version = $wp_scripts->registered['jquery-ui-core']->ver;
            wp_enqueue_style("jquery-ui-css", "//ajax.googleapis.com/ajax/libs/jqueryui/{$ui_version}/themes/ui-lightness/jquery-ui.min.css");

            // address validation support

            if ( class_exists('WC_Address_Validation') && is_page( woocommerce_get_page_id('multiple_addresses') ) ) {

                $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

                $validator  = $GLOBALS['wc_address_validation'];
                $handler    = $validator->handler;

                $params = array(
                    'nonce'                 => wp_create_nonce( 'wc_address_validation' ),
                    'debug_mode'            => 'yes' == get_option( 'wc_address_validation_debug_mode' ),
                    'force_postcode_lookup' => 'yes' == get_option( 'wc_address_validation_force_postcode_lookup' ),
                    'ajax_url'              => admin_url( 'admin-ajax.php', 'relative' ),
                );

                // load postcode lookup JS
                $provider = $handler->get_active_provider();

                if ( $provider && $provider->supports( 'postcode_lookup') ) {

                    wp_enqueue_script( 'wc_address_validation_postcode_lookup', $validator->get_plugin_url() . '/assets/js/frontend/wc-address-validation-postcode-lookup' . $suffix . '.js', array( 'jquery', 'woocommerce' ), WC_Address_Validation::VERSION, true );

                    wp_localize_script( 'wc_address_validation_postcode_lookup', 'wc_address_validation_postcode_lookup', $params );
                }

                // load address validation JS
                if ( $provider && $provider->supports( 'address_validation' ) && 'WC_Address_Validation_Provider_SmartyStreets' == get_class( $provider ) ) {

                    // load SmartyStreets LiveAddress jQuery plugin
                    wp_enqueue_script( 'wc_address_validation_smarty_streets', '//d79i1fxsrar4t.cloudfront.net/jquery.liveaddress/2.4/jquery.liveaddress.min.js', array( 'jquery' ), '2.4', true );

                    wp_enqueue_script( 'wcms_address_validation', plugins_url( 'js/address-validation.js', __FILE__ ), array('jquery') );

                    $params['smarty_streets_key'] = $provider->html_key;

                    wp_localize_script( 'wcms_address_validation', 'wc_address_validation', $params );

                    // add a bit of CSS to fix address correction popup from expanding to page width because of Chosen selects
                    echo '<style type="text/css">.chzn-done{position:absolute!important;visibility:hidden!important;display:block!important;width:120px!important;</style>';
                }

                // allow other providers to load JS
                do_action( 'wc_address_validation_load_js', $provider, $handler, $suffix );
            }

            // on the thank you page, remove the Shipping Address block if the order ships to multiple addresses
            if ( isset($_GET['order-received']) || isset($_GET['view-order']) ) {

                $order_id = isset($_GET['order-received']) ? intval( $_GET['order-received'] ) : intval( $_GET['view-order'] );
                $packages = get_post_meta($order_id, '_wcms_packages', true);
                $multiship= get_post_meta($order_id, '_multiple_shipping', true);

                if ( ($packages && count($packages) > 1) || $multiship == 'yes' ) {
                    wp_enqueue_script( 'wcms_shipping_address_override', plugins_url( 'js/address-override.js', __FILE__ ), array( 'jquery' ) );
                }

            }

        }

        function inline_scripts() {
            global $woocommerce;

            $order_id   = (isset($_GET['order'])) ? $_GET['order'] : false;

            if ($order_id):

                $order = WC_MS_Compatibility::wc_get_order( $order_id );

                if (method_exists($order, 'get_checkout_order_received_url')) {
                    $page_id    = $order->get_checkout_order_received_url();
                } else {
                    $page_id    = woocommerce_get_page_id( 'thanks' );
                }

                $custom = $order->order_custom_fields;

                if ( is_page($page_id) && isset($custom['_shipping_addresses']) && isset($custom['_shipping_addresses'][0]) && !empty($custom['_shipping_addresses'][0]) ) {
                    $html       = '<div>';
                    $addresses  = unserialize($custom['_shipping_addresses'][0]);
                    $packages   = get_post_meta($order_id, '_wcms_packages', true);

                    foreach ( $packages as $package ) {
                        $html .= '<address>'. wcms_get_formatted_address( $package['full_address'] ) .'</address><br /><hr/>';
                    }
                    $html .= '</div>';
                    $html = str_replace( '"', '\"', $html);
                    $html = str_replace("\n", " ", $html);
            ?>
            <script type="text/javascript">
            jQuery(document).ready(function() {
                jQuery(jQuery("address")[1]).replaceWith("<?php echo $html; ?>");
            });
            </script>
            <?php
                }
            endif;
        }

        function remove_shipping_calculator() {
            global $woocommerce;

            if ( isset($woocommerce->session) && isset($woocommerce->session->cart_item_addresses) ) {
                $script = 'jQuery(document).ready(function(){
                    jQuery("tr.shipping").remove();
                });';

                if ( function_exists('wc_enqueue_js') ) {
                    wc_enqueue_js( $script );
                } else {
                    $woocommerce->add_inline_js( $script );
                }

                echo '<tr class="multi-shipping">
                    <th>'. __( 'Shipping', 'woocommerce' ) .'</th>
                    <td>'. woocommerce_price($woocommerce->session->shipping_total) .'</td>
                </tr>';
            }
        }

        function ajax_delete_address() {
            $user       = wp_get_current_user();
            $idx        = absint( $_POST['idx'] );
            $addresses  = get_user_meta( $user->ID, 'wc_other_addresses', true );

            unset( $addresses[ $idx ] );

            update_user_meta( $user->ID, 'wc_other_addresses', $addresses );

            wp_send_json( array('ack' => 'OK') );
            exit;
        }

        function save_address_book() {
            global $woocommerce;

            $this->load_cart_files();

            $checkout   = new WC_Checkout();
            $user       = wp_get_current_user();

            $address    = $_POST['address'];
            $shipFields = $woocommerce->countries->get_address_fields( $address['shipping_country'], 'shipping_' );
            $errors     = array();

            foreach ( $shipFields as $key => $field ) {

                if ( isset($field['required']) && $field['required'] && empty($address[$key]) ) {
                    $errors[] = $key;
                }

                if (! empty($address[$key]) ) {

                    // Validation rules
                    if ( ! empty( $field['validate'] ) && is_array( $field['validate'] ) ) {
                        foreach ( $field['validate'] as $rule ) {
                            switch ( $rule ) {
                                case 'postcode' :
                                    $address[ $key ] = strtoupper( str_replace( ' ', '', $address[ $key ] ) );

                                    if ( ! WC_Validation::is_postcode( $address[ $key ], $address[ 'shipping_country' ] ) ) :
                                        $errors[] = $key;
                                        wc_add_notice( __( 'Please enter a valid postcode/ZIP.', 'woocommerce' ), 'error' );
                                    else :
                                        $address[ $key ] = wc_format_postcode( $address[ $key ], $address[ 'shipping_country' ] );
                                    endif;
                                    break;
                                case 'phone' :
                                    $address[ $key ] = wc_format_phone_number( $address[ $key ] );

                                    if ( ! WC_Validation::is_phone( $address[ $key ] ) ) {
                                        $errors[] = $key;

                                        if ( function_exists('wc_add_notice') )
                                            wc_add_notice( '<strong>' . $field['label'] . '</strong> ' . __( 'is not a valid phone number.', 'woocommerce' ), 'error' );
                                        else
                                            $woocommerce->add_error('<strong>' . $field['label'] . '</strong> ' . __( 'is not a valid phone number.', 'woocommerce' ));
                                    }

                                    break;
                                case 'email' :
                                    $address[ $key ] = strtolower( $address[ $key ] );

                                    if ( ! is_email( $address[ $key ] ) ) {
                                        $errors[] = $key;

                                        if ( function_exists('wc_add_notice') )
                                            wc_add_notice( '<strong>' . $field['label'] . '</strong> ' . __( 'is not a valid email address.', 'woocommerce' ), 'error' );
                                        else
                                            $woocommerce->add_error( '<strong>' . $field['label'] . '</strong> ' . __( 'is not a valid email address.', 'woocommerce' ) );
                                    }

                                    break;
                                case 'state' :
                                    // Get valid states
                                    $valid_states = WC()->countries->get_states( $address[ 'shipping_country' ] );
                                    if ( $valid_states )
                                        $valid_state_values = array_flip( array_map( 'strtolower', $valid_states ) );

                                    // Convert value to key if set
                                    if ( isset( $valid_state_values[ strtolower( $address[ $key ] ) ] ) )
                                        $address[ $key ] = $valid_state_values[ strtolower( $address[ $key ] ) ];

                                    // Only validate if the country has specific state options
                                    if ( is_array($valid_states) && sizeof( $valid_states ) > 0 )
                                        if ( ! in_array( $address[ $key ], array_keys( $valid_states ) ) ) {
                                            $errors[] = $key;

                                            if ( function_exists('wc_add_notice') )
                                                wc_add_notice( '<strong>' . $field['label'] . '</strong> ' . __( 'is not valid. Please enter one of the following:', 'woocommerce' ) . ' ' . implode( ', ', $valid_states ), 'error' );
                                            else
                                                $woocommerce->add_error('<strong>' . $field['label'] . '</strong> ' . __( 'is not valid. Please enter one of the following:', 'woocommerce' ) . ' ' . implode( ', ', $valid_states ));
                                        }

                                    break;
                            }
                        }
                    }

                }

            }

            if ( count($errors) > 0 ) {
                die(json_encode(array( 'ack' => 'ERR', 'errors' => $errors, 'message' => __( 'Please enter the complete address', 'wc_shipping_multiple_address' ))));
            }

            $id  = $_POST['id'];

            $addresses  = $this->get_user_addresses( $user );

            $redirect_url = (isset($_POST['next'])) ? $_POST['next'] : get_permalink( woocommerce_get_page_id('multiple_addresses') );

            if ( $id >= 0 )
                $next = add_query_arg( 'updated', '1', $redirect_url );
            else
                $next = add_query_arg( 'new', '1', $redirect_url );

            // address is unique, save!
            if ( $id == -1 ) {
                $vals = '';
                foreach ($address as $key => $value) {
                    $vals .= $value;
                }
                $md5 = md5($vals);

                foreach ($addresses as $addr) {
                    $vals = '';
                    if( !is_array($addr) ) { continue; }
                    foreach ($addr as $key => $value) {
                        $vals .= $value;
                    }
                    $addrMd5 = md5($vals);

                    if ($md5 == $addrMd5) {
                        // duplicate address!
                        die(json_encode(array( 'ack' => 'ERR', 'message' => __( 'Address is already in your address book', 'wc_shipping_multiple_address' ))));
                    }
                }

                $addresses[] = $address;
            } else {
                $addresses[$id] = $address;
            }

            // update the default address and remove it from the $addresses array
            if ( $user->ID > 0 ) {
                if ( $id == 0 ) {
                    $default_address = $addresses[0];
                    unset( $addresses[0] );

                    if ( $default_address['shipping_address_1'] && $default_address['shipping_postcode'] ) {
                        update_user_meta( $user->ID, 'shipping_first_name', $default_address['shipping_first_name'] );
                        update_user_meta( $user->ID, 'shipping_last_name',  $default_address['shipping_last_name'] );
                        update_user_meta( $user->ID, 'shipping_company',    $default_address['shipping_company'] );
                        update_user_meta( $user->ID, 'shipping_address_1',  $default_address['shipping_address_1'] );
                        update_user_meta( $user->ID, 'shipping_address_2',  $default_address['shipping_address_2'] );
                        update_user_meta( $user->ID, 'shipping_city',       $default_address['shipping_city'] );
                        update_user_meta( $user->ID, 'shipping_state',      $default_address['shipping_state'] );
                        update_user_meta( $user->ID, 'shipping_postcode',   $default_address['shipping_postcode'] );
                        update_user_meta( $user->ID, 'shipping_country',    $default_address['shipping_country'] );
                    }
                    unset( $addresses[0] );
                }

            }

            $this->save_user_addresses( $user->ID, $addresses );

            foreach ( $address as $key => $value ) {
                $new_key = str_replace( 'shipping_', '', $key);
                $address[$new_key] = $value;
            }

            $formatted_address  = wcms_get_formatted_address( $address );
            $json_address       = json_encode($address);

            if (!$formatted_address) return;

            if ( isset($_POST['return']) && $_POST['return'] == 'list' ) {
                $html = '<option value="'. $id .'">'. $formatted_address .'</option>';
            } else {
                $html = '
                    <div class="account-address">
                        <address>'. $formatted_address .'</address>
                        <div style="display: none;">';

                ob_start();
                foreach ($shipFields as $key => $field) :
                    $val = (isset($address[$key])) ? $address[$key] : '';
                    $key .= '_'. $id;

                    woocommerce_form_field( $key, $field, $val );
                endforeach;

                do_action( 'woocommerce_after_checkout_shipping_form', $checkout);
                $html .= ob_get_clean();

                $html .= '
                            <input type="hidden" name="addresses[]" value="'. $id .'" />
                        </div>

                        <ul class="items-column" id="items_column_'. $id .'">
                            <li class="placeholder">Drag items here</li>
                        </ul>
                    </div>
                    ';
            }

            $return = json_encode(array( 'ack' => 'OK', 'id' => $id, 'html' => $html, 'return' => $_POST['return'], 'next' => $next));
            die($return);

        }

        function duplicate_cart( $multiplier = 1 ) {
            global $woocommerce;

            $this->load_cart_files();

            $cart           = $woocommerce->cart;
            $current_cart   = $cart->get_cart();
            $orig_cart      = array();

            if ( wcms_session_isset('wcms_original_cart') ) {
                $orig_cart = wcms_session_get( 'wcms_original_cart' );
            }

            if ( !empty($orig_cart) ) {
                $contents = wcms_session_get( 'wcms_original_cart' );
            } else {
                $contents = $cart->get_cart();
                wcms_session_set( 'wcms_original_cart', $contents );
            }

            $added = array();
            foreach ( $contents as $cart_key => $content ) {
                $add_qty        = $content['quantity'] * $multiplier;
                $current_qty    = (isset($current_cart[$cart_key])) ? $current_cart[$cart_key]['quantity'] : 0;

                $cart->set_quantity( $cart_key, $current_qty + $add_qty );

                $added[] = array(
                    'id'        => $content['product_id'],
                    'qty'       => $add_qty,
                    'key'       => $cart_key,
                    'content'   => $content
                );
            }

            return $added;

        }

        function duplicate_cart_ajax() {
            global $woocommerce;

            $this->load_cart_files();

            $checkout   = new WC_Checkout();
            $cart       = $woocommerce->cart;
            $user       = wp_get_current_user();

            $shipFields = $woocommerce->countries->get_address_fields( $woocommerce->countries->get_base_country(), 'shipping_' );
            $address    = $_POST['address'];
            $add_id     = ( isset($_POST['address_id']) && !empty($_POST['address_id']) ) ? $_POST['address_id'] : false;

            $add = $this->duplicate_cart();

            $addresses = $this->get_user_addresses( $user );

            if ( $add_id !== false ) {
                $address    = $addresses[$add_id];
                $id         = $add_id;
            } else {
                $address    = $_POST['address'];
                $id         = rand(100,1000);
            }

            foreach ( $address as $key => $value ) {
                $new_key = str_replace( 'shipping_', '', $key);
                $address[$new_key] = $value;
            }

            $formatted_address  = wcms_get_formatted_address( $address );
            $json_address       = json_encode($address);

            //if (!$formatted_address) continue;

            if ( $user->ID > 0 ) {
                $vals = '';
                foreach ($address as $key => $value) {
                    $vals .= $value;
                }

                $md5 = md5($vals);
                $saved = false;

                foreach ($addresses as $addr) {
                    $vals = '';
                    if( !is_array($addr) ) { continue; }
                    foreach ($addr as $key => $value) {
                        $vals .= $value;
                    }
                    $addrMd5 = md5($vals);

                    if ($md5 == $addrMd5) {
                        // duplicate address!
                        $saved = true;
                        break;
                    }
                }

                if (! $saved && ! $add_id ) {
                    // address is unique, save!
                    $id = count($addresses);
                    $addresses[] = $address;

                    $this->save_user_addresses( $user->ID, $addresses );

                }
            }

            $html = '
            <div class="account-address">
                <address>'. $formatted_address .'</address>
                <div style="display: none;">';

            ob_start();
            foreach ($shipFields as $key => $field) :
                $val = (isset($address[$key])) ? $address[$key] : '';
                $key .= '_'. $id;

                woocommerce_form_field( $key, $field, $val );
            endforeach;

            do_action( 'woocommerce_after_checkout_shipping_form', $checkout);
            $html .= ob_get_clean();

            $html .= '
                    <input type="hidden" name="addresses[]" value="'. $id .'" />
                    <textarea style="display:none;">'. $json_address .'</textarea>
                </div>

                <ul class="items-column" id="items_column_'. $id .'">';

            foreach ( $add as $product ) {
                $html .= '
                <li data-product-id="'. $product['id'] .'" data-key="'. $product['key'] .'" class="address-item address-item-'. $product['id'] .' address-item-key-'. $product['key'] .'">
                    <span class="qty">'. $product['qty'] .'</span>
                    <h3 class="title">'. get_the_title($product['id']) .'</h3>
                    '. $woocommerce->cart->get_item_data( $product['content'] );

                for ($item_qty = 0; $item_qty < $product['qty']; $item_qty++):
                    $html .= '<input type="hidden" name="items_'. $id.'[]" value="'. $product['key'] .'">';
                endfor;

                $html .= '<a class="remove" href="#"><img style="width: 16px; height: 16px;" src="'. plugins_url('images/delete.png', self::FILE) .'" class="remove" title="'. __('Remove', 'wc_shipping_multiple_address') .'"></a>
                </li>';

            }

            $html .= '    </ul>
            </div>
            ';

            $return = json_encode(array( 'ack' => 'OK', 'id' => $id, 'html' => $html));
            die($return);
            exit;
        }

        function duplicate_cart_post() {
            global $woocommerce;

            if ( isset($_POST['duplicate_submit']) ) {
                $address_ids    = (isset($_POST['address_ids'])) ? (array)$_POST['address_ids'] : array();
                $fields         = $woocommerce->countries->get_address_fields( $woocommerce->countries->get_base_country(), 'shipping_' );

                $user_addresses = $this->get_user_addresses( wp_get_current_user() );

                $data   = (wcms_session_isset('cart_item_addresses')) ? wcms_session_get('cart_item_addresses') : array();
                $rel    = (wcms_session_isset('wcms_item_addresses')) ? wcms_session_get('wcms_item_addresses') : array();

                for ($x = 0; $x < count($address_ids); $x++ ) {

                    $added      = $this->duplicate_cart();
                    $address_id = $address_ids[ $x ];
                    $address    = $user_addresses[ $address_id ];

                    foreach ( $added as $item ) {

                        $qtys           = $item['qty'];

                        $product_id     = $item['id'];
                        $sig            = $item['key'] .'_'. $product_id .'_';

                        $i = 1;
                        for ( $y = 0; $y < $qtys; $y++ ) {
                            $rel[ $address_id ][]  = $item['key'];

                            while ( isset($data['shipping_first_name_'. $sig . $i]) ) {
                                $i++;
                            }

                            $_sig = $sig . $i;

                            if ( $fields ) foreach ( $fields as $key => $field ) :
                                $data[$key .'_'. $_sig] = $address[ $key ];
                            endforeach;
                        }

                        $cart_address_ids_session = wcms_session_get( 'cart_address_ids' );

                        if (!wcms_session_isset( 'cart_address_ids' ) || ! in_array($sig, $cart_address_ids_session) ) {
                            $cart_address_sigs_session = wcms_session_get( 'cart_address_sigs' );
                            $cart_address_sigs_session[$_sig] = $address_id;
                            wcms_session_set( 'cart_address_sigs', $cart_address_sigs_session);
                        }

                    }

                }

                wcms_session_set( 'cart_item_addresses', $data );
                wcms_session_set( 'address_relationships', $rel );
                wcms_session_set( 'wcms_item_addresses', $rel );

                wp_redirect( get_permalink( woocommerce_get_page_id('multiple_addresses') ) );
                exit;
            }

        }

        function address_book() {
            global $woocommerce;
            $user = wp_get_current_user();

            if ($user->ID == 0) return;

            if (isset($_GET['addressbook']) && $_GET['addressbook'] == 1) {
                $addresses = get_user_meta($user->ID, 'wc_other_addresses', true);
            ?>
                <p></p>
                <h2><?php _e( 'Address Book', 'wc_shipping_multiple_address' ); ?></h2>
            <?php
                if (!empty($addresses)):
                    foreach ($addresses as $addr) {
                        if ( empty($addr) ) continue;

                        echo '<div style="float: left; width: 200px;">';
                        $address = array(
                            'first_name'    => $addr['shipping_first_name'],
                            'last_name'     => $addr['shipping_last_name'],
                            'company'       => $addr['shipping_company'],
                            'address_1'     => $addr['shipping_address_1'],
                            'address_2'     => $addr['shipping_address_2'],
                            'city'          => $addr['shipping_city'],
                            'state'         => $addr['shipping_state'],
                            'postcode'      => $addr['shipping_postcode'],
                            'country'       => $addr['shipping_country']
                        );
                        $formatted_address  = wcms_get_formatted_address( $address );
                        $json_address       = json_encode($address);

                        if (!$formatted_address) _e( 'You have not set up a shipping address yet.', 'woocommerce' ); else echo '<address>'.$formatted_address.'</address>';
                        echo '  <textarea style="display:none;">'. $json_address .'</textarea>';
                        echo '  <p><button type="button" class="button address-use">'. __( 'Use this address', 'wc_shipping_multiple_address' ) .'</button></p>';
                        echo '</div>';
                    }
                    echo '<div class="clear: both;"></div>';
                else:
                    echo '<h4>'. __( 'You have no shipping addresses saved.', 'wc_shipping_multiple_address' ) .'</h4>';
                endif;
                ?>
                <script type="text/javascript">
                jQuery(document).ready(function() {
                    jQuery( '.address-use' ).click(function() {
                        var address = jQuery.parseJSON(jQuery(this).parents( 'p' ).prev( 'textarea' ).val());
                        jQuery(this).prop( 'disabled', true);

                        setAddress(address, '<?php echo $_GET['sig']; ?>' );
                        tb_remove();
                    });
                });
                </script>
                <?php
                exit;
            }
        }





        function show_duplicate_cart_button() {
            $ms_settings = get_option( 'woocommerce_multiple_shipping_settings', array() );


            if ( isset($ms_settings['cart_duplication']) && $ms_settings['cart_duplication'] != 'no' ) {
                $dupe_url = add_query_arg('duplicate-form', '1', get_permalink(woocommerce_get_page_id('multiple_addresses')));

                echo '<a class="button expand" href="' . $dupe_url . '" >' . __('Duplicate Cart', 'wc_shipping_multiple_address') . '</a>';
            }

        }

        function account_addresses() {
            global $woocommerce;

            $this->load_cart_files();

            $checkout   = new WC_Checkout();
            $user       = wp_get_current_user();
            $shipFields = $woocommerce->countries->get_address_fields( $woocommerce->countries->get_base_country(), 'shipping_' );

            if ($user->ID == 0) {
                return;
            }

            if ( isset( $_GET['edit'] ) ) {
                $updating   = true;
                $idx        = absint( $_GET['edit'] );

                $otherAddr  = get_user_meta($user->ID, 'wc_other_addresses', true);
                $address    = $otherAddr[ $idx ];
            } else {
                $updating   = false;
                $idx        = -1;
                $address    = array();
            }

            WC_MS_Compatibility::wc_get_template(
                'account-address-form.php',
                array(
                    'checkout'      => $checkout,
                    'user'          => $user,
                    'shipFields'    => $shipFields,
                    'address'       => $address,
                    'idx'           => $idx,
                    'updating'      => $updating
                ),
                'multi-shipping',
                dirname( __FILE__ ) .'/templates/'
            );
        }

        function output_body_class( $classes ) {
            if ( is_page( woocommerce_get_page_id( 'multiple_addresses' ) ) || is_page( woocommerce_get_page_id( 'account_addresses' ) ) ) {
                $classes[] = 'woocommerce';
                $classes[] = 'woocommerce-page';
            }

            return $classes;
        }

        function delete_address() {
            global $woocommerce;

            $user = wp_get_current_user();

            if ( isset($_REQUEST['address-delete']) && isset($_REQUEST['id']) ) {
                $id         = $_REQUEST['id'];
                $addresses  = $this->get_user_addresses( $user );
                //$addresses  = (wcms_session_isset('cart_item_addresses')) ? wcms_session_get('cart_item_addresses') : array();

                if ($user->ID != 0) {
                    $addresses = get_user_meta($user->ID, 'wc_other_addresses', true);

                    if (! $addresses) {
                        $addresses = array();
                    }

                    $default_address = array(
                        'first_name' 	=> get_user_meta( $user->ID, 'shipping_first_name', true ),
                        'last_name'		=> get_user_meta( $user->ID, 'shipping_last_name', true ),
                        'company'		=> get_user_meta( $user->ID, 'shipping_company', true ),
                        'address_1'		=> get_user_meta( $user->ID, 'shipping_address_1', true ),
                        'address_2'		=> get_user_meta( $user->ID, 'shipping_address_2', true ),
                        'city'			=> get_user_meta( $user->ID, 'shipping_city', true ),
                        'state'			=> get_user_meta( $user->ID, 'shipping_state', true ),
                        'postcode'		=> get_user_meta( $user->ID, 'shipping_postcode', true ),
                        'country'		=> get_user_meta( $user->ID, 'shipping_country', true ),
                        'shipping_first_name' 	=> get_user_meta( $user->ID, 'shipping_first_name', true ),
                        'shipping_last_name'	=> get_user_meta( $user->ID, 'shipping_last_name', true ),
                        'shipping_company'		=> get_user_meta( $user->ID, 'shipping_company', true ),
                        'shipping_address_1'	=> get_user_meta( $user->ID, 'shipping_address_1', true ),
                        'shipping_address_2'	=> get_user_meta( $user->ID, 'shipping_address_2', true ),
                        'shipping_city'			=> get_user_meta( $user->ID, 'shipping_city', true ),
                        'shipping_state'		=> get_user_meta( $user->ID, 'shipping_state', true ),
                        'shipping_postcode'		=> get_user_meta( $user->ID, 'shipping_postcode', true ),
                        'shipping_country'		=> get_user_meta( $user->ID, 'shipping_country', true ),
                        'default_address'       => true
                    );

                    if ( $default_address['address_1'] && $default_address['postcode'] ) {
                        array_unshift($addresses, $default_address);
                    }

                    if ( $id == 0 ) {
                        $default_address = $addresses[0];

                        if ( $default_address['shipping_address_1'] && $default_address['shipping_postcode'] ) {
                            update_user_meta( $user->ID, 'shipping_first_name', '' );
                            update_user_meta( $user->ID, 'shipping_last_name',  '' );
                            update_user_meta( $user->ID, 'shipping_company',    '' );
                            update_user_meta( $user->ID, 'shipping_address_1',  '' );
                            update_user_meta( $user->ID, 'shipping_address_2',  '' );
                            update_user_meta( $user->ID, 'shipping_city',       '' );
                            update_user_meta( $user->ID, 'shipping_state',      '' );
                            update_user_meta( $user->ID, 'shipping_postcode',   '' );
                            update_user_meta( $user->ID, 'shipping_country',    '' );
                        }
                    } else {
                        unset( $addresses[ $id ] );
                    }

                    unset( $addresses[0] );

                    update_user_meta($user->ID, 'wc_other_addresses', $addresses);

                } else {
                    // guests
                    unset( $addresses[ $id ] );
                    wcms_session_set( 'user_addresses', $addresses );

                }

                if ( function_exists('wc_add_notice') )
                    wc_add_notice(__('Address deleted', 'wc_shipping_multiple_address'), 'success');
                else
                    $woocommerce->add_message( __('Address deleted', 'wc_shipping_multiple_address') );

                wp_redirect( get_permalink( woocommerce_get_page_id('multiple_addresses') ) );
                exit;

            }
        }

        function draw_form() {
            global $woocommerce;

            if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {

                $this->load_cart_files();

                $user       = wp_get_current_user();
                $cart       = $woocommerce->cart;
                $checkout   = new WC_Checkout();
                $contents   = wcms_get_real_cart_items();
                $shipFields = $woocommerce->countries->get_address_fields( $woocommerce->countries->get_base_country(), 'shipping_' );
                $tips       = array();

                $addresses  = $this->get_user_addresses( $user );

                $addresses = $this->array_sort( $addresses, 'shipping_first_name', SORT_ASC );


                if ( isset($_GET['new']) ) {
                    if ( function_exists('wc_add_notice') )
                        wc_add_notice(__('New address saved', 'wc_shipping_multiple_address'), 'success');
                    else
                        $woocommerce->add_message( __('New address saved', 'wc_shipping_multiple_address') );
                }

                if ( function_exists('wc_print_notices') )
                    wc_print_notices();
                else
                    $woocommerce->show_messages();

                if ( isset($_REQUEST['duplicate-form']) ) {
                    WC_MS_Compatibility::wc_get_template(
                        'duplicate-form.php',
                        array(
                            'woocommerce'   => $woocommerce,
                            'checkout'      => $checkout,
                            'addresses'     => $addresses,
                            'shipFields'    => $shipFields
                        ),
                        'multi-shipping',
                        dirname( __FILE__ ) .'/templates/'
                    );
                } elseif ( empty($addresses) || isset($_REQUEST['address-form']) ) {
                    WC_MS_Compatibility::wc_get_template(
                        'address-form.php',
                        array(
                            'woocommerce'   => $woocommerce,
                            'checkout'      => $checkout,
                            'addresses'     => $addresses,
                            'shipFields'    => $shipFields
                        ),
                        'multi-shipping',
                        dirname( __FILE__ ) .'/templates/'
                    );
                } else {

                    if (! empty($contents)) {
                        $relations  = wcms_session_get('wcms_item_addresses');

                        if ($addresses) foreach ($addresses as $x => $addr) {
                            foreach ( $contents as $key => $value ) {
                                if ( isset($relations[$x]) && !empty($relations[$x]) ):
                                    $qty = array_count_values($relations[$x]);

                                    if ( in_array($key, $relations[$x]) ) {
                                        if ( isset($placed[$key]) ) {
                                            $placed[$key] += $qty[$key];
                                        } else {
                                            $placed[$key] = $qty[$key];
                                        }
                                    }

                                endif;
                            }
                        }

                        $addresses = $this->array_sort( $addresses, 'shipping_first_name', SORT_ASC );
                        $relations  = wcms_session_get( 'wcms_item_addresses' );

                        WC_MS_Compatibility::wc_get_template(
                            'shipping-address-table.php',
                            array(
                                'addresses'     => $addresses,
                                'relations'     => $relations,
                                'checkout'      => $checkout,
                                'woocommerce'   => $woocommerce,
                                'contents'      => $contents,
                                'shipFields'    => $shipFields,
                                'user'          => $user,
                            ),
                            'multi-shipping',
                            dirname( __FILE__ ) .'/templates/'
                        );

                    }
                }

            } else {
                // load order and display the addresses
                $order_id = (int)$_GET['order_id'];
                $order = WC_MS_Compatibility::wc_get_order( $order_id );

                if ($order_id == 0 || !$order) wp_die(__( 'Order could not be found', 'woocommerce' ) );

                $packages           = get_post_meta($order_id, '_wcms_packages', true);

                if ( !$packages ) wp_die(__( 'This order does not ship to multiple addresses', 'wc_shipping_multiple_address' ) );

                // load the address fields
                $this->load_cart_files();

                $checkout   = new WC_Checkout();
                $cart       = new WC_Cart();
                //$shipFields = apply_filters( 'woocommerce_shipping_fields', array() );
                $shipFields = $woocommerce->countries->get_address_fields( $woocommerce->countries->get_base_country(), 'shipping_' );

                echo '<table class="shop_tabe"><thead><tr><th class="product-name">'. __( 'Product', 'woocommerce' ) .'</th><th class="product-quantity">'. __( 'Qty', 'woocommerce' ) .'</th><th class="product-address">'. __( 'Address', 'woocommerce' ) .'</th></thead>';
                echo '<tbody>';

                $tr_class = '';
                foreach ( $packages as $x => $package ) {
                    $products = $package['contents'];
                    $item_meta = '';
                    foreach ( $products as $i => $product ) {
                        $tr_class = ($tr_class == '' ) ? 'alt-table-row' : '';

                        if (isset($product['data']->item_meta) && !empty($product['data']->item_meta)) {
                            $item_meta .= '<pre>';
                            foreach ($product['data']->item_meta as $meta) {
                                $item_meta .= $meta['meta_name'] .': '. $meta['meta_value'] ."\n";
                            }
                            $item_meta .= '</pre>';
                        }

                        echo '<tr class="'. $tr_class .'">';
                        echo '<td class="product-name"><a href="'. get_permalink($product['data']->id) .'">'. get_the_title($product['data']->id) .'</a><br />'. $item_meta .'</td>';
                        echo '<td class="product-quantity">'. $product['quantity'] .'</td>';
                        echo '<td class="product-address"><address>'. wcms_get_formatted_address( $package['full_address'] ) .'</td>';
                        echo '</tr>';
                    }
                }

                echo '</table>';
            }
        }

        function email_shipping_table($order) {
            $this->list_order_item_addresses( $order );
        }

        function list_order_item_addresses( $order_id ) {
            global $woocommerce;

            if ( false == apply_filters( 'wcms_list_order_item_addresses', true, $order_id ) )
                return;

            if ( $order_id instanceof WC_Order ) {
                $order      = $order_id;
                $order_id   = $order->id;
            } else {
                $order = WC_MS_Compatibility::wc_get_order( $order_id );
            }

            $methods            = get_post_meta($order_id, '_shipping_methods', true);
            $shipping_methods   = $order->get_shipping_methods();
            $packages           = get_post_meta($order_id, '_wcms_packages', true);
            $multiship          = get_post_meta($order_id, '_multiple_shipping', true);

            if ( ( !$packages || count($packages) == 1 ) )
                return;

            // load the address fields
            $this->load_cart_files();

            $cart = new WC_Cart();

            echo '<p><strong>'. __( 'This order ships to multiple addresses.', 'wc_shipping_multiple_address' ) .'</strong></p>';
            echo '<table class="shop_table shipping_packages" cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #eee;" border="1" bordercolor="#eee">';
            echo '<thead><tr>';
            echo '<th scope="col" style="text-align:left; border: 1px solid #eee;">'. __( 'Products', 'woocommerce' ) .'</th>';
            echo '<th scope="col" style="text-align:left; border: 1px solid #eee;">'. __( 'Address', 'woocommerce' ) .'</th>';
            echo '<th scope="col" style="text-align:left; border: 1px solid #eee;">'. __( 'Notes', 'woocommerce' ) .'</th>';
            echo '</tr></thead><tbody>';

            foreach ( $packages as $x => $package ) {
                $products   = $package['contents'];
                $method     = $methods[$x]['label'];

                foreach ( $shipping_methods as $ship_method ) {
                    if ($ship_method['method_id'] == $method) {
                        $method = $ship_method['name'];
                        break;
                    }
                }

                $address = '';

                if ( !empty($package['full_address']) )
                    $address = wcms_get_formatted_address($package['full_address']);

                ?>
                <tr>
                    <td style="text-align:left; vertical-align:middle; border: 1px solid #eee;"><ul>
                    <?php foreach ( $products as $i => $product ): ?>
                        <li><?php echo get_the_title($product['data']->id) .' &times; '. $product['quantity'] .'<br />'. $cart->get_item_data($product, true); ?></li>
                    <?php endforeach; ?>
                    </ul></td>
                    <td style="text-align:left; vertical-align:middle; border: 1px solid #eee;">
                        <?php echo $address; ?>
                        <br/>
                        <em>(<?php echo $method; ?>)</em>
                    </td>
                    <td style="text-align:left; vertical-align:middle; border: 1px solid #eee;">
                        <?php
                        if ( !empty( $package['note'] ) ) {
                            echo $package['note'];
                        } else {
                            echo '&ndash;';
                        }

                        if ( !empty( $package['date'] ) ) {
                            echo '<p>'. sprintf(__('Delivery date: %s', 'wc_shipping_multiple_address'), $package['date']) .'</p>';
                        }
                        ?>
                    </td>
                </tr>
                <?php
            }
            echo '</table>';
        }

        function save_addresses() {
            global $woocommerce;

            if (isset($_POST['shipping_address_action']) && $_POST['shipping_address_action'] == 'save' ) {
                /* @var $cart WC_Cart */
                $cart       = $woocommerce->cart;
                $checkout   = $woocommerce->checkout;

                $fields = $woocommerce->countries->get_address_fields( $woocommerce->countries->get_base_country(), 'shipping_' );

                $cart->get_cart_from_session();
                $cart_items = wcms_get_real_cart_items();

                $data   = array();
                $rel    = array();

                if ( isset($_POST['items']) ) {

                    $items = $_POST['items'];

                    // handler for delete requests
                    if ( isset($_POST['delete_line']) ) {
                        $delete     = $_POST['delete'];
                        $cart_key   = $delete['key'];
                        $index      = $delete['index'];

                        // trim the quantity by 1 and remove the corresponding address
                        $cart_items = wcms_get_real_cart_items();
                        $item_qty   = $cart_items[$cart_key]['quantity'] - 1;
                        $cart->set_quantity( $cart_key, $item_qty );

                        if ( isset($items[$cart_key]['qty'][$index]) )
                            unset( $items[$cart_key]['qty'][$index] );

                        if ( isset($items[$cart_key]['address'][$index]) )
                            unset( $items[$cart_key]['address'][$index] );

                    }

                    // handler for quantities update
                    foreach ( $items as $cart_key => $item ) {
                        $qtys           = $item['qty'];
                        $item_addresses = $item['address'];

                        foreach ( $item_addresses as $idx => $item_address ) {
                            $cart_items     = wcms_get_real_cart_items();
                            $new_qty        = false;

                            if ( $qtys[ $idx ] == 0 ) {
                                // decrement the cart item quantity by one
                                $current_qty = $cart_items[ $cart_key ]['quantity'];
                                $new_qty        = $current_qty - 1;
                                $cart->set_quantity( $cart_key, $new_qty );
                            } elseif ( $qtys[ $idx ] > 1 ) {
                                $qty_to_add = $qtys[$idx] - 1;
                                $item_qty   = $cart_items[$cart_key]['quantity'];
                                $new_qty    = $item_qty + $qty_to_add;
                                $cart->set_quantity( $cart_key, $new_qty );
                            }

                        }

                    }

                    $cart_items = wcms_get_real_cart_items();
                    foreach ( $items as $cart_key => $item ) {
                        $qtys           = $item['qty'];
                        $item_addresses = $item['address'];

                        $product_id = $cart_items[$cart_key]['product_id'];
                        $sig        = $cart_key .'_'. $product_id .'_';
                        $_sig       = '';

                        foreach ( $item_addresses as $idx => $item_address ) {
                            $address_id = $item_address;
                            $i = 1;
                            for ( $x = 0; $x < $qtys[$idx]; $x++ ) {

                                $rel[ $address_id ][]  = $cart_key;

                                while ( isset($data['shipping_first_name_'. $sig . $i]) ) {
                                    $i++;
                                }
                                $_sig = $sig . $i;

                                if ( $fields ) foreach ( $fields as $key => $field ) :
                                    $data[$key .'_'. $_sig] = $_POST[$key .'_'. $address_id];
                                endforeach;
                            }

                        }

                        $cart_address_ids_session = (array)wcms_session_get( 'cart_address_ids' );

                        if ( !empty($_sig) && !wcms_session_isset( 'cart_address_ids' ) || ! in_array($_sig, $cart_address_ids_session) ) {
                            $cart_address_sigs_session = wcms_session_get( 'cart_address_sigs' );
                            $cart_address_sigs_session[$_sig] = $address_id;
                            wcms_session_set( 'cart_address_sigs', $cart_address_sigs_session);
                        }

                    }

                }

                wcms_session_set( 'cart_item_addresses', $data );
                wcms_session_set( 'address_relationships', $rel );
                wcms_session_set( 'wcms_item_addresses', $rel );

                if ( isset($_POST['update_quantities']) || isset($_POST['delete_line']) ) {
                    $next_url = get_permalink( woocommerce_get_page_id( 'multiple_addresses' ) );
                } else {
                    // redirect to the checkout page
                    $next_url = $woocommerce->cart->get_checkout_url();
                }

                $this->clear_packages_cache();

                wp_redirect($next_url);
                exit;
            } elseif (isset($_POST['shipping_account_address_action']) && $_POST['shipping_account_address_action'] == 'save' ) {
                $user   = wp_get_current_user();
                $idx    = $_POST['idx'];

                $addresses = get_user_meta( $user->ID, 'wc_other_addresses', true );

                if ( !is_array( $addresses ) ) {
                    $addresses = array();
                }

                if ( $idx == -1 ) {
                    $idx = count( $addresses );

                    while ( array_key_exists( $idx, $addresses ) ) {
                        $idx++;
                    }
                }

                unset($_POST['shipping_account_address_action'], $_POST['set_addresses'], $_POST['idx']);

                foreach ($_POST as $key => $value) {
                    $addresses[ $idx ][ $key ] = $value;
                }


                update_user_meta( $user->ID, 'wc_other_addresses', $addresses );

                if ( function_exists('wc_add_notice') )
                    wc_add_notice( __( 'Address saved', 'wc_shipping_multiple_address' ), 'success' );
                else
                    $woocommerce->add_message(__( 'Address saved', 'wc_shipping_multiple_address' ) );

                $page_id = woocommerce_get_page_id( 'myaccount' );
                wp_redirect(get_permalink($page_id));
                exit;
            }
        }


        function view_order($order_id) {
            global $woocommerce;

            $addresses = get_post_meta($order_id, '_shipping_addresses', true);
            $packages  = get_post_meta($order_id, '_wcms_packages', true);

            if ( count($packages) <= 1 ) return;

            $page_id = woocommerce_get_page_id( 'multiple_addresses' );
            $url = add_query_arg( 'order_id', $order_id, get_permalink($page_id));
            echo '<div class="woocommerce_message woocommerce-message">'. __( 'This order ships to multiple addresses.', 'wc_shipping_multiple_address' ) .' <a class="button" href="'. $url .'">'. __( 'View Addresses', 'wc_shipping_multiple_address' ) .'</a></div>';
        }

        function display_shipping_methods() {
            global $woocommerce;

            $packages = $woocommerce->cart->get_shipping_packages();

            if (! $this->cart_is_eligible_for_multi_shipping() )
                return;

            $sess_cart_addresses = wcms_session_get( 'cart_item_addresses' );
            if ( isset($sess_cart_addresses) && !empty($sess_cart_addresses) ) {
                // always allow users to select shipping
                $this->render_shipping_row($packages, 0);
            } else {
                if ( $this->packages_have_different_origins($packages) || $this->packages_have_different_methods($packages) ) {
                    // show shipping methods available to each package
                    $this->render_shipping_row($packages, 1);
                } else {
                    if ( $this->packages_contain_methods($packages) ) {
                        // methods must be combined
                        $this->render_shipping_row($packages, 2);
                    }
                }
            }

        }

        /**
         * @param array $packages
         * @param int $type 0=multi-shipping; 1=different packages; 2=same packages
         */
        function render_shipping_row($packages, $type = 2) {
            global $woocommerce;

            $page_id            = woocommerce_get_page_id( 'multiple_addresses' );
            $_tax               = new WC_Tax;
            $rates_available    = false;

            if ( function_exists('wc_add_notice') ) {
                $available_methods  = $this->get_available_shipping_methods();
            } else {
                $available_methods  = $woocommerce->shipping->get_available_shipping_methods();
            }

            $field_name         = 'shipping_methods';
            $post               = array();

            if ( function_exists('wc_add_notice') ) {
                $field_name = 'shipping_method';
            }

            if ( isset($_POST['post_data']) ) {
                parse_str($_POST['post_data'], $post);
            }

            if ( $type == 0 || $type == 1):

            ?>
            <tr class="multi_shipping">
                <td style="vertical-align: top;" colspan="<?php if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) echo '2'; else echo '1'; ?>">
                    <?php _e( 'Shipping Methods', 'wc_shipping_multiple_address' ); ?>

                    <div id="shipping_addresses">
                        <?php
                        $tips = array();
                        foreach ($packages as $x => $package):
                            $package        = $woocommerce->shipping->calculate_shipping_for_package( $package );
                            $has_address    = true;

                            if (! isset($package['full_address']) || empty($package['full_address']) ) {
                                $has_address = false;
                            } elseif ( $this->is_address_empty( $package['full_address'] ) ) {
                                $has_address = false;
                            } elseif ( $this->is_address_empty( $package['destination'] ) ) {
                                $has_address = false;
                            } elseif ( !isset( $package['rates'] ) || empty( $package['rates'] ) ) {
                                $has_address = false;
                            }

                            if (! $has_address ) {
                                // we have cart items with no set address
                                $products           = $package['contents'];
                                ?>
                                <div class="ship_address no_shipping_address">
                                    <em><?php _e('The following items do not have shipping addresses assigned.', 'wc_shipping_multiple_address'); ?></em>
                                    <ul>
                                    <?php
                                        foreach ($products as $i => $product):
                                            $attributes = $woocommerce->cart->get_item_data($product);
                                            ?>
                                            <li>
                                                <strong><?php echo get_the_title($product['data']->id); ?> x <?php echo $product['quantity']; ?></strong>
                                                <?php
                                                if ( !empty( $attributes ) ) {
                                                    echo '<small class="data">'. str_replace( "\n", "<br/>", $attributes ) .'</small>';
                                                }
                                                ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                        <?php
                                        $sess_cart_addresses = wcms_session_get( 'cart_item_addresses' );
                                        //if ( $sess_cart_addresses && !empty($sess_cart_addresses) ) {
                                            echo '<p style="text-align: center"><a href="'. get_permalink($page_id) .'" class="button modify-address-button">'. __( 'Assign Shipping Address', 'wc_shipping_multiple_address' ) .'</a></p>';
                                        //}
                                ?>
                                </div>
                                <?php
                                continue;
                            }

                            $shipping_methods   = array();
                            $products           = $package['contents'];
                            //$shipping_methods   = $package['rates'];
                            $selected           = wcms_session_get('shipping_methods');
                            $rates_available    = true;

                            if ( $type == 0 ):
                        ?>
                        <div class="ship_address">
                            <dl>
                            <?php
                                foreach ($products as $i => $product):
                                    $attributes = $woocommerce->cart->get_item_data( $product, true );
                            ?>
                            <dd>
                                <strong><?php echo get_the_title($product['data']->id); ?> x <?php echo $product['quantity']; ?></strong>
                                <?php
                                    if ( !empty( $attributes ) ) {
                                        echo '<small class="data">'. str_replace( "\n", "<br/>", $attributes )  .'</small>';
                                    }
                                ?>
                            </dd>
                                <?php endforeach; ?>
                            </dl>
                                <?php
                                $formatted_address = wcms_get_formatted_address( $package['full_address'] );
                                echo '<address>'. $formatted_address .'</address><br />'; ?>
                                <?php

                                do_action( 'wc_ms_shipping_package_block', $x, $package );

                                // If at least one shipping method is available
                                $ship_package['rates'] = array();

                                foreach ( $package['rates'] as $rate ) {
                                    $ship_package['rates'][$rate->id] = $rate;
                                }
                                
                                foreach ( $ship_package['rates'] as $method ) {
                                    if ( $method->id == 'multiple_shipping' ) continue;

                                    $method->label = esc_html( $method->label );

                                    if ( $method->cost > 0 ) {
                                        $shipping_tax = $method->get_shipping_tax();
                                        $method->label .= ' &mdash; ';

                                        // Append price to label using the correct tax settings
                                        if ( $woocommerce->cart->display_totals_ex_tax || ! $woocommerce->cart->prices_include_tax ) {

                                            if ( $shipping_tax > 0 ) {
                                                if ( $woocommerce->cart->prices_include_tax ) {
                                                    $method->label .= woocommerce_price( $method->cost ) .' '.$woocommerce->countries->ex_tax_or_vat();
                                                } else {
                                                    $method->label .= woocommerce_price( $method->cost );
                                                }
                                            } else {
                                                $method->label .= woocommerce_price( $method->cost );
                                            }
                                        } else {
                                            $method->label .= woocommerce_price( $method->cost + $shipping_tax );
                                            if ( $shipping_tax > 0 && ! $woocommerce->cart->prices_include_tax ) {
                                                $method->label .= ' '.$woocommerce->countries->inc_tax_or_vat();
                                            }
                                        }
                                    }

                                    $shipping_methods[] = $method;
                                }

                                // Print the single available shipping method as plain text
                                if ( 1 === count( $shipping_methods ) ) {
                                    $method = $shipping_methods[0];

                                    echo $method->label;
                                    echo '<input type="hidden" class="shipping_methods shipping_method" name="'. $field_name .'['. $x .']" value="'.esc_attr( $method->id ).'">';

                                // Show multiple shipping methods in a select list
                                } elseif ( count( $shipping_methods ) > 1 ) {
                                    echo '<select class="shipping_methods shipping_method" name="'. $field_name .'['. $x .']">';

                                    foreach ( $package['rates'] as $rate ) {
                                        if ( $rate->id == 'multiple_shipping' ) continue;
                                        $sel = '';

                                        if ( isset($selected[$x]) && $selected[$x]['id'] == $rate->id ) $sel = 'selected';

                                        echo '<option value="'.esc_attr( $rate->id ).'" '. $sel .'>';
                                        echo strip_tags( $rate->label );
                                        echo '</option>';
                                    }

                                    echo '</select>';
                                } else {
                                    echo '<p>'.__( '(1) Sorry, it seems that there are no available shipping methods for your state. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce' ).'</p>';
                                }

                                $sess_cart_addresses = wcms_session_get( 'cart_item_addresses' );
                                if ( $sess_cart_addresses && !empty($sess_cart_addresses) ) {
                                    echo '<p><a href="'. get_permalink($page_id) .'" class="modify-address-button">'. __( 'Modify address', 'wc_shipping_multiple_address' ) .'</a></p>';
                                }
                        ?>
                        </div>
                        <?php
                            elseif ($type == 1):
                        ?>
                        <div class="ship_address">
                            <dl>
                            <?php
                                foreach ($products as $i => $product):
                                    $attributes = $woocommerce->cart->get_item_data($product);
                            ?>
                            <dd>
                                <strong><?php echo get_the_title($product['data']->id); ?> x <?php echo $product['quantity']; ?></strong>
                                    <?php
                                    if ( !empty($attributes) ) {
                                        echo '<small class="data">'. str_replace( "\n", "<br/>", $attributes )  .'</small>';
                                    }
                                    ?>
                            </dd>
                                <?php endforeach; ?>
                            </dl>
                            <?php
                                // If at least one shipping method is available
                                // Calculate shipping method rates
                                $ship_package['rates'] = array();

                                foreach ( $woocommerce->shipping->load_shipping_methods( $package ) as $shipping_method ) {

                                    if ( isset($package['method']) && !in_array($shipping_method->id, $package['method']) ) continue;

                                    if ( $shipping_method->is_available( $package ) ) {

                                        // Reset Rates
                                        $shipping_method->rates = array();

                                        // Calculate Shipping for package
                                        $shipping_method->calculate_shipping( $package );

                                        // Place rates in package array
                                        if ( ! empty( $shipping_method->rates ) && is_array( $shipping_method->rates ) )
                                            foreach ( $shipping_method->rates as $rate )
                                                $ship_package['rates'][$rate->id] = $rate;
                                    }

                                }

                                foreach ( $ship_package['rates'] as $method ) {
                                    if ( $method->id == 'multiple_shipping' ) continue;

                                    $method->label = esc_html( $method->label );

                                    if ( $method->cost > 0 ) {
                                        $shipping_tax = $method->get_shipping_tax();
                                        $method->label .= ' &mdash; ';

                                        // Append price to label using the correct tax settings
                                        if ( $woocommerce->cart->display_totals_ex_tax || ! $woocommerce->cart->prices_include_tax ) {

                                            if ( $shipping_tax > 0 ) {
                                                if ( $woocommerce->cart->prices_include_tax ) {
                                                    $method->label .= woocommerce_price( $method->cost ) .' '.$woocommerce->countries->ex_tax_or_vat();
                                                } else {
                                                    $method->label .= woocommerce_price( $method->cost );
                                                }
                                            } else {
                                                $method->label .= woocommerce_price( $method->cost );
                                            }
                                        } else {
                                            $method->label .= woocommerce_price( $method->cost + $shipping_tax );
                                            if ( $shipping_tax > 0 && ! $woocommerce->cart->prices_include_tax ) {
                                                $method->label .= ' '.$woocommerce->countries->inc_tax_or_vat();
                                            }
                                        }
                                    }

                                    $shipping_methods[] = $method;
                                }

                                // Print a single available shipping method as plain text
                                if ( 1 === count( $shipping_methods ) ) {
                                    $method = $shipping_methods[0];

                                    echo $method->label;
                                    echo '<input type="hidden" class="shipping_methods shipping_method" name="'. $field_name .'['. $x .']" value="'.esc_attr( $method->id ).'||'. strip_tags($method->label) .'">';

                                // Show multiple shipping methods in a select list
                                } elseif ( count( $shipping_methods ) > 1 ) {
                                    echo '<select class="shipping_methods shipping_method" name="'. $field_name .'['. $x .']">';
                                    foreach ( $shipping_methods as $method ) {
                                        if ($method->id == 'multiple_shipping' ) continue;
                                        $current_selected = ( isset($selected[ $x ])  ) ? $selected[ $x ]['id'] : '';
                                        echo '<option value="'.esc_attr( $method->id ).'||'. strip_tags($method->label) .'" '.selected( $current_selected, $method->id, false).'>';

                                        if ( function_exists('wc_cart_totals_shipping_method_label') )
                                            echo wp_kses_post( wc_cart_totals_shipping_method_label( $method ));
                                        else
                                            echo strip_tags( $method->label );

                                        echo '</option>';
                                    }
                                    echo '</select>';
                                } else {
                                    echo '<p>'.__( '(2) Sorry, it seems that there are no available shipping methods for your state. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce' ).'</p>';
                                }

                                $sess_cart_addresses = wcms_session_get( 'cart_item_addresses' );
                                if ( $sess_cart_addresses && !empty($sess_cart_addresses) ) {
                                    echo '<p><a href="'. get_permalink($page_id) .'" class="modify-address-button">'. __( 'Modify address', 'wc_shipping_multiple_address' ) .'</a></p>';
                                }
                        ?>
                        </div>
                        <?php endif;

                        endforeach; ?>
                        <div style="clear:both;"></div>

                        <?php if (! function_exists('wc_add_notice') ): ?>
                        <input type="hidden" name="shipping_method" value="multiple_shipping" />
                        <?php endif; ?>
                    </div>

                </td>
                <td style="vertical-align: top;">
                    <?php
                    $shipping_total = $woocommerce->cart->shipping_total;
                    $shipping_tax   = $woocommerce->cart->shipping_tax_total;
                    $inc_or_exc_tax = '';

                    if ( $shipping_total > 0 ) {

                        // Append price to label using the correct tax settings
                        if ( $woocommerce->cart->display_totals_ex_tax || ! $woocommerce->cart->prices_include_tax ) {

                            if ( $shipping_tax > 0 ) {

                                if ( $woocommerce->cart->prices_include_tax ) {
                                    $shipping_total = $shipping_total;
                                    $inc_or_exc_tax = $woocommerce->countries->ex_tax_or_vat();
                                } else {
                                    $shipping_total += $shipping_tax;
                                    $inc_or_exc_tax = $woocommerce->countries->inc_tax_or_vat();
                                }
                            }
                        } else {
                            $shipping_total += $shipping_tax;

                            if ( $shipping_tax > 0 && ! $woocommerce->cart->prices_include_tax ) {
                                $inc_or_exc_tax = $woocommerce->countries->inc_tax_or_vat();
                            }
                        }
                    }

                    echo woocommerce_price( $shipping_total ) .' '. $inc_or_exc_tax;
                    ?>
                </td>
                <script type="text/javascript">
                    jQuery(document).ready(function() {
                        jQuery("tr.shipping").remove();
                    });
                <?php
                if ( null == wcms_session_get('shipping_methods') && $rates_available ) {
                    echo 'jQuery("body").trigger("update_checkout");';
                }
                ?>
                </script>
            </tr>
            <?php
            else:
            ?>
            <tr class="multi_shipping">
                <td style="vertical-align: top;" colspan="<?php if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) echo '2'; else echo '1'; ?>">
                    <?php _e( 'Shipping Methods', 'wc_shipping_multiple_address' ); ?>

                    <?php
                    $tips = array();
                    foreach ($packages as $x => $package):
                        $shipping_methods   = array();
                        $products           = $package['contents'];

                        if ($type == 2):
                            // If at least one shipping method is available
                            // Calculate shipping method rates
                            $ship_package['rates'] = array();

                            foreach ( $woocommerce->shipping->load_shipping_methods( $package ) as $shipping_method ) {

                                if ( isset($package['method']) && !in_array($shipping_method->id, $package['method']) ) continue;

                                if ( $shipping_method->is_available( $package ) ) {

                                    // Reset Rates
                                    $shipping_method->rates = array();

                                    // Calculate Shipping for package
                                    $shipping_method->calculate_shipping( $package );

                                    // Place rates in package array
                                    if ( ! empty( $shipping_method->rates ) && is_array( $shipping_method->rates ) )
                                        foreach ( $shipping_method->rates as $rate )
                                            $ship_package['rates'][$rate->id] = $rate;
                                }

                            }

                            foreach ( $ship_package['rates'] as $method ) {
                                if ( $method->id == 'multiple_shipping' ) continue;

                                $method->label = esc_html( $method->label );

                                if ( $method->cost > 0 ) {
                                    $method->label .= ' &mdash; ';

                                    // Append price to label using the correct tax settings
                                    if ( $woocommerce->cart->display_totals_ex_tax || ! $woocommerce->cart->prices_include_tax ) {
                                    $method->label .= woocommerce_price( $method->cost );
                                        if ( $method->get_shipping_tax() > 0 && $woocommerce->cart->prices_include_tax ) {
                                            $method->label .= ' '.$woocommerce->countries->ex_tax_or_vat();
                                }
                                    } else {
                                        $method->label .= woocommerce_price( $method->cost + $method->get_shipping_tax() );
                                        if ( $method->get_shipping_tax() > 0 && ! $woocommerce->cart->prices_include_tax ) {
                                            $method->label .= ' '.$woocommerce->countries->inc_tax_or_vat();
                                        }
                                    }
                                }
                                $shipping_methods[] = $method;
                            }

                            // Print a single available shipping method as plain text
                            if ( 1 === count( $shipping_methods ) ) {
                                $method = $shipping_methods[0];
                                echo $method->label;
                                echo '<input type="hidden" class="shipping_methods shipping_method" name="'. $field_name .'['. $x .']" value="'.esc_attr( $method->id ).'">';

                            // Show multiple shipping methods in a select list
                            } elseif ( count( $shipping_methods ) > 1 ) {
                                echo '<select class="shipping_methods shipping_method" name="'. $field_name .'['. $x .']">';
                                foreach ( $shipping_methods as $method ) {
                                    if ($method->id == 'multiple_shipping' ) continue;
                                    echo '<option value="'.esc_attr( $method->id ).'" '.selected( $method->id, (isset($post['shipping_method'])) ? $post['shipping_method'] : '', false).'>';
                                    echo strip_tags( $method->label );
                                    echo '</option>';
                                }
                                echo '</select>';
                            } else {
                                echo '<p>'.__( '(3) Sorry, it seems that there are no available shipping methods for your state. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce' ).'</p>';
                            }

                            $sess_cart_addresses = wcms_session_get( 'cart_item_addresses' );
                            if ( $sess_cart_addresses && !empty($sess_cart_addresses) ) {
                                echo '<p><a href="'. get_permalink($page_id) .'" class="modify-address-button">'. __( 'Modify address', 'wc_shipping_multiple_address' ) .'</a></p>';
                            }
                        endif;
                    endforeach;
                    ?>
                </td>
                <td style="vertical-align: top;"><?php echo woocommerce_price( $woocommerce->cart->shipping_total + $woocommerce->cart->shipping_tax_total ); ?></td>
                <script type="text/javascript">
                jQuery("tr.shipping").remove();
                <?php
                if ( null == wcms_session_get('shipping_methods') && $rates_available ) {
                    echo 'jQuery("body").trigger("update_checkout");';
                }
                ?>
                </script>
            </tr>
            <?php
            endif;
        }

        public function get_available_shipping_methods() {
            global $woocommerce;

            $packages = $woocommerce->cart->get_shipping_packages();

            // Loop packages and merge rates to get a total for each shipping method
            $available_methods = array();

            foreach ( $packages as $package ) {
                if ( !isset($package['rates']) || !$package['rates'] ) continue;

                foreach ( $package['rates'] as $id => $rate ) {

                    if ( isset( $available_methods[$id] ) ) {
                        // Merge cost and taxes - label and ID will be the same
                        $available_methods[$id]->cost += $rate->cost;

                        foreach ( array_keys( $available_methods[$id]->taxes + $rate->taxes ) as $key ) {
                            $available_methods[$id]->taxes[$key] = ( isset( $rate->taxes[$key] ) ? $rate->taxes[$key] : 0 ) + ( isset( $available_methods[$id]->taxes[$key] ) ? $available_methods[$id]->taxes[$key] : 0 );
                        }
                    } else {
                        $available_methods[$id] = $rate;
                    }

                }

            }

            return apply_filters( 'wcms_available_shipping_methods', $available_methods );
        }

        /*function available_shipping_methods($shipping_methods) {

            if ( !wcms_session_isset( 'wcms_packages' ) && isset($shipping_methods['multiple_shipping']) ) {
                unset($shipping_methods['multiple_shipping']);
            }

            return $shipping_methods;
        }*/

        function shipping_packages($packages) {
            global $woocommerce;

            if ( defined('SHIPPING_PACKAGES_SET') )
                return $packages;

            $myPackages     = array();
            $settings       = $this->settings;
            $methods        = (wcms_session_isset( 'shipping_methods' )) ? wcms_session_get( 'shipping_methods' ) : array();

            $sess_cart_addresses = wcms_session_get( 'cart_item_addresses' );

            if ( is_null($sess_cart_addresses) || empty($sess_cart_addresses) ) {
                // multiple shipping is not set up
                // check if items have different origins

                if (wcms_count_real_cart_items() > 0) foreach (wcms_get_real_cart_items() as $cart_item_key => $values) {
                    $product_id     = $values['product_id'];
                    $product_cats   = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) );

                    // look for direct product matches
                    $matched = false;
                    foreach ( $settings as $idx => $setting ) {
                        if ( in_array($product_id, $setting['products']) ) {
                            $matched = $setting;
                            break;
                        }
                    }

                    if (! $matched ) {
                        // look for category matches
                        foreach ( $settings as $idx => $setting ) {
                            foreach ( $product_cats as $product_cat_id ) {
                                if ( in_array($product_cat_id, $setting['categories']) ) {
                                    $matched = $setting;
                                    break;
                                }
                            }
                        }
                    }

                    if ( $matched !== false ) {

                        // create or update package
                        $existing = false;
                        if ( !empty($myPackages) ) foreach ( $myPackages as $idx => $my_pkg ) {
                            if (
                                (isset($my_pkg['origin']) && $my_pkg['origin'] == $matched['zip'])
                                && (isset($my_pkg['method']) && $my_pkg['method'] == $matched['method'])
                            ) {
                                $existing = true;
                                $values['package_idx'] = $idx;
                                $myPackages[$idx]['contents'][$cart_item_key] = $values;
                                $myPackages[$idx]['contents_cost'] += $values['line_total'];

                                if ( isset($methods[$idx]) ) {
                                    $myPackages[$idx]['selected_method'] = $methods[$idx];
                                }

                                // modify the cart entry
                                $woocommerce->cart->cart_contents[$cart_item_key] = $values;
                                break;
                            }
                        }

                        if ( ! $existing ) {
                            $values['package_idx'] = count($myPackages);
                            $pkg = array(
                                'contents'          => array($cart_item_key => $values),
                                'contents_cost'     => $values['line_total'],
                                //'origin'            => $matched['zip'],
                                'method'            => $matched['method'],
                                'destination'       => $packages[0]['destination']
                            );

                            if (isset($methods[$idx])) {
                                $pkg['selected_method'] = $methods[$idx];
                            }
                            $myPackages[] = $pkg;

                            // modify the cart entry
                            $woocommerce->cart->cart_contents[$cart_item_key] = $values;
                        }
                    }
                }

                if (! empty($myPackages) && count($myPackages) > 1 ) {
                    if ( function_exists('wc_enqueue_js') ) {
                        wc_enqueue_js( '_multi_shipping = true;' );
                    } else {
                        $woocommerce->add_inline_js( '_multi_shipping = true;' );
                    }

                    $packages = $myPackages;
                }

            } else {

                // group items into ship-to addresses
                $addresses      = wcms_session_get( 'cart_item_addresses' );

                $productsArray  = array();
                $address_fields = $woocommerce->countries->get_address_fields( $woocommerce->countries->get_base_country(), 'shipping_' );

                if (wcms_count_real_cart_items()>0) foreach (wcms_get_real_cart_items() as $cart_item_key => $values) {
                    $qty = $values['quantity'];

                    for ($i = 1; $i <= $qty; $i++) {
                        if ( isset($addresses['shipping_first_name_'. $cart_item_key .'_'. $values['product_id'] .'_'. $i]) ) {
                            $address = array();

                            foreach ( $address_fields as $field_name => $field ) {
                                $addr_key = str_replace('shipping_', '', $field_name);
                                $address[$addr_key] = ( isset($addresses[ $field_name .'_'. $cart_item_key .'_'. $values['product_id'] .'_'. $i]) ) ? $addresses[$field_name .'_'. $cart_item_key .'_'. $values['product_id'] .'_'. $i] : '';
                            }
                        } else {
                            $address = array();

                            foreach ( $address_fields as $field_name => $field ) {
                                $addr_key = str_replace('shipping_', '', $field_name);
                                $address[$addr_key] = '';
                            }
                        }

                        $currentAddress = wcms_get_formatted_address( $address );
                        $key            = md5($currentAddress);
                        $_value         = $values;

                        $price          = $_value['line_total'] / $qty;
                        $tax            = $_value['line_tax'] / $qty;
                        $sub            = $_value['line_subtotal'] / $qty;
                        $subTax         = $_value['line_subtotal_tax'] / $qty;

                        $_value['quantity']             = 1;
                        $_value['line_total']           = $price;
                        $_value['line_tax']             = $tax;
                        $_value['line_subtotal']        = $sub;
                        $_value['line_subtotal_tax']    = $subTax;
                        $meta                           = md5($woocommerce->cart->get_item_data($_value));

                        //$origin = $this->get_product_origin( $values['product_id'] );
                        $origin = false;
                        $method = $this->get_product_shipping_method( $values['product_id'] );
                        // if origins and/or shipping method are set, group using origins and shipping methods
                        // if origins and/or shipping method are set, group using origins and shipping methods
                        if (! $origin ) $origin = '';
                        if (! $method ) $method = '';

                        if ( !empty($origin) || !empty($method) ) $key .= $origin . $method;

                        // no origin and method selected
                        if (isset($productsArray[$key])) {
                            // if the same product exists, add to the qty and cost
                            $found = false;
                            foreach ($productsArray[$key]['products'] as $idx => $prod) {
                                if ($prod['id'] == $_value['product_id']) {
                                    if ($meta == $prod['meta']) {
                                        $found = true;
                                        $productsArray[$key]['products'][$idx]['value']['quantity'] += 1;
                                        $productsArray[$key]['products'][$idx]['value']['line_total'] += $_value['line_total'];
                                        $productsArray[$key]['products'][$idx]['value']['line_tax'] += $_value['line_tax'];
                                        $productsArray[$key]['products'][$idx]['value']['line_subtotal'] += $_value['line_subtotal'];
                                        $productsArray[$key]['products'][$idx]['value']['line_subtotal_tax'] += $_value['line_subtotal_tax'];
                                        break;
                                    }
                                }
                            }

                            if (! $found) {
                                // new product
                                $productsArray[$key]['products'][] = array(
                                    'id' => $_value['product_id'],
                                    'meta' => $meta,
                                    'value' => $_value
                                );
                            }
                        } else {
                            $productsArray[$key] = array(
                                'products'  => array(
                                    array(
                                        'id' => $_value['product_id'],
                                        'meta' => $meta,
                                        'value' => $_value
                                    )
                                ),
                                'country'   => $address['country'],
                                'state'     => $address['state'],
                                'postcode'  => $address['postcode'],
                                'address'   => $address
                            );
                        }

                        if ( !empty($origin) ) $productsArray[$key]['origin'] = $origin;
                        if ( !empty($method) ) $productsArray[$key]['method'] = $method;
                    }
                }

                if (! empty($productsArray)) {
                    $myPackages = array();
                    foreach ($productsArray as $idx => $group) {
                        $pkg = array(
                            'contents'          => array(),
                            'contents_cost'     => 0,
                            'destination'       => $group['address'],
                            'full_address'      => $group['address']
                        );

                        if ( isset($group['origin']) ) $pkg['origin'] = $group['origin'];
                        if ( isset($group['method']) ) $pkg['method'] = $group['method'];

                        if ( isset($methods[$idx]) ) {
                            $pkg['selected_method'] = $methods[$idx];
                        }

                        foreach ($group['products'] as $item) {
                            $data = (array) apply_filters( 'woocommerce_add_cart_item_data', array(), $item['value']['product_id'], $item['value']['variation_id'] );

                            // Composite Products support. Manually add the composite data in the cart_item_data array to match the existing cart_item_key
                            if ( isset( $item['value']['composite_data'] ) )
                                $data['composite_data'] = $item['value']['composite_data'];

                            if ( isset( $item['value']['composite_children'] ) )
                                $data['composite_children'] = array();

                            // gravity forms support
                            if ( isset( $item['value']['_gravity_form_data'] ) ) {
                                $data['_gravity_form_data'] = $item['value']['_gravity_form_data'];
                            }

                            if ( isset( $item['value']['_gravity_form_lead'] ) ) {
                                $data['_gravity_form_lead'] = $item['value']['_gravity_form_lead'];
                            }

                            $cart_item_id = $woocommerce->cart->generate_cart_id($item['value']['product_id'], $item['value']['variation_id'], $item['value']['variation'], $data);

                            $item['value']['package_idx'] = $idx;
                            $pkg['contents'][$cart_item_id] = $item['value'];
                            if ($item['value']['data']->needs_shipping()) {
                                $pkg['contents_cost'] += $item['value']['line_total'];
                            }
                        }
                        $myPackages[] = $pkg;
                    }

                    if ( count( $myPackages ) > 1 ) {
                        if ( function_exists('wc_enqueue_js') ) {
                            wc_enqueue_js( '_multi_shipping = true;' );
                        } else {
                            $woocommerce->add_inline_js( '_multi_shipping = true;' );
                        }
                    }

                    $packages = $myPackages;
                }

            }

            $packages = $this->normalize_packages_address( $packages );

            wcms_session_set( 'wcms_packages', $packages);

            return $packages;
        }

        function clear_session() {
            $packages = wcms_session_get('wcms_packages');

            // clear packages transient
            if ( is_array($packages) ) {
                foreach ( $packages as $package ) {
                    $package_hash = 'wc_ship_' . md5( json_encode( $package ) );
                    delete_transient( $package_hash );
                }
            }

            wcms_session_delete( 'cart_item_addresses' );
            wcms_session_delete( 'wcms_item_addresses' );
            wcms_session_delete( 'cart_address_sigs' );
            wcms_session_delete( 'address_relationships' );
            wcms_session_delete( 'shipping_methods' );
            wcms_session_delete( 'wcms_original_cart' );
            wcms_session_delete( 'wcms_packages' );

            do_action( 'wc_ms_cleared_session' );

        }

        function cart_updated() {
            global $woocommerce;

            $cart = $woocommerce->cart->get_cart();

            if ( empty($cart) || !self::cart_is_eligible_for_multi_shipping() ) {
                wcms_session_delete( 'cart_item_addresses' );
                wcms_session_delete( 'cart_address_sigs' );
                wcms_session_delete( 'address_relationships' );
                wcms_session_delete( 'shipping_methods' );
                wcms_session_delete( 'wcms_original_cart' );
            }
        }

        function get_package_shipping_rates( $package = array() ) {
            global $woocommerce;

            $_tax = new WC_Tax;

            // See if we have an explicitly set shipping tax class
            if ( $shipping_tax_class = get_option( 'woocommerce_shipping_tax_class' ) ) {
                $tax_class = $shipping_tax_class == 'standard' ? '' : $shipping_tax_class;
            }

            if ( isset($package['full_address']) && !empty($package['full_address']) ) {

                $country    = $package['full_address']['country'];
                $state      = $package['full_address']['state'];
                $postcode   = $package['full_address']['postcode'];
                $city       = $package['full_address']['city'];

            } else {

                // Prices which include tax should always use the base rate if we don't know where the user is located
                // Prices excluding tax however should just not add any taxes, as they will be added during checkout
                if ( get_option( 'woocommerce_prices_include_tax' ) == 'yes' || get_option( 'woocommerce_default_customer_address' ) == 'base' ) {
                    $country    = $woocommerce->countries->get_base_country();
                    $state      = $woocommerce->countries->get_base_state();
                    $postcode   = '';
                    $city       = '';
                } else {
                    return array();
                }

            }

            // If we are here then shipping is taxable - work it out

            // This will be per order shipping - loop through the order and find the highest tax class rate
            $found_tax_classes = array();
            $matched_tax_rates = array();
            $rates = false;

            // Loop cart and find the highest tax band
            if ( sizeof( $woocommerce->cart->get_cart() ) > 0 )
                foreach ( $woocommerce->cart->get_cart() as $item )
                    $found_tax_classes[] = $item['data']->get_tax_class();

            $found_tax_classes = array_unique( $found_tax_classes );

            // If multiple classes are found, use highest
            if ( sizeof( $found_tax_classes ) > 1 ) {

                if ( in_array( '', $found_tax_classes ) ) {
                    $rates = $_tax->find_rates( array(
                        'country'   => $country,
                        'state'     => $state,
                        'city'      => $city,
                        'postcode'  => $postcode,
                    ) );
                } else {
                    $tax_classes = array_filter( array_map( 'trim', explode( "\n", get_option( 'woocommerce_tax_classes' ) ) ) );

                    foreach ( $tax_classes as $tax_class ) {
                        if ( in_array( $tax_class, $found_tax_classes ) ) {
                            $rates = $_tax->find_rates( array(
                                'country'   => $country,
                                'state'     => $state,
                                'postcode'  => $postcode,
                                'city'      => $city,
                                'tax_class' => $tax_class
                            ) );
                            break;
                        }
                    }
                }

            // If a single tax class is found, use it
            } elseif ( sizeof( $found_tax_classes ) == 1 ) {

                $rates = $_tax->find_rates( array(
                    'country'   => $country,
                    'state'     => $state,
                    'postcode'  => $postcode,
                    'city'      => $city,
                    'tax_class' => $found_tax_classes[0]
                ) );

            }

            // If no class rate are found, use standard rates
            if ( ! $rates )
                $rates = $_tax->find_rates( array(
                    'country'   => $country,
                    'state'     => $state,
                    'postcode'  => $postcode,
                    'city'      => $city,
                ) );

            if ( $rates )
                foreach ( $rates as $key => $rate )
                    if ( isset( $rate['shipping'] ) && $rate['shipping'] == 'yes' )
                        $matched_tax_rates[ $key ] = $rate;

            return $matched_tax_rates;

        }

        function get_cart_item_subtotal( $cart_item ) {
            global $woocommerce;

            $_product   = $cart_item['data'];
            $quantity   = $cart_item['quantity'];

            $price      = $_product->get_price();
            $taxable    = $_product->is_taxable();

            if ( $taxable ) {

                if ( $woocommerce->cart->tax_display_cart == 'excl' ) {

                    $row_price        = $_product->get_price_excluding_tax( $quantity );

                } else {

                    $row_price        = $_product->get_price_including_tax( $quantity );

                }

                // Non-taxable
            } else {

                $row_price        = $price * $quantity;

            }

            return $row_price;

        }



        function get_product_origin( $product_id ) {
            $origin         = false;
            $settings       = $this->settings;
            $product_cats   = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) );

            // look for direct product matches
            $matched = false;
            foreach ( $settings as $idx => $setting ) {
                if ( in_array($product_id, $setting['products']) ) {
                    return $setting['zip'];
                }
            }

            if (! $matched ) {
                // look for category matches
                foreach ( $settings as $idx => $setting ) {
                    foreach ( $product_cats as $product_cat_id ) {
                        if ( in_array($product_cat_id, $setting['categories']) ) {
                            return $setting['zip'];
                        }
                    }
                }
            }

            //return $origin;
            return false;
        }

        function get_product_shipping_method( $product_id ) {
            $method         = false;
            $settings       = $this->settings;
            $product_cats   = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) );

            // look for direct product matches
            $matched = false;
            foreach ( $settings as $idx => $setting ) {
                if ( in_array($product_id, $setting['products']) ) {
                    return $setting['method'];
                }
            }

            if (! $matched ) {
                // look for category matches
                foreach ( $settings as $idx => $setting ) {
                    foreach ( $product_cats as $product_cat_id ) {
                        if ( in_array($product_cat_id, $setting['categories']) ) {
                            return $setting['method'];
                        }
                    }
                }
            }

            return $method;
        }

        function packages_have_different_methods($packages = array()) {
            $last_method    = false;
            $_return        = false;

            foreach ( $packages as $package ) {
                if ( isset($package['method']) ) {
                    if (! $last_method ) {
                        $last_method = $package['method'];
                    } else {
                        if ( $last_method != $package['method']) {
                            $_return = true;
                            break;
                        }
                    }
                }
            }

            return apply_filters( 'wc_ms_packages_have_different_methods', $_return, $packages );
        }

        function packages_have_different_origins($packages = array()) {
            $last_origin    = false;
            $_return        = false;

            foreach ( $packages as $package ) {
                if ( isset($package['origin']) ) {
                    if (! $last_origin ) {
                        $last_origin = $package['origin'];
                    } else {
                        if ( $last_origin != $package['origin']) {
                            $_return = true;
                            break;
                        }
                    }
                }
            }

            return apply_filters( 'wc_ms_packages_have_different_origins', $_return, $packages );
        }

        function packages_contain_methods( $packages = array() ) {
            $return = false;

            foreach ( $packages as $package ) {
                if ( isset($package['method'])) {
                    $return = true;
                    break;
                }
            }

            return apply_filters( 'wc_ms_packages_contain_methods', $return, $packages );
        }





        function get_user_addresses( $user ) {

            if (! $user instanceof WP_User )
                $user = new WP_User( $user );

            $addresses = array();

            if ($user->ID != 0) {
                $addresses = get_user_meta($user->ID, 'wc_other_addresses', true);

                if (! $addresses) {
                    $addresses = array();
                }

                $default_address = array(
                    'first_name' 	=> get_user_meta( $user->ID, 'shipping_first_name', true ),
                    'last_name'		=> get_user_meta( $user->ID, 'shipping_last_name', true ),
                    'company'		=> get_user_meta( $user->ID, 'shipping_company', true ),
                    'address_1'		=> get_user_meta( $user->ID, 'shipping_address_1', true ),
                    'address_2'		=> get_user_meta( $user->ID, 'shipping_address_2', true ),
                    'city'			=> get_user_meta( $user->ID, 'shipping_city', true ),
                    'state'			=> get_user_meta( $user->ID, 'shipping_state', true ),
                    'postcode'		=> get_user_meta( $user->ID, 'shipping_postcode', true ),
                    'country'		=> get_user_meta( $user->ID, 'shipping_country', true ),
                    'shipping_first_name' 	=> get_user_meta( $user->ID, 'shipping_first_name', true ),
                    'shipping_last_name'	=> get_user_meta( $user->ID, 'shipping_last_name', true ),
                    'shipping_company'		=> get_user_meta( $user->ID, 'shipping_company', true ),
                    'shipping_address_1'	=> get_user_meta( $user->ID, 'shipping_address_1', true ),
                    'shipping_address_2'	=> get_user_meta( $user->ID, 'shipping_address_2', true ),
                    'shipping_city'			=> get_user_meta( $user->ID, 'shipping_city', true ),
                    'shipping_state'		=> get_user_meta( $user->ID, 'shipping_state', true ),
                    'shipping_postcode'		=> get_user_meta( $user->ID, 'shipping_postcode', true ),
                    'shipping_country'		=> get_user_meta( $user->ID, 'shipping_country', true ),
                    'default_address'       => true
                );

                if ( $default_address['address_1'] && $default_address['postcode'] ) {
                    array_unshift($addresses, $default_address);
                }

            } else {
                // guest address - using sessions to store the address
                $addresses = ( wcms_session_isset('user_addresses') ) ? wcms_session_get('user_addresses') : array();
            }

            return $addresses;
        }

        function save_user_addresses( $user_id, $addresses ) {

            // remove the default address
            foreach ( $addresses as $x => $address ) {
                if ( isset( $address['default_address'] ) && $address['default_address'] ) {
                    unset( $addresses[ $x ] );
                }
            }

            if ( $user_id != 0 ) {
                update_user_meta($user_id, 'wc_other_addresses', $addresses);
            } else {
                wcms_session_set( 'user_addresses', $addresses );
            }

        }

        function array_sort($array, $on, $order=SORT_ASC)
        {
            $new_array = array();
            $sortable_array = array();

            if (count($array) > 0) {
                foreach ($array as $k => $v) {
                    if (is_array($v)) {
                        foreach ($v as $k2 => $v2) {
                            if ($k2 == $on) {
                                $sortable_array[$k] = $v2;
                            }
                        }
                    } else {
                        $sortable_array[$k] = $v;
                    }
                }

                switch ($order) {
                    case SORT_ASC:
                        asort($sortable_array);
                        break;
                    case SORT_DESC:
                        arsort($sortable_array);
                        break;
                }

                foreach ($sortable_array as $k => $v) {
                    $new_array[$k] = $array[$k];
                }
            }

            return $new_array;
        }

        function load_cart_files() {
            global $woocommerce;

            if ( file_exists($woocommerce->plugin_path() .'/classes/class-wc-cart.php') ) {
                require_once $woocommerce->plugin_path() .'/classes/abstracts/abstract-wc-session.php';
                require_once $woocommerce->plugin_path() .'/classes/class-wc-session-handler.php';
                require_once $woocommerce->plugin_path() .'/classes/class-wc-cart.php';
                require_once $woocommerce->plugin_path() .'/classes/class-wc-checkout.php';
                require_once $woocommerce->plugin_path() .'/classes/class-wc-customer.php';
            } else {
                require_once $woocommerce->plugin_path() .'/includes/abstracts/abstract-wc-session.php';
                require_once $woocommerce->plugin_path() .'/includes/class-wc-session-handler.php';
                require_once $woocommerce->plugin_path() .'/includes/class-wc-cart.php';
                require_once $woocommerce->plugin_path() .'/includes/class-wc-checkout.php';
                require_once $woocommerce->plugin_path() .'/includes/class-wc-customer.php';
            }

            if (! $woocommerce->session )
                $woocommerce->session = new WC_Session_Handler();

            if (! $woocommerce->customer )
                $woocommerce->customer = new WC_Customer();
        }

        function clear_packages_cache() {
            global $woocommerce;

            $woocommerce->cart->calculate_totals();
            $packages = $woocommerce->cart->get_shipping_packages();

            foreach ( $packages as $idx => $package ) {
                $package_hash   = 'wc_ship_' . md5( json_encode( $package ) );
                delete_transient( $package_hash );
            }
        }

        /**
         * This method copies the destination and full address from $base_package if it exists over to the current package index
         * @param $packages
         * @return array modified $packages
         */
        public function normalize_packages_address( $packages ) {

            $default = $this->get_default_shipping_address();

            if ( empty($default) )
                return $packages;

            foreach ( $packages as $idx => $package ) {

                if ( (!isset( $package['destination'] ) || $this->is_address_empty($package['destination']) ) && !$this->is_address_empty( $default ) ) {
                    $packages[ $idx ]['destination'] = $default;
                }

                if ( (!isset( $package['full_address'] ) || $this->is_address_empty($package['full_address']) ) && !$this->is_address_empty( $default ) ) {
                    $packages[ $idx ]['full_address'] = $default;
                }

            }

            return $packages;

        }

        public function is_address_empty( $address_array ) {

            $empty = false;

            $required = array('country', 'postcode');

            foreach ( $required as $field ) {
                if ( !isset( $address_array[ $field ] ) || empty( $address_array[ $field ] ) ) {
                    $empty = true;
                    break;
                }
            }

            return $empty;

        }

        public function get_default_shipping_address() {
            global $woocommerce;

            $user_id = get_current_user_id();
            $address = array();

            if ( empty( $address ) ) {
                if ( $user_id > 0 ) {
                    $address = array(
                        'first_name'    => get_user_meta( $user_id, 'shipping_first_name', true ),
                        'last_name'     => get_user_meta( $user_id, 'shipping_last_name', true ),
                        'company'       => '',
                        'address_1'     => $woocommerce->customer->get_shipping_address(),
                        'address_2'     => $woocommerce->customer->get_shipping_address_2(),
                        'city'          => $woocommerce->customer->get_shipping_city(),
                        'state'         => $woocommerce->customer->get_shipping_state(),
                        'postcode'      => $woocommerce->customer->get_shipping_postcode(),
                        'country'       => $woocommerce->customer->get_shipping_country()
                    );
                } else {
                    $address = array(
                        'first_name'    => '',
                        'last_name'     => '',
                        'company'       => '',
                        'address_1'     => $woocommerce->customer->get_shipping_address(),
                        'address_2'     => $woocommerce->customer->get_shipping_address_2(),
                        'city'          => $woocommerce->customer->get_shipping_city(),
                        'state'         => $woocommerce->customer->get_shipping_state(),
                        'postcode'      => $woocommerce->customer->get_shipping_postcode(),
                        'country'       => $woocommerce->customer->get_shipping_country()
                    );
                }
            }

            return $address;
        }

        public function generate_address_session( $packages ) {
            global $woocommerce;
            
            $fields     = $woocommerce->countries->get_address_fields( $woocommerce->countries->get_base_country(), 'shipping_' );
            $data       = array();
            $rel        = array();

            foreach ( $packages as $pkg_idx => $package ) {

                if ( !isset($package['full_address']) || empty($package['full_address']['postcode']) || empty($package['full_address']['country']) )
                    continue;

                $items = $package['contents'];

                foreach ( $items as $cart_key => $item ) {

                    $qty            = $item['quantity'];

                    $product_id     = $item['product_id'];
                    $sig            = $cart_key .'_'. $product_id .'_';
                    $address_id     = 0;

                    $i = 1;
                    for ( $x = 0; $x < $qty; $x++ ) {
                        $rel[ $address_id ][]  = $cart_key;


                        while ( isset($data['shipping_first_name_'. $sig . $i]) ) {
                            $i++;
                        }
                        $_sig = $sig . $i;

                        if ( $fields ) foreach ( $fields as $key => $field ) :
                            $address_key = str_replace( 'shipping_', '', $key );
                            $data[$key .'_'. $_sig] = $package['full_address'][ $address_key ];
                        endforeach;
                    }

                }

            }

            wcms_session_set( 'cart_item_addresses', $data );
            wcms_session_set( 'address_relationships', $rel );

        }

        /**
         * Check if the contents of the current cart are valid for multiple shipping
         *
         * To pass, there must be 1 or more items in the cart that passes the @see WC_Cart::needs_shipping() test.
         * If there is only 1 item in the cart, it must have a quantity of 2 or more. And child items
         * from Bundles and Composite Products are excluded from the count.
         *
         * This method will automatically return false if the only available shipping method is Local Pickup
         *
         * @return bool
         */
        public function cart_is_eligible_for_multi_shipping() {
            global $woocommerce;

            $sess_item_address  = wcms_session_get( 'cart_item_addresses' );
            $has_item_address   = (!wcms_session_isset( 'cart_item_addresses' ) || empty( $sess_item_address )) ? false : true;
            $item_allowed       = false;
            $contents           = wcms_get_real_cart_items();

            if ( count( $contents ) > 1) {
                $item_allowed = true;
            } else {
                $content = current( $contents );
                if ( $content && $content['quantity'] > 1) {
                    $item_allowed = true;
                }
            }

            // do not allow to set multiple addresses if only local pickup is available
            if ( function_exists('wc_add_notice') ) {
                $available_methods = $this->get_available_shipping_methods();
            } else {
                $available_methods = $woocommerce->shipping->get_available_shipping_methods();
            }

            if ( count($available_methods) == 1 && ( isset($available_methods['local_pickup']) || isset($available_methods['local_pickup_plus']) ) ) {
                $item_allowed = false;
            } elseif (isset($_POST['shipping_method']) && ( $_POST['shipping_method'] == 'local_pickup' || $_POST['shipping_method'] == 'local_pickup_plus' ) ) {
                $item_allowed = false;
            }

            // do not allow if any of the cart items is in the excludes list
            $settings           = get_option( 'woocommerce_multiple_shipping_settings', array() );
            $excl_products      = (isset($settings['excluded_products'])) ? $settings['excluded_products'] : array();
            $excl_categories    = (isset($settings['excluded_categories'])) ? $settings['excluded_categories'] : array();

            if ( $excl_products || $excl_categories ) {

                foreach ( $contents as $cart_item ) {
                    if ( in_array($cart_item['product_id'], $excl_products) ) {
                        $item_allowed = false;
                        break;
                    }

                    // item categories
                    $cat_ids = wp_get_object_terms( $cart_item['product_id'], 'product_cat', array('fields' => 'ids') );

                    foreach ( $cat_ids as $cat_id ) {
                        if ( in_array( $cat_id, $excl_categories ) ) {
                            $item_allowed = false;
                            break 2;
                        }
                    }

                }
            }

            return apply_filters( 'wc_ms_cart_is_eligible', $item_allowed );
        }


    }

    $GLOBALS['wcms'] = new WC_Ship_Multiple();

}