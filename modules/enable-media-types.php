<?php
/**
 * Module: Enable SVG & PDF Uploads
 * Description: Allow SVG and PDF files in the WordPress media library.
 */

add_filter( 'upload_mimes', function ( $mimes ) {
	$mimes['svg']  = 'image/svg+xml';
	$mimes['pdf']  = 'application/pdf';
	$mimes['svgz'] = 'image/svg+xml';
	return $mimes;
} );

// Ensure SVG files are treated as images for thumbnail generation.
add_filter( 'wp_check_filetype_and_ext', function ( $data, $file, $filename, $mimes ) {
	if ( ! $data['ext'] || ! $data['type'] ) {
		$wp_filetype = wp_check_filetype( $filename, $mimes );
		if ( $wp_filetype['ext'] === 'svg' ) {
			$data['ext']  = 'svg';
			$data['type'] = 'image/svg+xml';
		} elseif ( $wp_filetype['ext'] === 'pdf' ) {
			$data['ext']  = 'pdf';
			$data['type'] = 'application/pdf';
		}
	}
	return $data;
}, 10, 4 );
