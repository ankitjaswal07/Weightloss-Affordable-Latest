<?php
/**
 * 404 Template — matches exact Straw luxury design.
 */
get_header();
echo '<main id="main-content">';
echo wla_render_subpage_hero(
	array(
		'eyebrow' => 'PAGE NOT FOUND',
		'title'   => "Let's find a clearer way forward.",
		'lead'    => 'This page may have moved. Explore the homepage or send a general question below.',
		'badge'   => 'Return home or explore guides',
	)
);
echo wla_render_cta_band(
	array(
		'title'       => 'Looking for a specific page?',
		'body'        => 'Browse the full page directory below, or send us a general question.',
		'button_text' => 'Open the Contact page',
		'button_url'  => '/contact',
	)
);
echo wla_render_page_nav_strip( '' );
echo '</main>';
get_footer();
