<?php
/**
 * Short Title ACF Block - Frontend & Editor Render
 *
 * Displays the 'short_title' meta field scoped to the current post/page.
 * This is NOT a block attribute — it reads from the post's meta directly.
 */

if (! defined('ABSPATH')) {
	exit;
}

// Get the post ID — works in both frontend and editor preview context.
$post_id = get_the_ID();
if (! $post_id) {
	$post_id = $block->post_id ?? 0;
}

$short_title = '';
if ($post_id) {
	$short_title = get_post_meta($post_id, 'short_title', true);
}

// Fall back to the post title if short_title is empty.
if (empty($short_title) && $post_id) {
	$short_title = get_the_title($post_id);
}

if (empty($short_title)) {
	return;
}

$classes = ['rav-short-title'];
if (! empty($block->className)) {
	$classes[] = $block->className;
}
if (! empty($block->align)) {
	$classes[] = 'align' . $block->align;
}

$anchor = ! empty($block->anchor) ? ' id="' . esc_attr($block->anchor) . '"' : '';
?>
<div<?php echo $anchor; ?> class="<?php echo esc_attr(implode(' ', $classes)); ?>">
	<h2 class="rav-short-title__text"><?php echo esc_html($short_title); ?></h2>
</div>
