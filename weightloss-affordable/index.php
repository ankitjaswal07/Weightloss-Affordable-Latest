<?php
/** Fallback template. */
get_header(); ?>
<main id="main-content" class="wla-main"><div class="wla-wrap wla-archive-wrap"><header class="wla-archive-header"><p class="wla-eyebrow"><?php echo esc_html( wla_copy( 'Learn at your own pace', 'Aprende a tu propio ritmo' ) ); ?></p><h1><?php echo esc_html( wla_copy( 'Resources', 'Recursos' ) ); ?></h1></header>
<?php if ( have_posts() ) : ?><div class="wla-archive-grid"><?php while ( have_posts() ) : the_post(); ?><article <?php post_class( 'wla-archive-card' ); ?>><div class="wla-archive-card-content"><p class="wla-card-meta"><?php wla_posted_on(); ?></p><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><p><?php echo esc_html( wp_trim_words( get_the_excerpt() ?: wp_strip_all_tags( get_the_content() ), 28 ) ); ?></p><a class="wla-text-link" href="<?php the_permalink(); ?>"><?php echo esc_html( wla_copy( 'Read more', 'Leer más' ) ); ?> <span aria-hidden="true">→</span></a></div></article><?php endwhile; ?></div><?php the_posts_pagination(); ?><?php else : ?><p><?php echo esc_html( wla_copy( 'No content has been published here yet.', 'Todavía no se ha publicado contenido.' ) ); ?></p><?php endif; ?>
</div></main>
<?php get_footer(); ?>
