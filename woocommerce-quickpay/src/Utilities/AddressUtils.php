<?php

namespace QuickpayPSP\Utilities;

/**
 * Utility methods for parsing raw address strings into their constituent parts.
 *
 * Used when building basket/shipping data for the QuickPay API, which requires
 * street name, house number, and house extension as separate fields.
 *
 * No WordPress or WooCommerce dependencies — safe to use in any layer.
 */
final class AddressUtils {
	/**
	 * Extracts the street name from a full address string.
	 *
	 * Everything before the first house number is returned, trimmed of whitespace.
	 * If no house number is found the full address is returned as-is.
	 *
	 * @param string $address Raw address string, e.g. "Main Street 12A".
	 * @return string Street name, e.g. "Main Street".
	 */
	public static function get_street_name( string $address ): string {
		$house_number = self::get_house_number( $address );
		$street_name  = $address;

		if ( $house_number !== '' ) {
			[ $street_name ] = explode( $house_number, $address, 2 );
		}

		return trim( $street_name );
	}

	/**
	 * Extracts the house number (including optional letter suffix) from an address.
	 *
	 * Uses a simple regex to find the first numeric sequence optionally followed
	 * by a single letter, e.g. "12" or "12A".
	 *
	 * @param string $address Raw address string.
	 * @return string House number, or an empty string if none is found.
	 */
	public static function get_house_number( string $address ): string {
		preg_match( '/\d+[A-Z]?/i', $address, $matches );

		return trim( (string) reset( $matches ) );
	}

	/**
	 * Extracts the house extension (floor, apartment, etc.) from an address.
	 *
	 * Returns everything after the house number, stripped of leading/trailing
	 * whitespace and commas.
	 *
	 * @param string $address Raw address string.
	 * @return string House extension, or an empty string if none is found.
	 */
	public static function get_house_extension( string $address ): string {
		$house_number = self::get_house_number( $address );
		$extension    = '';

		if ( $house_number !== '' ) {
			[ , $extension ] = explode( $house_number, $address, 2 );
		}

		return trim( trim( $extension, ',' ) );
	}
}
