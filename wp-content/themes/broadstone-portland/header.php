<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package gutenberg-starter-theme
 */
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="http://gmpg.org/xfn/11">
    
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-K99XKFX');</script>
    <!-- End Google Tag Manager -->
    
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-223834245-6"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'UA-223834245-6');
    </script>

    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-TSZ4J32"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

    <div id="page" class="site">
        <a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e('Skip to content', 'gutenberg-starter-theme'); ?></a>
        <header id="masthead" class="site-header">
        <?php if (get_theme_mod( 'gtma_theme_cta_is_active' )): ?>
            <?php
            $bgcolor = get_theme_mod( 'gtma_theme_cta_bg_color' );
            $textcolor = get_theme_mod( 'gtma_theme_cta_text_color' );
            ?>
            <style type="text/css">
                .cta-banner a:hover, .cta-banner * { color: <?php echo $textcolor ?>; }
            </style>
            <div class="cta-banner" style="background: <?php echo $bgcolor ?>; color: <?php echo $textcolor ?>;">
            <div class="container d-flex align-items-center justify-content-between">
                <div>
                    <?php echo get_theme_mod( 'gtma_theme_cta_content' ) ?>
                </div>
                <a class="close mr-4">×</a>
            </div>

            </div>
        <?php endif ?>
            <?php if (get_theme_mod( 'gtma_theme_cta_is_active' )): ?>
            <nav class="navbar navbar-expand-lg navbar-light cta-active">
                <?php else : ?>
            <nav class="navbar navbar-expand-lg navbar-light">
            <?php endif ?>
                <div class="container-fluid">
                    <div class="navbar-brand">
                        <?php
                        the_custom_logo();
                        $description = get_bloginfo('description', 'display');
                        if ($description || is_customize_preview()) : ?>
                            <p class="site-description"><?php echo $description; /* WPCS: xss ok. */ ?></p>
                        <?php
                        endif; ?>
                    </div>

                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse justify-content-end" id="navbarSupportedContent">
                        <?php
                            wp_nav_menu(array(
                                'theme_location' => 'menu-1',
                                'menu_id'        => 'primary-menu',
                                'menu_class'     => 'navbar-nav d-flex me-auto mb-2 mb-lg-0',
                            ));
                        ?>
                    </div>
                </div>
            </nav>
        </header><!-- #masthead -->
