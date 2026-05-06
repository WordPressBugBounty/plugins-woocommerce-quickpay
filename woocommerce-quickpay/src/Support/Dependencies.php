<?php

namespace QuickpayPSP\Support;

/**
 * Class Dependencies provides utility methods to verify the presence and state of specific plugin dependencies required for functionality.
 */
class Dependencies {

	/**
	 * Checks if WooCommerce Subscriptions is active by verifying the existence of the WC_Subscriptions class and a specific property value.
	 *
	 * @return bool True if WooCommerce Subscriptions is active, false otherwise.
	 */
	public static function is_woo_subscriptions_active(): bool {
		return class_exists( 'WC_Subscriptions' ) && \WC_Subscriptions::$name === 'subscription';
	}

	/**
	 * Checks if WooCommerce Pre-Orders is active by verifying the existence of the WC_Pre_Orders class.
	 *
	 * @return bool True if WooCommerce Pre-Orders is active, false otherwise.
	 */
	public static function is_pre_orders_active(): bool {
		return class_exists( 'WC_Pre_Orders' );
	}

	/**
	 * Checks if the user agent matches the specified browser.
	 *
	 * @param string $browser The name of the browser to check for (e.g., "Chrome", "Firefox").
	 *
	 * @return bool Returns true if the user agent matches the specified browser, false otherwise.
	 */
	public static function is_browser( string $browser ): bool {
		if ( ! isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			return false;
		}

		$u_agent = sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) );
		$name    = 'Unknown';

		if ( false !== stripos( $u_agent, "MSIE" ) && false === stripos( $u_agent, "Opera" ) ) {
			$name = "MSIE";
		} elseif ( false !== stripos( $u_agent, "Firefox" ) ) {
			$name = "Firefox";
		} elseif ( false !== stripos( $u_agent, "Chrome" ) ) {
			$name = "Chrome";
		} elseif ( false !== stripos( $u_agent, "Safari" ) ) {
			$name = "Safari";
		} elseif ( false !== stripos( $u_agent, "Opera" ) ) {
			$name = "Opera";
		} elseif ( false !== stripos( $u_agent, "Netscape" ) ) {
			$name = "Netscape";
		}

		return strtolower( $name ) === strtolower( $browser );
	}
}
