<?php

namespace QuickpayPSP\WooCommerce;

use QuickpayPSP\Support\Dependencies;

class WooGuard {
	/**
	 * @param callable $fn
	 *
	 * @return void
	 */
	public static function when_woocommerce_ready( callable $fn ): void {
		add_action( 'plugins_loaded', function () use ( $fn ) {
			if ( ! class_exists( \WooCommerce::class ) ) {
				return;
			}
			$fn();
		}, 20 );
	}

	/**
	 * Executes the provided callback function if WooCommerce Subscriptions is active.
	 *
	 * @param callable $fn The callback function to execute when WooCommerce Subscriptions is active.
	 *
	 * @return void
	 */
	public static function when_subscriptions_active( callable $fn ): void {
		self::when_woocommerce_ready( static function () use ( $fn ) {
			if ( ! Dependencies::is_woo_subscriptions_active() ) {
				return;
			}
			$fn();
		} );
	}
}
