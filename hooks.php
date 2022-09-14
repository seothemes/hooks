<?php

declare( strict_types=1 );

if ( ! function_exists( 'hook_container' ) ) {
	/**
	 * Stores the static hooks object.
	 *
	 * @since 1.0.0
	 *
	 * @param string $method  Calling __METHOD__ (add_hook or remove_hook).
	 * @param mixed  ...$args Hook name, alias, callback and priority.
	 *
	 * @return object
	 */
	function hook_container( string $method = '', ...$args ): object {
		static $hooks = null;

		if ( ! $hooks ) {
			$hooks = new stdClass();
		}

		$hook_name = $args[0] ?? '';
		$alias     = $args[1] ?? '';
		$callback  = $args[2] ?? null;
		$priority  = $args[3] ?? 10;

		if ( $method === 'add_hook' ) {
			if ( ! isset( $hooks->{$hook_name} ) ) {
				$hooks->{$hook_name} = new stdClass();
			}

			$alias = apply_filters( 'hook_namespace', $alias, $args );

			if ( ! isset( $hooks->{$hook_name}->{$alias} ) ) {
				$hooks->{$hook_name}->{$alias} = new stdClass();
			}

			$hooks->{$hook_name}->{$alias}->{$priority} = $callback;
		}

		if ( $method === 'remove_hook' ) {
			$alias = apply_filters( 'hook_namespace', $alias );

			unset ( $hooks->{$hook_name}->{$alias}->{$priority} );
		}

		return $hooks;
	}
}

if ( ! function_exists( 'add_hook' ) ) {
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
	function add_hook( string $hook_name, string $alias, Closure $callback, int $priority = 10, int $accepted_args = 1 ): bool {
		hook_container( __METHOD__, $hook_name, $alias, $callback, $priority );

		return add_filter(
			$hook_name,
			function () use ( $hook_name, $alias, $callback, $priority, $accepted_args ) {
				$args  = func_get_args();
				$hooks = hook_container();

				if ( isset( $hooks->{$hook_name}->{$alias}->{$priority} ) ) {
					return $callback( ...$args );
				}

				return is_array( $args ) ? $args[0] : $args;
			},
			$priority,
			$accepted_args
		);
	}
}

if ( ! function_exists( 'remove_hook' ) ) {
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
	function remove_hook( string $hook_name, string $alias, int $priority = 10 ): bool {
		$hooks = hook_container();

		if ( isset( $hooks->{$hook_name}->{$alias}->{$priority} ) ) {
			hook_container( __METHOD__, $hook_name, $alias, $priority );

			return true;
		}

		return false;
	}
}

if ( ! function_exists( 'addFilter' ) ) {
	function addFilter( string $hook_name, string $alias, Closure $callback, int $priority = 10, int $accepted_args = 1 ): bool {
		return add_hook( $hook_name, $alias, $callback, $priority, $accepted_args );
	}
}

if ( ! function_exists('addAction') ) {
	function addAction( string $hook_name, string $alias, Closure $callback, int $priority = 10, int $accepted_args = 1 ) : void {
		add_hook( $hook_name, $alias, $callback, $priority, $accepted_args );
	}
}

if ( ! function_exists('removeFilter')) {
	function removeFilter( string $hook_name, string $alias, int $priority = 10 ): bool {
		return remove_hook( $hook_name, $alias, $priority );
	}
}

if ( ! function_exists('removeAction')) {
	function removeAction( string $hook_name, string $alias, int $priority = 10 ): bool {
		return remove_hook( $hook_name, $alias, $priority );
	}
}
