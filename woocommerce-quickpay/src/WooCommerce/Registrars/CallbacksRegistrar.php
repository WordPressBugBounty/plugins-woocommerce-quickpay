<?php

declare(strict_types=1);

namespace QuickpayPSP\WooCommerce\Registrars;

use QuickpayPSP\WooCommerce\Callbacks\WcApiCallbackEndpoint;

/**
 * Registers WooCommerce WC-API endpoint hooks for QuickPay callbacks.
 */
final class CallbacksRegistrar {
	private WcApiCallbackEndpoint $endpoint;

	public function __construct( WcApiCallbackEndpoint $endpoint ) {
		$this->endpoint = $endpoint;
	}

	public function register(): void {
		// WooCommerce transforms ?wc-api=WC_QuickPay (and similar) into action 'woocommerce_api_wc_quickpay'.
		add_action( 'woocommerce_api_wc_quickpay', [ $this->endpoint, 'handle' ] );
	}
}
