<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package gutenberg-starter-theme
 */

?>

<footer class="site-footer text-center">
    <div class="container-fluid">
        <div class="d-flex footer-widgets text-center">
            <?php $widgets = get_field('footer_widgets', 'options'); ?>
            <?php foreach ($widgets as $widget): ?>
                <article class="footer-widget">
                    <h3><?php echo $widget['title']; ?></h3>
                    <?php echo $widget['content']; ?>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="d-flex footer-nav">
            <nav class="text-left">
                <?php
                    wp_nav_menu(array(
                        'theme_location' => 'footer-menu-1',
                        'menu_id'        => 'footer-menu-1',
                        'menu_class'     => 'navbar-nav',
                    ));
                ?>
            </nav>
            <div class="navbar-brand">
                <?php the_custom_logo(); ?>

                <?php
                    if( have_rows('social_links', 'options') ): ?>
                        <div class="social-links">
                            <a href="https://www.greystar.com/fair-housing-statement" target="_blank"><img src="/wp-content/uploads/2021/10/equal-housing-opportunity.svg"></a>
                            <a href="https://www.greystar.com/fair-housing-statement" target="_blank"><img src="/wp-content/uploads/2021/10/wheelchair-symbol.svg"></a>
                            <?php while( have_rows('social_links', 'options') ) : the_row();
                                $social_media = get_sub_field('social_media');
                                $url = get_sub_field('url');
                            ?>
                            <a href="<?php echo $url; ?>" target="_blank">
                                <i class="<?php echo $social_media; ?>"></i>
                            </a>
                            <?php endwhile; ?>
                        </div>
                    <?php endif; ?>

            </div>
            <nav class="text-left">
                <?php
                    wp_nav_menu(array(
                        'theme_location' => 'footer-menu-2',
                        'menu_id'        => 'footer-menu-2',
                        'menu_class'     => 'navbar-nav',
                    ));
                ?>
            </nav>
        </div>
    </div>
    <div class="copyright-section">
        <div class="container-fluid">
            <div class="the-content">
                <?php the_field('copyright', 'options'); ?>
            </div>
        </div>
    </div>
</footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
