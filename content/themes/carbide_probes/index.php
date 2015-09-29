<?php
/**
 * The main template file.
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no front-page.php file exists.
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package rdmgumby
 */
get_header(); ?>

		<?php if ( have_posts() ) : ?>

			<?php /* Start the Loop */ ?>
			<?php while ( have_posts() ) : the_post(); ?>

				<?php
					/* Include the Post-Format-specific template for the content.
					 * If you want to override this in a child theme, then include a file
					 * called content-___.php (where ___ is the Post Format name) and that will be used instead.
					 */
					get_template_part( 'content', get_post_format() );
				?>

			<?php endwhile; ?>

            <div class="l-v-margin woocommerce">
                <nav class="woocommerce-pagination">
                    <?php
                        echo paginate_links( array(
                            'base'         => esc_url_raw( str_replace( 999999999, '%#%', get_pagenum_link( 999999999, false ) ) ),
                            'format'       => '',
                            'add_args'     => '',
                            'current'      => max( 1, get_query_var( 'paged' ) ),
                            'total'        => $wp_query->max_num_pages,
                            'prev_text'    => '&larr;',
                            'next_text'    => '&rarr;',
                            'type'         => 'list',
                            'end_size'     => 3,
                            'mid_size'     => 3
                        ) );
                    ?>
                </nav>
            </div>

		<?php else : ?>

			<?php get_template_part( 'content', 'none' ); ?>

		<?php endif; ?>

<?php get_footer(); ?>
