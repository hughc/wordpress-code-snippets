<?php

/**
 * Module: Favicon
 * Description: Inject a favicon link from the active theme directory into wp_head and admin_head.
 */

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Path (URI) to the favicon file. Defaults to the active theme's favicon.svg.
 * Override by defining PHP_SNIPPETS_FAVICON_URL in wp-config.php.
 */
if (! defined('PHP_SNIPPETS_FAVICON_URL')) {
	define('PHP_SNIPPETS_FAVICON_URL', get_stylesheet_directory_uri() . '/images/favicon.svg');
}

add_action('wp_head', function () {
	echo '<link rel="icon" type="image/svg+xml" href="' . esc_url(PHP_SNIPPETS_FAVICON_URL) . '">' . "\n";
});

add_action('admin_head', function () {
	echo '<link rel="icon" type="image/svg+xml" href="' . esc_url(PHP_SNIPPETS_FAVICON_URL) . '">' . "\n";
});
