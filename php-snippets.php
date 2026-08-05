<?php

/**
 * Plugin Name: RAV Site Snippets
 * Description: A library of small, toggleable code snippets for common site customisations.
 * Version: 1.1.0
 * Author: RAV
 * Text Domain: rav-snippets
 */

use PhPSnippets\Debug;

if (! defined('ABSPATH')) {
	exit;
}


define('SNIPPETS_PLUGIN_DIR', plugin_dir_path(__FILE__));

require_once SNIPPETS_PLUGIN_DIR . 'debug.php';

/**
 * Directory where snippet modules live.
 */
define('php_snippets_DIR', __DIR__ . '/modules');

/**
 * Option name for active snippets.
 */
define('php_snippets_OPTION', 'php_snippets_active');

/**
 * Directory where ACF block modules live.
 */
define('php_snippets_BLOCKS_DIR', __DIR__ . '/blocks');

/**
 * Option name for active ACF blocks.
 */
define('php_snippets_BLOCKS_OPTION', 'php_snippets_active_blocks');

/**
 * Directory where JS snippet modules live.
 */
define('js_snippets_DIR', __DIR__ . '/js-modules');

/**
 * Option name for active JS snippets.
 */
define('js_snippets_OPTION', 'js_snippets_active');

/**
 * Parse the header comment block from a PHP file.
 *
 * @param string $file Path to the PHP file.
 * @return array{title: string, desc: string}|null
 */
function php_snippets_parse_header($file)
{
	$contents = file_get_contents($file);
	if (! preg_match('#/\*\*\s*\n\s*\*\s*Module:\s*(.+?)\s*\n\s*\*\s*Description:\s*(.+?)\s*\n\s*\*/#s', $contents, $m)) {
		return null;
	}
	return [
		'title' => trim($m[1]),
		'desc'  => trim($m[2]),
	];
}

/**
 * Discover all snippet modules.
 *
 * @return array<string, array{title: string, desc: string}>
 */
function php_snippets_discover()
{
	$modules = [];
	if (! is_dir(php_snippets_DIR)) {
		return $modules;
	}
	foreach (glob(php_snippets_DIR . '/*.php') as $file) {
		$slug = basename($file, '.php');
		$header = php_snippets_parse_header($file);
		if ($header) {
			$modules[$slug] = $header;
		}
	}
	return $modules;
}

/**
 * Load active snippet modules.
 */
function php_snippets_load_active()
{
	$active = (array) get_option(php_snippets_OPTION, []);
	foreach ($active as $slug) {
		$file = php_snippets_DIR . '/' . $slug . '.php';
		if (file_exists($file)) {
			include_once $file;
		}
	}
}
add_action('plugins_loaded', 'php_snippets_load_active');

/**
 * Parse the header comment block from a JS file.
 *
 * @param string $file Path to the JS file.
 * @return array{title: string, desc: string}|null
 */
function js_snippets_parse_header($file)
{
	$contents = file_get_contents($file);
	if (! preg_match('#/\*\*\s*\n\s*\*\s*Module:\s*(.+?)\s*\n\s*\*\s*Description:\s*(.+?)\s*\n\s*\*/#s', $contents, $m)) {
		return null;
	}
	return [
		'title' => trim($m[1]),
		'desc'  => trim($m[2]),
	];
}

/**
 * Discover all JS snippet modules.
 *
 * @return array<string, array{title: string, desc: string}>
 */
function js_snippets_discover()
{
	$modules = [];
	if (! is_dir(js_snippets_DIR)) {
		return $modules;
	}
	foreach (glob(js_snippets_DIR . '/*.js') as $file) {
		$slug = basename($file, '.js');
		$header = js_snippets_parse_header($file);
		if ($header) {
			$modules[$slug] = $header;
		}
	}
	return $modules;
}

/**
 * Enqueue active JS snippet modules.
 */
function js_snippets_enqueue_active()
{
	$active = (array) get_option(js_snippets_OPTION, []);
	foreach ($active as $slug) {
		$file = js_snippets_DIR . '/' . $slug . '.js';
		if (file_exists($file)) {
			wp_enqueue_script(
				'js-snippet-' . sanitize_title($slug),
				plugin_dir_url(__FILE__) . 'js-modules/' . $slug . '.js',
				['jquery'],
				filemtime($file),
				true
			);
		}
	}
}
add_action('wp_footer', 'js_snippets_enqueue_active', 1);

