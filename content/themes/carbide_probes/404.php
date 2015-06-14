<?php
/**
 * What happens when you can't find it? Tell 'em.
 */

get_header(); ?>

    <div class="row">
        <div class="sixteen columns">
            <?php echo ot_get_option('not_found_content'); ?>

        </div>
    </div>

<?php get_footer(); ?>