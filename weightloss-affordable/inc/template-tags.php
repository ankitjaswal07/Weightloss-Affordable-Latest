<?php
/** Reusable template helpers. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
function wla_posted_on() {
	$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
	if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) { $time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>'; }
	$time_string = sprintf( $time_string, esc_attr( get_the_date( DATE_W3C ) ), esc_html( get_the_date() ), esc_attr( get_the_modified_date( DATE_W3C ) ), esc_html( get_the_modified_date() ) );
	printf( '<span class="posted-on">%1$s %2$s</span>', esc_html( wla_copy( 'Updated', 'Actualizado' ) ), wp_kses_post( $time_string ) );
}
function wla_reading_time() {
	$content = wp_strip_all_tags( get_post_field( 'post_content', get_the_ID() ) );
	$minutes = max( 1, (int) ceil( str_word_count( $content ) / 220 ) );
	printf( '%1$s %2$s', esc_html( number_format_i18n( $minutes ) ), esc_html( wla_copy( 'min read', 'min de lectura' ) ) );
}
