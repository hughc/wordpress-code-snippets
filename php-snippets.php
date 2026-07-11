<?php

/**
 * Plugin Name: RAV Site Snippets
 * Description: A library of small, toggleable code snippets for common site customisations.
 * Version: 1.0.0
 * Author: RAV
 * Text Domain: rav-snippets
 */

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Directory where snippet modules live.
 */
define('php_snippets_DIR', __DIR__ . '/modules');

/**
 * Option name for active snippets.
 */
define('php_snippets_OPTION', 'php_snippets_active');

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
?>
	<div class="wrap">
		<h1><?php esc_html_e('Site Snippets', 'rav-snippets'); ?></h1>
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
	</div>
<?php
}
