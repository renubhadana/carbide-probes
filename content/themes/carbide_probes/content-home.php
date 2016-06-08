<?php
/**
 * The template used for displaying page content
 */
?>
<div class="row mobile-no-padding">
    <?php $wcatTerms = get_terms('product_cat', array('hide_empty' => 0, 'orderby' => 'ASC',  'parent' =>0)); //, 'exclude' => '17,77'
    foreach($wcatTerms as $wcatTerm) :
        $count = 0;
        $wthumbnail_id = get_woocommerce_term_meta( $wcatTerm->term_id, 'thumbnail_id', true );
        $wimage = wp_get_attachment_url( $wthumbnail_id );
        ?>
        <div class="category-container">
            <a href="<?php echo get_term_link( $wcatTerm->slug, $wcatTerm->taxonomy ); ?>">
                <div class="category-content">
                    <?php if($wimage!=""):?>
                        <div class="category-image-container">
                            <img src="<?php echo $wimage?>">
                        </div>
                    <?php endif;?>
                    <a href="<?php echo get_term_link( $wcatTerm->slug, $wcatTerm->taxonomy ); ?>"><?php echo $wcatTerm->name; ?> <span class="right-caret"></span></a>
                    <div style="clear: both;"></div>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
    <div class="custom-quote-widget-container hidden-xs">
        <div class="custom-quote-widget-content">
            <h2><?php echo ot_get_option('quote_block_title'); ?></h2>
            <p><?php echo ot_get_option('quote_block_text'); ?></p>
            <a href="<?php echo ot_get_option('quote_block_button_link'); ?>" class="btn transparent-btn white-btn"><?php echo ot_get_option('quote_block_button_text'); ?></a>
        </div>
    </div>
    <div class="custom-quote-widget-container visible-xs">
        <div class="custom-quote-widget-content">
            <h2><?php echo ot_get_option('quote_block_title'); ?></h2>
            <p><?php echo ot_get_option('quote_block_text'); ?></p>
            <a href="<?php echo ot_get_option('quote_block_button_link'); ?>" class="btn blue-btn"><?php echo ot_get_option('quote_block_button_text'); ?></a>
        </div>
    </div>
    <div style="clear: both;"></div>
</div>
<div class="home-middle-blocks hidden-xs">
    <div class="row">
        <div class="eight columns">
            <figure class="effect-ruby">
                <img src="<?php echo ot_get_option('middle_block_background_image_1'); ?>" alt="Figure Image"/>
                <figcaption>
                    <h2><?php echo ot_get_option('call_to_action_left_text'); ?></h2>
                    <p>Learn More</p>
                    <a href="<?php echo ot_get_option('call_to_action_left_link'); ?>">Learn More</a>
                </figcaption>
            </figure>
        </div>
        <div class="eight columns">
            <figure class="effect-ruby">
                <img src="<?php echo ot_get_option('middle_block_background_image_2'); ?>" alt="Figure Image"/>
                <figcaption>
                    <h2><?php echo ot_get_option('call_to_action_right_text'); ?></h2>
                    <p>Learn More</p>
                    <a href="<?php echo ot_get_option('call_to_action_right_link'); ?>">Learn More</a>
                </figcaption>
            </figure>
        </div>
    </div>
</div>
<div class="home-middle-blocks hidden-xs">
    <div class="row">
        <div class="sixteen columns blog-roll">
            <div class="header-title-2">
                <h3 class="blog entry-title"><span class="orange">Latest</span> News</h3>
            </div>
        <?php
            $args = array( 'posts_per_page' => 1, 'offset'=> 0, 'category' => 0 );
            $carbide_posts = get_posts( $args );
                foreach ( $carbide_posts as $post ) : setup_postdata( $post );

        ?>
            <div class="blogroll post-align">
                <article>
                    <a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
                        <?php the_post_thumbnail('large' ); ?>
                    </a>
                    <h3 class="entry-title">
                        <a href="<?php the_permalink(); ?>"><?php short_title('','',true, '0'); ?></a>
                    </h3>
                    <p><?php echo substr(get_the_excerpt(), 0, 120); ?> [...]<br>
                        <a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><span class="btn blue-btn breath">Continue Reading »</span></a>
                    </p>
                </article>
            </div>
        <?php endforeach;
            wp_reset_postdata();
        ?>
        </div>
    </div>
</div>
<div class="row hidden-xs">
    <?php get_sidebar('home'); ?>
</div>

<div class="row">
    <div class="sixteen columns">
        <?php the_content(); ?>
    </div>
</div>
