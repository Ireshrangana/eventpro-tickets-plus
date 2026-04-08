<?php
/**
 * Plugin deactivation.
 *
 * @package EventProTicketsPlus
 */

namespace EventPro\TicketsPlus;

defined( 'ABSPATH' ) || exit;

/**
 * Handles deactivation.
 */
class Deactivator {

	/**
	 * Deactivate plugin.
	 *
	 * @return void
	 */
	public static function deactivate() : void {
		flush_rewrite_rules();
	}
}
