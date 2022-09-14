<?php

declare( strict_types=1 );

namespace SEOThemes\Hooks;

use Closure;
use function add_filter;
use function class_exists;
use function func_get_args;
use function is_array;

if ( ! class_exists(__NAMESPACE__ . '\\Hooks')) {

	class Hooks {
		private Container $container;

		public function __construct( Container $container ) {
			$this->container = $container;
		}

		/**
		 * Adds hook to container and registers with WordPress.
		 *
		 * @since 1.0.0
		 *
		 * @param string  $hook_name Hook name or array of hook names.
		 * @param string  $alias
		 * @param Closure $callback
		 * @param int     $priority
		 * @param int     $accepted_args
		 *
		 * @return bool
		 */
		public function add( string $hook_name, string $alias, Closure $callback, int $priority = 10, int $accepted_args = 1 ): bool {
			$this->container->add( $hook_name, $alias, $callback, $priority );

			return add_filter(
				$hook_name,
				function () use ( $hook_name, $alias, $callback, $priority, $accepted_args ) {
					$args = func_get_args();

					if ( $this->container->has( $hook_name, $alias, $priority ) ) {
						return $callback( ...$args );
					}

					return is_array( $args ) ? $args[0] : $args;
				},
				$priority,
				$accepted_args
			);
		}

		/**
		 * Removes hook from container which prevents closure being called.
		 *
		 * @since 1.0.0
		 *
		 * @param string $hook_name
		 * @param string $alias
		 * @param int    $priority
		 *
		 * @return bool Whether the hook was removed.
		 */
		public function remove( string $hook_name, string $alias, int $priority = 10 ): bool {
			if ( $this->container->has( $hook_name, $alias, $priority ) ) {
				$this->container->remove( $hook_name, $alias, $priority );

				return true;
			}

			return false;
		}
	}
}
