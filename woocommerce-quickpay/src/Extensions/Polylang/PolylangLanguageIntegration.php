<?php

namespace QuickpayPSP\Extensions\Polylang;

use QuickpayPSP\Extensions\Contracts\ExtensionIntegrationInterface;

final class PolylangLanguageIntegration implements ExtensionIntegrationInterface {
	public function is_available(): bool {
		return function_exists( 'pll_current_language' );
	}

	public function register(): void {
		add_filter( 'woocommerce_quickpay_language', [ $this, 'filter_language' ], 10, 1 );
	}

	/** @param mixed $language @return mixed */
	public function filter_language( $language ): ?string {
		if ( function_exists( 'pll_current_language' ) ) {
			$lang = pll_current_language();
			if ( is_string( $lang ) && $lang !== '' ) {
				return $lang;
			}
		}

		return $language;
	}
}
