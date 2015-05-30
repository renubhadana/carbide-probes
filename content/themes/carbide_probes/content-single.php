<?php
/**
 * The default template for displaying single content
 */
?>

<div class="row" xmlns="http://www.w3.org/1999/html">
    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <div class="sixteen columns">

                <header class="entry-header">
                    <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
                </header><!-- .entry-header -->

                <?php if(has_post_thumbnail()): ?>
                    <div class="featured-image">
                        <?php the_post_thumbnail(); ?>
                    </div>
                <?php endif; ?>

                <div class="entry-content">
                    <?php the_content(); ?>
                </div><!-- .entry-content -->

            </div>
    </article>
</div>