<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after
 *
 * @package rdmgumby
 */
?>

	</div><!-- #content .site-content -->

	<footer id="colophon" class="site-footer" role="contentinfo">
        <div class="row">
            <div class="six columns">
                <div class="site-info">
                    <?php echo ot_get_option("footer_copyright"); ?>
                </div><!-- .site-info -->
            </div>
            <div class="ten columns">
                <div class="footer-menu">
                    <?php wp_nav_menu( array('theme_location' => 'footer_menu') ); ?>
                </div>
            </div>
        </div>
	</footer><!-- #colophon .site-footer -->

</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>