<?php

namespace QuickpayPSP\WooCommerce\Registrars;

use QuickpayPSP\WooCommerce\Gateways\GatewayRegistry;

/**
 * Handles the registration of payment gateways within the WooCommerce environment.
 *
 * This class integrates custom payment gateways into the WooCommerce ecosystem
 * by leveraging a registry of gateway classes and applying them to the available
 * payment gateways through a WordPress filter.
 */
final class GatewayRegistrar {

	private GatewayRegistry $gateway_registry;

	public function __construct( GatewayRegistry $gateway_registry ) {
		$this->gateway_registry = $gateway_registry;
	}

	public function register(): void {
		add_filter( 'woocommerce_payment_gateways', function ( array $gateways ) {
			return array_merge( $gateways, $this->gateway_registry->classes() );
		}, 50 );
	}
}
