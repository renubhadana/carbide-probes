<?php
/*
Plugin Name: WooCommerce Category Diagram Widget
Plugin URI: http://carbideprobes.com
Description: Adds a sidebar widget and category fields for displaying a diagram and legend with the product  category filter.
Version: 1.0.0
Author: Todd Miller <todd@rainydaymedia.net>
Author URI: http://rainydaymedia.net
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class for handling the category diagram fields on the product taxonomy.
 *
 * @class WC_Category_Diagram_Taxonomies
 * @version 1.0.0
 * @category Class
 * @author Todd Miller <todd@rainydaymedia.net>
 */
class WC_Category_Diagram_Taxonomies
{
    /**
     * constructor
     * initializes hooks
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        require_once( 'class-category-diagram-widget.php' );

        // adding and editing category fields
        add_action( 'product_cat_add_form_fields', array( $this, 'add_fields' ), 15 );
        add_action( 'product_cat_edit_form_fields', array( $this, 'edit_fields' ), 15, 1 );
        add_action( 'edit_term', array( $this, 'save_fields' ), 15, 3 );
        add_action( 'created_term', array( $this, 'save_fields' ), 10, 3 );

        // register widget
        add_action( 'widgets_init', array( $this, 'register_widget' ) );
    }

    /**
     * register_widget function
     * registers our lovely widget
     *
     * @access public
     * @return void
     */
    public function register_widget()
    {
        register_widget( 'WC_Category_Diagram_Widget' );
    }

    /**
     * add_fields function
     * adds fields to the add category form
     *
     * @see WC_Admin_Taxonomies
     *
     * @access public
     * @return void
     */
    public function add_fields() {
        ?>
        <div class="form-field">
            <label><?php _e( 'Diagram Image', 'woocommerce-category-diagram-widget' ); ?></label>
            <div id="wc_category_diagram" style="float: left; margin-right: 10px;"><img src="<?php echo esc_url( wc_placeholder_img_src() ); ?>" width="60px" height="60px" /></div>
            <div style="line-height: 60px;">
                <input type="hidden" id="wc_category_diagram_id" name="wc_category_diagram_id" />
                <button type="button" class="diagram_upload_image_button button"><?php _e( 'Upload/Add image', 'woocommerce' ); ?></button>
                <button type="button" class="diagram_remove_image_button button"><?php _e( 'Remove image', 'woocommerce' ); ?></button>
            </div>
            <script type="text/javascript">

                // Only show the "remove image" button when needed
                if ( ! jQuery( '#wc_category_diagram_id' ).val() ) {
                    jQuery( '.diagram_remove_image_button' ).hide();
                }

                // Uploading files
                var diagram_file_frame;

                jQuery( document ).on( 'click', '.diagram_upload_image_button', function( event ) {

                    event.preventDefault();

                    // If the media frame already exists, reopen it.
                    if ( diagram_file_frame ) {
                        diagram_file_frame.open();
                        return;
                    }

                    // Create the media frame.
                    diagram_file_frame = wp.media.frames.downloadable_file = wp.media({
                        title: '<?php _e( "Choose an image", "woocommerce" ); ?>',
                        button: {
                            text: '<?php _e( "Use image", "woocommerce" ); ?>'
                        },
                        multiple: false
                    });

                    // When an image is selected, run a callback.
                    diagram_file_frame.on( 'select', function() {
                        var attachment = diagram_file_frame.state().get( 'selection' ).first().toJSON();

                        jQuery( '#wc_category_diagram_id' ).val( attachment.id );
                        jQuery( '#wc_category_diagram img' ).attr( 'src', attachment.sizes.thumbnail.url );
                        jQuery( '.diagram_remove_image_button' ).show();
                    });

                    // Finally, open the modal.
                    diagram_file_frame.open();
                });

                jQuery( document ).on( 'click', '.diagram_remove_image_button', function() {
                    jQuery( '#wc_category_diagram img' ).attr( 'src', '<?php echo esc_js( wc_placeholder_img_src() ); ?>' );
                    jQuery( '#wc_category_diagram_id' ).val( '' );
                    jQuery( '.diagram_remove_image_button' ).hide();
                    return false;
                });

            </script>
            <div class="clear"></div>
        </div>
        <div class="form-field">
            <label for="wc_category_diagram_text"><?php _e( 'Diagram Legend', 'woocommerce-category-diagram-widget' ); ?></label>
            <textarea id="wc_category_diagram_text" name="wc_category_diagram_text" rows="5" cols="40"></textarea>
        </div>
        <?php
    }

