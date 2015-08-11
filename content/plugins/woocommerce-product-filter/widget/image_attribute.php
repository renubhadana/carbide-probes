<?php
if (!defined('ABSPATH')) exit('No direct script access allowed');

 /**
 * CodeNegar WooCommerce AJAX Product Filter by Attribute widget
 *
 * Adds widget to use in sidebar
 *
 * @package    	WooCommerce AJAX Product Filter
 * @author      Farhad Ahmadi <ahm.farhad@gmail.com>
 * @license     http://codecanyon.net/licenses
 * @link		http://codenegar.com/go/wcpf
 * @version    	2.8.0
 */

class Woocommerce_product_filter_image_attribute extends WP_Widget{
    public function __construct() {
        global $codenegar_wcpf;
        parent::__construct(
            'woocommerce_product_filter_image_attribute',
            __('Product Filter by Image Attribute', $codenegar_wcpf->text_domain),
            array('description' => __('Filter products by their attributes using images as title.', $codenegar_wcpf->text_domain))
        );
    }

    public function defaults() {
        $defaults = array(
            'widget' => 'image_attr',
            'parent' => 0,
            'type' => 'list', // we don't need this but for remains compatibility with the rest of code
            'title' => '',
            'sub_title' => '',
            'operator' => 'IN',
            'print_before_widget' => 'true',
            'print_after_widget' => 'true',
            'use_theme_toggle_effect' => 'true',
            'custom_class' => '',
            'width' => '24',
            'height' => '24',
            'element_type' => 'li', // maybe for future versions we add more html elements
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
            $instance = codenegar_parse_args($instance, array('print_before_widget' => 'false', 'print_after_widget' => 'false', 'use_theme_toggle_effect' => 'false')); // if it has been saved and checkboxes have no value means there were unchecked
        }
        $instance = codenegar_parse_args($instance, $defaults);
        extract($instance); // Extract array to multiple variables
        ?>
		<p>
			<label for="<?php echo $this->get_field_id('parent'); ?>" ><?php _e('Attribute', $codenegar_wcpf->text_domain); ?>:</label>
			<select
			name="<?php echo $this->get_field_name('parent'); ?>"
			class="widefat wcsl_attr_image"
			id = "<?php echo $this->get_field_id('parent'); ?>"
			>
			<?php
            $attributes = (array) ($codenegar_wcpf->helper->get_attributes());
            $count = count($attributes);
            if($count>0){
                for($i = 0; $i<$count; $i++){
                    $label = $attributes[$i]->attribute_label;
                    if(!$label){
                        $label = $attributes[$i]->attribute_name;
                    }
                    ?>
					<option <?php if(isset($parent) && $parent == $attributes[$i]->attribute_name) { echo 'selected="selected"'; } ?>  value="<?php echo $attributes[$i]->attribute_name; ?>"><?php echo $label; ?></option>
					<?php
                }
            }
        ?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('operator'); ?>" ><?php _e('Query Type', $codenegar_wcpf->text_domain); ?>:</label>
			<select
			name="<?php echo $this->get_field_name('operator'); ?>"
			class="widget_type widefat"
			id = "<?php echo $this->get_field_id('operator'); ?>"
			>
				<option <?php if(isset($operator) && $operator == 'OR') { echo 'selected="selected"'; } ?>  value="OR"><?php _e('OR', $codenegar_wcpf->text_domain); ?></option>
				<option <?php if(isset($operator) && $operator == 'AND') { echo 'selected="selected"'; } ?>  value="AND"><?php _e('AND', $codenegar_wcpf->text_domain); ?></option>
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

		<p>
			<label for="<?php echo $this->get_field_id('width'); ?>" ><?php _e('Width', $codenegar_wcpf->text_domain); ?>:</label>
			<input type="text" class="small-text"
				id = "<?php echo $this->get_field_id('width'); ?>"
				name = "<?php echo $this->get_field_name('width'); ?>"
				value = "<?php if(isset($width)) echo $width; ?>"
			/>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('height'); ?>" ><?php _e('Height', $codenegar_wcpf->text_domain); ?>:</label>
			<input type="text" class="small-text"
				id = "<?php echo $this->get_field_id('height'); ?>"
				name = "<?php echo $this->get_field_name('height'); ?>"
				value = "<?php if(isset($height)) echo $height; ?>"
			/>
		</p>

		<?php

            if($count>0){
                $current_attrib = '';
                if(!isset($parent) || !$parent){ // default options, user hasn't selected any attribute
                    $current_attrib = $attributes[0]->attribute_name;// first attrib
                }else{
                    $current_attrib = $parent;
                }
                $current_attrib_values = $codenegar_wcpf->helper->get_attribute_values($current_attrib);
                wp_enqueue_style('thickbox');
                wp_enqueue_script('jquery');
                wp_enqueue_script('thickbox');
                wp_enqueue_script('postbox');
                wp_enqueue_script('underscore');
                foreach($current_attrib_values as $attrib){
                    $att_name = (isset($attrib->name)) ? $attrib->name : "";
                    if(!$att_name){
                        $att_name = $attrib->slug;
                        $att_name = str_replace('-', '', $att_name);
                        $att_name = ucfirst($att_name);
                    }
                    $term_id = $attrib->term_id;
                    echo '<label for="image_attribute_' . $attrib->name . '" >' . $att_name . ':</label>';
                    $base_name = 'id_' . $term_id . '_image_attribute';
                    $field_name = $this->get_field_name($base_name);
                    $field_id = $this->get_field_id($base_name);
                    $field_value = (isset($$base_name)) ? $$base_name : ''; // $$base_name is dynamic variable name: $($base_name)
                    $codenegar_wcpf->html->image_upload_field($field_value, $field_name, 'widefat', $field_id);
                    echo '<div>&nbsp;</div>';
                }
            }
        ?>
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

			<input class="checkbox"
				<?php if(isset($use_theme_toggle_effect) && $use_theme_toggle_effect == 'true') { echo 'checked="checked"'; } ?>
				id = "<?php echo $this->get_field_id('use_theme_toggle_effect'); ?>"
				name = "<?php echo $this->get_field_name('use_theme_toggle_effect'); ?>"
				value = "true"
				type = "checkbox"
			/>
			<label for="<?php echo $this->get_field_id('use_theme_toggle_effect'); ?>" ><?php _e('Use Theme Toggle Effect', $codenegar_wcpf->text_domain); ?></label>
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
            $instance = codenegar_parse_args($instance, array('print_before_widget' => 'false', 'print_after_widget' => 'false', 'use_theme_toggle_effect' => 'false')); // if it has been saved and checkboxes have no value means there were unchecked
        }
        $instance = codenegar_parse_args($instance, $defaults);

        extract($instance);
        extract($args);
        // $before_widget, $print_before_widget,... are defined by extracting arrays
        $before_widget = $codenegar_wcpf->helper->add_master_wrap_class($before_widget);
        if($print_before_widget == 'true'){
            if(($type == 'list' || $type == 'dropdown') && $use_theme_toggle_effect == 'true'){
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

function woocommerce_product_filter_image_attribute() {
    register_widget('woocommerce_product_filter_image_attribute');
}

add_action('widgets_init', 'woocommerce_product_filter_image_attribute');
?>
