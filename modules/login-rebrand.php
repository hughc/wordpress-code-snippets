<?php

/**
 * Module: Rebrand WP Login
 * Description: Replace the default WordPress logo on wp-login.php with a custom site logo.
 */

if (! defined('ABSPATH')) {
	exit;
}

/**
 * URL to the custom login logo.
 * Defaults to the active theme's logo if it has one, otherwise falls back
 * to a bundled placeholder path. Override by defining PHP_SNIPPETS_LOGIN_LOGO_URL
 * in wp-config.php.
 */
if (! defined('PHP_SNIPPETS_LOGIN_LOGO_URL')) {
	define('PHP_SNIPPETS_LOGIN_LOGO_URL', get_stylesheet_directory_uri() . '/images/login-logo.svg
	');
}

/**
 * Width of the custom login logo (px). Adjust to taste.
 */
if (! defined('PHP_SNIPPETS_LOGIN_LOGO_WIDTH')) {
	define('PHP_SNIPPETS_LOGIN_LOGO_WIDTH', 320);
}

/**
 * URL the login logo links to (default: home URL).
 */
if (! defined('PHP_SNIPPETS_LOGIN_LOGO_LINK')) {
	define('PHP_SNIPPETS_LOGIN_LOGO_LINK', home_url('/'));
}

/**
 * Enqueue custom CSS on the login page to swap the logo.
 */
add_action('login_enqueue_scripts', function () {
	$logo_url  = PHP_SNIPPETS_LOGIN_LOGO_URL;
	$logo_w    = PHP_SNIPPETS_LOGIN_LOGO_WIDTH;
?>
	<style type="text/css">
		#login h1 a {
			background-image: url("<?php echo esc_url($logo_url); ?>");
			width: <?php echo intval($logo_w); ?>px;
			background-size: contain;
			background-repeat: no-repeat;
			background-position: center;
			padding-bottom: 30px;
		}
	</style>
<?php
});

/**
 * Change the login logo link URL.
 */
add_filter('login_headerurl', function () {
	return PHP_SNIPPETS_LOGIN_LOGO_LINK;
});

/**
 * Change the login logo title attribute.
 */
add_filter('login_headertext', function () {
	return get_bloginfo('name');
});
