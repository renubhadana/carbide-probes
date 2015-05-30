<?php
/**
 * The default template for displaying content
 *
 * Used for both single and index/archive/search.
 */
?>

<div class="row" xmlns="http://www.w3.org/1999/html">
    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
        <?php if(has_post_thumbnail()): ?>
            <div class="four columns">
                <div class="featured-image">
                    <?php the_post_thumbnail(); ?>
                </div>
            </div>
            <div class="twelve columns">
                <header class="entry-header">
                    <?php
                    if ( is_single() ) :
                        the_title( '<h1 class="entry-title">', '</h1>' );
                    else :
                        the_title( sprintf( '<h2 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' );
                    endif;
                    ?>
                </header><!-- .entry-header -->

                <div class="entry-content">
                    <?php the_content( 'Read More &gg;', 'rdmgumby' ); ?>
                </div><!-- .entry-content -->
            </div>
        <?php else: ?>
            <div class="sixteen columns">
                <header class="entry-header">
                    <?php
                    if ( is_single() ) :
                        the_title( '<h1 class="entry-title">', '</h1>' );
                    else :
                        the_title( sprintf( '<h2 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' );
                    endif;
                    ?>
                </header><!-- .entry-header -->

                <div class="entry-content">
                    <?php the_content( 'Read More &gg;', 'rdmgumby' ); ?>
                </div><!-- .entry-content -->
            </div>
        <?php endif; ?>
    </article>
</div>
