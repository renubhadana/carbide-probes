<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class for creating and handling the actual widget
 *
 * @class WC_Category_Diagram_Widget
 * @extends WC_Widget
 * @version 1.0.0
 * @category Class
 * @author Todd Miller <todd@rainydaymedia.net>
 */
class WC_Category_Diagram_Widget extends WC_Widget
{
    /**
     * Category ancestors
     * @var array
     */
    public $cat_ancestors;

    /**
     * Current Category
     * @var bool
     */
    public $current_cat;

    /**
     * constructor
     *
     * @see WC_Widget_Product_Categories
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        $this->widget_cssclass    = 'woocommerce widget_product_categories widget_category_diagram';
        $this->widget_description = __( 'A list or dropdown of product categories with a diagram image and legend.', 'woocommerce-category-diagram-widget' );
        $this->widget_id          = 'woocommerce_product_category_diagram';
        $this->widget_name        = __( 'WooCommerce Categories w/ Diagram', 'woocommerce-category-diagram-widget' );
        $this->settings           = array(
            'title'  => array(
                'type'  => 'text',
                'std'   => __( 'Product Categories', 'woocommerce' ),
                'label' => __( 'Title', 'woocommerce' )
            ),
            'orderby' => array(
                'type'  => 'select',
                'std'   => 'name',
                'label' => __( 'Order by', 'woocommerce' ),
                'options' => array(
                    'order' => __( 'Category Order', 'woocommerce' ),
                    'name'  => __( 'Name', 'woocommerce' )
                )
            ),
            'diagram_position' => array(
                'type'    => 'select',
                'std'     => 'below',
                'label'   => __( 'Diagram Position', 'woocommerce-category-diagram-widget' ),
                'options' => array(
                    'above' => __( 'Above Filter', 'woocommerce-category-diagram-widget' ),
                    'below' => __( 'Below Filter', 'woocommerce-category-diagram-widget' )
                )
            ),
            'dropdown' => array(
                'type'  => 'checkbox',
                'std'   => 0,
                'label' => __( 'Show as dropdown', 'woocommerce' )
            ),
            'count' => array(
                'type'  => 'checkbox',
                'std'   => 0,
                'label' => __( 'Show product counts', 'woocommerce' )
            ),
            'hierarchical' => array(
                'type'  => 'checkbox',
                'std'   => 1,
                'label' => __( 'Show hierarchy', 'woocommerce' )
            ),
            'show_children_only' => array(
                'type'  => 'checkbox',
                'std'   => 0,
                'label' => __( 'Only show children of the current category', 'woocommerce' )
            )
        );

        parent::__construct();
    }

    /**
     * widget function
     * actually displays the widget on the front end
     *
     * @see WC_Widget_Product_Categories
     *
     * @param array $args
     * @param array $instance
     */
    public function widget( $args, $instance ) {
        global $wp_query, $post;

        $c             = isset( $instance['count'] ) ? $instance['count'] : $this->settings['count']['std'];
        $h             = isset( $instance['hierarchical'] ) ? $instance['hierarchical'] : $this->settings['hierarchical']['std'];
        $s             = isset( $instance['show_children_only'] ) ? $instance['show_children_only'] : $this->settings['show_children_only']['std'];
        $d             = isset( $instance['dropdown'] ) ? $instance['dropdown'] : $this->settings['dropdown']['std'];
        $o             = isset( $instance['orderby'] ) ? $instance['orderby'] : $this->settings['orderby']['std'];
        $pos           = isset( $instance['diagram_position'] ) ? $instance['diagram_position'] : $this->settings['diagram_position']['std'];
        $dropdown_args = array( 'hide_empty' => false );
        $list_args     = array( 'show_count' => $c, 'hierarchical' => $h, 'taxonomy' => 'product_cat', 'hide_empty' => false );

        // Menu Order
        $list_args['menu_order'] = false;
        if ( $o == 'order' ) {
            $list_args['menu_order'] = 'asc';
        } else {
            $list_args['orderby']    = 'title';
        }

        // Setup Current Category
        $this->current_cat   = false;
        $this->cat_ancestors = array();

        if ( is_tax( 'product_cat' ) ) {

            $this->current_cat   = $wp_query->queried_object;
            $this->cat_ancestors = get_ancestors( $this->current_cat->term_id, 'product_cat' );

        } elseif ( is_singular( 'product' ) ) {

            $product_category = wc_get_product_terms( $post->ID, 'product_cat', array( 'orderby' => 'parent' ) );

            if ( $product_category ) {
                $this->current_cat   = end( $product_category );
                $this->cat_ancestors = get_ancestors( $this->current_cat->term_id, 'product_cat' );
            }

        }

        // Show Siblings and Children Only
        if ( $s && $this->current_cat ) {

            // Top level is needed
            $top_level = get_terms(
                'product_cat',
                array(
                    'fields'       => 'ids',
                    'parent'       => 0,
                    'hierarchical' => true,
                    'hide_empty'   => false
                )
            );

            // Direct children are wanted
            $direct_children = get_terms(
                'product_cat',
                array(
                    'fields'       => 'ids',
                    'parent'       => $this->current_cat->term_id,
                    'hierarchical' => true,
                    'hide_empty'   => false
                )
            );

            // Gather siblings of ancestors
            $siblings  = array();
            if ( $this->cat_ancestors ) {
                foreach ( $this->cat_ancestors as $ancestor ) {
                    $ancestor_siblings = get_terms(
                        'product_cat',
                        array(
                            'fields'       => 'ids',
                            'parent'       => $ancestor,
                            'hierarchical' => false,
                            'hide_empty'   => false
                        )
                    );
                    $siblings = array_merge( $siblings, $ancestor_siblings );
                }
            }

            if ( $h ) {
                $include = array_merge( $top_level, $this->cat_ancestors, $siblings, $direct_children, array( $this->current_cat->term_id ) );
            } else {
                $include = array_merge( $direct_children );
            }

            $dropdown_args['include'] = implode( ',', $include );
            $list_args['include']     = implode( ',', $include );

            if ( empty( $include ) ) {
                return;
            }

        } elseif ( $s ) {
            $dropdown_args['depth']        = 1;
            $dropdown_args['child_of']     = 0;
            $dropdown_args['hierarchical'] = 1;
            $list_args['depth']            = 1;
            $list_args['child_of']         = 0;
            $list_args['hierarchical']     = 1;
        }

        // output the diagram and legend if possible
        if ( $this->current_cat && $pos === 'above' ) {
            $diagram_text = get_woocommerce_term_meta( $this->current_cat->term_id, 'wc_category_diagram_text', true );
            $diagram_id   = absint( get_woocommerce_term_meta( $this->current_cat->term_id, 'wc_category_diagram_id', true ) );

            if ( $diagram_id ) {
                $image = wp_get_attachment_url( $diagram_id );
                echo '<img src="'.esc_url( $image ).'"/>';
                echo apply_filters( 'the_content', $diagram_text );
            }
        }

        $this->widget_start( $args, $instance );

        // Dropdown
        if ( $d ) {
            $dropdown_defaults = array(
                'show_counts'        => $c,
                'hierarchical'       => $h,
                'show_uncategorized' => 0,
                'orderby'            => $o,
                'selected'           => $this->current_cat ? $this->current_cat->slug : ''
            );
            $dropdown_args = wp_parse_args( $dropdown_args, $dropdown_defaults );

            // Stuck with this until a fix for http://core.trac.wordpress.org/ticket/13258
            wc_product_dropdown_categories( apply_filters( 'woocommerce_product_categories_widget_dropdown_args', $dropdown_args ) );

            wc_enqueue_js( "
                jQuery( '.dropdown_product_cat' ).change( function() {
                    if ( jQuery(this).val() != '' ) {
                        var this_page = location.href.toString();
                        var home_url  = '" . esc_js( home_url( '/' ) ) . "';
                        if ( this_page.indexOf( '?' ) > 0 ) {
                            this_page = home_url + '&product_cat=' + jQuery(this).val();
                        } else {
                            this_page = home_url + '?product_cat=' + jQuery(this).val();
                        }
                        location.href = this_page;
                    }
                });
            " );

        // List
        } else {

            include_once( WC()->plugin_path() . '/includes/walkers/class-product-cat-list-walker.php' );

            $list_args['walker']                     = new WC_Product_Cat_List_Walker;
            $list_args['title_li']                   = '';
            $list_args['pad_counts']                 = 1;
            $list_args['show_option_none']           = __('No product categories exist.', 'woocommerce' );
            $list_args['current_category']           = ( $this->current_cat ) ? $this->current_cat->term_id : '';
            $list_args['current_category_ancestors'] = $this->cat_ancestors;

            echo '<ul class="product-categories">';

            wp_list_categories( apply_filters( 'woocommerce_product_categories_widget_args', $list_args ) );

            echo '</ul>';
        }

        // output the diagram and legend if possible
        if ( $this->current_cat && $pos === 'below' ) {
            $diagram_text = get_woocommerce_term_meta( $this->current_cat->term_id, 'wc_category_diagram_text', true );
            $diagram_id   = absint( get_woocommerce_term_meta( $this->current_cat->term_id, 'wc_category_diagram_id', true ) );

            if ( $diagram_id ) {
                $image = wp_get_attachment_url( $diagram_id );
                echo '<img style="margin-top: 10px;" src="'.esc_url( $image ).'"/>';
                echo apply_filters( 'the_content', $diagram_text );
            }
        }

        $this->widget_end( $args );
    }
}
