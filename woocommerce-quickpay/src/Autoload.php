<?php

namespace QuickpayPSP;

final class Autoload {
	private static bool $registered = false;

	public static function register( string $srcDir ): void {
		if ( self::$registered ) {
			return;
		}
		self::$registered = true;

		$prefix  = 'QuickpayPSP\\';
		$baseDir = rtrim( $srcDir, '/\\' ) . DIRECTORY_SEPARATOR;

		spl_autoload_register( function ( $class ) use ( $prefix, $baseDir ) {
			if ( strncmp( $prefix, $class, strlen( $prefix ) ) !== 0 ) {
				return;
			}
			$relative = substr( $class, strlen( $prefix ) );
			$file     = $baseDir . str_replace( '\\', DIRECTORY_SEPARATOR, $relative ) . '.php';
			if ( is_file( $file ) ) {
				require_once $file;
			}
		} );
	}
}
