<?php
/**
 * Template Name: Sidebar Left
 */

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
        <div class="row">
            <div class="four columns">
                <?php get_sidebar('left'); ?>
            </div>
            <div class="twelve columns">
                <?php
                // Start the loop.
                while ( have_posts() ) : the_post();

                    // Include the page content template.
                    get_template_part( 'content', 'page' );

                    // End the loop.
                endwhile;
                ?>
            </div>
        </div>
    </main><!-- .site-main -->
</div><!-- .content-area -->

<?php get_footer(); ?>
