<?php

namespace QuickpayPSP\Extensions\WPML;

use QuickpayPSP\Extensions\Contracts\ExtensionIntegrationInterface;

final class WpmlLanguageIntegration implements ExtensionIntegrationInterface {
	public function is_available(): bool {
		// WPML sets this constant in runtime contexts where language is known.
		return defined( 'ICL_LANGUAGE_CODE' ) || defined( 'ICL_SITEPRESS_VERSION' );
	}

	public function register(): void {
		add_filter( 'woocommerce_quickpay_language', [ $this, 'filter_language' ], 10, 1 );
	}

	/** @param mixed $language @return mixed */
	public function filter_language( $language ) {
		if ( defined( 'ICL_LANGUAGE_CODE' ) && ICL_LANGUAGE_CODE ) {
			return ICL_LANGUAGE_CODE;
		}

		return $language;
	}
}
