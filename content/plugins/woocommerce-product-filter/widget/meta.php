<?php
if (!defined('ABSPATH')) exit('No direct script access allowed');

 /**
 * CodeNegar WooCommerce AJAX Product Filter by Meta widget
 *
 * Adds widget to use in sidebar
 *
 * @package    	WooCommerce AJAX Product Filter
 * @author      Farhad Ahmadi <ahm.farhad@gmail.com>
 * @license     http://codecanyon.net/licenses
 * @link		http://codenegar.com/go/wcpf
 * @version    	2.8.0
 */

class Woocommerce_product_filter_meta extends WP_Widget{
    public function __construct() {
        global $codenegar_wcpf;
        parent::__construct(
            'woocommerce_product_filter_meta',
            __('Product Filter by Meta', $codenegar_wcpf->text_domain),
            array('description' => __('Filter products by WooCommerce products meta.', $codenegar_wcpf->text_domain))
        );
    }

    public function defaults() {
        $defaults = array(
            'widget' => 'meta',
            'type'    => 'slider',
            'parent' => 0,
            'title' => '',
            'sub_title' => '',
            'range_template' => '%s $ - %e $',
            'min' => 0,
            'max' => 100,
            'step' => 1,
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
			<label for="<?php echo $this->get_field_id('type'); ?>" ><?php _e('Display Type', $codenegar_wcpf->text_domain); ?>:</label>
			<select
			name="<?php echo $this->get_field_name('type'); ?>"
			class="widget_type widefat"
			id = "<?php echo $this->get_field_id('type'); ?>"
			>
				<option <?php if(isset($type) && $type == 'slider') { echo 'selected="selected"'; } ?> value="slider"><?php _e('Slider', $codenegar_wcpf->text_domain); ?></option>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('parent'); ?>" ><?php _e('Meta', $codenegar_wcpf->text_domain); ?>:</label>
			<select
			name="<?php echo $this->get_field_name('parent'); ?>"
			class="widefat"
			id = "<?php echo $this->get_field_id('parent'); ?>"
			>
			<?php
            $metas = (array) ($codenegar_wcpf->helper->get_meta_list());
            $count = count($metas);
            if($count>0){
                foreach($metas as $key => $value){
                    ?>
					<option <?php if(isset($parent) && $parent == $key) { echo 'selected="selected"'; } ?>  value="<?php echo $key; ?>"><?php echo $value; ?></option>
					<?php
                }
            }
        ?>
			</select>
		</p>

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
		<p class="slider_option" style="<?php if(isset($type) && $type != 'slider') { echo 'display: none;'; } ?>">
			<label for="<?php echo $this->get_field_id('range_template'); ?>" ><?php _e('Range Template', $codenegar_wcpf->text_domain); ?>:</label>
			<input
				type="text"
				class="widefat"
				id = "<?php echo $this->get_field_id('range_template'); ?>"
				name = "<?php echo $this->get_field_name('range_template'); ?>"
				value = "<?php if(isset($range_template)) echo $range_template; ?>"
			/>
		</p>
		<p class="slider_option" style="<?php if(isset($type) && $type != 'slider') { echo 'display: none;'; } ?>">
			<label for="<?php echo $this->get_field_id('min'); ?>" ><?php _e('Minimum Value', $codenegar_wcpf->text_domain); ?>:</label>
			<input class="small-text"
				id = "<?php echo $this->get_field_id('min'); ?>"
				name = "<?php echo $this->get_field_name('min'); ?>"
				value = "<?php if(isset($min)) echo $min; ?>"
			/>
		</p>
		<p class="slider_option" style="<?php if(isset($type) && $type != 'slider') { echo 'display: none;'; } ?>">
			<label for="<?php echo $this->get_field_id('max'); ?>" ><?php _e('Maximum Value', $codenegar_wcpf->text_domain); ?>:</label>
			<input class="small-text"
				id = "<?php echo $this->get_field_id('max'); ?>"
				name = "<?php echo $this->get_field_name('max'); ?>"
				value = "<?php if(isset($max)) echo $max; ?>"
			/>
		</p>

		<p class="slider_option" style="<?php if(isset($type) && $type != 'slider') { echo 'display: none;'; } ?>">
			<label for="<?php echo $this->get_field_id('step'); ?>" ><?php _e('Step', $codenegar_wcpf->text_domain); ?>:</label>
			<input class="small-text"
				id = "<?php echo $this->get_field_id('step'); ?>"
				name = "<?php echo $this->get_field_name('step'); ?>"
				value = "<?php if(isset($step)) echo $step; ?>"
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

    public function widget($args, $instance) { // Frontend widget form
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
            if($type == 'list' || $type == 'dropdown'){
                $before_widget = $codenegar_wcpf->helper->add_layered_class($before_widget);
            }
            echo $before_widget;
        }
        echo $before_title;
        echo $title;
        echo $after_title;
        ?>

			<?php
                codenegar_wcpf_generate_widget($instance);
            ?>

		<?php
        if($print_after_widget == 'true'){
            echo $after_widget;
        }
    }
}

function woocommerce_product_filter_meta() {
    register_widget('woocommerce_product_filter_meta');
}

add_action('widgets_init', 'woocommerce_product_filter_meta');
?>
