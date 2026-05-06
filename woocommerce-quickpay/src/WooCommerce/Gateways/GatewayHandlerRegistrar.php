<?php

declare(strict_types=1);

namespace QuickpayPSP\WooCommerce\Gateways;

use QuickpayPSP\WooCommerce\Gateways\Contracts\GatewayHandlerInterface;

/**
 * Registers gateway-specific handlers without requiring gateway instantiation.
 *
 * Each handler is a lightweight class responsible for hooking into the relevant
 * actions and filters for its gateway. Add new handlers here as gateways require
 * custom behavior.
 */
final class GatewayHandlerRegistrar {
	/** @var GatewayHandlerInterface[] */
	private array $handlers;

	public function __construct( GatewayHandlerInterface ...$handlers ) {
		$this->handlers = $handlers;
	}

	public function register(): void {
		foreach ( $this->handlers as $handler ) {
			$handler->register();
		}
	}
}
