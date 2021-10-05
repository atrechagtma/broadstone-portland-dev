<?php
/**
 * Template part for displaying posts
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package gutenberg-starter-theme
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <div class="container-fluid">
        <header class="entry-header">
            <?php if (is_singular()) : ?>
                <h3 class="entry-title"><?php _e('Blog Posts', 'broadstone'); ?></h3>
            <?php else :
                the_title('<h2 class="entry-title"><a href="' . esc_url(get_permalink()) . '" rel="bookmark">', '</a></h2>');
                if (has_post_thumbnail()) :
                    the_post_thumbnail();
                endif;
            endif; ?>
        </header>

        <div class="entry-content">
            <div class="row">
                <div class="col">
                    <h1><?php the_title(); ?></h1>
                    <time><?php the_date(); ?></time>
                    <div class="post-author"><?php _e('Author', 'broadstone'); ?> | <?php the_author(); ?><div>
                        <div class="the-content">
                            <?php the_content(); ?>
                        </div>
                </div>
            </div>
        </div>
    </div>
</article><!-- #post-<?php the_ID(); ?> -->
