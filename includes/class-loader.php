<?php
/**
 * Hook loader.
 *
 * @package EventProTicketsPlus
 */

namespace EventPro\TicketsPlus;

defined( 'ABSPATH' ) || exit;

/**
 * Simple action/filter registry.
 */
class Loader {

	/**
	 * Registered actions.
	 *
	 * @var array<int, array<string, mixed>>
	 */
	protected array $actions = array();

	/**
	 * Registered filters.
	 *
	 * @var array<int, array<string, mixed>>
	 */
	protected array $filters = array();

	/**
	 * Register a WordPress action.
	 *
	 * @param string   $hook Hook name.
	 * @param object   $component Component instance.
	 * @param string   $callback Method name.
	 * @param int      $priority Priority.
	 * @param int      $accepted_args Accepted args.
	 * @return void
	 */
	public function add_action( string $hook, object $component, string $callback, int $priority = 10, int $accepted_args = 1 ) : void {
		$this->actions[] = compact( 'hook', 'component', 'callback', 'priority', 'accepted_args' );
	}

	/**
	 * Register a WordPress filter.
	 *
	 * @param string   $hook Hook name.
	 * @param object   $component Component instance.
	 * @param string   $callback Method name.
	 * @param int      $priority Priority.
	 * @param int      $accepted_args Accepted args.
	 * @return void
	 */
	public function add_filter( string $hook, object $component, string $callback, int $priority = 10, int $accepted_args = 1 ) : void {
		$this->filters[] = compact( 'hook', 'component', 'callback', 'priority', 'accepted_args' );
	}

	/**
	 * Execute all stored hooks.
	 *
	 * @return void
	 */
	public function run() : void {
		foreach ( $this->filters as $hook ) {
			add_filter(
				$hook['hook'],
				array( $hook['component'], $hook['callback'] ),
				$hook['priority'],
				$hook['accepted_args']
			);
		}

		foreach ( $this->actions as $hook ) {
			add_action(
				$hook['hook'],
				array( $hook['component'], $hook['callback'] ),
				$hook['priority'],
				$hook['accepted_args']
			);
		}
	}
}
