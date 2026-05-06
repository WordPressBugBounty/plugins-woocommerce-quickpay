<?php

namespace QuickpayPSP\Utilities;

/**
 * Utility methods for currency-aware price conversion and formatting.
 *
 * Handles the multiplied integer format used by the QuickPay API (e.g. 1000 = 10.00 DKK)
 * and accounts for zero-decimal currencies such as JPY and KRW.
 *
 * No WordPress dependencies except where WooCommerce separator options are read
 * (price_custom_to_multiplied). Safe to use in the Application and Domain layers.
 */
final class MoneyUtils {
	/**
	 * Returns the price with decimals. 1010 returns as 10.10.
	 *
	 * @param mixed  $price
	 * @param string $currency
	 *
	 * @return mixed
	 */
	public static function price_normalize( $price, string $currency ) {
		if ( self::is_currency_using_decimals( $currency ) ) {
			return number_format( (float) $price / 100, 2, wc_get_price_decimal_separator(), '' );
		}

		return $price;
	}

	/**
	 * Returns a normalized float based on the multiplied amount.
	 *
	 * @param mixed  $price
	 * @param string $currency
	 *
	 * @return mixed
	 */
	public static function price_multiplied_to_float( $price, string $currency ) {
		if ( self::is_currency_using_decimals( $currency ) ) {
			return number_format( (float) $price / 100, 2, '.', '' );
		}

		return $price;
	}

	/**
	 * Multiplies a custom formatted price based on the WooCommerce decimal/thousand separators.
	 *
	 * @param mixed  $price
	 * @param string $currency
	 *
	 * @return int|float|string
	 */
	public static function price_custom_to_multiplied( $price, string $currency ) {
		$decimal_separator  = get_option( 'woocommerce_price_decimal_sep' );
		$thousand_separator = get_option( 'woocommerce_price_thousand_sep' );

		$price = str_replace( [ $thousand_separator, $decimal_separator ], [ '', '.' ], (string) $price );

		return self::price_multiply( $price, $currency );
	}

	/**
	 * Returns the price with no decimals. 10.10 returns as 1010.
	 *
	 * @param mixed  $price
	 * @param string $currency
	 *
	 * @return int|float|string
	 */
	public static function price_multiply( $price, ?string $currency = null ) {
		if ( $currency && self::is_currency_using_decimals( $currency ) ) {
			return number_format( (float) $price * 100, 0, '', '' );
		}

		return $price;
	}

	/**
	 * Returns whether the given ISO 4217 currency code uses decimal sub-units.
	 *
	 * Zero-decimal currencies (e.g. JPY, KRW) must not be divided or multiplied
	 * by 100 when converting between the API's integer format and display values.
	 *
	 * @param string $currency ISO 4217 currency code.
	 * @return bool True if the currency uses decimals, false for zero-decimal currencies.
	 */
	public static function is_currency_using_decimals( string $currency ): bool {
		$non_decimal_currencies = [
			'BIF',
			'CLP',
			'DJF',
			'GNF',
			'JPY',
			'KMF',
			'KRW',
			'PYG',
			'RWF',
			'UGX',
			'UYI',
			'VND',
			'VUV',
			'XAF',
			'XOF',
			'XPF',
		];

		return ! in_array( strtoupper( $currency ), $non_decimal_currencies, true );
	}
}
