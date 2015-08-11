<?php
if (!defined('ABSPATH')) exit('No direct script access allowed');

 /**
 * CodeNegar WooCommerce AJAX Product Filter reset button widget
 *
 * Adds widget to use in sidebar
 *
 * @package    	WooCommerce AJAX Product Filter
 * @author      Farhad Ahmadi <ahm.farhad@gmail.com>
 * @license     http://codecanyon.net/licenses
 * @link		http://codenegar.com/go/wcpf
 * @version    	2.8.0
 */

class woocommerce_product_filter_reset extends WP_Widget{
    public function __construct() {
        global $codenegar_wcpf;
        parent::__construct(
            'woocommerce_product_filter_reset',
            __('Product Filter Reset Button', $codenegar_wcpf->text_domain),
            array('description' => __('Reset all filters by a single click.', $codenegar_wcpf->text_domain))
        );
    }

    public function defaults() {
        $defaults = array(
            'title' => 'Reset Filters',
            'sub_title' => 'Reset',
            'print_before_widget' => 'true',
            'print_after_widget' => 'true',
            'custom_class' => '',
        );

        return $defaults;
    }

    public function form($instance) { // Backend widget form
        global $codenegar_wcpf;
        wp_enqueue_script('jquery');
        wp_enqueue_script('codenegar-admin-option-widget');
        wp_enqueue_style('codenegar-admin-option-widget-css');
        $defaults = $this->defaults();
        if(count($instance)>0){ // not first time
            $instance = codenegar_parse_args($instance, array('print_before_widget' => 'false', 'print_after_widget' => 'false')); // if it has been saved and checkboxes have no value means there were unchecked
        }
        $instance = codenegar_parse_args($instance, $defaults);
        extract($instance); // Extract array to multiple variables
        ?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>" ><?php _e('Title', $codenegar_wcpf->text_domain); ?>:</label>
			<input
				type="text"
				class="widefat"
				id = "<?php echo $this->get_field_id('title'); ?>"
				name = "<?php echo $this->get_field_name('title'); ?>"
				value = "<?php if(isset($title)) echo $title; ?>"
			/>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('sub_title'); ?>" ><?php _e('Sub Title', $codenegar_wcpf->text_domain); ?>:</label>
			<input
				type="text"
				class="widefat"
				id = "<?php echo $this->get_field_id('sub_title'); ?>"
				name = "<?php echo $this->get_field_name('sub_title'); ?>"
				value = "<?php if(isset($sub_title)) echo $sub_title; ?>"
			/>
		</p>

		<p>
			<input class="checkbox"
				<?php if(isset($print_before_widget) && $print_before_widget == 'true') { echo 'checked="checked"'; } ?>
				id = "<?php echo $this->get_field_id('print_before_widget'); ?>"
				name = "<?php echo $this->get_field_name('print_before_widget'); ?>"
				value = "true"
				type = "checkbox"
			/>
			<label for="<?php echo $this->get_field_id('print_before_widget'); ?>" ><?php _e('Print Widget Opener', $codenegar_wcpf->text_domain); ?></label>
		</p>
		<p>
			<input class="checkbox"
				<?php if(isset($print_after_widget) && $print_after_widget == 'true') { echo 'checked="checked"'; } ?>
				id = "<?php echo $this->get_field_id('print_after_widget'); ?>"
				name = "<?php echo $this->get_field_name('print_after_widget'); ?>"
				value = "true"
				type = "checkbox"
			/>
			<label for="<?php echo $this->get_field_id('print_after_widget'); ?>" ><?php _e('Print Widget Closer', $codenegar_wcpf->text_domain); ?></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('custom_class'); ?>" ><?php _e('Custom CSS Class', $codenegar_wcpf->text_domain); ?>:</label>
			<input
				type="text"
				class="widefat"
				id = "<?php echo $this->get_field_id('custom_class'); ?>"
				name = "<?php echo $this->get_field_name('custom_class'); ?>"
				value = "<?php if(isset($custom_class)) echo $custom_class; ?>"
			/>
		</p>
		<?php
    }

    public function widget($args, $instance) { // Fronend widget form
        global $codenegar_wcpf;

        if(!$codenegar_wcpf->helper->is_wcpf_area()){ // Not on product page - return
            return;
        }

        $defaults = $this->defaults();
        if(count($instance)>0){ // not first time
            $instance = codenegar_parse_args($instance, array('print_before_widget' => 'false', 'print_after_widget' => 'false')); // if it has been saved and checkboxes have no value means there were unchecked
        }
        $instance = codenegar_parse_args($instance, $defaults);

        extract($instance);
        extract($args);
        // $before_widget, $print_before_widget,... are defined by extracting arrays
        $before_widget = $codenegar_wcpf->helper->add_master_wrap_class($before_widget);
        if($print_before_widget == 'true'){
            echo $before_widget;
        }
        echo $before_title;
        echo $title;
        echo $after_title;
        $url = get_pagenum_link();
        $url = $codenegar_wcpf->helper->remove_filter_parameters($url);
        ?>

			<div class="codenegar_product_filter_wrap reset_button <?php echo $custom_class; ?>">
			<a href="#" rel="nofollow" data-url="<?php echo $url; ?>" class="codenegar_product_filter_reset_button button"><?php echo $sub_title; ?></a>
			</div>
		<?php // removed "product_type_simple" css class from above line
        if($print_after_widget == 'true'){
            echo $after_widget;
        }
    }
}

function woocommerce_product_filter_reset() {
    register_widget('woocommerce_product_filter_reset');
}

add_action('widgets_init', 'woocommerce_product_filter_reset');
?>
