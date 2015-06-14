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

    <div class="row">
        <div class="footer-widget-container">
            <div class="footer-widget-content contact-info">
                <img src="<?php echo ot_get_option('footer_logo'); ?>" alt="Logo" /><br>
                <p><?php echo ot_get_option('footer_address'); ?></p>
                <ul>
                    <li><i class="fa fa-phone"></i>&nbsp; <?php echo generate_phone_link(ot_get_option('footer_phone')); ?></li>
                    <li><i class="fa fa-fax"></i> <?php echo generate_phone_link(ot_get_option('footer_fax')); ?></li>
                    <li><i class="fa fa-envelope"></i> <a href="mailto:<?php echo antispambot( ot_get_option('footer_email') ); ?>"><?php echo ot_get_option('footer_email'); ?></a></li>
                </ul>
            </div>
            <div class="footer-widget-content newsletter hidden-xs">
                <h1 class="widget-title">Newsletter</h1>
                <p>Get the latest news! Sign up for our newsletter to stay current!</p>
                <?php gravity_form( "Newsletter", false, false, false ); ?>
            </div>
            <div class="footer-widget-content hidden-xs">
                <h1 class="widget-title">Testimonials</h1>
                <div class="testimonial">
                    <blockquote>
                        <p>"Your A+ rating for customer service restores my faith that our industry can survive!"</p>
                        <cite>North American Measurement Systems</cite>
                    </blockquote>
                </div>
                <div class="testimonial">
                    <blockquote>
                        <p>"Thank you! Things run very smoothly working with your company."</p>
                        <cite>CNC Services, Inc.</cite>
                    </blockquote>
                </div>
            </div>
        </div>
    </div>

	<footer id="colophon" class="site-footer" role="contentinfo">
        <div class="row">
            <div class="ten columns">
                <div class="site-info">
                    <?php echo ot_get_option("footer_copyright"); ?>
                </div><!-- .site-info -->
            </div>
            <div class="six columns">
                <div class="footer-menu hidden-xs">
                    <?php wp_nav_menu( array('theme_location' => 'footer_menu') ); ?>
                </div>
            </div>
        </div>
	</footer><!-- #colophon .site-footer -->

</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>