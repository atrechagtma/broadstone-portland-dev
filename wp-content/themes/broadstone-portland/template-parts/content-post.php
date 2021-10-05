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
        <header class="single-entry-header">
            <h3 class="entry-title"><?php _e('Blog Posts', 'broadstone'); ?></h3>
            <a class="btb-btn" href="<?php echo get_field('blog_page', 'options')['url']; ?>">
                <i class="fas fa-arrow-left"></i>
                <span><?php _e('back to blog posts list', 'broadstone'); ?></span>
            </a>
        </header>
        <div>
            <div class="row align-items-center">
                <div class="col-md-7">
                    <h1><?php the_title(); ?></h1>
                    <div class="post-meta">
                        <time><?php the_date(); ?></time>
                        <div class="post-author"><?php _e('Author', 'broadstone'); ?> | <?php the_author(); ?></div>
                    </div>
                    <div class="the-content">
                        <?php the_content(); ?>
                    </div>
                </div>
                <div class="col-md-5">
                    <?php if (has_post_thumbnail()) :
                        the_post_thumbnail();
                    endif; ?>
                </div>
            </div>
        </div>
    </div>
</article><!-- #post-<?php the_ID(); ?> -->
