<?php
/**
 * functions.php
 *
 * All functionality specific to the custom theme lives here.
 * Note that if you're building a theme based on rdmgumby, you'll want to find and replace
 * the rdmgumby package name with your own package name.
 *
 * @package rdmgumby
 */

/**
 * Library functions that act independently of the theme templates.
 */
require_once get_template_directory() . '/inc/lib.php';
require_once get_template_directory() . '/inc/helpers.php';
require_once get_template_directory() . '/inc/gumby-nav-walker.php';

/**
 * Include some useful widgets
 */
require_once get_template_directory() . '/inc/widgets/catalog-download.php';
require_once get_template_directory() . '/inc/widgets/quote.php';

/**
 * Sets up the theme and registers support for WordPress features
 *
 * Note this is hooked into after_setup_theme, which runs before the init hook.
 * The init hook is too late for some features
 */
add_action( 'after_setup_theme', 'rdm_gumby_setup' );
function rdm_gumby_setup() {
	// Make the theme available for translations. Complete and install translations into ./languages/
	load_theme_textdomain( 'rdmgumby', get_template_directory() . '/languages' );

	// Enable support for Post Thumbnails on posts and pages.
	add_theme_support( 'post-thumbnails' );

	// Enable support for Post Formats.
	//add_theme_support( 'post-formats', array( 'aside', 'image', 'video', 'quote', 'link' ) );

	// Enable support for HTML5 markup.
	add_theme_support( 'html5', array( 'comment-list', 'search-form', 'comment-form', 'gallery', 'caption' ) );

	// Enable default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	// Register Nav Menus
	if ( function_exists( 'rdmgumby_register_nav_menus' ) ) {
		rdmgumby_register_nav_menus();
	}

	// Register sidebars
	if ( function_exists( 'rdmgumby_widgets_init' ) ) {
		rdmgumby_widgets_init();
	}

	// Set up the custom post types, if there are any
	// we build out our CPT using a custom plugin with the below function
	if ( function_exists( 'theme_custom_post_types' ) ) {
		theme_custom_post_types();
	}
}

/**
 * Registers Nav Menus for the theme. Add array entries as needed.
 * If not registering any menus, you can safely delete this function
 *
 * For new menus, follow the format
 * 'menu-slug' => __( 'menu-name', 'textdomain' ),
 */
function rdmgumby_register_nav_menus() {
	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'rdmgumby' ),
		'upper_header' => __( 'Upper Header', 'rdmgumby' ),
        'upper_header_mobile' => __( 'Upper Header Mobile', 'rdmgumby' ),
        'footer_menu' => __( 'Footer Menu', 'rdmgumby' )
	) );
}

/**
 * Register any Sidebars for the theme
 * If not registering any sidebars, you can safely delete this function
 *
 * For new sidebars, copy and paste the register sidebar function
 */
function rdmgumby_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'Sidebar', 'rdmgumby' ),
		'id'            => 'sidebar-1',
		'description'   => '',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h1 class="widget-title">',
		'after_title'   => '</h1>'
	) );

    register_sidebar( array(
        'name'          => __( 'Home Page Widgets', 'rdmgumby' ),
        'id'            => 'home-page-widgets',
        'description'   => 'Widgets for the Home Page',
        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
        'after_widget'  => '</aside>',
        'before_title'  => '<h1 class="widget-title">',
        'after_title'   => '</h1>'
    ) );

	register_sidebar( array(
		'name'          => __( 'Sidebar Left Widgets', 'rdmgumby' ),
		'id'            => 'sidebar-left-widgets',
		'description'   => 'Widgets for the Sidebar Left Template',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h1 class="widget-title">',
		'after_title'   => '</h1>'
	) );

	register_sidebar( array(
		'name'          => __( 'Sidebar Right Widgets', 'rdmgumby' ),
		'id'            => 'sidebar-right-widgets',
		'description'   => 'Widgets for the Sidebar Right Template',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h1 class="widget-title">',
		'after_title'   => '</h1>'
	) );
    register_sidebar( array(
        'name'          => __( 'Products Page Widgets', 'rdmgumby' ),
        'id'            => 'products-page-widgets',
        'description'   => 'Widgets for the Products Category Page',
        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
        'after_widget'  => '</aside>',
        'before_title'  => '<h1 class="widget-title">',
        'after_title'   => '</h1>'
    ) );
}

/**
 * Include Advanced Custom Fields as a required plugin
 */

/**
 * This includes the free version (4.3.9) of ACF and is included with this theme
 * Comment this out if you are using ACF Pro
 */
//include_once( get_template_directory() . '/inc/advanced-custom-fields/acf.php' );

