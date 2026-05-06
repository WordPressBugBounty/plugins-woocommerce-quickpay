<?php
namespace QuickpayPSP\Providers;

use QuickpayPSP\Support\Config\GatewaySettingsProvider;
use QuickpayPSP\WooCommerce\Gateways\VippsMobilePaySubscriptionsGateway;
use QuickpayPSP\WooCommerce\Gateways\GatewayRegistry;
use QuickpayPSP\WooCommerce\Gateways\AnydayGateway;
use QuickpayPSP\WooCommerce\Gateways\ExtraGateway;
use QuickpayPSP\WooCommerce\Gateways\ApplePayGateway;
use QuickpayPSP\WooCommerce\Gateways\Fbg1886Gateway;
use QuickpayPSP\WooCommerce\Gateways\GooglePayGateway;
use QuickpayPSP\WooCommerce\Gateways\IdealGateway;
use QuickpayPSP\WooCommerce\Gateways\KlarnaGateway;
use QuickpayPSP\WooCommerce\Gateways\MainGateway;
use QuickpayPSP\WooCommerce\Gateways\ResursGateway;
use QuickpayPSP\WooCommerce\Gateways\SofortGateway;
use QuickpayPSP\WooCommerce\Gateways\SwishGateway;
use QuickpayPSP\WooCommerce\Gateways\TrustlyGateway;
use QuickpayPSP\WooCommerce\Gateways\ViabillGateway;
use QuickpayPSP\WooCommerce\Gateways\VippsGateway;
use QuickpayPSP\WooCommerce\Gateways\VippsMobilePayGateway;
use QuickpayPSP\WooCommerce\Registrars\GatewayRegistrar;
use QuickpayPSP\WooCommerce\Settings\GatewaySettingsSchema;
use QuickpayPSP\Providers\Contracts\ServiceProviderInterface;
use QuickpayPSP\Support\Services;

/**
 * Registers all payment gateway services within the Integration Layer.
 *
 * Covers:
 * - The GatewayRegistry containing all supported payment gateways
 * - The GatewayRegistrar that hooks gateways into WooCommerce
 * - The settings schema (field definitions) and settings provider (runtime values)
 *
 * This provider is the single source of truth for which gateways are active
 * in the plugin. Adding or removing a gateway is done here.
 */
class GatewayProvider implements ServiceProviderInterface {
	public function register( Services $services ): void {
		$this->register_gateways( $services );
		$this->register_settings( $services );
	}

	/**
	 * Gateway registry and registrar.
	 *
	 * Populates the registry with every supported payment gateway class, then
	 * binds the registrar that exposes them to WooCommerce via the
	 * `woocommerce_payment_gateways` filter.
	 */
	private function register_gateways( Services $services ): void {
		$services->set( 'gateway/registry', static function (): GatewayRegistry {
			$r = new GatewayRegistry();
			$r->add( MainGateway::class );
			$r->add( AnydayGateway::class );
			$r->add( ApplePayGateway::class );
			$r->add( Fbg1886Gateway::class );
			$r->add( GooglePayGateway::class );
			$r->add( IdealGateway::class );
			$r->add( KlarnaGateway::class );
			$r->add( ResursGateway::class );
			$r->add( SofortGateway::class );
			$r->add( SwishGateway::class );
			$r->add( TrustlyGateway::class );
			$r->add( ViabillGateway::class );
			$r->add( VippsGateway::class );
			$r->add( VippsMobilePayGateway::class );
			$r->add( VippsMobilePaySubscriptionsGateway::class );
			$r->add( ExtraGateway::class );
			return $r;
		} );

		$services->set( 'gateway/registrar', static fn( Services $s ): GatewayRegistrar => new GatewayRegistrar(
			$s->get( 'gateway/registry' )
		) );
	}

	/**
	 * Gateway settings schema and provider.
	 *
	 * The schema defines the available settings fields (used to render the
	 * admin settings page), while the provider gives runtime access to the
	 * stored option values throughout the plugin.
	 */
	private function register_settings( Services $services ): void {
		$services->set( 'gateway/settings_schema', static fn( Services $s ): GatewaySettingsSchema => new GatewaySettingsSchema() );

		$services->set( 'gateway/settings_provider', static fn( Services $s ) => new GatewaySettingsProvider( 'quickpay' ) );
	}
}
