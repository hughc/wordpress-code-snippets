<?php
/**
 * Module: Disable Comments Everywhere
 * Description: Remove comment support from all post types and hide comment-related admin UI.
 */

// Disable comments on all existing post types.
add_action( 'init', function () {
	foreach ( get_post_types() as $post_type ) {
		if ( post_type_supports( $post_type, 'comments' ) ) {
			remove_post_type_support( $post_type, 'comments' );
		}
	}
}, 20 );

// Hide the Comments menu item.
add_action( 'admin_menu', function () {
	remove_menu_page( 'edit-comments.php' );
} );

// Remove "Recent Comments" dashboard widget.
add_action( 'wp_dashboard_setup', function () {
	remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
} );

// Redirect any direct access to the comments admin page.
add_action( 'admin_init', function () {
	if ( isset( $_GET['page'] ) && $_GET['page'] === 'edit-comments.php' ) {
		wp_safe_redirect( admin_url() );
		exit;
	}
} );

// Disable comment-related admin bar items.
add_action( 'admin_bar_menu', function ( $wp_admin_bar ) {
	$wp_admin_bar->remove_node( 'comments' );
}, 999 );
