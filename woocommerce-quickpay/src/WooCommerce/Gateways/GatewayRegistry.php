<?php

namespace QuickpayPSP\WooCommerce\Gateways;

use QuickpayPSP\WooCommerce\Gateways\BaseGateway;
use RuntimeException;

/**
 * Handles the registration of payment gateway classes.
 *
 * Best practice:
 * - Each gateway class defines: public const ID = 'quickpay_...';
 * - BaseGateway sets $this->id = static::ID;
 */
final class GatewayRegistry {
	/** @var array<string, class-string<WC_Payment_Gateway>> */
	private array $classes_by_id = [];

	/**
	 * Register a gateway class.
	 *
	 * @param class-string<WC_Payment_Gateway> $class
	 */
	public function add( string $class ): void {
		// If you want to enforce your own base type:
		if ( ! is_subclass_of( $class, BaseGateway::class ) ) {
			throw new RuntimeException( "Gateway class must extend " . BaseGateway::class . ": {$class}" );
		}

		/** @var class-string<BaseGateway> $class */
		$gatewayId = $class::ID ?? null;

		if ( ! is_string( $gatewayId ) || $gatewayId === '' ) {
			throw new RuntimeException( "Gateway class missing non-empty public const ID: {$class}" );
		}

		// Prevent accidental overwrites (helps catch copy/paste mistakes)
		if ( isset( $this->classes_by_id[ $gatewayId ] ) && $this->classes_by_id[ $gatewayId ] !== $class ) {
			throw new RuntimeException( "Duplicate gateway ID '{$gatewayId}' for {$class} (already used by {$this->classes_by_id[$gatewayId]})" );
		}

		$this->classes_by_id[ $gatewayId ] = $class;
	}

	/**
	 * @return array<class-string<WC_Payment_Gateway>>
	 */
	public function classes(): array {
		return array_values( $this->classes_by_id );
	}

	/**
	 * @return string[]
	 */
	public function ids(): array {
		return array_keys( $this->classes_by_id );
	}

	/**
	 * @return class-string<WC_Payment_Gateway>
	 */
	public function class_for( string $gatewayId ): string {
		if ( ! isset( $this->classes_by_id[ $gatewayId ] ) ) {
			throw new RuntimeException( "Unknown gatewayId: {$gatewayId}" );
		}

		return $this->classes_by_id[ $gatewayId ];
	}
}
