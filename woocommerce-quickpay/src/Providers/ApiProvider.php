<?php
namespace QuickpayPSP\Providers;

use QuickpayPSP\Support\Http\WordPressHttpClient;
use QuickpayPSP\QuickPay\Api\ApiClientFactory;
use QuickpayPSP\Providers\Contracts\ServiceProviderInterface;
use QuickpayPSP\Support\Services;

/**
 * Registers Infrastructure Layer services for HTTP communication and the QuickPay API.
 *
 * Covers:
 * - The WordPress HTTP client used for all outbound requests
 * - The QuickPay API client factory, which depends on the HTTP client and gateway settings
 *
 * This provider sits in the Infrastructure Layer — no business logic or WP hooks here.
 */
class ApiProvider implements ServiceProviderInterface {
	public function register( Services $services ): void {
		// WordPress-backed HTTP client used for all outbound API requests
		$services->set( 'http/client', WordPressHttpClient::class );

		// Factory that produces authenticated QuickPay API clients on demand
		$services->set( 'quickpay/api/factory', static fn( Services $s ) => new ApiClientFactory(
			$s->get( 'http/client' ),
			$s->get( 'gateway/settings_provider' )
		) );
	}
}
