<?php

declare( strict_types=1 );

namespace SEOThemes\Hooks;

use function class_exists;

if ( ! class_exists( __NAMESPACE__ . '\\Factory' ) ) {
	class Factory {
		public function instance( Hooks $hooks ): Hooks {
			static $instance = null;

			if ( $instance === null ) {
				$instance = $hooks;
			}

			return $instance;
		}
	}
}