/**
 * This includes ACF Pro, but you must install into ./inc/ yourself
 * If using ACF Pro, simply uncomment all of the following code
 */
/*
add_filter( 'acf/settings/path', 'acfSettingsPath' );
function acfSettingsPath( $path )
{
		$path = get_template_directory() . '/inc/advanced-custom-fields-pro/';
		return $path;
}

add_filter( 'acf/settings/dir', 'acfSettingsDir' );
function acfSettingsDir( $dir )
{
		$dir = get_template_directory_uri() . '/inc/advanced-custom-fields-pro/';
		return $dir;
}

include_once( get_template_directory() . '/inc/advanced-custom-fields-pro/acf.php' );
*/

/**
 * Include the web-admin-role plugin. This creates a Web Admin user role when the theme
 * is activated, and removes it when deactivated. We use the Web Admin role to
 * slightly limit what the client admins can do in the backend. Usually this will
 * prevent them from updating plugins or core code, inserting crazy html on the site,
 * and willy-nilly activating, deactivating, or deleting things like themes.
 *
 * Comment this line out to disable the feature.
 */
include_once( get_template_directory() . '/inc/web-admin-role/web-admin-role.php' );

// Gravity Forms
add_filter( 'gform_enable_field_label_visibility_settings', '__return_true' );

add_filter( 'gform_submit_button', 'form_submit_button', 10, 2 );
function form_submit_button( $button, $form ) {
    return "<button class='btn orange-btn' id='gform_submit_button_{$form['id']}'><span>Submit</span></button>";
}

/**
 * RICG Responsive Images plugin v2.1.1
 * It seems like this will change in the future to become part of the WordPress core,
 * at which point this will be redundant.
 * You can take this out by commenting out the include.
 */
include_once( get_template_directory() . '/inc/ricg-responsive-images/wp-tevko-responsive-images.php' );

/**
 * Enqueue scripts and styles
 *
 * Note that we enqueue minified versions of all of these files. If you are not
 * using minified files, you may want to modify the enqueues here. Or more likely,
 * you'll want to start using minified files.
 *
 * Note that you can uncomment the vendor-style and vendor-script enqueues if you
 * need them for your theme. They are used if you have bower components installed
 * that will create css or js files via gulp.
 */
add_action( 'wp_enqueue_scripts', 'theme_enqueue_scripts', 99 );
function theme_enqueue_scripts() {
	wp_enqueue_style( 'theme-style', get_template_directory_uri() . '/style.min.css' );
	//wp_enqueue_style( 'vendor-style', get_template_directory_uri() . '/vendor.min.css' );

	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'modernizr-script', get_template_directory_uri() . '/assets/js/dist/modernizr-2.6.2.min.js', array(), '2.6.2', false );
	wp_enqueue_script( 'gumby-script', get_template_directory_uri() . '/assets/js/dist/gumby.min.js', array(), '2.6.4', true );
	//wp_enqueue_script( 'vendor-script', get_template_directory_uri() . '/assets/js/dist/vendor.min.js', array(), '', true );
	//wp_enqueue_script( 'theme-script', get_template_directory_uri() . '/assets/js/dist/all.min.js', array(), '', true );
}

/**
 * carbide_keep_dash_in_search_before
 * carbide_keep_dash_in_search_after
 *
 * keeps the dash in the search tokens for relevanssi
 * normal behavior for relevanssi is to strip out all punctuation, but for
 * carbide, the dash is relevant to nailing down the best possible search results.
 * we split this into two filters, the first filter replaces dashes with
 * something that will not be stripped out by relevanssi. the second filter
 * replaces that original replacement with the dash, thus keeping the dash
 * in the search term.
 *
 * @param string $a The search string
 * @param string The updated search string
 */
add_filter('relevanssi_remove_punctuation', 'carbide_keep_dash_in_search_before', 9);
add_filter('relevanssi_remove_punctuation', 'carbide_keep_dash_in_search_after', 11);
function carbide_keep_dash_in_search_before( $a )
{
    $a = str_replace( '-', 'HYPHENDASH', $a );
    return $a;
}

function carbide_keep_dash_in_search_after( $a )
{
    $a = str_replace( 'HYPHENDASH', '-', $a );
    return $a;
}

/**
 * Declare Woocommerce support and customize content wrapper markup
 */
add_action( 'after_setup_theme', 'woocommerce_support' );
function woocommerce_support() {
	add_theme_support( 'woocommerce' );
}

remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );

function my_theme_wrapper_start() {
	echo '<section id="main">';
}

function my_theme_wrapper_end() {
	echo '</section>';
}

add_action( 'woocommerce_before_main_content', 'my_theme_wrapper_start', 10 );
add_action( 'woocommerce_after_main_content', 'my_theme_wrapper_end', 10 );

