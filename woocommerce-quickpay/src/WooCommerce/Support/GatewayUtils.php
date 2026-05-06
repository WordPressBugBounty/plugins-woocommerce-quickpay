<?php

namespace QuickpayPSP\WooCommerce\Support;

use QuickpayPSP\WooCommerce\Gateways\BaseGateway;
use QuickpayPSP\WooCommerce\Gateways\GatewayIcons;
use WC_Payment_Gateway;

final class GatewayUtils {
	/**
	 * @deprecated Use GatewayIcons::logo_url() instead.
	 */
	public static function get_payment_type_logo( $payment_type ): ?string {
		return GatewayIcons::logo_url( $payment_type );
	}

	public static function is_plugin_gateway( WC_Payment_Gateway $class ): bool {
		return $class instanceof BaseGateway;
	}

	public static function get_plugin_gateways(): array {
		if ( ! function_exists( 'wc' ) ) {
			return [];
		}

		$gateways = wc()->payment_gateways();
		if ( ! $gateways ) {
			return [];
		}

		return array_filter( $gateways->payment_gateways(), static function ( $gateway ) {
			return $gateway instanceof WC_Payment_Gateway && self::is_plugin_gateway( $gateway );
		} );
	}
}
