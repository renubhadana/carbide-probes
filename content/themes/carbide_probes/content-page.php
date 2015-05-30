<?php
/**
 * The template used for displaying page content
 */
?>

<div class="row">
    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

        <?php the_post_thumbnail(); ?>

        <header class="entry-header">
            <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
        </header><!-- .entry-header -->

        <div class="entry-content">
            <?php the_content(); ?>
        </div><!-- .entry-content -->

    </article><!-- #post-## -->
</div>
