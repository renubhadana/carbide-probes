<?php
/**
 * Quote Widget Class
 */
class quote_widget extends WP_Widget {


    /** constructor -- name this the same as the class above */
    public function __construct() {
        parent::__construct(false, $name = 'Quote Widget');
    }

    /** @see WP_Widget::widget -- do not rename this */
    function widget($args, $instance) {
        extract( $args );
        $quote 	= $instance['quote'];
        $citation = $instance['citation'];

        ?>
        <?php echo $before_widget; ?>
            <div class="quote-widget-content">
                <blockquote>
                    <p>"<?php echo $quote; ?>"</p>
                    <br>
                    <cite><?php echo $citation; ?></cite>
                </blockquote>
            </div>
        <?php echo $after_widget; ?>
    <?php
    }

    /** @see WP_Widget::update -- do not rename this */
    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['quote'] = strip_tags($new_instance['quote']);
        $instance['citation'] = strip_tags($new_instance['citation']);
        return $instance;
    }

    /** @see WP_Widget::form -- do not rename this */
    function form($instance) {

        $quote 		= esc_attr($instance['quote']);
        $citation	= esc_attr($instance['citation']);
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('quote'); ?>"><?php _e('Quote:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('quote'); ?>" name="<?php echo $this->get_field_name('quote'); ?>" type="text" value="<?php echo $quote; ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('citation'); ?>"><?php _e('Citation:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('citation'); ?>" name="<?php echo $this->get_field_name('citation'); ?>" type="text" value="<?php echo $citation; ?>" />
        </p>
    <?php
    }


} // end class example_widget
add_action('widgets_init', create_function('', 'return register_widget("quote_widget");'));
?>
