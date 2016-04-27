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
    <?php require_once get_template_directory() . '/inc/analytics.php'; ?>
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
                <a href="<?php echo home_url(); ?>">
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
<!-- THIS IS MOBILE -->
                    <?php
                        $homeTitle = get_field('home_page_title');
                        if(is_page('home')){
                            echo '<h1>' . $homeTitle . '</h1>';
                        }
                        elseif(is_tax('product_cat')){
                            echo woocommerce_page_title();
                        }
                        else{
                            the_title();
                        }
                    ?>
                    </h1>
                    <h2 class="sub-title">
                        <?php
                            $queried_object = get_queried_object();
                            $taxonomy = $queried_object->taxonomy;
                            $term_id = $queried_object->term_id;
                            if(is_tax('product_cat')){
                                the_field('sub_heading', $taxonomy . '_' . $term_id);
                            }
                            else{
                                the_field('sub_heading');
                            }
                        ?>
                    </h2>
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
                <a href="<?php echo home_url(); ?>">
                    <img src="<?php echo ot_get_option('company_logo'); ?>" alt="Carbide Probes Logo">
                </a>
            </h1>
            <?php wp_nav_menu( array('theme_location' => 'primary', 'container' => false, 'walker' => new Gumby_Nav_Walker())); ?>
        </div>

        <div class="row">
            <div class="sixteen columns">
                <div class="header-title">
                    <h1 class="entry-title">
<!-- THIS IS DESKTOP -->
                    <?php
                        $homeTitle = get_field('home_page_title');
                        if(is_page('home')){
                            echo '<h1>' . $homeTitle . '</h1>';
                        }
                        elseif(is_tax('product_cat')){
                            echo woocommerce_page_title();
                        }
                        else{
                            the_title();
                        }
                    ?>
                    </h1>
                    <h2 class="sub-title">
                        <?php
                            $queried_object = get_queried_object();
                            $taxonomy = $queried_object->taxonomy;
                            $term_id = $queried_object->term_id;
                            if(is_tax('product_cat')){
                                the_field('sub_heading', $taxonomy . '_' . $term_id);
                            }
                            else{
                                the_field('sub_heading');
                            }
                        ?>
                    </h2>
                </div>
            </div>
        </div>

    </header>
    <!-- #masthead -->

    <div id="content" class="site-content">
        <div class="row">
            <div class="sixteen columns">
                <?php get_search_form(true); ?>
            </div>
        </div>