    /**
     * edit_fields function
     * adds the fields to the edit category form
     *
     * @see WC_Admin_Taxonomies
     *
     * @access public
     * @param object $term The object of the term currently being edited
     * @return void
     */
    public function edit_fields( $term )
    {
        $diagram_text = get_woocommerce_term_meta( $term->term_id, 'wc_category_diagram_text', true );
        $diagram_id   = absint( get_woocommerce_term_meta( $term->term_id, 'wc_category_diagram_id', true ) );

        if ( $diagram_id ) {
            $image = wp_get_attachment_thumb_url( $diagram_id );
        } else {
            $image = wc_placeholder_img_src();
        }
    ?>

        <tr class="form-field">
            <th scope="row" valign="top"><label><?php _e( 'Diagram Image', 'woocommerce-category-diagram-widget' ); ?></label></th>
            <td>
                <div id="wc_category_diagram" style="float: left; margin-right: 10px;"><img src="<?php echo esc_url( $image ); ?>" width="60px" height="60px" /></div>
                <div style="line-height: 60px;">
                    <input type="hidden" id="wc_category_diagram_id" name="wc_category_diagram_id" value="<?php echo $diagram_id; ?>" />
                    <button type="button" class="diagram_upload_image_button button"><?php _e( 'Upload/Add image', 'woocommerce' ); ?></button>
                    <button type="button" class="diagram_remove_image_button button"><?php _e( 'Remove image', 'woocommerce' ); ?></button>
                </div>

                <script type="text/javascript">

                    // Only show the "remove image" button when needed
                    if ( '0' === jQuery( '#wc_category_diagram_id' ).val() ) {
                        jQuery( '.diagram_remove_image_button' ).hide();
                    }

                    // Uploading files
                    var diagram_file_frame;

                    jQuery( document ).on( 'click', '.diagram_upload_image_button', function( event ) {

                        event.preventDefault();

                        // If the media frame already exists, reopen it.
                        if ( diagram_file_frame ) {
                            diagram_file_frame.open();
                            return;
                        }

                        // Create the media frame.
                        diagram_file_frame = wp.media.frames.downloadable_file = wp.media({
                            title: '<?php _e( "Choose an image", "woocommerce" ); ?>',
                            button: {
                                text: '<?php _e( "Use image", "woocommerce" ); ?>'
                            },
                            multiple: false
                        });

                        // When an image is selected, run a callback.
                        diagram_file_frame.on( 'select', function() {
                            var attachment = diagram_file_frame.state().get( 'selection' ).first().toJSON();

                            jQuery( '#wc_category_diagram_id' ).val( attachment.id );
                            jQuery( '#wc_category_diagram img' ).attr( 'src', attachment.sizes.thumbnail.url );
                            jQuery( '.diagram_remove_image_button' ).show();
                        });

                        // Finally, open the modal.
                        diagram_file_frame.open();
                    });

                    jQuery( document ).on( 'click', '.diagram_remove_image_button', function() {
                        jQuery( '#wc_category_diagram img' ).attr( 'src', '<?php echo esc_js( wc_placeholder_img_src() ); ?>' );
                        jQuery( '#wc_category_diagram_id' ).val( '' );
                        jQuery( '.diagram_remove_image_button' ).hide();
                        return false;
                    });

                </script>
                <div class="clear"></div>
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row" valign="top"><label><?php _e( 'Diagram Legend', 'woocommerce-category-diagram-widget' ); ?></label></th>
            <td>
                <textarea id="wc_category_diagram_text" name="wc_category_diagram_text" rows="5" cols="50" class="large-text"><?php echo $diagram_text; ?></textarea>
            </td>
        </tr>

    <?php
    }

    /**
     * save_fields function
     * saves the form fields to the term meta
     *
     * @see WC_Admin_Taxonomies
     *
     * @access public
     * @param int $term_id Id of the term that's being updated
     * @param int $tt_id I don't know
     * @param string $taxonomy Slug of the taxonomy type being updated
     * @return void
     */
    public function save_fields( $term_id, $tt_id = '', $taxonomy = '' ) {
        if ( isset( $_POST['wc_category_diagram_id'] ) && 'product_cat' === $taxonomy ) {
            update_woocommerce_term_meta( $term_id, 'wc_category_diagram_id', esc_attr( $_POST['wc_category_diagram_id'] ) );
        }
        if ( isset( $_POST['wc_category_diagram_text'] ) && 'product_cat' === $taxonomy ) {
            update_woocommerce_term_meta( $term_id, 'wc_category_diagram_text', esc_attr( $_POST['wc_category_diagram_text'] ) );
        }
    }
}

// make sure we're only loading this plugin if WooCommerce is also activated
add_action( 'plugins_loaded', 'woocommerce_category_diagram_init', 99 );
function woocommerce_category_diagram_init()
{
    if ( ! class_exists( 'WooCommerce' ) )
        return;

    new WC_Category_Diagram_Taxonomies();
}
