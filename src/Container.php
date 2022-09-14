<?php

declare( strict_types=1 );

namespace SEOThemes\Hooks;

use Closure;
use stdClass;
use function class_exists;

if ( ! class_exists( __NAMESPACE__ . '\\Container' ) ) {

	class Container {
		private object $hooks;
		public string  $namespace;

		public function __construct( string $namespace = '' ) {
			$this->hooks     = new stdClass();
			$this->namespace = $namespace . '\\';
		}

		public function get( string $hook_name, string $alias, int $priority ): Closure {
			$alias = $this->namespace . $alias;

			return $this->hooks->{$hook_name}->{$alias}->{$priority};
		}

		public function has( string $hook_name, string $alias, int $priority ): bool {
			$alias = $this->namespace . $alias;

			return isset( $this->hooks->{$hook_name}->{$alias}->{$priority} );
		}

		public function add( string $hook_name, string $alias, Closure $callback, int $priority ): object {
			$alias = $this->namespace . $alias;

			if ( ! isset( $this->hooks->{$hook_name} ) ) {
				$this->hooks->{$hook_name} = new stdClass();
			}

			if ( ! isset( $this->hooks->{$hook_name}->{$alias} ) ) {
				$this->hooks->{$hook_name}->{$alias} = new stdClass();
			}

			$this->hooks->{$hook_name}->{$alias}->{$priority} = $callback;

			return $this;
		}

		public function remove( string $hook_name, string $alias, int $priority ): object {
			$alias = $this->namespace . $alias;

			unset ( $this->hooks->{$hook_name}->{$alias}->{$priority} );

			return $this;
		}
	}
}
