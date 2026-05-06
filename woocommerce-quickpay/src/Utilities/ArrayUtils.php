<?php

namespace QuickpayPSP\Utilities;

/**
 * General-purpose array manipulation utilities.
 *
 * No WordPress or WooCommerce dependencies — safe to use in any layer.
 */
final class ArrayUtils {
	/**
	 * Inserts a new key/value pair into an array immediately after a given key.
	 *
	 * If the needle key does not exist the original array is returned unchanged.
	 *
	 * @param string $needle    The key after which the new entry should be inserted.
	 * @param array  $haystack  The source array.
	 * @param string $new_key   The key for the new entry.
	 * @param mixed  $new_value The value for the new entry.
	 * @return array The array with the new entry inserted, or the original array if needle was not found.
	 */
	public static function insert_after( string $needle, array $haystack, string $new_key, $new_value ): array {
		if ( array_key_exists( $needle, $haystack ) ) {
			$new_array = [];

			foreach ( $haystack as $key => $value ) {
				$new_array[ $key ] = $value;
				if ( $key === $needle ) {
					$new_array[ $new_key ] = $new_value;
				}
			}

			return $new_array;
		}

		return $haystack;
	}
}
