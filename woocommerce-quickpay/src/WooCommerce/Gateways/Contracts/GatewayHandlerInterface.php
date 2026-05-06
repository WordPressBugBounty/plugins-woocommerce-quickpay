<?php
declare(strict_types=1);

namespace QuickpayPSP\WooCommerce\Gateways\Contracts;

/**
 * Implement this interface to register gateway-specific hook behavior
 * without requiring the gateway to be instantiated.
 */
interface GatewayHandlerInterface {
	/**
	 * Returns the gateway ID this handler is responsible for (e.g. 'sofort').
	 */
	public function gateway_id(): string;

	/**
	 * Register any WordPress action/filter hooks needed by this handler.
	 */
	public function register(): void;
}
