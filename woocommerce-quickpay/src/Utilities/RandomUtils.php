<?php

namespace QuickpayPSP\Utilities;

/**
 * Utility methods for generating random values.
 *
 * No WordPress or WooCommerce dependencies — safe to use in any layer.
 */
final class RandomUtils {
	/**
	 * Generates a cryptographically random alphanumeric string of the given length.
	 *
	 * Falls back to a time-based string if the system's random source is unavailable.
	 *
	 * @param int $length Desired string length.
	 * @return string Random alphanumeric string.
	 */
	public static function create_random_string( int $length ): string {
		$characters    = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$random_string = '';

		for ( $i = 0; $i < $length; $i++ ) {
			try {
				$index         = random_int( 0, strlen( $characters ) - 1 );
				$random_string .= $characters[ $index ];
			} catch ( \Exception $e ) {
				$random_string = substr( (string) time(), - $length );
				break;
			}
		}

		return $random_string;
	}
}
