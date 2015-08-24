<?php
/**
 * Catalog Download Widget Class
 */
class catalog_download_widget extends WP_Widget {


    /** constructor -- name this the same as the class above */
    public function __construct() {
        parent::__construct(false, $name = 'Catalog Download Widget');
        add_action('admin_enqueue_scripts', array($this, 'upload_scripts'));
    }

    /**
     * Upload the Javascripts for the media uploader
     */
    public function upload_scripts()
    {
        wp_enqueue_script('media-upload');
        wp_enqueue_script('thickbox');
        wp_enqueue_script('upload_media_widget', get_template_directory_uri() . '/admin/scripts/upload-media.js', array('jquery'));
        wp_enqueue_style('thickbox');
    }

    /** @see WP_Widget::widget -- do not rename this */
    function widget($args, $instance) {
        extract( $args );
        $title 		= apply_filters('widget_title', $instance['title']);
        $message 	= $instance['message'];
        $download_text = $instance['download_text'];
        $download_dest = $instance['download_dest'];
        $image = $instance['image'];

        ?>
        <?php echo $before_widget; ?>
        <?php if ( $title )
            echo $before_title . $title . $after_title; ?>
        <p><?php echo $message; ?></p>
        <img src="<?php echo $image; ?>" alt="Catalog" class="catalog-img" />
        <a href="<?php echo $download_dest; ?>" class="btn orange-btn"><?php echo $download_text; ?></a>
        <?php echo $after_widget; ?>
    <?php
    }

    /** @see WP_Widget::update -- do not rename this */
    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['message'] = strip_tags($new_instance['message']);
        $instance['download_text'] = strip_tags($new_instance['download_text']);
        $instance['download_dest'] = strip_tags($new_instance['download_dest']);
        $instance['image'] = strip_tags($new_instance['image']);
        return $instance;
    }

    /** @see WP_Widget::form -- do not rename this */
    function form($instance) {

        $title 		= esc_attr($instance['title']);
        $message	= esc_attr($instance['message']);
        $download_text = esc_attr($instance['download_text']);
        $download_dest = esc_attr($instance['download_dest']);
        $image = '';
        if(isset($instance['image']))
        {
            $image = $instance['image'];
        }
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('message'); ?>"><?php _e('Message'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('message'); ?>" name="<?php echo $this->get_field_name('message'); ?>" type="text" value="<?php echo $message; ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('download_text'); ?>"><?php _e('Download Text:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('download_text'); ?>" name="<?php echo $this->get_field_name('download_text'); ?>" type="text" value="<?php echo $download_text; ?>" />
        </p>
        <p>
           <label for="<?php echo $this->get_field_id('download_dest'); ?>"><?php _e('Download Link:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('download_dest'); ?>" name="<?php echo $this->get_field_name('download_dest'); ?>" type="text" value="<?php echo $download_dest; ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_name( 'image' ); ?>"><?php _e( 'Image:' ); ?></label>
            <input name="<?php echo $this->get_field_name( 'image' ); ?>" id="<?php echo $this->get_field_id( 'image' ); ?>" class="widefat" type="text" size="36"  value="<?php echo esc_url( $image ); ?>" />
            <input class="upload_image_button button button-primary" type="button" value="Upload Image" />
        </p>
    <?php
    }


} // end class example_widget
add_action('widgets_init', create_function('', 'return register_widget("catalog_download_widget");'));
?>
