<?php

namespace QuickpayPSP\Extensions;

use QuickpayPSP\Extensions\Contracts\ExtensionIntegrationInterface;

final class ExtensionsRegistrar {
	/** @var ExtensionIntegrationInterface[] */
	private array $integrations;

	/**
	 * @param ExtensionIntegrationInterface[] $integrations
	 */
	public function __construct( array $integrations ) {
		$this->integrations = $integrations;
	}

	public function register(): void {
		foreach ( $this->integrations as $integration ) {
			if ( $integration->is_available() ) {
				$integration->register();
			}
		}
	}
}
