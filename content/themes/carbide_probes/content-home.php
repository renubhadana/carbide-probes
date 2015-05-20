<?php
/**
 * The template used for displaying page content
 */
?>
<div class="row">
    <?php $wcatTerms = get_terms('product_cat', array('hide_empty' => 0, 'orderby' => 'ASC',  'parent' =>0)); //, 'exclude' => '17,77'
    foreach($wcatTerms as $wcatTerm) :
        $count = 0;
        $wthumbnail_id = get_woocommerce_term_meta( $wcatTerm->term_id, 'thumbnail_id', true );
        $wimage = wp_get_attachment_url( $wthumbnail_id );
        ?>
        <div class="category-container">
            <div class="category-content">
                <?php if($wimage!=""):?><img src="<?php echo $wimage?>"><?php endif;?>
                <a href="<?php echo get_term_link( $wcatTerm->slug, $wcatTerm->taxonomy ); ?>"><?php echo $wcatTerm->name; ?></a>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="row">
    <div class="sixteen columns">
        <?php the_content(); ?>
    </div>
</div>
