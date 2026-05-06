<?php

namespace QuickpayPSP\Extensions\QTranslate;

use QuickpayPSP\Extensions\Contracts\ExtensionIntegrationInterface;

/**
 *
 */
class QTranslateIntegration implements ExtensionIntegrationInterface {

	public function is_available(): bool {
		return defined( 'QTRANSLATE_DIR' ) && defined( 'QTX_VERSION' );
	}

	public function register(): void {
		add_filter( 'qtranslate_language_detect_redirect', [ $this, 'prevent_redirect' ], 10, 3 );
	}

	/**
	 * @param $url_lang
	 * @param $url_orig
	 * @param $url_info
	 *
	 * @return false|mixed|string
	 */
	public function prevent_redirect( $url_lang, $url_orig, $url_info ) {
		if ( isset( $url_info['query'] ) && stripos( $url_info['query'], 'wc-api=wc_quickpay' ) !== false ) {
			return false;
		}

		return $url_lang;
	}
}