/**
 * Discover all ACF block modules.
 *
 * @return array<string, array{title: string, desc: string, dir: string}>
 */
function php_snippets_blocks_discover()
{
	$blocks = [];
	if (! is_dir(php_snippets_BLOCKS_DIR)) {
		return $blocks;
	}
	foreach (scandir(php_snippets_BLOCKS_DIR) as $slug) {
		if ($slug === '.' || $slug === '..') {
			continue;
		}
		$dir = php_snippets_BLOCKS_DIR . '/' . $slug;
		if (! is_dir($dir)) {
			continue;
		}
		$block_json = $dir . '/block.json';
		if (! file_exists($block_json)) {
			continue;
		}
		$data = json_decode(file_get_contents($block_json), true);
		if (! $data || empty($data['title'])) {
			continue;
		}
		$blocks[$slug] = [
			'title' => $data['title'],
			'desc'  => $data['description'] ?? '',
			'dir'   => $dir,
		];
	}
	return $blocks;
}

/**
 * Register enabled ACF blocks.
 */
function php_snippets_blocks_register()
{
	if (! function_exists('acf_register_block_type')) {
		return;
	}
	$active = (array) get_option(php_snippets_BLOCKS_OPTION, []);
	foreach ($active as $slug) {
		$dir = php_snippets_BLOCKS_DIR . '/' . $slug;
		$block_json = $dir . '/block.json';
		if (file_exists($block_json)) {
			register_block_type($dir);
		}
	}
}
add_action('init', 'php_snippets_blocks_register');

/**
 * Enqueue block assets (styles) for enabled blocks.
 */
function php_snippets_blocks_enqueue_assets($block)
{
	$slug = str_replace('rav/', '', $block['name']);
	$style_file = php_snippets_BLOCKS_DIR . '/' . $slug . '/style.css';
	if (file_exists($style_file)) {
		wp_enqueue_style(
			'php-snippets-block-' . sanitize_title($slug),
			plugin_dir_url(__FILE__) . 'blocks/' . $slug . '/style.css',
			[],
			filemtime($style_file)
		);
	}
}
add_action('enqueue_block_assets', function () {
	// Assets are enqueued per-block at render time via renderTemplate.
	// This hook is kept for any future shared block assets.
});

/**
 * Register admin menu page.
 */
function php_snippets_admin_menu()
{
	add_options_page(
		__('Site Snippets', 'rav-snippets'),
		__('Site Snippets', 'rav-snippets'),
		'manage_options',
		'rav-snippets',
		'php_snippets_admin_page'
	);
}
add_action('admin_menu', 'php_snippets_admin_menu');

/**
 * Render the admin page.
 */
