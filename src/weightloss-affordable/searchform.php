<?php
/**
 * Search form.
 *
 * @package WeightLossAffordable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$wla_search_id = 'wla-search-' . wp_unique_id();
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label for="<?php echo esc_attr( $wla_search_id ); ?>" class="screen-reader-text"><?php esc_html_e( 'Search this website', 'weightloss-affordable' ); ?></label>
	<input id="<?php echo esc_attr( $wla_search_id ); ?>" type="search" class="search-field" placeholder="<?php esc_attr_e( 'Search…', 'weightloss-affordable' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" name="s" />
	<button type="submit" class="button button-primary"><?php esc_html_e( 'Search', 'weightloss-affordable' ); ?></button>
</form>
