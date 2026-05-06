<?php
namespace QuickpayPSP\Providers;

use QuickpayPSP\Extensions\ExtensionsRegistrar;
use QuickpayPSP\Extensions\Polylang\PolylangLanguageIntegration;
use QuickpayPSP\Extensions\QTranslate\QTranslateIntegration;
use QuickpayPSP\Extensions\SpamShield\SpamShieldIntegration;
use QuickpayPSP\Extensions\WPML\WpmlLanguageIntegration;
use QuickpayPSP\Providers\Contracts\ServiceProviderInterface;
use QuickpayPSP\Support\Services;

/**
 * Registers third-party extension integrations within the Infrastructure Layer.
 *
 * Covers:
 * - Language integrations (WPML, Polylang, qTranslate)
 * - Spam protection integration (SpamShield)
 *
 * All integrations are collected into a single ExtensionsRegistrar that hooks
 * each one into WordPress at the appropriate time. No business logic lives here.
 */
class ExtensionsProvider implements ServiceProviderInterface {
	public function register( Services $services ): void {
		$services->set( 'extensions/registrar', static function () {
			return new ExtensionsRegistrar( [
				new WpmlLanguageIntegration(),
				new PolylangLanguageIntegration(),
				new QTranslateIntegration(),
				new SpamShieldIntegration(),
			] );
		} );
	}
}
