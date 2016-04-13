<?php
/**
 * The default template for displaying single content
 */
?>

<div class="row" xmlns="http://www.w3.org/1999/html">
    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <div class="sixteen columns">
                <?php
                    $args = array( 'posts_per_page' => 5, 'offset'=> 0, 'category' => 1 );
                    $myposts = get_posts( $args );
                        foreach ( $myposts as $post ) : setup_postdata( $post );

                ?>
                    <div class="blogroll post-align">
                            <aside class="post-image">
                                <a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
                                    <?php the_post_thumbnail('thumbnail' ); ?>
                                </a>
                            </aside>
                            <article>
                                <h3 class="entry-title">
                                    <a href="<?php the_permalink(); ?>"><?php short_title('','...',true, '25'); ?></a>
                                </h3>
                                <p><?php the_excerpt(); ?>
                                    <a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><span class="btn blue-btn">Continue Reading »</span></a>
                                </p>
                            </article>
                    </div>
                <?php endforeach;
                    wp_reset_postdata();
                ?>
            </div>
    </article>
</div>
