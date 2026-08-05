<?php

/**
 * Simple debug logger for development.
 *
 * Writes labelled variable dumps to PHP error_log.
 *
 * @package PhPSnippets
 */

namespace PhPSnippets;

// Exit if accessed directly.
if (! defined('ABSPATH')) {
	exit;
}

class Debug
{
	/**
	 * Log a labelled variable dump to error_log.
	 *
	 * @param mixed  $var   The variable to dump.
	 * @param string $label Context label (prepended to output).
	 */
	public static function log($var, string $label = 'debug'): void
	{
		$output = print_r($var, true);
		error_log("[php-snippets {$label}] {$output}");
	}

	/**
	 * Log a message (no variable dump) to error_log.
	 *
	 * @param string $message
	 */
	public static function msg(string $message, string $label = 'info'): void
	{
		error_log("[php-snippets {$label}] {$message}");
	}

	/**
	 * Log a backtrace to error_log for tracing call paths.
	 */
	public static function trace(string $label = 'trace'): void
	{
		ob_start();
		debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
		$trace = ob_get_clean();
		error_log("[php-snippets {$label}] {$trace}");
	}
}