function php_snippets_admin_page()
{
	$modules = php_snippets_discover();
	$active  = (array) get_option(php_snippets_OPTION, []);

	if (isset($_POST['php_snippets_save']) && check_admin_referer('php_snippets_save')) {
		$checked = isset($_POST['rav_snippet_modules']) ? (array) $_POST['rav_snippet_modules'] : [];
		update_option(php_snippets_OPTION, array_values(array_intersect($checked, array_keys($modules))));
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Settings saved.', 'rav-snippets') . '</p></div>';
		$active = (array) get_option(php_snippets_OPTION, []);
	}

	// Handle block enable/disable save.
	$blocks = php_snippets_blocks_discover();
	$active_blocks = (array) get_option(php_snippets_BLOCKS_OPTION, []);

	if (isset($_POST['php_snippets_blocks_save']) && check_admin_referer('php_snippets_blocks_save')) {
		$checked_blocks = isset($_POST['rav_snippet_blocks']) ? (array) $_POST['rav_snippet_blocks'] : [];
		update_option(php_snippets_BLOCKS_OPTION, array_values(array_intersect($checked_blocks, array_keys($blocks))));
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Block settings saved.', 'rav-snippets') . '</p></div>';
		$active_blocks = (array) get_option(php_snippets_BLOCKS_OPTION, []);
	}

	// Handle JS snippet enable/disable save.
	$js_modules = js_snippets_discover();
	$active_js = (array) get_option(js_snippets_OPTION, []);

	if (isset($_POST['js_snippets_save']) && check_admin_referer('js_snippets_save')) {
		$checked_js = isset($_POST['rav_js_snippets']) ? (array) $_POST['rav_js_snippets'] : [];
		update_option(js_snippets_OPTION, array_values(array_intersect($checked_js, array_keys($js_modules))));
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('JS snippet settings saved.', 'rav-snippets') . '</p></div>';
		$active_js = (array) get_option(js_snippets_OPTION, []);
	}

	$acf_available = function_exists('acf_register_block_type');
?>
	<div class="wrap">
		<h1><?php esc_html_e('Site Snippets', 'rav-snippets'); ?></h1>

		<h2><?php esc_html_e('PHP Modules', 'rav-snippets'); ?></h2>
		<form method="post">
			<?php wp_nonce_field('php_snippets_save'); ?>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th style="width:40px;"></th>
						<th><?php esc_html_e('Module', 'rav-snippets'); ?></th>
						<th><?php esc_html_e('Description', 'rav-snippets'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if (empty($modules)) : ?>
						<tr>
							<td colspan="3"><?php esc_html_e('No snippet modules found.', 'rav-snippets'); ?></td>
						</tr>
					<?php else : ?>
						<?php foreach ($modules as $slug => $info) : ?>
							<tr>
								<td><input type="checkbox" name="rav_snippet_modules[]" value="<?php echo esc_attr($slug); ?>" <?php checked(in_array($slug, $active, true)); ?> /></td>
								<td><strong><?php echo esc_html($info['title']); ?></strong><br /><code><?php echo esc_html($slug); ?></code></td>
								<td><?php echo esc_html($info['desc']); ?></td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
			<p class="submit"><input type="submit" name="php_snippets_save" class="button button-primary" value="<?php esc_attr_e('Save Changes', 'rav-snippets'); ?>" /></p>
		</form>

		<hr style="margin: 2em 0;" />

		<h2><?php esc_html_e('JS Modules', 'rav-snippets'); ?></h2>
		<form method="post">
			<?php wp_nonce_field('js_snippets_save'); ?>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th style="width:40px;"></th>
						<th><?php esc_html_e('Module', 'rav-snippets'); ?></th>
						<th><?php esc_html_e('Description', 'rav-snippets'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if (empty($js_modules)) : ?>
						<tr>
							<td colspan="3"><?php esc_html_e('No JS snippet modules found.', 'rav-snippets'); ?></td>
						</tr>
					<?php else : ?>
						<?php foreach ($js_modules as $slug => $info) : ?>
							<tr>
								<td><input type="checkbox" name="rav_js_snippets[]" value="<?php echo esc_attr($slug); ?>" <?php checked(in_array($slug, $active_js, true)); ?> /></td>
								<td><strong><?php echo esc_html($info['title']); ?></strong><br /><code><?php echo esc_html($slug); ?></code></td>
								<td><?php echo esc_html($info['desc']); ?></td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
			<p class="submit"><input type="submit" name="js_snippets_save" class="button button-primary" value="<?php esc_attr_e('Save JS Snippet Changes', 'rav-snippets'); ?>" /></p>
		</form>

		<hr style="margin: 2em 0;" />

		<h2><?php esc_html_e('ACF Blocks', 'rav-snippets'); ?></h2>
		<?php if (! $acf_available) : ?>
			<div class="notice notice-warning">
				<p><?php esc_html_e('ACF (Advanced Custom Fields) is not active. Blocks will not register until ACF is installed and activated.', 'rav-snippets'); ?></p>
			</div>
		<?php endif; ?>
		<form method="post">
			<?php wp_nonce_field('php_snippets_blocks_save'); ?>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th style="width:40px;"></th>
						<th><?php esc_html_e('Block', 'rav-snippets'); ?></th>
						<th><?php esc_html_e('Description', 'rav-snippets'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if (empty($blocks)) : ?>
						<tr>
							<td colspan="3"><?php esc_html_e('No ACF block modules found.', 'rav-snippets'); ?></td>
						</tr>
					<?php else : ?>
						<?php foreach ($blocks as $slug => $info) : ?>
							<tr>
								<td><input type="checkbox" name="rav_snippet_blocks[]" value="<?php echo esc_attr($slug); ?>" <?php checked(in_array($slug, $active_blocks, true)); ?> <?php disabled(! $acf_available); ?> /></td>
								<td><strong><?php echo esc_html($info['title']); ?></strong><br /><code><?php echo esc_html($slug); ?></code></td>
								<td><?php echo esc_html($info['desc']); ?></td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
			<p class="submit"><input type="submit" name="php_snippets_blocks_save" class="button button-primary" value="<?php esc_attr_e('Save Block Settings', 'rav-snippets'); ?>" /></p>
		</form>
	</div>
<?php
}
