<?php

namespace QuickpayPSP\Extensions\SpamShield;

use QuickpayPSP\Extensions\Contracts\ExtensionIntegrationInterface;

class SpamShieldIntegration implements ExtensionIntegrationInterface {

	public function is_available(): bool {
		return true;
	}

	public function register(): void {
		add_filter( 'wpss_misc_form_spam_check_bypass', [ $this, 'handle_spam_check_bypass' ], - 10 );
	}

	/**
	 * @param bool $bypass
	 *
	 * @return bool
	 */
	public function handle_spam_check_bypass( bool $bypass ): bool {
		return isset( $_GET['wc-api'] ) && strtolower( sanitize_text_field( wp_unslash( $_GET['wc-api'] ) ) ) === 'wc_quickpay';
	}
}
