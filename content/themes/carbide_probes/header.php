<?php
/**
 * The Header for the theme.
 *
 * Displays all of the <head> section and everything up till <div id="content">
 *
 * @package rdmgumby
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js sixteen colgrid">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php wp_title( '|', true, 'right' ); ?><?php bloginfo('name'); ?></title>

	<link rel="author" href="<?php get_template_directory_uri(); ?>/inc/humans.txt">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<div id="page" class="site">

    <header id="mobileMasthead" class="site-header visible-xs" role="banner">
        <div class="row navbar" id="mobileNav1">
            <!-- Toggle for mobile navigation, targeting the <ul> -->
            <!--<a class="toggle" gumby-trigger="#mobileNav1 > ul" href="#"><i class="icon-menu"></i></a>-->
            <a href="#" class="mobile-menu-button toggle" gumby-trigger="#mobileNav1 > ul">
                <div class="mobile-menu-button-wrapper">
                    <div class="line one"><span></span></div>
                    <div class="line two"><span></span></div>
                </div>
            </a>
            <h1 class="logo">
                <a href="/">
                    <img src="<?php echo ot_get_option('company_logo'); ?>" alt="Carbide Probes Logo">
                </a>
            </h1>
            <?php wp_nav_menu( array('theme_location' => 'primary', 'container' => false, 'walker' => new Gumby_Nav_Walker())); ?>
        </div>

        <div class="upper-header">
            <?php wp_nav_menu( array('theme_location' => 'upper_header_mobile') ); ?>
        </div>

        <div class="row">
            <div class="sixteen columns">
                <div class="header-title">
                    <h1>Find Exactly<br>What You Need.</h1>
                    <h2>Finding the right gage tip or stylus<br>has never been easier.</h2>
                </div>
            </div>
        </div>
    </header>

	<header id="masthead" class="site-header hidden-xs" role="banner">
		<div id="upper-header">
			<div class="row">
				<div class="six columns">
					<?php echo generate_phone_link(ot_get_option("company_phone")); ?>
				</div>
				<div class="ten columns">
					<?php wp_nav_menu( array('theme_location' => 'upper_header') ); ?>
				</div>
			</div>
		</div>

		<div class="row navbar" id="nav1">
			<!-- Toggle for mobile navigation, targeting the <ul> -->
			<a class="toggle" gumby-trigger="#nav1 > ul" href="#"><i class="icon-menu"></i></a>
			<h1 class="four columns logo">
				<a href="/">
                    <img src="<?php echo ot_get_option('company_logo'); ?>" alt="Carbide Probes Logo">
				</a>
			</h1>
            <?php wp_nav_menu( array('theme_location' => 'primary', 'container' => false, 'walker' => new Gumby_Nav_Walker())); ?>
		</div>

        <div class="row">
            <div class="sixteen columns">
                <div class="header-title">
                    <h1>Find Exactly<br>What You Need.</h1>
                    <h2>Finding the right gage tip or stylus<br>has never been easier.</h2>
                </div>
            </div>
        </div>

	</header>
	<!-- #masthead -->

	<div id="content" class="site-content">