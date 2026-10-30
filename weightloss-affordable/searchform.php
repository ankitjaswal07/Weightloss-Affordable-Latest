<?php
/** Accessible localized search form. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
$placeholder = wla_copy( 'Search the site', 'Buscar en el sitio' );
?>
<form role="search" method="get" class="search-form wla-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>"><label><span class="screen-reader-text"><?php echo esc_html( $placeholder ); ?></span><input type="search" class="search-field" placeholder="<?php echo esc_attr( $placeholder ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" name="s"></label><button type="submit" class="wla-button"><?php echo esc_html( wla_copy( 'Search', 'Buscar' ) ); ?></button></form>