add_filter( 'woocommerce_product_tabs', 'wcs_woo_remove_reviews_tab', 98 );
function wcs_woo_remove_reviews_tab($tabs) {
    unset($tabs['reviews']);
    return $tabs;
}

add_filter( 'wc_product_sku_enabled', '__return_false' );
add_filter( 'wc_product_enable_dimensions_display', '__return_false' );

/*
 * wc_remove_related_products
 *
 * Clear the query arguments for related products so none show.
 * Add this code to your theme functions.php file.
 */
add_filter( 'woocommerce_related_products_args', 'carbide_remove_related_products', 10 );
function carbide_remove_related_products( $args ) {
    return array();
}

/**
 * carbide_add_purchase_order_field
 * adds the purchase order number form field to the checkout page
 *
 * @param object $checkout The checkout object for the current order
 */
add_action( 'woocommerce_before_order_notes', 'carbide_add_purchase_order_field', 99 );
function carbide_add_purchase_order_field( $checkout )
{
    woocommerce_form_field( 'purchase_order_number', array(
            'type' => 'text',
            'class' => array( 'form-row-wide' ),
            'label' => __( 'Add a Purchase Order Number' )
        ), $checkout->get_value( 'purchase_order_number' ));
}

/**
 * carbide_save_purchase_order_field
 * saves the purchase order number to the post meta, if the field is not empty
 *
 * @param int $order_id The ID of the current order
 */
add_action( 'woocommerce_checkout_update_order_meta', 'carbide_save_purchase_order_field', 99 );
function carbide_save_purchase_order_field( $order_id )
{
    if ( ! empty( $_POST['purchase_order_number'] ) )
        update_post_meta( $order_id, '_purchase_order_number', sanitize_text_field( $_POST['purchase_order_number'] ) );
}

/**
 * carbide_add_purchase_order_to_item_totals
 * adds the PO number to the order totals array if it has one
 *
 * @param array $fields The current order total fields
 * @param object $order The current order object
 * @return array The updated order totals array
 */
add_filter( 'woocommerce_get_order_item_totals', 'carbide_add_purchase_order_to_item_totals', 99, 2 );
function carbide_add_purchase_order_to_item_totals( $fields, $order )
{
    $po_number = get_post_meta( $order->id, '_purchase_order_number', true );

    if ( ! empty( $po_number ) ) {
        $po_field = array(
                'label' => 'PO Number',
                'value' => $po_number
            );
        array_unshift( $fields, $po_field );
    }

    return $fields;
}

/**
 * carbide_add_purchase_order_to_admin
 * adds the purchase order field to the admin screen if there is one
 *
 * @param object $order The current order object
 */
add_action( 'woocommerce_admin_order_data_after_shipping_address', 'carbide_add_purchase_order_to_admin', 99 );
function carbide_add_purchase_order_to_admin( $order )
{
    $po_number = get_post_meta( $order->id, '_purchase_order_number', true );

    if ( ! empty( $po_number ) ) {
        echo '<p><strong>'.__( 'Purchase Order' ).':</strong> ' . $po_number . '</p>';
    }
}

/**
 * Features you can enable or disable as needed.
 */

// Implement the Custom Header feature.
//require get_template_directory() . '/inc/custom-header.php';

// Customizer additions.
//require get_template_directory() . '/inc/customizer.php';

// Load Jetpack compatibility file.
//require get_template_directory() . '/inc/jetpack.php';

// Add support for automatic creation of alt tags for images in the content
add_filter( 'the_content', 'rdmgumby_add_alt_tags', 9999 );

// Add support for including <p> tags in the excerpts
remove_filter( 'get_the_excerpt', 'wp_trim_excerpt' );
add_filter( 'get_the_excerpt', 'rdmgumby_trim_excerpt' );

// Sets up the theme color
// this is used as the ms tile background color and the chrome toolbar color
global $favicon_theme_color;
$favicon_theme_color = '#424240';
// Adds generated favicons to theme from end and backend
// http://realfavicongenerator.net/
add_action( 'wp_head', 'rdmgumby_output_favicons' );
add_action( 'admin_head', 'rdmgumby_output_favicons' );
add_action( 'login_head', 'rdmgumby_output_favicons' );

// Adds a short title for the blog roll
function short_title($before = '', $after = '', $echo = true, $length = false) { $title = get_the_title();

	if ( $length && is_numeric($length) ) {
		$title = substr( $title, 0, $length );
	}

	if ( strlen($title)> 0 ) {
		$title = apply_filters('short_title', $before . $title . $after, $before, $after);
		if ( $echo )
			echo $title;
		else
			return $title;
	}
}
